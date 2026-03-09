<?php
include '../../class/include.php';
header('Content-Type: application/json');

// Get next ID
if (isset($_POST['get_next_id'])) {
    $ASSIGNMENT = new DagCompanyAssignment(NULL);
    $next_id = $ASSIGNMENT->getNextId();
    $assignment_number = 'CDA-' . str_pad($next_id, 5, "0", STR_PAD_LEFT);
    echo json_encode(['status' => 'success', 'next_id' => $assignment_number]);
    exit();
}

// Search DAGs
if (isset($_POST['search_dag'])) {
    $keyword = $_POST['keyword'];
    $show_rejected = isset($_POST['show_rejected']) ? (int) $_POST['show_rejected'] : 0;
    $db = Database::getInstance();

    if ($show_rejected === 1) {
        $query = "SELECT DISTINCT dc.*, c.name as customer_name, c.name_2 as customer_name_2 
                  FROM `dag_customers` dc 
                  LEFT JOIN `customer_master` c ON dc.customer_id = c.id 
                  JOIN `dag_company_assignment_items` dai ON dai.dag_id = dc.id
                  WHERE dai.company_status = 'Rejected' 
                  AND dc.id NOT IN (
                      SELECT dag_id 
                      FROM `dag_company_assignment_items` 
                      WHERE company_status IN ('Processing', 'Completed')
                  )
                  AND (dc.my_number LIKE '%$keyword%' OR dc.serial_no LIKE '%$keyword%') 
                  ORDER BY dc.id DESC LIMIT 20";
    } else {
        $query = "SELECT dc.*, c.name as customer_name, c.name_2 as customer_name_2 
                  FROM `dag_customers` dc 
                  LEFT JOIN `customer_master` c ON dc.customer_id = c.id 
                  WHERE dc.id NOT IN (
                      SELECT dag_id 
                      FROM `dag_company_assignment_items`
                  )
                  AND (dc.my_number LIKE '%$keyword%' OR dc.serial_no LIKE '%$keyword%') 
                  ORDER BY dc.id DESC LIMIT 20";
    }

    $result = $db->readQuery($query);
    $dags = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['customer_full_name'] = trim($row['customer_name'] . ' ' . $row['customer_name_2']);
        $dags[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $dags]);
    exit();
}

// Create Assignment
if (isset($_POST['create'])) {
    // Collect Header Data
    $company_id = $_POST['company_id'] ?? null;
    $company_receipt_number = $_POST['company_receipt_number'] ?? null;
    $company_issued_date = $_POST['company_issued_date'] ?? null;
    $assignment_number = $_POST['assignment_number'] ?? null;

    $items = $_POST['items'] ?? []; // Array of objects

    if (empty($company_id) || empty($company_receipt_number) || empty($items)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing header data or empty items.']);
        exit();
    }

    $ASSIGNMENT = new DagCompanyAssignment(NULL);
    $ASSIGNMENT->assignment_number = $assignment_number;
    $ASSIGNMENT->company_id = $company_id;
    $ASSIGNMENT->company_receipt_number = $company_receipt_number;
    $ASSIGNMENT->company_issued_date = $company_issued_date;

    $createdAssignment = $ASSIGNMENT->create();

    if ($createdAssignment) {
        if (is_string($items)) {
            $items = json_decode($items, true);
        }
        $addSuccess = $createdAssignment->addItems($items);
        if ($addSuccess) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add some items.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create assignment header.']);
    }
    exit();
}

// Update Assignment
if (isset($_POST['update'])) {
    $id = $_POST['id'] ?? null;
    $company_id = $_POST['company_id'] ?? null;
    $company_receipt_number = $_POST['company_receipt_number'] ?? null;
    $company_issued_date = $_POST['company_issued_date'] ?? null;

    $items = $_POST['items'] ?? [];

    if (empty($id) || empty($company_id) || empty($company_receipt_number) || empty($items)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing data.']);
        exit();
    }

    $ASSIGNMENT = new DagCompanyAssignment($id);
    $ASSIGNMENT->company_id = $company_id;
    $ASSIGNMENT->company_receipt_number = $company_receipt_number;
    $ASSIGNMENT->company_issued_date = $company_issued_date;

    $updatedAssignment = $ASSIGNMENT->update();

    if ($updatedAssignment) {
        // Clear old items and add new ones
        $updatedAssignment->deleteItems();

        if (is_string($items)) {
            $items = json_decode($items, true);
        }
        $addSuccess = $updatedAssignment->addItems($items);

        if ($addSuccess) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update some items.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update assignment header.']);
    }
    exit();
}

// Delete Assignment
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $ASSIGNMENT = new DagCompanyAssignment($id);
    // Delete items first
    $ASSIGNMENT->deleteItems();
    // Then delete assignment
    if ($ASSIGNMENT->delete()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete.']);
    }
    exit();
}

// Search Assignments
if (isset($_POST['search_assignment'])) {
    $keyword = $_POST['keyword'];
    $db = Database::getInstance();

    $query = "SELECT da.*, c.name as company_name,
              (SELECT GROUP_CONCAT(dc.my_number SEPARATOR ', ')
               FROM `dag_company_assignment_items` dai
               JOIN `dag_customers` dc ON dai.dag_id = dc.id
               WHERE dai.assignment_id = da.id) as my_numbers
              FROM `dag_company_assignments` da 
              LEFT JOIN `company_master` c ON da.company_id = c.id 
              WHERE (da.assignment_number LIKE '%$keyword%' OR da.company_receipt_number LIKE '%$keyword%'
              OR EXISTS (
                  SELECT 1 FROM `dag_company_assignment_items` dai2
                  JOIN `dag_customers` dc2 ON dai2.dag_id = dc2.id
                  WHERE dai2.assignment_id = da.id AND dc2.my_number LIKE '%$keyword%'
              )) 
              ORDER BY da.id DESC LIMIT 20";

    $result = $db->readQuery($query);
    $assignments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $assignments[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $assignments]);
    exit();
}

// Load Assignment Items
if (isset($_POST['get_assignment_items'])) {
    $assignment_id = $_POST['assignment_id'];
    $ASSIGNMENT = new DagCompanyAssignment($assignment_id);
    $items = $ASSIGNMENT->getItems();

    $detailed_items = [];
    $db = Database::getInstance();
    foreach ($items as $item) {
        $dag_id = $item['dag_id'];
        $q = "SELECT dc.id as dag_id, dc.dag_number, dc.my_number, dc.size, dc.serial_no, c.name as customer_name, c.name_2 as customer_name_2 
              FROM `dag_customers` dc 
              LEFT JOIN `customer_master` c ON dc.customer_id = c.id 
              WHERE dc.id = " . $dag_id;
        $res = mysqli_fetch_assoc($db->readQuery($q));
        if ($res) {
            $item['my_number'] = $res['my_number'];
            $item['customer_full_name'] = trim($res['customer_name'] . ' ' . $res['customer_name_2']);
            $detailed_items[] = $item;
        }
    }

    echo json_encode(['status' => 'success', 'data' => $detailed_items]);
    exit();
}
?>