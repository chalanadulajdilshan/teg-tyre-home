<!doctype html>
<?php
include 'class/include.php';
include './auth.php';
?>

<html lang="en">

<head>

    <meta charset="utf-8" />
    <title>Daily Income | <?php echo $COMPANY_PROFILE_DETAILS->name ?> </title>
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
                                <a href="#" class="btn btn-warning" id="update" style="display:none;">
                                    <i class="uil uil-edit me-1"></i> Update
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4 text-md-end text-start mt-3 mt-md-0">
                            <ol class="breadcrumb m-0 justify-content-md-end">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                <li class="breadcrumb-item active">Daily Income </li>
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
                                        <div class="d-flex align-items-center flex-grow-1">
                                            <div class="me-3">
                                                <h5 class="font-size-16 mb-1">Daily Income Entry</h5>
                                                <p class="text-muted text-truncate mb-0">Fill all information below to add Daily Income</p>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <i class="mdi mdi-chevron-up accor-down-icon font-size-24"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-4">
                                    <form id="form-data" autocomplete="off">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label" for="date">Date <span class="text-danger">*</span></label>
                                                <div class="input-group mb-3">
                                                    <input type="date" class="form-control" id="date" name="date"
                                                        value="<?php echo date('Y-m-d'); ?>" required>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label" for="amount">Amount <span class="text-danger">*</span></label>
                                                <div class="input-group mb-3">
                                                    <input type="number" step="0.01" class="form-control" id="amount"
                                                        name="amount" placeholder="Enter Amount" required>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="remark" class="form-label">Remark</label>
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control" id="remark" name="remark"
                                                        placeholder="Enter Remark">
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" id="id" name="id" value="0">
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Daily Income Records Section -->
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">Daily Income Records</h4>
                                </div>
                                <div class="card-body">
                                    <!-- Date Range Filter -->
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <label for="filter_from_date" class="form-label">From Date</label>
                                            <input type="date" class="form-control" id="filter_from_date" 
                                                   value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="filter_to_date" class="form-label">To Date</label>
                                            <input type="date" class="form-control" id="filter_to_date" 
                                                   value="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-primary w-100" id="filter_records">
                                                <i class="uil uil-search me-1"></i> Filter
                                            </button>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Total Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rs.</span>
                                                <input type="text" class="form-control" id="total_amount" readonly 
                                                       value="0.00">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Records Table -->
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="incomeTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th>Remark</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="incomeTableBody">
                                                <tr id="noRecordsRow">
                                                    <td colspan="5" class="text-center text-muted">
                                                        No records found. Use the filter to search.
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- container-fluid -->
            </div>
            <?php include 'footer.php' ?>
        </div>
    </div>

    <!-- Right bar overlay-->
    <div class="rightbar-overlay"></div>

    <!-- JAVASCRIPT -->
    <script src="assets/libs/jquery/jquery.min.js"></script>
    <!-- /////////////////////////// -->
    <script src="ajax/js/daily-income.js"></script>

    <!-- include main js  -->
    <?php include 'main-js.php' ?>

</body>

</html>
