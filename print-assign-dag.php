<!doctype html>
<?php
include 'class/include.php';

if (!isset($_SESSION)) {
    session_start();
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$US = new User($_SESSION['id']);
$COMPANY_PROFILE = new CompanyProfile($US->company_id);

$ASSIGNMENT = new DagCompanyAssignment($id);
$COMPANY = new CompanyMaster($ASSIGNMENT->company_id);
?>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Company Assignment Print |
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
    </style>
</head>

<body data-layout="horizontal" data-topbar="colored">

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h4>Company Assignment Print</h4>
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
                        <h4 class="font-size-16 mb-2">ASSIGNMENT NO:
                            #
                            <?php echo htmlspecialchars($ASSIGNMENT->assignment_number); ?>
                        </h4>
                        <p class="mb-1"><strong>Print Date:</strong>
                            <?php echo date('d M, Y'); ?>
                        </p>
                        <p class="mb-1"><strong>Assigned Company:</strong>
                            <?php echo htmlspecialchars($COMPANY->name); ?>
                        </p>
                        <p class="mb-1"><strong>Receipt No:</strong>
                            <?php echo htmlspecialchars($ASSIGNMENT->company_receipt_number); ?>
                        </p>
                        <p class="mb-0"><strong>Issued Date:</strong>
                            <?php echo htmlspecialchars($ASSIGNMENT->company_issued_date); ?>
                        </p>
                    </div>
                </div>

                <!-- Assignment Information Details -->
                <h6 class="section-title">Assignment Items</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-centered table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>My Number</th>
                                <th>Customer Name</th>
                                <th>Job Number</th>
                                <th>Belt Design</th>
                                <th>Status</th>
                                <th>Received Date</th>
                                <th>UC Number</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $items = $ASSIGNMENT->getItems();
                            if (count($items) > 0) {
                                foreach ($items as $index => $item) {
                                    $dag_customer = new DagCustomer($item['dag_id']);
                                    $customer = new CustomerMaster($dag_customer->customer_id);
                                    $customer_name = $customer->name . (isset($customer->name_2) ? ' ' . $customer->name_2 : '');
                                    ?>
                                    <tr>
                                        <td>
                                            <?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($dag_customer->my_number); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($customer_name); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($item['job_number']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($item['belt_design']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($item['company_status']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($item['company_received_date']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($item['uc_number']); ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr><td colspan='8' class='text-center'>No items found for this assignment</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
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