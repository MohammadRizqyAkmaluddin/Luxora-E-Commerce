<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['customerID'])) {
    die("Anda harus login terlebih dahulu.");
}

$customerID = $_SESSION['customerID'];
$query = "SELECT Name, Email, customerImage FROM customer WHERE customerID = '$customerID'";
$result = mysqli_query($conn, $query);
$customer = mysqli_fetch_assoc($result);

function formatTanggalIndo($tanggal) {
    $monthNames = [
        '01' => 'January', '02' => 'February', '03' => 'March',
        '04' => 'April',   '05' => 'May',      '06' => 'June',
        '07' => 'July',    '08' => 'August',   '09' => 'September',
        '10' => 'October', '11' => 'November', '12' => 'December'
    ];
    $timestamp = strtotime($tanggal);
    $day   = date('d', $timestamp);
    $month = $monthNames[date('m', $timestamp)];
    $year  = date('Y', $timestamp);
    return $day . ' ' . $month . ' ' . $year;
}

$filter = isset($_GET['filter_months']) ? $_GET['filter_months'] : 'all';

$filterOptions = [
    'all' => 'ALL TIME',
    '1m'  => 'LAST 1 MONTH',
    '3m'  => 'LAST 3 MONTH',
    '6m'  => 'LAST 6 MONTH',
    '8m'  => 'LAST 8 MONTH',
    '1y'  => 'LAST 1 YEAR',
    '2y'  => 'LAST 2 YEAR',
    '3y'  => 'LAST 3 YEAR'
];

$intervalSQL = '';
switch ($filter) {
    case '1m': $intervalSQL = '1 MONTH'; break;
    case '3m': $intervalSQL = '3 MONTH'; break;
    case '6m': $intervalSQL = '6 MONTH'; break;
    case '8m': $intervalSQL = '8 MONTH'; break;
    case '1y': $intervalSQL = '1 YEAR'; break;
    case '2y': $intervalSQL = '2 YEAR'; break;
    case '3y': $intervalSQL = '3 YEAR'; break;
    default:  $intervalSQL = ''; break;
}

if ($intervalSQL != '') {
    $query = "
        SELECT o.orderID, o.orderDate
        FROM `order` o
        WHERE o.customerID = ?
          AND o.orderDate >= DATE_SUB(NOW(), INTERVAL $intervalSQL)
        ORDER BY o.orderDate DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $customerID);
} else {
    $query = "
        SELECT o.orderID, o.orderDate
        FROM `order` o
        WHERE o.customerID = ?
        ORDER BY o.orderDate DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $customerID);
}
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orderID   = $row['orderID'];
    $orderDate = $row['orderDate'];

    $productQuery = "
        SELECT p.productImage
        FROM product p
        JOIN orderdetail od ON p.productID = od.productID
        JOIN suborder so ON od.subOrderID = so.subOrderID
        WHERE so.orderID = ?
    ";
    $productStmt = $conn->prepare($productQuery);
    $productStmt->bind_param("s", $orderID);
    $productStmt->execute();
    $productResult = $productStmt->get_result();

    $productImages = [];
    while ($productRow = $productResult->fetch_assoc()) {
        $productImages[] = $productRow['productImage'];
    }

    $orders[] = [
        'orderDate'     => $orderDate,
        'productImages' => $productImages,
        'orderID'       => $orderID
    ];
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/customerNavbar.css">
    <link rel="stylesheet" href="css/orderHistory.css">
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


<div class="header" id="header">
    
</div>

<div class="content">
    <div class="left-content">
        <div class="text">
            <i class="fa-regular fa-clock"></i>
            <h1>ORDER HISTORY</h1>
        </div>
        <form method="GET" action="">
            <select name="filter_months" onchange="this.form.submit()">
                <?php foreach ($filterOptions as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($filter == $key) ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <a href="homeCustomer.php"><i class="fa-solid fa-angle-left"></i></a>
    </div>
    <div class="right-content">
        <div class="order-history">
            <?php foreach ($orders as $order): ?>
                <?php 
                    $orderDateFormatted = formatTanggalIndo($order['orderDate']);
                    $orderID = $order['orderID'];
                    $totalProducts = count($order['productImages']);
                ?>
                <div class="order">
                    <div class="order-info">
                        <h3>Ordered <?= $orderDateFormatted ?></h3> 
                        <h3>ID: <?= htmlspecialchars($orderID) ?></h3> 
                        <h3><?= $totalProducts ?> Product<?= $totalProducts > 1 ? 's' : '' ?></h3>
                    </div>
                    <div class="product-images">
                        <?php 
                            $totalImages = count($order['productImages']);
                            foreach (array_slice($order['productImages'], 0, 3) as $index => $image): 
                        ?>
                            <div class="wrap">
                                <img class="image" src="uploads/products/<?= htmlspecialchars($image) ?>" alt="Product Image">
                                <?php if ($index == 2 && $totalImages > 3): ?>
                                    <div class="more-image">
                                        +<?= $totalImages - 3 ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <div class="buttons">
                            <a href="orderDetail.php?id=<?= htmlspecialchars($orderID) ?>">View Order Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>




    </div>
</div>


</body>
</html>