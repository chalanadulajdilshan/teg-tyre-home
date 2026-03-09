<?php
include 'class/include.php';
include 'auth.php';

$DAG_CUSTOMER = new DagCustomer(NULL);
$companies = $DAG_CUSTOMER->getDistinctCompanies();
$brands = $DAG_CUSTOMER->getDistinctBrands();

$customers = $DAG_CUSTOMER->getCustomersWithDags();

// Initial load - get all DAGs
$reports = $DAG_CUSTOMER->getReportData();
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>DAG Report |
        <?php echo $COMPANY_PROFILE_DETAILS->name ?>
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="<?php echo $COMPANY_PROFILE_DETAILS->name ?>" name="author" />

    <!-- Include main CSS -->
    <?php include 'main-css.php' ?>

    <!-- DataTables CSS -->
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet"
        type="text/css" />

    <style>
        .badge-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .timeline-info {
            font-size: 12px;
            line-height: 1.6;
        }

        .timeline-info .label {
            color: #6c757d;
            font-weight: 500;
        }

        .timeline-info .value {
            font-weight: 600;
        }
    </style>
</head>

<body data-layout="horizontal" data-topbar="colored" class="someBlock">
    <div id="layout-wrapper">
        <?php include 'navigation.php' ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <!-- Start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-flex align-items-center justify-content-between">
                                <h4 class="mb-0 font-size-18">DAG Report</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Reports</a></li>
                                        <li class="breadcrumb-item active">DAG Report</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End page title -->

                    <!-- Filter Section -->
                    <div class="row mb-2">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
                                            <label for="from_date" class="form-label">Date From</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i
                                                        class="uil uil-calendar-alt"></i></span>
                                                <input type="text" class="form-control date-picker" id="from_date"
                                                    name="from_date" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
                                            <label for="to_date" class="form-label">Date To</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i
                                                        class="uil uil-calendar-alt"></i></span>
                                                <input type="text" class="form-control date-picker" id="to_date"
                                                    name="to_date" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
                                            <label class="form-label">Customer</label>
                                            <select class="form-select" id="filter_customer">
                                                <option value="">All Customers</option>
                                                <?php foreach ($customers as $cust):
                                                    $fullName = trim($cust['name'] . ' ' . ($cust['name_2'] ?? ''));
                                                    ?>
                                                    <option value="<?php echo htmlspecialchars($cust['name']); ?>">
                                                        <?php echo htmlspecialchars($fullName . ($cust['code'] ? ' - ' . $cust['code'] : '')); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
                                            <label class="form-label">Company</label>
                                            <select class="form-select" id="filter_company">
                                                <option value="">All Companies</option>
                                                <?php foreach ($companies as $comp): ?>
                                                    <option value="<?php echo htmlspecialchars($comp); ?>">
                                                        <?php echo htmlspecialchars($comp); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
                                            <label class="form-label">Brand</label>
                                            <select class="form-select" id="filter_brand">
                                                <option value="">All Brands</option>
                                                <?php foreach ($brands as $brand): ?>
                                                    <option value="<?php echo htmlspecialchars($brand); ?>">
                                                        <?php echo htmlspecialchars($brand); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
                                            <label class="form-label">Invoice Status</label>
                                            <select class="form-select" id="filter_invoice_status">
                                                <option value="">All</option>
                                                <option value="invoiced">Invoiced</option>
                                                <option value="not_invoiced">Not Invoiced</option>
                                                <option value="cancelled">Cancelled</option>
                                            </select>
                                        </div>
                                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6 ms-auto">
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-primary w-100" id="btn-filter">
                                                    <i class="uil uil-filter me-1"></i> Filter
                                                </button>
                                                <button type="button" class="btn btn-secondary w-100"
                                                    id="btn-reset-filter">
                                                    <i class="uil uil-redo me-1"></i> Reset
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Filter Section -->

                    <!-- Report Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-4">
                                        <h4 class="card-title">DAG Lifecycle Report</h4>
                                        <div>
                                            <button class="btn btn-danger btn-sm" onclick="window.print()">
                                                <i class="mdi mdi-printer me-1"></i> Print
                                            </button>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="dag-report-table"
                                            class="table table-bordered dt-responsive nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th style="width:30px;"></th>
                                                    <th>DAG No</th>
                                                    <th>Customer & Issued</th>
                                                    <th>Tyre Details</th>
                                                    <th>DAG Received</th>
                                                    <th>Invoice Status</th>
                                                    <th>Pricing</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Data will be loaded by DataTables -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Report Table -->
                </div> <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <?php include 'footer.php' ?>
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <!-- JAVASCRIPT -->
    <?php include 'main-js.php' ?>

    <!-- Required datatable js -->
    <script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

    <!-- DAG Report JS -->
    <script src="ajax/js/dag-report.js"></script>

</body>

</html>