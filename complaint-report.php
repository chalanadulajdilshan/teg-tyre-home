<?php
include 'class/include.php';
include 'auth.php';

$COMPLAINT = new CustomerComplaint(NULL);

// Get filter parameters - empty by default to fetch all records
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
$filter_company = isset($_GET['filter_company']) ? $_GET['filter_company'] : '';

// Get distinct companies for filter dropdown
$companies = $COMPLAINT->getDistinctCompanies();

// Get filtered complaint reports
$reports = $COMPLAINT->getFilteredReports($from_date, $to_date, $category, $filter_status, $filter_company);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Complaint Report |
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
                                <h4 class="mb-0 font-size-18">Complaint Report</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Reports</a></li>
                                        <li class="breadcrumb-item active">Complaint Report</li>
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
                                                    name="from_date" value="<?php echo $from_date ?>"
                                                    autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
                                            <label for="to_date" class="form-label">Date To</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i
                                                        class="uil uil-calendar-alt"></i></span>
                                                <input type="text" class="form-control date-picker" id="to_date"
                                                    name="to_date" value="<?php echo $to_date ?>" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
                                            <label class="form-label">Category</label>
                                            <select class="form-select" name="filter_category" id="filter_category">
                                                <option value="">All Categories</option>
                                                <option value="Dag" <?php echo ($category == 'Dag') ? 'selected' : ''; ?>>
                                                    Dag</option>
                                                <option value="Original" <?php echo ($category == 'Original') ? 'selected' : ''; ?>>Original</option>
                                            </select>
                                        </div>
                                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="filter_status" id="filter_status">
                                                <option value="">All Status</option>
                                                <option value="Pending" <?php echo ($filter_status == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                                <option value="Priced Issued" <?php echo ($filter_status == 'Priced Issued') ? 'selected' : ''; ?>>Priced Issued</option>
                                                <option value="Rejection" <?php echo ($filter_status == 'Rejection') ? 'selected' : ''; ?>>Rejection</option>
                                                <option value="Special Request" <?php echo ($filter_status == 'Special Request') ? 'selected' : ''; ?>>Special Request</option>
                                            </select>
                                        </div>
                                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
                                            <label class="form-label">Company</label>
                                            <select class="form-select" name="filter_company" id="filter_company">
                                                <option value="">All Companies</option>
                                                <?php foreach ($companies as $comp): ?>
                                                    <option value="<?php echo htmlspecialchars($comp); ?>" <?php echo ($filter_company == $comp) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($comp); ?>
                                                    </option>
                                                <?php endforeach; ?>
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
                                        <h4 class="card-title">Complaint Report</h4>
                                        <div>
                                            <button class="btn btn-danger btn-sm" onclick="printReport()">
                                                <i class="mdi mdi-printer me-1"></i> Print
                                            </button>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="complaint-report-table"
                                            class="table table-bordered dt-responsive nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Complaint No</th>
                                                    <th>UC Number</th>
                                                    <th>Customer Name</th>
                                                    <th>Company</th>
                                                    <th>Tyre Serial</th>
                                                    <th>Fault Description</th>
                                                    <th>Complaint Date</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($reports)): ?>
                                                    <?php $counter = 1; ?>
                                                    <?php foreach ($reports as $report): ?>
                                                        <?php
                                                        $status = isset($report['company_status']) && !empty($report['company_status']) ? $report['company_status'] : '';
                                                        $row_class = (strtolower($status) === 'rejection') ? 'table-danger' : '';
                                                        ?>
                                                        <tr class="<?php echo $row_class; ?>">
                                                            <td><?php echo $counter++; ?></td>
                                                            <td><?php echo htmlspecialchars($report['complaint_no']); ?></td>
                                                            <td><?php echo htmlspecialchars($report['uc_number']); ?></td>
                                                            <td><?php echo htmlspecialchars($report['customer_name']); ?></td>
                                                            <td><?php echo (!empty($report['company_name'])) ? htmlspecialchars($report['company_name']) : '<span class="text-muted">Not Assigned</span>'; ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($report['tyre_serial_number']); ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($report['fault_description']); ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $c_date = $report['complaint_date'];
                                                                if (!empty($c_date) && $c_date != '0000-00-00') {
                                                                    echo date('d/m/Y', strtotime($c_date));
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $status = isset($report['company_status']) && !empty($report['company_status']) ? $report['company_status'] : 'Pending';
                                                                $status_class = '';
                                                                $status_lower = strtolower($status);
                                                                if (strpos($status_lower, 'priced issued') !== false) {
                                                                    $status_class = 'bg-success';
                                                                } elseif (strpos($status_lower, 'special request') !== false) {
                                                                    $status_class = 'bg-primary';
                                                                } elseif (strpos($status_lower, 'rejection') !== false) {
                                                                    $status_class = 'bg-danger';
                                                                } else {
                                                                    $status_class = 'bg-warning';
                                                                }
                                                                ?>
                                                                <span class="badge <?php echo $status_class; ?> font-size-12">
                                                                    <?php echo ucfirst($status); ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <?php if (!empty($report['handling_id']) && strtolower(trim($status)) !== 'pending'): ?>
                                                                    <?php
                                                                    if (strtolower(trim($status)) === 'priced issued') {
                                                                        $print_url = "company-handling-print.php?id=" . $report['handling_id'];
                                                                    } else {
                                                                        $print_url = "complaint-print.php?id=" . $report['id'];
                                                                    }
                                                                    ?>
                                                                    <a href="<?php echo $print_url; ?>" target="_blank"
                                                                        class="btn btn-info btn-sm">
                                                                        <i class="mdi mdi-printer"></i>
                                                                    </a>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="10" class="text-center">No records found</td>
                                                    </tr>
                                                <?php endif; ?>
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

    <!-- Complaint Report JS -->
    <script src="ajax/js/complaint-report.js"></script>

</body>

</html>