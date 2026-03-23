<!doctype html>
<?php
include 'class/include.php';

if (!isset($_SESSION)) {
    session_start();
}

$invoice_param = $_GET['invoice_no'];
$US = new User($_SESSION['id']);
$COMPANY_PROFILE = new CompanyProfile($US->company_id);

// Handle both invoice ID and invoice number
if (is_numeric($invoice_param)) {
    // It's an ID - use it directly
    $SALES_INVOICE = new SalesInvoice($invoice_param);
    $invoice_id = $invoice_param;
} else {
    // It's an invoice number - look it up
    $SALES_INVOICE_TEMP = new SalesInvoice(null);
    $invoice_data = $SALES_INVOICE_TEMP->getInvoiceByNo($invoice_param);

    if ($invoice_data) {
        $SALES_INVOICE = new SalesInvoice($invoice_data['id']);
        $invoice_id = $invoice_data['id'];
    } else {
        die('Invoice not found: ' . $invoice_param);
    }
}

// Verify invoice exists
if (!$SALES_INVOICE->id) {
    die('Invoice not found');
}

$COMPANY_PROFILE = new CompanyProfile($SALES_INVOICE->company_id);
$CUSTOMER_MASTER = new CustomerMaster($SALES_INVOICE->customer_id);

// Generate public PDF URL
$pdfBaseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$pdfUrl = $pdfBaseUrl . $_SERVER['REQUEST_URI'];
$pdfUrl = preg_replace('/\?.*/', '', $pdfUrl); // Remove existing query parameters
$pdfUrl .= '?pdf=1&invoice_no=' . urlencode($SALES_INVOICE->invoice_no);

// Get customer mobile number for WhatsApp
$customerMobile = !empty($SALES_INVOICE->customer_mobile) ? $SALES_INVOICE->customer_mobile : '';
if (!empty($customerMobile)) {
    // Remove all non-numeric characters
    $customerMobile = preg_replace('/\D/', '', $customerMobile);
    // Add country code if not present (assuming Sri Lanka +94 if 10 digits)
    if (strlen($customerMobile) == 10) {
        $customerMobile = '94' . substr($customerMobile, 1);
    }
}
?>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Invoice Details </title>
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


        }

        /* Remove padding and spacing in invoice table */
        #invoice-content table,
        #invoice-content th,
        #invoice-content td {
            padding: 2px !important;
            /* reduce padding */
            margin: 0 !important;
            border-spacing: 0 !important;
            border-collapse: collapse !important;
        }

        #invoice-content th,
        #invoice-content td {
            vertical-align: middle !important;
            /* optional: center content vertically */
        }

        /* Optional: remove Bootstrap table styles */
        #invoice-content .table {
            width: 100%;

            border-top-width: 0 !important;
            border-style: none !important;
        }
    </style>

</head>

<body data-layout="horizontal" data-topbar="colored">

    <div class="container mt-4">
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 no-print gap-2">
            <h4 class="mb-0">Invoice</h4>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="toggleOutstanding" <?php echo ($SALES_INVOICE->payment_type == 2) ? 'checked' : 'disabled'; ?>>
                    <label class="form-check-label" for="toggleOutstanding">
                        Show customer outstanding
                    </label>
                </div>
                <button onclick="window.print()" class="btn btn-success ms-2">Print</button>
                <button onclick="downloadPDF()" class="btn btn-primary ms-2">PDF</button>
                <button onclick="shareViaWhatsApp()" class="btn btn-success ms-2 no-print">
                    <i class="uil uil-whatsapp"></i> WhatsApp
                </button>
            </div>
        </div>

        <div class="card" id="invoice-content">
            <div class="card-body">
                <!-- Company & Customer Info -->
                <div class="invoice-title">
                    <div class="row mb-2">
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
                        <!-- Header: Logo + Company Info (Left), Invoice Meta (Right) -->
                        <div class="col-12 d-flex justify-content-between align-items-start">
                            <!-- Left: Logo & Company -->
                            <div class="d-flex align-items-center gap-3">
                                <div class="flex-shrink-0">
                                    <?php
                                    $logoPath = 'assets/images/logo.png'; // Default
                                    if (!empty($COMPANY_PROFILE->image_name) && file_exists('uploads/company-logos/' . $COMPANY_PROFILE->image_name)) {
                                        $logoPath = 'uploads/company-logos/' . $COMPANY_PROFILE->image_name;
                                    } elseif (file_exists('assets/images/logo.jpg')) {
                                        $logoPath = 'assets/images/logo.jpg';
                                    }
                                    ?>
                                    <img src="<?php echo $logoPath; ?>" alt="Logo"
                                        style="max-height: 80px; max-width: 150px;">
                                </div>
                                <div>
                                    <h4 class="mb-1 text-uppercase" style="font-weight:900;">
                                        <?php echo $COMPANY_PROFILE->name ?>
                                    </h4>
                                    <p class="mb-1" style="font-size:13px;"><?php echo $COMPANY_PROFILE->address ?></p>
                                    <p class="mb-1" style="font-size:13px;">
                                        <?php echo formatPhone($COMPANY_PROFILE->mobile_number_1); ?>
                                        <?php if (!empty($COMPANY_PROFILE->email))
                                            echo ' | ' . $COMPANY_PROFILE->email; ?>
                                    </p>
                                    <?php if (!empty($COMPANY_PROFILE->vat_number)): ?>
                                        <p class="mb-1" style="font-size:13px;">VAT Reg No:
                                            <?php echo $COMPANY_PROFILE->vat_number ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Right: Invoice Meta -->
                            <div class="text-end">
                                <p class="mb-1" style="font-size:14px;"><strong>Inv No:</strong>
                                    <?php echo $SALES_INVOICE->invoice_no ?></p>
                                <p class="mb-1" style="font-size:14px;"><strong>Inv Date:</strong>
                                    <?php echo date('d M, Y', strtotime($SALES_INVOICE->invoice_date)); ?></p>
                                <?php if (!empty($SALES_INVOICE->vehicle_no)): ?>
                                    <p class="mb-1" style="font-size:14px;"><strong>Vehicle No:</strong>
                                        <?php echo $SALES_INVOICE->vehicle_no ?></p>
                                <?php endif; ?>
                                <?php if ($SALES_INVOICE->payment_type == 2 && $SALES_INVOICE->credit_period): ?>
                                    <?php $CP = new CreditPeriod($SALES_INVOICE->credit_period); ?>
                                    <p class="mb-1" style="font-size:14px;"><strong>Credit Period:</strong>
                                        <?php echo $CP->days ?> Days</p>
                                    <p class="mb-1" style="font-size:14px;"><strong>Due Date:</strong>
                                        <?php echo date('d M, Y', strtotime($SALES_INVOICE->due_date)); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <hr class="my-2" style="border-top: 1px solid #ccc;">
 

                    <!-- Customer Details -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <p class="mb-1" style="font-size:14px;"><strong>Customer:</strong>
                                <?php echo $SALES_INVOICE->customer_name ?></p>
                            <p class="mb-1" style="font-size:14px;"><strong>Contact No:</strong>
                                <?php
                                $contactParts = [];
                                if (!empty($SALES_INVOICE->customer_mobile)) {
                                    $contactParts[] = $SALES_INVOICE->customer_mobile;
                                }
                                if (!empty($SALES_INVOICE->customer_address)) {
                                    $contactParts[] = $SALES_INVOICE->customer_address;
                                }
                                echo !empty($contactParts) ? implode(' - ', $contactParts) : '.................................';
                                ?>
                            </p>
                            <p class="mb-1" style="font-size:14px;"><strong>VAT No:</strong>
                                <?php
                                if (!empty($SALES_INVOICE->customer_id)) {
                                    $CUSTOMER_MASTER = new CustomerMaster($SALES_INVOICE->customer_id);
                                    echo !empty($CUSTOMER_MASTER->vat_no) ? $CUSTOMER_MASTER->vat_no : '.................................';
                                } else {
                                    echo '.................................';
                                }
                                ?>
                            </p>
                        </div>
                    </div>

                    <?php if ($SALES_INVOICE->payment_type == 2): ?>
                        <div id="customer-outstanding" class="alert alert-warning py-2 px-3 mb-3" style="font-size:14px;">
                            <strong>Outstanding Balance:</strong>
                            <?php echo number_format((float) ($SALES_INVOICE->grand_total - $SALES_INVOICE->outstanding_settle_amount), 2); ?>
                        </div>
                    <?php endif; ?>

                    <!-- ITEM INVOICE PRINT -->
                    <?php if ($SALES_INVOICE->invoice_type == 'INV') { ?>
                        <div class="table-responsive">
                            <table class="table table-centered">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th colspan="3">Item Name</th>
                                        <th>Serial No</th>
                                        <th>Selling Price</th>
                                        <th>Qty</th>
                                        <?php if ($SALES_INVOICE->tax > 0): ?>
                                            <th class="text-center">VAT</th>
                                        <?php endif; ?>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody style="font-size:14px;" class="font-bold">
                                    <?php
                                    $TEMP_SALES_ITEM = new SalesInvoiceItem(null);
                                    $temp_items_list = $TEMP_SALES_ITEM->getItemsByInvoiceId($invoice_id);
                                    $subtotal = 0;
                                    $total_discount = 0;

                                    foreach ($temp_items_list as $key => $temp_items) {
                                        $key++;
                                        $price = $temp_items['price'];
                                        $quantity = (int) $temp_items['quantity'];
                                        $list_price = (float)($temp_items['list_price'] ?: $temp_items['price']);
                                        $discount_value = isset($temp_items['discount']) ? (float)$temp_items['discount'] : 0;
                                        
                                        // Calculate percentage: (Total Discount / Total Original Price) * 100
                                        $discount_percentage = ($list_price > 0 && $quantity > 0) ? ($discount_value / ($list_price * $quantity)) * 100 : 0;
                                        
                                        $discount_per_item = $quantity > 0 ? $discount_value / $quantity : 0;
                                        $selling_price = $price * $quantity;
                                        $line_total = $price * $quantity;
                                        $subtotal += $list_price * $quantity;
                                        $total_discount += $discount_value;
                                        ?>
                                        <?php
                                        $item_vat = 0;
                                        if ($SALES_INVOICE->tax > 0) {
                                            $vat_percentage = $COMPANY_PROFILE->vat_percentage;
                                            $item_vat = $line_total * ($vat_percentage / (100 + $vat_percentage));
                                        }
                                        ?>
                                        <tr>
                                            <td>0<?php echo $key; ?></td>
                                            <td colspan="3">
                                                <?php echo $temp_items['item_code_name'] . ' ' . $temp_items['display_name']; ?>
                                                <?php if (!empty($temp_items['next_service_date']) && $temp_items['next_service_date'] !== '0000-00-00' && strtotime($temp_items['next_service_date']) > 0): ?>
                                                    <br><strong>Next Service Date:</strong>
                                                    <?php echo date('d M, Y', strtotime($temp_items['next_service_date'])); ?>
                                                <?php elseif (!empty($temp_items['current_km'])): ?>
                                                    <br><strong>Next Service Km:</strong>
                                                    <?php echo ($temp_items['current_km'] + 500); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo isset($temp_items['serial_no']) ? $temp_items['serial_no'] : ''; ?>
                                            </td>
                                            <td>
                                                <?php echo number_format($price, 2); ?>
                                                <?php if ($discount_percentage > 0): ?>
                                                    <br><small class="text-danger">(<?php echo number_format($discount_percentage, 2); ?>%)</small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $quantity; ?></td>
                                            <?php if ($SALES_INVOICE->tax > 0): ?>
                                                <td class="text-center"><?php echo number_format($item_vat, 2); ?></td>
                                            <?php endif; ?>
                                            <td class="text-end"><?php echo number_format($line_total, 2); ?></td>
                                        </tr>
                                    <?php } ?>
                                    <?php
                                    // Calculate rowspan based on visible rows + hidden discount row
                                    // Cash: Gross, Discount(hidden), Net (3 rows) - VAT is now hidden
                                    // Credit: Gross, Paid, Payable, Discount(hidden), Net (5 rows) - VAT is now hidden
                                    $rowSpan = ($SALES_INVOICE->payment_type == 2) ? 5 : 3;
                                    ?>
                                    <tr>
                                        <td colspan="<?php echo ($SALES_INVOICE->tax > 0) ? 8 : 7; ?>" class="text-end font-weight-bold">
                                            <strong>Grand Total:-</strong>
                                        </td>
                                        <td class="text-end font-weight-bold">
                                            <strong><?php echo number_format($SALES_INVOICE->grand_total, 2); ?></strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="<?php echo ($SALES_INVOICE->tax > 0) ? 9 : 8; ?>" style="padding-top:20px !important;">
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
                                    <tr>
                                        <td colspan="<?php echo ($SALES_INVOICE->tax > 0) ? 9 : 8; ?>" style="vertical-align:top; border-top: none !important; ">
                                            <h6 style="margin-top:15px;"><strong>Terms & Conditions:</strong></h6>
                                            <ul style="padding-left:20px;margin-bottom:0; font-size: 13px;">
                                                <?php
                                                $invoiceRemark = new InvoiceRemark();
                                                $paymentRemarks = $invoiceRemark->getRemarkByPaymentType($SALES_INVOICE->payment_type);
                                                if (!empty($paymentRemarks)) {
                                                    foreach ($paymentRemarks as $remark) {
                                                        if (!empty($remark['remark'])) {
                                                            echo '<li>' . htmlspecialchars($remark['remark']) . '</li>';
                                                        }
                                                    }
                                                }
                                                ?>
                                            </ul>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>



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
                    filename: 'Invoice_<?php echo $SALES_INVOICE->invoice_no ?>.pdf',
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

            function shareViaWhatsApp() {
                const customerMobile = '<?php echo $customerMobile; ?>';
                const invoiceNo = '<?php echo $SALES_INVOICE->invoice_no; ?>';
                const customerName = '<?php echo addslashes($SALES_INVOICE->customer_name); ?>';
                const companyName = '<?php echo addslashes($COMPANY_PROFILE->name); ?>';
                const pdfUrl = '<?php echo $pdfUrl; ?>';

                // Create WhatsApp message
                const message = `Dear ${customerName},\n\nYour invoice ${invoiceNo} from ${companyName} is ready.\n\nYou can download the PDF here: ${pdfUrl}\n\nThank you for your business!`;

                // URL encode the message
                const encodedMessage = encodeURIComponent(message);

                // Create WhatsApp URL using wa.me format
                let whatsappUrl;
                if (customerMobile && customerMobile.length >= 10) {
                    whatsappUrl = `https://wa.me/${customerMobile}?text=${encodedMessage}`;
                } else {
                    // If no customer mobile, open WhatsApp with message (user will need to select contact)
                    whatsappUrl = `https://wa.me/?text=${encodedMessage}`;
                }

                // Open WhatsApp in new tab
                window.open(whatsappUrl, '_blank');
            }

            // Show/hide outstanding banner using the toggle checkbox
            document.addEventListener("DOMContentLoaded", function () {
                const toggleOutstanding = document.getElementById("toggleOutstanding");
                const outstandingBlock = document.getElementById("customer-outstanding");

                function syncOutstandingVisibility() {
                    if (!outstandingBlock || !toggleOutstanding) return;
                    if (toggleOutstanding.checked) {
                        outstandingBlock.style.display = "";
                    } else {
                        outstandingBlock.style.display = "none";
                    }
                }

                if (toggleOutstanding) {
                    toggleOutstanding.addEventListener("change", syncOutstandingVisibility);
                    syncOutstandingVisibility();
                }
            });

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