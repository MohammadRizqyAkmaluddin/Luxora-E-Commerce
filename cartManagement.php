<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['customerID'])) {
    die("Anda harus login terlebih dahulu.");
}
$customerID = $_SESSION['customerID'];

$query = "SELECT Name, phoneNumber, Email, Address,customerImage FROM customer WHERE customerID = '$customerID'";
$result = mysqli_query($conn, $query);
$customer = mysqli_fetch_assoc($result);

function generateCustomID($conn, $prefix, $table, $idColumn) {
    $query = "SELECT $idColumn FROM `$table` WHERE $idColumn LIKE '$prefix%' ORDER BY $idColumn DESC LIMIT 1";
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



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['update_cart'])) {
        $action = $_POST['update_cart'];
        $productID = $_POST['productID'];
        $size = $_POST['size'];
        // Ambil informasi produk dari database
        $product_query = "SELECT productID, productName,storeID, price FROM product WHERE productID = '$productID'";
        $product_result = mysqli_query($conn, $product_query);
        $product = mysqli_fetch_assoc($product_result);
        $price = $product['price'];
        
        if ($product) {
            if ($action === "increase") {
               
                $update_quantity_query = "UPDATE shoppingCart sc
                SET quantity = quantity + 1
                WHERE customerID = '$customerID' AND productID = '$productID' AND sc.size = '$size'";
                mysqli_query($conn, $update_quantity_query);

                $update_price_query = "UPDATE shoppingCart sc
                JOIN product p ON sc.productID = p.productID
                LEFT JOIN hotdeals h ON sc.productID = h.productID
                SET 
                sc.price = COALESCE(h.finalPrice, p.price),
                sc.totalPrice = sc.quantity * COALESCE(h.finalPrice, p.price)
                WHERE sc.customerID = '$customerID' AND sc.productID = '$productID' AND sc.size = '$size'";
                mysqli_query($conn, $update_price_query);

            } elseif ($action === "decrease") {
              
                $check_quantity_query = "SELECT quantity FROM shoppingCart WHERE customerID = '$customerID' AND productID = '$productID'";
                $check_quantity_result = mysqli_query($conn, $check_quantity_query);
                $cart_item = mysqli_fetch_assoc($check_quantity_result);

        if ($cart_item && $cart_item['quantity'] > 1) {
            $update_quantity_query = "UPDATE shoppingCart sc
                                    SET quantity = quantity - 1
                                    WHERE customerID = '$customerID' AND productID = '$productID' AND sc.size = '$size'";
            mysqli_query($conn, $update_quantity_query);

            $update_price_query = "UPDATE shoppingCart sc
                JOIN product p ON sc.productID = p.productID
                LEFT JOIN hotdeals h ON sc.productID = h.productID
                SET 
                    sc.price = COALESCE(h.finalPrice, p.price),
                    sc.totalPrice = sc.quantity * COALESCE(h.finalPrice, p.price)
                WHERE sc.customerID = '$customerID' AND sc.productID = '$productID' AND sc.size = '$size'";
            mysqli_query($conn, $update_price_query);

        } else {
            $delete_cart_query = "DELETE FROM shoppingCart sc WHERE customerID = '$customerID' AND productID = '$productID' AND sc.size = '$size'";
            mysqli_query($conn, $delete_cart_query);
        }

                    } elseif ($action === "delete") {
                       
                        $delete_cart_query = "DELETE FROM shoppingCart WHERE customerID = '$customerID' AND productID = '$productID' AND `size` = '$size'";
                        mysqli_query($conn, $delete_cart_query);
                    }
                }
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            }
}

if (isset($_POST['clear_cart'])) {
    $clear_cart_query = "DELETE FROM shoppingCart WHERE customerID = '$customerID'";
    mysqli_query($conn, $clear_cart_query);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

$cart_query = "SELECT sc.productID, p.productName, p.storeID, s.storeName, 
           sc.quantity, 
           COALESCE(h.finalPrice, p.price) AS price, 
           p.productImage,
           sc.size
    FROM shoppingCart sc 
    JOIN product p ON sc.productID = p.productID
    JOIN store s ON p.storeID = s.storeID
    LEFT JOIN hotdeals h ON sc.productID = h.productID 
    WHERE sc.customerID = '$customerID'
    ORDER BY s.storeID";
$cart_result = mysqli_query($conn, $cart_query);


$cart_items = [];
while ($row = mysqli_fetch_assoc($cart_result)) {
    error_log("Product: {$row['productID']}, SizeID: " . ($row['sizeID'] ?? 'NULL')); 
    $cart_items[$row['storeName']][] = $row; 
}

$total_items_query = "SELECT COUNT(DISTINCT CONCAT(productID, '-', `size`)) AS totalItems 
                      FROM shoppingCart 
                      WHERE customerID = '$customerID'";
$total_items_result = mysqli_query($conn, $total_items_query);
$total_items = mysqli_fetch_assoc($total_items_result)['totalItems'];


$total_items_price = "SELECT ROUND(SUM(totalPrice),2) AS total FROM shoppingCart WHERE customerID = '$customerID'";
$total_items_resultt = mysqli_query($conn, $total_items_price);
$total_itemsPrice = mysqli_fetch_assoc($total_items_resultt)['total'];


$total_items_tax = "SELECT ROUND(SUM(totalPrice) * 0.1, 2) AS Tax FROM shoppingCart WHERE customerID = '$customerID'";
$total_items_resulttt = mysqli_query($conn, $total_items_tax);
$total_itemsTax = mysqli_fetch_assoc($total_items_resulttt)['Tax'];

$total_cart_price = $total_itemsPrice + $total_itemsTax;

if (isset($_POST['checkout'])) {
    $paymentTypeID = $_POST['paymentTypeID'];
    $deliveryIDs = $_POST['deliveryID'];

    $payment_query = "SELECT adminFee FROM payment WHERE paymentTypeID = '$paymentTypeID'";
    $payment_result = mysqli_query($conn, $payment_query);
    $payment = mysqli_fetch_assoc($payment_result);
    $adminFee = floatval($payment['adminFee']);

    $subtotal_query = "SELECT SUM(quantity * price) AS subtotal FROM shoppingCart WHERE customerID = '$customerID'";
    $subtotal_result = mysqli_query($conn, $subtotal_query);
    $subtotal = floatval(mysqli_fetch_assoc($subtotal_result)['subtotal']);

    $tax = $subtotal * 0.1;
    $total = $subtotal + $tax;

    $totalDeliveryFee = 0;
    foreach ($deliveryIDs as $storeID => $deliveryID) {
        $delivery_query = "SELECT deliveryFee FROM delivery WHERE deliveryID = '$deliveryID'";
        $delivery_result = mysqli_query($conn, $delivery_query);
        $delivery = mysqli_fetch_assoc($delivery_result);
        $deliveryFee = floatval($delivery['deliveryFee']);
        $totalDeliveryFee += $deliveryFee;
    }

    $tax = $subtotal * 0.1;
    $totalPrice = $subtotal + $adminFee + $totalDeliveryFee + $tax;

    $orderID = generateCustomID($conn, "TRX", "order", "orderID");

    $orderDate = date('Y-m-d H:i:s');
    $insert_order = "INSERT INTO `order` (orderID, customerID, paymentTypeID, tax, totalPrice, orderDate) 
                     VALUES ('$orderID', '$customerID', '$paymentTypeID', '$tax', '$totalPrice', '$orderDate')";
    mysqli_query($conn, $insert_order);

    foreach ($cart_items as $store_name => $items) {
        $storeID = $items[0]['storeID']; 
        $deliveryID = $deliveryIDs[$storeID]; 

        $storeSubtotal = 0;
        foreach ($items as $item) {
            $productPrice = floatval($item['price']);
            $productQuantity = intval($item['quantity']);
            $storeSubtotal += ($productPrice * $productQuantity);
        }

        $delivery_query = "SELECT deliveryFee FROM delivery WHERE deliveryID = '$deliveryID'";
        $delivery_result = mysqli_query($conn, $delivery_query);    
        $delivery = mysqli_fetch_assoc($delivery_result);
        $deliveryFee = floatval($delivery['deliveryFee']);

        $storeSubtotal += $deliveryFee;

        $subOrderID = generateCustomID($conn, "SUB", "subOrder", "subOrderID");

        echo "Store: $store_name, Subtotal: $storeSubtotal<br>";
   
        $insert_suborder = "INSERT INTO subOrder (subOrderID, orderID, storeID, deliveryID, subTotal) 
                            VALUES ('$subOrderID', '$orderID', '$storeID', '$deliveryID', '$storeSubtotal')";
        mysqli_query($conn, $insert_suborder);

        foreach ($items as $item) {
            $productID = $item['productID'];
            $quantity = intval($item['quantity']);
            $size = $item['size'];

            $price_query = "SELECT COALESCE(h.finalPrice, p.price) AS price 
                            FROM product p 
                            LEFT JOIN hotdeals h ON p.productID = h.productID
                            WHERE p.productID = '$productID'";
            $price_result = mysqli_query($conn, $price_query);
            $price = mysqli_fetch_assoc($price_result)['price'];
            
            $orderDetailID = generateCustomID($conn, "DTL", "orderDetail", "orderDetailID");
            $insert_orderDetail = "INSERT INTO orderDetail (orderDetailID, subOrderID, productID, `size`, quantity, price) 
                                   VALUES ('$orderDetailID','$subOrderID', '$productID', '$size', '$quantity', '$price')";
            mysqli_query($conn, $insert_orderDetail);
        }
    }

    $clear_cart_query = "DELETE FROM shoppingCart WHERE customerID = '$customerID'";
    mysqli_query($conn, $clear_cart_query);

    header("Location: success.php?orderID=$orderID");
    exit();
}


$payment_query = "SELECT paymentTypeID, paymentType, adminFee,paymentIcon FROM payment";
$paymentDisplay = mysqli_query($conn, $payment_query);
while ($row = mysqli_fetch_assoc($paymentDisplay)) {
    $payment_methods[] = $row;
}

$delivery_query = "SELECT deliveryID, deliveryType, deliveryFee FROM delivery";
$deliveryDisplay = mysqli_query($conn, $delivery_query);
while ($row = mysqli_fetch_assoc($deliveryDisplay)) {
    $delivery_methods[] = $row;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart Management</title>
    <link rel="stylesheet" href="css/customerNavbar.css">
    <link rel="stylesheet" href="css/cart.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Afacad:ital,wght@0,400..700;1,400..700&family=Bebas+Neue&family=Gabarito:wght@400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Luxora</title>      
    <link rel="icon" href="images/logos/logo.png" type="image/png">
</head>
<body>

 
<div class="header" id="header">
    <div class="left">
        <a href="homeCustomer.php"><i class="fa-solid fa-angle-left"></i>Home</a>
    </div>
    <h1>LUXORA</h1>
</div>
<div class="subHeader">
            <h2>Your Cart</h2>  
</div>
<div class="all-content">
    <div class="mid">
         <form method="POST">
            <div class="totalItem">
                <button type="submit" name="clear_cart" class="clear-cart">Clear all items</button>
              </form>
            </div>
        <div class="cartItems">

            <?php if (!empty($cart_items)): ?>
                <?php foreach ($cart_items as $store_name => $items): ?>
                <h3><?= htmlspecialchars($store_name) ?></h3>
            <div class="detailsHeader">
                <p class="d">Details</p>
                <p class="q">Quantity</p>
                <p>Price</p>
            </div>
            <table>
                <?php foreach ($items as $item): ?>
                <div class="cart-content">
                    <div class="details">
                    <img src="uploads/products/<?= htmlspecialchars($item['productImage']) ?>" class="cart-image">
                    <div class="specific-details">
                     <div class="name"> 
                        <h1><?= htmlspecialchars($item['productName']) ?></h1>
                        <h2>Size <?= htmlspecialchars($item['size']) ?></h2>
                     </div>
                     
                    </div>
                    </div>
                    <form method="POST">
                            <input type="hidden" name="productID" value="<?= $item['productID'] ?>">
                            <input type="hidden" name="size" value="<?= htmlspecialchars($item['size']) ?>">
                            <div class="count">
                            <button type="submit" name="update_cart" value="decrease"><i class="fa-solid fa-minus"></i></button>
                            <p><?= $item['quantity'] ?><p>
                            <button type="submit" name="update_cart" value="increase"><i class="fa-solid fa-plus"></i></button>
                            </div>
                            <div class="price">$<?= number_format($item['price'] * $item['quantity'], 2) ?> </div>
                            <button type="submit" name="update_cart" value="delete"><i class="fa-solid fa-xmark"></i></button>
                    </form>
                </div>
                <?php endforeach; ?>
                <div class="line"></div>
            </table>
            <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-cart">
                    <img src="images/empty-cart.jpg">
                    <div class="empty-text">
                        <h3>Your cart is empty</h3>
                        <p>Looks like you have not added anything into your cart, go ahead & explore top categories</p>
                    </div>
                    <a href="products.php"><button>Shop Now</button></a>
                </div>
             <?php endif; ?>
        </div>
    </div>
    <div class="right">
        <div class="pop">
            <div class="cart-details">
                <h1>Order Summary</h1>
                <p class="total-item">Total <?php echo $total_items; ?> Items</p> 
                <div class="details-product">
                    <?php foreach ($cart_items as $store_name => $items): ?>
                        <h3><?= htmlspecialchars($store_name) ?></h3>
                        <?php foreach ($items as $item): ?>
                          <div class="product-name-details">
                            <div class="name-details"><?= htmlspecialchars($item['productName']) ?></div>
                            
                            <p><?= $item['quantity'] ?><p>
                          </div>
                          <div class="line-details"></div>
                        <?php endforeach; ?>
                       
                    <?php endforeach; ?>
                </div>
                <div class="rincian-cart">
                   <div class="rincian-name">
                        <p>Product Price</p>
                        <p>Total Tax</p>
                        <h2>Subtotal</h2>
                   </div>
                   <div class="rincian-number">
                        <p >$<?php echo $total_itemsPrice; ?></p>
                        <p >$<?php echo $total_itemsTax; ?></p>
                        <h2>$<?php echo number_format($total_cart_price, 2); ?></p>
                   </div>
                </div>
            </div>
        <div class="popup" id="popup">
            <div class="popup-content">
              <div class="pop-head">
                <div class="pop-head-top">
                <button type="button" onclick="closePopup()" ><i class="fa-solid fa-angle-left"></i></button>
                <h1>Checkout</h1>
                </div>
                    
                    
                <div class="pop-customer-details">
                    <div class="data">
                        <h2>Recipient</h2>
                       <p><?= htmlspecialchars($customer['Name']) ?></p>
                    </div>
                    <div class="data">
                        <h2>Contact</h2>
                        <p><?= htmlspecialchars($customer['phoneNumber']) ?></p>
                    </div>  
                    <div class="data">
                        <h2>Address</h2>
                        <p><?= htmlspecialchars($customer['Address']) ?></p>
                    </div>
                </div>
              </div>
              <div class="pop-details">
                <h1>Complete The Details</h1>
                <div class="pop-order-details">
                    
                <form method="post">
                
                    <div class="wrap-details">
                        <div class="payment-method">
                            <label for="paymentType">Payment Method</label>
                            
                            <?php foreach ($payment_methods as $payment): ?>
                            <label>
                            
                            <input type="radio" name="paymentTypeID" value="<?= htmlspecialchars($payment['paymentTypeID'])?>" required>
                            <div class="payment-wrap">
                            <img src="images/paymentIcon/<?= htmlspecialchars($payment['paymentIcon']) ?>" width="20px">
                            <span><?= htmlspecialchars($payment['paymentType']) ?></span>
                            <p>| $<?= htmlspecialchars($payment['adminFee']) ?></p>
                            </div>
                            </label><br>
                            
                            <?php endforeach; ?>
                        
                        </div>
                        
                        <div class="delivery-type">
                            <?php foreach ($cart_items as $store_name => $items): ?>
                                <div class="selection">
                                <h3><?= htmlspecialchars($store_name) ?></h3>
                                <select name="deliveryID[<?= $items[0]['storeID'] ?>]" required>
                                <option value="" disabled selected>Choose Delivery</option>
                                <?php foreach ($delivery_methods as $delivery): ?>
                                    <option value="<?= htmlspecialchars($delivery['deliveryID'])?>"><?= htmlspecialchars($delivery['deliveryType'])?> $<?= htmlspecialchars($delivery['deliveryFee'])?></option>
                                <?php endforeach; ?>
                                </select> 
                                </div>
                            <?php endforeach; ?>
                            
                        </div>
                        
                        </div>
        
                        <button type="submit" class="order-button" name="checkout">Proceed payment</button>
                </form> 
                   
              
                    </div>                        
                </div>            
            </div>
        </div>
        <button type="submit" class="btn" onclick="openPopup()">Checkout</button> 
        </div>
        
    </div>
</div>

    <script src="js/cart.js"></script>
</body>
</html>
