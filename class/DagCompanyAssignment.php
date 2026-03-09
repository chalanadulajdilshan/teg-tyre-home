<?php
class DagCompanyAssignment
{
    public $id;
    public $assignment_number;
    public $company_id;
    public $company_receipt_number;
    public $company_issued_date;
    public $created_at;
    public $updated_at;

    public function __construct($id = null)
    {
        if ($id) {
            $query = "SELECT * FROM `dag_company_assignments` WHERE `id` = " . (int) $id;
            $db = Database::getInstance();
            $result = mysqli_fetch_array($db->readQuery($query));

            if ($result) {
                $this->id = $result['id'];
                $this->assignment_number = $result['assignment_number'];
                $this->company_id = $result['company_id'];
                $this->company_receipt_number = $result['company_receipt_number'];
                $this->company_issued_date = $result['company_issued_date'];
                $this->created_at = $result['created_at'];
                $this->updated_at = $result['updated_at'];
            }
        }
    }

    public function create()
    {
        $query = "INSERT INTO `dag_company_assignments` (`assignment_number`, `company_id`, `company_receipt_number`, `company_issued_date`) " .
            "VALUES ('" . $this->assignment_number . "', '" . $this->company_id . "', '" . $this->company_receipt_number . "', '" . $this->company_issued_date . "')";

        $db = Database::getInstance();
        $result = $db->readQuery($query);

        if ($result) {
            $last_id = mysqli_insert_id($db->DB_CON);
            // Auto generate assignment number
            $assignment_number = 'CDA-' . str_pad($last_id, 5, "0", STR_PAD_LEFT);
            $update_query = "UPDATE `dag_company_assignments` SET `assignment_number` = '" . $assignment_number . "' WHERE `id` = " . $last_id;
            $db->readQuery($update_query);

            $this->__construct($last_id);
            return $this;
        } else {
            return FALSE;
        }
    }

    public function update()
    {
        $query = "UPDATE `dag_company_assignments` SET " .
            "`company_id` = '" . $this->company_id . "', " .
            "`company_receipt_number` = '" . $this->company_receipt_number . "', " .
            "`company_issued_date` = '" . $this->company_issued_date . "' " .
            "WHERE `id` = '" . $this->id . "'";

        $db = Database::getInstance();
        $result = $db->readQuery($query);

        if ($result) {
            $this->__construct($this->id);
            return $this;
        } else {
            return FALSE;
        }
    }

    public function all()
    {
        $query = "SELECT * FROM `dag_company_assignments` ORDER BY id DESC";
        $db = Database::getInstance();
        $result = $db->readQuery($query);
        $array_res = array();

        while ($row = mysqli_fetch_array($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }

    public function delete()
    {
        $query = "DELETE FROM `dag_company_assignments` WHERE id = '" . $this->id . "'";
        $db = Database::getInstance();
        return $db->readQuery($query);
    }

    public function getNextId()
    {
        $query = "SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dag_company_assignments'";
        $db = Database::getInstance();
        $result = mysqli_fetch_assoc($db->readQuery($query));
        return $result ? $result['AUTO_INCREMENT'] : 1;
    }

    // ITEM Management
    public function addItems($items)
    {
        $db = Database::getInstance();
        $success = true;

        foreach ($items as $item) {
            $dag_id = $item['dag_id'];
            $job_number = $item['job_number'];
            $belt_design = $item['belt_design'];
            $company_status = $item['company_status'];
            $company_received_date = $item['company_received_date'];
            $uc_number = $item['uc_number'];

            $query = "INSERT INTO `dag_company_assignment_items` (`assignment_id`, `dag_id`, `job_number`, `belt_design`, `company_status`, `company_received_date`, `uc_number`) " .
                "VALUES ('" . $this->id . "', '" . $dag_id . "', '" . $job_number . "', '" . $belt_design . "', '" . $company_status . "', '" . $company_received_date . "', '" . $uc_number . "')";

            $result = $db->readQuery($query);
            if (!$result) {
                $success = false;
            }
        }
        return $success;
    }

    public function getItems()
    {
        $query = "SELECT * FROM `dag_company_assignment_items` WHERE `assignment_id` = " . $this->id;
        $db = Database::getInstance();
        $result = $db->readQuery($query);
        $array_res = array();

        while ($row = mysqli_fetch_array($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }

    public function deleteItems()
    {
        $query = "DELETE FROM `dag_company_assignment_items` WHERE `assignment_id` = " . $this->id;
        $db = Database::getInstance();
        return $db->readQuery($query);
    }
}
?>