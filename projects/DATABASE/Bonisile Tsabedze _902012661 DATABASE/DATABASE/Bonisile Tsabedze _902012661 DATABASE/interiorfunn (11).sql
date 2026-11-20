-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 15, 2026 at 12:16 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `interiorfunn`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetCustomerOrders` (IN `p_CustomerID` INT)   BEGIN
    SELECT o.OrderID, o.OrderDate, o.TotalOrderCost, o.Status, i.ProductCode, i.Quantity
    FROM orders o
    JOIN invoices i ON o.OrderID = i.OrderID
    WHERE o.CustomerID = p_CustomerID
    ORDER BY o.OrderDate DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ProcessOrder` (IN `p_CustomerID` INT, IN `p_ProductCode` VARCHAR(4), IN `p_Quantity` INT)   BEGIN
    DECLARE v_Price DECIMAL(10,2);
    DECLARE v_Total DECIMAL(10,2);
    DECLARE v_OrderID INT;

    
    SELECT SellingPrice INTO v_Price FROM products WHERE ProductCode = p_ProductCode;
    SET v_Total = v_Price * p_Quantity;

    
    INSERT INTO orders (CustomerID, OrderDate, TotalOrderCost, Status, ApprovalStatus)
    VALUES (p_CustomerID, CURDATE(), v_Total, 'Pending', 'Pending');
    
    SET v_OrderID = LAST_INSERT_ID();

    
    INSERT INTO invoices (OrderID, ProductCode, Quantity, PriceEach, TotalCost)
    VALUES (v_OrderID, p_ProductCode, p_Quantity, v_Price, v_Total);
    
    SELECT v_OrderID AS NewOrderID, v_Total AS TotalAmount;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `CustomerID` int(11) NOT NULL,
  `FirstName` varchar(50) DEFAULT NULL,
  `LastName` varchar(50) DEFAULT NULL,
  `ContactNumber` varchar(20) DEFAULT NULL,
  `Address` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`CustomerID`, `FirstName`, `LastName`, `ContactNumber`, `Address`) VALUES
(0, 'Smanga', 'Motsa', '78343222', 'Zone 3'),
(1, 'Alice', 'Smith', '123-456-7890', '101 Main St'),
(2, 'Bob', 'Johnson', '234-567-8901', '102 Oak Ave'),
(3, 'Carol', 'Williams', '345-678-9012', '103 Pine Rd'),
(4, 'David', 'Brown', '456-789-0123', '104 Maple Dr'),
(5, 'Eve', 'Jones', '567-890-1234', '105 Elm St'),
(6, 'Frank', 'Garcia', '678-901-2345', '106 Cedar Ln'),
(7, 'Grace', 'Martinez', '789-012-3456', '107 Birch Blvd'),
(8, 'Hank', 'Rodriguez', '890-123-4567', '108 Spruce Ct'),
(9, 'Ivy', 'Lee', '901-234-5678', '109 Walnut St'),
(10, 'Jack', 'Walker', '012-345-6789', '110 Chestnut Ave'),
(11, 'Lwandile', 'Ndzingane', '123-456-7890', '123 Main St'),
(12, 'Lwex', 'Banks', '79369657', 'Mkhonubovu St'),
(20, 'SAMKE', 'STHOLE', '76445443', 'CHECKERS');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `EmployeeID` int(11) NOT NULL,
  `FirstName` varchar(50) DEFAULT NULL,
  `LastName` varchar(50) DEFAULT NULL,
  `Position` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`EmployeeID`, `FirstName`, `LastName`, `Position`) VALUES
(1, 'Michael', 'Scott', 'Manager'),
(2, 'Jim', 'Halpert', 'Sales'),
(3, 'Pam', 'Beesly', 'Receptionist'),
(4, 'Dwight', 'Schrute', 'Sales'),
(5, 'Stanley', 'Hudson', 'Sales'),
(6, 'Angela', 'Martin', 'Accountant'),
(7, 'Kevin', 'Malone', 'Accountant'),
(8, 'Oscar', 'Martinez', 'Accountant'),
(9, 'Phyllis', 'Vance', 'Sales'),
(10, 'Ryan', 'Howard', 'Temp'),
(11, 'Cebo', 'Thokazi', 'CEO'),
(12, 'Nkosenhle ', 'Dlamini', 'Cashier'),
(13, 'MR', 'GININDZA', 'MANAGER'),
(14, 'mamsy', 'dlamini', 'cashier'),
(17, 'SIPHIWO', 'SIMELANE', 'cashier'),
(19, 'MANAKA', 'GAMA', 'cashier'),
(21, 'SENZO', 'MANANA', 'cashier'),
(22, 'Anele', 'Ngwenya', 'MANAGER'),
(24, 'THOKAZI', 'NDZINGANE', 'MANAGER');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `InvoiceID` int(11) NOT NULL,
  `OrderID` int(11) DEFAULT NULL,
  `ProductCode` varchar(4) DEFAULT NULL,
  `Quantity` int(11) DEFAULT NULL,
  `PriceEach` decimal(10,2) DEFAULT NULL,
  `DiscountApplied` int(11) DEFAULT NULL,
  `TotalCost` decimal(10,2) DEFAULT NULL,
  `DateIssued` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`InvoiceID`, `OrderID`, `ProductCode`, `Quantity`, `PriceEach`, `DiscountApplied`, `TotalCost`, `DateIssued`) VALUES
(1, 0, 'P001', 3, 2300.00, NULL, 6900.00, '2025-11-29 12:41:15'),
(2, 1011, 'P002', 2, 20.00, NULL, 40.00, '2025-11-29 14:17:16'),
(3, 1012, 'P009', 10, 260.00, NULL, 2600.00, '2025-11-29 14:20:44'),
(4, 1013, 'P006', 10, 300.00, NULL, 3000.00, '2025-11-29 15:56:02'),
(5, 1014, 'P006', 10, 300.00, NULL, 3000.00, '2025-11-29 15:56:04'),
(6, 1015, 'P007', 4, 2400.00, NULL, 9600.00, '2025-11-29 16:46:46'),
(7, 1016, 'P007', 2, 2400.00, NULL, 4800.00, '2025-11-30 18:15:15'),
(8, 1017, 'P005', 2, 2500.00, NULL, 5000.00, '2025-11-30 18:15:47'),
(9, 1018, 'P005', 1, 2500.00, NULL, 2500.00, '2025-11-30 18:20:09'),
(10, 1019, 'P005', 1, 2500.00, NULL, 2500.00, '2025-11-30 18:22:45'),
(11, 1020, 'P008', 3, 280.00, NULL, 840.00, '2025-11-30 18:23:03'),
(12, 1021, 'P002', 1, 20.00, NULL, 20.00, '2025-11-30 18:44:21'),
(13, 1022, 'P007', 8, 2400.00, NULL, 19200.00, '2025-11-30 19:51:12'),
(14, 1023, 'P001', 10, 2300.00, NULL, 23000.00, '2025-12-01 07:05:36'),
(15, 1024, '02', 10, 1300.00, NULL, 13000.00, '2025-12-02 10:15:06'),
(16, 1025, '2331', 10, 2500.00, NULL, 25000.00, '2025-12-02 10:19:42'),
(17, 1026, '02', 10, 1300.00, NULL, 13000.00, '2025-12-02 10:21:40'),
(18, 1027, '02', 10, 1300.00, NULL, 13000.00, '2025-12-02 10:22:19'),
(19, 1028, '02', 100, 1300.00, NULL, 130000.00, '2025-12-02 10:43:14'),
(20, 1029, 'P004', 100, 2030.00, NULL, 203000.00, '2025-12-02 21:29:57'),
(21, 1030, 'P004', 100, 2030.00, NULL, 203000.00, '2025-12-02 21:29:57'),
(22, 1031, 'P005', 100, 2500.00, NULL, 250000.00, '2025-12-02 23:31:18'),
(23, 1032, '02', 100, 1300.00, NULL, 130000.00, '2025-12-03 18:22:45'),
(24, 1033, '02', 50, 1300.00, NULL, 65000.00, '2025-12-03 23:19:23'),
(25, 1034, '2331', 10, 2500.00, NULL, 25000.00, '2025-12-07 18:17:04'),
(26, 1035, 'P001', 10, 2300.00, NULL, 23000.00, '2025-12-07 20:00:15'),
(27, 1036, '2331', 10, 2500.00, NULL, 25000.00, '2025-12-07 22:05:02'),
(28, 1037, 'P003', 10, 500.00, NULL, 5000.00, '2025-12-08 07:57:32'),
(29, 1038, '2331', 10, 2500.00, NULL, 25000.00, '2026-01-12 13:50:01'),
(30, 1039, '2331', 10, 2500.00, NULL, 25000.00, '2026-01-14 23:39:29'),
(31, 1040, '2331', 50, 2500.00, NULL, 125000.00, '2026-01-15 06:51:00'),
(32, 1041, 'P004', 100, 2030.00, NULL, 203000.00, '2026-01-15 06:51:29'),
(33, 1042, 'P008', 100, 280.00, NULL, 28000.00, '2026-01-15 06:52:06'),
(34, 1043, '2331', 10, 2500.00, NULL, 25000.00, '2026-01-15 06:59:53'),
(35, 1044, 'P001', 1, 2300.00, NULL, 2300.00, '2026-01-15 07:05:39'),
(36, 1045, 'P003', 10, 500.00, NULL, 5000.00, '2026-01-15 07:38:50'),
(37, 1046, 'P002', 10, 20.00, NULL, 200.00, '2026-01-15 07:47:41'),
(38, 1047, 'P002', 10, 20.00, NULL, 200.00, '2026-01-15 07:51:29'),
(39, 1048, '2331', 100, 2500.00, NULL, 250000.00, '2026-01-15 08:37:42'),
(40, 1049, '2331', 100, 2500.00, NULL, 250000.00, '2026-01-15 08:37:58'),
(41, 1050, 'P002', 20, 20.00, NULL, 400.00, '2026-01-15 08:43:31'),
(42, 1051, 'P001', 9, 2300.00, NULL, 20700.00, '2026-01-15 08:50:54');

--
-- Triggers `invoices`
--
DELIMITER $$
CREATE TRIGGER `Before_Invoice_Insert` BEFORE INSERT ON `invoices` FOR EACH ROW BEGIN
    DECLARE v_Stock INT;
    
    SELECT QuantityAvailable INTO v_Stock 
    FROM products 
    WHERE ProductCode = NEW.ProductCode;

    IF v_Stock < NEW.Quantity THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Insufficient stock for this product.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `OrderID` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `EmployeeID` int(11) DEFAULT NULL,
  `OrderDate` date DEFAULT NULL,
  `TotalOrderCost` decimal(10,2) DEFAULT NULL,
  `Status` varchar(20) NOT NULL DEFAULT 'Pending',
  `ApprovalStatus` varchar(20) DEFAULT 'Pending',
  `Feedback` text DEFAULT NULL,
  `ApprovedDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`OrderID`, `CustomerID`, `EmployeeID`, `OrderDate`, `TotalOrderCost`, `Status`, `ApprovalStatus`, `Feedback`, `ApprovedDate`) VALUES
(1, 1, NULL, '2025-11-29', 6900.00, 'Pending', 'Pending', NULL, NULL),
(1001, 1, 2, '2025-11-01', 2300.00, 'Pending', 'Pending', NULL, NULL),
(1002, 2, 1, '2025-11-02', 500.00, 'Pending', 'Pending', NULL, NULL),
(1003, 3, 3, '2025-11-03', 150.00, 'Pending', 'Pending', NULL, NULL),
(1004, 4, 4, '2025-11-04', 2500.00, 'Pending', 'Pending', NULL, NULL),
(1005, 5, 5, '2025-11-05', 2030.00, 'Pending', 'Pending', NULL, NULL),
(1006, 6, 6, '2025-11-06', 45.00, 'Pending', 'approved', NULL, NULL),
(1007, 7, 7, '2025-11-07', 260.00, 'Pending', 'Pending', NULL, NULL),
(1008, 8, 8, '2025-11-08', 280.00, 'Pending', 'Pending', NULL, NULL),
(1009, 9, 9, '2025-11-09', 300.00, 'Pending', 'Pending', NULL, NULL),
(1010, 10, 10, '2025-11-10', 20.00, 'Pending', 'Pending', NULL, NULL),
(1011, 1, NULL, '2025-11-29', 40.00, 'Pending', 'Pending', NULL, NULL),
(1012, 1, NULL, '2025-11-29', 2600.00, 'Pending', 'Pending', NULL, NULL),
(1013, 4, NULL, '2025-11-29', 3000.00, 'Pending', 'Pending', NULL, NULL),
(1014, 4, NULL, '2025-11-29', 3000.00, 'Pending', 'Pending', NULL, NULL),
(1015, 4, NULL, '2025-11-29', 9600.00, 'Pending', 'Pending', NULL, NULL),
(1016, 20, NULL, '2025-11-30', 4800.00, 'Pending', 'Pending', NULL, NULL),
(1017, 20, NULL, '2025-11-30', 5000.00, 'Pending', 'Pending', NULL, NULL),
(1018, 20, NULL, '2025-11-30', 2500.00, 'Pending', 'Pending', NULL, NULL),
(1019, 20, NULL, '2025-11-30', 2500.00, 'Pending', 'Pending', NULL, NULL),
(1020, 20, NULL, '2025-11-30', 840.00, 'Pending', 'Approved', 'order approved', '2025-12-02 22:08:51'),
(1021, 20, NULL, '2025-11-30', 20.00, 'Pending', 'Pending', NULL, NULL),
(1022, 20, NULL, '2025-11-30', 19200.00, 'Pending', 'Pending', NULL, NULL),
(1023, 20, NULL, '2025-12-01', 23000.00, 'Pending', 'Pending', NULL, NULL),
(1024, 20, NULL, '2025-12-02', 13000.00, 'Pending', 'rejected', NULL, NULL),
(1025, 20, NULL, '2025-12-02', 25000.00, 'Pending', 'approved', 'order approved', NULL),
(1026, 20, NULL, '2025-12-02', 13000.00, 'Pending', 'approved', 'approved', NULL),
(1027, 20, NULL, '2025-12-02', 13000.00, 'Pending', 'approved', '0rder successfully', NULL),
(1028, 20, NULL, '2025-12-02', 130000.00, 'Pending', 'rejected', NULL, NULL),
(1029, 20, NULL, '2025-12-02', 203000.00, 'Pending', 'approved', NULL, NULL),
(1030, 20, NULL, '2025-12-02', 203000.00, 'Pending', 'approved', 'order approved', NULL),
(1031, 20, NULL, '2025-12-02', 250000.00, 'Pending', 'Pending', NULL, NULL),
(1032, 20, NULL, '2025-12-03', 130000.00, 'Pending', 'rejected', NULL, NULL),
(1033, 20, NULL, '2025-12-03', 65000.00, 'Pending', 'approved', NULL, NULL),
(1034, 20, NULL, '2025-12-07', 25000.00, 'Pending', 'approved', NULL, NULL),
(1035, 20, NULL, '2025-12-07', 23000.00, 'Pending', 'approved', NULL, NULL),
(1036, 20, NULL, '2025-12-07', 25000.00, 'Pending', 'Pending', NULL, NULL),
(1037, 20, NULL, '2025-12-08', 5000.00, 'Pending', 'Pending', NULL, NULL),
(1038, 20, NULL, '2026-01-12', 25000.00, 'Pending', 'Pending', NULL, NULL),
(1039, 20, NULL, '2026-01-14', 25000.00, 'Pending', 'Pending', NULL, NULL),
(1040, 20, NULL, '2026-01-15', 125000.00, 'Pending', 'Pending', NULL, NULL),
(1041, 20, NULL, '2026-01-15', 203000.00, 'Pending', 'Pending', NULL, NULL),
(1042, 20, NULL, '2026-01-15', 28000.00, 'Rejected', 'Rejected', 'weee', NULL),
(1043, 20, NULL, '2026-01-15', 25000.00, 'Pending', 'Pending', NULL, NULL),
(1044, 20, NULL, '2026-01-15', 2300.00, 'Pending', 'Pending', NULL, NULL),
(1045, 20, NULL, '2026-01-15', 5000.00, 'Pending', 'Pending', NULL, NULL),
(1046, 20, NULL, '2026-01-15', 200.00, 'Pending', 'Pending', NULL, NULL),
(1047, 20, NULL, '2026-01-15', 200.00, 'Rejected', 'Rejected', 'no money', NULL),
(1048, 20, NULL, '2026-01-15', 250000.00, 'Pending', 'Pending', NULL, NULL),
(1049, 20, NULL, '2026-01-15', 250000.00, 'Pending', 'Pending', NULL, NULL),
(1050, 20, NULL, '2026-01-15', 400.00, 'Pending', 'Pending', NULL, NULL),
(1051, 20, NULL, '2026-01-15', 20700.00, 'Pending', 'Pending', NULL, NULL);

--
-- Triggers `orders`
--
DELIMITER $$
CREATE TRIGGER `After_Order_Approved` AFTER UPDATE ON `orders` FOR EACH ROW BEGIN
    
    IF NEW.ApprovalStatus = 'approved' AND OLD.ApprovalStatus != 'approved' THEN
        UPDATE products p
        JOIN invoices i ON i.OrderID = NEW.OrderID
        SET p.QuantityAvailable = p.QuantityAvailable - i.Quantity
        WHERE p.ProductCode = i.ProductCode;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `ProductCode` varchar(4) NOT NULL,
  `Name` varchar(50) DEFAULT NULL,
  `Description` varchar(100) DEFAULT NULL,
  `QuantityAvailable` int(11) DEFAULT NULL,
  `SellingPrice` decimal(10,2) DEFAULT NULL,
  `Image` varchar(200) DEFAULT NULL,
  `DiscountPercentage` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`ProductCode`, `Name`, `Description`, `QuantityAvailable`, `SellingPrice`, `Image`, `DiscountPercentage`) VALUES
('2331', 'WALL UNIT', 'FITTED', 0, 2500.00, 'img/wall-unit.jpg', 0),
('P001', 'SOFA', 'leather-sofa', 80, 2300.00, 'img/sofa.jpg', 0),
('P002', 'SPOON', '5-SETS', 60, 20.00, 'img/spoon.jpg', 0),
('P003', 'TABLE', 'COFFY TABLE', 80, 500.00, 'img/table.jpg', 0),
('P004', 'WARDROPE', 'WOODEN', 0, 2030.00, 'img/wardrope.jpg', 0),
('P005', 'BED', 'KING-SIZE', 281, 2500.00, 'img/bed.jpg', 0),
('P006', 'PLATES', '5-SETS', 100, 300.00, 'img/plates.jpg', 0),
('P007', 'DOOR', 'WOODEN', 100, 2400.00, 'img/door.jpg', 0),
('P008', 'STOVE', 'DIAMOND', 0, 280.00, 'img/stove.jpg', 0),
('P009', 'SPEAKER', 'BLUETOOTH', 100, 260.00, 'img/speaker.jpg', 0),
('P011', 'CHAIR', 'WOODEN', 199, 500.00, 'img/chair.jpg', 0),
('P231', 'TV STAND', 'BLACK', 50, 1300.00, 'img/tv-stand.jpg', 0);

-- --------------------------------------------------------

--
-- Table structure for table `purchase`
--

CREATE TABLE `purchase` (
  `PurchaseID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `EmployeeID` int(11) DEFAULT NULL,
  `ProductCode` varchar(50) NOT NULL,
  `PurchaseDate` datetime NOT NULL,
  `Quantity` int(11) NOT NULL,
  `CostOfOneItem` decimal(10,2) NOT NULL,
  `TotalCost` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase`
--

INSERT INTO `purchase` (`PurchaseID`, `OrderID`, `EmployeeID`, `ProductCode`, `PurchaseDate`, `Quantity`, `CostOfOneItem`, `TotalCost`) VALUES
(1, 1020, NULL, 'P008', '2025-11-30 00:00:00', 3, 280.00, 840.00),
(2, 1025, NULL, '2331', '2025-12-02 00:00:00', 10, 2500.00, 25000.00),
(3, 1030, NULL, 'P004', '2025-12-02 00:00:00', 100, 2030.00, 203000.00),
(4, 1034, NULL, '2331', '2025-12-07 00:00:00', 10, 2500.00, 25000.00),
(5, 1035, NULL, 'P001', '2025-12-07 00:00:00', 10, 2300.00, 23000.00),
(6, 1029, NULL, 'P004', '2025-12-02 00:00:00', 100, 2030.00, 203000.00);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_requests`
--

CREATE TABLE `purchase_requests` (
  `RequestID` int(11) NOT NULL,
  `SupplierID` int(11) NOT NULL,
  `ProductCode` varchar(4) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `CostOfOneItem` decimal(10,2) NOT NULL,
  `Status` varchar(50) DEFAULT 'Pending Manager Approval'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `SupplierID` int(11) NOT NULL,
  `Name` varchar(50) DEFAULT NULL,
  `Address` varchar(100) DEFAULT NULL,
  `ContactNumber` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`SupplierID`, `Name`, `Address`, `ContactNumber`) VALUES
(0, 'BUILD IT', 'MBABANE', '76544332'),
(1, 'Simphiwe', 'Manzini', '76061545'),
(2, 'Senzo', 'Nhlangano', '76098678'),
(3, 'Thabo', 'Mbabane', '76123456'),
(4, 'Lindiwe', 'Hhohho', '76234567'),
(5, 'Sibusiso', 'Lubombo', '76345678'),
(6, 'Nomsa', 'Manzini', '76456789'),
(7, 'Musa', 'Nhlangano', '76567890'),
(8, 'Zanele', 'Mbabane', '76678901'),
(10, 'Susan', 'Lubombo', '76890123'),
(11, 'Simphiwe', 'Manzini', '76061545');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_orders`
--

CREATE TABLE `supplier_orders` (
  `OrderID` int(11) NOT NULL,
  `ProductCode` varchar(255) NOT NULL,
  `QuantityAdded` int(11) NOT NULL,
  `OrderDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_orders`
--

INSERT INTO `supplier_orders` (`OrderID`, `ProductCode`, `QuantityAdded`, `OrderDate`) VALUES
(1, 'P005', 8, '2025-12-01 05:35:41'),
(2, 'P011', 9, '2025-12-01 05:35:41'),
(3, 'P007', 9, '2025-12-01 05:35:41'),
(4, 'P001', 3, '2025-12-01 05:35:41'),
(5, 'P002', 1, '2025-12-01 05:35:41'),
(6, 'P003', 2, '2025-12-01 05:35:41'),
(7, 'P005', 10, '2025-12-01 05:39:05'),
(8, 'P005', 40, '2025-12-01 06:49:36'),
(9, 'P011', 30, '2025-12-01 06:49:36'),
(10, 'P007', 50, '2025-12-01 06:49:36'),
(11, 'P006', 60, '2025-12-01 06:49:36'),
(12, 'P001', 60, '2025-12-01 06:49:36'),
(13, 'P002', 60, '2025-12-01 06:49:36'),
(14, 'P008', 20, '2025-12-01 06:49:36'),
(15, 'P003', 50, '2025-12-01 06:49:36'),
(16, 'P004', 60, '2025-12-01 06:49:36');

--
-- Triggers `supplier_orders`
--
DELIMITER $$
CREATE TRIGGER `After_Supplier_Order` AFTER INSERT ON `supplier_orders` FOR EACH ROW BEGIN
    UPDATE products 
    SET QuantityAvailable = QuantityAvailable + NEW.QuantityAdded
    WHERE ProductCode = NEW.ProductCode;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'lwandile17lwex', '$2y$10$FwO4dzAMUt.2inhDGu.GY.1xonYjnMVCSHIkhQOo.vaYMG7MBi9ha', 'customer'),
(2, 'Lwakhile', '$2y$10$lUvbPGTfwWpphdhBEk7TxOcyJt7L9ymEcsZGZSblZ9F77RHyGz7HS', 'customer'),
(3, 'Smangs', '$2y$10$wR29fzDPmgUwd3VnDeXET.L4BmXGMQt6H82qCWx0vkE9OPw3qUnAa', 'customer'),
(4, 'NGAGARA', '$2y$10$u4t231HW18pyjWvBVnu4A.vTRjcfa1vIRVl/8S2Rd38ihtcIKaNP2', 'customer'),
(5, 'LOMPHOFU', '$2y$10$kBkSXQ7aQpzdINkJDIaAT.Mj3aewsb0zbwO1aUWnF.4g28X1TXdKG', 'employee'),
(6, 'Khosi', '$2y$10$ADHzwr4VYUVDvMQvqAc0wOUBO5QyYC2KhP4dwKmnqYivgnKHN2nHq', 'customer'),
(7, 'mamsy', '$2y$10$WHWdSjQuusHx/l/t8T9O7u1Cn..PpiSPTnYtBiWw4.Fcw.unjG3dy', 'employee'),
(8, 'lwethu', '$2y$10$XKReDAZdm1uLP3MXkO.ePe7rTS3biWyelgQydxowLkCk3rGRmTsbO', 'customer'),
(9, 'selu', '$2y$10$02npf2oc68IAVakTzerZEO2kg7CCVFfafP8JwiMmhyS/ZAsZDj5VK', 'customer'),
(10, 'yoyo', '$2y$10$BnSyr0K8hMrIMWswHH9uNuIOsvvF8w15j.EqSy/hiVO/E9L8kVMhW', 'employee'),
(11, 'thot', '$2y$10$u1cq1Yqiqmz/QEseEuivv.W1BNNWGJHlo6Xj4UXmu7WjNu1CqYzu2', 'customer'),
(12, 'sese', '$2y$10$l2RfBYGnbSciTjF60GX1p.LHLb4bpx6MA6sUpZFuqIBYxldo3ExYm', 'customer'),
(13, 'seer', '$2y$10$D3Qtg2LBFXBl0STx/rPPdODPZKUhKzW8MZwocHDweiZ4RxpPciqVu', 'employee'),
(14, 'lwaa', '$2y$10$pHh2o/MuEUsF154lb4F9Tu5oixOtvfZjPrI5KQvra6U3AG3i1y75O', 'employee'),
(15, 'we', '$2y$10$p9x6a0vZ6RGFVgSeRAJcPOLP7DAMX/9ac4.pRO1VXzNoBZCnrEwNK', 'customer'),
(16, 'SENA', '$2y$10$HVTXKlmlaGLTS.oz3Sj3j.wH2X0SB6H9S70bhMUBMtJ1kXdZbVu6W', 'customer'),
(17, 'SIPHO', '$2y$10$MxceOW8b5K.tYBhHVEje/eOU4iUwQl/gAMgYi5DR1BIi7SSesXKNC', 'employee'),
(18, 'SENE', '$2y$10$W7bswZ086UeBbiYGrlZ5DeIHWqvRi8BvHjSwfJgluSDLco09X8VGi', 'customer'),
(19, 'MANAKA', '$2y$10$Y2uZ/wUYvMPuUelTaTtAFurB6fNohRGueB9mjExnXTcYbD9KqMhWK', 'employee'),
(20, 'SAMKE', '$2y$10$c01zkfIuXioJ0v4uhLkHjexa0G1BShs4zNtKZ8x2Nx4dXNV8EPvf6', 'customer'),
(21, 'SENZO', '$2y$10$UfZVI6Urbfv7p4y5xptTEevj/bvQDhk6CFn8IkIXcpLneOb.HXgFi', 'employee'),
(22, 'Ane', '$2y$10$DIhe8lHWa7I.KoSdAeOw/OjVjW9iY.kJfJJ846DCVUR77bxRqNU02', 'employee'),
(23, 'LWA', '$2y$10$6FXeqmBGThOIKObsMjXjfe/yu/TBm96jypO5vRzXJbUYVFAHQXIkq', 'customer'),
(24, 'THOKAZI', '$2y$10$usiEVLksmU.BoLGJxtNioO5zdCj5iK1lBiCKZnv9Uoz/HbxUU70WK', 'employee'),
(25, 'MR FUNN', 'funn', 'ceo'),
(26, 'LWAKHILE', 'lwah', 'ceo'),
(27, 'MaMntimande', '79510192', 'supervisor');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`CustomerID`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`EmployeeID`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`InvoiceID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `CustomerID` (`CustomerID`),
  ADD KEY `EmployeeID` (`EmployeeID`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ProductCode`);

--
-- Indexes for table `purchase`
--
ALTER TABLE `purchase`
  ADD PRIMARY KEY (`PurchaseID`),
  ADD KEY `OrderID` (`OrderID`),
  ADD KEY `ProductCode` (`ProductCode`);

--
-- Indexes for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD PRIMARY KEY (`RequestID`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`SupplierID`);

--
-- Indexes for table `supplier_orders`
--
ALTER TABLE `supplier_orders`
  ADD PRIMARY KEY (`OrderID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `EmployeeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `InvoiceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1052;

--
-- AUTO_INCREMENT for table `purchase`
--
ALTER TABLE `purchase`
  MODIFY `PurchaseID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  MODIFY `RequestID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_orders`
--
ALTER TABLE `supplier_orders`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomerID`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`EmployeeID`) REFERENCES `employees` (`EmployeeID`);

--
-- Constraints for table `purchase`
--
ALTER TABLE `purchase`
  ADD CONSTRAINT `purchase_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`),
  ADD CONSTRAINT `purchase_ibfk_2` FOREIGN KEY (`ProductCode`) REFERENCES `products` (`ProductCode`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
