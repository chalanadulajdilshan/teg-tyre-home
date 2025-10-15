<?php

class SalesInvoice
{
    public $id;
    public $is_cancel;
    public $ref_id;
    public $invoice_no;
    public $invoice_type;
    public $invoice_date;
    public $company_id;
    public $customer_id;
    public $customer_name;
    public $customer_mobile;
    public $customer_address;
    public $recommended_person;
    public $department_id;
    public $sale_type;
    public $discount_type;
    public $final_cost;
    public $payment_type;
    public $sub_total;
    public $discount;
    public $tax;
    public $grand_total;
    public $outstanding_settle_amount;
    public $remark;
    public $status;
    public $credit_period;
    public $due_date;

    // Constructor to initialize the SalesInvoice object with an ID
    public function __construct($id = null)
    {
        if ($id) {
            $query = "SELECT * FROM `sales_invoice` WHERE `id` = " . (int) $id;
            $db = new Database();
            $result = mysqli_fetch_array($db->readQuery($query));

            if ($result) {
                $this->id = $result['id'];
                $this->is_cancel = $result['is_cancel'];
                $this->ref_id = $result['ref_id'];
                $this->invoice_type = $result['invoice_type'];
                $this->invoice_no = $result['invoice_no'];
                $this->invoice_date = $result['invoice_date'];
                $this->company_id = $result['company_id'];
                $this->customer_id = $result['customer_id'];
                $this->customer_name = $result['customer_name'];
                $this->customer_mobile = $result['customer_mobile'];
                $this->customer_address = $result['customer_address'];
                $this->recommended_person = $result['recommended_person'];
                $this->department_id = $result['department_id'];
                $this->sale_type = $result['sale_type'];
                $this->discount_type = $result['discount_type'];
                $this->final_cost = $result['final_cost'];
                $this->payment_type = $result['payment_type'];
                $this->sub_total = $result['sub_total'];
                $this->discount = $result['discount'];
                $this->tax = $result['tax'];
                $this->grand_total = $result['grand_total'];
                $this->outstanding_settle_amount = $result['outstanding_settle_amount'];
                $this->remark = $result['remark'];
                $this->status = $result['status'];
                $this->credit_period = $result['credit_period'];
                $this->due_date = $result['due_date'];
            }
        }
    }

    // Create a new sales invoice record
    public function create()
    {
        $query = "INSERT INTO `sales_invoice` (
            `ref_id`,`invoice_type`,`invoice_no`, `invoice_date`, `company_id`, `customer_id`, `customer_name`, `customer_mobile`, `customer_address`, `recommended_person`, `department_id`, 
            `sale_type`, `discount_type`,`final_cost`, `payment_type`, `sub_total`, `discount`, 
            `tax`, `grand_total`, `outstanding_settle_amount`, `remark`, `credit_period`, `due_date`
        ) VALUES (
            '{$this->ref_id}','{$this->invoice_type}', '{$this->invoice_no}', '{$this->invoice_date}', '{$this->company_id}', '{$this->customer_id}', '{$this->customer_name}', '{$this->customer_mobile}', '{$this->customer_address}', '{$this->recommended_person}', '{$this->department_id}', 
            '{$this->sale_type}', '{$this->discount_type}', '{$this->final_cost}','{$this->payment_type}', '{$this->sub_total}', '{$this->discount}', 
            '{$this->tax}', '{$this->grand_total}', '{$this->outstanding_settle_amount}', '{$this->remark}', '{$this->credit_period}', '{$this->due_date}'
        )";



        $db = new Database();
        $result = $db->readQuery($query);

        if ($result) {
            return mysqli_insert_id($db->DB_CON);
        } else {
            return false;
        }
    }

    // Update an existing sales invoice record
    public function update()
    {
        $query = "UPDATE `sales_invoice` SET 
            `invoice_no` = '{$this->invoice_no}', 
            `invoice_type` = '{$this->invoice_type}', 
            `invoice_date` = '{$this->invoice_date}', 
            `company_id` = '{$this->company_id}', 
            `customer_id` = '{$this->customer_id}', 
            `customer_name` = '{$this->customer_name}', 
            `customer_mobile` = '{$this->customer_mobile}', 
            `customer_address` = '{$this->customer_address}', 
            `recommended_person` = '{$this->recommended_person}', 
            `department_id` = '{$this->department_id}', 
            `sale_type` = '{$this->sale_type}', 
            `discount_type` = '{$this->discount_type}', 
            `payment_type` = '{$this->payment_type}', 
            `sub_total` = '{$this->sub_total}', 
            `discount` = '{$this->discount}', 
            `tax` = '{$this->tax}', 
            `grand_total` = '{$this->grand_total}', 
            `remark` = '{$this->remark}' 
            WHERE `id` = '{$this->id}'";

        $db = new Database();
        $result = $db->readQuery($query);

        if ($result) {
            return $this->__construct($this->id);
        } else {
            return false;
        }
    }

    public function cancel()
    {

        // Use prepared statement to prevent SQL injection
        $query = "UPDATE `sales_invoice` SET `is_cancel` = 1 WHERE `id` = $this->id";

        $db = new Database();
        $result = $db->readQuery($query); // Assuming your Database class supports parameters

        if ($result) {

            return true; // Return boolean instead of calling constructor
        } else {
            return false;
        }
    }



    // Delete a sales invoice record by ID
    public function delete()
    {
        $query = "DELETE FROM `sales_invoice` WHERE `id` = '{$this->id}'";
        $db = new Database();
        return $db->readQuery($query);
    }

    // Retrieve all sales invoice records
    public function all()
    {
        $query = "SELECT * FROM `sales_invoice` ORDER BY `invoice_date` DESC";
        $db = new Database();
        $result = $db->readQuery($query);
        $array_res = array();

        while ($row = mysqli_fetch_array($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }

    public function fetchInvoicesForDataTable($request)
    {



        $db = new Database();
        $conn = $db->DB_CON;

        $start = isset($request['start']) ? (int) $request['start'] : 0;
        $length = isset($request['length']) ? (int) $request['length'] : 100;
        $search = $request['search']['value'] ?? '';

        $where = "WHERE 1=1";

        // Search filter
        if (!empty($search)) {
            $escapedSearch = mysqli_real_escape_string($conn, $search);
            $where .= " AND (invoice_no LIKE '%$escapedSearch%' OR remark LIKE '%$escapedSearch%')";
        }

        // Total records (without filters)
        $totalSql = "SELECT COUNT(*) as count FROM sales_invoice";
        $totalResult = $db->readQuery($totalSql);
        $totalData = mysqli_fetch_assoc($totalResult)['count'];

        // Total filtered records
        $filteredSql = "SELECT COUNT(*) as count FROM sales_invoice $where";
        $filteredResult = $db->readQuery($filteredSql);
        $filteredData = mysqli_fetch_assoc($filteredResult)['count'];

        // Paginated query
        $query = "SELECT * FROM sales_invoice $where ORDER BY invoice_date DESC LIMIT $start, $length";



        $result = $db->readQuery($query);

        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            // Optionally load related names if needed
            $CUSTOMER = new CustomerMaster($row['customer_id']);
            $DEPARTMENT = new DepartmentMaster($row['department_id']);

            $nestedData = [
                "id" => $row['id'], // Needed!
                "invoice_no" => $row['invoice_no'],
                "invoice_date" => $row['invoice_date'],
                "customer" => $CUSTOMER->name ?? $row['customer_id'],
                "department" => $DEPARTMENT->name ?? $row['department_id'],
                "grand_total" => number_format($row['grand_total'], 2),
                "remark" => $row['remark']
            ];


            $data[] = $nestedData;
        }
        return [
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($filteredData),
            "data" => $data
        ];
    }

    public function getLastID()
    {
        $query = "SELECT * FROM `sales_invoice` ORDER BY `id` DESC LIMIT 1";
        $db = new Database();
        $result = mysqli_fetch_array($db->readQuery($query));

        if ($result && isset($result['id'])) {
            return $result['id'];
        } else {
            return 0; // Or null, depending on how you want to handle "no results"
        }
    }

    public function getByID($id)
    {
        $query = "SELECT * FROM `sales_invoice` where `id` = '$id'";
        $db = new Database();
        $result = mysqli_fetch_array($db->readQuery($query));

        if ($result && isset($result['id'])) {
            return $result;
        } else {
            return 0; // Or null, depending on how you want to handle "no results"
        }
    }


    public function checkInvoiceIdExist($id)
    {
        $query = "SELECT * FROM `sales_invoice` where `invoice_no` = '$id' ";


        $db = new Database();
        $result = mysqli_fetch_array($db->readQuery($query));

        return ($result) ? true : false;
    }

    public static function filterSalesInvoices($filters)
    {
        $db = new Database();
        $conditions = [];

        // Customer filter
        if (empty($filters['all_customers']) && !empty($filters['customer_code'])) {
            $conditions[] = "`customer_id` = '" . $db->escapeString($filters['customer_code']) . "'";
        }

        // Date range or from-to
        if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
            $conditions[] = "`invoice_date` BETWEEN '" . $db->escapeString($filters['from_date']) . "' AND '" . $db->escapeString($filters['to_date']) . "'";
        }

        if (!empty($filters['date_range'])) {
            $today = date('Y-m-d');
            switch ($filters['date_range']) {
                case 'today':
                    $conditions[] = "`invoice_date` = '$today'";
                    break;
                case 'this_week':
                    $start = date('Y-m-d', strtotime('monday this week'));
                    $end = date('Y-m-d', strtotime('sunday this week'));
                    $conditions[] = "`invoice_date` BETWEEN '$start' AND '$end'";
                    break;
                case 'this_month':
                    $start = date('Y-m-01');
                    $end = date('Y-m-t');
                    $conditions[] = "`invoice_date` BETWEEN '$start' AND '$end'";
                    break;
            }
        }

        // // Status filter
        // if (!empty($filters['status'])) {
        //     $conditions[] = "`status` = '" . $db->escapeString($filters['status']) . "'";
        // }

        // Build WHERE clause
        $where = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

        $sql = "SELECT 
                `id`, `invoice_no`, `invoice_date`, `customer_id`, `final_cost`, `grand_total`, `status`
            FROM `sales_invoice`
            $where
            ORDER BY `invoice_date` DESC";

        $result = $db->readQuery($sql);

        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        return $data;
    }

    public static function getProfitTable($filters)
    {
        $db = new Database();
        $conditions = [];

        // Filter: Customer
        if (empty($filters['all_customers']) && !empty($filters['customer_id'])) {
            $conditions[] = "si.`customer_id` = '" . $db->escapeString($filters['customer_id']) . "'";
        }

        // Filter: Department
        if (!empty($filters['department_id'])) {
            $conditions[] = "si.`department_id` = '" . $db->escapeString($filters['department_id']) . "'";
        }

        // Company filter
        if (!empty($filters['company_id'])) {
            $conditions[] = "si.`company_id` = '" . $db->escapeString($filters['company_id']) . "'";
        }

        // Item filter - now applies to item code/name in the items table
        if (!empty($filters['item_code'])) {
            $conditions[] = "sii.`item_code` = '" . $db->escapeString($filters['item_code']) . "'";
        } elseif (!empty($filters['item_name'])) {
            $conditions[] = "sii.`item_name` LIKE '%" . $db->escapeString($filters['item_name']) . "%'";
        }

        // Date range filter
        if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
            $conditions[] = "si.`invoice_date` BETWEEN '" . $db->escapeString($filters['from_date']) . "' AND '" . $db->escapeString($filters['to_date']) . "'";
        }

        // Add condition to exclude canceled invoices
        $conditions[] = "si.`is_cancel` = 0";

        // Build WHERE clause
        $where = count($conditions) > 0 ? "WHERE " . implode(" AND ", $conditions) : "";

        // Final SQL query - now selecting item-level data
        $sql = "SELECT 
                    sii.id as item_id,
                    sii.item_name,
                    sii.item_code,
                    sii.service_item_code,
                    sii.quantity,
                    sii.cost as item_cost,
                    sii.price as item_price,
                    (sii.price - sii.cost) * sii.quantity as item_profit,
                    si.id,
                    si.invoice_no,
                    si.invoice_date,
                    si.company_id,
                    si.customer_id,
                    si.department_id,
                    si.sale_type,
                    si.outstanding_settle_amount
                FROM `sales_invoice_items` sii
                INNER JOIN `sales_invoice` si ON sii.invoice_id = si.id
                $where
                ORDER BY si.`invoice_date` DESC, sii.id ASC";

        $result = $db->readQuery($sql);

        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $COMPANY_PROFILE = new CompanyProfile($row['company_id']);
            $CUSTOMER_MASTER = new CustomerMaster($row['customer_id']);
            $DEPARTMENT_MASTER = new DepartmentMaster($row['department_id']);
            $SALES_TYPE = new SalesType($row['sale_type']);

            // Add display names
            $row['company_name'] = $COMPANY_PROFILE->name;
            $row['customer_name'] = $CUSTOMER_MASTER->name;
            $row['department_name'] = $DEPARTMENT_MASTER->name;
            $row['sales_type'] = $SALES_TYPE->code;

            // For backward compatibility, add aggregated fields (though now per item)
            $row['final_cost'] = $row['item_cost'] * $row['quantity'];
            $row['selling_price'] = $row['item_price'] * $row['quantity'];
            $row['profit'] = $row['item_profit'];
            $row['grand_total'] = $row['item_price'] * $row['quantity'];

            $data[] = $row;
        }

        return $data;
    }

    public function getCreditInvoicesByCustomerAndStatus($status, $customer_id)
    {
        
        $query = "SELECT * FROM `sales_invoice` 
                 WHERE `sale_type` = 2 
                 AND `status` = $status 
                 AND `customer_id` = $customer_id 
                 AND `grand_total` > `outstanding_settle_amount`
                 ORDER BY `invoice_date` DESC";
        $db = new Database();
        $result = $db->readQuery($query);
        $array_res = array();

        while ($row = mysqli_fetch_array($result)) {
            $array_res[] = $row;
        }

        return $array_res;
    }

    public function latest()
    {
        $query = "SELECT si.*, dm.name as department_name 
              FROM sales_invoice si
              LEFT JOIN department_master dm ON si.department_id = dm.id
              ORDER BY si.id DESC 
        LIMIT 10";
        $db = new Database();
        $result = $db->readQuery($query);
        $array_res = array();

        while ($row = mysqli_fetch_array($result)) {
            $array_res[] = $row;
            $DEPARTMENT_MASTER = new DepartmentMaster($row['department_id']);
            $row['department_name'] = $DEPARTMENT_MASTER->name;
        }

        return $array_res;
    }

    // Search invoices (invoice_no, customer, department)
    public function search($keyword)
    {
        $db = new Database();
        $keyword = $db->escapeString($keyword);

        $query = "SELECT si.*, dm.name as department_name 
                  FROM `sales_invoice` si
                  LEFT JOIN `customer_master` c ON si.customer_id = c.id
                  LEFT JOIN `department_master` dm ON si.department_id = dm.id
                  WHERE si.invoice_no LIKE '%$keyword%'
                     OR c.name LIKE '%$keyword%'
                     OR dm.name LIKE '%$keyword%'
                  ORDER BY si.id DESC
                  LIMIT 50";

        $result = $db->readQuery($query);
        $array_res = array();

        while ($row = mysqli_fetch_array($result)) {
            $array_res[] = $row;
        }

        return $array_res;
    }

    #feature-sales-summary-reports
    /**
     * Get sales summary report data for DataTables
     * 
     * @param array $filters Array of filter criteria
     * @return array Processed data for DataTables
     */
    public function getSalesSummaryReport($filters = [])
    {
        $db = new Database();

        // Base query
        $query = "SELECT 
                    si.id,
                    si.invoice_no as invoice_id,
                    si.invoice_date as date,
                    si.customer_name as customer,
                    dm.name as department,
                    si.grand_total as amount,
                    CASE 
                        WHEN si.sale_type = 1 THEN 'Cash Sale'
                        WHEN si.sale_type = 2 THEN 'Credit Sale'
                        ELSE 'Other'
                    END as sales_type
                  FROM `sales_invoice` si
                  LEFT JOIN `department_master` dm ON si.department_id = dm.id
                  WHERE 1=1";

        // Apply filters
        $params = [];

        // Customer filter
        if (!empty($filters['customer_id'])) {
            $customerId = $db->escapeString($filters['customer_id']);
            $query .= " AND si.customer_id = '$customerId'";
        }

        // Date range filter
        if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
            $fromDate = $db->escapeString($filters['from_date']);
            $toDate = $db->escapeString($filters['to_date']);
            $query .= " AND DATE(si.invoice_date) BETWEEN '$fromDate' AND '$toDate'";
        } elseif (!empty($filters['from_date'])) {
            $fromDate = $db->escapeString($filters['from_date']);
            $query .= " AND DATE(si.invoice_date) >= '$fromDate'";
        } elseif (!empty($filters['to_date'])) {
            $toDate = $db->escapeString($filters['to_date']);
            $query .= " AND DATE(si.invoice_date) <= '$toDate'";
        }

        // Get total records count for pagination
        $countQuery = "SELECT COUNT(*) as total FROM ($query) as total_count";
        $countResult = $db->readQuery($countQuery);
        $totalRecords = mysqli_fetch_assoc($countResult)['total'];

        // Add sorting
        $orderColumn = $_POST['order'][0]['column'] ?? 2; // Default sort by date (column index 2)
        $orderDir = $_POST['order'][0]['dir'] ?? 'DESC';
        $orderColumnName = '';

        // Map column index to database column name
        $columnMap = [
            0 => 'si.id',
            1 => 'invoice_id',
            2 => 'si.invoice_date',
            3 => 'customer',
            4 => 'dm.name',
            5 => 'sales_type',
            6 => 'si.grand_total'
        ];

        if (isset($columnMap[$orderColumn])) {
            $orderColumnName = $columnMap[$orderColumn];
            $query .= " ORDER BY $orderColumnName $orderDir";
        } else {
            $query .= " ORDER BY si.invoice_date DESC";
        }

        // Add pagination
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 25;
        $query .= " LIMIT $start, $length";

        // Execute query
        $result = $db->readQuery($query);
        $data = [];
        $totalAmount = 0;

        while ($row = mysqli_fetch_assoc($result)) {
            $amount = (float)$row['amount'];
            $totalAmount += $amount;
            $data[] = [
                'id' => $row['id'],
                'invoice_id' => $row['invoice_id'],
                'date' => date('Y-m-d', strtotime($row['date'])),
                'customer_name' => $row['customer'],
                'department' => $row['department'],
                'amount' => $amount, // Keep as number for DataTables
                'formatted_amount' => number_format($amount, 2), // Add formatted version for display
                'sales_type' => $row['sales_type'],
                'action' => '<a href="sales-invoice-view.php?inv=' . $row['invoice_id'] . '" class="btn btn-sm btn-info" target="_blank"><i class="uil uil-eye"></i> View</a>'
            ];
        }

        return [
            'data' => $data,
            'total_records' => $totalRecords,
            'total_amount' => number_format($totalAmount, 2)
        ];
    }

    public function updateInvoiceOutstanding($invoice_id, $amount)
    {
        $query = "UPDATE `sales_invoice` SET `outstanding_settle_amount` = `outstanding_settle_amount` + $amount WHERE `id` = $invoice_id";

        $db = new Database();
        $result = $db->readQuery($query);
        return ($result) ? true : false;
    }

    public function getMonthlyProfitByYear($year)
    {
        $db = new Database();
        $query = "SELECT 
                MONTH(invoice_date) as month,
                SUM(grand_total - final_cost) as total_profit
              FROM sales_invoice si
              WHERE YEAR(invoice_date) = '" . $db->escapeString($year) . "'
              AND is_cancel = 0
              GROUP BY MONTH(invoice_date)";

        $result = $db->readQuery($query);
        $data = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }

    // Get invoice by invoice number
    public function getInvoiceByNo($invoice_no)
    {
        $query = "SELECT * FROM `sales_invoice` WHERE `invoice_no` = '{$invoice_no}' LIMIT 1";
        $db = new Database();
        $result = mysqli_fetch_array($db->readQuery($query));
        return $result;
    }
}
