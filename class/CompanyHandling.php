<?php

class CompanyHandling
{
    public $id;
    public $complaint_id;
    public $company_number;
    public $company_name;
    public $sent_date;
    public $company_status;
    public $price_amount;
    public $price_issued_date;
    public $issued_invoice_number;
    public $rejection_reason;
    public $rejection_date;
    public $company_invoice_number;
    public $received_invoice_number;
    public $special_remark;
    public $special_request_date;
    public $status_remark;
    public $general_remark;
    public $created_at;
    public $updated_at;

    public function __construct($id = null)
    {
        if ($id) {
            $query = "SELECT * FROM `company_handling` WHERE `id` = " . (int) $id;
            $db = Database::getInstance();
            $result = mysqli_fetch_array($db->readQuery($query));

            if ($result) {
                $this->id = $result['id'];
                $this->complaint_id = $result['complaint_id'];
                $this->company_number = $result['company_number'];
                $this->company_name = $result['company_name'];
                $this->sent_date = $result['sent_date'];
                $this->company_status = $result['company_status'];
                $this->price_amount = $result['price_amount'];
                $this->price_issued_date = $result['price_issued_date'];
                $this->issued_invoice_number = $result['issued_invoice_number'];
                $this->rejection_reason = $result['rejection_reason'];
                $this->rejection_date = $result['rejection_date'];
                $this->company_invoice_number = $result['company_invoice_number'];
                $this->received_invoice_number = $result['received_invoice_number'];
                $this->special_remark = $result['special_remark'];
                $this->special_request_date = $result['special_request_date'];
                $this->status_remark = $result['status_remark'];
                $this->general_remark = $result['general_remark'];
                $this->created_at = $result['created_at'];
                $this->updated_at = $result['updated_at'];
            }
        }
    }

    public function create()
    {
        $db = Database::getInstance();

        $this->company_number = mysqli_real_escape_string($db->DB_CON, $this->company_number);
        $this->company_name = mysqli_real_escape_string($db->DB_CON, $this->company_name);
        $this->company_status = mysqli_real_escape_string($db->DB_CON, $this->company_status);

        // Handle new fields (allow pulling nulls or empty strings properly)
        $this->price_amount = empty($this->price_amount) ? 'NULL' : "'" . mysqli_real_escape_string($db->DB_CON, $this->price_amount) . "'";
        $this->price_issued_date = empty($this->price_issued_date) ? 'NULL' : "'" . mysqli_real_escape_string($db->DB_CON, $this->price_issued_date) . "'";
        $this->issued_invoice_number = mysqli_real_escape_string($db->DB_CON, $this->issued_invoice_number);
        $this->rejection_reason = mysqli_real_escape_string($db->DB_CON, $this->rejection_reason);
        $this->rejection_date = empty($this->rejection_date) ? 'NULL' : "'" . mysqli_real_escape_string($db->DB_CON, $this->rejection_date) . "'";
        $this->company_invoice_number = mysqli_real_escape_string($db->DB_CON, $this->company_invoice_number);
        $this->received_invoice_number = mysqli_real_escape_string($db->DB_CON, $this->received_invoice_number);
        $this->special_remark = mysqli_real_escape_string($db->DB_CON, $this->special_remark);
        $this->special_request_date = empty($this->special_request_date) ? 'NULL' : "'" . mysqli_real_escape_string($db->DB_CON, $this->special_request_date) . "'";
        $this->status_remark = mysqli_real_escape_string($db->DB_CON, $this->status_remark);
        $this->general_remark = mysqli_real_escape_string($db->DB_CON, $this->general_remark);

        $query = "INSERT INTO `company_handling` (
            `complaint_id`, `company_number`, `company_name`, `sent_date`, `company_status`,
            `price_amount`, `price_issued_date`, `issued_invoice_number`, `rejection_reason`, `rejection_date`,
            `company_invoice_number`, `received_invoice_number`, `special_remark`, `special_request_date`,
            `status_remark`, `general_remark`
        ) VALUES (
            '{$this->complaint_id}', '{$this->company_number}', '{$this->company_name}', '{$this->sent_date}', '{$this->company_status}',
            $this->price_amount, $this->price_issued_date, '{$this->issued_invoice_number}', '{$this->rejection_reason}', $this->rejection_date,
            '{$this->company_invoice_number}', '{$this->received_invoice_number}', '{$this->special_remark}', $this->special_request_date,
            '{$this->status_remark}', '{$this->general_remark}'
        )";

        $result = $db->readQuery($query);
        if ($result) {
            return mysqli_insert_id($db->DB_CON);
        } else {
            return false;
        }
    }

    public function update()
    {
        $db = Database::getInstance();

        $this->company_number = mysqli_real_escape_string($db->DB_CON, $this->company_number);
        $this->company_name = mysqli_real_escape_string($db->DB_CON, $this->company_name);
        $this->company_status = mysqli_real_escape_string($db->DB_CON, $this->company_status);

        $this->price_amount = empty($this->price_amount) ? 'NULL' : "'" . mysqli_real_escape_string($db->DB_CON, $this->price_amount) . "'";
        $this->price_issued_date = empty($this->price_issued_date) ? 'NULL' : "'" . mysqli_real_escape_string($db->DB_CON, $this->price_issued_date) . "'";
        $this->issued_invoice_number = mysqli_real_escape_string($db->DB_CON, $this->issued_invoice_number);
        $this->rejection_reason = mysqli_real_escape_string($db->DB_CON, $this->rejection_reason);
        $this->rejection_date = empty($this->rejection_date) ? 'NULL' : "'" . mysqli_real_escape_string($db->DB_CON, $this->rejection_date) . "'";
        $this->company_invoice_number = mysqli_real_escape_string($db->DB_CON, $this->company_invoice_number);
        $this->received_invoice_number = mysqli_real_escape_string($db->DB_CON, $this->received_invoice_number);
        $this->special_remark = mysqli_real_escape_string($db->DB_CON, $this->special_remark);
        $this->special_request_date = empty($this->special_request_date) ? 'NULL' : "'" . mysqli_real_escape_string($db->DB_CON, $this->special_request_date) . "'";
        $this->status_remark = mysqli_real_escape_string($db->DB_CON, $this->status_remark);
        $this->general_remark = mysqli_real_escape_string($db->DB_CON, $this->general_remark);

        $query = "UPDATE `company_handling` SET 
            `complaint_id` = '{$this->complaint_id}',
            `company_number` = '{$this->company_number}',
            `company_name` = '{$this->company_name}',
            `sent_date` = '{$this->sent_date}',
            `company_status` = '{$this->company_status}',
            `price_amount` = $this->price_amount,
            `price_issued_date` = $this->price_issued_date,
            `issued_invoice_number` = '{$this->issued_invoice_number}',
            `rejection_reason` = '{$this->rejection_reason}',
            `rejection_date` = $this->rejection_date,
            `company_invoice_number` = '{$this->company_invoice_number}',
            `received_invoice_number` = '{$this->received_invoice_number}',
            `special_remark` = '{$this->special_remark}',
            `special_request_date` = $this->special_request_date,
            `status_remark` = '{$this->status_remark}',
            `general_remark` = '{$this->general_remark}'
            WHERE `id` = '{$this->id}'";

        return $db->readQuery($query);
    }

    public function delete()
    {
        $db = Database::getInstance();
        if (!$this->id) {
            return false;
        }
        $query = "DELETE FROM `company_handling` WHERE `id` = '{$this->id}'";
        return $db->readQuery($query);
    }

    public function all()
    {
        $query = "SELECT ch.*, cc.complaint_no, cc.uc_number, cc.fault_description 
                  FROM `company_handling` ch
                  LEFT JOIN `customer_complaint` cc ON ch.complaint_id = cc.id
                  ORDER BY ch.`id` DESC";
        $db = Database::getInstance();
        $result = $db->readQuery($query);

        $array_res = array();
        while ($row = mysqli_fetch_array($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }

    public function getComplaintsWithDetails()
    {
        $query = "SELECT cc.*, cm.name as customer_name, 
                         ch.id as handling_id, ch.company_number, ch.company_name, ch.sent_date, ch.company_status,
                         ch.price_amount, ch.price_issued_date, ch.issued_invoice_number, ch.rejection_reason,
                         ch.company_invoice_number, ch.received_invoice_number, ch.special_remark,
                         ch.status_remark, ch.general_remark
                  FROM `customer_complaint` cc
                  LEFT JOIN `customer_master` cm ON cc.customer_id = cm.id
                  LEFT JOIN `company_handling` ch ON cc.id = ch.complaint_id
                  ORDER BY cc.`id` DESC";
        $db = Database::getInstance();
        $result = $db->readQuery($query);

        $array_res = array();
        while ($row = mysqli_fetch_array($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }
}
