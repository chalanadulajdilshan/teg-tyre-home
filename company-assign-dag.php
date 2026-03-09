<!doctype html>
<?php
include 'class/include.php';
include 'auth.php';

$COMPANY_ASSIGNMENT = new DagCompanyAssignment(NULL);

// Get the last inserted ID for auto-numbering
$lastId = $COMPANY_ASSIGNMENT->getNextId();
$assignment_number = 'CDA-' . str_pad($lastId, 5, "0", STR_PAD_LEFT);
?>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title>Assign DAG to Company |
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

                            <?php if ($PERMISSIONS['add_page'] ?? true): // fallback to true for testing ?>
                                <a href="#" class="btn btn-primary" id="create">
                                    <i class="uil uil-save me-1"></i> Save
                                </a>
                            <?php endif; ?>

                            <?php if ($PERMISSIONS['edit_page'] ?? true): ?>
                                <a href="#" class="btn btn-warning" id="update" style="display: none;">
                                    <i class="uil uil-edit me-1"></i> Update
                                </a>
                            <?php endif; ?>

                            <?php if ($PERMISSIONS['delete_page'] ?? true): ?>
                                <a href="#" class="btn btn-danger delete-assignment" style="display: none;">
                                    <i class="uil uil-trash-alt me-1"></i> Delete
                                </a>
                            <?php endif; ?>

                            <a href="#" class="btn btn-secondary" id="print" target="_blank" style="display: none;">
                                <i class="uil uil-print me-1"></i> Print
                            </a>
                        </div>

                        <!-- end page title -->

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
                                                <h5 class="font-size-16 mb-1">Step 1: Company Assignment Details</h5>
                                                <p class="text-muted text-truncate mb-0">Select the assigned company</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-4 pt-0">
                                        <form id="form-data" autocomplete="off">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label class="form-label" for="assignment_number">Assignment
                                                        Number</label>
                                                    <div class="input-group mb-3">
                                                        <input id="assignment_number" name="assignment_number"
                                                            type="text" value="<?php echo $assignment_number; ?>"
                                                            placeholder="Assignment No" class="form-control" readonly>
                                                        <button class="btn btn-info" type="button"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#assignmentSearchModal">
                                                            <i class="uil uil-search"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label" for="company_id">Company <span
                                                            class="text-danger">*</span></label>
                                                    <select class="form-select" name="company_id" id="company_id"
                                                        required>
                                                        <option value="">--Select Company--</option>
                                                        <?php
                                                        $COMPANIES = new CompanyMaster(NULL);
                                                        foreach ($COMPANIES->getActiveCompany() as $comp) {
                                                            ?>
                                                            <option value="<?php echo $comp['id']; ?>">
                                                                <?php echo htmlspecialchars($comp['name'] . ' - ' . $comp['code']); ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label" for="company_receipt_number">Company
                                                        Receipt
                                                        Number <span class="text-danger">*</span></label>
                                                    <input id="company_receipt_number" name="company_receipt_number"
                                                        type="text" class="form-control"
                                                        placeholder="Enter Receipt Number" required>
                                                </div>

                                                <div class="col-md-3">
                                                    <label for="company_issued_date" class="form-label">Issued Date
                                                        <span class="text-danger">*</span></label>
                                                    <div class="input-group mb-3">
                                                        <input type="text" class="form-control date-picker-date"
                                                            id="company_issued_date" name="company_issued_date"
                                                            placeholder="Select Date" required>
                                                        <span class="input-group-text"><i
                                                                class="mdi mdi-calendar"></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" id="id" name="id" value="0">
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ITEMS SECTION -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="p-4">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar-xs">
                                                    <div
                                                        class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                        02
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <h5 class="font-size-16 mb-1">Step 2: Add DAG Items</h5>
                                                <p class="text-muted text-truncate mb-0">Search DAGs by My Number and
                                                    assign
                                                    them.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-4 pt-0">
                                        <div class="row mb-4 align-items-end">
                                            <div class="col-md-5">
                                                <label class="form-label" for="search_dag">Find DAG by Search</label>
                                                <div class="d-flex align-items-center gap-3">
                                                    <button class="btn btn-secondary w-100" type="button"
                                                        data-bs-toggle="modal" data-bs-target="#dagSearchModal">
                                                        <i class="uil uil-search"></i> Select DAG Items
                                                    </button>
                                                    <div class="form-check form-switch form-switch-lg mb-0"
                                                        style="min-width: max-content;">
                                                        <input type="checkbox" class="form-check-input"
                                                            id="showRejectedDagsToggle" style="margin-top: 0;">
                                                        <label class="form-check-label mb-0"
                                                            for="showRejectedDagsToggle" style="margin-top: 5px;">Show
                                                            Only Rejected DAGs</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-bordered mb-0" id="dagItemsTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="5%">#</th>
                                                        <th width="15%">My Number</th>
                                                        <th width="15%">Customer Name</th>
                                                        <th width="15%">Job Number</th>
                                                        <th width="15%">Belt Design</th>
                                                        <th width="15%">Company Status</th>
                                                        <th width="12%">Company Received Date</th>
                                                        <th width="10%">UC Number</th>
                                                        <th width="8%">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="dagItemsBody">
                                                    <!-- Items will dynamically appear here -->
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

        <!-- DAG Search Modal -->
        <div class="modal fade" id="dagSearchModal" tabindex="-1" role="dialog" aria-labelledby="dagModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="dagModalLabel">Select DAG Item</h5>
                        <div class="input-group ms-3" style="max-width: 400px;">
                            <input type="text" id="dagSearchInput" class="form-control"
                                placeholder="Search by My Number or Serial No">
                            <button class="btn btn-outline-primary" type="button" id="searchDagBtn">
                                Search
                            </button>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table id="dagSelectionTable"
                            class="table table-bordered table-hover dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>DAG Number</th>
                                    <th>My Number</th>
                                    <th>Customer</th>
                                    <th>Serial No</th>
                                    <th>Size</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="dagSelectionTableBody">
                                <!-- DAGs will be loaded here via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignment Search Modal -->
        <div class="modal fade" id="assignmentSearchModal" tabindex="-1" role="dialog"
            aria-labelledby="assignmentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="assignmentModalLabel">Select Company Assignment</h5>
                        <div class="input-group ms-3" style="max-width: 400px;">
                            <input type="text" id="assignmentSearchInput" class="form-control"
                                placeholder="Search by Assignment No / Receipt">
                            <button class="btn btn-outline-primary" type="button" id="searchAssignmentBtn">
                                Search
                            </button>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table id="assignmentSelectionTable"
                            class="table table-bordered table-hover dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Assignment No</th>
                                    <th>Company</th>
                                    <th>Receipt No</th>
                                    <th>My Numbers</th>
                                    <th>Issued Date</th>
                                    <th>Date Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="assignmentSelectionTableBody">
                                <!-- Assignments will be loaded here via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Right bar overlay-->
    <div class="rightbar-overlay"></div>

    <!-- JAVASCRIPT -->
    <script src="assets/libs/jquery/jquery.min.js"></script>
    <script src="ajax/js/common.js"></script>
    <script src="ajax/js/company-assign-dag.js"></script>

    <!-- include main js  -->
    <?php include 'main-js.php' ?>

</body>

</html>