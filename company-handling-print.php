<!doctype html>
<?php
include 'class/include.php';

if (!isset($_SESSION)) {
    session_start();
}

// Validate ID parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid ID');
}

$handling_id = (int) $_GET['id'];
$US = new User($_SESSION['id']);
$COMPANY_PROFILE = new CompanyProfile($US->company_id);
$COMPANY_HANDLING = new CompanyHandling($handling_id);

// Verify record exists
if (!$COMPANY_HANDLING->id) {
    die('Record not found');
}

// Get complaint details
$COMPLAINT = null;
if ($COMPANY_HANDLING->complaint_id) {
    $db = Database::getInstance();
    $query = "SELECT cc.*, cm.name as customer_name, cm.mobile_number as customer_mobile, cm.address as customer_address
              FROM `customer_complaint` cc
              LEFT JOIN `customer_master` cm ON cc.customer_id = cm.id
              WHERE cc.id = " . (int) $COMPANY_HANDLING->complaint_id;
    $result = $db->readQuery($query);
    if ($result) {
        $COMPLAINT = mysqli_fetch_assoc($result);
    }
}
?>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Bill | <?php echo $COMPANY_PROFILE->name ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'main-css.php' ?>
    <link href="https://unicons.iconscout.com/release/v4.0.8/css/line.css" rel="stylesheet">

    <style>
        @media print {

            /* Hide non-print elements */
            .no-print {
                display: none !important;
            }

            /* Make invoice full width */
            body,
            html {
                width: 100%;
                margin: 0;
                padding: 0;
            }

            #invoice-content,
            .card {
                width: 100% !important;
                max-width: 100% !important;
                box-shadow: none;
            }

            .container {
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
            }

            @page {
                size: auto;
                margin: 10mm;
            }
        }

        /* Remove padding and spacing in invoice table */
        #invoice-content table,
        #invoice-content th,
        #invoice-content td {
            padding: 2px !important;
            margin: 0 !important;
            border-spacing: 0 !important;
            border-collapse: collapse !important;
        }

        #invoice-content th,
        #invoice-content td {
            vertical-align: middle !important;
        }

        #invoice-content .table {
            width: 100%;
            border-top-width: 0 !important;
            border-style: none !important;
        }
        
        .company-logo {
            max-height: 100px;
            width: auto;
            object-fit: contain;
            margin-bottom: 10px;
        }
    </style>

</head>

<body data-layout="horizontal" data-topbar="colored">

    <div class="container mt-4">
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 no-print gap-2">
            <h4 class="mb-0">Bill</h4>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button onclick="window.print()" class="btn btn-success ms-2">Print</button>
                <button onclick="downloadPDF()" class="btn btn-primary ms-2">PDF</button>
            </div>
        </div>

        <div class="card" id="invoice-content">
            <div class="card-body">
                <!-- Company & Customer Info -->
                <div class="invoice-title">
                    <div class="row mb-4">
                        <?php
                        function formatPhone($number)
                        {
                            $number = preg_replace('/\D/', '', $number);
                            if (strlen($number) == 10) {
                                return sprintf("(%s) %s-%s", substr($number, 0, 3), substr($number, 3, 3), substr($number, 6));
                            }
                            return $number;
                        }
                        ?>
                        <div class="col-md-5 text-muted">
                            <img src="./uploads/company-logos/<?php echo $COMPANY_PROFILE->image_name ?>" class="company-logo" alt="logo">
                            <p class="mb-1" style="font-weight:bold;font-size:18px;">
                                <?php echo $COMPANY_PROFILE->name ?>
                            </p>
                            <p class="mb-1" style="font-size:13px;"><?php echo $COMPANY_PROFILE->address ?></p>
                            <p class="mb-1" style="font-size:13px;">
                                <?php echo formatPhone($COMPANY_PROFILE->mobile_number_1); ?>
                                <?php echo $COMPANY_PROFILE->email ?>
                            </p>
                            <p class="mb-1" style="font-size:13px;">VAT Registration No:
                                <?php echo $COMPANY_PROFILE->vat_number ?><br>
                            </p>
                        </div>
                        <div class="col-md-4 text-sm-start text-md-start">
                            <h3 style="font-weight:bold;font-size:18px;">COMPLAINT BILL</h3>
                            <p class="mb-1 text-muted" style="font-size:14px;"><strong>Name:</strong>
                                <?php echo htmlspecialchars($COMPLAINT['customer_name'] ?? ''); ?></p>
                            <p class="mb-1 text-muted" style="font-size:14px;"><strong>Contact:</strong>
                                <?php echo !empty($COMPLAINT['customer_address']) ? htmlspecialchars($COMPLAINT['customer_address']) : '' ?>
                                -
                                <?php echo !empty($COMPLAINT['customer_mobile']) ? htmlspecialchars($COMPLAINT['customer_mobile']) : '.................................'; ?>
                            </p>
                        </div>

                        <div class="col-md-3 text-sm-start text-md-end">
                            <p class="mb-1" style="font-size:14px;"><strong>Ref No:</strong>
                                <?php echo htmlspecialchars($COMPLAINT['complaint_no'] ?? 'N/A'); ?></p>
                            <p class="mb-1" style="font-size:14px;"><strong>UC No:</strong>
                                <?php echo htmlspecialchars($COMPLAINT['uc_number'] ?? 'N/A'); ?></p>
                            <?php if (!empty($COMPANY_HANDLING->issued_invoice_number)): ?>
                                <p class="mb-1" style="font-size:14px;"><strong>Invoice No:</strong>
                                    <?php echo htmlspecialchars($COMPANY_HANDLING->issued_invoice_number); ?></p>
                            <?php endif; ?>
                            <p class="mb-1" style="font-size:14px;"><strong>Date:</strong>
                                <?php
                                if (!empty($COMPANY_HANDLING->price_issued_date) && $COMPANY_HANDLING->price_issued_date != '0000-00-00') {
                                    echo date('d M, Y', strtotime($COMPANY_HANDLING->price_issued_date));
                                } else {
                                    echo date('d M, Y');
                                }
                                ?>
                            </p>
                        </div>
                    </div>

                    <!-- ITEM TABLE -->
                    <div class="table-responsive">
                        <table class="table table-centered">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th colspan="2">Description</th>
                                    <th>Company</th>
                                    <th>Tyre Serial</th>
                                    <th>Status</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody style="font-size:13px;" class="font-bold">
                                <tr>
                                    <td>01</td>
                                    <td colspan="2">
                                        <?php echo htmlspecialchars($COMPLAINT['fault_description'] ?? 'Service Charge'); ?>
                                        <?php if (!empty($COMPLAINT['complaint_category'])): ?>
                                            <br><small class="text-muted">Category:
                                                <?php echo htmlspecialchars($COMPLAINT['complaint_category']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($COMPANY_HANDLING->company_name ?: 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($COMPLAINT['tyre_serial_number'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($COMPANY_HANDLING->company_status ?: 'N/A'); ?></td>
                                    <td class="text-end">
                                        <?php echo $COMPANY_HANDLING->price_amount ? number_format($COMPANY_HANDLING->price_amount, 2) : '0.00'; ?>
                                    </td>
                                </tr>

                                <!-- Totals Section -->
                                <tr>
                                    <td colspan="4" rowspan="3" style="vertical-align:top;">
                                        <h6 style="margin-top:8px;"><strong>Terms & Conditions:</strong></h6>
                                        <ul style="padding-left:20px;margin-bottom:0;">
                                            <li>Payment is due upon receipt</li>
                                            <li>Please retain this bill for your records</li>
                                        </ul>
                                    </td>
                                    <td colspan="2" class="text-end font-weight-bold"><strong>Gross Amount:-</strong>
                                    </td>
                                    <td class="text-end font-weight-bold">
                                        <strong><?php echo $COMPANY_HANDLING->price_amount ? number_format($COMPANY_HANDLING->price_amount, 2) : '0.00'; ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="text-end font-weight-bold"><strong>Discount:-</strong></td>
                                    <td class="text-end font-weight-bold">0.00</td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="text-end"><strong>Net Amount:-</strong></td>
                                    <td class="text-end">
                                        <strong><?php echo $COMPANY_HANDLING->price_amount ? number_format($COMPANY_HANDLING->price_amount, 2) : '0.00'; ?></strong>
                                    </td>
                                </tr>

                                <!-- Signature Section -->
                                <tr>
                                    <td colspan="7" style="padding-top:50px !important;">
                                        <table style="width:100%;">
                                            <tr>
                                                <td style="text-align:center;">
                                                    _________________________<br><strong>Prepared By</strong></td>
                                                <td style="text-align:center;">
                                                    _________________________<br><strong>Approved By</strong></td>
                                                <td style="text-align:center;">
                                                    _________________________<br><strong>Received By</strong></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadPDF() {
            const element = document.getElementById('invoice-content');
            const opt = {
                margin: 0.5,
                filename: 'Bill_<?php echo $COMPLAINT['complaint_no'] ?? $handling_id; ?>.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                }
            };
            html2pdf().set(opt).from(element).save();
        }

        // Trigger print on Enter
        document.addEventListener("keydown", function (e) {
            if (e.key === "Enter") {
                window.print();
            }
        });
    </script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>