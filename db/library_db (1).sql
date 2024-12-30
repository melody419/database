-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2024-12-28 14:06:08
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `library_db`
--

-- --------------------------------------------------------

--
-- 資料表結構 `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `account` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `book_isbn` char(13) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `book`
--

CREATE TABLE `book` (
  `isbn` char(13) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `title` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `author` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `category` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `copies` int(10) UNSIGNED NOT NULL,
  `content` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `borrowedbooks`
--

CREATE TABLE `borrowedbooks` (
  `request_id` int(11) NOT NULL,
  `member` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `book_isbn` varchar(13) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `time` timestamp NOT NULL DEFAULT (current_timestamp() + interval 7 day),
  `state` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `librarian`
--

CREATE TABLE `librarian` (
  `account` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `password` varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `email` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- 資料表結構 `member`
--

CREATE TABLE `member` (
  `account` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `password` char(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `name` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `email` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `balance` int(4) NOT NULL DEFAULT 3
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- 資料表結構 `wishlist`
--

CREATE TABLE `wishlist` (
  `request_id` int(11) NOT NULL,
  `member` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `book_isbn` varchar(13) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `book_log` (`book_isbn`),
  ADD KEY `member_log` (`account`);

--
-- 資料表索引 `book`
--
ALTER TABLE `book`
  ADD PRIMARY KEY (`isbn`);

--
-- 資料表索引 `borrowedbooks`
--
ALTER TABLE `borrowedbooks`
  ADD PRIMARY KEY (`request_id`),
  ADD UNIQUE KEY `member` (`member`,`book_isbn`),
  ADD KEY `book` (`book_isbn`);

--
-- 資料表索引 `librarian`
--
ALTER TABLE `librarian`
  ADD PRIMARY KEY (`account`),
  ADD UNIQUE KEY `email` (`email`);

--
-- 資料表索引 `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`account`),
  ADD UNIQUE KEY `email` (`email`);

--
-- 資料表索引 `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`request_id`),
  ADD UNIQUE KEY `member` (`member`,`book_isbn`),
  ADD KEY `book_wish` (`book_isbn`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `borrowedbooks`
--
ALTER TABLE `borrowedbooks`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `book_log` FOREIGN KEY (`book_isbn`) REFERENCES `book` (`isbn`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `member_log` FOREIGN KEY (`account`) REFERENCES `member` (`account`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- 資料表的限制式 `borrowedbooks`
--
ALTER TABLE `borrowedbooks`
  ADD CONSTRAINT `book` FOREIGN KEY (`book_isbn`) REFERENCES `book` (`isbn`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `username` FOREIGN KEY (`member`) REFERENCES `member` (`account`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `librarian`
--
ALTER TABLE `librarian`
  ADD CONSTRAINT `member_lib` FOREIGN KEY (`account`) REFERENCES `member` (`account`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `book_wish` FOREIGN KEY (`book_isbn`) REFERENCES `book` (`isbn`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `namee` FOREIGN KEY (`member`) REFERENCES `member` (`account`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
