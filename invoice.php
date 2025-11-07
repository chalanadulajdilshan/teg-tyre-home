<?php
include 'class/include.php';

if (!isset($_SESSION)) {
  session_start();
}

$invoice_id = $_GET['invoice_no'];
$US = new User($_SESSION['id']);
$COMPANY_PROFILE = new CompanyProfile($US->company_id);
$SALES_INVOICE = new SalesInvoice($invoice_id);
$CUSTOMER_MASTER = new CustomerMaster($SALES_INVOICE->customer_id);
?>


<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>A5 Invoice</title>
  <style>
    @page {

      margin: 5mm;
    }

    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      font-size: 12px;
    }

    p {
      margin: 0;
      padding: 0;
    }
  </style>
</head>

<body>

  <!-- Main A5 Layout Wrapper -->
  <table style="  border-collapse:collapse; border:1px solid #000; table-layout:fixed;">
    <?php
    function formatPhone($number)
    {
      $number = preg_replace('/\D/', '', $number);
      if (strlen($number) == 10) {
        return sprintf("(%s) %s-%s", substr($number, 0, 3), substr($number, 3, 3), substr($number, 6));
      }
      return $number;
    }
    ?>
    <!-- Header Section -->
    <tr style="height:2.5cm; vertical-align:top;">
      <td colspan="5" style="border:none; padding:0.3cm;">
        <table style="width:100%; border-collapse:collapse; font-size:13px;">
          <tr>
            <!-- Company Info -->
            <td style="width:8cm; vertical-align:top;">
              <p style="font-weight:bold; font-size:18px;"><?php echo $COMPANY_PROFILE->name ?></p>
              <p><?php echo $COMPANY_PROFILE->address ?></p>
              <p><?php echo $COMPANY_PROFILE->email ?> | <?php echo formatPhone($COMPANY_PROFILE->mobile_number_1); ?></p>
            </td>

            <!-- Invoice Type & Customer -->
            <td style="width:7cm; vertical-align:top;">
              <p style="font-weight:bold; font-size:18px;"><?php echo ($SALES_INVOICE->payment_type == 1) ? "CASH SALES INVOICE" : "CREDIT SALES INVOICE"; ?></p>
              <p><strong>Name:</strong> <?php echo $SALES_INVOICE->customer_name ?></p>
              <p><strong>Contact:</strong> <?php echo !empty($SALES_INVOICE->customer_mobile) ? $SALES_INVOICE->customer_mobile : '.................................' ?></p>
            </td>

            <!-- Invoice Details -->
            <td style="width:6cm; vertical-align:top; text-align:right;">
              <p><strong>Inv No:</strong> <?php echo $SALES_INVOICE->invoice_no ?></p>
              <p><strong>Inv Date:</strong> <?php echo date('d M, Y', strtotime($SALES_INVOICE->invoice_date)); ?></p>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- Items Table Section -->
    <tr style="vertical-align:top;">
      <td colspan="5" style="border:none; padding:0cm;">
        <table style="border-collapse:collapse; width:100%; height:100%; table-layout:fixed; border:1px solid #000; font-size:12px;">
          <thead>
            <tr>

              <th style="border:1px solid #000; width:1.2cm; height:0.8cm; text-align:center;">No</th>
              <th style="border:1px solid #000; width:8cm; height:0.8cm; text-align:center;">Item</th>
              <th style="border:1px solid #000; width:2.2cm; height:0.8cm; text-align:center;">Qty</th>
              <th style="border:1px solid #000; width:3cm; height:0.8cm; text-align:center;">Price</th>
              <th style="border:1px solid #000; width:3.6cm; height:0.8cm; text-align:center;">Total</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $TEMP_SALES_ITEM = new SalesInvoiceItem(null);
            $temp_items_list = $TEMP_SALES_ITEM->getItemsByInvoiceId($invoice_id);
            $subtotal = 0;
            $total = 0;
            $total_discount = 0;

            foreach ($temp_items_list as $key => $temp_items) {
              $key++;
              $price = (float) $temp_items['price'];
              $quantity = (int) $temp_items['quantity'];
              $discount_percentage = isset($temp_items['discount']) ? (float) $temp_items['discount'] : 0;
              $discount_per_item = $price * ($discount_percentage / 100);
              $selling_price = $price - $discount_per_item;
              $line_total = $price * $quantity;
              $subtotal += $price * $quantity;
              $total_discount += $discount_per_item * $quantity;
              $total += $line_total;
              $ITEM_MASTER = new ItemMaster($temp_items['item_code']);
            ?>
              <tr>
                <td style='border:1px solid #000; text-align:center; height:0.8cm;'><?php echo $key; ?></td>
                <td style='border:1px solid #000; padding-left:0.2cm;'><?php echo $ITEM_MASTER->code . ' ' . $temp_items['item_name']; ?></td>
                <td style='border:1px solid #000; text-align:center;'><?php echo $quantity; ?></td>
                <td style='border:1px solid #000; text-align:right; padding-right:0.2cm;'><?php echo number_format($price, 2); ?></td>
                <td style='border:1px solid #000; text-align:right; padding-right:0.2cm;'><?php echo number_format($line_total, 2); ?></td>
              </tr>
            <?php
            }
            ?>
            <tr>
              <td colspan="4" style="border:1px solid #000; text-align:right; font-weight:bold;">Total</td>
              <td style="border:1px solid #000; text-align:right; font-weight:bold; padding-right:0.2cm;">
                <?php echo number_format($total, 2); ?>
              </td>
            </tr>
            <tr>
                                    <td colspan="5" style="padding-top:50px !important;">
                                        <table style="width:100%;">
                                            <tr>
                                                <td style="text-align:center;">_________________________<br><strong>Prepared By</strong></td>
                                                <td style="text-align:center;">_________________________<br><strong>Approved By</strong></td>
                                                <td style="text-align:center;">_________________________<br><strong>Received By</strong></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
          </tbody>
        </table>
      </td>
    </tr>
  </table>

</body>

</html>