<?php

include '../../class/include.php';

if (isset($_POST['create'])) {

    $COMPANY_HANDLING = new CompanyHandling(NULL);

    $COMPANY_HANDLING->complaint_id = $_POST['complaint_id'];
    $COMPANY_HANDLING->company_number = $_POST['company_number'];
    $COMPANY_HANDLING->company_name = $_POST['company_name'];
    $COMPANY_HANDLING->sent_date = $_POST['sent_date'];
    $COMPANY_HANDLING->company_status = $_POST['company_status'];

    // New fields
    $COMPANY_HANDLING->price_amount = $_POST['price_amount'];
    $COMPANY_HANDLING->price_issued_date = $_POST['price_issued_date'];
    $COMPANY_HANDLING->issued_invoice_number = $_POST['issued_invoice_number'];
    $COMPANY_HANDLING->rejection_reason = $_POST['rejection_reason'];
    $COMPANY_HANDLING->rejection_date = $_POST['rejection_date'];
    $COMPANY_HANDLING->company_invoice_number = $_POST['company_invoice_number'];
    $COMPANY_HANDLING->received_invoice_number = $_POST['received_invoice_number'];
    $COMPANY_HANDLING->special_remark = $_POST['special_remark'];
    $COMPANY_HANDLING->special_request_date = $_POST['special_request_date'];
    $COMPANY_HANDLING->status_remark = $_POST['status_remark'];
    $COMPANY_HANDLING->general_remark = $_POST['general_remark'];

    $result = $COMPANY_HANDLING->create();
    if ($result) {
        $data = array("status" => "success");
        echo json_encode($data);
    } else {
        $data = array("status" => "error");
        echo json_encode($data);
    }
}

if (isset($_POST['update'])) {

    $COMPANY_HANDLING = new CompanyHandling($_POST['id']);

    $COMPANY_HANDLING->complaint_id = $_POST['complaint_id'];
    $COMPANY_HANDLING->company_number = $_POST['company_number'];
    $COMPANY_HANDLING->company_name = $_POST['company_name'];
    $COMPANY_HANDLING->sent_date = $_POST['sent_date'];
    $COMPANY_HANDLING->company_status = $_POST['company_status'];

    // New fields
    $COMPANY_HANDLING->price_amount = $_POST['price_amount'];
    $COMPANY_HANDLING->price_issued_date = $_POST['price_issued_date'];
    $COMPANY_HANDLING->issued_invoice_number = $_POST['issued_invoice_number'];
    $COMPANY_HANDLING->rejection_reason = $_POST['rejection_reason'];
    $COMPANY_HANDLING->rejection_date = $_POST['rejection_date'];
    $COMPANY_HANDLING->company_invoice_number = $_POST['company_invoice_number'];
    $COMPANY_HANDLING->received_invoice_number = $_POST['received_invoice_number'];
    $COMPANY_HANDLING->special_remark = $_POST['special_remark'];
    $COMPANY_HANDLING->special_request_date = $_POST['special_request_date'];
    $COMPANY_HANDLING->status_remark = $_POST['status_remark'];
    $COMPANY_HANDLING->general_remark = $_POST['general_remark'];

    $result = $COMPANY_HANDLING->update();

    if ($result) {
        $data = array("status" => "success");
        echo json_encode($data);
    } else {
        $data = array("status" => "error");
        echo json_encode($data);
    }
}

if (isset($_POST['delete'])) {

    $COMPANY_HANDLING = new CompanyHandling($_POST['id']);

    $result = $COMPANY_HANDLING->delete();

    if ($result) {
        $data = array("status" => "success");
        echo json_encode($data);
    } else {
        $data = array("status" => "error");
        echo json_encode($data);
    }
}

if (isset($_POST['get_complaint_details'])) {

    $COMPLAINT = new CustomerComplaint($_POST['complaint_id']);

    $data = array(
        "status" => "success",
        "uc_number" => $COMPLAINT->uc_number,
        "fault_description" => $COMPLAINT->fault_description
    );
    echo json_encode($data);
}

if (isset($_POST['load_complaints'])) {
    $COMPANY_HANDLING = new CompanyHandling(NULL);
    $result = $COMPANY_HANDLING->getComplaintsWithDetails();

    $html = '';
    if (!empty($result)) {
        foreach ($result as $key => $complaint) {
            $key++;

            // Define customer name early
            $customer_name = isset($complaint['customer_name']) ? $complaint['customer_name'] : '-';

            // Prepare raw values for data attributes
            $handling_id = isset($complaint['handling_id']) ? $complaint['handling_id'] : '';
            $raw_company_number = isset($complaint['company_number']) ? $complaint['company_number'] : '';
            $raw_company_name = isset($complaint['company_name']) ? $complaint['company_name'] : '';
            $raw_sent_date = isset($complaint['sent_date']) ? $complaint['sent_date'] : '';
            $raw_company_status = isset($complaint['company_status']) ? $complaint['company_status'] : '';

            $raw_price_amount = isset($complaint['price_amount']) ? $complaint['price_amount'] : '';
            $raw_price_issued_date = isset($complaint['price_issued_date']) ? $complaint['price_issued_date'] : '';
            $raw_issued_invoice_number = isset($complaint['issued_invoice_number']) ? $complaint['issued_invoice_number'] : '';
            $raw_rejection_reason = isset($complaint['rejection_reason']) ? $complaint['rejection_reason'] : '';
            $raw_rejection_date = isset($complaint['rejection_date']) ? $complaint['rejection_date'] : '';
            $raw_company_invoice_number = isset($complaint['company_invoice_number']) ? $complaint['company_invoice_number'] : '';
            $raw_received_invoice_number = isset($complaint['received_invoice_number']) ? $complaint['received_invoice_number'] : '';
            $raw_special_remark = isset($complaint['special_remark']) ? $complaint['special_remark'] : '';
            $raw_special_request_date = isset($complaint['special_request_date']) ? $complaint['special_request_date'] : '';
            $raw_status_remark = isset($complaint['status_remark']) ? $complaint['status_remark'] : '';
            $raw_general_remark = isset($complaint['general_remark']) ? $complaint['general_remark'] : '';

            // Prepare display values for table
            if ($handling_id) {
                $disp_company_name = $raw_company_name;
                $disp_company_number = $raw_company_number;
                $disp_sent_date = $raw_sent_date;
                $disp_company_status = $raw_company_status;
            } else {
                $disp_company_name = '<span class="badge bg-soft-danger font-size-12">Not Assigned</span>';
                $disp_company_number = '-';
                $disp_sent_date = '-';
                $disp_company_status = '-';
            }

            $html .= '<tr class="select-complaint" style="cursor: pointer;"
                        data-id="' . $complaint['id'] . '"
                        data-complaint_no="' . $complaint['complaint_no'] . '"
                        data-uc_number="' . $complaint['uc_number'] . '"
                        data-customer_name="' . $customer_name . '"
                        data-fault_description="' . htmlspecialchars($complaint['fault_description']) . '"
                        data-handling_id="' . $handling_id . '"
                        data-company_number="' . $raw_company_number . '"
                        data-company_name="' . $raw_company_name . '"
                        data-sent_date="' . $raw_sent_date . '"
                        data-company_status="' . $raw_company_status . '"
                        data-price_amount="' . $raw_price_amount . '"
                        data-price_issued_date="' . $raw_price_issued_date . '"
                        data-issued_invoice_number="' . $raw_issued_invoice_number . '"
                        data-rejection_reason="' . $raw_rejection_reason . '"
                        data-rejection_date="' . $raw_rejection_date . '"
                        data-company_invoice_number="' . $raw_company_invoice_number . '"
                        data-received_invoice_number="' . $raw_received_invoice_number . '"
                        data-special_remark="' . $raw_special_remark . '"
                        data-special_request_date="' . $raw_special_request_date . '"
                        data-status_remark="' . $raw_status_remark . '"
                        data-general_remark="' . $raw_general_remark . '"
                    >';
            $html .= '<td>' . $key . '</td>';
            $html .= '<td>' . $complaint['complaint_no'] . '</td>';
            $html .= '<td>' . $complaint['uc_number'] . '</td>';
            $html .= '<td>' . $customer_name . '</td>';
            $html .= '<td>' . $complaint['fault_description'] . '</td>';

            // Add new columns to the table
            $html .= '<td>' . $disp_company_name . '</td>';
            $html .= '<td>' . $disp_company_number . '</td>';
            $html .= '<td>' . $disp_sent_date . '</td>';
            $html .= '<td>' . $disp_company_status . '</td>';

            $html .= '</tr>';
        }
    } else {
        $html .= '<tr><td colspan="6" class="text-center">No complaints found</td></tr>';
    }

    echo json_encode(array("status" => "success", "html" => $html));
}

// Load companies for company selection modal
if (isset($_POST['load_companies'])) {
    $DAG_COMPANY = new CompanyMaster(null);
    $companies = $DAG_COMPANY->getActiveCompany();

    $html = '';
    if (!empty($companies)) {
        foreach ($companies as $key => $company) {
            $key++;
            $html .= '<tr class="select-company" style="cursor: pointer;"
                        data-id="' . $company['id'] . '"
                        data-code="' . htmlspecialchars($company['code']) . '"
                        data-name="' . htmlspecialchars($company['name']) . '"
                        data-contact_person="' . htmlspecialchars($company['contact_person'] ?? '') . '"
                        data-phone="' . htmlspecialchars($company['phone_number'] ?? '') . '">';
            $html .= '<td>' . $key . '</td>';
            $html .= '<td>' . htmlspecialchars($company['code']) . '</td>';
            $html .= '<td>' . htmlspecialchars($company['name']) . '</td>';
            $html .= '<td>' . htmlspecialchars($company['contact_person'] ?? '-') . '</td>';
            $html .= '<td>' . htmlspecialchars($company['phone_number'] ?? '-') . '</td>';
            $html .= '</tr>';
        }
    } else {
        $html .= '<tr><td colspan="5" class="text-center">No companies found</td></tr>';
    }

    echo json_encode(array("status" => "success", "html" => $html));
}
