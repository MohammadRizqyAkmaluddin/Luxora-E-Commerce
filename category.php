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


$total_items_query = "SELECT COUNT(DISTINCT productID) AS totalItems FROM shoppingCart WHERE customerID = '$customerID'";
$total_items_result = mysqli_query($conn, $total_items_query);
$total_items = mysqli_fetch_assoc($total_items_result)['totalItems'];

$total_wishlist_query = "SELECT COUNT(productID) AS totalFav 
                      FROM wishlist 
                      WHERE customerID = '$customerID'";
$total_wishlist_result = mysqli_query($conn, $total_wishlist_query);
$total_wishlist = mysqli_fetch_assoc($total_wishlist_result)['totalFav'];

$query = "SELECT storeID, storeImage, storeName 
          FROM store
          ORDER BY RAND()";
$result = mysqli_query($conn, $query);
$stores = mysqli_fetch_all($result, MYSQLI_ASSOC);

$category = isset($_GET['category']) ? $_GET['category'] : '';

$category = mysqli_real_escape_string($conn, $category);

$query = "SELECT p.productID, p.productName, p.productDescription, p.price, p.stock, p.productImage, s.storeName
          FROM product p
          JOIN store s ON p.storeID = s.storeID
          JOIN productCategory pc ON p.productTypeID = pc.productTypeID
          WHERE pc.productTypeID = '$category'";
$result = mysqli_query($conn, $query);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_favorite'])) {
  $productID = isset($_POST['productID']) ? trim($_POST['productID']) : '';

  if (empty($productID)) {
      die("Product ID tidak valid.");
  }

  $stmt = $conn->prepare("SELECT * FROM wishlist WHERE customerID = ? AND productID = ?");
  $stmt->bind_param("ss", $customerID, $productID);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows == 0) {
      $stmt = $conn->prepare("INSERT INTO wishlist (customerID, productID) VALUES (?, ?)");
      $stmt->bind_param("ss", $customerID, $productID);

      if ($stmt->execute()) {
          echo "<script>alert('Produk berhasil ditambahkan ke wishlist!');</script>";
      } else {
          echo "<script>alert('Gagal menambahkan produk ke wishlist!');</script>";
      }
  } else {
      echo "<script>alert('Produk sudah ada di wishlist!');</script>";
  }

  $stmt->close();
  $conn->close();
  echo "<script>window.history.back();</script>"; 
} 



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/customerMainNavbar.css">
    <link rel="stylesheet" href="css/homeCustomer.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Afacad:ital,wght@0,400..700;1,400..700&family=Bebas+Neue&family=Gabarito:wght@400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Luxora</title>      
    <link rel="icon" href="images/logos/logo.png" type="image/png">
</head>
<body>
    
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

<div class="productMain">
  <div class="productList">
  
  <?php foreach ($result as $product): ?>
    
    <div class="product-wrap">
        <div class="product-container">
        <a href="product.php?id=<?= htmlspecialchars($product['productID']) ?>">
            <img src="uploads/products/<?= htmlspecialchars($product['productImage']) ?>" class="product-image">
        </a>
          <form method="POST" class="cart-form">
              <input type="hidden" name="productID" value="<?= htmlspecialchars($product['productID']) ?>">
              <div class="productbutton">
                  <button type="submit" name="add_to_favorite" class="add-to-cart-btn1">Add To Wishlist</button> 
              </div>
          </form>
        </div>
        <div class="product-info">
            <div class="product-name"> <?= htmlspecialchars($product['productName']) ?></div>
            <div class="product-store"><?= htmlspecialchars($product['storeName']) ?></div>
            <div class="product-pricing">
              <div class="product-price">$<?= number_format($product['price'], 2) ?></div>
            </div>

        </div>
        
    </div>
    <?php endforeach; ?>
  </div>
</div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/homeCustomer.js"></script>
</body>
</html>