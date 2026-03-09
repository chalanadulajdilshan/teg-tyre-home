<?php

include '../../class/include.php';
header('Content-Type: application/json; charset=UTF8');

if (isset($_POST['create'])) {

    $DAG_CUSTOMER = new DagCustomer(NULL);
    $DAG_CUSTOMER->customer_id = $_POST['customer_id'];
    $DAG_CUSTOMER->my_number = $_POST['my_number'];
    $DAG_CUSTOMER->size = $_POST['size'];
    $DAG_CUSTOMER->brand = $_POST['brand'];
    $DAG_CUSTOMER->serial_no = $_POST['serial_no'];
    $DAG_CUSTOMER->dag_received_date = $_POST['dag_received_date'];
    $DAG_CUSTOMER->remark = $_POST['remark'];

    $result = $DAG_CUSTOMER->create();

    if ($result) {
        $result = ["status" => 'success'];
        echo json_encode($result);
        exit();
    } else {
        $result = ["status" => 'error'];
        echo json_encode($result);
        exit();
    }
}

if (isset($_POST['update'])) {

    $DAG_CUSTOMER = new DagCustomer($_POST['id']);
    $DAG_CUSTOMER->customer_id = $_POST['customer_id'];
    $DAG_CUSTOMER->my_number = $_POST['my_number'];
    $DAG_CUSTOMER->size = $_POST['size'];
    $DAG_CUSTOMER->brand = $_POST['brand'];
    $DAG_CUSTOMER->serial_no = $_POST['serial_no'];
    $DAG_CUSTOMER->dag_received_date = $_POST['dag_received_date'];
    $DAG_CUSTOMER->remark = $_POST['remark'];

    $result = $DAG_CUSTOMER->update();

    if ($result) {
        $result = ["status" => 'success'];
        echo json_encode($result);
        exit();
    } else {
        $result = ["status" => 'error'];
        echo json_encode($result);
        exit();
    }
}

if (isset($_POST['delete']) && isset($_POST['id'])) {
    $DAG_CUSTOMER = new DagCustomer($_POST['id']);
    $result = $DAG_CUSTOMER->delete();

    if ($result) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}

if (isset($_POST['get_next_id'])) {
    $DAG_CUSTOMER = new DagCustomer(NULL);
    $next_id = $DAG_CUSTOMER->getNextId();
    echo json_encode(['status' => 'success', 'next_id' => 'DAG-' . str_pad($next_id, 5, "0", STR_PAD_LEFT)]);
    exit();
}

