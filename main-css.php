<?php
// Get company profile for favicon and theme
if (!isset($COMPANY_PROFILE)) {
    $COMPANY_PROFILE = new CompanyProfile(1);
}

// Set default theme color if not set
$themeColor = !empty($COMPANY_PROFILE->theme) ? $COMPANY_PROFILE->theme : '#3b5de7';
?>
<!-- Favicon -->
<link rel="shortcut icon" href="<?php echo !empty($COMPANY_PROFILE->favicon) ? 'uploads/company-logos/' . $COMPANY_PROFILE->favicon : 'assets/images/favicon.ico'; ?>" type="image/x-icon">
<link rel="icon" type="image/x-icon" href="<?php echo !empty($COMPANY_PROFILE->favicon) ? 'uploads/company-logos/' . $COMPANY_PROFILE->favicon : 'assets/images/favicon.ico'; ?>">

<!-- Theme Color Variables -->
<style>
    :root {
        --bs-primary: <?php echo $themeColor; ?>;
        --bs-primary-rgb: <?php echo implode(', ', sscanf($themeColor, "#%02x%02x%02x")); ?>;
        --bs-primary-hover: <?php echo adjustBrightness($themeColor, -10); ?>;
        --bs-primary-active: <?php echo adjustBrightness($themeColor, -20); ?>;
    }

    /* Style the color picker */
    .form-control-color {
        height: calc(1.5em + 0.9rem + 2px);
        padding: 0.375rem 0.375rem;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }

    .form-control-color:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25);
    }
</style>

<?php
// Helper function to adjust color brightness
function adjustBrightness($hex, $steps)
{
    // Remove # if present
    $hex = str_replace('#', '', $hex);

    // Convert to RGB
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    // Adjust brightness
    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));

    // Convert back to hex
    return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT)
        . str_pad(dechex($g), 2, '0', STR_PAD_LEFT)
        . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
}
?>

<!-- Bootstrap Css -->
<link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
<!-- Icons Css -->
<link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
<!-- App Css-->
<link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">

<link href="assets/libs/sweetalert/sweetalert.css" rel="stylesheet" type="text/css" />

<link href="assets/css/preloader.css" rel="stylesheet" type="text/css" />
<!-- Responsive datatable examples -->
<!-- DataTables -->
<link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
<link href="assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
<link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />