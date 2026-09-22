<?php
require_once("../db.php");
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) {
    set_flash_message('error', 'Invoice not found.');
    header("Location: view_invoice.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice <?php echo e($row['invoice_no']); ?></title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }

        body {
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            background: #e2e8f0;
            margin: 0;
            padding: 20px;
            color: #1e293b;
        }

        .a4 {
            width: 210mm;
            min-height: 297mm;
            background: white;
            margin: auto;
            padding: 25mm 20mm;
            box-sizing: border-box;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: relative;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .company h1 {
            margin: 0;
            font-size: 24px;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .company p {
            margin: 3px 0;
            color: #64748b;
            font-size: 13px;
        }

        .title {
            text-align: right;
        }

        .title h2 {
            margin: 0;
            font-size: 28px;
            color: #1e3a8a;
            letter-spacing: 2px;
        }

        .title p {
            margin: 4px 0;
            font-size: 16px;
            font-weight: bold;
            color: #475569;
        }

        .info {
            margin-top: 20px;
            line-height: 26px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        table th {
            background: #f1f5f9;
            color: #1e293b;
            border: 1px solid #cbd5e1;
            padding: 12px;
            text-align: center;
            font-size: 14px;
        }

        table td {
            border: 1px solid #cbd5e1;
            padding: 12px;
            text-align: center;
            font-size: 14px;
        }

        .grand-total {
            text-align: right;
            margin-top: 25px;
            font-size: 20px;
            font-weight: bold;
            color: #1e3a8a;
        }

        .footer {
            margin-top: 70px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 25px;
            background: #1565C0;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .print-btn:hover {
            background: #0d47a1;
        }

        @media print {
            .print-btn {
                display: none;
            }
            body {
                background: white;
                padding: 0;
            }
            .a4 {
                border: none;
                box-shadow: none;
                margin: 0;
                width: 100%;
                min-height: auto;
                padding: 0;
            }
        }
    </style>
</head>
<body>

<button class="print-btn" onclick="window.print()">
    🖨️ Print Invoice
</button>

<div class="a4">
    <div class="header">
        <div class="company">
            <h1><?php echo e(COMPANY_NAME); ?></h1>
            <p><?php echo e(COMPANY_TAGLINE); ?></p>
            <p><?php echo e(COMPANY_ADDRESS); ?></p>
            <p>Phone: <?php echo e(COMPANY_PHONE); ?> | Email: <?php echo e(COMPANY_EMAIL); ?></p>
        </div>

        <div class="title">
            <h2>TAX INVOICE</h2>
            <p><?php echo e($row['invoice_no']); ?></p>
            <div style="font-size:13px; font-weight:bold; color:<?php echo ($row['payment_status'] === 'Paid') ? '#10b981' : '#ef4444'; ?>;">
                Status: <?php echo e($row['payment_status']); ?>
            </div>
        </div>
    </div>

    <div class="info">
        <table style="border:none; margin:0;">
            <tr>
                <td style="border:none; text-align:left; padding:4px 0; width:50%;">
                    <b>Order No:</b> <?php echo e($row['order_no']); ?><br>
                    <b>Customer / M/s:</b> <?php echo e($row['customer_name']); ?>
                </td>
                <td style="border:none; text-align:right; padding:4px 0; width:50%;">
                    <b>Invoice Date:</b> <?php echo date("d-m-Y", strtotime($row['invoice_date'])); ?>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:8%;">#</th>
                <th style="text-align:left;">Product / Item Description</th>
                <th style="width:15%;">Quantity</th>
                <th style="width:20%;">Unit Price</th>
                <th style="width:20%;">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td style="text-align:left;"><b><?php echo e($row['product_name']); ?></b></td>
                <td><?php echo e($row['quantity']); ?></td>
                <td><?php echo format_currency($row['price']); ?></td>
                <td><b><?php echo format_currency($row['total']); ?></b></td>
            </tr>
        </tbody>
    </table>

    <div class="grand-total">
        Grand Total: <?php echo format_currency($row['total']); ?>
    </div>

    <div class="footer">
        <div>
            <b>Terms & Conditions:</b>
            <p style="font-size:12px; color:#64748b; margin:4px 0;">
                1. Goods once sold will not be taken back or exchanged.<br>
                2. Interest @ 18% p.a. will be charged if bill is not paid on presentation.<br>
                3. Subject to Ahmedabad jurisdiction only.
            </p>
        </div>

        <div style="text-align:center;">
            <br><br>
            ______________________________<br>
            <b>Authorized Signatory</b><br>
            <span style="font-size:12px; color:#64748b;"><?php echo e(COMPANY_NAME); ?></span>
        </div>
    </div>
</div>

</body>
</html>
