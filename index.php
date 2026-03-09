<!doctype html>
<?php
include 'class/include.php';
include 'auth.php';

$homeViewMode = $COMPANY_PROFILE_DETAILS->home_view_mode ?? 'both';

$navigationLayout = $COMPANY_PROFILE_DETAILS->navigation_layout ?? 'horizontal';
?>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title>Homes | <?php echo $COMPANY_PROFILE_DETAILS->name ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="<?php echo $COMPANY_PROFILE_DETAILS->name; ?>" name="author" />
    <?php include 'main-css.php'; ?>


    <style>
        .dashboard-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            background: linear-gradient(135deg, #ffffff 0%, #f7f9ff 100%);
            position: relative;
            overflow: hidden;
        }

        .dashboard-card .card-body {
            padding: 20px 22px;
        }

        .badge-soft-primary {
            background: rgba(91, 115, 232, 0.15);
            color: #5b73e8;
            border-radius: 50px;
            padding: 6px 12px;
            font-weight: 600;
        }

        .metric-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3541;
            margin: 8px 0;
        }

        .metric-label {
            color: #6c757d;
            font-weight: 600;
            letter-spacing: 0.2px;
        }

        .chart-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .chart-card .card-header {
            background: #fff;
            border-bottom: 1px solid #f1f3f5;
            padding: 16px 20px;
        }

        .chart-card .card-body {
            padding: 16px;
        }

        .chart-legend {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }

        @media (max-width: 992px) {
            .metric-value {
                font-size: 1.6rem;
            }
        }
    </style>

</head>

<body data-layout="<?php echo $navigationLayout; ?>" data-topbar="colored">

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php include 'navigation.php'; ?>


        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">
                    <?php include 'partials/subscription-countdown/subscription-countdown.php'; ?>
                    <?php
                    $ITEM_MASTER = new ItemMaster(NULL);
                    $MESSAGE = new Message(null);

                    $reorderItems = $ITEM_MASTER->checkReorderLevel();

                    if (!empty($reorderItems)) {
                        $customMessages = [];

                        foreach ($reorderItems as $item) {
                            $customMessages[] = "Reorder Alert: <strong>{$item['code']}</strong> - {$item['name']} is below reorder level.";
                        }

                        $MESSAGE->showCustomMessages($customMessages, 'danger');
                    }

                    // Due Date Notifications
                    $db = Database::getInstance();
                    $dueDateColumnCheck = $db->readQuery("SHOW COLUMNS FROM `sales_invoice` LIKE 'due_date'");
                    $hasDueDateColumn = ($dueDateColumnCheck && mysqli_num_rows($dueDateColumnCheck) > 0);

                    if ($hasDueDateColumn) {
                        $query = "SELECT COUNT(*) as total FROM sales_invoice 
                                  WHERE payment_type = 2 AND due_date IS NOT NULL 
                                  AND due_date >= CURDATE() AND due_date <= DATE_ADD(CURDATE(), INTERVAL 2 DAY) 
                                  AND is_cancel = 0";
                        $result = $db->readQuery($query);
                        if ($result) {
                            $row = mysqli_fetch_assoc($result);
                            $totalDueNotifications = $row['total'];
                            if ($totalDueNotifications > 0) {
                                $dueNotifications = ["<a href='customer-outstanding-report.php' class='alert-link'>View {$totalDueNotifications} upcoming due date(s) within 2 days</a>"];
                                echo '<div id="due_date_notification">';
                                $MESSAGE->showCustomMessages($dueNotifications, 'warning');
                                echo '</div>';
                            }
                        }
                    }

                    ?>

                    <!-- Dashboard snapshot -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6 col-lg-3">
                            <div class="card dashboard-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge badge-soft-primary">Monthly Sales</span>
                                        <i class="uil uil-info-circle text-muted"></i>
                                    </div>
                                    <div class="metric-value" id="metric-monthly-sales">Rs. 0</div>
                                    <div class="metric-label">Current month</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="card dashboard-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge badge-soft-primary">Total Stock</span>
                                        <i class="uil uil-package text-muted"></i>
                                    </div>
                                    <div class="metric-value" id="metric-total-stock">0</div>
                                    <div class="metric-label">Units available</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="card dashboard-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge badge-soft-primary">Monthly Profit</span>
                                        <i class="uil uil-line-alt text-muted"></i>
                                    </div>
                                    <div class="metric-value" id="metric-monthly-profit">Rs. 0</div>
                                    <div class="metric-label">After expenses</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="card dashboard-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge badge-soft-primary">Monthly Expenses</span>
                                        <i class="uil uil-receipt text-muted"></i>
                                    </div>
                                    <div class="metric-value" id="metric-monthly-expenses">Rs. 0</div>
                                    <div class="metric-label">Current month</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-lg-8">
                            <div class="card chart-card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Sales Summary</h5>
                                    <div class="text-muted small">Monthly trend</div>
                                </div>
                                <div class="card-body">
                                    <div id="sales-summary-chart" style="height: 320px;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card chart-card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Profit Summary</h5>
                                    <div class="text-muted small">This month</div>
                                </div>
                                <div class="card-body">
                                    <div id="profit-summary-chart" style="height: 320px;"></div>
                                    <div class="mt-3 chart-legend" id="profit-legend"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($homeViewMode !== 'header') { ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Quick Navigation</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php
                                        $PAGE_CATEGORY = new PageCategory(NULL);
                                        $USER_PERMISSION = new UserPermission();
                                        $user_id = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;
                                        foreach ($PAGE_CATEGORY->getActiveCategory() as $category):
                                            $hasCategoryAccess = false;
                                            $firstPage = null;
                                            $PAGES = new Pages(null);
                                            if ($category['id'] == 1) { // Dashboard
                                                $dashboardPages = $PAGES->getPagesByCategory($category['id']);
                                                if (!empty($dashboardPages)) {
                                                    $dashboardPage = $dashboardPages[0];
                                                    $permissions = $USER_PERMISSION->hasPermission($user_id, $dashboardPage['id']);
                                                    if (in_array(true, $permissions, true)) {
                                                        $hasCategoryAccess = true;
                                                        $firstPage = $dashboardPage;
                                                    }
                                                }
                                            } elseif ($category['id'] == 4) { // Reports
                                                // For reports, get the first subpage
                                                $DEFAULT_DATA = new DefaultData();
                                                foreach ($DEFAULT_DATA->pagesSubCategory() as $key => $subCategoryTitle) {
                                                    $subPages = $PAGES->getPagesBySubCategory($key);
                                                    foreach ($subPages as $page) {
                                                        $permissions = $USER_PERMISSION->hasPermission($user_id, $page['id']);
                                                        if (in_array(true, $permissions, true)) {
                                                            $hasCategoryAccess = true;
                                                            $firstPage = $page;
                                                            break 2;
                                                        }
                                                    }
                                                }
                                            } else { // Other categories
                                                $categoryPages = $PAGES->getPagesByCategory($category['id']);
                                                foreach ($categoryPages as $page) {
                                                    $permissions = $USER_PERMISSION->hasPermission($user_id, $page['id']);
                                                    if (in_array(true, $permissions, true)) {
                                                        $hasCategoryAccess = true;
                                                        $firstPage = $page;
                                                        break;
                                                    }
                                                }
                                            }
                                            if ($hasCategoryAccess && $firstPage):
                                        ?>
                                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                            <a href="<?php echo strtolower(str_replace(' ', '-', $category['name'])) . '-tab.php?category_id=' . $category['id']; ?>" class="btn btn-outline-primary btn-lg w-100 d-flex align-items-center justify-content-start gp-tile-btn">
                                                <i class="<?php echo $category['icon']; ?> me-3 gp-tile-icon"></i> <?php echo $category['name']; ?>
                                            </a>
                                        </div>
                                        <?php
                                            endif;
                                        endforeach;
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    
                </div> <!-- container-fluid -->
            </div>
            <!-- End Page-content -->


            <?php include 'footer.php' ?>

        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->



    <!-- Right bar overlay-->
    <div class="rightbar-overlay"></div>

    <!-- JAVASCRIPT -->
    <script src="assets/libs/jquery/jquery.min.js"></script>
    <script src="ajax/js/common.js"></script>

    <!-- ApexCharts -->
    <script src="assets/libs/apexcharts/apexcharts.min.js"></script>

    <!-- include main js  -->
    <?php include 'main-js.php' ?>

    <script src="assets/libs/Simple-Countdown-Periodic-Timer-Plugin-With-jQuery-SyoTimer/Simple-Countdown-Periodic-Timer-Plugin-With-jQuery-SyoTimer/build/jquery.syotimer.min.js"></script>
    <script src="partials/subscription-countdown/ajax/js/subscription-countdown.js"></script>

    <!-- Dashboard init -->
    <script src="assets/js/pages/dashboard.init.js"></script>

</body>

</html>