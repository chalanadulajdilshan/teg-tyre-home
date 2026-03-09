<?php

include '../../class/include.php';
header('Content-Type: application/json; charset=UTF8');

// Get customers who have DAG items
if (isset($_POST['get_customers_with_dags'])) {
    $DAG_CUSTOMER = new DagCustomer(NULL);
    $customers = $DAG_CUSTOMER->getCustomersWithDags();
    echo json_encode(['status' => 'success', 'data' => $customers]);
    exit();
}

// Search customers
if (isset($_POST['search_customer'])) {
    $keyword = $_POST['keyword'] ?? '';
    $db = Database::getInstance();
    $keyword = mysqli_real_escape_string($db->DB_CON, $keyword);

    $query = "SELECT DISTINCT c.id, c.code, c.name, c.name_2,
                     COUNT(dc.id) as dag_count
              FROM `customer_master` c 
              INNER JOIN `dag_customers` dc ON c.id = dc.customer_id 
              WHERE c.name LIKE '%$keyword%' 
                 OR c.code LIKE '%$keyword%'
                 OR c.name_2 LIKE '%$keyword%'
              GROUP BY c.id
              ORDER BY c.name ASC 
              LIMIT 20";

    $result = $db->readQuery($query);
    $customers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['full_name'] = trim($row['name'] . ' ' . ($row['name_2'] ?? ''));
        $customers[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $customers]);
    exit();
}

// Get DAG items by customer (includes company name)
if (isset($_POST['get_customer_dags'])) {
    $customer_id = (int) $_POST['customer_id'];
    $invoiced = isset($_POST['invoiced']) ? (int) $_POST['invoiced'] : null;
    $DAG_CUSTOMER = new DagCustomer(NULL);
    $dags = $DAG_CUSTOMER->getByCustomerId($customer_id, $invoiced);
    echo json_encode(['status' => 'success', 'data' => $dags]);
    exit();
}

// Save invoice (update price/discount/total + set is_invoiced=1)
if (isset($_POST['save_invoice'])) {
    $items = $_POST['items'] ?? [];

    if (is_string($items)) {
        $items = json_decode($items, true);
    }

    if (empty($items)) {
        echo json_encode(['status' => 'error', 'message' => 'No items to save.']);
        exit();
    }

    $success = true;

    foreach ($items as $item) {
        $dag_id = (int) $item['dag_id'];
        $cost = floatval($item['cost'] ?? 0);
        $price = floatval($item['price'] ?? 0);
        $discount = floatval($item['discount'] ?? 0);
        $total = floatval($item['total'] ?? 0);
        $issued_date = !empty($item['issued_date']) ? $item['issued_date'] : null;

        $DAG_CUSTOMER = new DagCustomer($dag_id);
        if ($DAG_CUSTOMER->id) {
            $DAG_CUSTOMER->cost = $cost;
            $DAG_CUSTOMER->price = $price;
            $DAG_CUSTOMER->discount = $discount;
            $DAG_CUSTOMER->total = $total;
            $DAG_CUSTOMER->issued_date = $issued_date;
            $result = $DAG_CUSTOMER->updateInvoice();
            if (!$result) {
                $success = false;
            }
        }
    }

    if ($success) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save some items.']);
    }
    exit();
}

// Delete invoice (clear price/discount/total and set is_invoiced=0 for a customer)
if (isset($_POST['delete_invoice'])) {
    $customer_id = (int) $_POST['customer_id'];
    $DAG_CUSTOMER = new DagCustomer(NULL);
    $dags = $DAG_CUSTOMER->getByCustomerId($customer_id);

    $success = true;
    foreach ($dags as $dag) {
        $d = new DagCustomer($dag['id']);
        if ($d->id) {
            $result = $d->clearInvoice();
            if (!$result) {
                $success = false;
            }
        }
    }

    if ($success) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete invoice.']);
    }
    exit();
}

// Cancel invoice (set is_cancelled=1, keep data for records)
if (isset($_POST['cancel_invoice'])) {
    $customer_id = (int) $_POST['customer_id'];
    $DAG_CUSTOMER = new DagCustomer(NULL);
    // Get only active invoiced items (not already cancelled)
    $dags = $DAG_CUSTOMER->getByCustomerId($customer_id, 1);

    $success = true;
    foreach ($dags as $dag) {
        $d = new DagCustomer($dag['id']);
        if ($d->id) {
            $result = $d->cancelInvoice();
            if (!$result) {
                $success = false;
            }
        }
    }

    if ($success) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to cancel invoice.']);
    }
    exit();
}

// Search invoiced DAG items (invoiced=1, includes cancelled)
if (isset($_POST['search_dag_invoice'])) {
    $keyword = $_POST['keyword'] ?? '';
    $DAG_CUSTOMER = new DagCustomer(NULL);
    $dags = $DAG_CUSTOMER->searchForInvoice($keyword);
    echo json_encode(['status' => 'success', 'data' => $dags]);
    exit();
}
