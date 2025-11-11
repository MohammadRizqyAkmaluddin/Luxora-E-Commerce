<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['storeID'])) {
    header("Location: loginPage.php");
    exit();
}

$storeID = $_SESSION['storeID'];

$query = "SELECT storeName, storePhoneNum, storeEmail, storeAddress, storeImage FROM store WHERE storeID = '$storeID'";
$result = mysqli_query($conn, $query);
$store = mysqli_fetch_assoc($result);

$profileImage = !empty($store['storeImage']) ? 'uploads/' . $store['storeImage'] : 'images/empty-profile.jpg';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profileImage'])) {
    $uploadDir = 'uploads/';
    $uploadFile = $uploadDir . basename($_FILES['profileImage']['name']);

    $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($imageFileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $uploadFile)) {
      
            $fileName = basename($_FILES['profileImage']['name']);
            $updateQuery = "UPDATE store SET storeImage = '$fileName' WHERE storeID = '$customerID'";
            mysqli_query($conn, $updateQuery);
            
            header("Location: storeSetting.php");
            exit();
        } else {
            $error = "Gagal mengunggah file.";
        }
    } else {
        $error = "Format file tidak didukung. Harap unggah JPG, JPEG, PNG, atau GIF.";
    }
}


if (isset($_POST['updateName']) && !empty($_POST['name'])) {
    $newName = mysqli_real_escape_string($conn, $_POST['name']);
    $query = "UPDATE `store` SET storeName = '$newName' WHERE storeID = '$storeID'";
    mysqli_query($conn, $query);
    header("Location: storeSetting.php"); 
    exit();
}

if (isset($_POST['updatePhone']) && !empty($_POST['phoneNum'])) {
    $newPhone = mysqli_real_escape_string($conn, $_POST['phoneNum']);
    $query = "UPDATE `store` SET storePhoneNum = '$newPhone' WHERE storeID = '$storeID'";
    mysqli_query($conn, $query);
    header("Location: storeSetting.php");
    exit();
}

if (isset($_POST['updateAddress']) && !empty($_POST['address'])) {
    $newAddress = mysqli_real_escape_string($conn, $_POST['address']);
    $query = "UPDATE `store` SET storeAddress = '$newAddress' WHERE storeID = '$storeID'";
    mysqli_query($conn, $query);
    header("Location: storeSetting.php");
    exit();
}

if (isset($_POST['updatePassword'])) {
    $oldPassword = $_POST['oldPassword'];
    $newPassword = password_hash($_POST['newPassword'], PASSWORD_DEFAULT);
      
    $checkQuery = "SELECT `password` FROM `store` WHERE storeID = '$storeID'";
    $result = mysqli_query($conn, $checkQuery);
    $row = mysqli_fetch_assoc($result);

    if (password_verify($oldPassword, $row['password'])) {
        $updateQuery = "UPDATE `store` SET password = '$newPassword' WHERE storeID = '$storeID'";
        mysqli_query($conn, $updateQuery);
        header("Location: storeSetting.php");
        exit();
    } else {
        $error = "Password lama salah.";
    }
}

if (isset($_POST['updateEmail'])) {
    $passwordConfirm = $_POST['passwordConfirm'];
    $newEmail = $_POST['email'];

    $checkQuery = "SELECT `password` FROM store WHERE storeID = '$storeID'";
    $result = mysqli_query($conn, $checkQuery);
    $row = mysqli_fetch_assoc($result);

    if (password_verify($passwordConfirm, $row['password'])) {
        $updateQuery = "UPDATE `store` SET storeEmail = '$newEmail' WHERE storeID = '$storeID'";
        mysqli_query($conn, $updateQuery);
        header("Location: storeSetting.php");
        exit();
    } else {
        $error = "Password konfirmasi salah.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
    <link rel="stylesheet" href="css/customerSetting.css">
    <link rel="stylesheet" href="css/customerNavbar.css">
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
        <a href="homeStore.php"><i class="fa-solid fa-angle-left"></i>Home</a>
    </div>
    <h1>LUXORA</h1>
</div>


<div class="content">
  <div class="secHeader">
        <h1>Settings</h1>
  </div>

  <div class="setting-main"> 
   <div class="left-side">
    <div class="profile-container">
         
       <div class="profile-details">
       <div class="image-setting">
        <img src="<?= htmlspecialchars($profileImage); ?>" class="profile-img" alt="Profile Image">
        <i class="fa-solid fa-camera" onclick="triggerFileInput()"></i>
        <form id="uploadForm" enctype="multipart/form-data" style="display: none;">
            <input type="file" name="profileImage" id="fileInput" accept="image/*">
        </form>
        </div>
        <div class="data-profile">
            <h1><?= htmlspecialchars($store['storeName']) ?></h1>
            <div class="data-second">
                <p><?= htmlspecialchars($store['storeEmail']) ?></p>
                <p><?= htmlspecialchars($store['storePhoneNum']) ?></p>
                <p><?= htmlspecialchars($store['storeAddress']) ?></p>
            </div>
        </div>
       </div>
    </div>
    <div class="profile-manager">
        
    </div>
   </div>

    <div class="settings">
        <div class="top">
       <h1>Account Settings</h1>
       <p>Personalize Your Account Details</p>
       </div>
       <div class="setting-list">
       <form method="post">
        <div class="input-group">
        <input type="text" name="name" placeholder="<?= htmlspecialchars($store['storeName']) ?>" required>
        </div>
        <input type="submit" class="btn" value="Submit" name="updateName">
        </form>
        <form method="post">
         <div class="input-group">
        <input type="number" name="phoneNum" placeholder="<?= htmlspecialchars($store['storePhoneNum']) ?>" required>
        </div>
        <input type="submit" class="btn" value="Submit" name="updatePhone">
        </form>
        <form method="post">
        <div class="input-group">
        <textarea name="address" placeholder="<?= htmlspecialchars($store['storeAddress']) ?>" required></textarea>
        </div>
        <input type="submit" class="btn" value="Submit" name="updateAddress">
        </form>

       
         </div> 
            <div class="top">
       <h1>Privacy and Security</h1>
       <p>Settings your security access</p>
       </div> 
       
       <div class="setting-list">
        <p onclick="openPopup()">Change Password</p>
        <p onclick="openPopupp()">Change Email Address</p>
       <div class="pop">
        <div class="popup" id="popup">
        <form method="post">
        <div class="form-wrap">
        <div class="input-group">
            <input type="password" name="oldPassword" placeholder="Enter your old password" required>
        </div>
        <div class="input-group">
            <input type="password" name="newPassword" placeholder="Enter your new password" required>
        </div>
        <input type="submit" class="btn" value="Change Password" name="updatePassword">
        </div>
        </form>
            <button type="button" onclick="closePopup()" >Cancel</button>
        </div>
       </div>

       <div class="pop">
        <div class="popup" id="popupp">
        <form method="post">
         <div class="form-wrap">
        <div class="input-group">
            <input type="password" name="passwordConfirm" placeholder="Enter your password" required>
        </div>
        <div class="input-group">
            <input type="email" name="email" placeholder="Enter your new email" required>
        </div>
        <input type="submit" class="btn" value="Change Email" name="updateEmail">
        </div>
        </form>
            <button type="button" onclick="closePopupp()" >Cancel</button>
        
            
        </div>
        </div>

       </div>
      
    </div>
  </div>
</div>
    <script src="js/storeSetting.js"></script>

</body>
</html>
