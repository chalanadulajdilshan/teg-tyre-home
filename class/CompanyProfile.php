<?php

class CompanyProfile
{
    public $id;
    public $name;
    public $address;
    public $mobile_number_1;
    public $mobile_number_2;
    public $mobile_number_3;
    public $email;
    public $image_name;
    public $is_active;
    public $is_vat;
    public $vat_number;
    public $vat_percentage;
    public $company_code;
    public $customer_id;
    public $theme;
    public $favicon;
    public $home_view_mode;
    public $navigation_layout;
    public $is_one_company;
    public $is_credit;

    private static $homeViewModeColumnChecked = false;
    private static $hasHomeViewModeColumn = false;

    private static function ensureHomeViewModeColumn()
    {
        if (self::$homeViewModeColumnChecked) {
            return;
        }

        self::$homeViewModeColumnChecked = true;
        $db = Database::getInstance();
        
        // Check home_view_mode
        $result = $db->readQuery("SHOW COLUMNS FROM `company_profile` LIKE 'home_view_mode'");
        if ($result && mysqli_num_rows($result) == 0) {
            $alterQuery = "ALTER TABLE `company_profile` ADD COLUMN `home_view_mode` VARCHAR(20) NOT NULL DEFAULT 'both'";
            @mysqli_query($db->DB_CON, $alterQuery);
        }
        
        // Check navigation_layout
        $result = $db->readQuery("SHOW COLUMNS FROM `company_profile` LIKE 'navigation_layout'");
        if ($result && mysqli_num_rows($result) == 0) {
            $alterQuery = "ALTER TABLE `company_profile` ADD COLUMN `navigation_layout` VARCHAR(20) NOT NULL DEFAULT 'horizontal'";
            @mysqli_query($db->DB_CON, $alterQuery);
        }

        self::$hasHomeViewModeColumn = true;
    }

    // Constructor to load data by ID
    public function __construct($id = null)
    {
        if ($id) {
            self::ensureHomeViewModeColumn();
            $query = "SELECT * FROM `company_profile` WHERE `id` = " . (int)$id;
            $db = Database::getInstance();
            $result = mysqli_fetch_array($db->readQuery($query));

            if ($result) {
                $this->id = $result['id'];
                $this->name = $result['name'];
                $this->address = $result['address'];
                $this->mobile_number_1 = $result['mobile_number_1'];
                $this->mobile_number_2 = $result['mobile_number_2'];
                $this->mobile_number_3 = $result['mobile_number_3'];
                $this->email = $result['email'];
                $this->image_name = $result['image_name'];
                $this->is_active = $result['is_active'];
                $this->is_vat = $result['is_vat'];
                $this->vat_number = $result['vat_number'];
                $this->vat_percentage = $result['vat_percentage'];
                $this->company_code = $result['company_code'];
                $this->customer_id = $result['customer_id'] ?? null;
                $this->theme = $result['theme'] ?? 'default';
                $this->favicon = $result['favicon'] ?? '';
                $this->home_view_mode = isset($result['home_view_mode']) && !empty($result['home_view_mode']) ? $result['home_view_mode'] : 'both';
                $this->navigation_layout = isset($result['navigation_layout']) && !empty($result['navigation_layout']) ? $result['navigation_layout'] : 'horizontal';
                $this->is_one_company = $result['is_one_company'];
                $this->is_credit = $result['is_credit'];
            }
        }
    }

    // Method to create a new company profile
    public function create()
    {
        self::ensureHomeViewModeColumn();
        $customer_id = ($this->customer_id === null || $this->customer_id === '') ? 'NULL' : (int)$this->customer_id;

        $homeViewMode = !empty($this->home_view_mode) ? $this->home_view_mode : 'both';
        $navigationLayout = !empty($this->navigation_layout) ? $this->navigation_layout : 'horizontal';
        
        $columns = "`name`, `address`, `mobile_number_1`, `mobile_number_2`, `mobile_number_3`, `email`, `image_name`, `is_active`, `is_vat`, `vat_number`, `company_code`, `vat_percentage`, `customer_id`, `theme`, `favicon`,`is_one_company`, `is_credit`, `home_view_mode`, `navigation_layout` ";
        $values = "'{$this->name}', '{$this->address}', '{$this->mobile_number_1}', '{$this->mobile_number_2}', '{$this->mobile_number_3}', '{$this->email}', '{$this->image_name}', '{$this->is_active}', '{$this->is_vat}', '{$this->vat_number}', '{$this->company_code}', '{$this->vat_percentage}', {$customer_id}, '{$this->theme}', '{$this->favicon}', '{$this->is_one_company}', '{$this->is_credit}', '{$homeViewMode}', '{$navigationLayout}'";

        $query = "INSERT INTO `company_profile` ({$columns}) VALUES ({$values})";
        $db = Database::getInstance();
        return $db->readQuery($query) ? mysqli_insert_id($db->DB_CON) : false;
    }

    // Method to update an existing company profile
    public function update()
    {
        self::ensureHomeViewModeColumn();
        $customer_id = ($this->customer_id === null || $this->customer_id === '') ? 'NULL' : (int)$this->customer_id;

        $homeViewMode = !empty($this->home_view_mode) ? $this->home_view_mode : 'both';
        $navigationLayout = !empty($this->navigation_layout) ? $this->navigation_layout : 'horizontal';
        
        $set = "`name` = '{$this->name}',
            `address` = '{$this->address}',
            `mobile_number_1` = '{$this->mobile_number_1}',
            `mobile_number_2` = '{$this->mobile_number_2}',
            `mobile_number_3` = '{$this->mobile_number_3}',
            `email` = '{$this->email}',
            `image_name` = '{$this->image_name}',
            `is_active` = '{$this->is_active}',
            `is_vat` = '{$this->is_vat}',
            `vat_number` = '{$this->vat_number}',
            `company_code` = '{$this->company_code}',
            `customer_id` = {$customer_id},
            `vat_percentage` = '{$this->vat_percentage}',
            `theme` = '{$this->theme}',
            `favicon` = '{$this->favicon}',
            `is_one_company` = '{$this->is_one_company}',
            `is_credit` = '{$this->is_credit}',
            `home_view_mode` = '{$homeViewMode}',
            `navigation_layout` = '{$navigationLayout}'";

        $query = "UPDATE `company_profile` SET 
            {$set}
            WHERE `id` = '{$this->id}'";
        $db = Database::getInstance();
        return $db->readQuery($query);
    }

    // Method to delete the company profile
    public function delete()
    {
        $query = "DELETE FROM `company_profile` WHERE `id` = '{$this->id}'";
        $db = Database::getInstance();
        return $db->readQuery($query);
    }

    // Method to get all company profiles
    public function all()
    {
        $query = "SELECT * FROM `company_profile` ORDER BY name ASC";
        $db = Database::getInstance();
        $result = $db->readQuery($query);
        $array = [];

        while ($row = mysqli_fetch_array($result)) {
            array_push($array, $row);
        }

        return $array;
    }

    // Method to get the active company
    public function getActiveCompany()
    {
        $query = "SELECT * FROM `company_profile` WHERE `is_active` = 1 ";
        $db = Database::getInstance();
        $result = $db->readQuery($query);
        $array = [];

        while ($row = mysqli_fetch_array($result)) {
            array_push($array, $row);
        }

        return $array;
    }
    /**
     * Check if the company is marked as 'One Company'
     * @return bool Returns true if marked as One Company, false otherwise
     */
    public function getIsOneCompany()
    {
        if ($this->id) {
            $query = "SELECT is_one_company FROM `company_profile` WHERE `id` = {$this->id} LIMIT 1";
            $db = Database::getInstance();
            $result = $db->readQuery($query);
            
            if ($result && $row = mysqli_fetch_assoc($result)) {
                return (bool)$row['is_one_company'];
            }
        }
        return false;
    }

    /**
     * Check if the company is marked as 'Credit'
     * @return bool Returns true if marked as Credit, false otherwise
     */
    public function getIsCredit()
    {
        if ($this->id) {
            $query = "SELECT is_credit FROM `company_profile` WHERE `id` = {$this->id} LIMIT 1";
            $db = Database::getInstance();
            $result = $db->readQuery($query);
            
            if ($result && $row = mysqli_fetch_assoc($result)) {
                return (bool)$row['is_credit'];
            }
        }
        return false;
    }
}
