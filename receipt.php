<?php
session_start();
include "includes/functions.php";

if (!isset($_GET['order']) || !isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    get_redirect("cart.php");
}

// Store cart data for display
$_SESSION['receipt_cart'] = $_SESSION['cart'];

// Process order
add_order();

// Fetch cart items for display
$cart_items = [];
if (isset($_SESSION['receipt_cart'])) {
    foreach ($_SESSION['receipt_cart'] as $cart_item) {
        $item_id = $cart_item['item_id'];
        $query = "SELECT item_id, item_image, item_title, item_quantity, item_price, item_brand FROM item WHERE item_id = ?";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "i", $item_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $item = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        if (!empty($item)) {
            $item['quantity'] = $cart_item['quantity'];
            $cart_items[] = $item;
        }
    }
    unset($_SESSION['receipt_cart']); // Clear temporary cart data
}

$total = total_price($cart_items);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Receipt</title>
    <link rel="stylesheet" type="text/css" href="/print.css" media="print">
    <style>
        .btn-custom {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 0.3rem;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
            cursor: pointer;
            font-family: Arial, sans-serif;
        }
        .btn-custom:hover {
            background-color: #218838;
        }
        .button-container {
            text-align: right;
        }
        body {
            font-family: courier;
            font-size: 16px;
        }
        .receipt {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
        }
        .logo {
            text-align: center;
            margin-bottom: 10px;
        }
        .logo img {
            max-width: 200px;
            height: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .total {
            text-align: right;
            margin-top: 20px;
        }
        .print-button {
            text-align: center;
            margin-top: 20px;
        }
        .print-button button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #28a745;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        @media print {
            body {
                font-family: 'Courier', monospace;
                font-size: 16px;
            }
            .receipt {
                width: 80%;
                margin: 0 auto;
                padding: 20px;
                border: 1px solid #ccc;
                page-break-after: always;
            }
            .logo img {
                max-width: 200px;
                height: auto;
            }
            body * {
                visibility: hidden;
            }
            .receipt, .receipt * {
                visibility: visible;
            }
            .receipt {
                position: absolute;
                left: 0;
                top: 0;
            }
            .print-button, .button-container {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php message(); ?>
    <div class="receipt">
        <div class="logo">
            <img src="images/pharmAid.png" alt="PharmAid Logo">
        </div>
        <h2>Order Receipt</h2>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Brand</th>
                    <th>Price (Ksh)</th>
                    <th>Quantity</th>
                    <th>Total (Ksh)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item) : ?>
                    <tr>
                        <td><?php echo isset($item['item_title']) ? htmlspecialchars($item['item_title']) : ''; ?></td>
                        <td><?php echo isset($item['item_brand']) ? htmlspecialchars($item['item_brand']) : ''; ?></td>
                        <td><?php echo isset($item['item_price']) ? htmlspecialchars($item['item_price']) : ''; ?></td>
                        <td><?php echo isset($item['quantity']) ? htmlspecialchars($item['quantity']) : ''; ?></td>
                        <td><?php echo isset($item['item_price']) && isset($item['quantity']) ? htmlspecialchars($item['item_price'] * $item['quantity']) : ''; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="total">
            <h3>Total Amount: Ksh <?php echo htmlspecialchars($total); ?></h3>
        </div>
    </div>

    <div class="print-button">
        <button onclick="window.print()">Print Receipt</button>
    </div>
    <div class="button-container">
        <a href="final.php" class="btn btn-custom">Proceed to Confirmation</a>
    </div>
</body>
</html>