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

$profileImage = !empty($customer['customerImage']) ? 'uploads/profile/' . $customer['customerImage'] : 'images/empty-profile.jpg';

$getID = isset($_GET['id']) ? $_GET['id'] : '';
$productID = mysqli_real_escape_string($conn, $getID);

$total_items_query = "SELECT COUNT(DISTINCT CONCAT(productID, '-', `size`)) AS totalItems 
                      FROM shoppingCart 
                      WHERE customerID = '$customerID'";
$total_items_result = mysqli_query($conn, $total_items_query);
$total_items = mysqli_fetch_assoc($total_items_result)['totalItems'];


$total_wishlist_query = "SELECT COUNT(productID) AS totalFav 
                      FROM wishlist 
                      WHERE customerID = '$customerID'";
$total_wishlist_result = mysqli_query($conn, $total_wishlist_query);
$total_wishlist = mysqli_fetch_assoc($total_wishlist_result)['totalFav'];

$query = "SELECT 
            p.productID, 
            p.productName, 
            p.productDescription, 
            p.price AS originalPrice, 
            p.stock, 
            p.productImage,
            pc.productType, 
            s.storeName,
            s.storeImage,
            s.storeEmail,
            s.storeAddress,
            s.storePhoneNum,
            s.storeDescription,
            s.storeID,
            h.finalPrice,
            h.discount
          FROM product p
          JOIN store s ON p.storeID = s.storeID
          JOIN productCategory pc ON p.productTypeID = pc.productTypeID
          LEFT JOIN hotdeals h ON p.productID = h.productID
          WHERE p.productID = '$productID'";
$result = mysqli_query($conn, $query);
$product = mysqli_fetch_assoc($result);

$query = "SELECT AVG(pr.rating) AS averageRating
          FROM productreview pr
          JOIN orderdetail od ON pr.orderDetailID = od.orderDetailID
          JOIN product p ON od.productID = p.productID
          WHERE p.productID = '$productID'";
$result = mysqli_query($conn, $query);
$totalRating = mysqli_fetch_assoc($result);

$rating = $totalRating['averageRating'] ?? 0;
$fullStars = floor($rating);
$halfStar = ($rating - $fullStars) >= 0.5;
$totalStars = 5;

$query = "SELECT COUNT(DISTINCT o.customerID) AS totalReview
          FROM productreview pr
          JOIN orderdetail od ON pr.orderDetailID = od.orderDetailID
          JOIN suborder so ON od.subOrderID = so.subOrderID
          JOIN `order` o ON so.orderID = o.orderID
          WHERE od.productID = '$productID'";
$result = mysqli_query($conn, $query);
$totalReview = mysqli_fetch_assoc($result);

$query = "SELECT  sc.sizeType, pc.productTypeID, p.productID, sl.size, pc.sizeType
          FROM sizeList sl
          JOIN sizeCategory sc ON sc.sizeType = sl.sizeType
          JOIN productCategory pc ON sc.sizeType = pc.sizeType
          JOIN product p ON p.productTypeID = pc.productTypeID
          WHERE p.productID = '$productID'";
$result = mysqli_query($conn, $query);
$sizeCategory = mysqli_fetch_all($result, MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_cart'])) {
    $productID = $_POST['productID'];
    $size = $_POST['size-option'];

    error_log("Menambahkan productID: $productID dengan size: $size untuk customerID: $customerID");

    $query = "SELECT p.price, h.finalPrice FROM product p 
              LEFT JOIN hotDeals h ON p.productID = h.productID 
              WHERE p.productID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $productID);
    $stmt->execute();
    $result = $stmt->get_result();
    $productData = $result->fetch_assoc();

    if (!$productData) {
        die("Produk tidak ditemukan.");
    }

    $price = $productData['finalPrice'] ?? $productData['price'];

    $query = "SELECT quantity FROM shoppingCart WHERE customerID = ? AND productID = ? AND `size` = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $customerID, $productID, $size);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $newQuantity = $row['quantity'] + 1;
        $totalPrice = $newQuantity * $price;

        $updateQuery = "UPDATE shoppingCart SET quantity = ?, totalPrice = ? WHERE customerID = ? AND productID = ? AND `size` = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("idsss", $newQuantity, $totalPrice, $customerID, $productID, $size);
        $updateStmt->execute();
    } else {
        $totalPrice = $price;

        $insertQuery = "INSERT INTO shoppingCart (customerID, productID, `size`, quantity, price, totalPrice) VALUES (?, ?, ?, 1, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("sssdd", $customerID, $productID, $size, $price, $totalPrice);
        $insertStmt->execute();
    }

    $_SESSION['success_message'] = "Successfully added to bag!";

    if (isset($_SERVER['HTTP_REFERER'])) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        header("Location: product.php");
    }
    exit();
}

$query = "SELECT storeID, storeImage, storeName 
          FROM store
          ORDER BY RAND()";
$result = mysqli_query($conn, $query);
$stores = mysqli_fetch_all($result, MYSQLI_ASSOC);

$query = "SELECT 
          review, 
          rating, 
          reviewDate, 
          customerImage, 
          `Name`,
          Email
      FROM productreview pr
      JOIN orderdetail od ON pr.orderDetailID = od.orderDetailID
      JOIN suborder so ON od.subOrderID = so.subOrderID
      JOIN `order` o ON so.orderID = o.orderID
      JOIN customer c ON o.customerID = c.customerID
      WHERE od.productID = '$productID'
      ORDER BY pr.reviewDate DESC";
$result = mysqli_query($conn, $query);
$reviews = mysqli_fetch_all($result, MYSQLI_ASSOC);

$storeID = $product['storeID'];

$query = "SELECT 
          s.storeID,
          s.storeName,
          AVG(pr.rating) AS storeRating,
          COUNT(pr.rating) AS storeReview
      FROM store s
      JOIN product p ON s.storeID = p.storeID
      JOIN orderdetail od ON p.productID = od.productID
      JOIN productreview pr ON od.orderDetailID = pr.orderDetailID
      WHERE s.storeID = '$storeID'
      GROUP BY s.storeID, s.storeName";
$result = mysqli_query($conn, $query);
$storeDetail = mysqli_fetch_assoc($result);

$storeRating = $storeDetail['storeRating'] ?? 0;
$fullStarsss = floor($storeRating);
$halfStarss = ($storeRating - $fullStarsss) >= 0.5;
$totalStarsss = 5;

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">   
    <link rel="stylesheet" href="css/customerMainNavbar.css">
    <link rel="stylesheet" href="css/product.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Afacad:ital,wght@0,400..700;1,400..700&family=Bebas+Neue&family=Gabarito:wght@400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Luxora</title>      
    <link rel="icon" href="images/logos/logo.png" type="image/png">
</head>
<body>
    
<?php
if (isset($_SESSION['success_message'])) {
    echo '<div class="custom-popup" id="successPopup">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}
?>

<header>
    <div class="header-main">

  <div class="container">

    <a href="homeCustomer.php" class="header-logo"><img src="images/logos/logo-black.png" alt=""></a>

    <div class="search">
      <form method="get" autocomplete="off">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchOnly" name="searchOnly" placeholder="Search for products" value="<?= isset($_GET['searchOnly']) ? htmlspecialchars($_GET['searchOnly']) : '' ?>">
        <div id="searchDropdown" class="search-dropdown" style="display:none;"></div>
      </form>
    </div> 

  <div class="header-user-actions">
    <div class="cart-wish">
      <button class="action-btn">
      <a href="wishlist.php"><i class="fa-regular fa-heart"></i></a>
      <div class="count"><?php echo $total_wishlist; ?></div>
      </button>
      <button class="action-btn">
      <a href="cartManagement.php"><img src="images/bag.png"></a>
      <div class="count"><?php echo $total_items; ?></div>
      </button>
    </div>

    <div class="drop-down">
        <img src="<?= htmlspecialchars($profileImage);  ?>" class="nav-profile-img">
        <div class="sub-menu-wrapp">
          <div class="sub-menuu">
              <div class="user-info">
                  <img src="<?= htmlspecialchars($profileImage);  ?>" class="drop-profile-img">
                  <div>
                      <h1> <?= htmlspecialchars($customer['Name']) ?> </h1>
                      <h2><?= htmlspecialchars($customer['Email']) ?></a></h2>
                      
                  </div>
                  
              </div>
              <hr>
              <ul class="drop__listt">
           <li><a href="customerSetting.php" class="nav__link"></i>Account Settings</a></li>
           <li><a href="orderHistory.php" class="nav__link"></i>My Order</a></li>
           <li><a href="#" class="nav__link"></i>Add Funds</a></li>
           <li><a href="#" class="nav__link"></i>Privacy Policy</a></li>
           <li><a href="logout.php" class="nav__link">Log Out</a></li>
        </ul>
          </div>
        </div>
    </div>

  </div>

    


  </div>


    </div>

  <nav class="desktop-navigation-menu">

    <div class="container">

    <ul class="desktop-menu-category-list">

      <li class="menu-category">
        <a href="homeCustomer.php" class="menu-title">Home</a>
      </li>

 


      <li class="menu-category">
        <a href="#" class="menu-title">Categories</a>

        <div class="dropdown-panel">
        
          <ul class="dropdown-panel-list">
        
            <li class="panel-list-item">
                <div class="nav-category"> 
                    <a href="category.php?category=TO"><img src="images/categories/Shirts.png"><p>Shirts</p></a>
                </div>
            </li>

            <li class="panel-list-item">
                <div class="nav-category">      
                    <a href="category.php?category=JT"><img src="images/categories/Pants.png"><p>Trousers</p></a>
                </div>
            </li>

            <li class="panel-list-item">
                <div class="nav-category">   
                    <a href="category.php?category=OW"><img src="images/categories/Outerwear.png"><p>Outerwear</p></a>
                </div>
            </li>

            <li class="panel-list-item">
                <div class="nav-category">           
                    <a href="category.php?category=SO"><img src="images/categories/Shorts.png" ><p>Shorts</p></a>
                </div>
            </li>

            <li class="panel-list-item">
                <div class="nav-category">    
                    <a href="category.php?category=SH"><img src="images/categories/Footwear.png" ><p>Footwear</p></a>
                </div>
            </li>

            <li class="panel-list-item">
                <div class="nav-category">     
                    <a href="category.php?category=AC"><img src="images/categories/Accessories.png" ><p>Accessories</p></a>
                </div>
            </li>
            <li class="panel-list-item">
                <div class="nav-category"> 
                    <a href="category.php?category=BA"><img src="images/categories/Bags.png" ><p>Bags</p></a>
                </div>
            </li>
            <li class="panel-list-item">
                <div class="nav-category"> 
                    <a href="category.php?category=PE"><img src="images/categories/Perfume.png" ><p>Perfume</p></a>
                </div>
            </li>
            <li class="panel-list-item">
                <div class="nav-category">               
                    <a href="category.php?category=BU"><img src="images/categories/Bundling.png" ><p>Bundling</p></a>
                </div>
            </li>
        

          </ul>
          
          <ul class="dropdown-panel-list">
            <?php foreach ($stores as $store): ?> 
            <li class="panel-list-item">
                <div class="wrap">
                <a href="brands.php?storeID=<?= htmlspecialchars($store['storeID']) ?>">
                <div class="brand-wrap">
                    <img src="uploads/stores/<?= htmlspecialchars($store['storeImage']) ?>">
                    <p><?= htmlspecialchars($store['storeName']) ?></p>
                </div> 
                </a>
            </div>
              
            </li>
            <?php endforeach; ?>

          </ul>


        </div>
      </li>

      <li class="menu-category">
        <a href="products.php?type=luxury" class="menu-title">Luxury</a>
      </li>
      <li class="menu-category">
        <a href="products.php?type=limited" class="menu-title">Limited Edition</a>
      </li>
      <li class="menu-category">
        <a href="products.php?type=hotdeals" class="menu-title">Hot Deal's</a>
      </li>
      <li class="menu-category">
        <a href="products.php?type=all" class="menu-title">Explore All</a>
      </li>

      

    </ul>

    </div>
      
  </nav>

</header>

<div class="contentt">
  <div class="product-wrap">
    <div class="product-container">
      <div class="image-container"><img src="uploads/products/<?= htmlspecialchars($product['productImage']) ?>" class="product-image"></div>  
    </div>
    <div class="product-info">
      <div class="top-info"> 
        <h4><?= htmlspecialchars($product['productType']) ?></h4>
        <h1><?= htmlspecialchars($product['productName']) ?></h1>
        <?php if (!is_null($product['finalPrice'])): ?>
          <div class="discount-pricing">
            <h3>$<?= number_format($product['finalPrice'], 2) ?></h3>
            <p>$<?= number_format($product['originalPrice'], 2) ?></p>              
            <h2>( <tr></tr><?= number_format($product['discount']) ?>% OFF )</h2>              
          </div>    
        <?php else: ?>
          <div class="non-discount">
            <h2>$<?= number_format($product['originalPrice'], 2) ?></h2>
          </div>
        <?php endif; ?>
      </div> 
      <div class="rating"> 
        <?php if (!empty($totalRating['averageRating'])): ?>
            <h2><?= number_format($totalRating['averageRating'], 1) ?></h2>
        <?php else: ?>
            <h2>N/A</h2>
        <?php endif; ?>
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
        <p >( <?= htmlspecialchars($totalReview['totalReview'])?> Review )</p>
      </div>
      <form method="POST">
        <input type="hidden" name="productID" value="<?= $product['productID'] ?>">
        <p>Select Size</p>
        <div class="size-options">
          <?php foreach ($sizeCategory as $size): ?> 
          <label>
          <input type="radio" name="size-option" value="<?= htmlspecialchars($size['size'])?>" required>
            <div class="s">
              <p><?= htmlspecialchars($size['size']) ?></p>
            </div>
          </label>
          <?php endforeach; ?>
        </div>  
        <div class="productbutton">
          <button type="submit" name="add_to_cart" class="add-to-cart-btn">ADD TO BAG</button>          
        </div>                
      </form>
        
      <div class="payment">
        <h2>Available Payment Methods:</h2>
        <div class="payment-wrapper">
          <img src="images/paymentIcon/fullBca.png" alt="">
          <img src="images/paymentIcon/fullDana.png" alt="">
          <img src="images/paymentIcon/fullGopay.png" alt="">
          <img src="images/paymentIcon/fullSeabank.png" alt="">
          <img src="images/paymentIcon/fullOvo.png" alt="">
        </div>
      </div>
    </div> 
  </div>
  <div class="bot">
    <div class="store-and-details">
      <div class="details">
        <h1>Details</h1>
        <p><?= htmlspecialchars($product['productDescription'])?></p>
      </div>
      <div class="store-details">
          <div class="store-wrapper">
            <div class="store-outline">
              <img src="uploads/stores/<?= htmlspecialchars($product['storeImage'])?>" alt="">
              <div class="store-detail">
                <h2><?= htmlspecialchars($product['storeName'])?></h2>
                <p><?= htmlspecialchars($product['storeEmail'])?></p>
                <div class="ratingss"> 
                  <?php if (!empty($storeDetail['storeRating'])): ?>
                      <h2><?= number_format($storeDetail['storeRating'], 1) ?></h2>
                  <?php else: ?>
                      <h2>N/A</h2>
                  <?php endif; ?>
                  <?php for ($i = 1; $i <= $totalStarsss; $i++): ?>
                      <?php if ($i <= $fullStarsss): ?>
                          <svg class="starss full" viewBox="0 0 24 24">
                              <path d="M12 2l2.9 6.9L22 9.2l-5.5 4.8L18 21l-6-3.6L6 21l1.5-7L2 9.2l7.1-0.3L12 2z"/>
                          </svg>
                      <?php elseif ($i == $fullStarsss + 1 && $halfStarss): ?>
                          <svg class="starss" viewBox="0 0 24 24">
                              <defs>
                                  <linearGradient id="halfGold" x1="0%" y1="0%" x2="100%" y2="0%">
                                      <stop offset="50%" stop-color="gold"/>
                                      <stop offset="50%" stop-color="white"/>
                                  </linearGradient>
                              </defs>
                              <path d="M12 2l2.9 6.9L22 9.2l-5.5 4.8L18 21l-6-3.6L6 21l1.5-7L2 9.2l7.1-0.3L12 2z" fill="url(#halfGold)" stroke="gold" stroke-width="2"/>
                          </svg>
                      <?php else: ?>
                          <svg class="starss" viewBox="0 0 24 24">
                              <path d="M12 2l2.9 6.9L22 9.2l-5.5 4.8L18 21l-6-3.6L6 21l1.5-7L2 9.2l7.1-0.3L12 2z"/>
                          </svg>
                      <?php endif; ?>
                  <?php endfor; ?>
                  <p >( <?= htmlspecialchars($storeDetail['storeReview'])?> Review )</p>
                </div>
              </div>
            </div>
            <p class="storeDesc"><?= htmlspecialchars($product['storeDescription'])?></p>
          </div>
      </div>
    </div>
    <div class="reviews-container">
      <h1>Product Reviews</h1>
      <div class="reviews">
        <?php foreach ($reviews as $review): ?>
          <div class="review-wrapper">
            <div class="reviewer">
              <img src="uploads/profile/<?= htmlspecialchars($review['customerImage'])?>" alt="">
              <div class="reviewer-detail">
                <h2><?= htmlspecialchars($review['Name'])?></h2>
                <p><?= htmlspecialchars($review['Email'])?></p>
              </div>
            </div>
            <?php
              $ratings = $review['rating'];
              $fullStarss = floor($ratings);
              $halfStars = ($ratings - $fullStarss) >= 0.5;
              $totalStarss = 5;
            ?>
            <div class="ratings"> 
              <h2><?= number_format($review['rating'], 1) ?></h2>
              <?php for ($i = 1; $i <= $totalStarss; $i++): ?>
                  <?php if ($i <= $fullStarss): ?>
                      <svg class="stars full" viewBox="0 0 24 24">
                          <path d="M12 2l2.9 6.9L22 9.2l-5.5 4.8L18 21l-6-3.6L6 21l1.5-7L2 9.2l7.1-0.3L12 2z"/>
                      </svg>
                  <?php elseif ($i == $fullStarss + 1 && $halfStars): ?>
                      <svg class="stars" viewBox="0 0 24 24">
                          <defs>
                              <linearGradient id="halfGold" x1="0%" y1="0%" x2="100%" y2="0%">
                                  <stop offset="50%" stop-color="gold"/>
                                  <stop offset="50%" stop-color="white"/>
                              </linearGradient>
                          </defs>
                          <path d="M12 2l2.9 6.9L22 9.2l-5.5 4.8L18 21l-6-3.6L6 21l1.5-7L2 9.2l7.1-0.3L12 2z" fill="url(#halfGold)" stroke="gold" stroke-width="2"/>
                      </svg>
                  <?php else: ?>
                      <svg class="stars" viewBox="0 0 24 24">
                          <path d="M12 2l2.9 6.9L22 9.2l-5.5 4.8L18 21l-6-3.6L6 21l1.5-7L2 9.2l7.1-0.3L12 2z"/>
                      </svg>
                  <?php endif; ?>
              <?php endfor; ?>
            </div>
            <p class="review-comment"><?= htmlspecialchars($review['review'])?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/homeCustomer.js"></script>
    <script src="js/product.js"></script>
</body>
</html>