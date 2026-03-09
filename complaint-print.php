<!doctype html>
<?php
include 'class/include.php';

if (!isset($_SESSION)) {
    session_start();
}

$complaint_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$US = new User($_SESSION['id']);
$COMPANY_PROFILE = new CompanyProfile($US->company_id);

// Get complaint details
$COMPLAINT = new CustomerComplaint($complaint_id);
$CUSTOMER_MASTER = new CustomerMaster($COMPLAINT->customer_id);

// Get company handling details
$CH = new CompanyHandling(null);
$handling_data = null;
$all_handling = $CH->all();
foreach ($all_handling as $h) {
    if ($h['complaint_id'] == $complaint_id) {
        $handling_data = $h;
        break;
    }
}

$status = isset($handling_data['company_status']) ? $handling_data['company_status'] : 'Pending';
?>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Complaint Print |
        <?php echo $COMPANY_PROFILE->name ?>
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- include main CSS -->
    <?php include 'main-css.php' ?>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            @page {
                size: A4 portrait;
                margin: 10mm;
                margin-top: 20mm;
            }

            body {
                width: 100%;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .container,
            .container-fluid {
                width: 100% !important;
                max-width: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }
        }

        .table th,
        .table td {
            padding: 6px 10px !important;
            vertical-align: middle;
            border: 1px solid #dee2e6;
            font-size: 13px;
        }

        .table thead th {
            background-color: #f8f9fa !important;
            color: #495057;
            font-weight: 600;
            font-size: 12px;
        }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 14px;
            color: #333;
        }

        .company-logo {
            max-height: 100px;
            width: auto;
            object-fit: contain;
            margin-bottom: 10px;
        }

        .detail-label {
            font-weight: 600;
            color: #555;
            min-width: 160px;
            display: inline-block;
        }

        .detail-row {
            margin-bottom: 6px;
        }

        .status-badge {
            font-size: 14px;
            padding: 5px 15px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #333;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body data-layout="horizontal" data-topbar="colored">

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h4>Complaint Print</h4>
            <div>
                <button onclick="window.print()" class="btn btn-success ms-2">
                    <i class="mdi mdi-printer me-1"></i> Print
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <!-- Header Section -->
                <div class="row mb-4">
                    <div class="col-sm-6">
                        <img src="./uploads/company-logos/<?php echo $COMPANY_PROFILE->image_name ?>"
                            class="company-logo" alt="logo">
                        <div class="text-muted mt-2">
                            <p class="mb-1"><i class="uil uil-building me-1"></i>
                                <?php echo $COMPANY_PROFILE->name ?>
                            </p>
                            <p class="mb-1"><i class="uil uil-map-marker me-1"></i>
                                <?php echo $COMPANY_PROFILE->address ?>
                            </p>
                            <p><i class="uil uil-phone me-1"></i>
                                <?php echo $COMPANY_PROFILE->mobile_number_1 ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <p><strong>Complaint No:</strong> #
                            <?php echo htmlspecialchars($COMPLAINT->complaint_no); ?>
                        </p>
                        <p><strong>UC Number:</strong>
                            <?php echo htmlspecialchars($COMPLAINT->uc_number); ?>
                        </p>
                        <p><strong>Complaint Date:</strong>
                            <?php
                            if (!empty($COMPLAINT->complaint_date) && $COMPLAINT->complaint_date != '0000-00-00') {
                                echo date('d M, Y', strtotime($COMPLAINT->complaint_date));
                            } else {
                                echo '-';
                            }
                            ?>
                        </p>
                        <p><strong>Print Date:</strong>
                            <?php echo date('d M, Y'); ?>
                        </p>
                    </div>
                </div>

                <!-- Customer & Company Info -->
                <div class="row mb-4">
                    <div class="col-sm-6">
                        <h6 class="section-title">Customer Details</h6>
                        <div class="detail-row">
                            <span class="detail-label">Customer Name:</span>
                            <?php echo htmlspecialchars($CUSTOMER_MASTER->name ?? ''); ?>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Address:</span>
                            <?php echo htmlspecialchars($CUSTOMER_MASTER->address ?? ''); ?>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Phone:</span>
                            <?php echo htmlspecialchars($CUSTOMER_MASTER->mobile_number ?? ''); ?>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <h6 class="section-title">Company Details</h6>
                        <div class="detail-row">
                            <span class="detail-label">Company Name:</span>
                            <?php echo htmlspecialchars($handling_data['company_name'] ?? ''); ?>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Company Number:</span>
                            <?php echo htmlspecialchars($handling_data['company_number'] ?? ''); ?>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Sent Date:</span>
                            <?php
                            if (!empty($handling_data['sent_date']) && $handling_data['sent_date'] != '0000-00-00') {
                                echo date('d M, Y', strtotime($handling_data['sent_date']));
                            } else {
                                echo '-';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Complaint Details -->
                <h6 class="section-title">Complaint Information</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-centered">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Tyre Serial No</th>
                                <th>Fault Description</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($COMPLAINT->complaint_category ?? ''); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($COMPLAINT->tyre_serial_number ?? ''); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($COMPLAINT->fault_description ?? ''); ?>
                                </td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    switch (strtolower($status)) {
                                        case 'priced issued':
                                            $status_class = 'bg-success';
                                            break;
                                        case 'rejection':
                                            $status_class = 'bg-danger';
                                            break;
                                        case 'special request':
                                            $status_class = 'bg-info';
                                            break;
                                        case 'pending':
                                        default:
                                            $status_class = 'bg-warning';
                                    }
                                    ?>
                                    <span class="badge <?php echo $status_class; ?> status-badge">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Status-Specific Section -->
                <?php
                $status_lower = strtolower($status);
                $is_priced = ($status_lower === 'approved' || $status_lower === 'priced issued');
                $is_rejection = (strpos($status_lower, 'rejection') !== false);
                $is_special = (strpos($status_lower, 'special request') !== false);
                ?>

                <?php if ($is_priced): ?>
                    <!-- PRICE ISSUED / APPROVED - Invoice Style Bill -->
                    <h6 class="section-title">Price Issued - Invoice</h6>
                    <div class="table-responsive">
                        <table class="table table-centered">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th colspan="2">Description</th>
                                    <th>Tyre Serial No</th>
                                    <th>Invoice No</th>
                                    <th>Price Issued Date</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody style="font-size:13px;" class="font-bold">
                                <tr>
                                    <td>01</td>
                                    <td colspan="2">
                                        Complaint - <?php echo htmlspecialchars($COMPLAINT->complaint_category ?? ''); ?>
                                        <br><small
                                            class="text-muted"><?php echo htmlspecialchars($COMPLAINT->fault_description ?? ''); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($COMPLAINT->tyre_serial_number ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($handling_data['issued_invoice_number'] ?? '-'); ?></td>
                                    <td>
                                        <?php
                                        if (!empty($handling_data['price_issued_date']) && $handling_data['price_issued_date'] != '0000-00-00') {
                                            echo date('d M, Y', strtotime($handling_data['price_issued_date']));
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-end">
                                        <?php echo !empty($handling_data['price_amount']) ? number_format($handling_data['price_amount'], 2) : '0.00'; ?>
                                    </td>
                                </tr>

                                <!-- Totals Section -->
                                <tr>
                                    <td colspan="4" rowspan="3" style="vertical-align:top;">
                                        <h6 style="margin-top:8px;"><strong>Terms & Conditions:</strong></h6>
                                        <ul style="padding-left:20px;margin-bottom:0;">
                                            <li>This is a complaint settlement invoice.</li>
                                            <li>Price issued as per company approval.</li>
                                            <li>Subject to company terms and policies.</li>
                                        </ul>
                                    </td>
                                    <td colspan="2" class="text-end font-weight-bold">
                                        <strong>Gross Amount:-</strong>
                                    </td>
                                    <td class="text-end font-weight-bold">
                                        <strong><?php echo !empty($handling_data['price_amount']) ? number_format($handling_data['price_amount'], 2) : '0.00'; ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="text-end font-weight-bold">
                                        <strong>Discount:-</strong>
                                    </td>
                                    <td class="text-end font-weight-bold">0.00</td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="text-end">
                                        <strong>Net Amount:-</strong>
                                    </td>
                                    <td class="text-end">
                                        <strong><?php echo !empty($handling_data['price_amount']) ? number_format($handling_data['price_amount'], 2) : '0.00'; ?></strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                <?php else: ?>

                    <?php if ($is_rejection): ?>
                        <!-- REJECTION Section -->
                        <h6 class="section-title">Rejection Details</h6>
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="detail-row">
                                    <span class="detail-label">Rejection Date:</span>
                                    <?php
                                    if (!empty($handling_data['rejection_date']) && $handling_data['rejection_date'] != '0000-00-00') {
                                        echo date('d M, Y', strtotime($handling_data['rejection_date']));
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </div>
                                <div class="detail-row mt-2">
                                    <span class="detail-label">Rejection Reason:</span>
                                    <?php echo htmlspecialchars($handling_data['rejection_reason'] ?? '-'); ?>
                                </div>
                                <?php if (!empty($handling_data['status_remark'])): ?>
                                    <div class="detail-row mt-2">
                                        <span class="detail-label">Status Remark:</span>
                                        <?php echo htmlspecialchars($handling_data['status_remark']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($is_rejection && $is_special): ?>
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-light border-dark">
                                    <strong>Note:</strong> This was rejected by the company and has been reassigned to the same
                                    company with a special request.
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($is_special): ?>
                        <!-- SPECIAL REQUEST Section -->
                        <h6 class="section-title">Special Request Details</h6>
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="detail-row">
                                    <span class="detail-label">Special Request Date:</span>
                                    <?php
                                    if (!empty($handling_data['special_request_date']) && $handling_data['special_request_date'] != '0000-00-00') {
                                        echo date('d M, Y', strtotime($handling_data['special_request_date']));
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </div>
                                <div class="detail-row mt-2">
                                    <span class="detail-label">Special Remark:</span>
                                    <?php echo htmlspecialchars($handling_data['special_remark'] ?? '-'); ?>
                                </div>
                                <div class="detail-row mt-2">
                                    <span class="detail-label">Send To Company:</span>
                                    <?php echo htmlspecialchars($handling_data['company_name'] ?? '-'); ?>
                                </div>
                                <?php if (!empty($handling_data['status_remark'])): ?>
                                    <div class="detail-row mt-2">
                                        <span class="detail-label">Status Remark:</span>
                                        <?php echo htmlspecialchars($handling_data['status_remark']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!$is_rejection && !$is_special && $status_lower != 'pending'): ?>
                        <!-- PENDING / Other -->
                        <div class="alert alert-warning">
                            <strong>Note:</strong> This complaint is currently in <strong>
                                <?php echo ucfirst($status); ?>
                            </strong> status.
                            No specific print details are available for this status.
                        </div>
                    <?php endif; ?>

                <?php endif; ?>

                <!-- General Remarks -->
                <?php if (!empty($handling_data['general_remark'])): ?>
                    <h6 class="section-title">General Remarks</h6>
                    <p>
                        <?php echo htmlspecialchars($handling_data['general_remark']); ?>
                    </p>
                <?php endif; ?>

                <!-- Signature Section -->
                <div style="margin-top: 60px;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="text-align: center;">
                                _________________________<br>
                                <strong>Prepared By</strong>
                            </td>
                            <td style="text-align: center;">
                                _________________________<br>
                                <strong>Approved By</strong>
                            </td>
                            <td style="text-align: center;">
                                _________________________<br>
                                <strong>Received By</strong>
                            </td>
                        </tr>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("keydown", function (e) {
            if (e.key === "Enter") {
                window.print();
            }
        });
    </script>
</body>

</html>