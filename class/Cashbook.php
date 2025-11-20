<?php

class Cashbook
{
    public $id;
    public $ref_no;
    public $transaction_type; // 'deposit' or 'withdrawal'
    public $bank_id;
    public $branch_id;
    public $amount;
    public $remark;
    public $created_at;

    public function __construct($id = null)
    {
        if ($id) {
            $query = "SELECT * FROM `cashbook_transactions` WHERE `id` = " . (int) $id;
            $db = new Database();
            $result = mysqli_fetch_array($db->readQuery($query));

            if ($result) {
                $this->id = $result['id'];
                $this->ref_no = $result['ref_no'];
                $this->transaction_type = $result['transaction_type'];
                $this->bank_id = $result['bank_id'];
                $this->branch_id = $result['branch_id'];
                $this->amount = $result['amount'];
                $this->remark = $result['remark'];
                $this->created_at = $result['created_at'];
            }
        }
    }

    public function create()
    {
        $db = new Database();
        $ref_no = mysqli_real_escape_string($db->DB_CON, $this->ref_no);
        $transaction_type = mysqli_real_escape_string($db->DB_CON, $this->transaction_type);
        $bank_id = (int) $this->bank_id;
        $branch_id = (int) $this->branch_id;
        $amount = (float) $this->amount;
        $remark = mysqli_real_escape_string($db->DB_CON, $this->remark);

        $query = "INSERT INTO `cashbook_transactions` (
            `ref_no`, `transaction_type`, `bank_id`, `branch_id`, `amount`, `remark`, `created_at`
        ) VALUES (
            '$ref_no', '$transaction_type', '$bank_id', '$branch_id', '$amount', '$remark', NOW()
        )";

        $result = $db->readQuery($query);

        if ($result) {
            return mysqli_insert_id($db->DB_CON);
        }
        return false;
    }

    public function update()
    {
        $db = new Database();
        $ref_no = mysqli_real_escape_string($db->DB_CON, $this->ref_no);
        $transaction_type = mysqli_real_escape_string($db->DB_CON, $this->transaction_type);
        $bank_id = (int) $this->bank_id;
        $branch_id = (int) $this->branch_id;
        $amount = (float) $this->amount;
        $remark = mysqli_real_escape_string($db->DB_CON, $this->remark);
        $id = (int) $this->id;

        $query = "UPDATE `cashbook_transactions` SET 
            `ref_no` = '$ref_no',
            `transaction_type` = '$transaction_type',
            `bank_id` = '$bank_id',
            `branch_id` = '$branch_id',
            `amount` = '$amount',
            `remark` = '$remark'
            WHERE `id` = '$id'";

        return $db->readQuery($query);
    }

    public function delete()
    {
        $id = (int) $this->id;
        $query = "DELETE FROM `cashbook_transactions` WHERE `id` = '$id'";
        $db = new Database();
        return $db->readQuery($query);
    }

    public function all()
    {
        $query = "SELECT 
            ct.*,
            b.name as bank_name,
            br.name as branch_name
            FROM `cashbook_transactions` ct
            LEFT JOIN `banks` b ON ct.bank_id = b.id
            LEFT JOIN `branches` br ON ct.branch_id = br.id
            ORDER BY ct.created_at DESC";
        $db = new Database();
        $result = $db->readQuery($query);

        $array = [];
        while ($row = mysqli_fetch_array($result)) {
            array_push($array, $row);
        }

        return $array;
    }

    public function getLastID()
    {
        $query = "SELECT * FROM `cashbook_transactions` ORDER BY `id` DESC LIMIT 1";
        $db = new Database();
        $result = mysqli_fetch_array($db->readQuery($query));

        if ($result && isset($result['id'])) {
            return $result['id'];
        }
        return 0;
    }

    // Get opening balance from company profile
    public function getOpeningBalance()
    {
        $query = "SELECT cashbook_opening_balance FROM `company_profile` WHERE `is_active` = 1 LIMIT 1";
        $db = new Database();
        $result = mysqli_fetch_array($db->readQuery($query));

        if ($result && isset($result['cashbook_opening_balance'])) {
            return (float) $result['cashbook_opening_balance'];
        }
        return 0;
    }

    // Get total cash IN from various sources
    public function getTotalCashIn($dateFrom = null, $dateTo = null)
    {
        $db = new Database();
        $where = "WHERE 1=1";
        
        if ($dateFrom && $dateTo) {
            $dateFrom = mysqli_real_escape_string($db->DB_CON, $dateFrom);
            $dateTo = mysqli_real_escape_string($db->DB_CON, $dateTo);
            $where .= " AND DATE(si.invoice_date) BETWEEN '$dateFrom' AND '$dateTo'";
        }

        // Cash from sales invoices (payment_type = 1 means cash)
        $queryCashInvoices = "SELECT COALESCE(SUM(grand_total), 0) as total 
                              FROM `sales_invoice` si
                              $where AND si.payment_type = 1";
        $resultCash = mysqli_fetch_array($db->readQuery($queryCashInvoices));
        $totalCashInvoices = (float) $resultCash['total'];

        // Cash from payment receipts (customer payments)
        $wherePayment = str_replace('si.invoice_date', 'pr.entry_date', $where);
        $queryPaymentReceipts = "SELECT COALESCE(SUM(amount_paid), 0) as total 
                                 FROM `payment_receipt` pr
                                 $wherePayment";
        $resultPayment = mysqli_fetch_array($db->readQuery($queryPaymentReceipts));
        $totalPaymentReceipts = (float) $resultPayment['total'];

        // Cash from daily income
        $whereIncome = str_replace('si.invoice_date', 'di.date', $where);
        $queryDailyIncome = "SELECT COALESCE(SUM(amount), 0) as total 
                            FROM `daily_income` di
                            $whereIncome";
        $resultIncome = mysqli_fetch_array($db->readQuery($queryDailyIncome));
        $totalDailyIncome = (float) $resultIncome['total'];

        // Withdrawals (add to cash)
        $whereWithdrawal = str_replace('si.invoice_date', 'created_at', $where);
        $queryWithdrawals = "SELECT COALESCE(SUM(amount), 0) as total 
                            FROM `cashbook_transactions`
                            $whereWithdrawal AND transaction_type = 'withdrawal'";
        $resultWithdrawal = mysqli_fetch_array($db->readQuery($queryWithdrawals));
        $totalWithdrawals = (float) $resultWithdrawal['total'];

        return $totalCashInvoices + $totalPaymentReceipts + $totalDailyIncome + $totalWithdrawals;
    }

    // Get total cash OUT from various sources
    public function getTotalCashOut($dateFrom = null, $dateTo = null)
    {
        $db = new Database();
        $where = "WHERE 1=1";
        
        if ($dateFrom && $dateTo) {
            $dateFrom = mysqli_real_escape_string($db->DB_CON, $dateFrom);
            $dateTo = mysqli_real_escape_string($db->DB_CON, $dateTo);
            $where .= " AND DATE(e.expense_date) BETWEEN '$dateFrom' AND '$dateTo'";
        }

        // Cash from expenses
        $queryExpenses = "SELECT COALESCE(SUM(amount), 0) as total 
                         FROM `expenses` e
                         $where";
        $resultExpenses = mysqli_fetch_array($db->readQuery($queryExpenses));
        $totalExpenses = (float) $resultExpenses['total'];

        // Cash for supplier payments
        $whereSupplier = str_replace('e.expense_date', 'prs.entry_date', $where);
        $querySupplierPayments = "SELECT COALESCE(SUM(amount_paid), 0) as total 
                                  FROM `payment_receipt_supplier` prs
                                  $whereSupplier";
        $resultSupplier = mysqli_fetch_array($db->readQuery($querySupplierPayments));
        $totalSupplierPayments = (float) $resultSupplier['total'];

        // Cash for ARN (purchase returns)
        $whereArn = str_replace('e.expense_date', 'am.entry_date', $where);
        $queryArn = "SELECT COALESCE(SUM(total_arn_value), 0) as total 
                    FROM `arn_master` am
                    $whereArn";
        $resultArn = mysqli_fetch_array($db->readQuery($queryArn));
        $totalArn = (float) $resultArn['total'];

        // Bank deposits (remove from cash)
        $whereDeposit = str_replace('e.expense_date', 'created_at', $where);
        $queryDeposits = "SELECT COALESCE(SUM(amount), 0) as total 
                         FROM `cashbook_transactions`
                         $whereDeposit AND transaction_type = 'deposit'";
        $resultDeposit = mysqli_fetch_array($db->readQuery($queryDeposits));
        $totalDeposits = (float) $resultDeposit['total'];

        return $totalExpenses + $totalSupplierPayments + $totalArn + $totalDeposits;
    }

    // Get balance in hand
    public function getBalanceInHand($dateFrom = null, $dateTo = null)
    {
        // Get all transactions and return the final balance
        $transactions = $this->getAllTransactionsDetailed($dateFrom, $dateTo);
        
        if (empty($transactions)) {
            return $this->getOpeningBalance();
        }
        
        // Get the last transaction's balance
        $lastTransaction = end($transactions);
        $balance = (float)str_replace(',', '', $lastTransaction['balance']);
        
        return $balance;
    }

    // Get all transactions with details
    public function getAllTransactionsDetailed($dateFrom = null, $dateTo = null)
    {
        $db = new Database();
        $transactions = [];

        // Add opening balance as first transaction
        $openingBalance = $this->getOpeningBalance();
        $transactions[] = [
            'date' => '',
            'account_type' => 'CASH',
            'transaction' => 'IN',
            'description' => 'Opening Balance',
            'doc' => '',
            'debit' => number_format($openingBalance, 2),
            'credit' => '0.00',
            'balance' => number_format($openingBalance, 2),
            'sort_date' => '0000-00-00 00:00:00'
        ];

        $runningBalance = $openingBalance;
        $where = " WHERE 1=1";
        
        if ($dateFrom && $dateTo) {
            $dateFrom = mysqli_real_escape_string($db->DB_CON, $dateFrom);
            $dateTo = mysqli_real_escape_string($db->DB_CON, $dateTo);
            $where .= " AND DATE(invoice_date) BETWEEN '$dateFrom' AND '$dateTo'";
        }

        // Cash sales invoices
        $query = "SELECT invoice_date as date, invoice_no as doc, grand_total as amount, 'Cash Sale' as description
                  FROM sales_invoice 
                  $where AND payment_type = 1
                  ORDER BY invoice_date ASC";
        $result = $db->readQuery($query);
        while ($row = mysqli_fetch_array($result)) {
            $runningBalance += (float)$row['amount'];
            $transactions[] = [
                'date' => date('Y-m-d', strtotime($row['date'])),
                'account_type' => 'CASH',
                'transaction' => 'IN',
                'description' => $row['description'],
                'doc' => $row['doc'],
                'debit' => number_format($row['amount'], 2),
                'credit' => '0.00',
                'balance' => number_format($runningBalance, 2),
                'sort_date' => $row['date']
            ];
        }

        // Payment receipts
        $wherePayment = str_replace('invoice_date', 'entry_date', $where);
        $query = "SELECT pr.entry_date as date, pr.receipt_no as doc, pr.amount_paid as amount, 
                  CONCAT('Payment from ', cm.name) as description
                  FROM payment_receipt pr
                  LEFT JOIN customer_master cm ON pr.customer_id = cm.id
                  $wherePayment
                  ORDER BY pr.entry_date ASC";
        $result = $db->readQuery($query);
        while ($row = mysqli_fetch_array($result)) {
            $runningBalance += (float)$row['amount'];
            $transactions[] = [
                'date' => date('Y-m-d', strtotime($row['date'])),
                'account_type' => 'CASH',
                'transaction' => 'IN',
                'description' => $row['description'],
                'doc' => $row['doc'],
                'debit' => number_format($row['amount'], 2),
                'credit' => '0.00',
                'balance' => number_format($runningBalance, 2),
                'sort_date' => $row['date']
            ];
        }

        // Daily income
        $whereIncome = str_replace('invoice_date', 'date', $where);
        $query = "SELECT date, CONCAT('DI-', id) as doc, amount, COALESCE(remark, 'Daily Income') as description
                  FROM daily_income
                  $whereIncome
                  ORDER BY date ASC";
        $result = $db->readQuery($query);
        while ($row = mysqli_fetch_array($result)) {
            $runningBalance += (float)$row['amount'];
            $transactions[] = [
                'date' => date('Y-m-d', strtotime($row['date'])),
                'account_type' => 'CASH',
                'transaction' => 'IN',
                'description' => $row['description'],
                'doc' => $row['doc'],
                'debit' => number_format($row['amount'], 2),
                'credit' => '0.00',
                'balance' => number_format($runningBalance, 2),
                'sort_date' => $row['date']
            ];
        }

        // Expenses
        $whereExpense = str_replace('invoice_date', 'expense_date', $where);
        $query = "SELECT e.expense_date as date, e.code as doc, e.amount, 
                  CONCAT('Expense - ', et.name) as description
                  FROM expenses e
                  LEFT JOIN expenses_type et ON e.expense_type_id = et.id
                  $whereExpense
                  ORDER BY e.expense_date ASC";
        $result = $db->readQuery($query);
        while ($row = mysqli_fetch_array($result)) {
            $runningBalance -= (float)$row['amount'];
            $transactions[] = [
                'date' => date('Y-m-d', strtotime($row['date'])),
                'account_type' => 'CASH',
                'transaction' => 'OUT',
                'description' => $row['description'],
                'doc' => $row['doc'],
                'debit' => '0.00',
                'credit' => number_format($row['amount'], 2),
                'balance' => number_format($runningBalance, 2),
                'sort_date' => $row['date']
            ];
        }

        // Supplier payments
        $whereSupplier = str_replace('invoice_date', 'entry_date', $where);
        $query = "SELECT prs.entry_date as date, prs.receipt_no as doc, prs.amount_paid as amount,
                  CONCAT('Payment to Supplier') as description
                  FROM payment_receipt_supplier prs
                  $whereSupplier
                  ORDER BY prs.entry_date ASC";
        $result = $db->readQuery($query);
        while ($row = mysqli_fetch_array($result)) {
            $runningBalance -= (float)$row['amount'];
            $transactions[] = [
                'date' => date('Y-m-d', strtotime($row['date'])),
                'account_type' => 'CASH',
                'transaction' => 'OUT',
                'description' => $row['description'],
                'doc' => $row['doc'],
                'debit' => '0.00',
                'credit' => number_format($row['amount'], 2),
                'balance' => number_format($runningBalance, 2),
                'sort_date' => $row['date']
            ];
        }

        // Bank deposits
        $whereDeposit = str_replace('invoice_date', 'ct.created_at', $where);
        $query = "SELECT ct.created_at as date, ct.ref_no as doc, ct.amount, 
                  CONCAT('Bank Deposit - ', b.name, ' (', br.name, ')') as description
                  FROM cashbook_transactions ct
                  LEFT JOIN banks b ON ct.bank_id = b.id
                  LEFT JOIN branches br ON ct.branch_id = br.id
                  $whereDeposit AND ct.transaction_type = 'deposit'
                  ORDER BY ct.created_at ASC";
        $result = $db->readQuery($query);
        while ($row = mysqli_fetch_array($result)) {
            $runningBalance -= (float)$row['amount'];
            $transactions[] = [
                'date' => date('Y-m-d H:i:s', strtotime($row['date'])),
                'account_type' => 'CASH',
                'transaction' => 'OUT',
                'description' => $row['description'],
                'doc' => $row['doc'],
                'debit' => '0.00',
                'credit' => number_format($row['amount'], 2),
                'balance' => number_format($runningBalance, 2),
                'sort_date' => $row['date']
            ];
        }

        // Withdrawals
        $whereWithdrawal = str_replace('invoice_date', 'ct.created_at', $where);
        $query = "SELECT ct.created_at as date, ct.ref_no as doc, ct.amount, 
                  CONCAT('Bank Withdrawal - ', b.name) as description
                  FROM cashbook_transactions ct
                  LEFT JOIN banks b ON ct.bank_id = b.id
                  $whereWithdrawal AND ct.transaction_type = 'withdrawal'
                  ORDER BY ct.created_at ASC";
        $result = $db->readQuery($query);
        while ($row = mysqli_fetch_array($result)) {
            $runningBalance += (float)$row['amount'];
            $transactions[] = [
                'date' => date('Y-m-d H:i:s', strtotime($row['date'])),
                'account_type' => 'CASH',
                'transaction' => 'IN',
                'description' => $row['description'],
                'doc' => $row['doc'],
                'debit' => number_format($row['amount'], 2),
                'credit' => '0.00',
                'balance' => number_format($runningBalance, 2),
                'sort_date' => $row['date']
            ];
        }

        // Sort by date
        usort($transactions, function($a, $b) {
            return strcmp($a['sort_date'], $b['sort_date']);
        });

        // Recalculate running balance after sorting
        $runningBalance = 0;
        foreach ($transactions as &$transaction) {
            // Get the debit and credit amounts (remove formatting)
            $debit = (float)str_replace(',', '', $transaction['debit']);
            $credit = (float)str_replace(',', '', $transaction['credit']);
            
            // Update running balance
            $runningBalance += $debit - $credit;
            
            // Update the balance in the transaction
            $transaction['balance'] = number_format($runningBalance, 2);
        }

        return $transactions;
    }
}
