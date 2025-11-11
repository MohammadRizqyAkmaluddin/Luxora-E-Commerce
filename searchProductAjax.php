<?php
include 'connect.php';


if (isset($_GET['searchOnly'])) {
    $search = $_GET['searchOnly'];
    $stmt = $conn->prepare("SELECT productID, productName FROM product WHERE productName LIKE ? LIMIT 10");
    $like = "%" . $search . "%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<ul>';
        while ($product = $result->fetch_assoc()) {
            $name = htmlspecialchars($product['productName']);
            $id = htmlspecialchars($product['productID']);
            echo "<li><a href='product.php?id=$id'>$name</a></li>";
        }
        echo '</ul>';
    } else {
        echo '<p style="padding: 8px;">No results found.</p>';
    }
}

?>