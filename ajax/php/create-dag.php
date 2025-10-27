<?php

include '../../class/include.php';
header('Content-Type: application/json; charset=UTF8');

// Create a new Dag
if (isset($_POST['create'])) {

    $DAG = new DAG(NULL);

    // Set DAG master fields
    $DAG->ref_no = $_POST['ref_no'];
    $DAG->department_id = $_POST['department_id'];
    $DAG->customer_id = $_POST['customer_id'];
    $DAG->received_date = $_POST['received_date'];
    $DAG->delivery_date = $_POST['delivery_date'];
    $DAG->customer_request_date = $_POST['customer_request_date'];
    $DAG->vehicle_no = $_POST['vehicle_no'];
    $DAG->remark = $_POST['remark'];

    $dag_id = $DAG->create();

    if ($dag_id) {
        // Insert DAG items
        if (isset($_POST['dag_items'])) {
            $items = json_decode($_POST['dag_items'], true);

            foreach ($items as $item) {
                $DAG_ITEM = new DagItem(NULL);
                $DAG_ITEM->dag_id = $dag_id;
                $DAG_ITEM->belt_id = $item['belt_id'];
                $DAG_ITEM->size_id = $item['size_id'];
                $DAG_ITEM->serial_number = $item['serial_num1'];
                $DAG_ITEM->qty = 1; // Always set qty to 1
                $DAG_ITEM->is_invoiced = 0; // Default not invoiced
                $DAG_ITEM->dag_company_id = $item['dag_company_id'];
                $DAG_ITEM->company_issued_date = $item['company_issued_date'];
                $DAG_ITEM->company_delivery_date = $item['company_delivery_date'];
                $DAG_ITEM->receipt_no = $item['receipt_no'];
                $DAG_ITEM->brand_id = isset($item['brand_id']) ? $item['brand_id'] : null;
                $DAG_ITEM->job_number = $item['job_number'];
                $DAG_ITEM->status = $item['status'];
                $DAG_ITEM->create();
            }
        }

        if ($dag_id) {
            echo json_encode([
                'status' => 'success',
                'id' => $dag_id // Return the newly created ID
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to create DAG.'
            ]);
        }
        exit;



    } else {
        echo json_encode(["status" => "error"]);
        exit();
    }
}


// Update Dag details
if (isset($_POST['update'])) {
    $DAG = new DAG($_POST['dag_id']); // use correct key 'dag_id' from JS

    // Update DAG master fields
    $DAG->ref_no = $_POST['ref_no'];
    $DAG->department_id = $_POST['department_id'];
    $DAG->customer_id = $_POST['customer_id'];
    $DAG->received_date = $_POST['received_date'];
    $DAG->delivery_date = $_POST['delivery_date'];
    $DAG->customer_request_date = $_POST['customer_request_date'];
    $DAG->vehicle_no = $_POST['vehicle_no'];
    $DAG->remark = $_POST['remark'];

    if ($DAG->update()) {
        // Delete all old DAG items
        $DAG_ITEM = new DagItem(null);
        $DAG_ITEM->deleteDagItemByItemId($DAG->id);

        // Add new DAG items
        if (isset($_POST['dag_items'])) {
            $items = json_decode($_POST['dag_items'], true);
            foreach ($items as $item) {
                $DAG_ITEM = new DagItem(null);
                $DAG_ITEM->dag_id = $DAG->id;
                $DAG_ITEM->belt_id = $item['belt_id'];
                $DAG_ITEM->size_id = $item['size_id'];
                $DAG_ITEM->serial_number = $item['serial_num1'];
                $DAG_ITEM->qty = 1; // Always set qty to 1
                $DAG_ITEM->is_invoiced = 0; // Default not invoiced
                $DAG_ITEM->dag_company_id = $item['dag_company_id'];
                $DAG_ITEM->company_issued_date = $item['company_issued_date'];
                $DAG_ITEM->company_delivery_date = $item['company_delivery_date'];
                $DAG_ITEM->receipt_no = $item['receipt_no'];
                $DAG_ITEM->brand_id = isset($item['brand_id']) ? $item['brand_id'] : null;
                $DAG_ITEM->job_number = $item['job_number'];
                $DAG_ITEM->status = $item['status'];
                $DAG_ITEM->create();
            }
        }

        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Update failed"]);
    }
    exit();
}



if (isset($_POST['dag_id']) && empty($_POST['delete'])) {
    $dag_id = $_POST['dag_id'];

    try {
        $DAG_ITEM = new DagItem(null);
        // Get only non-invoiced items for sales invoice
        if (isset($_POST['for_invoice']) && $_POST['for_invoice'] == true) {
            // Try to get non-invoiced items, fallback to all items if column doesn't exist
            try {
                $items = $DAG_ITEM->getNonInvoicedByDagId($dag_id);
            } catch (Exception $e) {
                // If is_invoiced column doesn't exist, get all items
                $items = $DAG_ITEM->getByValuesDagId($dag_id);
            }
        } else {
            // Get all items for other purposes (like DAG management)
            $items = $DAG_ITEM->getByValuesDagId($dag_id);
        }

        echo json_encode([
            "status" => "success",
            "data" => $items
        ]);
    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to fetch DAG items: " . $e->getMessage()
        ]);
    }
    exit();
}

// Delete Dag
if (isset($_POST['delete'])) {
    $dagId = isset($_POST['dag_id']) ? (int) $_POST['dag_id'] : 0;

    if ($dagId <= 0) {
        echo json_encode(["status" => "error", "message" => "Invalid DAG ID"]);
        exit();
    }

    $DAG = new DAG($dagId);

    if (!$DAG->id) {
        echo json_encode(["status" => "error", "message" => "DAG not found"]);
        exit();
    }

    // First delete associated dag items
    $DAG_ITEM = new DagItem(null);
    $DAG_ITEM->deleteDagItemByItemId($dagId);

    if ($DAG->delete()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete DAG"]);
    }
    exit();
}
?>