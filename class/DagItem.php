<?php

class DagItem
{
    public $id;
    public $dag_id;
    public $vehicle_no;
    public $belt_id;
    public $size_id;
    public $serial_number;
    public $is_invoiced;
    public $casing_cost;
    public $qty;
    public $total_amount;

    // Constructor to fetch data by ID
    public function __construct($id = null)
    {
        if ($id) {
            $query = "SELECT *
                      FROM `dag_item` WHERE `id` = " . (int) $id;
            $db = new Database();
            $result = mysqli_fetch_array($db->readQuery($query));
            if ($result) {
                $this->id = $result['id'];
                $this->dag_id = $result['dag_id'];
                $this->vehicle_no = $result['vehicle_no'];
                $this->belt_id = $result['belt_id'];
                $this->size_id = $result['size_id'];
                $this->serial_number = $result['serial_number'];
                $this->is_invoiced = isset($result['is_invoiced']) ? $result['is_invoiced'] : 0;
                $this->casing_cost = $result['casing_cost'];
                $this->qty = $result['qty'];
                $this->total_amount = $result['total_amount'];
            }
        }
    }

    // Create a new record
    public function create()
    {
        $query = "INSERT INTO `dag_item` (`dag_id`, `vehicle_no`, `belt_id`, `size_id`, `serial_number`, `is_invoiced`, `casing_cost`, `qty`, `total_amount`)
                  VALUES (
                    '{$this->dag_id}', '{$this->vehicle_no}', '{$this->belt_id}', '{$this->size_id}', '{$this->serial_number}', '{$this->is_invoiced}', '{$this->casing_cost}',
                    '{$this->qty}', '{$this->total_amount}'
                  )";

        $db = new Database();
        $result = $db->readQuery($query);
        if ($result) {
            return mysqli_insert_id($db->DB_CON);
        }
        return false;
    }

    // Update existing record
    public function update()
    {
        $query = "UPDATE `dag_item` SET
                  `dag_id` = '{$this->dag_id}',
                  `vehicle_no` = '{$this->vehicle_no}',
                  `belt_id` = '{$this->belt_id}',
                  `size_id` = '{$this->size_id}',
                  `serial_number` = '{$this->serial_number}',
                  `is_invoiced` = '{$this->is_invoiced}',
                  `casing_cost` = '{$this->casing_cost}',
                  `qty` = '{$this->qty}',
                  `total_amount` = '{$this->total_amount}'
                  WHERE `id` = '{$this->id}'";

        $db = new Database();
        return $db->readQuery($query);
    }

    // Delete record
    public function delete()
    {
        $query = "DELETE FROM `dag_item` WHERE `id` = '{$this->id}'";
        $db = new Database();
        return $db->readQuery($query);
    }

    // Delete record
    public function deleteDagItemByItemId($id)
    {
        $query = "DELETE FROM `dag_item` WHERE `dag_id` = $id";
        $db = new Database();
        return $db->readQuery($query);
    }

    // Get all records
    public function all()
    {
        $query = "SELECT * FROM `dag_item` ORDER BY `id` DESC";
        $db = new Database();
        $result = $db->readQuery($query);
        $array_res = [];

        while ($row = mysqli_fetch_array($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }

    // Get items by dag_id
    public function getByDagId($dag_id)
    {
        $query = "SELECT * FROM `dag_item` WHERE `dag_id` = '{$dag_id}' ORDER BY `id` ASC";
        $db = new Database();
        $result = $db->readQuery($query);
        $array_res = [];

        while ($row = mysqli_fetch_array($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }

    public function getByValuesDagId($dag_id)
    {
        $query = "SELECT di.*, bm.name AS belt_title, sm.name AS size_name
              FROM `dag_item` di 
              LEFT JOIN `belt_master` bm ON di.belt_id = bm.id 
              LEFT JOIN `size_master` sm ON di.size_id = sm.id 
              WHERE di.dag_id = '{$dag_id}' 
              ORDER BY di.id ASC";

        $db = new Database();
        $result = $db->readQuery($query);
        $array_res = [];

        while ($row = mysqli_fetch_array($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }

    // Check if all items for a DAG are invoiced
    public function areAllDagItemsInvoiced($dag_id)
    {
        $db = new Database();
        
        // First check if is_invoiced column exists
        $checkColumn = "SHOW COLUMNS FROM `dag_item` LIKE 'is_invoiced'";
        $columnExists = mysqli_fetch_array($db->readQuery($checkColumn));
        
        if (!$columnExists) {
            // Column doesn't exist, return false (not all invoiced)
            return false;
        }
        
        $query = "SELECT COUNT(*) as total_items, 
                         SUM(COALESCE(is_invoiced, 0)) as invoiced_items 
                  FROM `dag_item` 
                  WHERE `dag_id` = '{$dag_id}'";
        
        $result = mysqli_fetch_array($db->readQuery($query));
        
        if ($result && $result['total_items'] > 0) {
            return $result['total_items'] == $result['invoiced_items'];
        }
        
        return false;
    }

    // Get only non-invoiced items by dag_id
    public function getNonInvoicedByDagId($dag_id)
    {
        // First check if is_invoiced column exists
        $db = new Database();
        $checkColumn = "SHOW COLUMNS FROM `dag_item` LIKE 'is_invoiced'";
        $columnExists = mysqli_fetch_array($db->readQuery($checkColumn));
        
        if ($columnExists) {
            // Column exists, use it
            $query = "SELECT di.*, bm.name AS belt_title, sm.name AS size_name
                  FROM `dag_item` di 
                  LEFT JOIN `belt_master` bm ON di.belt_id = bm.id 
                  LEFT JOIN `size_master` sm ON di.size_id = sm.id 
                  WHERE di.dag_id = '{$dag_id}' AND (di.is_invoiced = 0 OR di.is_invoiced IS NULL)
                  ORDER BY di.id ASC";
        } else {
            // Column doesn't exist, get all items
            $query = "SELECT di.*, bm.name AS belt_title, sm.name AS size_name
                  FROM `dag_item` di 
                  LEFT JOIN `belt_master` bm ON di.belt_id = bm.id 
                  LEFT JOIN `size_master` sm ON di.size_id = sm.id 
                  WHERE di.dag_id = '{$dag_id}'
                  ORDER BY di.id ASC";
        }

        $result = $db->readQuery($query);
        $array_res = [];

        if ($result) {
            while ($row = mysqli_fetch_array($result)) {
                array_push($array_res, $row);
            }
        }

        return $array_res;
    }

}
?>