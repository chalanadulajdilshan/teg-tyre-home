<!doctype html>
<?php
include 'class/include.php';
include 'auth.php';
?>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Company Handling |
        <?php echo $COMPANY_PROFILE_DETAILS->name ?>
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="<?php echo $COMPANY_PROFILE_DETAILS->name ?>" name="author" />
    <!-- include main CSS -->
    <?php include 'main-css.php' ?>
</head>

<body data-layout="horizontal" data-topbar="colored">

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php include 'navigation.php' ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <div class="row mb-4">
                        <div class="col-md-8 d-flex align-items-center flex-wrap gap-2">
                            <a href="#" class="btn btn-success" id="new">
                                <i class="uil uil-plus me-1"></i> New
                            </a>
                            <a href="#" class="btn btn-primary" id="create">
                                <i class="uil uil-save me-1"></i> Save
                            </a>

                            <a href="#" class="btn btn-warning" id="update" style="display: none;">
                                <i class="uil uil-edit me-1"></i> Update
                            </a>
                        </div>
                        <div class="col-md-4 text-md-end text-start mt-3 mt-md-0">
                            <ol class="breadcrumb m-0 justify-content-md-end">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                <li class="breadcrumb-item active">Company Handling</li>
                            </ol>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="p-4">
                                    <h5 class="font-size-16 mb-1">Company Handling</h5>
                                    <p class="text-muted text-truncate mb-0">Manage complaints sent to companies</p>
                                </div>
                                <div class="p-4">
                                    <form id="form-data" autocomplete="off">
                                        <div class="row">

                                            <!-- Complaint Selection -->
                                            <div class="col-md-3">
                                                <label class="form-label">Complaint No <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group mb-3">
                                                    <input id="complaint_no" name="complaint_no" type="text"
                                                        class="form-control" readonly placeholder="Select Complaint">
                                                    <button class="btn btn-info" type="button" id="searchComplaintBtn">
                                                        <i class="uil uil-search"></i>
                                                    </button>
                                                </div>
                                                <input type="hidden" id="complaint_id" name="complaint_id">
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">UC Number</label>
                                                <input id="uc_number" name="uc_number" type="text" class="form-control"
                                                    readonly>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Fault Description</label>
                                                <input id="fault_description" name="fault_description" type="text"
                                                    class="form-control" readonly>
                                            </div>

                                            <!-- Manual Company Entry -->
                                            <div class="col-md-3">
                                                <label class="form-label">Company Number</label>
                                                <div class="input-group mb-3">
                                                    <input id="company_number" name="company_number" type="text"
                                                        class="form-control" placeholder="Enter Company Number">
                                                    <button class="btn btn-info" type="button" id="searchCompanyBtn">
                                                        <i class="uil uil-search"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">Company Name <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group mb-3">
                                                    <input id="company_name" name="company_name" type="text"
                                                        class="form-control" placeholder="Select Company" readonly>

                                                </div>
                                                <input type="hidden" id="company_id" name="company_id">
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">Sent to Company Date</label>
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control date-picker-date"
                                                        id="sent_date" name="sent_date" placeholder="Select Date">
                                                    <span class="input-group-text"><i
                                                            class="mdi mdi-calendar"></i></span>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">Status Remark</label>
                                                <input type="text" class="form-control" id="status_remark"
                                                    name="status_remark" placeholder="Enter status remark">
                                            </div>

                                            <!-- Status Checkboxes -->
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label d-block">Status</label>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input status-checkbox" type="checkbox"
                                                        id="status_priced_issued" value="Priced Issued">
                                                    <label class="form-check-label" for="status_priced_issued">Priced
                                                        Issued by Company</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input status-checkbox" type="checkbox"
                                                        id="status_rejection" value="Rejection">
                                                    <label class="form-check-label"
                                                        for="status_rejection">Rejection</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input status-checkbox" type="checkbox"
                                                        id="status_special_request" value="Special Request">
                                                    <label class="form-check-label" for="status_special_request">Special
                                                        Request</label>
                                                </div>
                                                <!-- Hidden field to store the primary status (or comma separated list if multiple are allowed, but usually status is singular. Let's assume singular dominance or just store the text) -->
                                                <input type="hidden" id="company_status" name="company_status">
                                            </div>

                                            <!-- Priced Issued Section -->
                                            <div id="section_priced_issued" class="col-md-12 status-section"
                                                style="display:none; background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                                                <h6 class="text-primary">Priced Issued Details</h6>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Price Amount</label>
                                                        <input type="number" step="0.01" class="form-control"
                                                            id="price_amount" name="price_amount" placeholder="0.00">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Price Issued Date</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control date-picker-date"
                                                                id="price_issued_date" name="price_issued_date"
                                                                placeholder="Select Date">
                                                            <span class="input-group-text"><i
                                                                    class="mdi mdi-calendar"></i></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Rejection Section -->
                                            <div id="section_rejection" class="col-md-12 status-section"
                                                style="display:none; background: #fff5f5; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                                                <h6 class="text-danger">Rejection Details</h6>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Rejection Date</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control date-picker-date"
                                                                id="rejection_date" name="rejection_date"
                                                                placeholder="Select Date">
                                                            <span class="input-group-text"><i
                                                                    class="mdi mdi-calendar"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Rejection Reason</label>
                                                        <textarea class="form-control" id="rejection_reason"
                                                            name="rejection_reason" rows="1"
                                                            placeholder="Enter reason..."></textarea>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Received Invoice Number</label>
                                                        <input type="text" class="form-control"
                                                            id="received_invoice_number" name="received_invoice_number">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Special Request Section -->
                                            <div id="section_special_request" class="col-md-12 status-section"
                                                style="display:none; background: #fff8e1; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                                                <h6 class="text-warning">Special Request Details</h6>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Special Request Date</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control date-picker-date"
                                                                id="special_request_date" name="special_request_date"
                                                                placeholder="Select Date">
                                                            <span class="input-group-text"><i
                                                                    class="mdi mdi-calendar"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Company Invoice Number</label>
                                                        <input type="text" class="form-control"
                                                            id="company_invoice_number" name="company_invoice_number">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Remark</label>
                                                        <textarea class="form-control" id="special_remark"
                                                            name="special_remark" rows="1"></textarea>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Issued Invoice Number</label>
                                                        <input type="text" class="form-control"
                                                            id="issued_invoice_number" name="issued_invoice_number"
                                                            placeholder="Optional">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- General Remark -->
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">General Remark</label>
                                                <textarea class="form-control" id="general_remark" name="general_remark"
                                                    rows="3" placeholder="Add some text..."></textarea>
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

    <!-- Company Search Modal -->
    <div class="modal fade" id="companyModal" tabindex="-1" aria-labelledby="companyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="companyModalLabel">Select Company</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="companyTable" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Code</th>
                                    <th>Company Name</th>
                                    <th>Contact Person</th>
                                    <th>Phone</th>
                                </tr>
                            </thead>
                            <tbody id="companyTableBody">
                                <!-- Loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Complaint Search Modal -->
    <div class="modal fade" id="complaintModal" tabindex="-1" aria-labelledby="complaintModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="complaintModalLabel">Select Complaint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="complaintTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Complaint No</th>
                                    <th>UC Number</th>
                                    <th>Customer</th>
                                    <th>Fault</th>
                                    <th>Company Name</th>
                                    <th>Company Number</th>
                                    <th>Sent Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="complaintTableBody">
                                <!-- Loaded via AJAX -->
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
    <script src="ajax/js/company-handling.js"></script>
    <?php include 'main-js.php' ?>
    <script>
        $(document).ready(function () {
            $('#datatable').DataTable();
        });
    </script>
</body>

</html>