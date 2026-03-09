<!doctype html>
<?php
include 'class/include.php';
include 'auth.php';


$COMPLAINT = new CustomerComplaint(NULL);

// Get the last inserted ID for auto-numbering
$lastId = $COMPLAINT->getLastID();
$complaint_no = 'CC/00/' . ($lastId + 1);

?>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title>Create Customer Complaint |
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
                                <a href="#" class="btn btn-danger delete-complaint">
                                    <i class="uil uil-trash-alt me-1"></i> Delete
                                </a>
                            <?php endif; ?>

                        </div>

                        <div class="col-md-4 text-md-end text-start mt-3 mt-md-0">
                            <ol class="breadcrumb m-0 justify-content-md-end">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                <li class="breadcrumb-item active">Create Complaint</li>
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
                                            <h5 class="font-size-16 mb-1">Create Customer Complaint</h5>
                                            <p class="text-muted text-truncate mb-0">Fill all information below to
                                                create a complaint</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <i class="mdi mdi-chevron-up accor-down-icon font-size-24"></i>
                                        </div>
                                    </div>

                                </div>

                                <div class="p-4">

                                    <form id="form-data" autocomplete="off">
                                        <div class="row">

                                            <!-- Row 1: Complaint No, UC Number, Category, Date -->
                                            <div class="col-md-3">
                                                <label class="form-label" for="complaint_no">Complaint No</label>
                                                <div class="input-group mb-3">
                                                    <input id="complaint_no" name="complaint_no" type="text"
                                                        value="<?php echo $complaint_no; ?>" placeholder="Complaint No"
                                                        class="form-control" readonly>
                                                    <button class="btn btn-info" type="button" data-bs-toggle="modal"
                                                        data-bs-target="#mainComplaintModel">
                                                        <i class="uil uil-search"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label" for="uc_number">UC Number</label>
                                                <div class="input-group mb-3">
                                                    <input id="uc_number" name="uc_number" type="text"
                                                        placeholder="Enter UC Number" class="form-control">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <label for="complaint_category" class="form-label">Complaint Category
                                                    <span class="text-danger">*</span></label>
                                                <select id="complaint_category" name="complaint_category"
                                                    class="form-select mb-3" required>
                                                    <option value="">-- Select Category --</option>
                                                    <option value="Dag">Dag</option>
                                                    <option value="Original">Original</option>
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <label for="complaint_date" class="form-label">Complaint Date <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control date-picker-date"
                                                        id="complaint_date" name="complaint_date"
                                                        placeholder="Select Date">
                                                    <span class="input-group-text"><i
                                                            class="mdi mdi-calendar"></i></span>
                                                </div>
                                            </div>

                                            <!-- Row 2: Customer Code, Customer Name, Tyre Serial -->
                                            <div class="col-md-2">
                                                <label for="customer_code" class="form-label">Customer Code</label>
                                                <div class="input-group mb-3">
                                                    <input id="customer_code" name="customer_code" type="text"
                                                        class="form-control" readonly>
                                                    <button class="btn btn-info" type="button" data-bs-toggle="modal"
                                                        data-bs-target="#customerModal">
                                                        <i class="uil uil-search"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- hidden customer id -->
                                            <input type="hidden" id="customer_id" name="customer_id">

                                            <div class="col-md-5">
                                                <label for="customer_name" class="form-label">Customer Name <span
                                                        class="text-danger">*</span></label>
                                                <input id="customer_name" name="customer_name" type="text"
                                                    class="form-control mb-3" placeholder="Select Customer" readonly>
                                            </div>

                                            <div class="col-md-5">
                                                <label for="tyre_serial_number" class="form-label">Tyre Serial
                                                    Number</label>
                                                <input id="tyre_serial_number" name="tyre_serial_number" type="text"
                                                    class="form-control mb-3" placeholder="Enter Tyre Serial Number">
                                            </div>

                                            <!-- Row 3: Fault Description - Full Width -->
                                            <div class="col-md-12">
                                                <label for="fault_description" class="form-label">Fault/Issue
                                                    Description</label>
                                                <textarea id="fault_description" name="fault_description"
                                                    class="form-control" rows="3"
                                                    placeholder="Enter detailed description of the fault or issue..."></textarea>
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
    </div>

    <!-- Complaint Search Modal -->
    <div class="modal fade" id="mainComplaintModel" tabindex="-1" role="dialog" aria-labelledby="complaintModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="complaintModalLabel">Select Complaint</h5>

                    <div class="input-group ms-3" style="max-width: 500px;">
                        <input type="text" id="complaintSearchInput" class="form-control"
                            placeholder="Search by Complaint No, UC Number or Customer">
                        <button class="btn btn-outline-primary" type="button" id="searchComplaintBtn">
                            Search
                        </button>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <table id="mainComplaintTable" class="table table-bordered table-hover dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Complaint No</th>
                                <th>UC Number</th>
                                <th>Customer</th>
                                <th>Category</th>
                                <th>Date</th>
                            </tr>
                        </thead>

                        <tbody id="mainComplaintTableBody">
                            <!-- Complaints will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Customer Modal -->
    <?php include 'customer-master-model.php' ?>

    <!-- Right bar overlay-->
    <div class="rightbar-overlay"></div>

    <!-- JAVASCRIPT -->
    <script src="assets/libs/jquery/jquery.min.js"></script>
    <!-- /////////////////////////// -->
    <script src="ajax/js/common.js"></script>
    <script src="ajax/js/customer-master.js"></script>
    <script src="ajax/js/customer-complaint.js"></script>

    <!-- include main js  -->
    <?php include 'main-js.php' ?>

</body>

</html>