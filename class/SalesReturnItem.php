<?php

class SalesReturnItem
{
    public $id;
    public $return_id;
    public $item_id;
    public $quantity;
    public $unit_price;
    public $discount;
    public $tax;
    public $net_amount;
    public $remarks;
    public $created_at;

    public function __construct($id = null)
    {
        if ($id) {
            $query = "SELECT * FROM `sales_return_items` WHERE `id` = " . (int)$id;
            $db = new Database();
            $result = mysqli_fetch_array($db->readQuery($query));

            if ($result) {
                $this->id = $result['id'];
                $this->return_id = $result['return_id'];
                $this->item_id = $result['item_id'];
                $this->quantity = $result['quantity'];
                $this->unit_price = $result['unit_price'];
                $this->discount = $result['discount'];
                $this->tax = $result['tax'];
                $this->net_amount = $result['net_amount'];
                $this->remarks = $result['remarks'];
                $this->created_at = $result['created_at'];
            }
        }
    }

    public function create()
    {
        $query = "INSERT INTO `sales_return_items` (
            `return_id`, `item_id`, `quantity`, `unit_price`, `discount`, `tax`, `net_amount`, `remarks`, `created_at`
        ) VALUES (
            '$this->return_id', '$this->item_id', '$this->quantity', '$this->unit_price', '$this->discount', '$this->tax', '$this->net_amount', '$this->remarks', NOW()
        )";

        $db = new Database();
        $result = $db->readQuery($query);

        if ($result) {
            return mysqli_insert_id($db->DB_CON);
        } else {
            return false;
        }
    }

    public function update()
    {
        $query = "UPDATE `sales_return_items` SET
            `return_id` = '$this->return_id',
            `item_id` = '$this->item_id',
            `quantity` = '$this->quantity',
            `unit_price` = '$this->unit_price',
            `discount` = '$this->discount',
            `tax` = '$this->tax',
            `net_amount` = '$this->net_amount',
            `remarks` = '$this->remarks'
            WHERE `id` = '$this->id'";

        $db = new Database();
        return $db->readQuery($query);
    }

    public function delete()
    {
        $query = "DELETE FROM `sales_return_items` WHERE `id` = '$this->id'";
        $db = new Database();
        return $db->readQuery($query);
    }

    public function getByReturnId($return_id)
    {
        $query = "SELECT * FROM `sales_return_items` WHERE `return_id` = '$return_id' ORDER BY `created_at` ASC";
        $db = new Database();
        $result = $db->readQuery($query);

        $array_res = array();
        while ($row = mysqli_fetch_array($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }

    public function deleteByReturnId($return_id)
    {
        $query = "DELETE FROM `sales_return_items` WHERE `return_id` = '$return_id'";
        $db = new Database();
        return $db->readQuery($query);
    }

    public function all()
    {
        $query = "SELECT * FROM `sales_return_items` ORDER BY `created_at` DESC";
        $db = new Database();
        $result = $db->readQuery($query);

        $array_res = array();
        while ($row = mysqli_fetch_array($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }
}

?>
