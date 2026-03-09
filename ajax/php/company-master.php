<?php

include '../../class/include.php';
header('Content-Type: application/json; charset=UTF8');

// Create a new Company
if (isset($_POST['create'])) {

    $COMPANY = new CompanyMaster(NULL);

    // Set the Company details
    $COMPANY->name = $_POST['name'];
    $COMPANY->code = $_POST['code'];
    $COMPANY->address = $_POST['address'];
    $COMPANY->contact_person = $_POST['contact_person'];
    $COMPANY->phone_number = $_POST['phone_number'];
    $COMPANY->email = $_POST['email'];
    $COMPANY->is_active = isset($_POST['is_active']) ? 1 : 0;
    $COMPANY->remark = $_POST['remark'];

    // Attempt to create the Company
    $res = $COMPANY->create();

    if ($res) {
        $result = [
            "status" => 'success'
        ];
        echo json_encode($result);
        exit();
    } else {
        $result = [
            "status" => 'error'
        ];
        echo json_encode($result);
        exit();
    }
}

// Update Company details
if (isset($_POST['update'])) {

    $COMPANY = new CompanyMaster($_POST['id']);

    // Update Company details
    $COMPANY->name = $_POST['name'];
    $COMPANY->code = $_POST['code'];
    $COMPANY->address = $_POST['address'];
    $COMPANY->contact_person = $_POST['contact_person'];
    $COMPANY->phone_number = $_POST['phone_number'];
    $COMPANY->email = $_POST['email'];
    $COMPANY->is_active = isset($_POST['is_active']) ? 1 : 0;
    $COMPANY->remark = $_POST['remark'];

    // Attempt to update the Company
    $result = $COMPANY->update();

    if ($result) {
        $result = [
            "status" => 'success'
        ];
        echo json_encode($result);
        exit();
    } else {
        $result = [
            "status" => 'error'
        ];
        echo json_encode($result);
        exit();
    }
}

if (isset($_POST['delete']) && isset($_POST['id'])) {
    $company = new CompanyMaster($_POST['id']);
    $result = $company->delete();

    if ($result) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}

?>