<?php
include '../../class/include.php';

if (!isset($_SESSION)) {
    session_start();
}

$action = $_POST['action'] ?? '';

if ($action === 'filter_report') {
    $DAG_CUSTOMER = new DagCustomer(NULL);

    $filters = [
        'date_from' => $_POST['date_from'] ?? '',
        'date_to' => $_POST['date_to'] ?? '',
        'customer' => $_POST['customer'] ?? '',
        'company' => $_POST['company'] ?? '',
        'brand' => $_POST['brand'] ?? '',
        'invoice_status' => $_POST['invoice_status'] ?? ''
    ];

    $data = $DAG_CUSTOMER->getReportData($filters);

    echo json_encode(['status' => 'success', 'data' => $data]);
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
exit();
?>