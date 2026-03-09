<?php
ob_start();

include '../../class/include.php';

ob_clean();
header('Content-Type: application/json; charset=UTF8');

// Create a new Complaint
if (isset($_POST['create'])) {
    $COMPLAINT = new CustomerComplaint(NULL);

    $COMPLAINT->complaint_no = $_POST['complaint_no'];
    $COMPLAINT->uc_number = $_POST['uc_number'];
    $COMPLAINT->customer_id = $_POST['customer_id'];
    $COMPLAINT->tyre_serial_number = $_POST['tyre_serial_number'];
    $COMPLAINT->fault_description = $_POST['fault_description'];
    $COMPLAINT->complaint_category = $_POST['complaint_category'];
    $COMPLAINT->complaint_date = $_POST['complaint_date'];

    $complaint_id = $COMPLAINT->create();

    if ($complaint_id) {
        echo json_encode([
            'status' => 'success',
            'id' => $complaint_id
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to create complaint.'
        ]);
    }
    exit;
}

// Update Complaint
if (isset($_POST['update'])) {
    $COMPLAINT = new CustomerComplaint($_POST['complaint_id']);

    $COMPLAINT->complaint_no = $_POST['complaint_no'];
    $COMPLAINT->uc_number = $_POST['uc_number'];
    $COMPLAINT->customer_id = $_POST['customer_id'];
    $COMPLAINT->tyre_serial_number = $_POST['tyre_serial_number'];
    $COMPLAINT->fault_description = $_POST['fault_description'];
    $COMPLAINT->complaint_category = $_POST['complaint_category'];
    $COMPLAINT->complaint_date = $_POST['complaint_date'];

    if ($COMPLAINT->update()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Update failed"]);
    }
    exit();
}

// Load complaints for modal
if (isset($_POST['load_complaints'])) {
    $search_term = isset($_POST['search']) ? trim($_POST['search']) : '';

    $COMPLAINT = new CustomerComplaint(null);
    if (!empty($search_term)) {
        $complaints = $COMPLAINT->search($search_term);
    } else {
        $complaints = $COMPLAINT->all();
    }

    $html = '';
    foreach ($complaints as $key => $complaint) {
        $key++;
        $html .= '<tr class="select-complaint" data-id="' . $complaint['id'] . '"
                    data-complaint_no="' . htmlspecialchars($complaint['complaint_no']) . '"
                    data-uc_number="' . htmlspecialchars($complaint['uc_number']) . '"
                    data-customer_id="' . $complaint['customer_id'] . '"
                    data-customer_code="' . htmlspecialchars($complaint['customer_code']) . '"
                    data-customer_name="' . htmlspecialchars($complaint['customer_name']) . '"
                    data-tyre_serial_number="' . htmlspecialchars($complaint['tyre_serial_number']) . '"
                    data-fault_description="' . htmlspecialchars($complaint['fault_description']) . '"
                    data-complaint_category="' . htmlspecialchars($complaint['complaint_category']) . '"
                    data-complaint_date="' . htmlspecialchars($complaint['complaint_date']) . '">
                    <td>' . $key . '</td>
                    <td>' . htmlspecialchars($complaint['complaint_no']) . '</td>
                    <td>' . htmlspecialchars($complaint['uc_number']) . '</td>
                    <td>' . htmlspecialchars($complaint['customer_name']) . '</td>
                    <td>' . htmlspecialchars($complaint['complaint_category']) . '</td>
                    <td>' . htmlspecialchars($complaint['complaint_date']) . '</td>
                </tr>';
    }

    echo json_encode([
        'status' => 'success',
        'html' => $html
    ]);
    exit;
}

// Delete Complaint
if (isset($_POST['delete'])) {
    $complaintId = isset($_POST['complaint_id']) ? (int) $_POST['complaint_id'] : 0;

    if ($complaintId <= 0) {
        echo json_encode(["status" => "error", "message" => "Invalid Complaint ID"]);
        exit();
    }

    $COMPLAINT = new CustomerComplaint($complaintId);

    if (!$COMPLAINT->id) {
        echo json_encode(["status" => "error", "message" => "Complaint not found"]);
        exit();
    }

    if ($COMPLAINT->delete()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete complaint"]);
    }
    exit();
}

// Filter report (for complaint-report.php AJAX)
if (isset($_POST['action']) && $_POST['action'] === 'filter_report') {
    $from_date = isset($_POST['from_date']) ? trim($_POST['from_date']) : '';
    $to_date = isset($_POST['to_date']) ? trim($_POST['to_date']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';
    $company = isset($_POST['company']) ? trim($_POST['company']) : '';

    $COMPLAINT = new CustomerComplaint(null);
    $reports = $COMPLAINT->getFilteredReports($from_date, $to_date, $category, $status, $company);

    echo json_encode([
        'status' => 'success',
        'reports' => $reports
    ]);
    exit;
}
?>