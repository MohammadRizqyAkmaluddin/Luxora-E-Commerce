<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['storeID'])) {
    die("Anda harus login terlebih dahulu.");
}

$storeID = $_SESSION['storeID'];
$query = "SELECT storeName, storePhoneNum, storeEmail, storeAddress, storeImage FROM store WHERE storeID = '$storeID'";
$result = mysqli_query($conn, $query);
$store = mysqli_fetch_assoc($result);

$profileImage = !empty($store['storeImage']) ? 'uploads/stores/' . $store['storeImage'] : 'images/empty-profile.jpg';

$previewQuery = "SELECT p.* FROM product p JOIN store s ON p.storeID = s.storeID WHERE p.storeID = '$storeID'";
$previewResult = $conn->query($previewQuery);

$query = "SELECT p.*
          FROM product p
          JOIN store s ON p.storeID = s.storeID
          WHERE p.storeID = '$storeID'
          ORDER BY RAND()";
$result = mysqli_query($conn, $query);
$products = mysqli_fetch_all($result, MYSQLI_ASSOC);

$searchOnly = isset($_GET['searchOnly']) ? trim($_GET['searchOnly']) : '';
$productQuery = "SELECT p.*, 
                    COALESCE(SUM(od.quantity), 0) AS totalSold,
                    COALESCE(hd.discount, '-') AS discountPercent
                FROM product p
                LEFT JOIN orderDetail od ON p.productID = od.productID
                LEFT JOIN hotDeals hd ON p.productID = hd.productID
                WHERE p.storeID = '$storeID'";
if (!empty($searchOnly)) {
    $productQuery .= " AND p.productName LIKE '%" . $conn->real_escape_string($searchOnly) . "%'";
}
$productQuery .= " GROUP BY p.productID";
$productResult = $conn->query($productQuery);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home store</title>
    <link rel="stylesheet" href="css/homeStore.css">
    <link rel="stylesheet" href="css/storeNavbar.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Afacad:ital,wght@0,400..700;1,400..700&family=Bebas+Neue&family=Gabarito:wght@400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Luxora</title>      
    <link rel="icon" href="images/logos/logo.png" type="image/png">

    <style>
        
    </style>
</head>
<body>
<header>

    <div class="header-main">

  <div class="container">

    <a href="homeStore.php" class="header-logo"><img src="images/logos/logo-black.png" alt=""></a>
    <ul class="desktop-menu-category-list">
       <li class="menu-category">
          <a href="#" class="menu-title">Dashboard</a>
       </li>

       <li class="menu-category">
          <a href="productManage.php" class="menu-title">Manage product</a>
       </li>
       <li class="menu-category">
         <a href="#" class="menu-title">Sellings</a>
       </li>
       <li class="menu-category">
         <a href="#" class="menu-title">Customer Review</a>
       </li>
    </ul>

    <div class="header-user-actions">
    <div class="drop-down">
        
        <img src="<?= htmlspecialchars($profileImage);  ?>" class="nav-profile-img">
        <div class="sub-menu-wrapp">
          <div class="sub-menuu">
              <div class="user-info">
                  <img src="<?= htmlspecialchars($profileImage);  ?>" class="drop-profile-img">
                  <div>
                      <h1> <?= htmlspecialchars($store['storeName']) ?> </h1>
                      <h2><?= htmlspecialchars($store['storeEmail']) ?></a></h2>
                      
                  </div>
                  
              </div>
              <hr>
              <ul class="drop__listt">
              <li><a href="storeSetting.php" class="nav__link">Account Settings</a></li>
           <li><a href="#" class="nav__link">Privacy Policy</a></li>
           <li><a href="logout.php" class="nav__link">Log Out</a></li>
        </ul>
          </div>
        </div>
    </div>
    </div>
  </div>
    </div>
    </nav>

</header>



  <main>
    <div class="store-section"> 
      <a><img src="<?= htmlspecialchars($profileImage);  ?>" class="home-profile-img"></a>
      <div class="storeInformation">
        <div class="store_name"><?= htmlspecialchars($store['storeName']) ?></div> 
        <div class="store_detail"><i class="fa-solid fa-map-location-dot"></i><?= htmlspecialchars($store['storeAddress']) ?></div> 
        <div class="store_detail"><i class="fa-solid fa-phone"></i><?= htmlspecialchars($store['storePhoneNum']) ?></div> 
        <div class="store_detail"><i class="fa-solid fa-envelope"></i><?= htmlspecialchars($store['storeEmail']) ?></div> 
      </div> 
    </div>
    <div class="product-preview">
    <div class="slider" reverse="true" style="--quantity: <?= count($products) ?>;">
        <div class="list">
            <?php
            $position = 1;
            foreach ($products as $product):
            ?>
                <div class="item" style="--position: <?= $position ?>;">
                    <img src="uploads/products/<?= htmlspecialchars($product['productImage']) ?>" alt="">
                </div>
            <?php
            $position++;
            endforeach;
            ?>
        </div>
    </div>

    </div>
  </main>
    
   


  
</body>
</html>
