<?php
include_once '../../class/include.php';
header('Content-Type: application/json');

//price control laord
if (isset($_POST['action']) && $_POST['action'] == 'loard_price_Control') {

    $category_id = $_POST['category_id'] ?? 0;
    $brand_id = $_POST['brand_id'] ?? 0;
    $group_id = $_POST['group_id'] ?? 0;
    $department_id = $_POST['department_id'] ?? 0;
    $item_code = $_POST['item_code'] ?? '';

    $ITEM = new ItemMaster(NULL);
    $items = $ITEM->getItemsFiltered($category_id, $brand_id, $group_id, $department_id, $item_code);

    echo json_encode($items);
    exit;
}

// Handle monthly sales data request
if (isset($_POST['action']) && $_POST['action'] === 'get_monthly_sales') {
    try {
        $year = isset($_POST['year']) ? (int)$_POST['year'] : date('Y');

        $salesInvoice = new SalesInvoice();
        $monthlySales = $salesInvoice->getMonthlySalesByYear($year);

        $salesMap = [];
        foreach ($monthlySales as $row) {
            $salesMap[$row['month']] = (float)$row['total_sales'];
        }

        $data = [];
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        for ($m = 1; $m <= 12; $m++) {
            $data[] = [
                'month' => $monthNames[$m - 1],
                'value' => round($salesMap[$m] ?? 0)
            ];
        }

        echo json_encode([
            'status' => 'success',
            'data' => $data
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to load monthly sales data: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Summary cards data (current month)
if (isset($_POST['action']) && $_POST['action'] === 'get_dashboard_cards') {
    try {
        $db = Database::getInstance();
        $year = date('Y');
        $month = date('m');
        $fromDate = date('Y-m-01');
        $toDate = date('Y-m-d');

        // Sales + profit per item (same as profit report)
        $filters = [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'all_customers' => 1
        ];
        $salesInvoice = new SalesInvoice();
        $items = $salesInvoice->getProfitTable($filters);

        $salesTotal = 0; // gross selling
        $profitTotal = 0; // item_profit sum
        foreach ($items as $row) {
            $salesTotal += (float)($row['selling_price'] ?? 0);
            $profitTotal += (float)($row['profit'] ?? 0);
        }

        // Expenses (month to date)
        $expense = new Expense();
        $monthlyExpenses = $expense->getTotalExpensesByDateRange($fromDate, $toDate);

        // Returns (month to date)
        $salesReturn = new SalesReturn();
        $monthlyReturns = $salesReturn->getTotalReturnsByDateRange($fromDate, $toDate);

        // Daily income (month to date)
        $dailyIncome = new DailyIncome();
        $monthlyDailyIncome = $dailyIncome->getTotalIncome($fromDate, $toDate);

        // Final profit matches profit report: profit - returns - expenses + daily income
        $finalProfit = $profitTotal - $monthlyReturns - $monthlyExpenses + $monthlyDailyIncome;

        // Net sales after returns
        $netSales = $salesTotal - $monthlyReturns;

        // Total stock (sum quantities)
        $stockSql = "SELECT IFNULL(SUM(quantity),0) AS qty FROM stock_master WHERE is_active = 1";
        $stockRes = mysqli_fetch_assoc($db->readQuery($stockSql));
        $totalStock = (float)($stockRes['qty'] ?? 0);

        echo json_encode([
            'status' => 'success',
            'data' => [
                'monthly_sales' => round($netSales),
                'monthly_sales_gross' => round($salesTotal),
                'monthly_expenses' => round($monthlyExpenses),
                'monthly_returns' => round($monthlyReturns),
                'monthly_daily_income' => round($monthlyDailyIncome),
                'monthly_profit' => round($finalProfit),
                'total_stock' => round($totalStock)
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to load dashboard cards: ' . $e->getMessage()
        ]);
    }
    exit;
}

//load all active brand items (for pre-invoice)
if (isset($_POST['action']) && $_POST['action'] == 'load_all_active_items') {

    $category_id = $_POST['category_id'] ?? 0;
    $brand_id = $_POST['brand_id'] ?? 0;
    $group_id = $_POST['group_id'] ?? 0;
    $item_code = $_POST['item_code'] ?? '';

    $ITEM = new ItemMaster(NULL);
    $items = $ITEM->getItemsByActiveBrands($category_id, $brand_id, $group_id, $item_code);

    echo json_encode($items);
    exit;
}

//check pre-invoice pending qty for an item (used during GRN entry)
if (isset($_POST['action']) && $_POST['action'] == 'check_pre_invoice_qty') {
    $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
    $pre_invoice_qty = 0;

    if ($item_id > 0) {
        $db = Database::getInstance();
        $query = "SELECT SUM(remaining_qty) as total_remaining FROM `pre_invoice_pending` WHERE `item_id` = '{$item_id}' AND `remaining_qty` > 0";
        $result = $db->readQuery($query);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $pre_invoice_qty = (float)($row['total_remaining'] ?? 0);
        }
    }

    echo json_encode(['pre_invoice_qty' => $pre_invoice_qty]);
    exit;
}

//profit table load
if (isset($_POST['action']) && $_POST['action'] === 'load_profit_report') {
    // Collect filters into an array
    $filters = [
        'category_id' => isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0,
        'brand_id' => isset($_POST['brand_id']) ? (int) $_POST['brand_id'] : 0,
        'group_id' => isset($_POST['group_id']) ? (int) $_POST['group_id'] : 0,
        'department_id' => isset($_POST['department_id']) ? (int) $_POST['department_id'] : 0,
        'item_code' => isset($_POST['item_code']) ? trim($_POST['item_code']) : '',
        'item_name' => isset($_POST['item_name']) ? trim($_POST['item_name']) : '',
        'customer_id' => isset($_POST['customer_id']) ? (int) $_POST['customer_id'] : 0,
        'company_id' => isset($_POST['company_id']) ? (int) $_POST['company_id'] : 0,
        'from_date' => isset($_POST['from_date']) ? $_POST['from_date'] : '',
        'to_date' => isset($_POST['to_date']) ? $_POST['to_date'] : '',
        'all_customers' => isset($_POST['all_customers']) ? $_POST['all_customers'] : false
    ];

    // If item name is provided but not item code, we'll use that for filtering
    if (empty($filters['item_code']) && !empty($filters['item_name'])) {
        // No need to set item_code here as we'll use item_name in the query
    }

    // Load profit data
    $salesInvoice = new SalesInvoice(NULL);
    $items = $salesInvoice->getProfitTable($filters);

    // Calculate total expenses for the same date range
    $totalExpenses = 0;
    if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
        $expense = new Expense(NULL);
        $totalExpenses = $expense->getTotalExpensesByDateRange($filters['from_date'], $filters['to_date']);
    }

    // Calculate total return value for the same date range
    $totalReturns = 0;
    if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
        $salesReturn = new SalesReturn(NULL);
        $totalReturns = $salesReturn->getTotalReturnsByDateRange($filters['from_date'], $filters['to_date']);
    }

    // Calculate total daily income for the same date range
    $totalDailyIncome = 0;
    if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
        $dailyIncome = new DailyIncome(NULL);
        $totalDailyIncome = $dailyIncome->getTotalIncome($filters['from_date'], $filters['to_date']);
    }

    // Prepare response with sales data, expense total, and daily income total
    $response = [
        'sales_data' => $items,
        'total_expenses' => $totalExpenses,
        'total_daily_income' => $totalDailyIncome,
        'total_returns' => $totalReturns
    ];

    // Output JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'update_stock_tmp_price') {

    $id = (int) $_POST['id'];
    $field = $_POST['field'];
    $value = $_POST['value'];

    $STOCK_ITEM_TMP = new StockItemTmp(NULL);

    $response = $STOCK_ITEM_TMP->updateStockItemTmpPrice($id, $field, $value);
    //audit log
    $AUDIT_LOG = new AuditLog(NUll);
    $AUDIT_LOG->ref_id = $_POST['id'];
    $AUDIT_LOG->ref_code = '#ITEM/PRICE/UPDATE';
    $AUDIT_LOG->action = 'UPDATE';
    $AUDIT_LOG->description = 'UPDATE ITEM NO PRICES ';
    $AUDIT_LOG->user_id = $_SESSION['id'];
    $AUDIT_LOG->created_at = date("Y-m-d H:i:s");
    $AUDIT_LOG->create();

    echo json_encode($response);
    exit;
}

// Update item price
// Handle monthly profit data request
if (isset($_POST['action']) && $_POST['action'] === 'get_monthly_profit') {
    try {
        $year = isset($_POST['year']) ? (int)$_POST['year'] : date('Y');

        $salesInvoice = new SalesInvoice();
        $monthlySalesProfit = $salesInvoice->getMonthlyProfitByYear($year);

        $expense = new Expense();
        $monthlyExpenses = $expense->getMonthlyExpensesByYear($year);

        $salesMap = [];
        foreach ($monthlySalesProfit as $row) {
            $salesMap[$row['month']] = (float)$row['total_profit'];
        }

        $expMap = [];
        foreach ($monthlyExpenses as $row) {
            $expMap[$row['month']] = (float)$row['total_amount'];
        }

        $data = [];
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        for ($m = 1; $m <= 12; $m++) {
            $profit = ($salesMap[$m] ?? 0) - ($expMap[$m] ?? 0);
            $data[] = [
                'month' => $monthNames[$m - 1],
                'value' => round($profit)
            ];
        }

        echo json_encode([
            'status' => 'success',
            'data' => $data
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to load monthly profit data: ' . $e->getMessage()
        ]);
    }
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'update_item_price') {
    try {
        $item_id = (int)$_POST['item_id'];
        $new_price = (float)$_POST['new_price'];

        if ($item_id <= 0 || $new_price < 0) {
            throw new Exception('Invalid input parameters');
        }

        // Load the item
        $ITEM = new ItemMaster($item_id);

        if (!$ITEM->id) {
            throw new Exception('Item not found');
        }

        // Update the price
        $ITEM->list_price = $new_price;

        // Recalculate invoice price if needed (based on discount)
        if ($ITEM->discount > 0) {
            $discount_amount = $new_price * ($ITEM->discount / 100);
            $ITEM->invoice_price = $new_price - $discount_amount;
        } else {
            $ITEM->invoice_price = $new_price;
        }

        // Save the changes
        $result = $ITEM->update();

        if ($result) {
            // Add audit log
            $AUDIT_LOG = new AuditLog(null);
            $AUDIT_LOG->ref_id = $item_id;
            $AUDIT_LOG->ref_code = $ITEM->code;
            $AUDIT_LOG->action = 'UPDATE';
            $AUDIT_LOG->description = 'UPDATED ITEM PRICE TO ' . $new_price;
            $AUDIT_LOG->user_id = $_SESSION['id'];
            $AUDIT_LOG->created_at = date('Y-m-d H:i:s');
            $AUDIT_LOG->create();

            echo json_encode([
                'status' => 'success',
                'message' => 'Price updated successfully'
            ]);
        } else {
            throw new Exception('Failed to update item price');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
    exit;
}
