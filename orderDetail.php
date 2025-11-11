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

$id = isset($_GET['id']) ? $_GET['id'] : '';
$orderID = mysqli_real_escape_string($conn, $id);

$detail_query = "SELECT o.*, p.* 
                FROM `order` o
                JOIN payment p ON o.paymentTypeID = p.paymentTypeID
                WHERE orderID = '$orderID'"; 
$detail_result = mysqli_query($conn, $detail_query);
$detail_data = mysqli_fetch_assoc($detail_result);

$order_query = " SELECT 
                s.storeID,
                s.storeName,
                s.storeImage,
                p.productID,
                p.productName,
                p.productImage,
                od.quantity,
                od.price,
                od.size,
                od.orderDetailID,
                pr.rating
                FROM `order` o
                JOIN subOrder so ON o.orderID = so.orderID
                JOIN orderDetail od ON so.subOrderID = od.subOrderID
                JOIN product p ON od.productID = p.productID
                JOIN store s ON p.storeID = s.storeID
                LEFT JOIN productreview pr ON od.orderDetailID = pr.orderDetailID
                WHERE o.orderID = '$orderID'
                ORDER BY s.storeName ASC";
$order_result = mysqli_query($conn, $order_query);

$order_items = [];
while ($row = mysqli_fetch_assoc($order_result)) {
    $storeName = $row['storeName'];

    if (!isset($order_items[$storeName])) {
        $order_items[$storeName] = [
            'storeImage' => $row['storeImage'],
            'products' => []
        ];
    }
    $order_items[$storeName]['products'][] = $row;
}

function generateCustomID($conn, $prefix, $table, $idColumn) {
    $query = "SELECT $idColumn FROM $table WHERE $idColumn LIKE '$prefix%' ORDER BY $idColumn DESC LIMIT 1";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastNumber = (int)substr($row[$idColumn], 3); 
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }   
    $customID = $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    return $customID;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $orderDetailID = $_POST['orderDetailID'];
    $rating = $_POST['rating'];
    $review = $_POST['review'];

    $stmt = $conn->prepare("INSERT INTO productReview (orderDetailID, rating, review) 
                            VALUES (?, ?, ?)");
    $stmt->bind_param("sds", $orderDetailID, $rating, $review);

    if ($stmt->execute()) {
        header("Location: orderDetail.php?id=$orderID");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $orderDetailID = mysqli_real_escape_string($conn, $_POST['orderDetailID']);
    $customerID = mysqli_real_escape_string($conn, $_POST['customerID']);
    $rating = intval($_POST['rate']);
    $reviewText = mysqli_real_escape_string($conn, $_POST['reviewText']);
    $reviewDate = date('Y-m-d');

    $query = "INSERT INTO coursereview (orderDetailID, customerID, rating, review, reviewDate)
                VALUES ('$orderDetailID', '$customerID', '$rating', '$reviewText', '$reviewDate')";
    $result = mysqli_query($conn, $query);

    if ($result) {
        echo "<script>alert('Thanks for your review!');</script>";
    } else {
        echo "<script>alert('Error saving review.');</script>";
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/customerNavbar.css">
    <link rel="stylesheet" href="css/orderDetail.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Afacad:ital,wght@0,400..700;1,400..700&family=Bebas+Neue&family=Gabarito:wght@400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Luxora</title>      
    <link rel="icon" href="images/logos/logo.png" type="image/png">
</head>
<body>


<div class="headerDetail" id="headerDetail">
    <h2>ID <?= htmlspecialchars($orderID) ?></h2>

    <div class="detail-top">
        <p>PAYMENT METHOD</p>
        <h3><?= htmlspecialchars($detail_data['paymentType']) ?></h3>
    </div>
    <div class="detail-top">
        <p>TOTAL PRICE</p>
        <h3>$ <?= htmlspecialchars($detail_data['totalPrice']) ?></h3>
    </div>
    <div class="detail-top">
        <p>TAX</p>
        <h3>$ <?= htmlspecialchars($detail_data['tax']) ?></h3>
    </div>
    <div class="detail-top">
        <p>STATUS</p>
        <h3 class="status">DELIVERED</h3>
    </div>
    <div class="detail-top">
        <p>ORDERED ON</p>
        <h3><?= htmlspecialchars($detail_data['orderDate']) ?></h3>
    </div>
    
</div>

<div class="content">
    <div class="left-content">
        <div class="text">
            <i class="fa-regular fa-clock"></i>
            <h1>ORDER DETAILS</h1>
        </div>
        <a href="orderHistory.php"><i class="fa-solid fa-angle-left"></i></a>
    </div>
    <div class="right-content">
        <div class="store-list">
        <?php foreach ($order_items as $store_name => $store): ?>
          <div class="store-wrap">
            <div class="store-header">
                <div class="store-image">
                    <img src="uploads/stores/<?= htmlspecialchars($store['storeImage']) ?>" alt="<?= htmlspecialchars($store_name) ?>">
                </div>
            </div>
              <div class="product-list">
                <?php foreach ($store['products'] as $item): ?>
                <div class="product-wrap">
                    <div class="detailed">
                        <div class="details">
                        <div class="product-container">
                            <a><img src="uploads/products/<?= htmlspecialchars($item['productImage']) ?>"></a>
                        </div>
                            <div class="specific-details">
                                <div class="name"> 
                                    <h1><?= htmlspecialchars($item['productName']) ?></h1>
                                </div>
                            </div>
                        </div>
                        <div class="size-qty">
                            <h2>Size <?= htmlspecialchars($item['size']) ?></h2>
                            <p>Qty <?= htmlspecialchars($item['quantity'])?></p>
                        </div>
                        <div class="price">$ <?= number_format($item['price'] * $item['quantity'], 2) ?> </div>
                    </div>
                    <?php
                        $rating = $item['rating'] ?? 0;
                        $fullStars = floor($rating);
                        $halfStar = ($rating - $fullStars) >= 0.5;
                        $totalStars = 5;    
                    ?>
                    <div class="rating"> 
                        <?php if (!empty($item['rating'])): ?>
                            <h2><?= number_format($item['rating'], 1) ?></h2>
                            <?php for ($i = 1; $i <= $totalStars; $i++): ?>
                            <?php if ($i <= $fullStars): ?>
                                <svg class="star full" viewBox="0 0 24 24">
                                    <path d="M12 2l2.9 6.9L22 9.2l-5.5 4.8L18 21l-6-3.6L6 21l1.5-7L2 9.2l7.1-0.3L12 2z"/>
                                </svg>
                            <?php elseif ($i == $fullStars + 1 && $halfStar): ?>
                                <svg class="star" viewBox="0 0 24 24">
                                    <defs>
                                        <linearGradient id="halfGold" x1="0%" y1="0%" x2="100%" y2="0%">
                                            <stop offset="50%" stop-color="gold"/>
                                            <stop offset="50%" stop-color="white"/>
                                        </linearGradient>
                                    </defs>
                                    <path d="M12 2l2.9 6.9L22 9.2l-5.5 4.8L18 21l-6-3.6L6 21l1.5-7L2 9.2l7.1-0.3L12 2z" fill="url(#halfGold)" stroke="gold" stroke-width="2"/>
                                </svg>
                            <?php else: ?>
                                <svg class="star" viewBox="0 0 24 24">
                                    <path d="M12 2l2.9 6.9L22 9.2l-5.5 4.8L18 21l-6-3.6L6 21l1.5-7L2 9.2l7.1-0.3L12 2z"/>
                                </svg>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <?php else: ?>
                            <form method="POST" class="rating-form" >
                                <input type="hidden" name="orderDetailID" value="<?= htmlspecialchars($item['orderDetailID']) ?>">
                                <input type="hidden" name="customerID" value="<?= htmlspecialchars($customerID) ?>">
                                <div class="review-input">
                                    <input name="review" placeholder="Write your review here..." required></input>
                                    <div class="star-rating">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" id="star<?= $item['productID'] . $i ?>" name="rating" value="<?= $i ?>" required />
                                            <label for="star<?= $item['productID'] . $i ?>">&#9733;</label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <button type="submit" name="submit_review">Submit Review</button>
                            </form>
                        <?php endif; ?>
                    </div> 
                </div>
                <?php endforeach; ?>
              </div>
                <div class="line"></div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
    </div>



    
</div>
    <script src="js/orderDetail.js"></script>

</body>
</html>