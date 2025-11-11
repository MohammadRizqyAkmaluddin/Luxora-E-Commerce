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


if (isset($_POST['clearWishlist'])) {
    $clear_wishlist_query = "DELETE FROM wishlist WHERE customerID = '$customerID'";
    mysqli_query($conn, $clear_wishlist_query);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

$wishlist_query = "SELECT w.*,p.*
    FROM wishlist w
    JOIN product p ON w.productID = p.productID
    WHERE customerID = '$customerID'";
$wishlist_result = mysqli_query($conn, $wishlist_query);

$wishlist_items = [];
while ($row = mysqli_fetch_assoc($wishlist_result)) {
    $wishlist_items[$row['productID']][] = $row; 
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['update_cart'])) {
        $action = $_POST['update_cart'];
        $productID = $_POST['productID'];
 
        $product_query = "SELECT productID, productName,storeID, price FROM product WHERE productID = '$productID'";
        $product_result = mysqli_query($conn, $product_query);
        $product = mysqli_fetch_assoc($product_result);
        if ($product) {
            if ($action === "delete") {
                       
                        $delete_cart_query = "DELETE FROM wishlist WHERE customerID = '$customerID' AND productID = '$productID'";
                        mysqli_query($conn, $delete_cart_query);
                    }
                }
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luxora</title>
    <link rel="stylesheet" href="css/customerNavbar.css">
    <link rel="stylesheet" href="css/wishlist.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bellefair&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mate+SC&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Bodoni+Moda+SC:ital,opsz,wght@0,6..96,400..900;1,6..96,400..900&family=Bodoni+Moda:ital,opsz,wght@0,6..96,400..900;1,6..96,400..900&family=DM+Serif+Text:ital@0;1&family=Oswald:wght@200..700&family=Staatliches&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>

 
<div class="header" id="header">
    <div class="left">
        <a href="homeCustomer.php"><i class="fa-solid fa-angle-left"></i>Home</a>
    </div>
    <h1>LUXORA</h1>
</div>
<div class="subHeader">
            <h2>Your Wishlist</h2>  
</div>
<div class="all-contentt">
    <div class="mid">
         <form method="POST">
            <div class="totalItem">
                <button type="submit" name="clearWishlist" class="clear-cart">Clear all items</button>
              </form>
            </div>
        <div class="cartItems">
            <?php if (!empty($wishlist_items)): ?>
         
                <?php foreach ($wishlist_result as $wishlist): ?>
                <div class="cart-content">
                    <div class="details">
                    <img src="uploads/products/<?= htmlspecialchars($wishlist['productImage']) ?>" class="cart-image">
                    <div class="specific-details">
                     <div class="name"> 
                        <h1><?= htmlspecialchars($wishlist['productName']) ?></h1>
                       
                     </div>
                     
                    </div>
                    </div>
                    <form method="POST">
                            <input type="hidden" name="productID" value="<?= $wishlist['productID'] ?>">
                            <button type="submit" name="update_cart" value="delete"><i class="fa-solid fa-xmark"></i></button>
                    </form>
                </div>
                <?php endforeach; ?>
              
   
            <?php else: ?>
                <div class="empty-cart">
                    <img class="wishlist" src="images/wishList.png">
                    <div class="empty-text">
                        <h3>Your don't have any wishlist</h3>
                        <p>Looks like you have not added anything into your wishlist, go ahead & explore top categories</p>
                    </div>
                </div>
             <?php endif; ?>
        </div>
    </div>
    
</div>

    <script src="js/cart.js"></script>
</body>
</html>
