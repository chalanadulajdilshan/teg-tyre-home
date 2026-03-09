<?php
$companyId = 1;
if (isset($COMPANY_PROFILE_DETAILS) && !empty($COMPANY_PROFILE_DETAILS->id)) {
    $companyId = (int) $COMPANY_PROFILE_DETAILS->id;
}

$COMPANY = new CompanyProfile($companyId);
$logoPath = !empty($COMPANY->image_name) ? 'uploads/company-logos/' . $COMPANY->image_name : 'assets/images/logo.png';
$themeColor = !empty($COMPANY->theme) ? $COMPANY->theme : '#3b5de7';
$homeViewMode = $COMPANY->home_view_mode ?? 'both';

$showTopNav = ($homeViewMode === 'both' || $homeViewMode === 'header');

$navigationLayout = $COMPANY->navigation_layout ?? 'horizontal';

$dashboardHref = 'index.php';
if ($homeViewMode === 'nav_buttons' || $homeViewMode === 'header') {
    $userId = isset($_SESSION['id']) ? (int) $_SESSION['id'] : 0;
    if ($userId > 0) {
        $PAGES = new Pages(null);
        $dashboardPages = $PAGES->getPagesByCategory(1);
        $USER_PERMISSION = new UserPermission();
        foreach ($dashboardPages as $page) {
            $permissions = $USER_PERMISSION->hasPermission($userId, $page['id']);
            if (in_array(true, $permissions, true)) {
                $dashboardHref = $page['page_url'] . '?page_id=' . $page['id'];
                break;
            }
        }
    }
}
?>

<?php if ($homeViewMode === 'nav_buttons') { ?>
    <style>
        #page-topbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.18);
        }

        #page-topbar .navbar-header {
            height: 64px;
            padding: 0 12px;
        }

        #page-topbar .navbar-brand-box.mt-3 {
            margin-top: 0 !important;
        }

        #page-topbar .navbar-brand-box {
            height: 64px;
            display: flex;
            align-items: center;
        }

        #page-topbar .logo-lg img {
            height: 44px !important;
        }

        #page-topbar .logo-sm img {
            height: 38px !important;
        }

        #page-topbar .d-flex.mt-20 {
            margin-top: 0 !important;
            align-items: center;
        }

        #dashboard-back-btn {
            color: #fff;
            height: 36px;
            padding: 0 12px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            background: rgba(255, 255, 255, 0.10);
        }

        #dashboard-back-btn:hover,
        #dashboard-back-btn:focus {
            color: #fff;
            background: rgba(255, 255, 255, 0.18);
            border-color: rgba(255, 255, 255, 0.35);
        }

        body[data-layout="horizontal"] .page-content {
            margin-top: 0 !important;
            padding-top: calc(64px + 1.25rem) !important;
        }

        @media (max-width: 991.98px) {
            body[data-layout="horizontal"] .page-content {
                margin-top: 0 !important;
                padding-top: calc(64px + 1.25rem) !important;
            }
        }
    </style>
<?php } ?>

<?php if ($navigationLayout === 'vertical'): ?>
    <!-- ========== Left Sidebar Start ========== -->
    <div class="vertical-menu">
        <div class="navbar-brand-box">
            <button type="button" class="btn btn-sm px-3 font-size-16 vertical-menu-btn header-item waves-effect waves-light d-lg-none" style="color: white; position: absolute; left: 10px;">
                <i class="fa fa-fw fa-bars"></i>
            </button>
            <a href="index.php" class="logo logo-dark">
                <span class="logo-sm">
                    <img src="<?php echo $logoPath; ?>" alt="" height="24">
                </span>
                <span class="logo-lg">
                    <img src="<?php echo $logoPath; ?>" alt="" height="44">
                </span>
            </a>
            <a href="index.php" class="logo logo-light">
                <span class="logo-sm">
                    <img src="<?php echo $logoPath; ?>" alt="" height="24">
                </span>
                <span class="logo-lg">
                    <img src="<?php echo $logoPath; ?>" alt="" height="44">
                </span>
            </a>
        </div>

        <div data-simplebar class="h-100">
            <!--- Sidemenu -->
            <div id="sidebar-menu">
                <!-- Left Menu Start -->
                <ul class="metismenu list-unstyled" id="side-menu">
                    <li class="menu-title">Menu</li>
                    <?php
                    $PAGE_CATEGORY = new PageCategory(NULL);
                    $USER_PERMISSION = new UserPermission();
                    $user_id = isset($_SESSION['id']) ? (int) $_SESSION['id'] : 0;

                    foreach ($PAGE_CATEGORY->getActiveCategory() as $category):
                        $hasCategoryAccess = false;
                        $categoryPages = [];

                        if ($category['id'] != 1) {
                            $PAGES = new Pages(null);
                            $categoryPages = $PAGES->getPagesByCategory($category['id']);
                            foreach ($categoryPages as $page) {
                                $permissions = $USER_PERMISSION->hasPermission($user_id, $page['id']);
                                if (in_array(true, $permissions, true)) {
                                    $hasCategoryAccess = true;
                                    break;
                                }
                            }
                        }

                        if (!$hasCategoryAccess && $category['id'] != 1) continue;

                        if ($category['id'] == 1): // Dashboard
                            $dashboardPage = (new Pages(null))->getPagesByCategory($category['id'])[0] ?? null;
                            if ($dashboardPage):
                                $permissions = $USER_PERMISSION->hasPermission($user_id, $dashboardPage['id']);
                                if (in_array(true, $permissions, true)): ?>
                                    <li>
                                        <a href="<?php echo $dashboardPage['page_url'] . '?page_id=' . $dashboardPage['id']; ?>" class="waves-effect">
                                            <i class="<?php echo $category['icon']; ?>"></i>
                                            <span><?php echo $category['name']; ?></span>
                                        </a>
                                    </li>
                                <?php endif;
                            endif;
                        elseif ($category['id'] == 4): // Reports
                            $reportSubmenus = [];
                            $DEFAULT_DATA = new DefaultData();
                            foreach ($DEFAULT_DATA->pagesSubCategory() as $key => $subCategoryTitle) {
                                $PAGES = new Pages(null);
                                $subPages = $PAGES->getPagesBySubCategory($key);
                                foreach ($subPages as $page) {
                                    $permissions = $USER_PERMISSION->hasPermission($user_id, $page['id']);
                                    if (in_array(true, $permissions, true)) {
                                        if (!isset($reportSubmenus[$key])) {
                                            $reportSubmenus[$key] = ['title' => $subCategoryTitle, 'pages' => []];
                                        }
                                        $reportSubmenus[$key]['pages'][] = $page;
                                    }
                                }
                            }
                            if (!empty($reportSubmenus)): ?>
                                <li>
                                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                                        <i class="uil-layers"></i>
                                        <span>Reports</span>
                                    </a>
                                    <ul class="sub-menu" aria-expanded="false">
                                        <li class="submenu-title-wrapper"><h5 class="submenu-title">Reports</h5></li>
                                        <?php foreach ($reportSubmenus as $submenu): ?>
                                            <li>
                                                <a href="javascript: void(0);" class="has-arrow"><?php echo $submenu['title']; ?></a>
                                                <ul class="sub-menu" aria-expanded="false">
                                                    <?php foreach ($submenu['pages'] as $page): ?>
                                                        <li>
                                                            <a href="<?php echo $page['page_url'] . '?page_id=' . $page['id']; ?>">
                                                                <i class="<?php echo !empty($page['page_icon']) ? $page['page_icon'] : 'uil uil-circle'; ?> sub-icon"></i>
                                                                <span><?php echo $page['page_name']; ?></span>
                                                            </a>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php endif;
                        else: // Other Categories
                            $visiblePages = [];
                            foreach ($categoryPages as $page) {
                                if (basename($page['page_url']) === 'profile.php' || in_array(true, $USER_PERMISSION->hasPermission($user_id, $page['id']), true)) {
                                    $visiblePages[] = $page;
                                }
                            }
                            if (!empty($visiblePages)): ?>
                                <li>
                                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                                        <i class="<?php echo $category['icon']; ?>"></i>
                                        <span><?php echo $category['name']; ?></span>
                                    </a>
                                    <ul class="sub-menu" aria-expanded="false">
                                        <li class="submenu-title-wrapper"><h5 class="submenu-title"><?php echo $category['name']; ?></h5></li>
                                        <?php foreach ($visiblePages as $page): ?>
                                            <li>
                                                <a href="<?php echo $page['page_url'] . '?page_id=' . $page['id']; ?>">
                                                    <i class="<?php echo !empty($page['page_icon']) ? $page['page_icon'] : 'uil uil-circle'; ?> sub-icon"></i>
                                                    <span><?php echo $page['page_name']; ?></span>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php endif;
                        endif;
                    endforeach; ?>
                </ul>
            </div>
            <!-- Sidebar -->
        </div>
    </div>
    <!-- Left Sidebar End -->
    <style>
        /* Modern Sidebar Styles */
        @media (min-width: 992px) {
            .vertical-menu {
                background: #ffffff !important;
                border-right: 1px solid #f1f3f5;
                transition: all 0.3s ease;
                width: 250px !important;
                z-index: 1002;
            }

            /* Ensure content and topbar align with sidebar width */
            #page-topbar {
                left: 250px !important;
                right: 0 !important;
                width: auto !important;
                transition: all 0.3s ease;
                position: fixed;
                top: 0;
                z-index: 1001;
            }

            .main-content {
                margin-left: 250px !important;
                transition: all 0.3s ease;
            }

            body.vertical-collpsed #page-topbar {
                left: 70px !important;
            }

            body.vertical-collpsed .main-content {
                margin-left: 70px !important;
            }

            /* Border fix for blue brand box area */
            .navbar-brand-box {
                background: <?php echo $themeColor; ?> !important;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                padding: 0 24px;
                height: 70px;
                display: flex;
                align-items: center;
                transition: all 0.3s ease;
                width: 250px !important;
                z-index: 1003;
                position: fixed;
                top: 0;
                left: 0;
                box-shadow: none !important;
            }

            /* Remove white border from the brand box section of sidebar */
            body:not(.vertical-collpsed) .navbar-brand-box::after {
                content: "";
                position: absolute;
                right: 0;
                top: 0;
                bottom: 0;
                width: 1px;
                background: rgba(255, 255, 255, 0.1);
            }
        }

        @media (max-width: 991px) {
            #page-topbar {
                left: 0 !important;
                width: 100% !important;
                z-index: 1004 !important; /* Below sidebar but above content */
            }

            .main-content {
                margin-left: 0 !important;
            }

            .vertical-menu {
                top: 0 !important;
                z-index: 1006 !important; /* Above everything */
            }

            /* Show the toggle button inside the sidebar on mobile */
            .vertical-menu .navbar-brand-box {
                display: flex !important;
                width: 100% !important;
                position: relative !important;
                background: <?php echo $themeColor; ?> !important;
                justify-content: center;
            }
        }

        /* Collapsed Sidebar State */
        body.vertical-collpsed .vertical-menu {
            width: 70px !important;
        }

        body.vertical-collpsed .navbar-brand-box {
            width: 70px !important;
            padding: 0 !important;
            justify-content: center;
        }

        body.vertical-collpsed .logo-lg,
        body.vertical-collpsed .logo-sm {
            display: none !important;
        }

        body.vertical-collpsed #sidebar-menu ul li a {
            padding: 10px !important;
            justify-content: center !important;
        }

        body.vertical-collpsed #sidebar-menu ul li a i {
            margin-right: 0 !important;
            font-size: 1.25rem !important;
        }

        /* Hide text only in the main level link of collapsed sidebar */
        body.vertical-collpsed #sidebar-menu > ul > li > a > span,
        body.vertical-collpsed #sidebar-menu .has-arrow:after,
        body.vertical-collpsed .menu-title {
            display: none !important;
        }

        /* Base Sidebar Menu Spacing */
        #sidebar-menu {
            padding: 30px 12px 20px !important;
        }

        /* Desktop specific spacing to clear fixed header */
        @media (min-width: 992px) {
            #sidebar-menu {
                padding-top: 100px !important; /* 70px header + 30px margin */
            }
            
            body.vertical-collpsed #sidebar-menu {
                padding-top: 90px !important;
            }

            body[data-layout="vertical"]:not(.vertical-collpsed) .navbar-header .navbar-brand-box {
                display: none !important;
            }
        }

        #sidebar-menu ul li a {
            border-radius: 12px !important;
            margin: 4px 8px !important;
            padding: 10px 16px !important;
            color: #5d6778 !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            font-weight: 500 !important;
            display: flex;
            align-items: center;
        }

        #sidebar-menu ul li a i {
            font-size: 1.1rem !important;
            margin-right: 12px !important;
            color: #7b8190 !important;
            transition: color 0.3s ease !important;
        }

        #sidebar-menu ul li a:hover {
            background: rgba(var(--bs-primary-rgb), 0.08) !important;
            color: var(--bs-primary) !important;
        }

        #sidebar-menu ul li a:hover i {
            color: var(--bs-primary) !important;
        }

        #sidebar-menu ul li.mm-active > a {
            background: var(--bs-primary) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.3) !important;
            position: relative;
        }

        #sidebar-menu ul li.mm-active > a::before {
            content: "";
            position: absolute;
            left: -12px;
            top: 15%;
            height: 70%;
            width: 4px;
            background: var(--bs-primary);
            border-radius: 0 4px 4px 0;
        }

        #sidebar-menu ul li.mm-active > a i {
            color: #ffffff !important;
        }

        #sidebar-menu .has-arrow:after {
            content: "\F0142" !important; /* mdi-chevron-right */
            font-family: 'Material Design Icons' !important;
            font-size: 1.1rem;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            transition: transform 0.2s ease;
            opacity: 0.5;
            display: inline-block;
        }

        #sidebar-menu li.mm-active > .has-arrow:after {
            transform: translateY(-50%) rotate(90deg) !important;
            opacity: 1 !important;
            color: var(--bs-primary);
        }

        #sidebar-menu ul li ul.sub-menu {
            padding: 4px 0 4px 28px;
        }

        #sidebar-menu ul li ul.sub-menu li a {
            padding: 8px 16px;
            font-size: 0.9rem;
            border-radius: 8px;
            background: transparent;
            color: #6c757d;
        }

        #sidebar-menu ul li ul.sub-menu li a:hover {
            color: var(--bs-primary);
            background: rgba(var(--bs-primary-rgb), 0.05);
        }

        .sub-icon {
            font-size: 0.85rem !important;
            margin-right: 10px !important;
            color: #adb5bd !important;
            opacity: 0.7;
            transition: all 0.3s ease;
            width: 14px;
            display: inline-block;
            text-align: center;
        }

        #sidebar-menu ul li ul.sub-menu li a:hover .sub-icon {
            color: var(--bs-primary) !important;
            opacity: 1;
        }

        /* Collapsed Sub-menu Popup Styles */
        body.vertical-collpsed .vertical-menu #sidebar-menu ul li:hover > ul {
            display: block;
            left: 70px;
            position: absolute;
            width: 220px; /* Increased width */
            height: auto !important;
            box-shadow: 3px 5px 12px rgba(0, 0, 0, 0.12);
            border: 1px solid #f1f3f5;
            background: #ffffff;
            padding: 0 !important; /* Reset padding for alignment */
            border-radius: 0 12px 12px 0;
            z-index: 1010;
        }

        .submenu-title-wrapper {
            background: #f8f9fa;
            padding: 12px 15px;
            border-bottom: 1px solid #f1f3f5;
            border-radius: 0 12px 0 0;
            display: none; /* Hidden by default (expanded mode) */
        }

        body.vertical-collpsed .submenu-title-wrapper {
            display: block; /* Show only in collapsed mode popup */
        }

        .submenu-title {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
            color: var(--bs-primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        body.vertical-collpsed .vertical-menu #sidebar-menu ul li:hover > ul > li > a {
            padding: 10px 15px !important;
            display: flex;
            align-items: center;
            justify-content: flex-start !important; /* Force left align */
        }

        body.vertical-collpsed .vertical-menu #sidebar-menu ul li:hover > ul > li > a span {
            display: inline-block !important;
            margin-left: 0;
            color: #5d6778;
            font-size: 13px;
        }

        body.vertical-collpsed .vertical-menu #sidebar-menu ul li:hover > ul > li > a i {
            margin-right: 12px !important;
            font-size: 1rem !important;
        }

        .menu-title {
            color: #adb5bd !important;
            font-weight: 700 !important;
            padding: 12px 16px 8px !important;
            font-size: 10px !important;
            letter-spacing: 1px !important;
        }

        /* Topbar Toggle Refinement */
        #vertical-menu-btn {
            background: rgba(255, 255, 255, 0.15) !important;
            border-radius: 10px !important;
            width: 38px !important;
            height: 38px !important;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 10px;
            transition: all 0.2s ease;
        }

        #vertical-menu-btn:hover {
            background: rgba(255, 255, 255, 0.25) !important;
        }

        #page-topbar {
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1) !important;
        }
    </style>
<?php endif; ?>

<header id="page-topbar" style="background-color: <?php echo $themeColor; ?>">
    <div class="navbar-header">
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-sm px-3 font-size-16 <?php echo $navigationLayout === 'vertical' ? 'vertical-menu-btn' : 'd-lg-none'; ?> header-item waves-effect waves-light"
                <?php echo $navigationLayout === 'vertical' ? 'id="vertical-menu-btn"' : 'data-bs-toggle="collapse" data-bs-target="#topnav-menu-content"'; ?> style="color: white; z-index: 1003;">
                <i class="fa fa-fw fa-bars"></i>
            </button>

            <!-- Logo for Mobile (Both Layouts) & Desktop (Horizontal) -->
            <div class="navbar-brand-box mt-0 d-flex align-items-center d-lg-none" style="background: transparent; position: relative; width: auto; padding-left: 10px; box-shadow: none !important;">
                <a href="index.php" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="<?php echo $logoPath; ?>" alt="" height="32">
                    </span>
                    <span class="logo-lg <?php echo $navigationLayout === 'vertical' ? 'd-lg-none' : ''; ?>">
                        <img src="<?php echo $logoPath; ?>" alt="" height="44">
                    </span>
                </a>
                <a href="index.php" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="<?php echo $logoPath; ?>" alt="" height="32">
                    </span>
                    <span class="logo-lg <?php echo $navigationLayout === 'vertical' ? 'd-lg-none' : ''; ?>">
                        <img src="<?php echo $logoPath; ?>" alt="" height="44">
                    </span>
                </a>
            </div>
        </div>

        <div class="d-flex mt-20">
            <!-- Search -->
            <div class="dropdown d-inline-block d-lg-none ms-2">
                <button class="btn header-item noti-icon waves-effect" data-bs-toggle="dropdown">
                    <i class="uil-search"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0">
                    <form class="p-3">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search ...">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="mdi mdi-magnify"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($homeViewMode === 'nav_buttons' || $homeViewMode === 'header') { ?>
                <a href="<?php echo $dashboardHref; ?>" id="dashboard-back-btn"
                    class="btn btn-sm d-flex align-items-center waves-effect" title="Dashboard" aria-label="Dashboard">
                    <i class="uil uil-estate"></i>
                    <span class="ms-1 d-none d-md-inline">Dashboard</span>
                </a>
            <?php } ?>

            <!-- Fullscreen -->
            <div class="dropdown d-none d-lg-inline-block ms-1">
                <button type="button" class="btn header-item noti-icon waves-effect" data-bs-toggle="fullscreen">
                    <i class="uil-minus-path"></i>
                </button>
            </div>

            <!-- Notifications -->
            <div class="dropdown d-inline-block">
                <button class="btn header-item noti-icon waves-effect" data-bs-toggle="dropdown">
                    <i class="uil-bell"></i>
                    <span class="badge bg-danger rounded-pill">3</span>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0">
                    <div class="p-3">
                        <div class="d-flex justify-content-between">
                            <h5 class="m-0 font-size-16">Notifications</h5>
                            <a href="#" class="small">Mark all as read</a>
                        </div>
                    </div>
                    <div data-simplebar style="max-height: 230px;">
                        <!-- Dynamic notifications can be loaded here -->
                    </div>
                    <div class="p-2 border-top text-center">
                        <a href="#" class="btn btn-sm btn-link font-size-14">
                            <i class="uil-arrow-circle-right me-1"></i> View More..
                        </a>
                    </div>
                </div>
            </div>

            <!-- User -->
            <div class="dropdown d-inline-block">
                <button class="btn header-item waves-effect" data-bs-toggle="dropdown">
                    <?php
                    $user = new User($_SESSION['id']);
                    $profileImage = !empty($user->image_name) ? 'upload/users/' . $user->image_name : 'assets/images/users/avatar-4.jpg';
                    ?>
                    <img class="rounded-circle header-profile-user" src="<?php echo $profileImage; ?>"
                        alt="<?php echo htmlspecialchars($user->name); ?>">
                    <span
                        class="d-none d-xl-inline-block ms-1 fw-medium font-size-15"><?php echo htmlspecialchars($user->name); ?></span>
                    <i class="uil-angle-down d-none d-xl-inline-block font-size-15"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="profile.php"><i class="uil uil-user-circle me-1"></i> View
                        Profile</a>
                    <a class="dropdown-item" href="#"><i class="uil uil-lock-alt me-1"></i> Settings </a>
                    <a class="dropdown-item" href="log-out.php"><i class="uil uil-sign-out-alt me-1"></i> Sign out</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <?php if ($navigationLayout === 'horizontal' && $showTopNav) { ?>
        <div class="container-fluid">
            <div class="topnav">
                <nav class="navbar navbar-light navbar-expand-lg topnav-menu">
                    <div class="collapse navbar-collapse" id="topnav-menu-content">
                        <ul class="navbar-nav">
                            <?php
                            $PAGE_CATEGORY = new PageCategory(NULL);
                            $USER_PERMISSION = new UserPermission();
                            $user_id = isset($_SESSION['id']) ? (int) $_SESSION['id'] : 0;

                            foreach ($PAGE_CATEGORY->getActiveCategory() as $category):
                                $hasCategoryAccess = false;
                                $categoryPages = [];

                                // Get all pages for this category first to check permissions
                                if ($category['id'] != 1) { // Skip dashboard for now
                                    $PAGES = new Pages(null);
                                    $categoryPages = $PAGES->getPagesByCategory($category['id']);

                                    // Check if user has any permission for any page in this category
                                    foreach ($categoryPages as $page) {
                                        $permissions = $USER_PERMISSION->hasPermission($user_id, $page['id']);
                                        if (in_array(true, $permissions, true)) {
                                            $hasCategoryAccess = true;
                                            break;
                                        }
                                    }
                                }

                                // Skip category if user has no permissions for any page in it
                                if (!$hasCategoryAccess && $category['id'] != 1) {
                                    continue;
                                }

                                if ($category['id'] == 1): // Dashboard
                                    $dashboardPage = (new Pages(null))->getPagesByCategory($category['id'])[0] ?? null;
                                    if ($dashboardPage):
                                        $permissions = $USER_PERMISSION->hasPermission($user_id, $dashboardPage['id']);
                                        if (in_array(true, $permissions, true)): ?>
                                            <li class="nav-item">
                                                <a class="nav-link"
                                                    href="<?php echo $dashboardPage['page_url'] . '?page_id=' . $dashboardPage['id']; ?>">
                                                    <i class="<?php echo $category['icon']; ?> me-2"></i> <?php echo $category['name']; ?>
                                                </a>
                                            </li>
                                            <?php
                                        endif;
                                    endif;
                                elseif ($category['id'] == 4): // Reports Category
                                    $hasReportAccess = false;
                                    $reportSubmenus = [];
                                    $DEFAULT_DATA = new DefaultData();

                                    // First check if user has any report access
                                    foreach ($DEFAULT_DATA->pagesSubCategory() as $key => $subCategoryTitle) {
                                        $PAGES = new Pages(null);
                                        $subPages = $PAGES->getPagesBySubCategory($key);

                                        foreach ($subPages as $page) {
                                            $permissions = $USER_PERMISSION->hasPermission($user_id, $page['id']);
                                            if (in_array(true, $permissions, true)) {
                                                $hasReportAccess = true;
                                                if (!isset($reportSubmenus[$key])) {
                                                    $reportSubmenus[$key] = [
                                                        'title' => $subCategoryTitle,
                                                        'pages' => []
                                                    ];
                                                }
                                                $reportSubmenus[$key]['pages'][] = $page;
                                            }
                                        }
                                    }

                                    if ($hasReportAccess): ?>
                                        <li class="nav-item dropdown">
                                            <a class="nav-link dropdown-toggle arrow-none" href="#" role="button">
                                                <i class="uil-layers me-2"></i> Reports <div class="arrow-down"></div>
                                            </a>
                                            <div class="dropdown-menu">
                                                <?php foreach ($reportSubmenus as $key => $submenu):
                                                    if (!empty($submenu['pages'])): ?>
                                                        <div class="dropdown">
                                                            <a class="dropdown-item dropdown-toggle arrow-none" href="#">
                                                                <?php echo $submenu['title']; ?>
                                                                <div class="arrow-down"></div>
                                                            </a>
                                                            <div class="dropdown-menu">
                                                                <?php foreach ($submenu['pages'] as $page):
                                                                    $permissions = $USER_PERMISSION->hasPermission($user_id, $page['id']);
                                                                    if (in_array(true, $permissions, true)): ?>
                                                                        <a class="dropdown-item"
                                                                            href="<?php echo $page['page_url'] . '?page_id=' . $page['id']; ?>">
                                                                            <?php if (!empty($page['page_icon'])): ?>
                                                                                <i class="<?php echo htmlspecialchars($page['page_icon']); ?> me-2"></i>
                                                                            <?php endif; ?>
                                                                            <?php echo $page['page_name']; ?>
                                                                        </a>
                                                                    <?php endif;
                                                                endforeach; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif;
                                                endforeach; ?>
                                            </div>
                                        </li>
                                        <?php
                                    endif;
                                else: // Other Categories
                                    $hasAnyPermission = false;
                                    $visiblePages = [];

                                    // Filter pages to only those the user has permission for
                                    foreach ($categoryPages as $page) {
                                        // Always allow access to profile.php for logged-in users
                                        if (basename($page['page_url']) === 'profile.php') {
                                            $visiblePages[] = $page;
                                            $hasAnyPermission = true;
                                            continue;
                                        }

                                        // Check permissions for other pages
                                        $permissions = $USER_PERMISSION->hasPermission($user_id, $page['id']);
                                        if (in_array(true, $permissions, true)) {
                                            $visiblePages[] = $page;
                                            $hasAnyPermission = true;
                                        }
                                    }

                                    if ($hasAnyPermission): ?>
                                        <li class="nav-item dropdown">
                                            <a class="nav-link dropdown-toggle arrow-none" href="#" role="button">
                                                <i class="<?php echo $category['icon']; ?> me-2"></i> <?php echo $category['name']; ?>
                                                <div class="arrow-down"></div>
                                            </a>
                                            <?php if (count($visiblePages) <= 4): ?>
                                                <div class="dropdown-menu">
                                                    <?php foreach ($visiblePages as $page):
                                                        $permissions = $USER_PERMISSION->hasPermission($user_id, $page['id']);
                                                        if (in_array(true, $permissions, true)): ?>
                                                            <a class="dropdown-item"
                                                                href="<?php echo $page['page_url'] . '?page_id=' . $page['id']; ?>">
                                                                <?php if (!empty($page['page_icon'])): ?>
                                                                    <i class="<?php echo htmlspecialchars($page['page_icon']); ?> me-2"></i>
                                                                <?php endif; ?>
                                                                <?php echo $page['page_name']; ?>
                                                            </a>
                                                        <?php endif;
                                                    endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="dropdown-menu mega-dropdown-menu px-2 dropdown-mega-menu-xl">
                                                    <div class="row">
                                                        <?php foreach ($visiblePages as $page):
                                                            $permissions = $USER_PERMISSION->hasPermission($user_id, $page['id']);
                                                            if (in_array(true, $permissions, true)): ?>
                                                                <div class="col-lg-3">
                                                                    <a class="dropdown-item"
                                                                        href="<?php echo $page['page_url'] . '?page_id=' . $page['id']; ?>">
                                                                        <?php if (!empty($page['page_icon'])): ?>
                                                                            <i class="<?php echo htmlspecialchars($page['page_icon']); ?> me-2"></i>
                                                                        <?php endif; ?>
                                                                        <?php echo $page['page_name']; ?>
                                                                    </a>
                                                                </div>
                                                            <?php endif;
                                                        endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </li>
                                        <?php
                                    endif;
                                endif;
                            endforeach; ?>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    <?php } ?>
</header>