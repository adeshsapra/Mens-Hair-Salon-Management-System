-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 14, 2024 at 11:50 AM
-- Server version: 10.4.21-MariaDB
-- PHP Version: 8.0.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `classycut`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `admin_email` varchar(255) NOT NULL,
  `admin_password` varchar(255) NOT NULL,
  `admin_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_email`, `admin_password`, `admin_name`) VALUES
(10, 'akshay007@gmail.com', '1234', 'akshay');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `a_id` int(11) NOT NULL,
  `a_name` varchar(50) NOT NULL,
  `a_email` varchar(50) NOT NULL,
  `a_no` int(10) NOT NULL,
  `a_date` date NOT NULL,
  `a_time` time NOT NULL,
  `a_category` varchar(50) NOT NULL,
  `a_type` varchar(50) NOT NULL,
  `a_status` varchar(100) NOT NULL,
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`a_id`, `a_name`, `a_email`, `a_no`, `a_date`, `a_time`, `a_category`, `a_type`, `a_status`, `id`) VALUES
(2, 'akki07', 'abhi@gmail.com', 1234567890, '2022-11-11', '02:22:00', 'beard', 'fadebeard', 'Accepted', 6),
(4, 'adesh', 'akshay@gmail.com', 1234567890, '2024-11-11', '02:22:00', 'spa', 'mud_wrap', 'Pending', 5),
(5, 'akki07', 'abhi@gmail.com', 1234567890, '2024-11-11', '11:11:00', 'beard', 'long_beard', 'Pending', 6),
(8, 'prince', 'prince@gmail.com', 2147483647, '2024-12-10', '12:00:00', 'spa', 'fullbody', 'Accepted', 13);

-- --------------------------------------------------------

--
-- Table structure for table `appointment_history`
--

CREATE TABLE `appointment_history` (
  `ah_id` int(11) NOT NULL,
  `a_id` int(11) DEFAULT NULL,
  `ah_name` varchar(50) NOT NULL,
  `ah_email` varchar(50) NOT NULL,
  `ah_no` int(10) NOT NULL,
  `ah_date` date NOT NULL,
  `ah_time` time NOT NULL,
  `ah_category` varchar(50) NOT NULL,
  `ah_type` varchar(50) NOT NULL,
  `ah_status` varchar(100) NOT NULL,
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `appointment_history`
--

INSERT INTO `appointment_history` (`ah_id`, `a_id`, `ah_name`, `ah_email`, `ah_no`, `ah_date`, `ah_time`, `ah_category`, `ah_type`, `ah_status`, `id`) VALUES
(2, 2, 'akki07', 'abhi@gmail.com', 1234567890, '2022-11-11', '02:22:00', 'beard', 'fadebeard', 'Accepted', 6),
(4, 4, 'adesh', 'akshay@gmail.com', 1234567890, '2024-11-11', '02:22:00', 'spa', 'mud_wrap', 'Pending', 5),
(5, 5, 'akki07', 'abhi@gmail.com', 1234567890, '2024-11-11', '11:11:00', 'beard', 'long_beard', 'Pending', 6),
(8, 8, 'prince', 'prince@gmail.com', 2147483647, '2024-12-10', '12:00:00', 'spa', 'fullbody', 'Accepted', 13);

-- --------------------------------------------------------

--
-- Table structure for table `beard_service`
--

CREATE TABLE `beard_service` (
  `beard_id` int(100) NOT NULL,
  `beard_service` varchar(255) NOT NULL,
  `beard_price` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `beard_service`
--

INSERT INTO `beard_service` (`beard_id`, `beard_service`, `beard_price`) VALUES
(7, 'Short Beard', 400),
(8, 'Trimmed Beard', 400),
(9, 'Fade Beard', 100),
(10, 'Anchor Beard', 400),
(11, 'Thick Bushy Beard', 600),
(12, 'Gotee Beard', 350);

-- --------------------------------------------------------

--
-- Table structure for table `classic_membership`
--

CREATE TABLE `classic_membership` (
  `classic_id` int(100) NOT NULL,
  `classic_plan` varchar(100) NOT NULL,
  `classic_desc` varchar(255) NOT NULL,
  `classic_price` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `classic_membership`
--

INSERT INTO `classic_membership` (`classic_id`, `classic_plan`, `classic_desc`, `classic_price`) VALUES
(1, 'yearly', '30% off On Spa services', 0),
(2, 'yearly', 'Unlimited Beards & Skin Services', 0),
(3, 'yearly', '1 complimentary Hair Style per month', 0),
(4, 'yearly', '1 complimentary Child HairCut Per Month', 0),
(5, 'yearly', 'Priority booking Preferred Stylists', 0),
(19, 'yearly', 'Free Product Samples', 7999),
(20, 'monthly', '30% off On Spa services', 0),
(21, 'monthly', '1 complimentary Hair Style per month', 0),
(22, 'monthly', '1 complimentary Child HairCut Per Month', 0),
(23, 'monthly', 'Priority booking Preferred Stylists', 0),
(24, 'monthly', 'Free Product Samples', 699);

-- --------------------------------------------------------

--
-- Table structure for table `contact_details`
--

CREATE TABLE `contact_details` (
  `c_id` int(11) NOT NULL,
  `id` int(11) DEFAULT NULL,
  `c_name` varchar(255) DEFAULT NULL,
  `c_email` varchar(255) DEFAULT NULL,
  `c_phone` varchar(10) DEFAULT NULL,
  `c_message` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `contact_details`
--

INSERT INTO `contact_details` (`c_id`, `id`, `c_name`, `c_email`, `c_phone`, `c_message`) VALUES
(1, NULL, 'adesh', 'classycut007@gmail.com', '7575852866', 'hiii, my name is akshay. your service is mind-blowing..!!'),
(2, NULL, 'adesh', 'classycut007@gmail.com', '7575852866', 'hiii, my name is akshay. your service is mind-blowing..!!'),
(3, NULL, 'adesh', 'classycut007@gmail.com', '7575852866', 'hiii, my name is akshay. your service is mind-blowing..!!');

-- --------------------------------------------------------

--
-- Table structure for table `haircut_service`
--

CREATE TABLE `haircut_service` (
  `hair_id` int(100) NOT NULL,
  `hair_category` varchar(255) NOT NULL,
  `hair_service` varchar(255) NOT NULL,
  `hair_price` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `haircut_service`
--

INSERT INTO `haircut_service` (`hair_id`, `hair_category`, `hair_service`, `hair_price`) VALUES
(12, 'hairstyle', 'Buzz Cut', 250),
(14, 'hairstyle', 'French Cut', 350),
(15, 'hairstyle', 'Crew Cut', 200),
(16, 'hairstyle', 'Mohawak Cut', 500),
(17, 'hairdesign', 'Hair Crop with Wash', 350),
(18, 'hairdesign', 'Hair Color', 500),
(19, 'hairdesign', 'Hair Crop Prince (Up to 10 Yrs)', 250),
(20, 'hairdesign', 'Smooth Hair Shower', 150);

-- --------------------------------------------------------

--
-- Table structure for table `membership_payments`
--

CREATE TABLE `membership_payments` (
  `m_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `membership_type` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `card_name` varchar(100) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `membership_payments`
--

INSERT INTO `membership_payments` (`m_id`, `id`, `membership_type`, `price`, `card_name`, `phone_number`, `payment_date`, `status`) VALUES
(2, 6, 'Yearly Royal Pass', '11999.00', 'akshay', '1234567890', '2024-10-09 19:26:00', 'active'),
(4, 6, 'Yearly Royal Pass', '11999.00', 'akshay', '1234567890', '2024-10-09 19:28:39', 'active'),
(5, 6, 'Monthly Classic Pass', '699.00', 'akshay', '1234567890', '2024-10-09 19:31:00', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `pay_id` int(11) NOT NULL,
  `id` int(11) DEFAULT NULL,
  `s_id` int(11) DEFAULT NULL,
  `p_name` varchar(50) NOT NULL,
  `p_phno` int(15) NOT NULL,
  `p_address` varchar(500) NOT NULL,
  `p_city` varchar(255) NOT NULL,
  `p_state` varchar(255) NOT NULL,
  `p_pincode` int(6) NOT NULL,
  `p_method` varchar(50) NOT NULL,
  `p_date` date NOT NULL,
  `p_time` time NOT NULL,
  `p_status` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`pay_id`, `id`, `s_id`, `p_name`, `p_phno`, `p_address`, `p_city`, `p_state`, `p_pincode`, `p_method`, `p_date`, `p_time`, `p_status`) VALUES
(68, 6, 115, 'akshay', 2147483647, 'x', 'savarkundla', 'gujrat', 364515, 'Cash On Delivery', '2024-10-14', '09:19:51', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `p_id` int(100) NOT NULL,
  `p_name` varchar(255) NOT NULL,
  `p_desc` varchar(255) NOT NULL,
  `p_price` int(100) NOT NULL,
  `p_size` varchar(100) NOT NULL,
  `p_overview` varchar(500) DEFAULT NULL,
  `p_f1` varchar(100) NOT NULL,
  `p_f2` varchar(100) NOT NULL,
  `p_ingred` varchar(100) NOT NULL,
  `p_img` varchar(255) NOT NULL,
  `p_quantity` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`p_id`, `p_name`, `p_desc`, `p_price`, `p_size`, `p_overview`, `p_f1`, `p_f2`, `p_ingred`, `p_img`, `p_quantity`) VALUES
(18, 'Hair Powder', 'ClassyCuts volumizing powder wax adds instant lift and texture with a lightweight, natural feel.', 349, '100ml', 'ClassyCut Hair Volumizing Powder is designed to create instant volume and texture. Infused with silica and rice starch, it lifts and adds body while absorbing excess oil. This powder helps you achieve fuller, more voluminous hair effortlessly.', 'Instant root lift and volume boost.', '          Matte finish with a long-lasting hold.', 'Adds texture and volume to hair.Strengthens and supports hair structure.', 'hairpowder.jpg', 0),
(19, 'Hair Oil', 'ClassyCuts Hair Oil nourishes and protects your hair with a luxurious, silky smooth finish.', 299, '100ml', 'ClassyCut Hair Oil is a luxurious blend designed to nourish and enhance your hair. With argan oil and jojoba oil, it provides deep moisture and shine, while hyaluronic acid tackles frizz.', 'Nourishes and hydrates for silky, smooth hair.', '    Adds shine while reducing frizz and split ends.', 'Argan Oil: Nourishes and adds shine to hair.\r\nHyaluronic Acid: Hydrates and helps manage frizz.', 'hairoil.jpg', 2),
(20, 'Hair Spray', 'ClassyCuts Strong Hold Hair Spray, a fast-drying, non-sticky formula that keeps your look in place all day.', 499, '100ml', 'ClassyCut Hair Spray delivers a flexible, long-lasting hold while enhancing shine and reducing frizz. Infused with panthenol and hyaluronic acid, it strengthens and moisturizes your hair. Aloe vera adds a soothing touch.', 'Strong hold with instant volume and shine.', '  Humidity-resistant with long-lasting control.', 'Panthenol (Vitamin B5): Strengthens and adds shine.\r\nHyaluronic Acid: Provides moisture and reduces ', 'homespray.jpg', 5),
(21, 'Hair Wax', 'ClassyCuts provides hair wax delivers a strong, lexible hold with, matte texture for all-day style.', 699, '50g', 'ClassyCut Hair Wax delivers a strong, flexible hold with a natural matte finish. \r\n', 'Strong hold with flexible styling.', '  Matte finish for a natural look.', 'Coconut Oil: Moisturizes and adds a subtle shine.\r\nShea Butter: Nourishes and softens hair.', 'wax.jpg', 5),
(22, 'Hair Conditioner', 'classycuts hair conditioner is smooths, detangles and leaving it soft and shiny.', 199, '100ml', 'The ClassyCut Hair Conditioner is crafted to nourish and enhance your hair. With moisturizing argan oil and hydrating hyaluronic acid, it provides deep hydration and combats frizz. this conditioner leaves your hair soft, smooth, and revitalized.', 'Deeply hydrates and detangles for smooth, manageable hair.', '  Enhances shine and strengthens strands with every use.', 'Argan Oil: Moisturizes and smooths hair.\r\nHyaluronic Acid: Provides deep hydration and reduces frizz', 'conditioner.jpg', 5),
(23, 'Hair Shampoo', 'ClassyCuts shampoo deeply cleanses and hydrates for soft, healthy, and manageable hair.', 399, '100ml', 'The ClassyCut Vitamin C Hair Shampoo is designed to revitalize and strengthen your hair from root to tip. Enriched with Vitamin C and biotin, it promotes healthy hair growth, while aloe vera and argan oil deeply moisturize and add a luxurious shine.', 'Cleanses and nourishes for healthy, balanced hair.', '  Gentle formula enhances shine and reduces frizz.', 'Vitamin C: Promotes healthy scalp and strengthens hair.\r\nAloe Vera: Moisturizes and soothes the scal', 'shampoo.png', 5),
(24, 'Hair Serum', 'ClassyCuts Hair Serum  a lightweight, shine, and protects your hair from heat and damage.', 499, '50ml', 'ClassyCut Hair Serum is a lightweight formula designed to enhance shine and smoothness. With nourishing argan oil and hydrating hyaluronic acid, it provides deep moisture while reducing frizz. ', 'Nourishes and smooths for sleek, frizz-free hair.', '  Adds shine and reduces split ends for a healthy look.', 'Argan Oil: Nourishes and adds shine.\r\nHyaluronic Acid: Hydrates and smooths.\r\nKeratin: Strengthens a', 'serum.jpg', 5),
(25, 'Hair gel', 'ClassyCuts provides hair gel offers firm control and a smooth, residue-free shine for any style.', 249, '50g', 'ClassyCut Hair Gel is designed to give your hair a strong, long-lasting hold with a natural finish. Enriched with aloe vera and glycerin, it hydrates and adds shine while hydrolyzed proteins strengthen and protect. ', 'Provides strong hold and defines styles without flaking.', '  Adds shine and controls frizz for a polished finish.', 'Aloe Vera: Soothes and hydrates the scalp.\r\nGlycerin: Provides moisture and adds shine.', 'hairjel.jpg', 5),
(26, 'Face Wash', 'ClassyCuts Face Wash gently cleanses and balances your skin, removing impurities for a refreshed and glow.', 499, '100ml', 'ClassyCut Face Wash is formulated to cleanse and refresh your skin while addressing acne and dullness. With salicylic acid for exfoliation, hyaluronic acid for hydration, and green tea extract for soothing, it cleanses deeply and enhances your complexion.', 'Gently cleanses and removes impurities for fresh, clear skin.', '  Balances and refreshes with a soothing, hydrating formula.', 'Salicylic Acid: Exfoliates and helps prevent acne.\r\nHyaluronic Acid: Hydrates and maintains moisture', 'facewash.jpg', 5),
(27, 'Face Cream', 'ClassyCuts hydrating face cream deeply moisturizes and rejuvenates skin for a radiant, youthful glow.', 199, '100ml', 'ClassyCut Face Cream is a rich, hydrating formula designed to nourish and rejuvenate your skin. With hyaluronic acid for deep hydration, vitamin E for protection, and niacinamide for brightening, it helps to even out skin tone and reduce the appearance of pores.', 'Moisturizes and nourishes for soft, radiant skin.', '  Reduces fine lines and improves texture with daily use.', 'Hyaluronic Acid: Deeply hydrates and plumps the skin.\r\nVitamin E: Nourishes and protects against env', 'facecream.jpg', 5),
(28, 'Beard Oil', 'ClassyCuts beard oil conditions and softens for a well-groomed, smooth beard with a subtle shine.', 499, '100ml', 'ClassyCut Beard Oil is designed to hydrate and condition your beard while soothing the skin underneath. With argan and jojoba oils for deep moisture and shine, it keeps your beard soft and manageable. Vitamin E provides essential nutrients and protectio.', 'Nourishes and hydrates for a softer, more manageable beard.', '  Reduces itchiness and adds a natural shine.', 'Argan Oil: Moisturizes and softens beard hair.\r\nJojoba Oil: Conditions and promotes a healthy shine.', 'beardoil2.jpg', 5),
(29, 'Beard Cream', 'ClassyCuts beard cream tames and hydrates your beard, ensuring a smooth, polished look with every use.', 799, '100g', 'ClassyCut Beard Cream is formulated to condition and tame your beard. With jojoba oil and shea butter for deep moisture and softness, it enhances manageability and reduces dryness. ', 'Moisturizes and softens beard for a smoother feel.', '  Tames unruly hair and adds a subtle shine.', 'Jojoba Oil: Moisturizes and softens beard hair.\r\nShea Butter: Provides nourishment and improves mana', 'beardcream.jpg', 5),
(30, 'Golden Face Mask', 'ClassyCuts Gold Mask delivers a golden touch of luxury, illuminating your skin for a radiant glow.', 1999, '50g', 'The ClassyCut Gold Face Mask is a luxurious treatment designed to rejuvenate and brighten the skin. Infused with 24K gold and collagen, it helps to reduce fine lines, improve skin firmness, and deliver a glowing complexion.', 'Revitalizes skin with a radiant glow.', '  Nourishes and hydrates for a youthful appearance.', '24K Gold: Enhances skin radiance and elasticity.\r\nCollagen: Firms and smooths the skin.', 'goldmask.jpg', 5),
(31, 'Silver Face Mask', 'ClassyCuts Silver Mask revitalizes your skin with a premium silver formula for a luminous, sophisticated glow.', 1499, '50g', 'The ClassyCut Silver Face Mask is crafted to detoxify and clarify the skin. With the purifying power of colloidal silver and activated charcoal, it effectively removes impurities, reduces excess oil, and prevents blemishes.', 'Revitalizes and brightens with a radiant silver glow.', '  Hydrates and smooths skin for a refreshed, youthful appearance.', 'Colloidal Silver: Helps purify and balance the skin.\r\nActivated Charcoal: Draws out impurities and t', 'silvermask.jpg', 5),
(32, 'Charcol Face Mask', 'ClassyCuts Charcoal Facial Mask detoxifies and purifies for a clear and refreshed complexion', 999, '50g', 'The ClassyCut Charcoal Face Mask is a powerful treatment designed to detoxify and purify the skin. With activated charcoal and kaolin clay, it effectively draws out impurities, controls oil, and exfoliates dead skin cells.', 'Deeply cleanses and detoxifies pores.', '  Removes impurities for a clear, matte complexion.', 'Activated Charcoal: Deeply cleanses and detoxifies the skin.\r\nKaolin Clay: Absorbs excess oil and mi', 'charcolmask.jpg', 5),
(33, 'Vitamin-c Face Mask', 'ClassyCuts Vitamin C Face mask brightens and energizes your skin, revealing a radiant and youthful complexion.', 599, '50g', 'The ClassyCut Vitamin C Face Mask is formulated to brighten and revitalize the skin. Packed with potent antioxidants like Vitamin C and turmeric, it targets dark spots, evens skin tone, and provides deep hydration.', 'Brightens skin with a radiant glow.', '  Boosts collagen and fights signs of aging.', 'Vitamin C: Brightens skin and reduces dark spots.\r\nTurmeric Extract: Evens skin tone and fights infl', 'vitaminmask.jpg', 5);

-- --------------------------------------------------------

--
-- Table structure for table `product_cart`
--

CREATE TABLE `product_cart` (
  `c_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `p_id` int(11) NOT NULL,
  `c_img` varchar(100) NOT NULL,
  `c_name` varchar(50) NOT NULL,
  `c_price` int(11) NOT NULL,
  `c_size` varchar(50) DEFAULT NULL,
  `c_quantity` int(11) DEFAULT 1,
  `c_total` int(11) DEFAULT NULL,
  `c_grand_total` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `product_sales`
--

CREATE TABLE `product_sales` (
  `s_id` int(11) NOT NULL,
  `id` int(11) DEFAULT NULL,
  `s_img` varchar(50) NOT NULL,
  `s_name` varchar(50) NOT NULL,
  `s_price` int(10) NOT NULL,
  `s_size` varchar(50) NOT NULL,
  `s_quantity` int(10) NOT NULL,
  `s_total` int(10) NOT NULL,
  `s_grand_total` int(10) NOT NULL,
  `s_date` date NOT NULL,
  `s_status` varchar(100) NOT NULL,
  `s_time` time(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `product_sales`
--

INSERT INTO `product_sales` (`s_id`, `id`, `s_img`, `s_name`, `s_price`, `s_size`, `s_quantity`, `s_total`, `s_grand_total`, `s_date`, `s_status`, `s_time`) VALUES
(115, 6, 'hairpowder.jpg', 'Hair Powder', 349, '100ml', 1, 349, 349, '2024-10-14', 'Order Placed', '09:19:51.000000');

-- --------------------------------------------------------

--
-- Table structure for table `royal_membership`
--

CREATE TABLE `royal_membership` (
  `royal_id` int(100) NOT NULL,
  `royal_plan` varchar(100) NOT NULL,
  `royal_desc` varchar(255) NOT NULL,
  `royal_price` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `royal_membership`
--

INSERT INTO `royal_membership` (`royal_id`, `royal_plan`, `royal_desc`, `royal_price`) VALUES
(1, 'yearly', '50% off On Spa services', 0),
(2, 'yearly', 'Unlimited Hair Styling 2 Times a Month', 0),
(3, 'yearly', 'Unlimited Beards & Skin Services', 0),
(4, 'yearly', '2 complimentary Hair Style per 3 month', 0),
(5, 'yearly', '2 complimentary Child HairCut Per 3 Month', 0),
(22, 'yearly', 'Free Product Gift & Samples', 11999),
(23, 'monthly', '50% off On Spa services', 0),
(24, 'monthly', '2 complimentary Hair Style per month', 0),
(25, 'monthly', '2 complimentary Child HairCut Per Month', 0),
(26, 'monthly', 'Priority booking With Top stylists', 0),
(27, 'monthly', 'Free Product Gift & Samples', 1299);

-- --------------------------------------------------------

--
-- Table structure for table `skin_service`
--

CREATE TABLE `skin_service` (
  `skin_id` int(100) NOT NULL,
  `skin_service` varchar(255) NOT NULL,
  `skin_price` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `skin_service`
--

INSERT INTO `skin_service` (`skin_id`, `skin_service`, `skin_price`) VALUES
(7, 'Mens Facial', 150),
(8, 'Brightening Facial', 350),
(9, 'Hydra Facial', 250),
(10, 'Collagen Facial', 400),
(11, 'Chemical Peel', 300),
(12, 'Charcoal Facial', 500),
(14, 'Oxygen Facial', 600),
(15, 'Laser Skin Resurfacing', 1000);

-- --------------------------------------------------------

--
-- Table structure for table `spa_service`
--

CREATE TABLE `spa_service` (
  `spa_id` int(100) NOT NULL,
  `spa_category` varchar(255) NOT NULL,
  `spa_service` varchar(255) NOT NULL,
  `spa_price` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `spa_service`
--

INSERT INTO `spa_service` (`spa_id`, `spa_category`, `spa_service`, `spa_price`) VALUES
(6, 'bodytreatment', 'Body Scrub', 400),
(7, 'bodytreatment', 'Hydrating Body Treatment', 600),
(8, 'bodytreatment', 'Detoxifying Mud Wrap', 700),
(9, 'bodytreatment', 'Cellulite Treatment', 350),
(10, 'bodytreatment', 'Paraffin Body Treatment', 850),
(11, 'bodymassage', 'Full Body Exfoliation', 2500),
(12, 'bodymassage', 'Full Hand Massage', 1200),
(14, 'bodymassage', 'Massage & Wrap', 3500),
(15, 'bodymassage', 'Hot Stone Massage', 3000),
(16, 'bodymassage', 'Deep Tissue Massage', 2000),
(17, 'bodymassage', 'Ayurvedic Massage', 1500);

-- --------------------------------------------------------

--
-- Table structure for table `standard_membership`
--

CREATE TABLE `standard_membership` (
  `standard_id` int(100) NOT NULL,
  `standard_plan` varchar(100) NOT NULL,
  `standard_desc` varchar(255) NOT NULL,
  `standard_price` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `standard_membership`
--

INSERT INTO `standard_membership` (`standard_id`, `standard_plan`, `standard_desc`, `standard_price`) VALUES
(1, 'yearly', '15% off On Spa services', 0),
(2, 'yearly', '10% off On Hair Styling', 0),
(3, 'yearly', '5% off On Beard services', 0),
(4, 'yearly', '1 complimentary HairCut Per 3 Months', 0),
(5, 'yearly', 'Priority booking', 3999),
(18, 'monthly', '20% off On Spa services', 0),
(19, 'monthly', '10% off On Hair Styling', 0),
(20, 'monthly', '5% off On Beard services', 0),
(21, 'monthly', 'Priority booking', 399);

-- --------------------------------------------------------

--
-- Table structure for table `user_reg`
--

CREATE TABLE `user_reg` (
  `id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(8) NOT NULL,
  `profile_img` varchar(255) DEFAULT 'photos/default.jpeg',
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user_reg`
--

INSERT INTO `user_reg` (`id`, `name`, `email`, `username`, `password`, `profile_img`, `last_login`) VALUES
(5, 'adesh', 'adesh@gmail.com', 'adesh', '123', '../upload_img/Snapchat-1330099180.jpg', '2024-10-06 20:28:51'),
(6, 'akshay', 'akshayhariyani007@gmail.com', 'akki07', '123', 'WhatsApp Image 2024-01-22 at 10.27.29_1eae723a.jpg', '2024-10-14 00:39:00'),
(13, 'prince', 'princedodiya2663@gmail.com', 'prince', '123', '', '2024-10-14 00:02:15'),
(14, 'ujas gediya', 'ujas@gmail.com', 'ujas', '123', '', NULL),
(15, 'parth', 'airwellcompany@gmail.com', 'parth', '1234', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wallet_transactions`
--

CREATE TABLE `wallet_transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `product_id` int(11) DEFAULT NULL,
  `sale_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `wallet_transactions`
--

INSERT INTO `wallet_transactions` (`id`, `user_id`, `amount`, `date`, `product_id`, `sale_id`) VALUES
(1, 6, '699.00', '2024-10-01 16:11:42', 92, NULL),
(2, 6, '349.00', '2024-10-04 18:14:00', 93, NULL),
(3, 5, '249.00', '2024-10-06 14:06:12', 95, NULL),
(4, 13, '299.00', '2024-10-08 16:46:26', 102, NULL),
(5, 6, '299.00', '2024-10-09 09:06:01', 94, NULL),
(6, 6, '499.00', '2024-10-09 09:45:28', 101, NULL),
(7, 6, '499.00', '2024-10-09 10:34:01', 105, NULL),
(8, 6, '299.00', '2024-10-10 08:54:56', 103, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`a_id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `appointment_history`
--
ALTER TABLE `appointment_history`
  ADD PRIMARY KEY (`ah_id`),
  ADD KEY `a_id` (`a_id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `beard_service`
--
ALTER TABLE `beard_service`
  ADD PRIMARY KEY (`beard_id`);

--
-- Indexes for table `classic_membership`
--
ALTER TABLE `classic_membership`
  ADD PRIMARY KEY (`classic_id`);

--
-- Indexes for table `contact_details`
--
ALTER TABLE `contact_details`
  ADD PRIMARY KEY (`c_id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `haircut_service`
--
ALTER TABLE `haircut_service`
  ADD PRIMARY KEY (`hair_id`);

--
-- Indexes for table `membership_payments`
--
ALTER TABLE `membership_payments`
  ADD PRIMARY KEY (`m_id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`pay_id`),
  ADD KEY `s_id` (`s_id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`p_id`);

--
-- Indexes for table `product_cart`
--
ALTER TABLE `product_cart`
  ADD PRIMARY KEY (`c_id`),
  ADD UNIQUE KEY `id` (`id`,`p_id`),
  ADD KEY `p_id` (`p_id`);

--
-- Indexes for table `product_sales`
--
ALTER TABLE `product_sales`
  ADD PRIMARY KEY (`s_id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `royal_membership`
--
ALTER TABLE `royal_membership`
  ADD PRIMARY KEY (`royal_id`);

--
-- Indexes for table `skin_service`
--
ALTER TABLE `skin_service`
  ADD PRIMARY KEY (`skin_id`);

--
-- Indexes for table `spa_service`
--
ALTER TABLE `spa_service`
  ADD PRIMARY KEY (`spa_id`);

--
-- Indexes for table `standard_membership`
--
ALTER TABLE `standard_membership`
  ADD PRIMARY KEY (`standard_id`);

--
-- Indexes for table `user_reg`
--
ALTER TABLE `user_reg`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `sale_id` (`sale_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `a_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `appointment_history`
--
ALTER TABLE `appointment_history`
  MODIFY `ah_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `beard_service`
--
ALTER TABLE `beard_service`
  MODIFY `beard_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `classic_membership`
--
ALTER TABLE `classic_membership`
  MODIFY `classic_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `contact_details`
--
ALTER TABLE `contact_details`
  MODIFY `c_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `haircut_service`
--
ALTER TABLE `haircut_service`
  MODIFY `hair_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `membership_payments`
--
ALTER TABLE `membership_payments`
  MODIFY `m_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `pay_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `p_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `product_cart`
--
ALTER TABLE `product_cart`
  MODIFY `c_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- AUTO_INCREMENT for table `product_sales`
--
ALTER TABLE `product_sales`
  MODIFY `s_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `royal_membership`
--
ALTER TABLE `royal_membership`
  MODIFY `royal_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `skin_service`
--
ALTER TABLE `skin_service`
  MODIFY `skin_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `spa_service`
--
ALTER TABLE `spa_service`
  MODIFY `spa_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `standard_membership`
--
ALTER TABLE `standard_membership`
  MODIFY `standard_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `user_reg`
--
ALTER TABLE `user_reg`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`id`) REFERENCES `user_reg` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `appointment_history`
--
ALTER TABLE `appointment_history`
  ADD CONSTRAINT `appointment_history_ibfk_1` FOREIGN KEY (`a_id`) REFERENCES `appointments` (`a_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `appointment_history_ibfk_2` FOREIGN KEY (`id`) REFERENCES `user_reg` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `contact_details`
--
ALTER TABLE `contact_details`
  ADD CONSTRAINT `contact_details_ibfk_1` FOREIGN KEY (`id`) REFERENCES `user_reg` (`id`);

--
-- Constraints for table `membership_payments`
--
ALTER TABLE `membership_payments`
  ADD CONSTRAINT `membership_payments_ibfk_1` FOREIGN KEY (`id`) REFERENCES `user_reg` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`s_id`) REFERENCES `product_sales` (`s_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`id`) REFERENCES `user_reg` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_cart`
--
ALTER TABLE `product_cart`
  ADD CONSTRAINT `product_cart_ibfk_1` FOREIGN KEY (`id`) REFERENCES `user_reg` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_cart_ibfk_2` FOREIGN KEY (`p_id`) REFERENCES `products` (`p_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_sales`
--
ALTER TABLE `product_sales`
  ADD CONSTRAINT `product_sales_ibfk_1` FOREIGN KEY (`id`) REFERENCES `user_reg` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD CONSTRAINT `wallet_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_reg` (`id`),
  ADD CONSTRAINT `wallet_transactions_ibfk_2` FOREIGN KEY (`sale_id`) REFERENCES `product_sales` (`s_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
