-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Waktu pembuatan: 24 Sep 2025 pada 07.31
-- Versi server: 9.1.0
-- Versi PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `luxora`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `customer`
--

DROP TABLE IF EXISTS `customer`;
CREATE TABLE IF NOT EXISTS `customer` (
  `customerID` varchar(6) NOT NULL,
  `Name` varchar(50) DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `PhoneNumber` varchar(20) DEFAULT NULL,
  `Address` varchar(50) DEFAULT NULL,
  `Email` varchar(50) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `customerImage` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`customerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `delivery`
--

DROP TABLE IF EXISTS `delivery`;
CREATE TABLE IF NOT EXISTS `delivery` (
  `deliveryID` varchar(2) NOT NULL,
  `deliveryType` varchar(50) DEFAULT NULL,
  `deliveryFee` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`deliveryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `hotdeals`
--

DROP TABLE IF EXISTS `hotdeals`;
CREATE TABLE IF NOT EXISTS `hotdeals` (
  `productID` varchar(6) NOT NULL,
  `discount` decimal(10,2) DEFAULT NULL,
  `finalPrice` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`productID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `order`
--

DROP TABLE IF EXISTS `order`;
CREATE TABLE IF NOT EXISTS `order` (
  `orderID` varchar(6) NOT NULL,
  `customerID` varchar(6) DEFAULT NULL,
  `paymentTypeID` varchar(50) DEFAULT NULL,
  `tax` decimal(10,2) DEFAULT NULL,
  `totalPrice` decimal(10,2) DEFAULT NULL,
  `orderDate` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`orderID`),
  KEY `customerID` (`customerID`),
  KEY `paymentTypeID` (`paymentTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `orderdetail`
--

DROP TABLE IF EXISTS `orderdetail`;
CREATE TABLE IF NOT EXISTS `orderdetail` (
  `orderDetailID` varchar(6) NOT NULL,
  `subOrderID` varchar(6) DEFAULT NULL,
  `productID` varchar(6) DEFAULT NULL,
  `size` varchar(6) DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`orderDetailID`),
  KEY `subOrderID` (`subOrderID`),
  KEY `productID` (`productID`),
  KEY `size` (`size`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `payment`
--

DROP TABLE IF EXISTS `payment`;
CREATE TABLE IF NOT EXISTS `payment` (
  `paymentTypeID` varchar(2) NOT NULL,
  `paymentType` varchar(50) DEFAULT NULL,
  `adminFee` decimal(10,2) DEFAULT NULL,
  `paymentIcon` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`paymentTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `product`
--

DROP TABLE IF EXISTS `product`;
CREATE TABLE IF NOT EXISTS `product` (
  `productID` varchar(6) NOT NULL,
  `productTypeID` varchar(2) DEFAULT NULL,
  `storeID` varchar(6) DEFAULT NULL,
  `productName` varchar(50) DEFAULT NULL,
  `productDescription` text,
  `price` int DEFAULT NULL,
  `stock` int DEFAULT NULL,
  `productImage` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`productID`),
  KEY `productTypeID` (`productTypeID`),
  KEY `storeID` (`storeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `productcategory`
--

DROP TABLE IF EXISTS `productcategory`;
CREATE TABLE IF NOT EXISTS `productcategory` (
  `productTypeID` varchar(6) NOT NULL,
  `productType` varchar(50) DEFAULT NULL,
  `sizeType` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`productTypeID`),
  KEY `sizeType` (`sizeType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `productreview`
--

DROP TABLE IF EXISTS `productreview`;
CREATE TABLE IF NOT EXISTS `productreview` (
  `orderDetailID` varchar(6) NOT NULL,
  `rating` int DEFAULT NULL,
  `review` text,
  `reviewDate` date DEFAULT NULL,
  PRIMARY KEY (`orderDetailID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `shoppingcart`
--

DROP TABLE IF EXISTS `shoppingcart`;
CREATE TABLE IF NOT EXISTS `shoppingcart` (
  `customerID` varchar(6) NOT NULL,
  `productID` varchar(6) NOT NULL,
  `size` varchar(6) NOT NULL,
  `quantity` int DEFAULT NULL,
  `price` int DEFAULT NULL,
  `totalPrice` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`customerID`,`productID`,`size`),
  KEY `productID` (`productID`),
  KEY `size` (`size`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sizecategory`
--

DROP TABLE IF EXISTS `sizecategory`;
CREATE TABLE IF NOT EXISTS `sizecategory` (
  `sizeType` varchar(50) NOT NULL,
  PRIMARY KEY (`sizeType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sizelist`
--

DROP TABLE IF EXISTS `sizelist`;
CREATE TABLE IF NOT EXISTS `sizelist` (
  `size` varchar(6) NOT NULL,
  `sizeType` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`size`),
  KEY `sizeType` (`sizeType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `store`
--

DROP TABLE IF EXISTS `store`;
CREATE TABLE IF NOT EXISTS `store` (
  `storeID` varchar(6) NOT NULL,
  `storename` varchar(50) DEFAULT NULL,
  `storePhoneNum` varchar(20) DEFAULT NULL,
  `storeEmail` varchar(50) DEFAULT NULL,
  `storeAddress` varchar(50) DEFAULT NULL,
  `storeDescription` text,
  `password` varchar(255) DEFAULT NULL,
  `storeImage` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`storeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `storereview`
--

DROP TABLE IF EXISTS `storereview`;
CREATE TABLE IF NOT EXISTS `storereview` (
  `subOrderID` varchar(6) NOT NULL,
  `review` text,
  `rating` int DEFAULT NULL,
  PRIMARY KEY (`subOrderID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `suborder`
--

DROP TABLE IF EXISTS `suborder`;
CREATE TABLE IF NOT EXISTS `suborder` (
  `subOrderID` varchar(6) NOT NULL,
  `orderID` varchar(6) DEFAULT NULL,
  `storeID` varchar(6) DEFAULT NULL,
  `deliveryID` varchar(2) DEFAULT NULL,
  `subTotal` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`subOrderID`),
  KEY `orderID` (`orderID`),
  KEY `storeID` (`storeID`),
  KEY `deliveryID` (`deliveryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `wishlist`
--

DROP TABLE IF EXISTS `wishlist`;
CREATE TABLE IF NOT EXISTS `wishlist` (
  `customerID` varchar(6) DEFAULT NULL,
  `productID` varchar(6) DEFAULT NULL,
  KEY `customerID` (`customerID`),
  KEY `productID` (`productID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `hotdeals`
--
ALTER TABLE `hotdeals`
  ADD CONSTRAINT `hotdeals_ibfk_1` FOREIGN KEY (`productID`) REFERENCES `product` (`productID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `order_ibfk_1` FOREIGN KEY (`customerID`) REFERENCES `customer` (`customerID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_ibfk_2` FOREIGN KEY (`paymentTypeID`) REFERENCES `payment` (`paymentTypeID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `orderdetail`
--
ALTER TABLE `orderdetail`
  ADD CONSTRAINT `orderdetail_ibfk_1` FOREIGN KEY (`subOrderID`) REFERENCES `suborder` (`subOrderID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `orderdetail_ibfk_2` FOREIGN KEY (`productID`) REFERENCES `product` (`productID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `orderdetail_ibfk_3` FOREIGN KEY (`size`) REFERENCES `sizelist` (`size`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`productTypeID`) REFERENCES `productcategory` (`productTypeID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_ibfk_2` FOREIGN KEY (`storeID`) REFERENCES `store` (`storeID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `productcategory`
--
ALTER TABLE `productcategory`
  ADD CONSTRAINT `productcategory_ibfk_1` FOREIGN KEY (`sizeType`) REFERENCES `sizecategory` (`sizeType`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `productreview`
--
ALTER TABLE `productreview`
  ADD CONSTRAINT `productreview_ibfk_1` FOREIGN KEY (`orderDetailID`) REFERENCES `orderdetail` (`orderDetailID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `shoppingcart`
--
ALTER TABLE `shoppingcart`
  ADD CONSTRAINT `shoppingcart_ibfk_1` FOREIGN KEY (`customerID`) REFERENCES `customer` (`customerID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `shoppingcart_ibfk_2` FOREIGN KEY (`productID`) REFERENCES `product` (`productID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `shoppingcart_ibfk_3` FOREIGN KEY (`size`) REFERENCES `sizelist` (`size`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `sizelist`
--
ALTER TABLE `sizelist`
  ADD CONSTRAINT `sizelist_ibfk_1` FOREIGN KEY (`sizeType`) REFERENCES `sizecategory` (`sizeType`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `storereview`
--
ALTER TABLE `storereview`
  ADD CONSTRAINT `storereview_ibfk_1` FOREIGN KEY (`subOrderID`) REFERENCES `suborder` (`subOrderID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `suborder`
--
ALTER TABLE `suborder`
  ADD CONSTRAINT `suborder_ibfk_1` FOREIGN KEY (`orderID`) REFERENCES `order` (`orderID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `suborder_ibfk_2` FOREIGN KEY (`storeID`) REFERENCES `store` (`storeID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `suborder_ibfk_3` FOREIGN KEY (`deliveryID`) REFERENCES `delivery` (`deliveryID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`customerID`) REFERENCES `customer` (`customerID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`productID`) REFERENCES `product` (`productID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
