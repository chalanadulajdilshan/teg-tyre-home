<!doctype html>
<?php
include 'class/include.php';
include './auth.php';

// Check if ID is provided in the URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Initialize empty receipt data
$receipt = null;
$customer = null;
$paymentMethods = [];

// If ID is provided, load the receipt data
if ($id > 0) {
    $PAYMENT_RECEIPT = new PaymentReceipt($id);
    if ($PAYMENT_RECEIPT->id) {
        $receipt = [
            'id' => $PAYMENT_RECEIPT->id,
            'receipt_no' => $PAYMENT_RECEIPT->receipt_no,
            'customer_id' => $PAYMENT_RECEIPT->customer_id,
            'entry_date' => $PAYMENT_RECEIPT->entry_date,
            'amount_paid' => $PAYMENT_RECEIPT->amount_paid,
            'remark' => $PAYMENT_RECEIPT->remark,
            'created_at' => $PAYMENT_RECEIPT->created_at
        ];
        
        // Load customer details
        $CUSTOMER = new CustomerMaster($PAYMENT_RECEIPT->customer_id);
        if ($CUSTOMER->id) {
            $customer = [
                'id' => $CUSTOMER->id,
                'code' => $CUSTOMER->code,
                'name' => $CUSTOMER->name,
                'address' => $CUSTOMER->address,
                'email' => $CUSTOMER->email,
                'mobile' => $CUSTOMER->mobile_number
            ];
        }
        
        // Load payment methods
        $PAYMENT_METHODS = new PaymentReceiptMethod();
        $paymentMethods = $PAYMENT_METHODS->getByReceiptId($id);
    }
}

// If no receipt found with the given ID, redirect to the list
if ($id > 0 && empty($receipt)) {
    header('Location: payment-receipt.php');
    exit();
}

?>

<html lang="en">

<head>

    <meta charset="utf-8" />
    <title> Manage Payment Receipt | <?php echo $COMPANY_PROFILE_DETAILS->name ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="<?php echo $COMPANY_PROFILE_DETAILS->name ?>" name="author" />
    <!-- include main CSS -->
    <?php include 'main-css.php' ?>

    <style>
        .btn-danger {
            color: #fff;
            background-color: #f46a6a !important;
            border-color: #f46a6a;
            padding: 6px !important;
            margin: 4px !important;
        }
    </style>
</head>

<body data-layout="horizontal" data-topbar="colored" class="someBlock">


    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php include 'navigation.php' ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <div class="row mb-4">
                        <div class="col-md-8 d-flex align-items-center flex-wrap gap-2">
                            <a href="#" class="btn btn-success" id="new">
                                <i class="uil uil-plus me-1"></i> New
                            </a>

                            <?php if ($PERMISSIONS['add_page']): ?>
                                <a href="#" class="btn btn-primary" id="create">
                                    <i class="uil uil-save me-1"></i> Save
                                </a>
                            <?php endif; ?>

                        </div>

                        <div class="col-md-4 text-md-end text-start mt-3 mt-md-0">
                            <ol class="breadcrumb m-0 justify-content-md-end">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                <li class="breadcrumb-item active"> Manage Payment Receipt </li>
                            </ol>
                        </div>
                    </div>
                    <!--- Hidden Values -->


                    <!-- end page title -->
                    <section>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="p-4">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar-xs">
                                                    <div
                                                        class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                        01
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <h5 class="font-size-16 mb-1">Manage Payment Receipt </h5>
                                                <p class="text-muted text-truncate mb-0">Fill all information below to
                                                    Manage Payment Receipt </p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <i class="mdi mdi-chevron-up accor-down-icon font-size-24"></i>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="p-4">
                                        <form id="form-data" autocomplete="off">
                                            <div class="row">
                                                <!-- hidden customer id -->
                                                <input type="hidden" id="customer_id">


                                                <!-- Hidden receipt ID -->
                                                <input type="hidden" name="id" value="<?php echo $receipt ? $receipt['id'] : ''; ?>">
                                                
                                                <div class="col-md-2">
                                                    <label for="code" class="form-label">Receipt No</label>
                                                    <div class="input-group mb-3">
                                                        <input type="text" id="code" name="code"
                                                            value="<?php echo $receipt ? htmlspecialchars($receipt['receipt_no']) : ''; ?>"
                                                            class="form-control" readonly>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <label for="customer_code" class="form-label">Customer Code</label>
                                                    <div class="input-group mb-3">
                                                        <input id="customer_code" name="customer_code" type="text"
                                                            value="<?php echo $customer ? htmlspecialchars($customer['code']) : ''; ?>"
                                                            placeholder="Customer code" class="form-control" readonly>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <label for="customer_name" class="form-label">Customer Name</label>
                                                    <div class="input-group mb-3">
                                                        <input id="customer_name" name="customer_name" type="text"
                                                            value="<?php echo $customer ? htmlspecialchars($customer['name']) : ''; ?>"
                                                            class="form-control" placeholder="Customer name" readonly>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <label for="customer_address" class="form-label">Customer Address</label>
                                                    <div class="input-group mb-3">
                                                        <input id="customer_address" name="customer_address" type="text"
                                                            value="<?php echo $customer ? htmlspecialchars($customer['address']) : ''; ?>"
                                                            class="form-control" placeholder="Customer address" readonly>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <label for="entry_date" class="form-label">Entry Date</label>
                                                    <div class="input-group" id="datepicker2">
                                                        <input type="text" class="form-control date-picker"
                                                            id="entry_date" name="entry_date"
                                                            value="<?php echo $receipt ? htmlspecialchars($receipt['entry_date']) : date('Y-m-d'); ?>">
                                                        <span class="input-group-text"><i class="mdi mdi-calendar"></i></span>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <label for="remark" class="form-label">Remark</label>
                                                    <div class="input-group mb-3">
                                                        <input id="remark" name="remark" type="text"
                                                            value="<?php echo $receipt ? htmlspecialchars($receipt['remark']) : ''; ?>"
                                                            class="form-control" placeholder="Enter remark">
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-3">
                                                    <label for="amount_paid" class="form-label text-primary fw-bold">Total Amount</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control border-primary text-primary" 
                                                            id="amount_paid" name="amount_paid" 
                                                            value="<?php echo $receipt ? htmlspecialchars($receipt['amount_paid']) : '0.00'; ?>"
                                                            readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 d-flex">
                                <div class="card w-100 h-100">
                                    <div class="p-4">
                                        <form id="form-data-cheque" autocomplete="off">
                                            <div class="row">
                                                <div class="row align-items-center mb-3">
                                                    <div class="col-md-6">
                                                        <h5 class="mb-0">Add Cheque Details</h5>
                                                    </div>
                                                    <div class="col-md-6 text-end">
                                                        <div class="d-inline-flex align-items-center">
                                                            <label for="cheque_total"
                                                                class="form-label me-2 mb-0 text-danger"
                                                                style="white-space: nowrap;">Cheque Total:</label>
                                                            <input id="cheque_total" name="cheque_total" type="text"
                                                                placeholder="Cheque Total Amount" class="form-control"
                                                                readonly>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row align-items-end">
                                                    <div class="col-md-2">
                                                        <label for="cheque_no" class="form-label">Cheque
                                                            No</label>
                                                        <div class="input-group">
                                                            <input id="cheque_no" type="text" class="form-control"
                                                                placeholder="No">

                                                        </div>
                                                    </div>

                                                    <div class="col-md-2">
                                                        <label for="cheque_date" class="form-label">Cheque
                                                            Date</label>
                                                        <div class="input-group" id="datepicker2">
                                                            <input type="text" class="form-control date-picker"
                                                                id="cheque_date" name="cheque_date"
                                                                placeholder="Cheque Date">

                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label for="bank_branch" class="form-label">Bank &
                                                            Branch</label>
                                                        <div class="input-group">
                                                            <input type="hidden" id="bank_branch">
                                                            <input id="bank_branch_name" type="text"
                                                                class="form-control" placeholder="Bank & Branch"
                                                                readonly>
                                                            <button class="btn btn-info" type="button"
                                                                data-bs-toggle="modal" data-bs-target="#branch_master">
                                                                <i class="uil uil-search me-1"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <label class="form-label">Amount</label>
                                                        <input type="number" id="amount" class="form-control"
                                                            placeholder="Amount">
                                                    </div>

                                                    <div class="col-md-1">
                                                        <button type="button" class="btn btn-success  "
                                                            id="add_cheque">Add</button>
                                                    </div>
                                                </div>

                                                <!-- Table -->
                                                <div class="table-responsive mt-4">
                                                    <table class="table table-bordered" id="chequeBody">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Cheque No</th>
                                                                <th>Cheque Date</th>
                                                                <th>Bank & Branch</th>
                                                                <th>Amount</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="chequeBody">
                                                            <tr id="noItemRow">
                                                                <td colspan="5" class="text-center text-muted">No
                                                                    items added</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>


                    <section>
                        <div class="row mt-4">
                            <div class="  col-md-12">
                                <div class="card p-4">
                                    <form id="form-data-invoice" autocomplete="off">

                                        <div class="row align-items-center mb-3">
                                            <div class="col-md-2">
                                                <h5 class="mb-0">Invoice Summary Details</h5>
                                            </div>
                                            <div class="col-md-3 text-end">
                                                <div class="d-inline-flex align-items-center">
                                                    <label for="cheque_total" class="form-label me-2 mb-0 text-danger"
                                                        style="white-space: nowrap;">Total Outstanding Amount:</label>
                                                    <input id="outstanding" name="outstanding" type="text"
                                                        placeholder="  Outstanding Amount " class="form-control"
                                                        readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-3 text-end">
                                                <div class="d-inline-flex align-items-center">
                                                    <label for="cheque_total" class="form-label me-2 mb-0 text-danger"
                                                        style="white-space: nowrap;">Cheque Available Balance :</label>
                                                    <input id="cheque_balance" name="cheque_balance" type="text"
                                                        placeholder="Cheque Balance " class="form-control" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-3 text-end">
                                                <div class="d-inline-flex align-items-center">
                                                    <label for="cheque_total" class="form-label me-2 mb-0 text-danger"
                                                        style="white-space: nowrap;">Cash Available Balance:</label>
                                                    <input id="cash_balance" name="cash_balance" type="text"
                                                        placeholder="Cash Balance " class="form-control" readonly>
                                                </div>
                                            </div>

                                        </div>
                                        <!-- Payment Methods Summary Table -->
                                        <div class="table-responsive mt-4">
                                            <h6 class="mb-3">Payment Methods Summary</h6>
                                            <table class="table table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Payment Type</th>
                                                        <th>Invoice No</th>
                                                        <th>Amount</th>
                                                        <th>Cheque No</th>
                                                        <th>Cheque Date</th>
                                                        <th>Bank</th>
                                                        <th>Branch</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($paymentMethods)): ?>
                                                        <?php foreach ($paymentMethods as $index => $method): ?>
                                                            <tr>
                                                                <td><?php echo $index + 1; ?></td>
                                                                <td>
                                                                    <?php
                                                                    $paymentTypeId = $method['payment_type_id'] ?? 0;
                                                                    switch ($paymentTypeId) {
                                                                        case 1:
                                                                            echo '<span class="badge bg-success">Cash</span>';
                                                                            break;
                                                                        case 2:
                                                                            echo '<span class="badge bg-primary">Cheque</span>';
                                                                            break;
                                                                        default:
                                                                            echo '<span class="badge bg-secondary">Payment Type ' . htmlspecialchars($paymentTypeId) . '</span>';
                                                                    }
                                                                    ?>
                                                                </td>
                                                                <td><?php echo htmlspecialchars($method['invoice_id'] ?? 'N/A'); ?></td>
                                                                <td><?php echo number_format($method['amount'] ?? 0, 2); ?></td>
                                                                <td>
                                                                    <?php
                                                                    $paymentTypeId = $method['payment_type_id'] ?? 0;
                                                                    echo ($paymentTypeId == 1) ? 'N/A' : htmlspecialchars($method['cheq_no'] ?? 'N/A');
                                                                    ?>
                                                                </td>
                                                                <td>
                                                                    <?php
                                                                    echo ($paymentTypeId == 1) ? 'N/A' : htmlspecialchars($method['cheq_date'] ?? 'N/A');
                                                                    ?>
                                                                </td>
                                                                <td>
                                                                    <?php
                                                                    echo ($paymentTypeId == 1) ? 'N/A' : htmlspecialchars($method['bank_id'] ?? 'N/A');
                                                                    ?>
                                                                </td>
                                                                <td>
                                                                    <?php
                                                                    echo ($paymentTypeId == 1) ? 'N/A' : htmlspecialchars($method['branch_id'] ?? 'N/A');
                                                                    ?>
                                                                </td>
                                                                <td>
                                                                    <?php if (isset($method['is_settle']) && $method['is_settle'] == 1): ?>
                                                                        <span class="badge bg-success">Settled</span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-warning">Pending</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="9" class="text-center text-muted">No payment methods found</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        <!-- Payment Summary -->
                                        <?php if (!empty($paymentMethods)): ?>
                                            <?php
                                            $totalAmount = 0;
                                            $cashAmount = 0;
                                            $chequeAmount = 0;
                                            $settledCount = 0;

                                            foreach ($paymentMethods as $method) {
                                                $amount = (float)($method['amount'] ?? 0);
                                                $totalAmount += $amount;

                                                // Assuming payment_type_id 1 = cash, 2 = cheque (adjust based on your logic)
                                                if (($method['payment_type_id'] ?? 0) == 1) {
                                                    $cashAmount += $amount;
                                                } else {
                                                    $chequeAmount += $amount;
                                                }

                                                if (isset($method['is_settle']) && $method['is_settle'] == 1) {
                                                    $settledCount++;
                                                }
                                            }
                                            ?>
                                            <div class="row mt-3">
                                                <div class="col-md-12">
                                                    <div class="card bg-light">
                                                        <div class="card-body">
                                                            <div class="row text-center">
                                                                <div class="col-md-3">
                                                                    <h6 class="text-primary">Total Amount</h6>
                                                                    <h4 class="text-primary"><?php echo number_format($totalAmount, 2); ?></h4>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <h6 class="text-success">Cash Amount</h6>
                                                                    <h4 class="text-success"><?php echo number_format($cashAmount, 2); ?></h4>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <h6 class="text-warning">Cheque Amount</h6>
                                                                    <h4 class="text-warning"><?php echo number_format($chequeAmount, 2); ?></h4>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <h6 class="text-info">Settled</h6>
                                                                    <h4 class="text-info"><?php echo $settledCount . ' / ' . count($paymentMethods); ?></h4>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                                <form id="form-data-invoice" autocomplete="off">
                                                    <div class="  p-2 border rounded bg-light" style="max-width: 600px;">
                                                        <div class="row mb-2">
                                                            <div class="col-7">
                                                                <input type="text" class="form-control  " value="Total Outstanding"
                                                                    disabled>
                                                            </div>
                                                            <div class="col-5">
                                                                <input type="text" class="form-control" id="total_outstanding"
                                                                    value="0.00" disabled>
                                                            </div>
                                                        </div>

                                                        <div class="row mb-2">
                                                            <div class="col-7">
                                                                <input type="text" class="form-control  "
                                                                    value="Paid Amount:" disabled>
                                                            </div>
                                                            <div class="col-5">
                                                                <input type="text" class="form-control" id="paid_amount"
                                                                    value="0.00" disabled>
                                                            </div>
                                                        </div>


                                                        <div class="row mb-2">
                                                            <div class="col-7">
                                                                <input type="text" class="form-control   fw-bold"
                                                                    value="Balance Amount:" disabled>
                                                            </div>
                                                            <div class="col-5">
                                                                <input type="text" class="form-control  fw-bold"
                                                                    id="balance_amount" value="0.00" disabled>
                                                            </div>
                                                        </div>
                                                    </div>
                                            </div>

                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <?php include 'footer.php' ?>

            <!-- Right bar overlay-->
            <div class="rightbar-overlay"></div>

            <!-- include main js  -->
            <?php include 'main-js.php' ?>
</body>

</html>