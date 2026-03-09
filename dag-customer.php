<!doctype html>
<?php
include 'class/include.php';
include 'auth.php';
?>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title>Create DAG Customer |
        <?php echo $COMPANY_PROFILE_DETAILS->name ?>
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="<?php echo $COMPANY_PROFILE_DETAILS->name ?>" name="author" />
    <!-- include main CSS -->
    <?php include 'main-css.php' ?>

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

                            <?php if ($PERMISSIONS['edit_page']): ?>
                                <a href="#" class="btn btn-warning" id="update" style="display: none;">
                                    <i class="uil uil-edit me-1"></i> Update
                                </a>
                            <?php endif; ?>

                            <?php if ($PERMISSIONS['delete_page']): ?>
                                <a href="#" class="btn btn-danger delete-item" style="display: none;">
                                    <i class="uil uil-trash-alt me-1"></i> Delete
                                </a>
                            <?php endif; ?>

                            <a href="#" class="btn btn-secondary" id="print" target="_blank" style="display: none;">
                                <i class="uil uil-print me-1"></i> Print
                            </a>

                        </div>

                        <div class="col-md-4 text-md-end text-start mt-3 mt-md-0">
                            <ol class="breadcrumb m-0 justify-content-md-end">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                <li class="breadcrumb-item active">Create DAG Customer</li>
                            </ol>
                        </div>
                    </div>

                    <!-- end page title -->

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">

                                <div class="p-4">

                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar-xs">
                                                <div class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                    01
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h5 class="font-size-16 mb-1">Create DAG Customer</h5>
                                            <p class="text-muted text-truncate mb-0">Fill all information below to
                                                create a DAG Customer</p>
                                        </div>
                                    </div>

                                </div>

                                <div class="p-4">

                                    <form id="form-data" autocomplete="off">
                                        <div class="row">

                                            <!-- Row 1: DAG Number, My Number, Size, Brand, Serial No -->
                                            <div class="col-md-3">
                                                <label class="form-label" for="dag_number">DAG Number</label>
                                                <input id="dag_number" name="dag_number" type="text"
                                                    class="form-control" placeholder="DAG Number" value="<?php $DAG = new DagCustomer(NULL);
                                                    echo 'DAG-' . str_pad($DAG->getNextId(), 5, "0", STR_PAD_LEFT); ?>"
                                                    readonly>
                                            </div>

                                            <div class="col-md-2">
                                                <label for="customer_code" class="form-label">Customer Code <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group mb-3">
                                                    <input id="customer_code" name="customer_code" type="text"
                                                        class="form-control" readonly required>
                                                    <button class="btn btn-info" type="button" data-bs-toggle="modal"
                                                        data-bs-target="#customerModal">
                                                        <i class="uil uil-search"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- hidden customer id -->
                                            <input type="hidden" id="customer_id" name="customer_id">

                                            <div class="col-md-4">
                                                <label for="customer_name" class="form-label">Customer Name <span
                                                        class="text-danger">*</span></label>
                                                <input id="customer_name" name="customer_name" type="text"
                                                    class="form-control mb-3" placeholder="Select Customer" readonly
                                                    required>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label" for="my_number">My Number <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group mb-3">
                                                    <input id="my_number" name="my_number" type="text"
                                                        placeholder="Enter My Number" class="form-control" required>
                                                    <button class="btn btn-info" type="button" data-bs-toggle="modal"
                                                        data-bs-target="#dagCustomerModal">
                                                        <i class="uil uil-search me-1"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label" for="size">Size <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group mb-3">
                                                    <select class="form-select" name="size" id="size" required>
                                                        <option value="">--Select Size--</option>
                                                        <?php
                                                        $SIZE = new Sizes(NULL);
                                                        foreach ($SIZE->all() as $size) {
                                                            ?>
                                                            <option value="<?php echo $size['name']; ?>">
                                                                <?php echo $size['name']; ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <label for="brand" class="form-label">Brand <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-select mb-3" name="brand" id="brand" required>
                                                    <option value="">--Select Brand--</option>
                                                    <?php
                                                    $BRAND = new Brand(NULL);
                                                    foreach ($BRAND->all() as $brand) {
                                                        ?>
                                                        <option value="<?php echo $brand['name']; ?>">
                                                            <?php echo $brand['name']; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <label for="serial_no" class="form-label">Serial No <span
                                                        class="text-danger">*</span></label>
                                                <input id="serial_no" name="serial_no" type="text"
                                                    class="form-control mb-3" placeholder="Enter Serial No" required>
                                            </div>

                                            <!-- Row 2: DAG Received Date, Remark -->
                                            <div class="col-md-3">
                                                <label for="dag_received_date" class="form-label">DAG Received Date
                                                    <span class="text-danger">*</span></label>
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control date-picker-date"
                                                        id="dag_received_date" name="dag_received_date"
                                                        placeholder="Select Date" required>
                                                    <span class="input-group-text"><i
                                                            class="mdi mdi-calendar"></i></span>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="remark" class="form-label">Remark</label>
                                                <textarea id="remark" name="remark" class="form-control" rows="3"
                                                    placeholder="Enter remark..."></textarea>
                                            </div>

                                        </div>
                                        <input type="hidden" id="id" name="id" value="0">

                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- container-fluid -->
            </div>
            <?php include 'footer.php' ?>

        </div>

        <!-- model open here -->
        <div class="modal fade bs-example-modal-xl" id="dagCustomerModal" tabindex="-1" role="dialog"
            aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="myExtraLargeModalLabel">Manage DAG Customers</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">


                                <table id="dagCustomerTable" class="table table-bordered dt-responsive nowrap"
                                    style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>DAG Number</th>
                                            <th>My Number</th>
                                            <th>Size</th>
                                            <th>Brand</th>
                                            <th>Serial No</th>
                                            <th>Date</th>
                                            <th>Remark</th>
                                        </tr>
                                    </thead>


                                    <tbody>
                                        <?php
                                        $DAG_CUSTOMER = new DagCustomer(NULL);
                                        foreach ($DAG_CUSTOMER->all() as $key => $dag_customer) {
                                            $key++;
                                            $CUSTOMER = new CustomerMaster($dag_customer['customer_id']);
                                            ?>
                                            <tr class="select-dag-customer" data-id="<?php echo $dag_customer['id']; ?>"
                                                data-customer_id="<?php echo $dag_customer['customer_id']; ?>"
                                                data-customer_code="<?php echo htmlspecialchars($CUSTOMER->code); ?>"
                                                data-customer_name="<?php echo htmlspecialchars($CUSTOMER->name . (isset($CUSTOMER->name_2) ? ' ' . $CUSTOMER->name_2 : '')); ?>"
                                                data-my_number="<?php echo htmlspecialchars($dag_customer['my_number']); ?>"
                                                data-size="<?php echo htmlspecialchars($dag_customer['size']); ?>"
                                                data-brand="<?php echo htmlspecialchars($dag_customer['brand']); ?>"
                                                data-serial_no="<?php echo htmlspecialchars($dag_customer['serial_no']); ?>"
                                                data-dag_received_date="<?php echo htmlspecialchars($dag_customer['dag_received_date']); ?>"
                                                data-remark="<?php echo htmlspecialchars($dag_customer['remark']); ?>">

                                                <td>
                                                    <?php echo $key; ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($dag_customer['dag_number']); ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($dag_customer['my_number']); ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($dag_customer['size']); ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($dag_customer['brand']); ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($dag_customer['serial_no']); ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($dag_customer['dag_received_date']); ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($dag_customer['remark']); ?>
                                                </td>
                                            </tr>

                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div> <!-- end col -->
                        </div> <!-- end row -->
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>
        <!-- model close here -->

        <!-- Include Customer Modal -->
        <?php include 'customer-master-model.php' ?>

        <!-- Right bar overlay-->
        <div class="rightbar-overlay"></div>

        <!-- JAVASCRIPT -->
        <script src="assets/libs/jquery/jquery.min.js"></script>
        <!-- /////////////////////////// -->
        <script src="ajax/js/common.js"></script>
        <script src="ajax/js/customer-master.js"></script>
        <script src="ajax/js/dag-customer.js"></script>

        <!-- include main js  -->
        <?php include 'main-js.php' ?>
        <script>
            $('#dagCustomerTable').DataTable();
        </script>

</body>

</html>