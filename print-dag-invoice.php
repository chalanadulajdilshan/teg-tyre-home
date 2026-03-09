<!doctype html>
<?php
include 'class/include.php';

if (!isset($_SESSION)) {
    session_start();
}

$customer_id = isset($_GET['customer_id']) ? (int) $_GET['customer_id'] : 0;
$US = new User($_SESSION['id']);
$COMPANY_PROFILE = new CompanyProfile($US->company_id);

$CUSTOMER = new CustomerMaster($customer_id);
$DAG_CUSTOMER = new DagCustomer(NULL);
$dag_items = $DAG_CUSTOMER->getByCustomerId($customer_id);

// Collect DAG numbers for bill number display
$dagNumbers = [];
foreach ($dag_items as $item) {
    $dagNumbers[] = $item['dag_number'] ?: 'DAG-' . str_pad($item['id'], 5, '0', STR_PAD_LEFT);
}
$billNumber = implode(', ', $dagNumbers);
?>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>DAG Invoice Print |
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

        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #333;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }

        .totals-table td {
            padding: 4px 10px !important;
            border: none !important;
        }
    </style>
</head>

<body data-layout="horizontal" data-topbar="colored">

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h4>DAG Invoice Print</h4>
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
                        <h4 class="font-size-16 mb-2">DAG INVOICE</h4>
                        <p class="mb-1"><strong>Bill No:</strong>
                            <?php echo htmlspecialchars($billNumber); ?>
                        </p>
                        <p class="mb-1"><strong>Print Date:</strong>
                            <?php echo date('d M, Y'); ?>
                        </p>
                        <p class="mb-1"><strong>Customer Code:</strong>
                            <?php echo htmlspecialchars($CUSTOMER->code); ?>
                        </p>
                        <p class="mb-0"><strong>Customer Name:</strong>
                            <?php echo htmlspecialchars($CUSTOMER->name . (isset($CUSTOMER->name_2) ? ' ' . $CUSTOMER->name_2 : '')); ?>
                        </p>
                    </div>
                </div>

                <!-- Invoice Items -->
                <h6 class="section-title">DAG Items</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-centered table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 35px;">#</th>
                                <th>Company</th>
                                <th>Size</th>
                                <th>Brand</th>
                                <th>Serial No</th>
                                <th>Issued Date</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Discount (%)</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $subTotal = 0;
                            $discountTotal = 0;
                            $grandTotal = 0;

                            if (count($dag_items) > 0) {
                                foreach ($dag_items as $index => $item) {
                                    $cost = floatval($item['cost'] ?? 0);
                                    $price = floatval($item['price'] ?? 0);
                                    $discountPct = floatval($item['discount'] ?? 0);
                                    $discountAmount = $price * $discountPct / 100;
                                    $total = floatval($item['total'] ?? 0);

                                    $subTotal += $price;
                                    $discountTotal += $discountAmount;
                                    $grandTotal += $total;

                                    $dagNumber = $item['dag_number'] ?: 'DAG-' . str_pad($item['id'], 5, '0', STR_PAD_LEFT);
                                    $companyName = $item['company_name'] ?? '-';
                                    ?>
                                    <tr>
                                        <td>
                                            <?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($companyName); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($item['size']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($item['brand']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($item['serial_no']); ?>
                                        </td>
                                        <td>
                                            <?php
                                            $itemIssuedDate = $item['issued_date'] ?? '';
                                            echo $itemIssuedDate ? date('d M, Y', strtotime($itemIssuedDate)) : '-';
                                            ?>
                                        </td>
                                        <td class="text-end">
                                            <?php echo number_format($price, 2); ?>
                                        </td>
                                        <td class="text-end">
                                            <?php echo number_format($discountPct, 2); ?>%
                                        </td>
                                        <td class="text-end">
                                            <?php echo number_format($total, 2); ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr><td colspan='9' class='text-center'>No DAG items found for this customer</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Totals Section -->
                <div class="row">
                    <div class="col-sm-6"></div>
                    <div class="col-sm-6">
                        <table class="table totals-table" style="max-width: 400px; float: right;">
                            <tr>
                                <td class="text-end"><strong>Sub Total:</strong></td>
                                <td class="text-end" style="width: 150px;">
                                    <?php echo number_format($subTotal, 2); ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-end"><strong>Discount:</strong></td>
                                <td class="text-end">
                                    <?php echo number_format($discountTotal, 2); ?>
                                </td>
                            </tr>
                            <tr style="border-top: 2px solid #333;">
                                <td class="text-end"><strong>Grand Total:</strong></td>
                                <td class="text-end"><strong>
                                        <?php echo number_format($grandTotal, 2); ?>
                                    </strong></td>
                            </tr>
                        </table>
                    </div>
                </div>

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
                                <strong>Checked By</strong>
                            </td>
                            <td style="text-align: center;">
                                _________________________<br>
                                <strong>Approved By</strong>
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