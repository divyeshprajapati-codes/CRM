<?php
require_once("../db.php");
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM quotations WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) {
    set_flash_message('error', 'Quotation not found.');
    header("Location: view_quotation.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quotation <?php echo e($row['quotation_no']); ?></title>
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

        .page {
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
            text-align: center;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 15px;
        }

        .header h1 {
            margin: 0;
            font-size: 26px;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header p {
            margin: 4px 0;
            color: #64748b;
            font-size: 14px;
        }

        .doc-title {
            margin-top: 15px;
            display: inline-block;
            background: #1e3a8a;
            color: #fff;
            padding: 4px 18px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 14px;
            letter-spacing: 1px;
        }

        .info {
            margin-top: 30px;
        }

        .info table {
            width: 100%;
            border: none;
        }

        .info td {
            padding: 6px 0;
            font-size: 14px;
            vertical-align: top;
        }

        .items {
            margin-top: 30px;
            width: 100%;
            border-collapse: collapse;
        }

        .items th {
            background: #f1f5f9;
            color: #1e293b;
            border: 1px solid #cbd5e1;
            padding: 12px;
            font-size: 14px;
            text-align: center;
        }

        .items td {
            border: 1px solid #cbd5e1;
            padding: 12px;
            text-align: center;
            font-size: 14px;
        }

        .total {
            margin-top: 30px;
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            color: #1e3a8a;
        }

        .footer {
            margin-top: 80px;
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
            .page {
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
    🖨️ Print Quotation
</button>

<div class="page">
    <div class="header">
        <h1><?php echo e(COMPANY_NAME); ?></h1>
        <p><?php echo e(COMPANY_TAGLINE); ?></p>
        <p><?php echo e(COMPANY_ADDRESS); ?> | Phone: <?php echo e(COMPANY_PHONE); ?> | Email: <?php echo e(COMPANY_EMAIL); ?></p>
        <div class="doc-title">QUOTATION</div>
    </div>

    <div class="info">
        <table>
            <tr>
                <td style="width:50%;">
                    <b>Quotation No:</b> <?php echo e($row['quotation_no']); ?><br>
                    <b>Status:</b> <?php echo e($row['status']); ?>
                </td>
                <td style="width:50%; text-align:right;">
                    <b>Date:</b> <?php echo date("d-m-Y", strtotime($row['created_at'])); ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding-top:15px;">
                    <b>Customer / Company:</b> <?php echo e($row['customer_name']); ?>
                </td>
            </tr>
        </table>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:8%;">#</th>
                <th style="text-align:left;">Product Description</th>
                <th style="width:15%;">Quantity</th>
                <th style="width:20%;">Unit Price</th>
                <th style="width:20%;">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td style="text-align:left;"><b><?php echo e($row['product']); ?></b></td>
                <td><?php echo e($row['quantity']); ?></td>
                <td><?php echo format_currency($row['price']); ?></td>
                <td><b><?php echo format_currency($row['total']); ?></b></td>
            </tr>
        </tbody>
    </table>

    <div class="total">
        Grand Total: <?php echo format_currency($row['total']); ?>
    </div>

    <div class="footer">
        <div>
            <p style="font-size:12px; color:#64748b; margin:0;">
                * This quotation is valid for 30 days from date of issue.<br>
                * Taxes and freight extra as applicable.
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
