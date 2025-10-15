<?php

include '../../class/include.php';
header('Content-Type: application/json; charset=UTF8');


if (isset($_POST['action']) && $_POST['action'] == 'check_invoice_id') {


    $invoice_no = trim($_POST['invoice_no']);
    $SALES_INVOICE = new SalesInvoice(NULL);
    $res = $SALES_INVOICE->checkInvoiceIdExist($invoice_no);

    // Send JSON response
    echo json_encode(['exists' => $res]);
    exit();
}


// Create a new invoice
if (isset($_POST['create'])) {

    $invoiceId = $_POST['invoice_no'];
    $items = json_decode($_POST['items'], true); // array of items 

    $paymentType = $_POST['payment_type'];



    $totalSubTotal = 0;
    $totalDiscount = 0;
    $final_cost = 0;

    // Calculate subtotal and discount
    foreach ($items as $item) {
        $price = floatval($item['price']); // Use list_price for subtotal calculation to match interface
        $qty = floatval($item['qty']);

        if (isset($item['discount'])) {
            $discount_percentage = (float)$item['discount'];
        } else {
            $discount_percentage = 0;
        } // item-wise discount percentage


        //GET ARN ID BY ARN NO
        $ARN_MASTER = new ArnMaster(NULL);
        $arn_id = $ARN_MASTER->getArnIdByArnNo($item['arn_no']);

        $ITEM_MASTER = new ItemMaster($item['item_id']);


        if (substr($item['code'], 0, 2) !== 'SI') {
            $ARN_ITEM = new ArnItem(NULL);
            $cost = $ARN_ITEM->getArnCostByArnId($arn_id);
            $final_cost_item = $cost * $item['qty'];
            $final_cost += $final_cost_item;
        } else {
            $SERVICE_ITEM = new ServiceItem($item['item_id']);
            $final_cost_item = $SERVICE_ITEM->cost * $item['service_qty'];
            $final_cost += $final_cost_item;

            $available_qty = $SERVICE_ITEM->qty - $item['service_qty'];
            $SERVICE_ITEM->qty = $available_qty;
            $SERVICE_ITEM->update();
        }

        $itemTotal = $price * $qty;
        $discount_amount = $itemTotal * $discount_percentage / 100;
        $totalSubTotal += $itemTotal;
        $totalDiscount += $discount_amount;
    }
    $netTotal = $totalSubTotal - $totalDiscount;

    $USER = new User($_SESSION['id']);
    $COMPANY_PROFILE = new CompanyProfile($USER->company_id);




    // VAT calculation - only if company has VAT enabled
    $tax = 0;
    if ($COMPANY_PROFILE->is_vat == 1) {
        $tax = round(($netTotal * $COMPANY_PROFILE->vat_percentage) / 100, 2);
    }

    // Grand total = net total + VAT
    $grandTotal = $netTotal + $tax;

    // Create invoice
    $SALES_INVOICE = new SalesInvoice(NULL);
    $CUSTOMER_MASTER = new CustomerMaster(NULL);

    $SALES_INVOICE->invoice_no = $invoiceId;
    $SALES_INVOICE->invoice_type = 'INV';
    $SALES_INVOICE->invoice_date = $_POST['invoice_date'];
    $SALES_INVOICE->company_id = $_POST['company_id'];
    $SALES_INVOICE->customer_id = $_POST['customer_id'];
    $SALES_INVOICE->customer_name = ucwords(strtolower(trim($_POST['customer_name'])));
    $SALES_INVOICE->customer_mobile = $_POST['customer_mobile'];
    $SALES_INVOICE->customer_address = ucwords(strtolower(trim($_POST['customer_address'])));
    $SALES_INVOICE->recommended_person = isset($_POST['recommended_person']) ? ucwords(strtolower(trim($_POST['recommended_person']))) : null;
    $SALES_INVOICE->department_id = $_POST['department_id'];
    $SALES_INVOICE->sale_type = $_POST['sales_type'];
    $SALES_INVOICE->final_cost = $final_cost;

    $SALES_INVOICE->payment_type = $paymentType;
    $SALES_INVOICE->sub_total = $totalSubTotal;
    $SALES_INVOICE->discount = $totalDiscount;
    $SALES_INVOICE->tax = $tax;
    $SALES_INVOICE->grand_total = $grandTotal;
    $SALES_INVOICE->outstanding_settle_amount = $_POST['paidAmount'];
    $SALES_INVOICE->remark = !empty($_POST['remark']) ? $_POST['remark'] : null;

    if ($paymentType == 2 && !empty($_POST['credit_period'])) {
        $SALES_INVOICE->credit_period = $_POST['credit_period'];
        $CREDIT_PERIOD_OBJ = new CreditPeriod($_POST['credit_period']);
        if (isset($CREDIT_PERIOD_OBJ->days)) {
            $days = $CREDIT_PERIOD_OBJ->days;
            $due_date = date('Y-m-d', strtotime($_POST['invoice_date'] . ' + ' . $days . ' days'));
            $SALES_INVOICE->due_date = $due_date;
        } else {
            // Handle error: invalid credit period id
            echo json_encode(["status" => 'error', "message" => "Invalid credit period selected."]);
            exit();
        }
    }

    $invoiceResult = $SALES_INVOICE->create();

    if ($paymentType == 2) {
        $CUSTOMER_MASTER->updateCustomerOutstanding($_POST['customer_id'], $grandTotal, true);
    }

    $DOCUMENT_TRACKING = new DocumentTracking(null);

    if ($paymentType == 1) {
        $DOCUMENT_TRACKING->incrementDocumentId('cash');
    } else if ($paymentType == 2) {
        $DOCUMENT_TRACKING->incrementDocumentId('credit');
    } else {

        $DOCUMENT_TRACKING->incrementDocumentId('invoice');
    }


    if ($invoiceResult) {
        $invoiceTableId = $invoiceResult;

        foreach ($items as $item) {

            $item_discount_percentage = isset($item['discount']) ? $item['discount'] : 0;

            $ITEM_MASTER = new ItemMaster($item['item_id']);

            //GET ARN ID BY ARN NO FIRST
            $ARN_MASTER = new ArnMaster(NULL);
            $arn_id = $ARN_MASTER->getArnIdByArnNo($item['arn_no']);
            
            // Get the correct department_id for this ARN before saving item
            $db = new Database();
            $deptQuery = "SELECT department_id FROM stock_item_tmp WHERE arn_id = '{$arn_id}' AND item_id = '{$item['item_id']}' LIMIT 1";
            $deptResult = $db->readQuery($deptQuery);
            $correctDepartmentId = $_POST['department_id']; // fallback to form department
            
            if ($deptRow = mysqli_fetch_assoc($deptResult)) {
                $correctDepartmentId = $deptRow['department_id'];
            }

            $SALES_ITEM = new SalesInvoiceItem(NULL);

            $SALES_ITEM->invoice_id = $invoiceTableId;

            if (substr($item['code'], 0, 2) !== 'SI') {
                // Regular item
                $SALES_ITEM->item_code = $item['item_id'];
                $SALES_ITEM->quantity = $item['qty'];
                $qty_for_total = $item['qty'];
                $qty_for_stock = $item['qty']; // Use regular qty for stock management
            } else {
                // Service item - main qty should always be 1, but use service_qty for calculations
                $SALES_ITEM->service_item_code = $item['item_id'];
                $SALES_ITEM->quantity = 1; // Always 1 for service items in the invoice table
                $qty_for_total = $item['service_qty']; // Use service_qty for price calculations
                $qty_for_stock = $item['service_qty']; // Use service_qty for stock management
            }

            $item_discount_amount = ($item['price'] * $qty_for_total) * $item_discount_percentage / 100;

            // Store item name with ARN ID and department for cancellation tracking
            $SALES_ITEM->item_name = $item['name'] . '|ARN:' . $arn_id . '|DEPT:' . $correctDepartmentId;
            $SALES_ITEM->list_price = $item['price']; // Save the original list price
            $SALES_ITEM->price = $item['selling_price']; // Save the actual selling price (price after discount per unit)
            $SALES_ITEM->cost = $item['cost']; // Set the cost field
            $SALES_ITEM->discount = $item_discount_amount;
            $SALES_ITEM->total = ($item['selling_price'] * $qty_for_total);
            $SALES_ITEM->vehicle_no = isset($item['vehicle_no']) ? $item['vehicle_no'] : '';
            $SALES_ITEM->current_km = isset($item['current_km']) ? $item['current_km'] : '';
            $SALES_ITEM->next_service_date = (isset($item['next_service_days']) && !empty($item['next_service_days']) && intval($item['next_service_days']) > 0) ? date('Y-m-d', strtotime($SALES_INVOICE->invoice_date . ' + ' . $item['next_service_days'] . ' days')) : null;
            $SALES_ITEM->created_at = date("Y-m-d H:i:s");
            $SALES_ITEM->create();

            //stock master update quantity
            $STOCK_MASTER = new StockMaster(NULL);
            $currentQty = $STOCK_MASTER->getAvailableQuantity($_POST['department_id'], $item['item_id']);
            $newQty = $currentQty - $qty_for_stock; // Use the correct quantity for stock management
            $STOCK_MASTER->quantity = $newQty;
            $STOCK_MASTER->updateQtyByItemAndDepartment($_POST['department_id'], $item['item_id'], $newQty);

            // Update stock transaction with ARN reference if available
            $STOCK_TRANSACTION = new StockTransaction(NULL);
            $STOCK_TRANSACTION->item_id = $item['item_id'];

            // Update stock_item_tmp for ARN-based inventory
            $STOCK_ITEM_TMP = new StockItemTmp(NULL);
            // Use negative qty to reduce stock
            $qtyToDeduct = -abs($qty_for_stock); // Use correct quantity for stock deduction
            
            $STOCK_ITEM_TMP->updateQtyByArnId(
                $arn_id,
                $item['item_id'],
                $correctDepartmentId, // Use the correct department for this ARN
                $qtyToDeduct
            );


            //stock transaction table update
            $STOCK_TRANSACTION->type = 4; // get this id from stock adjustment type table PK
            $STOCK_TRANSACTION->date = date("Y-m-d");
            $STOCK_TRANSACTION->qty_in = 0;
            $STOCK_TRANSACTION->qty_out = $qty_for_stock; // Use correct quantity for transaction record
            $STOCK_TRANSACTION->remark = "INVOICE #$invoiceId " . (!empty($item['arn_id']) ? "(ARN: {$item['arn']}) " : "") . "Issued " . date("Y-m-d H:i:s");
            $STOCK_TRANSACTION->created_at = date("Y-m-d H:i:s");
            $STOCK_TRANSACTION->create();

            if ($paymentType == 1) {
                $payments = json_decode($_POST['payments'], true); // decode JSON â†’ array

                if (is_array($payments)) {
                    foreach ($payments as $payment) {
                        $INVOICE_PAYMENT = new InvoicePayment(NULL);
                        $INVOICE_PAYMENT->invoice_id  = $invoiceTableId;
                        $INVOICE_PAYMENT->method_id   = $payment['method_id'];
                        $INVOICE_PAYMENT->amount      = $payment['amount'];
                        $INVOICE_PAYMENT->reference_no = $payment['reference_no'] ?? null;
                        $INVOICE_PAYMENT->bank_name    = $pssayment['bank_name'] ?? null;
                        $INVOICE_PAYMENT->cheque_date  = $payment['cheque_date'] ?? null;

                        $res = $INVOICE_PAYMENT->create();
                    }
                }
            }
            //audit log 
            $AUDIT_LOG = new AuditLog(NUll);
            $AUDIT_LOG->ref_id = $invoiceTableId;
            $AUDIT_LOG->ref_code = $_POST['invoice_no'];
            $AUDIT_LOG->action = 'CREATE';
            $AUDIT_LOG->description = 'CREATE INVOICE NO #' . $invoiceTableId;
            $AUDIT_LOG->user_id = $_SESSION['id'];
            $AUDIT_LOG->created_at = date("Y-m-d H:i:s");
            $AUDIT_LOG->create();
        }

        echo json_encode([
            "status" => 'success',
            "invoice_id" => $invoiceTableId,
            "sub_total" => $totalSubTotal,
            "discount" => $totalDiscount,
            "vat" => $tax,
            "grand_total" => $grandTotal
        ]);
        exit();
    } else {
        echo json_encode(["status" => 'error']);
        exit();
    }
}


// Update invoice details
if (isset($_POST['update'])) {
    $invoiceId = $_POST['invoice_id']; // Retrieve invoice ID

    // Create SalesInvoice object and load the data by ID
    $SALES_INVOICE = new SalesInvoice($invoiceId);

    // Update invoice details
    $SALES_INVOICE->invoice_date = $_POST['invoice_date']; // You can update the date or other details here
    $SALES_INVOICE->company_id = $_POST['company_id'];
    $SALES_INVOICE->customer_id = $_POST['customer_id'];
    $SALES_INVOICE->customer_name = ucwords(strtolower(trim($_POST['customer_name'])));
    $SALES_INVOICE->customer_mobile = $_POST['customer_mobile'];
    $SALES_INVOICE->customer_address = ucwords(strtolower(trim($_POST['customer_address'])));
    $SALES_INVOICE->recommended_person = isset($_POST['recommended_person']) ? ucwords(strtolower(trim($_POST['recommended_person']))) : null;


    // Attempt to update the invoice
    $result = $SALES_INVOICE->update();

    if ($result) {
        $result = [
            "status" => 'success'
        ];
        echo json_encode($result);
        exit();
    } else {
        $result = [
            "status" => 'error'
        ];
        echo json_encode($result);
        exit();
    }
}

if (isset($_POST['filter'])) {

    $SALES_INVOICE = new SalesInvoice();
    $response = $SALES_INVOICE->fetchInvoicesForDataTable($_REQUEST);


    echo json_encode($response);
    exit;
}

if (isset($_POST['get_by_id'])) {

    $SALES_INVOICE = new SalesInvoice();
    $response = $SALES_INVOICE->getByID($_POST['id']);

    $CUSTOMER_MASTER = new CustomerMaster($response['customer_id']);
    $response['customer_code'] = $CUSTOMER_MASTER->code;
    $response['customer_name'] = $CUSTOMER_MASTER->name;
    $response['customer_address'] = $CUSTOMER_MASTER->address;
    $response['customer_mobile'] = $CUSTOMER_MASTER->mobile_number;
    $response['recommended_person'] = $response['recommended_person'] ?? null;

    echo json_encode($response);
    exit;
}



// Delete invoice
if (isset($_POST['delete']) && isset($_POST['id'])) {
    $invoice = new SalesInvoice($_POST['id']);
    $result = $invoice->delete(); // Make sure this method exists in your class

    if ($result) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}



if (isset($_POST['action']) && $_POST['action'] == 'latest') {
    $SALES_INVOICE = new SalesInvoice();
    $invoices = $SALES_INVOICE->latest();

    echo json_encode(["data" => $invoices]);
    exit();
}


if (isset($_POST['action']) && $_POST['action'] == 'search') {
    $SALES_INVOICE = new SalesInvoice();
    $invoices = $SALES_INVOICE->search($_POST['q']);

    echo json_encode(["data" => $invoices]);
    exit();
}


// Handle cancel invoice action
// Check invoice status
if (isset($_POST['action']) && $_POST['action'] == 'check_status') {
    $invoiceId = $_POST['id'];
    $SALES_INVOICE = new SalesInvoice($invoiceId);
    echo json_encode(['is_cancelled' => ($SALES_INVOICE->is_cancel == 1)]);
    exit();
}

// Cancel invoice
if (isset($_POST['action']) && $_POST['action'] == 'cancel') {


    $invoiceId = $_POST['id'];
    $arnIds = isset($_POST['arnIds']) ? $_POST['arnIds'] : [];

    $SALES_INVOICE = new SalesInvoice($invoiceId);




    if ($SALES_INVOICE->is_cancel == 1) {
        echo json_encode(['status' => 'already_cancelled']);
        exit();
    }
    $result = $SALES_INVOICE->cancel();

    if ($result) {
        $STOCK_TRANSACTION = new StockTransaction(NULL);
        $SALES_INVOICE_ITEM = new SalesInvoiceItem(NULL);
        $STOCK_ITEM_TMP = new StockItemTmp(NULL);

        $items = $SALES_INVOICE_ITEM->getItemsByInvoiceId($invoiceId);


        foreach ($items as $item) {
            
            // Extract ARN ID and Department from item_name
            $arnId = null;
            $arnDepartmentId = $SALES_INVOICE->department_id; // fallback to invoice department
            
            if (strpos($item['item_name'], '|ARN:') !== false) {
                preg_match('/\|ARN:(\d+)\|DEPT:(\d+)/', $item['item_name'], $matches);
                if (isset($matches[1]) && isset($matches[2])) {
                    $arnId = (int)$matches[1];
                    $arnDepartmentId = (int)$matches[2];
                }
            }
            
            if ($item['item_code'] != 0) {
                $STOCK_MASTER = new StockMaster(NULL);
                
                // Add quantity back to the ARN's original department, not invoice department
                $currentQty = $STOCK_MASTER->getAvailableQuantity($arnDepartmentId, $item['item_code']);
                $newQty = $currentQty + $item['quantity'];
                $STOCK_MASTER->quantity = $newQty;
                $STOCK_MASTER->updateQtyByItemAndDepartment($arnDepartmentId, $item['item_code'], $newQty);

                // Update stock transaction with ARN reference if available
                $STOCK_TRANSACTION->item_id = $item['item_code'];
                $STOCK_TRANSACTION->type = 14; // get this id from stock adjustment type table PK
                $STOCK_TRANSACTION->date = date("Y-m-d");
                $STOCK_TRANSACTION->qty_in = $item['quantity'];
                $STOCK_TRANSACTION->qty_out = 0;
                $STOCK_TRANSACTION->remark = "INVOICE CANCELLED #$invoiceId " . ($arnId ? "(ARN: {$arnId}) " : "") . "Cancelled " . date("Y-m-d H:i:s");
                $STOCK_TRANSACTION->created_at = date("Y-m-d H:i:s");
                $STOCK_TRANSACTION->create();

                // Add back quantity to the specific ARN in its original department
                if ($arnId) {
                    $qtyToAdd = abs($item['quantity']);
                    $STOCK_ITEM_TMP->updateQtyByArnId($arnId, $item['item_code'], $arnDepartmentId, $qtyToAdd);
                }
             
            }else{
                $SERVICE_ITEM = new ServiceItem($item['service_item_code']);
                $currentQty = $SERVICE_ITEM->qty;
                $newQty = $currentQty + $item['quantity'];
                $SERVICE_ITEM->qty = $newQty;
                $SERVICE_ITEM->update();
            }

        }


        //audit log
        $AUDIT_LOG = new AuditLog(NUll);
        $AUDIT_LOG->ref_id = $invoiceId;
        $AUDIT_LOG->ref_code = $invoiceId;
        $AUDIT_LOG->action = 'CANCEL';
        $AUDIT_LOG->description = 'CANCEL INVOICE NO #' . $SALES_INVOICE->invoice_no;
        $AUDIT_LOG->user_id = $_SESSION['id'];
        $AUDIT_LOG->created_at = date("Y-m-d H:i:s");
        $result =   $AUDIT_LOG->create();

        if ($result) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
    }
}
