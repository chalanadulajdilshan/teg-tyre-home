<?php

class CustomerComplaint
{
    public $id;
    public $complaint_no;
    public $uc_number;
    public $customer_id;
    public $tyre_serial_number;
    public $fault_description;
    public $complaint_category;
    public $complaint_date;

    // Constructor: Fetch by ID
    public function __construct($id = null)
    {
        if ($id) {
            $query = "SELECT * FROM `customer_complaint` WHERE `id` = " . (int) $id;
            $db = Database::getInstance();
            $result = mysqli_fetch_array($db->readQuery($query));

            if ($result) {
                $this->id = $result['id'];
                $this->complaint_no = $result['complaint_no'];
                $this->uc_number = $result['uc_number'];
                $this->customer_id = $result['customer_id'];
                $this->tyre_serial_number = $result['tyre_serial_number'];
                $this->fault_description = $result['fault_description'];
                $this->complaint_category = $result['complaint_category'];
                $this->complaint_date = $result['complaint_date'];
            }
        }
    }

    // Create new complaint
    public function create()
    {
        $db = Database::getInstance();
        $this->fault_description = mysqli_real_escape_string($db->DB_CON, $this->fault_description);
        $this->uc_number = mysqli_real_escape_string($db->DB_CON, $this->uc_number);
        $this->tyre_serial_number = mysqli_real_escape_string($db->DB_CON, $this->tyre_serial_number);

        $query = "INSERT INTO `customer_complaint` (
            `complaint_no`, `uc_number`, `customer_id`, `tyre_serial_number`, 
            `fault_description`, `complaint_category`, `complaint_date`
        ) VALUES (
            '{$this->complaint_no}', '{$this->uc_number}', '{$this->customer_id}', '{$this->tyre_serial_number}',
            '{$this->fault_description}', '{$this->complaint_category}', '{$this->complaint_date}'
        )";

        $result = $db->readQuery($query);
        if ($result) {
            return mysqli_insert_id($db->DB_CON);
        } else {
            return false;
        }
    }

    // Update existing complaint
    public function update()
    {
        $db = Database::getInstance();
        $this->fault_description = mysqli_real_escape_string($db->DB_CON, $this->fault_description);
        $this->uc_number = mysqli_real_escape_string($db->DB_CON, $this->uc_number);
        $this->tyre_serial_number = mysqli_real_escape_string($db->DB_CON, $this->tyre_serial_number);

        $query = "UPDATE `customer_complaint` SET 
            `complaint_no` = '{$this->complaint_no}',
            `uc_number` = '{$this->uc_number}',
            `customer_id` = '{$this->customer_id}',
            `tyre_serial_number` = '{$this->tyre_serial_number}',
            `fault_description` = '{$this->fault_description}',
            `complaint_category` = '{$this->complaint_category}',
            `complaint_date` = '{$this->complaint_date}'
            WHERE `id` = '{$this->id}'";

        return $db->readQuery($query);
    }

    // Delete complaint
    public function delete()
    {
        $db = Database::getInstance();

        if (!$this->id) {
            return false;
        }

        $query = "DELETE FROM `customer_complaint` WHERE `id` = '{$this->id}'";
        return $db->readQuery($query);
    }

    // Get all complaints
    public function all()
    {
        $query = "SELECT cc.*, cm.name as customer_name, cm.code as customer_code 
                  FROM `customer_complaint` cc
                  LEFT JOIN `customer_master` cm ON cc.customer_id = cm.id
                  ORDER BY cc.`id` DESC";
        $db = Database::getInstance();
        $result = $db->readQuery($query);

        $array_res = array();
        while ($row = mysqli_fetch_array($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }

    // Get last inserted ID for auto-numbering
    public function getLastID()
    {
        $query = "SELECT `id` FROM `customer_complaint` ORDER BY `id` DESC LIMIT 1";
        $db = Database::getInstance();
        $result = mysqli_fetch_array($db->readQuery($query));
        return $result ? $result['id'] : 0;
    }

    // Get filtered reports
    public function getFilteredReports($from_date = '', $to_date = '', $category = '', $status = '', $company = '')
    {
        $query = "SELECT cc.*, cm.name as customer_name, cm.code as customer_code,
                         ch.id as handling_id, ch.company_name, ch.company_number, ch.company_status,
                         ch.rejection_date, ch.rejection_reason,
                         ch.special_request_date, ch.special_remark,
                         ch.price_amount, ch.price_issued_date, ch.issued_invoice_number,
                         ch.sent_date, ch.status_remark, ch.general_remark
                  FROM `customer_complaint` cc
                  LEFT JOIN `customer_master` cm ON cc.customer_id = cm.id
                  LEFT JOIN `company_handling` ch ON cc.id = ch.complaint_id
                  WHERE 1=1";

        if (!empty($from_date) && !empty($to_date)) {
            $query .= " AND cc.complaint_date BETWEEN '$from_date' AND '$to_date'";
        }
        if (!empty($category)) {
            $query .= " AND cc.complaint_category = '$category'";
        }
        if (!empty($status)) {
            $db_tmp = Database::getInstance();
            $status = mysqli_real_escape_string($db_tmp->DB_CON, $status);
            if ($status === 'Pending') {
                $query .= " AND (ch.company_status IS NULL OR ch.company_status = '')";
            } else {
                $query .= " AND ch.company_status LIKE '%$status%'";
            }
        }
        if (!empty($company)) {
            $db_tmp = Database::getInstance();
            $company = mysqli_real_escape_string($db_tmp->DB_CON, $company);
            $query .= " AND ch.company_name = '$company'";
        }

        $query .= " ORDER BY cc.complaint_date DESC";

        $db = Database::getInstance();
        $result = $db->readQuery($query);

        $reports = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $reports[] = $row;
        }

        return $reports;
    }

    // Get distinct company names from company_handling
    public function getDistinctCompanies()
    {
        $query = "SELECT DISTINCT ch.company_name 
                  FROM `company_handling` ch 
                  WHERE ch.company_name IS NOT NULL AND ch.company_name != ''
                  ORDER BY ch.company_name ASC";
        $db = Database::getInstance();
        $result = $db->readQuery($query);

        $companies = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $companies[] = $row['company_name'];
        }
        return $companies;
    }

    // Search complaints by various fields
    public function search($search_term)
    {
        $db = Database::getInstance();
        $search_term = mysqli_real_escape_string($db->DB_CON, $search_term);

        $query = "SELECT cc.*, cm.name as customer_name, cm.code as customer_code 
                  FROM `customer_complaint` cc
                  LEFT JOIN `customer_master` cm ON cc.customer_id = cm.id
                  WHERE cc.complaint_no LIKE '%$search_term%' 
                     OR cc.uc_number LIKE '%$search_term%'
                     OR cc.tyre_serial_number LIKE '%$search_term%'
                     OR cm.name LIKE '%$search_term%'
                  ORDER BY cc.id DESC";

        $result = $db->readQuery($query);

        $array_res = array();
        while ($row = mysqli_fetch_array($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }
}
?>