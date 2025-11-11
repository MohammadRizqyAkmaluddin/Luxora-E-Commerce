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

if (!isset($_SESSION['sEmail'])) {
    header("Location: index.php");
    exit();
}



$storeID = $_SESSION['storeID'];
$query = "SELECT storeImage FROM store WHERE storeID = '$storeID'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

$profileImage = !empty($row['storeImage']) ? 'uploads/stores/' . $row['storeImage'] : 'images/empty-profile.jpg';


$sellerEmail = $_SESSION['sEmail'];
$sellerQuery = "SELECT * FROM store WHERE storeEmail = '$sellerEmail'";
$sellerResult = $conn->query($sellerQuery);
if ($sellerResult->num_rows > 0) {
    $seller = $sellerResult->fetch_assoc();
    $sellerID = $seller['storeID'];
} else {
    echo "Seller not found!";
    exit();
}

$categoryQuery = "SELECT DISTINCT c.productTypeID, c.productType 
                  FROM productCategory c
                  JOIN product p ON c.productTypeID = p.productTypeID
                  WHERE p.storeID = '$sellerID'";
$categoryResult = $conn->query($categoryQuery);

$filterCategory = isset($_GET['filterCategory']) ? $_GET['filterCategory'] : '';
$sortStock = isset($_GET['sortStock']) ? $_GET['sortStock'] : '';
$sortSold = isset($_GET['sortSold']) ? $_GET['sortSold'] : '';
$searchOnly = isset($_GET['searchOnly']) ? trim($_GET['searchOnly']) : '';

$productQuery = "SELECT p.*, 
                    COALESCE(SUM(od.quantity), 0) AS totalSold,
                    COALESCE(hd.discount, '-') AS discountPercent
                FROM product p
                LEFT JOIN orderDetail od ON p.productID = od.productID
                LEFT JOIN hotDeals hd ON p.productID = hd.productID
                WHERE p.storeID = '$sellerID'";

if (!empty($searchOnly)) {
    $productQuery .= " AND p.productName LIKE '%" . $conn->real_escape_string($searchOnly) . "%'";
} else {
    if (!empty($filterCategory)) {
        $productQuery .= " AND p.productTypeID = '$filterCategory'";
    }
}

$productQuery .= " GROUP BY p.productID";

if (empty($searchOnly)) {
    $sortConditions = [];

    if (!empty($sortStock)) {
        if ($sortStock == 'Lowest') {
            $sortConditions[] = "p.stock ASC";
        } elseif ($sortStock == 'Highest') {
            $sortConditions[] = "p.stock DESC";
        }
    }

    if (!empty($sortSold)) {
        if ($sortSold == 'Lowest') {
            $sortConditions[] = "totalSold ASC";
        } elseif ($sortSold == 'Highest') {
            $sortConditions[] = "totalSold DESC";
        }
    }

    if (!empty($sortConditions)) {
        $productQuery .= " ORDER BY " . implode(", ", $sortConditions);
    }
}

$productResult = $conn->query($productQuery);





$total_items_query = "SELECT COUNT(productID) as totalItems FROM product WHERE storeID = '$storeID'";
$total_items_result = mysqli_query($conn, $total_items_query);
$total_items = mysqli_fetch_assoc($total_items_result)['totalItems'];

$stock_query = "SELECT COUNT(*) as outOfStock FROM product WHERE storeID = '$storeID' AND stock = 0";
$stock_result = mysqli_query($conn, $stock_query);
$outOfStockCount = mysqli_fetch_assoc($stock_result)['outOfStock'];

if ($outOfStockCount > 0) {
    $stock_status = "$outOfStockCount Out Of Stock";
} else {
    $stock_status = "All Available";
}

$discount_query = "SELECT COUNT(*) as discounted FROM hotDeals 
                   JOIN product ON hotDeals.productID = product.productID 
                   WHERE product.storeID = '$storeID'";
$discount_result = mysqli_query($conn, $discount_query);
$Discounted_product = mysqli_fetch_assoc($discount_result)['discounted'];


$discountSelection = $conn->query("SELECT productID, productName FROM product WHERE storeID = '$sellerID'");




if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['addProduct'])) {
        if (!isset($conn)) {
            die("Database connection error.");
        }
        $productName = mysqli_real_escape_string($conn, $_POST['productName']);
        $productDescription = mysqli_real_escape_string($conn, $_POST['productDescription']);
        $productPrice = $_POST['productPrice'];
        $productStock = $_POST['productStock'];
        $typeID = $_POST['typeID'];

        $productID = generateCustomID($conn, "PRO", "product", "productID");

        if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] == 0) {
            $imageName = basename($_FILES['productImage']['name']);
            $imageTmpName = $_FILES['productImage']['tmp_name'];
            $imagePath = "uploads/products/" . $imageName;

            if (move_uploaded_file($imageTmpName, $imagePath)) {
                $insertProduct = $conn->prepare("INSERT INTO product (productID, productTypeID, storeID, productName, productDescription, price, stock, productImage) 
                                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $insertProduct->bind_param("sssssdss", $productID, $typeID, $sellerID, $productName, $productDescription, $productPrice, $productStock, $imageName);

                if ($insertProduct->execute()) {
                    header("Location: productManagement.php");
                    exit;
                } else {
                    echo "Gagal menambahkan produk: " . $conn->error;
                }

                $insertProduct->close();
            } else {
                echo "Failed to upload image.";
            }
        } else {
            echo "No image uploaded or an error occurred.";
        }
    }

    else if (isset($_POST['deleteProduct'])) {
        if (!isset($conn)) {
            die("Database connection error.");
        }

        $productID = $_POST['productID'];

        $deleteProduct = $conn->prepare("DELETE FROM product WHERE productID = ?");
        $deleteProduct->bind_param("s", $productID);

        if ($deleteProduct->execute()) {
            header("Location: homeStore.php");
            exit;
        } else {
            echo "Failed to delete product: " . $conn->error;
        }

        $deleteProduct->close();
    }
}


if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['addToHotDeals'])) {
        $productID = $_POST['productID'];
        $discount = $_POST['discount'];
        
        $result = $conn->query("SELECT price FROM product WHERE productID = '$productID'");
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $originalPrice = $row['price'];
            
            $finalPrice = $originalPrice - ($originalPrice * ($discount / 100));
            
            $checkExist = $conn->query("SELECT * FROM hotDeals WHERE productID = '$productID'");
            
            if ($checkExist->num_rows > 0) {
                $conn->query("UPDATE hotDeals SET discount = '$discount', finalPrice = '$finalPrice' WHERE productID = '$productID'");
            } else {
                $conn->query("INSERT INTO hotDeals (productID, discount, finalPrice) VALUES ('$productID', '$discount', '$finalPrice')");
            }
            
            header("Location: homeStore.php");
        } else {
            echo "Product not found.";
        }
    }
}

$category_query = "SELECT productTypeID, productType FROM productcategory";
$categoryDisplay = mysqli_query($conn, $category_query);
while ($row = mysqli_fetch_assoc($categoryDisplay)) {
    $productCategory[] = $row;
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/productManage.css">
    <link rel="stylesheet" href="css/storeNavbar.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.2.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" 
    integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" 
    crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Luxora</title>
    <link rel="icon" href="images/logos/logo.png" type="image/png">
</head>
<body>
<header>

    <div class="header-main" id="header">

  <div class="container">

    <a href="#" class="header-logo"><img src="images/logos/logo-black.png" alt=""></a>
    <ul class="desktop-menu-category-list">
       <li class="menu-category">
          <a href="homeStore.php" class="menu-title">Dashboard</a>
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
              <li><a href="storeSettings.php" class="nav__link">Account Settings</a></li>
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

<div class="all-content">
    <div class="sec-header">
        <h1>Product Management</h1>
        <form method="get">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input  type="text" name="searchOnly" placeholder="Search" value="<?= isset($_GET['searchOnly']) 
            ? htmlspecialchars($_GET['searchOnly']) : '' ?>" autocomplete="off">
        </form>
    </div>

    <div class="content">

    <div class="configuration-panel">
        
        <div class="details">
        <h1>Details</h1>
        <div class="details-wrap">
            <div class="details-label">
                <p>Total Product</p>  
                <p>Stock Status</p>  
                <p>Discounted Product</p>  
            </div>
            <div class="details-stat">
                <p><?php echo $total_items;?> Product</p>
                <p><?php echo $stock_status; ?></p>
                <p><?php echo $Discounted_product;?> Product</p>
            </div>
        </div>
        </div> 
        <div class="filter">
            <h1>Filter & Sort</h1>
            <form method="get">
            <div class="filter-wrap">
                <h2>Categories</h2>
                <select name="filterCategory" onchange="this.form.submit()">
                    <option value="">Default</option>
                    <?php foreach ($categoryResult as $category): ?>
                        <option value="<?= $category['productTypeID'] ?>" <?= (isset($_GET['filterCategory']) && $_GET['filterCategory'] == $category['productTypeID']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['productType']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-wrap">
                <h2>Stock</h2>
                <select name="sortStock" onchange="this.form.submit()">
                    <option value="">Default</option>
                    <option value="Lowest" <?= (isset($_GET['sortStock']) && $_GET['sortStock'] == 'Lowest') ? 'selected' : '' ?>>
                        Lowest
                    </option>
                    <option value="Highest" <?= (isset($_GET['sortStock']) && $_GET['sortStock'] == 'Highest') ? 'selected' : '' ?>>
                        Highest
                    </option>
                </select>
            </div>
            <div class="filter-wrap">
                <h2>Sold</h2>
                <select name="sortSold" onchange="this.form.submit()">
                    <option value="">Default</option>
                    <option value="Lowest" <?= (isset($_GET['sortSold']) && $_GET['sortSold'] == 'Lowest') ? 'selected' : '' ?>>
                        Lowest
                    </option>
                    <option value="Highest" <?= (isset($_GET['sortSold']) && $_GET['sortSold'] == 'Highest') ? 'selected' : '' ?>>
                        Highest
                    </option>
                </select>
            </div>
            </form>

        </div>
        <div class="tools">  
        <h1>Tools</h1>
            <button class="add-product" onclick="openPopup()"><i class="fa-solid fa-plus"></i> Add New Product</button>
            <button class="add-discount" onclick="openPopupp()"><i class="fa-solid fa-tag"></i> Manage Discount</button>
        </div>

        <div class="pop">
            <div class="popup" id="popup">
                <div class="add-product-form">
                    <div class="back">
                    <i class="fa-solid fa-angle-left" onclick="closePopup()"></i>
                    <div class="b">
                        <div>Add New Product</div>
                        <p>Back to Manage</p>
                    </div>
                    </div>
                    <form method="post" enctype="multipart/form-data">
                    <div class="image-input">
                            <label for="productImage" class="custom-file-label">Upload Image</label>
                            <input type="file" id="productImage" name="productImage" accept="image/*" required onchange="previewImage(event)">
                            <img id="preview">
                        </div>
                    <div class="category">
                            <h1>Product Type</h1>
                            <?php foreach ($productCategory as $category): ?>
                            <label>
                            <input type="radio" name="typeID" value="<?= htmlspecialchars($category['productTypeID'])?>" required>
                            <div class="category-wrap">
                            
                            <span><?= htmlspecialchars($category['productType']) ?></span>
                            </div>
                            </label><br>
                            <?php endforeach; ?>
                        </div>
                        <div class="detail-form">
                            <h1>Product Details</h1>
                            <div class="input-form">
                            <p>Product Name</p>
                            <input type="text" name="productName" placeholder="Product Name" required>
                            </div>
                            <div class="input-form">
                            <p>Price</p>
                            <input type="number" name="productPrice" placeholder="Price" required>
                            </div>
                            <div class="input-form">
                            <p>Unit Stock</p>
                            <input type="number" name="productStock" placeholder="Stock" required>
                            </div>
                            <div class="input-form">
                            <p>Description</p>
                            <textarea id="address" name="productDescription" placeholder="Description"></textarea>
                            </div>
                            <button type="submit" name="addProduct">Add Product</button>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>

        <div class="pop">
            <div class="popup" id="popupp">
            <div class="add-discount-form">
                <div class="back">
                    <i class="fa-solid fa-angle-left" onclick="closePopupp()"></i>
                    <div class="b">
                        <div>Discount Management</div>
                        <p>Back To Manage Product</p>
                    </div>
                </div>
                <form method="post">
                    <div class="form-wrap">
                        <div class="discount-label-form">
                        <span>Select Product</span>
                        <span>Discount</span>
                        </div>
                        <div class="wrap">
                        <div class="product-select">
                            <select name="productID" class="productDisc" required>
                                <?php foreach ($discountSelection as $productDisc): ?>
                                    <option value="<?= htmlspecialchars($productDisc['productID']) ?>" class="product-option">
                                        <?= htmlspecialchars($productDisc['productName']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="percentage">
                            <div class="input-percent-wrapper">
                                <input type="number" name="discount" placeholder="Discount Percentage" required>
                                <span class="percent-symbol">%</span>
                            </div>
                        </div>
                        </div>
                    </div>
                    <button type="submit" name="addToHotDeals">Set Discount</button>
                </form>

            </div>
            </div>
        </div>

    </div>

    <div class="product-panel">

        <div class="product-label">
            <h1>Product Info</h1>
            <h2>Price</h2>
            <h3>Stock</h3>
            <h4>Total Sold</h4>
            <h4>Discount</h4>
        </div>
        <div class="product-list">
            <?php foreach ($productResult as $products): ?>
            <div class="products">
                <div class="product-info">
                    <img src="uploads/products/<?php echo $products['productImage']; ?>" alt="Product Image">
                    <div class="info">
                        <h1><?= htmlspecialchars($products['productName']) ?></h1>
                        <h2>ID: <?= htmlspecialchars($products['productID']) ?></h2>
                    </div>
                </div>
                <p class="price">$<?= number_format($products['price'], 2) ?></p>
                <p class="stock"><?= ($products['stock']) ?></p>
                <p class="sold"><?= ($products['totalSold']) ?></p>
                <p class="discount"><?= is_numeric($products['discountPercent']) ? intval($products['discountPercent']) . '%' : '--' ?></p>
                <form method="post">
                    <input type="hidden" name="productID" value="<?php echo $products['productID']; ?>">
                    <button type="submit" name="deleteProduct"><i class="fa-solid fa-xmark"></i></button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    </div>
</div>

    <script src="js/productManage.js"></script>
</body>
</html>

<ul>
