<?php

include '../../class/include.php';
header('Content-Type: application/json; charset=UTF8');

// Create a new Dag
if (isset($_POST['create'])) {

    $DAG = new DAG(NULL);

    // Set DAG master fields
    $DAG->ref_no = $_POST['ref_no'];
    $DAG->job_number = $_POST['job_number'];
    $DAG->department_id = $_POST['department_id'];
    $DAG->customer_id = $_POST['customer_id'];
    $DAG->received_date = $_POST['received_date'];
    $DAG->customer_request_date = $_POST['customer_request_date'];
    $DAG->dag_company_id = $_POST['dag_company_id'];
    $DAG->delivery_date = $_POST['delivery_date'];
    $DAG->company_issued_date = $_POST['company_issued_date'];
    $DAG->company_delivery_date = $_POST['company_delivery_date'];
    $DAG->remark = $_POST['remark'];
    $DAG->receipt_no = $_POST['receipt_no'];
    $DAG->status = $_POST['dag_status'];

    $dag_id = $DAG->create();

    if ($dag_id) {
        // Insert DAG items
        if (isset($_POST['dag_items'])) {
            $items = json_decode($_POST['dag_items'], true);

            foreach ($items as $item) {
                // Collect all serial numbers from the 8 columns
                $serial_numbers = [];
                for ($i = 1; $i <= 8; $i++) {
                    $serial_key = 'serial_num' . $i;
                    if (!empty($item[$serial_key])) {
                        $serial_numbers[] = trim($item[$serial_key]);
                    }
                }
                
                // Create separate row for each serial number
                foreach ($serial_numbers as $serial_number) {
                    $DAG_ITEM = new DagItem(NULL);
                    $DAG_ITEM->dag_id = $dag_id;
                    $DAG_ITEM->vehicle_no = strtoupper($item['vehicle_no']);
                    $DAG_ITEM->belt_id = $item['belt_id'];
                    $DAG_ITEM->size_id = $item['size_id'];
                    $DAG_ITEM->serial_number = $serial_number;
                    $DAG_ITEM->qty = 1; // Always set qty to 1
                    $DAG_ITEM->is_invoiced = 0; // Default not invoiced
                    $DAG_ITEM->create();
                }
                
                // If no serial numbers provided, create one row with empty serial number
                if (empty($serial_numbers)) {
                    $DAG_ITEM = new DagItem(NULL);
                    $DAG_ITEM->dag_id = $dag_id;
                    $DAG_ITEM->vehicle_no = strtoupper($item['vehicle_no']);
                    $DAG_ITEM->belt_id = $item['belt_id'];
                    $DAG_ITEM->size_id = $item['size_id'];
                    $DAG_ITEM->serial_number = '';
                    $DAG_ITEM->qty = 1; // Always set qty to 1
                    $DAG_ITEM->is_invoiced = 0; // Default not invoiced
                    $DAG_ITEM->create();
                }
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
    $DAG->job_number = $_POST['job_number'];
    $DAG->department_id = $_POST['department_id'];
    $DAG->customer_id = $_POST['customer_id'];
    $DAG->received_date = $_POST['received_date'];
    $DAG->customer_request_date = $_POST['customer_request_date'];
    $DAG->dag_company_id = $_POST['dag_company_id'];
    $DAG->delivery_date = $_POST['delivery_date'];
    $DAG->company_issued_date = $_POST['company_issued_date'];
    $DAG->company_delivery_date = $_POST['company_delivery_date'];
    $DAG->remark = $_POST['remark'];
    $DAG->receipt_no = $_POST['receipt_no'];
    $DAG->status = $_POST['dag_status'];

    if ($DAG->update()) {
        // Delete all old DAG items
        $DAG_ITEM = new DagItem(null);
        $DAG_ITEM->deleteDagItemByItemId($DAG->id);

        // Add new DAG items
        if (isset($_POST['dag_items'])) {
            $items = json_decode($_POST['dag_items'], true);
            foreach ($items as $item) {
                // Collect all serial numbers from the 8 columns
                $serial_numbers = [];
                for ($i = 1; $i <= 8; $i++) {
                    $serial_key = 'serial_num' . $i;
                    if (!empty($item[$serial_key])) {
                        $serial_numbers[] = trim($item[$serial_key]);
                    }
                }
                
                // Create separate row for each serial number
                foreach ($serial_numbers as $serial_number) {
                    $DAG_ITEM = new DagItem(null);
                    $DAG_ITEM->dag_id = $DAG->id;
                    $DAG_ITEM->vehicle_no = $item['vehicle_no'];
                    $DAG_ITEM->belt_id = $item['belt_id'];
                    $DAG_ITEM->size_id = $item['size_id'];
                    $DAG_ITEM->serial_number = $serial_number;
                    $DAG_ITEM->qty = 1; // Always set qty to 1
                    $DAG_ITEM->is_invoiced = 0; // Default not invoiced
                    $DAG_ITEM->create();
                }
                
                // If no serial numbers provided, create one row with empty serial number
                if (empty($serial_numbers)) {
                    $DAG_ITEM = new DagItem(null);
                    $DAG_ITEM->dag_id = $DAG->id;
                    $DAG_ITEM->vehicle_no = $item['vehicle_no'];
                    $DAG_ITEM->belt_id = $item['belt_id'];
                    $DAG_ITEM->size_id = $item['size_id'];
                    $DAG_ITEM->serial_number = '';
                    $DAG_ITEM->qty = 1; // Always set qty to 1
                    $DAG_ITEM->is_invoiced = 0; // Default not invoiced
                    $DAG_ITEM->create();
                }
            }
        }

        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Update failed"]);
    }
    exit();
}



if (isset($_POST['dag_id'])) {
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
?>