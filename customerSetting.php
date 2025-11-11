<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['customerID'])) {
    header("Location: loginPage.php");
    exit();
}

$customerID = $_SESSION['customerID'];

$query = "SELECT Name, Address, phoneNumber,Email, customerImage FROM customer WHERE customerID = '$customerID'";
$result = mysqli_query($conn, $query);
$customer = mysqli_fetch_assoc($result);

$profileImage = !empty($customer['customerImage']) ? 'uploads/profile/' . $customer['customerImage'] : 'images/empty-profile.jpg';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profileImage'])) {
    $uploadDir = 'uploads/profile/';
    $uploadFile = $uploadDir . basename($_FILES['profileImage']['name']);

    $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($imageFileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $uploadFile)) {

            $fileName = basename($_FILES['profileImage']['name']);
            $updateQuery = "UPDATE customer SET customerImage = '$fileName' WHERE customerID = '$customerID'";
            mysqli_query($conn, $updateQuery);

            header("Location: customerSetting.php");
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
    $query = "UPDATE customer SET Name = '$newName' WHERE customerID = '$customerID'";
    mysqli_query($conn, $query);
    header("Location: customerSetting.php"); 
    exit();
}

if (isset($_POST['updatePhone']) && !empty($_POST['phoneNum'])) {
    $newPhone = mysqli_real_escape_string($conn, $_POST['phoneNum']);
    $query = "UPDATE customer SET phoneNumber = '$newPhone' WHERE customerID = '$customerID'";
    mysqli_query($conn, $query);
    header("Location: customerSetting.php");
    exit();
}

if (isset($_POST['updateAddress']) && !empty($_POST['address'])) {
    $newAddress = mysqli_real_escape_string($conn, $_POST['address']);
    $query = "UPDATE customer SET Address = '$newAddress' WHERE customerID = '$customerID'";
    mysqli_query($conn, $query);
    header("Location: customerSetting.php");
    exit();
}

if (isset($_POST['updatePassword'])) {
    $oldPassword = $_POST['oldPassword'];
    $newPassword = password_hash($_POST['newPassword'], PASSWORD_DEFAULT);
      
    $checkQuery = "SELECT `password` FROM customer WHERE customerID = '$customerID'";
    $result = mysqli_query($conn, $checkQuery);
    $row = mysqli_fetch_assoc($result);

    if (password_verify($oldPassword, $row['password'])) {
        $updateQuery = "UPDATE customer SET password = '$newPassword' WHERE customerID = '$customerID'";
        mysqli_query($conn, $updateQuery);
        header("Location: customerSetting.php");
        exit();
    } else {
        $error = "Password lama salah.";
    }
}

if (isset($_POST['updateEmail'])) {
    $passwordConfirm = $_POST['passwordConfirm'];
    $newEmail = $_POST['email'];

    $checkQuery = "SELECT password FROM customer WHERE customerID = '$customerID'";
    $result = mysqli_query($conn, $checkQuery);
    $row = mysqli_fetch_assoc($result);

    if (password_verify($passwordConfirm, $row['password'])) {
        $updateQuery = "UPDATE customer SET Email = '$newEmail' WHERE customerID = '$customerID'";
        mysqli_query($conn, $updateQuery);
        header("Location: customerSetting.php");
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
    <link rel="stylesheet" href="css/customerSetting.css">
    <link rel="stylesheet" href="css/customerNavbar.css">
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
        <a href="homeCustomer.php"><i class="fa-solid fa-angle-left"></i></a>
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
            <h1><?= htmlspecialchars($customer['Name']) ?></h1>
            <div class="data-second">
                <p><?= htmlspecialchars($customer['Email']) ?></p>
                <p>+62 <?= htmlspecialchars($customer['phoneNumber']) ?></p>
                <p><?= htmlspecialchars($customer['Address']) ?></p>
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
        <input type="text" name="name" placeholder="<?= htmlspecialchars($customer['Name']) ?>"  autocomplete="off"required>
        </div>
        <input type="submit" class="btn" value="Submit" name="updateName">
        </form>
        <form method="post">
         <div class="input-group">
        <input type="number" name="phoneNum" placeholder="<?= htmlspecialchars($customer['phoneNumber']) ?>" autocomplete="off" required>
        </div>
        <input type="submit" class="btn" value="Submit" name="updatePhone">
        </form>
        <form method="post">
        <div class="input-group">
        <textarea name="address" placeholder="<?= htmlspecialchars($customer['Address']) ?>"  autocomplete="off" required></textarea>
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
            <input type="email" name="email" placeholder="Enter your new email" autocomplete="off" required>
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
    <script>
        let popup = document.getElementById("popup")
        function openPopup(){
            popup.classList.add("open-popup");
        }
        function closePopup(){
            popup.classList.remove("open-popup"); 
        }

        let popupp = document.getElementById("popupp")
        function openPopupp(){
            popupp.classList.add("open-popupp");
            navbar.style.display = "none";
            document.body.style.overflow = "hidden";
        }
        function closePopupp(){
            popupp.classList.remove("open-popupp"); 
                 navbar.style.display = "none";
            document.body.style.overflow = "hidden";
        }

        
        function triggerFileInput() {
        document.getElementById('fileInput').click();
        }

        document.getElementById('fileInput').addEventListener('change', function () {
            let formData = new FormData(document.getElementById('uploadForm'));

            fetch('customerSetting.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    return response.text();
                }
            })
            .catch(error => {
                console.error('Upload error:', error);
            });
        });

    </script>

</body>
</html>
