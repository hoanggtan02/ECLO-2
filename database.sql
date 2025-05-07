-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 07, 2025 lúc 05:42 AM
-- Phiên bản máy phục vụ: 10.4.22-MariaDB
-- Phiên bản PHP: 8.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `eclo2`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `type` int(11) DEFAULT NULL,
  `name` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` varchar(3000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `gender` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` datetime NOT NULL,
  `status` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'A',
  `permission` int(11) DEFAULT NULL,
  `deleted` int(11) DEFAULT 0,
  `forget` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login_data` varchar(3000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `online` int(11) NOT NULL DEFAULT 0,
  `root` int(11) NOT NULL DEFAULT 0,
  `lang` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'vi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `accounts`
--

INSERT INTO `accounts` (`id`, `type`, `name`, `account`, `phone`, `email`, `password`, `content`, `active`, `avatar`, `birthday`, `gender`, `date`, `status`, `permission`, `deleted`, `forget`, `login`, `login_data`, `online`, `root`, `lang`) VALUES
(1, 1, 'Jatbi', 'jatbirat', '0939330014', 'jatbirat@gmail.com', '$2y$10$P8j3uxx8IKITE993FlCcAOb1qxOODrbKWLdQ08k72i1PW6TLL.nMe', NULL, '56b938fe-6b8b-4ba9-9f89-1e02fb591198', 'upload/images/0fcb023e-457d-4579-a339-f8736a173c3b', '2025-03-04', '2', '2025-03-04 21:50:54', 'A', 1, 0, NULL, 'create', NULL, 0, 0, 'vi'),
(2, 1, 'Demo', 'demo', '', 'demo@eclo.vn', '$2y$10$6ivk7P.HOPDAzXDT27ELQexggVH4JFgQB3pNbbadu6infn0BmT/QO', NULL, '11df4d57-5768-4f94-9fbf-d0b3b33f4fd3', 'upload/images/663d6a4b-fe02-4511-8f01-fe201ca9b9f8', '0000-00-00', '1', '2025-03-09 16:11:14', 'A', 1, 0, NULL, 'create', NULL, 0, 0, 'vi');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `accounts_login`
--

CREATE TABLE `accounts_login` (
  `id` int(11) NOT NULL,
  `accounts` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(600) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notification` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `agent` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted` int(11) NOT NULL DEFAULT 0,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `accounts_login`
--

INSERT INTO `accounts_login` (`id`, `accounts`, `token`, `notification`, `ip`, `agent`, `deleted`, `date`) VALUES
(1, '2', '2mV3q0rQXwRxjgrmlJnCXRpQQ1wHRv1T5tYzKNT3hElEmat3IhKpbsS3NcxIB1KhsEs6qPk0Xc6oQxTwtiO638olArrslzuj6724fdrdRsoYqXbWpFeelSssvxiITbgZh4Q2rBUJjqmDa0aQIcYI1rSbXAXijLm3YFLvEXbSOBoLFdbvx8zWygpieSHzQiOjBOmL5dUi0zYDFhBJNqejPzOdXhcLuhgeIxRJR2YrDzyU8AuVggtrGI34kwJ7bYeU', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 0, '2025-05-07 05:00:21');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `accounts_notification`
--

CREATE TABLE `accounts_notification` (
  `id` int(11) NOT NULL,
  `account` int(11) NOT NULL,
  `endpoint` text NOT NULL,
  `p256dh` text NOT NULL,
  `auth` text NOT NULL,
  `agent` text NOT NULL,
  `deleted` int(11) NOT NULL DEFAULT 0,
  `date` datetime NOT NULL,
  `active` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `accounts_times`
--

CREATE TABLE `accounts_times` (
  `id` int(11) NOT NULL,
  `account` int(11) NOT NULL,
  `fd` int(11) NOT NULL DEFAULT 0,
  `domain` varchar(300) DEFAULT NULL,
  `date` datetime NOT NULL,
  `time` double NOT NULL DEFAULT 0,
  `deleted` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `blockip`
--

CREATE TABLE `blockip` (
  `id` int(11) NOT NULL,
  `ip` varchar(300) NOT NULL,
  `status` varchar(1) NOT NULL,
  `date` datetime NOT NULL,
  `active` varchar(300) NOT NULL,
  `deleted` int(11) NOT NULL DEFAULT 0,
  `notes` varchar(3000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customer`
--

CREATE TABLE `customer` (
  `id` int(11) NOT NULL,
  `id_customer` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `fax` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `website` varchar(255) NOT NULL,
  `birthday` date NOT NULL,
  `address` varchar(255) NOT NULL,
  `note` varchar(255) NOT NULL,
  `district` varchar(255) NOT NULL,
  `tax` varchar(255) NOT NULL,
  `ward` varchar(255) NOT NULL,
  `province` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `customer`
--

INSERT INTO `customer` (`id`, `id_customer`, `name`, `email`, `fax`, `phone`, `website`, `birthday`, `address`, `note`, `district`, `tax`, `ward`, `province`) VALUES
(1, 'CUSTA1B2', 'John', 'john@example.com', '0123456789', 'asdasd0912345678', 'http://example.com', '1990-01-01', '123 Main St', 'Ghi chú 1', 'District 1', '123456789', 'Ward 1', 'Province A'),
(2, 'CUSTX9F3', 'Jane', 'jane@example.com', '0123456790', '0987654321', 'http://example.org', '1988-05-12', '456 Side St', 'Ghi chú 2', 'District 2', '987654321', 'Ward 2', 'Province B'),
(3, 'CUSTL7D5', 'Peter', 'peter@example.com', '0123000001', '0933333333', 'http://peter.com', '1992-03-15', '789 Another St', 'Ghi chú 3', 'District 3', '112233445', 'Ward 3', 'Province C'),
(4, 'CUSTB8K1', 'Lucy', 'lucy@example.com', '0123000002', '0900000000', 'http://lucy.org', '1995-07-07', '12 Lake Rd', 'Ghi chú 4', 'District 4', '556677889', 'Ward 4', 'Province D'),
(5, 'CUSTT2Z9', 'Anna', 'anna@example.com', '0123000003', '0966666666', 'http://anna.net', '1991-11-23', '98 Hill St', 'Ghi chú 5', 'District 5', '101010101', 'Ward 5', 'Province E'),
(6, 'CUSTQ4N7', 'Mark', 'mark@example.com', '0123000004', '0977777777', 'http://mark.biz', '1989-09-09', '55 Oak St', 'Ghi chú 6', 'District 6', '202020202', 'Ward 6', 'Province F'),
(7, 'CUSTY6X3', 'Sara', 'sara@example.com', '0123000005', '0955555555', 'http://sara.info', '1993-06-18', '33 Pine St', 'Ghi chú 7', 'District 7', '303030303', 'Ward 7', 'Province G'),
(8, 'CUSTP0A4', 'Tom', 'tom@example.com', '0123000006', '0944444444', 'http://tom.dev', '1987-12-31', '77 River Rd', 'Ghi chú 8', 'District 8', '404040404', 'Ward 8', 'Province H'),
(9, 'CUSTW8E6', 'Mia', 'mia@example.com', '0123000007', '0932323232', 'http://mia.co', '1994-04-04', '21 Sunset Blvd', 'Ghi chú 9', 'District 9', '505050505', 'Ward 9', 'Province I'),
(10, 'CUSTM1R8', 'Leo', 'leo@example.com', '0123000008', '0921212121', 'http://leo.io', '1996-08-20', '19 Sunrise Ave', 'Ghi chú 10', 'District 10', '606060606', 'Ward 10', 'Province J');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user` int(11) DEFAULT NULL,
  `dispatch` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `url` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browsers` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted` int(11) DEFAULT 0,
  `active` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `logs`
--

INSERT INTO `logs` (`id`, `user`, `dispatch`, `action`, `content`, `date`, `url`, `ip`, `browsers`, `deleted`, `active`) VALUES
(1, 2, 'accounts', 'login', '{\"ip\":\"::1\",\"id\":\"11df4d57-5768-4f94-9fbf-d0b3b33f4fd3\",\"email\":\"demo@eclo.vn\",\"token\":\"2mV3q0rQXwRxjgrmlJnCXRpQQ1wHRv1T5tYzKNT3hElEmat3IhKpbsS3NcxIB1KhsEs6qPk0Xc6oQxTwtiO638olArrslzuj6724fdrdRsoYqXbWpFeelSssvxiITbgZh4Q2rBUJjqmDa0aQIcYI1rSbXAXijLm3YFLvEXbSOBoLFdbvx8zWygpieSHzQiOjBOmL5dUi0zYDFhBJNqejPzOdXhcLuhgeIxRJR2YrDzyU8AuVggtrGI34kwJ7bYeU\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.0.0 Safari\\/537.36\",\"did\":\"fd628da4-071f-4457-9207-4fe36ed7b7a7\"}', '2025-05-06 22:00:21', 'http://localhost/login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 0, 'ce40913c-4a36-4a99-9a16-814bcceaeff7'),
(2, 2, 'permission', 'permission-edit', '{\"name\":\"Qu\\u1ea3n tr\\u1ecb vi\\u00ean\",\"status\":\"A\",\"permissions\":\"{\\\"accounts\\\":\\\"accounts\\\",\\\"accounts.add\\\":\\\"accounts.add\\\",\\\"accounts.edit\\\":\\\"accounts.edit\\\",\\\"accounts.deleted\\\":\\\"accounts.deleted\\\",\\\"permission\\\":\\\"permission\\\",\\\"permission.add\\\":\\\"permission.add\\\",\\\"permission.edit\\\":\\\"permission.edit\\\",\\\"permission.deleted\\\":\\\"permission.deleted\\\",\\\"customer\\\":\\\"customer\\\",\\\"customer.add\\\":\\\"customer.add\\\",\\\"customer.edit\\\":\\\"customer.edit\\\",\\\"customer.deleted\\\":\\\"customer.deleted\\\",\\\"blockip\\\":\\\"blockip\\\",\\\"blockip.add\\\":\\\"blockip.add\\\",\\\"blockip.edit\\\":\\\"blockip.edit\\\",\\\"blockip.deleted\\\":\\\"blockip.deleted\\\",\\\"config\\\":\\\"config\\\",\\\"logs\\\":\\\"logs\\\",\\\"trash\\\":\\\"trash\\\"}\"}', '2025-05-06 22:07:09', 'http://localhost/users/permission-edit/48a81d0b-c0e8-4166-8cb3-e6fb007118f6', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 0, '75d40893-8493-496f-8e2c-0aabd95f364b');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user` int(11) DEFAULT NULL,
  `account` int(11) DEFAULT NULL,
  `date` datetime NOT NULL,
  `title` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` varchar(3000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted` int(11) NOT NULL DEFAULT 0,
  `views` int(11) NOT NULL DEFAULT 0,
  `active` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fcm` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logs` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT 'content',
  `data` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(300) NOT NULL,
  `permissions` text DEFAULT NULL,
  `status` varchar(1) NOT NULL,
  `active` varchar(300) NOT NULL,
  `deleted` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Đang đổ dữ liệu cho bảng `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `permissions`, `status`, `active`, `deleted`) VALUES
(1, 'Quản trị viên', '{\"accounts\":\"accounts\",\"accounts.add\":\"accounts.add\",\"accounts.edit\":\"accounts.edit\",\"accounts.deleted\":\"accounts.deleted\",\"permission\":\"permission\",\"permission.add\":\"permission.add\",\"permission.edit\":\"permission.edit\",\"permission.deleted\":\"permission.deleted\",\"customer\":\"customer\",\"customer.add\":\"customer.add\",\"customer.edit\":\"customer.edit\",\"customer.deleted\":\"customer.deleted\",\"blockip\":\"blockip\",\"blockip.add\":\"blockip.add\",\"blockip.edit\":\"blockip.edit\",\"blockip.deleted\":\"blockip.deleted\",\"config\":\"config\",\"logs\":\"logs\",\"trash\":\"trash\"}', 'A', '48a81d0b-c0e8-4166-8cb3-e6fb007118f6', 0),
(2, 'ok', '{\"accounts\":\"accounts\",\"accounts.add\":\"accounts.add\",\"accounts.edit\":\"accounts.edit\",\"accounts.deleted\":\"accounts.deleted\",\"permission\":\"permission\",\"permission.add\":\"permission.add\",\"permission.edit\":\"permission.edit\",\"permission.deleted\":\"permission.deleted\",\"blockip\":\"blockip\",\"blockip.add\":\"blockip.add\",\"blockip.edit\":\"blockip.edit\",\"blockip.deleted\":\"blockip.deleted\",\"config\":\"config\",\"logs\":\"logs\",\"trash\":\"trash\"}', 'A', '7b850871-8496-422e-aff6-ed26cb5c245a', 0),
(3, 'asdasd', '{\"accounts\":\"accounts\",\"accounts.add\":\"accounts.add\",\"accounts.edit\":\"accounts.edit\",\"accounts.deleted\":\"accounts.deleted\",\"permission\":\"permission\",\"permission.add\":\"permission.add\",\"permission.edit\":\"permission.edit\",\"permission.deleted\":\"permission.deleted\",\"blockip\":\"blockip\",\"blockip.add\":\"blockip.add\",\"blockip.edit\":\"blockip.edit\",\"blockip.deleted\":\"blockip.deleted\",\"config\":\"config\",\"logs\":\"logs\",\"trash\":\"trash\"}', 'A', '3e722113-e438-4291-a991-0dca63117c31', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `account` int(11) NOT NULL,
  `notification` int(11) NOT NULL DEFAULT 1,
  `notification_mail` int(11) NOT NULL DEFAULT 0,
  `api` int(11) NOT NULL DEFAULT 0,
  `access_token` text DEFAULT NULL,
  `deleted` int(11) NOT NULL DEFAULT 0,
  `lang` varchar(30) NOT NULL DEFAULT 'vi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Đang đổ dữ liệu cho bảng `settings`
--

INSERT INTO `settings` (`id`, `account`, `notification`, `notification_mail`, `api`, `access_token`, `deleted`, `lang`) VALUES
(1, 1, 1, 0, 1, 'zXrsvGTfYf1wbNpikr8pqONXMtDLqJ0LteNURQDCH4sFuNGIF0ZI4p3YOrNrmwuA7LPBZMyzOfmXqdAULTp86fgG3HbOa90NKmJakEmfTUV2knf00J5JYpyskhTeC9oh', 0, 'vi'),
(2, 2, 1, 0, 0, NULL, 0, 'vi');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `trashs`
--

CREATE TABLE `trashs` (
  `id` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `account` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `data` text NOT NULL,
  `active` varchar(300) NOT NULL,
  `ip` varchar(300) DEFAULT NULL,
  `url` text DEFAULT NULL,
  `router` text NOT NULL,
  `deleted` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `uploads`
--

CREATE TABLE `uploads` (
  `id` int(11) NOT NULL,
  `account` int(11) NOT NULL,
  `type` varchar(300) NOT NULL,
  `content` text NOT NULL,
  `date` datetime NOT NULL,
  `active` varchar(300) NOT NULL,
  `data` text DEFAULT NULL,
  `size` double NOT NULL,
  `mime` varchar(300) DEFAULT NULL,
  `deleted` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Đang đổ dữ liệu cho bảng `uploads`
--

INSERT INTO `uploads` (`id`, `account`, `type`, `content`, `date`, `active`, `data`, `size`, `mime`, `deleted`) VALUES
(1, 1, 'images', 'datas/56b938fe-6b8b-4ba9-9f89-1e02fb591198/images/0fcb023e-457d-4579-a339-f8736a173c3b.png', '2025-03-04 21:50:54', '0fcb023e-457d-4579-a339-f8736a173c3b', '{\"file_src_name\":\"avatar5.png\",\"file_src_name_body\":\"avatar5\",\"file_src_name_ext\":\"png\",\"file_src_pathname\":\"datas\\/avatar\\/avatar5.png\",\"file_src_mime\":\"image\\/png\",\"file_src_size\":336040,\"image_src_x\":512,\"image_src_y\":512,\"image_src_pixels\":262144}', 336040, NULL, 0),
(2, 2, 'images', 'datas/11df4d57-5768-4f94-9fbf-d0b3b33f4fd3/images/663d6a4b-fe02-4511-8f01-fe201ca9b9f8.png', '2025-03-09 16:11:14', '663d6a4b-fe02-4511-8f01-fe201ca9b9f8', '{\"file_src_name\":\"avatar4.png\",\"file_src_name_body\":\"avatar4\",\"file_src_name_ext\":\"png\",\"file_src_pathname\":\"datas\\/avatar\\/avatar4.png\",\"file_src_mime\":\"image\\/png\",\"file_src_size\":336339,\"image_src_x\":512,\"image_src_y\":512,\"image_src_pixels\":262144}', 336339, NULL, 0);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `accounts_login`
--
ALTER TABLE `accounts_login`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `accounts_notification`
--
ALTER TABLE `accounts_notification`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `accounts_times`
--
ALTER TABLE `accounts_times`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `blockip`
--
ALTER TABLE `blockip`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `trashs`
--
ALTER TABLE `trashs`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `uploads`
--
ALTER TABLE `uploads`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `accounts_login`
--
ALTER TABLE `accounts_login`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `accounts_notification`
--
ALTER TABLE `accounts_notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `accounts_times`
--
ALTER TABLE `accounts_times`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `blockip`
--
ALTER TABLE `blockip`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `customer`
--
ALTER TABLE `customer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `trashs`
--
ALTER TABLE `trashs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `uploads`
--
ALTER TABLE `uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
