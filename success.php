<?php
session_start();
include 'connect.php';

if (!isset($_GET['orderID'])) {
    die("Order ID tidak ditemukan.");
}

$orderID = $_GET['orderID'];

$order_query = "SELECT o.orderID, o.tax,o.orderDate, o.totalPrice, c.Name, p.paymentType 
                FROM `order` o
                JOIN customer c ON o.customerID = c.customerID
                JOIN payment p ON o.paymentTypeID = p.paymentTypeID
                WHERE o.orderID = '$orderID'";
$order_result = mysqli_query($conn, $order_query);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    die("Order tidak ditemukan.");
}

$suborder_query = "SELECT s.subOrderID, s.storeID, st.storeName, s.subTotal, d.deliveryType 
                    FROM subOrder s
                    JOIN store st ON s.storeID = st.storeID
                    JOIN delivery d ON s.deliveryID = d.deliveryID
                    WHERE s.orderID = '$orderID'";
$suborder_result = mysqli_query($conn, $suborder_query);
$suborders = mysqli_fetch_all($suborder_result, MYSQLI_ASSOC);

$orderdetail_query = "SELECT od.orderDetailID, od.subOrderID, od.productID, od.quantity, od.price, od.size, s.storeName
                    FROM orderdetail od
                    JOIN suborder so ON so.subOrderID = od.subOrderID
                    JOIN store s ON so.storeID = s.storeID
                    JOIN `order` o ON so.orderID = o.orderID
                    WHERE so.orderID = '$orderID'";
$orderdetail_result = mysqli_query($conn, $orderdetail_query);
$orderdetail = mysqli_fetch_all($orderdetail_result, MYSQLI_ASSOC);

$total_items_query = "SELECT COUNT(orderDetailID) AS totalItems 
                      FROM orderDetail 
                      WHERE subOrderID IN (SELECT subOrderID FROM subOrder WHERE orderID = '$orderID')";
$total_items_result = mysqli_query($conn, $total_items_query);
$total_items = mysqli_fetch_assoc($total_items_result)['totalItems'];

$itemprice_query = "SELECT SUM(price) AS itemPrice 
                      FROM orderDetail 
                      WHERE subOrderID IN (SELECT subOrderID FROM subOrder WHERE orderID = '$orderID')";
$itemprice_result = mysqli_query($conn, $itemprice_query);
$itemprice = mysqli_fetch_assoc($itemprice_result)['itemPrice'];

$delivery_query = "SELECT SUM(deliveryFee) AS totalFee 
                      FROM delivery d
                      JOIN suborder so ON so.deliveryID = d.deliveryID 
                      WHERE subOrderID IN (SELECT subOrderID FROM subOrder WHERE orderID = '$orderID')";
$delivery_result = mysqli_query($conn, $delivery_query);
$delivery = mysqli_fetch_assoc($delivery_result)['totalFee'];

$tax = $itemprice * 0.1



?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart Management</title>
    <link rel="stylesheet" href="css/success.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bellefair&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mate+SC&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Bodoni+Moda+SC:ital,opsz,wght@0,6..96,400..900;1,6..96,400..900&family=Bodoni+Moda:ital,opsz,wght@0,6..96,400..900;1,6..96,400..900&family=DM+Serif+Text:ital@0;1&family=Oswald:wght@200..700&family=Staatliches&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Luxora</title>
    <link rel="icon" href="images/logos/logo.png" type="image/png">
</head>
<body>

    <div class="content">
        <div class="check-loading">
            <input type="checkbox" id="check">
            <label for="check" id="loadingLabel">     
            <div></div>
            </label>

            <div class="after" id="after">
                <h1 id="after">Payment Successful!</h1>    
            </div>
        </div>
  
        <div class="cart-details">
                  <h1>Order Summary</h1>
                  <p>Your order number is <?= htmlspecialchars($order['orderID']) ?></p>
                  <div class="rincian-cart">
                   <div class="rincian-name">  
                        <p>Total Order </p>
                        <p>All items price</p>
                        <p>Total Tax</p>
                        <p>All Shipping Fee</p>
                        <p>Payment Fee</p>
                        <h2>Total Paid</h2>
                   </div>
                   <div class="rincian-number">
                   <p><?= $total_items ?> Items</p>
                   <p> $<?= $itemprice ?></p>
                   <p> $<?= $tax ?></p>
                   <p> $<?= $delivery ?></p>
                   <p> $<?= htmlspecialchars($order['tax']) ?></p>
                   <h2> $<?= htmlspecialchars($order['totalPrice']) ?></h2>
                   </div>
                  </div>
                  <a href="homeCustomer.php">Continue</a>
            </div> 
    </div>
   
    <script src="js/success.js"></script>

</body>
</html>

