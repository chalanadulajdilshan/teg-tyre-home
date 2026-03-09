<?php
/**
 * Description of DagCustomer
 *
 * @author Wimali
 */
class DagCustomer
{

    public $id;
    public $customer_id;
    public $dag_number;
    public $my_number;
    public $size;
    public $brand;
    public $serial_no;
    public $dag_received_date;
    public $remark;
    public $cost;
    public $price;
    public $discount;
    public $total;
    public $is_invoiced;
    public $is_cancelled;
    public $issued_date;
    public $created_at;
    public $updated_at;

    public function getDagNumber()
    {
        return $this->dag_number;
    }

    public function __construct($id = null)
    {
        if ($id) {
            $query = "SELECT * FROM `dag_customers` WHERE `id`=" . (int) $id;
            $db = Database::getInstance();
            $result = mysqli_fetch_array($db->readQuery($query));

            if ($result) {
                $this->id = $result['id'];
                $this->customer_id = $result['customer_id'];
                $this->dag_number = $result['dag_number'];
                $this->my_number = $result['my_number'];
                $this->size = $result['size'];
                $this->brand = $result['brand'];
                $this->serial_no = $result['serial_no'];
                $this->dag_received_date = $result['dag_received_date'];
                $this->remark = $result['remark'];
                $this->cost = $result['cost'] ?? 0;
                $this->price = $result['price'] ?? 0;
                $this->discount = $result['discount'] ?? 0;
                $this->total = $result['total'] ?? 0;
                $this->is_invoiced = $result['is_invoiced'] ?? 0;
                $this->is_cancelled = $result['is_cancelled'] ?? 0;
                $this->issued_date = $result['issued_date'] ?? null;
                $this->created_at = $result['created_at'];
                $this->updated_at = $result['updated_at'];
            }
        }
    }

    public function create()
    {
        $query = "INSERT INTO `dag_customers` (`customer_id`, `my_number`, `size`, `brand`, `serial_no`, `dag_received_date`, `remark`, `price`, `discount`, `total`) VALUES  ('"
            . $this->customer_id . "', '"
            . $this->my_number . "', '"
            . $this->size . "', '"
            . $this->brand . "', '"
            . $this->serial_no . "', '"
            . $this->dag_received_date . "', '"
            . $this->remark . "', '"
            . ($this->price ?? 0) . "', '"
            . ($this->discount ?? 0) . "', '"
            . ($this->total ?? 0) . "')";

        $db = Database::getInstance();
        $result = $db->readQuery($query);

        if ($result) {
            $last_id = mysqli_insert_id($db->DB_CON);
            $dag_number = 'DAG-' . str_pad($last_id, 5, "0", STR_PAD_LEFT);

            $update_query = "UPDATE `dag_customers` SET `dag_number` = '" . $dag_number . "' WHERE `id` = " . $last_id;
            $db->readQuery($update_query);

            $this->__construct($last_id);
            return $this;
        } else {
            return FALSE;
        }
    }

    public function all()
    {
        $query = "SELECT * FROM `dag_customers` ORDER BY id ASC";
        $db = Database::getInstance();
        $result = $db->readQuery($query);
        $array_res = array();

        while ($row = mysqli_fetch_array($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }

    public function update()
    {
        $query = "UPDATE `dag_customers` SET "
            . "`customer_id` ='" . $this->customer_id . "', "
            . "`my_number` ='" . $this->my_number . "', "
            . "`size` ='" . $this->size . "', "
            . "`brand` ='" . $this->brand . "', "
            . "`serial_no` ='" . $this->serial_no . "', "
            . "`dag_received_date` ='" . $this->dag_received_date . "', "
            . "`remark` ='" . $this->remark . "', "
            . "`price` ='" . ($this->price ?? 0) . "', "
            . "`discount` ='" . ($this->discount ?? 0) . "', "
            . "`total` ='" . ($this->total ?? 0) . "' "
            . "WHERE `id` = '" . $this->id . "'";

        $db = Database::getInstance();
        $result = $db->readQuery($query);

        if ($result) {
            $this->__construct($this->id);
            return $this;
        } else {
            return FALSE;
        }
    }

    public function updateInvoice()
    {
        $query = "UPDATE `dag_customers` SET "
            . "`cost` ='" . ($this->cost ?? 0) . "', "
            . "`price` ='" . ($this->price ?? 0) . "', "
            . "`discount` ='" . ($this->discount ?? 0) . "', "
            . "`total` ='" . ($this->total ?? 0) . "', "
            . "`is_invoiced` = 1, "
            . "`is_cancelled` = 0, "
            . "`issued_date` = " . ($this->issued_date ? "'" . $this->issued_date . "'" : "NULL") . " "
            . "WHERE `id` = '" . $this->id . "'";

        $db = Database::getInstance();
        $result = $db->readQuery($query);

        if ($result) {
            $this->__construct($this->id);
            return $this;
        } else {
            return FALSE;
        }
    }

    public function clearInvoice()
    {
        $query = "UPDATE `dag_customers` SET "
            . "`cost` = 0, "
            . "`price` = 0, "
            . "`discount` = 0, "
            . "`total` = 0, "
            . "`is_invoiced` = 0 "
            . "WHERE `id` = '" . $this->id . "'";

        $db = Database::getInstance();
        $result = $db->readQuery($query);

        if ($result) {
            $this->__construct($this->id);
            return $this;
        } else {
            return FALSE;
        }
    }

    public function cancelInvoice()
    {
        $query = "UPDATE `dag_customers` SET "
            . "`is_cancelled` = 1, "
            . "`is_invoiced` = 0, "
            . "`cost` = 0, "
            . "`price` = 0, "
            . "`discount` = 0, "
            . "`total` = 0 "
            . "WHERE `id` = '" . $this->id . "'";

        $db = Database::getInstance();
        $result = $db->readQuery($query);

        if ($result) {
            $this->__construct($this->id);
            return $this;
        } else {
            return FALSE;
        }
    }

    public function delete()
    {
        $query = 'DELETE FROM `dag_customers` WHERE id="' . $this->id . '"';

        $db = Database::getInstance();
        return $db->readQuery($query);
    }

    public function getNextId()
    {
        $query = "SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dag_customers'";
        $db = Database::getInstance();
        $result = mysqli_fetch_assoc($db->readQuery($query));
        return $result ? $result['AUTO_INCREMENT'] : 1;
    }

    public function getByCustomerId($customer_id, $invoiced = null)
    {
        $query = "SELECT dc.*, c.name as customer_name, c.name_2 as customer_name_2, c.code as customer_code,
                         cm.name as company_name
                  FROM `dag_customers` dc 
                  LEFT JOIN `customer_master` c ON dc.customer_id = c.id 
                  LEFT JOIN `dag_company_assignment_items` dcai ON dcai.dag_id = dc.id
                  LEFT JOIN `dag_company_assignments` dca ON dca.id = dcai.assignment_id
                  LEFT JOIN `company_master` cm ON cm.id = dca.company_id
                  WHERE dc.customer_id = " . (int) $customer_id;

        if ($invoiced === 0) {
            // For new invoice: show uninvoiced OR cancelled (can be re-invoiced)
            // Exclude only if ALL company assignments are rejected (no successful one exists)
            $query .= " AND (dc.is_invoiced = 0 OR dc.is_cancelled = 1)";
            $query .= " AND (
                NOT EXISTS (SELECT 1 FROM dag_company_assignment_items WHERE dag_id = dc.id)
                OR EXISTS (SELECT 1 FROM dag_company_assignment_items WHERE dag_id = dc.id AND (company_status IS NULL OR LOWER(company_status) NOT LIKE '%reject%'))
            )";
        } elseif ($invoiced === 1) {
            // For editing: show invoiced and NOT cancelled
            $query .= " AND dc.is_invoiced = 1 AND (dc.is_cancelled = 0 OR dc.is_cancelled IS NULL)";
        }

        $query .= " GROUP BY dc.id ORDER BY dc.id ASC";
        $db = Database::getInstance();
        $result = $db->readQuery($query);
        $array_res = array();

        while ($row = mysqli_fetch_assoc($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }

    public function searchForInvoice($keyword)
    {
        $db = Database::getInstance();
        $keyword = mysqli_real_escape_string($db->DB_CON, $keyword);
        $query = "SELECT dc.*, c.name as customer_name, c.name_2 as customer_name_2, c.code as customer_code,
                         cm.name as company_name
                  FROM `dag_customers` dc 
                  LEFT JOIN `customer_master` c ON dc.customer_id = c.id 
                  LEFT JOIN `dag_company_assignment_items` dcai ON dcai.dag_id = dc.id
                  LEFT JOIN `dag_company_assignments` dca ON dca.id = dcai.assignment_id
                  LEFT JOIN `company_master` cm ON cm.id = dca.company_id
                  WHERE dc.is_invoiced = 1
                    AND (dc.dag_number LIKE '%$keyword%' 
                     OR dc.my_number LIKE '%$keyword%' 
                     OR dc.serial_no LIKE '%$keyword%'
                     OR c.name LIKE '%$keyword%')
                  GROUP BY dc.id
                  ORDER BY dc.id DESC 
                  LIMIT 50";

        $result = $db->readQuery($query);
        $array_res = array();

        while ($row = mysqli_fetch_assoc($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }

    public function getCustomersWithDags()
    {
        $query = "SELECT DISTINCT c.id, c.code, c.name, c.name_2 
                  FROM `customer_master` c 
                  INNER JOIN `dag_customers` dc ON c.id = dc.customer_id 
                  ORDER BY c.name ASC";
        $db = Database::getInstance();
        $result = $db->readQuery($query);
        $array_res = array();

        while ($row = mysqli_fetch_assoc($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }

    public function getReportData($filters = [])
    {
        $query = "SELECT dc.id, dc.dag_number, dc.my_number, dc.size, dc.brand, dc.serial_no, 
                         dc.dag_received_date, dc.remark, dc.cost, dc.price, dc.discount, dc.total,
                         dc.is_invoiced, dc.is_cancelled, dc.issued_date, dc.created_at,
                         c.name as customer_name, c.name_2 as customer_name_2, c.code as customer_code,
                         cm.name as company_name,
                         dca.assignment_number, dca.company_receipt_number, dca.company_issued_date,
                         dcai.job_number, dcai.belt_design, dcai.company_status, 
                         dcai.company_received_date, dcai.uc_number
                  FROM `dag_customers` dc
                  LEFT JOIN `customer_master` c ON dc.customer_id = c.id
                  LEFT JOIN `dag_company_assignment_items` dcai ON dcai.dag_id = dc.id
                  LEFT JOIN `dag_company_assignments` dca ON dca.id = dcai.assignment_id
                  LEFT JOIN `company_master` cm ON cm.id = dca.company_id
                  WHERE 1=1";

        if (!empty($filters['date_from'])) {
            $query .= " AND dc.dag_received_date >= '" . $filters['date_from'] . "'";
        }
        if (!empty($filters['date_to'])) {
            $query .= " AND dc.dag_received_date <= '" . $filters['date_to'] . "'";
        }
        if (!empty($filters['customer'])) {
            $query .= " AND (c.name LIKE '%" . $filters['customer'] . "%' OR c.code LIKE '%" . $filters['customer'] . "%')";
        }
        if (!empty($filters['company'])) {
            $query .= " AND cm.name LIKE '%" . $filters['company'] . "%'";
        }
        if (!empty($filters['brand'])) {
            $query .= " AND dc.brand = '" . $filters['brand'] . "'";
        }
        if (isset($filters['invoice_status']) && $filters['invoice_status'] !== '') {
            if ($filters['invoice_status'] === 'invoiced') {
                $query .= " AND dc.is_invoiced = 1 AND (dc.is_cancelled = 0 OR dc.is_cancelled IS NULL)";
            } elseif ($filters['invoice_status'] === 'not_invoiced') {
                $query .= " AND dc.is_invoiced = 0";
            } elseif ($filters['invoice_status'] === 'cancelled') {
                $query .= " AND dc.is_cancelled = 1";
            }
        }

        $query .= " ORDER BY dc.id DESC, dcai.id ASC";

        $db = Database::getInstance();
        $result = $db->readQuery($query);
        $dags = array();

        while ($row = mysqli_fetch_assoc($result)) {
            $dag_id = $row['id'];

            if (!isset($dags[$dag_id])) {
                // Initialize the DAG master record
                $dags[$dag_id] = [
                    'id' => $row['id'],
                    'dag_number' => $row['dag_number'],
                    'my_number' => $row['my_number'],
                    'size' => $row['size'],
                    'brand' => $row['brand'],
                    'serial_no' => $row['serial_no'],
                    'dag_received_date' => $row['dag_received_date'],
                    'remark' => $row['remark'],
                    'cost' => $row['cost'],
                    'price' => $row['price'],
                    'discount' => $row['discount'],
                    'total' => $row['total'],
                    'is_invoiced' => $row['is_invoiced'],
                    'is_cancelled' => $row['is_cancelled'],
                    'issued_date' => $row['issued_date'],
                    'customer_name' => $row['customer_name'],
                    'customer_name_2' => $row['customer_name_2'],
                    'customer_code' => $row['customer_code'],
                    'company_assignments' => []
                ];
            }

            // Add company assignment if it exists
            if (!empty($row['company_name']) || !empty($row['company_status'])) {
                $dags[$dag_id]['company_assignments'][] = [
                    'company_name' => $row['company_name'],
                    'assignment_number' => $row['assignment_number'],
                    'company_receipt_number' => $row['company_receipt_number'],
                    'company_issued_date' => $row['company_issued_date'],
                    'job_number' => $row['job_number'],
                    'belt_design' => $row['belt_design'],
                    'company_status' => $row['company_status'],
                    'company_received_date' => $row['company_received_date'],
                    'uc_number' => $row['uc_number']
                ];
            }
        }

        // Return indexed array instead of associative array
        return array_values($dags);
    }

    public function getDistinctCompanies()
    {
        $query = "SELECT DISTINCT cm.name 
                  FROM `dag_company_assignment_items` dcai
                  JOIN `dag_company_assignments` dca ON dca.id = dcai.assignment_id
                  JOIN `company_master` cm ON cm.id = dca.company_id
                  ORDER BY cm.name ASC";
        $db = Database::getInstance();
        $result = $db->readQuery($query);
        $companies = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $companies[] = $row['name'];
        }
        return $companies;
    }

    public function getDistinctBrands()
    {
        $query = "SELECT DISTINCT TRIM(brand) as brand FROM `dag_customers` WHERE brand IS NOT NULL AND TRIM(brand) != '' ORDER BY brand ASC";
        $db = Database::getInstance();
        $result = $db->readQuery($query);
        $brands = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $brands[] = $row['brand'];
        }
        return $brands;
    }

}
