-- phpMyAdmin SQL Dump
-- version 3.4.3.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 01, 2014 at 10:36 PM
-- Server version: 5.6.2
-- PHP Version: 5.4.24

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `db_wallet`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_banned_addresses`
--

CREATE TABLE IF NOT EXISTS `tbl_banned_addresses` (
  `wallet_id` int(11) NOT NULL AUTO_INCREMENT,
  `walletaddress` varchar(100) NOT NULL,
  `note` text NOT NULL,
  PRIMARY KEY (`wallet_id`),
  KEY `walletaddress` (`walletaddress`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_callbacks`
--

CREATE TABLE IF NOT EXISTS `tbl_callbacks` (
  `callback_id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(2000) NOT NULL,
  `url_referrer` varchar(1000) NOT NULL,
  `crypto_code` varchar(6) NOT NULL COMMENT 'btc ltc dgc etc...',
  `value_satoshi` decimal(14,0) NOT NULL COMMENT 'satoshis',
  `address` varchar(100) NOT NULL,
  `input_address` varchar(100) NOT NULL,
  `hash_transaction` varchar(200) NOT NULL,
  `errorcode` text NOT NULL,
  `userid` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `ipaddress` varchar(45) NOT NULL,
  `ipaddress2` varchar(45) NOT NULL,
  `date_added` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`callback_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1530 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_countries`
--

CREATE TABLE IF NOT EXISTS `tbl_countries` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `regionid` tinyint(4) NOT NULL,
  `name` varchar(250) NOT NULL DEFAULT '',
  `shortname` varchar(4) NOT NULL,
  `code` smallint(6) NOT NULL,
  `tp` int(11) NOT NULL,
  `members_count` int(11) NOT NULL,
  `lat` int(11) NOT NULL,
  `lon` int(11) NOT NULL,
  `zoom` tinyint(4) NOT NULL,
  `sortid` tinyint(1) NOT NULL,
  `currencyid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `regionid` (`regionid`),
  KEY `name` (`name`),
  KEY `id` (`id`),
  KEY `regionid_2` (`regionid`),
  KEY `name_2` (`name`),
  KEY `lat` (`lat`),
  KEY `lon` (`lon`),
  KEY `zoom` (`zoom`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=247 ;

--
-- Dumping data for table `tbl_countries`
--

INSERT INTO `tbl_countries` (`id`, `regionid`, `name`, `shortname`, `code`, `tp`, `members_count`, `lat`, `lon`, `zoom`, `sortid`, `currencyid`) VALUES
(1, 6, 'Afghanistan', '', 93, 20, 16, 33, 63, 6, 0, 0),
(2, 0, '&Aring;land Islands', '', 0, 0, 0, 0, 0, 0, 0, 0),
(3, 3, 'Albania', '', 355, 350, 197, 41, 19, 6, 0, 0),
(4, 4, 'Algeria', '', 213, 10, 9, 34, 0, 7, 0, 0),
(5, 0, 'American Samoa', '', 684, 0, 5, -14, -170, 7, 0, 0),
(6, 3, 'Andorra', '', 376, 210, 42, 42, 1, 6, 0, 0),
(7, 5, 'Angola', '', 244, 0, 12, -12, 17, 6, 0, 0),
(8, 0, 'Anguilla', '', 101, 0, 5, 0, 0, 0, 0, 0),
(9, 0, 'Antarctica', '', 0, 0, 0, 0, 0, 0, 0, 0),
(10, 0, 'Antigua and Barbuda', '', 1021, 0, 10, 0, 0, 0, 0, 0),
(11, 2, 'Argentina', '', 54, 480, 435, -34, -67, 5, 0, 0),
(12, 6, 'Armenia', '', 374, 20, 57, 40, 44, 6, 0, 0),
(13, 2, 'Aruba', '', 297, 0, 8, 12, -70, 7, 0, 0),
(14, 7, 'Australia', '', 61, 11625, 8944, -26, 125, 5, 1, 0),
(15, 3, 'Austria', '', 43, 405, 268, 47, 12, 6, 0, 47),
(16, 6, 'Azerbaijan', '', 994, 260, 66, 40, 46, 6, 0, 0),
(17, 2, 'Bahamas', '', 103, 0, 10, 24, -78, 6, 0, 0),
(18, 4, 'Bahrain', '', 973, 125, 258, 26, 50, 10, 0, 0),
(19, 6, 'Bangladesh', '', 880, 800, 357, 24, 89, 6, 0, 0),
(20, 2, 'Barbados', '', 104, 130, 15, 13, -59, 6, 0, 0),
(21, 3, 'Belarus', '', 375, 0, 35, 53, 26, 6, 0, 0),
(22, 3, 'Belgium', '', 32, 680, 635, 50, 3, 6, 0, 47),
(23, 2, 'Belize', '', 501, 0, 10, 17, -88, 6, 0, 0),
(24, 5, 'Benin', '', 229, 40, 26, 7, 0, 6, 0, 0),
(25, 2, 'Bermuda', '', 105, 30, 12, 32, -64, 8, 0, 0),
(26, 7, 'Bhutan', '', 975, 15, 5, 27, 89, 7, 0, 0),
(27, 2, 'Bolivia', '', 591, 0, 8, -17, -66, 6, 0, 0),
(28, 3, 'Bosnia and Herzegovina', '', 387, 570, 176, 4, 17, 6, 0, 0),
(29, 5, 'Botswana', '', 267, 0, 60, -22, 24, 6, 0, 0),
(30, 0, 'Bouvet Island', '', 0, 0, 0, 0, 0, 0, 0, 0),
(31, 2, 'Brazil', 'br', 55, 740, 623, -12, -63, 5, 0, 0),
(32, 0, 'British Indian Ocean territory', '', 0, 0, 0, 0, 0, 0, 0, 0),
(33, 0, 'Brunei Darussalam', '', 673, 20, 192, 0, 0, 0, 0, 0),
(34, 3, 'Bulgaria', '', 359, 910, 584, 42, 25, 6, 0, 0),
(35, 5, 'Burkina Faso', '', 226, 0, 1, 12, -2, 6, 0, 0),
(36, 5, 'Burundi', '', 257, 0, 5, -3, 29, 6, 0, 0),
(37, 7, 'Cambodia', '', 855, 10, 67, 12, 103, 7, 0, 0),
(38, 5, 'Cameroon', '', 237, 20, 58, 5, 12, 6, 0, 0),
(39, 1, 'Canada', '', 107, 51400, 21699, 50, -112, 5, 1, 0),
(40, 0, 'Cape Verde', '', 0, 0, 0, 0, 0, 0, 0, 0),
(41, 0, 'Cayman Islands', '', 108, 10, 10, 0, 0, 0, 0, 0),
(42, 5, 'Central African Republic', '', 236, 0, 4, 6, 19, 6, 0, 0),
(43, 5, 'Chad', '', 235, 0, 3, 15, 18, 6, 0, 0),
(44, 2, 'Chile', '', 56, 520, 427, -33, -71, 5, 0, 0),
(45, 7, 'China', '', 86, 1050, 277, 33, 93, 5, 0, 0),
(46, 0, 'Christmas Island', '', 672, 0, 8, 0, 0, 0, 0, 0),
(47, 0, 'Cocos (Keeling) Islands', '', 0, 0, 0, 0, 0, 0, 0, 0),
(48, 2, 'Colombia', '', 57, 210, 32, 3, -76, 6, 0, 0),
(49, 0, 'Comoros', '', 269, 0, 6, 0, 0, 0, 0, 0),
(51, 5, 'Congo', '', 242, 0, 2, -3, 13, 6, 0, 0),
(52, 5, 'Democratic Republic Congo', '', 242, 0, 0, -4, 20, 6, 0, 0),
(53, 0, 'Cook Islands', '', 682, 0, 7, 0, 0, 0, 0, 0),
(54, 2, 'Costa Rica', '', 506, 0, 7, 10, -85, 6, 0, 0),
(55, 0, 'Ivory Coast', '', 225, 40, 50, 7, -5, 6, 0, 0),
(56, 3, 'Croatia (Hrvatska)', '', 385, 540, 377, 44, 13, 6, 0, 0),
(57, 2, 'Cuba', '', 53, 0, 7, 22, -81, 6, 0, 0),
(58, 3, 'Cyprus', '', 357, 440, 222, 35, 33, 7, 0, 0),
(59, 3, 'Czech Republic', '', 420, 470, 404, 49, 13, 6, 0, 47),
(60, 3, 'Denmark', '', 45, 850, 1011, 55, 8, 6, 0, 47),
(61, 5, 'Djibouti', '', 253, 0, 7, 11, 42, 6, 0, 0),
(62, 0, 'Dominica', '', 109, 0, 4, 0, 0, 0, 0, 0),
(63, 2, 'Dominican Republic', '', 110, 300, 247, 18, -70, 6, 0, 0),
(64, 7, 'East Timor', '', 0, 0, 0, -9, 125, 7, 0, 0),
(65, 2, 'Ecuador', '', 593, 0, 62, 0, -79, 6, 0, 0),
(66, 4, 'Egypt', '', 20, 13325, 1274, 28, 29, 6, 0, 0),
(67, 2, 'El Salvador', '', 503, 0, 9, 13, -89, 6, 0, 0),
(68, 5, 'Equatorial Guinea', '', 240, 0, 2, 1, 10, 6, 0, 0),
(69, 5, 'Eritrea', '', 291, 0, 7, 15, 38, 6, 0, 0),
(70, 3, 'Estonia', '', 372, 550, 200, 58, 25, 7, 0, 0),
(71, 5, 'Ethiopia', '', 251, 0, 3, 9, 39, 7, 0, 0),
(72, 0, 'Falkland Islands', '', 500, 0, 3, 0, 0, 0, 0, 0),
(73, 0, 'Faroe Islands', '', 298, 0, 10, 0, 0, 0, 0, 0),
(74, 7, 'Fiji', '', 679, 240, 55, -18, 178, 8, 0, 0),
(75, 3, 'Finland', '', 358, 310, 576, 63, 22, 5, 0, 47),
(76, 3, 'France', '', 33, 400, 722, 46, 1, 6, 0, 47),
(77, 0, 'French Guiana', '', 594, 0, 1, 4, -53, 7, 0, 0),
(78, 0, 'French Polynesia', '', 689, 0, 4, 0, 0, 0, 0, 0),
(79, 0, 'French Southern Territories', '', 0, 0, 0, 0, 0, 0, 0, 0),
(80, 5, 'Gabon', '', 241, 0, 5, 0, 10, 6, 0, 0),
(81, 5, 'Gambia', '', 220, 0, 4, 12, -16, 6, 0, 0),
(82, 6, 'Georgia', '', 995, 270, 100, 42, 41, 6, 0, 0),
(83, 3, 'Germany', '', 49, 1130, 1073, 50, 7, 6, 0, 47),
(84, 5, 'Ghana', '', 233, 440, 237, 8, -1, 7, 0, 0),
(85, 0, 'Gibraltar', '', 350, 20, 18, 0, 0, 0, 0, 0),
(86, 3, 'Greece', '', 30, 950, 830, 39, 21, 7, 0, 47),
(87, 3, 'Greenland', '', 299, 0, 3, 74, -56, 4, 0, 0),
(88, 0, 'Grenada', '', 111, 0, 4, 0, 0, 0, 0, 0),
(89, 0, 'Guadeloupe', '', 590, 0, 5, 0, 0, 0, 0, 0),
(90, 7, 'Guam', '', 671, 10, 12, 13, 144, 7, 0, 0),
(91, 2, 'Guatemala', '', 502, 0, 9, 15, -91, 6, 0, 0),
(92, 5, 'Guinea', '', 224, 0, 5, 10, -11, 7, 0, 0),
(93, 5, 'Guinea-Bissau', '', 245, 0, 5, 11, -15, 6, 0, 0),
(94, 2, 'Guyana', '', 592, 0, 4, 5, -60, 6, 0, 0),
(95, 2, 'Haiti', '', 509, 10, 3, 18, -73, 6, 0, 0),
(96, 0, 'Heard and McDonald Islands', '', 0, 0, 0, 0, 0, 0, 0, 0),
(97, 2, 'Honduras', '', 504, 110, 8, 14, -88, 6, 0, 0),
(98, 7, 'Hong Kong', '', 852, 1250, 614, 22, 114, 7, 0, 0),
(99, 3, 'Hungary', '', 36, 290, 427, 47, 17, 6, 0, 47),
(100, 3, 'Iceland', '', 354, 195, 85, 64, -22, 6, 0, 0),
(101, 6, 'India', '', 91, 9895, 6618, 21, 73, 5, 0, 0),
(102, 7, 'Indonesia', '', 0, 0, 0, -6, 106, 5, 0, 65),
(103, 4, 'Iran', '', 98, 0, 12, 33, 50, 6, 0, 0),
(104, 4, 'Iraq', '', 964, 0, 6, 31, 40, 6, 0, 0),
(105, 3, 'Ireland', '', 353, 1230, 873, 53, -8, 7, 0, 0),
(106, 4, 'Israel', '', 972, 1000, 560, 32, 35, 8, 0, 0),
(107, 3, 'Italy', '', 39, 1100, 739, 42, 9, 6, 0, 47),
(108, 2, 'Jamaica', '', 112, 640, 597, 18, -77, 6, 0, 0),
(109, 7, 'Japan', '', 81, 551, 136, 36, 139, 6, 0, 0),
(110, 4, 'Jordan', '', 962, 220, 428, 31, 36, 8, 0, 0),
(111, 6, 'Kazakhstan', '', 7032, 0, 22, 48, 61, 5, 0, 0),
(112, 5, 'Kenya', '', 254, 1430, 352, 0, 37, 6, 0, 0),
(113, 0, 'Kiribati', '', 686, 0, 4, 0, 0, 0, 0, 0),
(114, 7, 'Korea (north)', '', 850, 0, 3, 40, 127, 6, 0, 0),
(115, 7, 'Korea (south)', '', 82, 600, 308, 36, 127, 7, 0, 0),
(116, 4, 'Kuwait', '', 965, 690, 527, 29, 47, 8, 0, 0),
(117, 6, 'Kyrgyzstan', '', 7033, 0, 4, 41, 71, 6, 0, 0),
(118, 7, 'Laos', '', 856, 0, 3, 18, 102, 7, 0, 0),
(119, 3, 'Latvia', '', 371, 220, 91, 56, 23, 6, 0, 47),
(120, 4, 'Lebanon', '', 961, 280, 407, 34, 36, 9, 0, 0),
(121, 5, 'Lesotho', '', 266, 0, 3, -29, 27, 6, 0, 0),
(122, 5, 'Liberia', '', 231, 0, 3, 6, -9, 6, 0, 0),
(123, 4, 'Libya', '', 218, 0, 2, 30, 13, 6, 0, 0),
(124, 3, 'Liechtenstein', '', 7034, 0, 8, 47, 9, 6, 0, 0),
(125, 3, 'Lithuania', '', 370, 360, 218, 55, 22, 6, 0, 0),
(126, 3, 'Luxembourg', '', 352, 180, 47, 49, 6, 6, 0, 47),
(127, 7, 'Macao', '', 853, 120, 53, 22, 133, 7, 0, 0),
(128, 3, 'Macedonia', '', 389, 230, 7, 41, 21, 6, 0, 0),
(129, 5, 'Madagascar', '', 261, 0, 3, -19, 45, 6, 0, 0),
(130, 5, 'Malawi', '', 265, 0, 9, -13, 33, 6, 0, 0),
(131, 7, 'Malaysia', '', 60, 5090, 3639, 4, 100, 5, 0, 0),
(132, 0, 'Maldives', '', 960, 210, 8, 0, 0, 0, 0, 0),
(133, 5, 'Mali', '', 223, 0, 4, 15, -8, 5, 0, 0),
(134, 3, 'Malta', '', 356, 50, 197, 35, 14, 6, 0, 0),
(135, 0, 'Marshall Islands', '', 692, 0, 3, 0, 0, 0, 0, 0),
(136, 2, 'Martinique', '', 596, 0, 1, 14, -60, 7, 0, 0),
(137, 5, 'Mauritania', '', 222, 0, 5, 19, -12, 7, 0, 0),
(138, 0, 'Mauritius', '', 230, 0, 10, 0, 0, 0, 0, 0),
(139, 0, 'Mayotte', '', 0, 0, 0, 0, 0, 0, 0, 0),
(140, 2, 'Mexico', '', 52, 1040, 1195, 23, -110, 5, 0, 0),
(141, 7, 'Micronesia', '', 691, 0, 7, 7, 158, 7, 0, 0),
(142, 3, 'Moldova', '', 373, 110, 50, 47, 27, 6, 0, 0),
(143, 3, 'Monaco', '', 377, 0, 4, 43, 7, 7, 0, 0),
(144, 7, 'Mongolia', '', 976, 0, 21, 47, 98, 5, 0, 0),
(145, 0, 'Montserrat', '', 113, 0, 6, 0, 0, 0, 0, 0),
(146, 4, 'Morocco', '', 212, 270, 154, 28, -11, 6, 0, 0),
(147, 5, 'Mozambique', '', 258, 10, 30, -17, 36, 6, 0, 0),
(148, 7, 'Myanmar', '', 95, 0, 6, 21, 94, 6, 0, 0),
(149, 5, 'Namibia', '', 264, 10, 69, -21, 16, 6, 0, 0),
(150, 0, 'Nauru', '', 674, 0, 4, 0, 0, 0, 0, 0),
(151, 7, 'Nepal', '', 977, 0, 4, 28, 81, 6, 0, 0),
(152, 3, 'Netherlands', '', 31, 5540, 2034, 52, 4, 6, 0, 47),
(153, 0, 'Netherlands Antilles', '', 599, 0, 13, 0, 0, 0, 0, 0),
(154, 0, 'New Caledonia', '', 687, 210, 8, 0, 0, 0, 0, 0),
(155, 7, 'New Zealand', '', 64, 3580, 1499, -42, 170, 6, 0, 0),
(156, 2, 'Nicaragua', '', 505, 0, 7, 12, -86, 6, 0, 0),
(157, 5, 'Niger', '', 227, 0, 7, 16, 5, 6, 0, 0),
(158, 5, 'Nigeria', '', 234, 2555, 1107, 8, 6, 6, 0, 0),
(159, 0, 'Niue', '', 683, 0, 4, 0, 0, 0, 0, 0),
(160, 0, 'Norfolk Island', '', 0, 0, 0, 0, 0, 0, 0, 0),
(161, 0, 'Northern Mariana Islands', '', 0, 0, 0, 0, 0, 0, 0, 0),
(162, 3, 'Norway', '', 47, 1865, 1102, 62, 7, 5, 0, 47),
(163, 4, 'Oman', '', 968, 0, 11, 22, 51, 6, 0, 0),
(164, 6, 'Pakistan', '', 92, 1925, 1924, 29, 65, 6, 0, 0),
(165, 0, 'Palau', '', 680, 0, 2, 0, 0, 0, 0, 0),
(166, 4, 'Palestine', '', 6080, 0, 40, 32, 35, 7, 0, 0),
(167, 2, 'Panama', '', 507, 70, 182, 8, -80, 6, 0, 0),
(168, 7, 'Papua New Guinea', '', 675, 0, 3, -6, 143, 6, 0, 0),
(169, 2, 'Paraguay', '', 595, 0, 3, -23, -60, 6, 0, 0),
(170, 2, 'Peru', '', 51, 350, 354, -9, -78, 6, 0, 0),
(171, 7, 'Philippines', '', 63, 2570, 5029, 13, 120, 6, 0, 0),
(172, 0, 'Pitcairn', '', 0, 0, 0, 0, 0, 0, 0, 0),
(173, 3, 'Poland', '', 48, 720, 561, 52, 16, 6, 0, 47),
(174, 3, 'Portugal', '', 351, 730, 550, 39, -8, 6, 0, 47),
(175, 1, 'Puerto Rico', '', 121, 900, 245, 18, -67, 10, 0, 0),
(176, 4, 'Qatar', '', 974, 0, 12, 25, 51, 8, 0, 0),
(177, 0, 'R&eacute;union', '', 262, 0, 4, 0, 0, 0, 0, 0),
(178, 3, 'Romania', '', 40, 1450, 1222, 45, 22, 6, 0, 47),
(179, 3, 'Russia', '', 7, 800, 319, 60, 55, 4, 0, 0),
(180, 5, 'Rwanda', '', 250, 0, 3, -2, 29, 7, 0, 0),
(181, 0, 'Saint Helena', '', 0, 0, 0, 0, 0, 0, 0, 0),
(182, 0, 'Saint Kitts and Nevis', '', 0, 0, 0, 0, 0, 0, 0, 0),
(183, 0, 'Saint Lucia', '', 0, 0, 0, 0, 0, 0, 0, 0),
(184, 0, 'Saint Pierre and Miquelon', '', 0, 0, 0, 0, 0, 0, 0, 0),
(185, 0, 'Saint Vincent and the Grenadines', '', 0, 0, 0, 0, 0, 0, 0, 0),
(186, 7, 'Samoa', '', 0, 0, 0, -13, -172, 8, 0, 0),
(187, 0, 'San Marino', '', 378, 0, 10, 0, 0, 0, 0, 0),
(188, 0, 'Sao Tome and Principe', '', 0, 0, 0, 0, 0, 0, 0, 0),
(189, 4, 'Saudi Arabia', '', 966, 460, 452, 24, 38, 6, 0, 0),
(190, 5, 'Senegal', '', 221, 420, 21, 15, -16, 7, 0, 0),
(191, 3, 'Serbia and Montenegro', '', 0, 0, 0, 44, 16, 6, 0, 0),
(192, 0, 'Seychelles', '', 248, 10, 24, 0, 0, 0, 0, 0),
(193, 5, 'Sierra Leone', '', 232, 0, 10, 8, -12, 6, 0, 0),
(194, 7, 'Singapore', '', 65, 3760, 3914, 1, 103, 7, 0, 0),
(195, 3, 'Slovakia', '', 421, 250, 183, 48, 17, 6, 0, 0),
(196, 3, 'Slovenia', '', 386, 150, 161, 46, 17, 6, 0, 0),
(197, 0, 'Solomon Islands', '', 677, 0, 4, 0, 0, 0, 0, 0),
(198, 5, 'Somalia', '', 252, 0, 3, 9, 45, 6, 0, 0),
(199, 5, 'South Africa', '', 27, 2240, 1631, -30, 22, 6, 0, 0),
(200, 0, 'South Georgia and the South Sandwich Islands', '', 0, 0, 0, 0, 0, 0, 0, 0),
(201, 3, 'Spain', '', 34, 940, 529, 39, -5, 6, 0, 47),
(202, 6, 'Sri Lanka', '', 94, 130, 219, 7, 80, 7, 0, 0),
(203, 4, 'Sudan', '', 249, 0, 8, 13, 27, 6, 0, 0),
(204, 0, 'Suriname', '', 597, 0, 3, 0, 0, 0, 0, 0),
(205, 0, 'Svalbard and Jan Mayen Islands', '', 0, 0, 0, 0, 0, 0, 0, 0),
(206, 5, 'Swaziland', '', 268, 310, 4, -26, 31, 7, 0, 0),
(207, 3, 'Sweden', '', 46, 920, 1478, 62, 14, 5, 0, 47),
(208, 3, 'Switzerland', '', 41, 600, 366, 46, 7, 6, 0, 47),
(209, 4, 'Syria', '', 963, 230, 128, 35, 36, 7, 0, 0),
(210, 7, 'Taiwan', '', 886, 1410, 392, 23, 120, 7, 0, 0),
(211, 6, 'Tajikistan', '', 708, 0, 6, 38, 68, 6, 0, 0),
(212, 5, 'Tanzania', '', 255, 0, 130, -6, 34, 6, 0, 0),
(213, 7, 'Thailand', '', 66, 1620, 2504, 13, 99, 6, 0, 0),
(214, 5, 'Togo', '', 228, 0, 5, 7, 0, 6, 0, 0),
(215, 0, 'Tokelau', '', 690, 0, 6, 0, 0, 0, 0, 0),
(216, 0, 'Tonga', '', 676, 0, 2, 0, 0, 0, 0, 0),
(217, 2, 'Trinidad and Tobago', '', 117, 630, 155, 10, -61, 6, 0, 0),
(218, 4, 'Tunisia', '', 216, 0, 8, 34, 9, 7, 0, 0),
(219, 4, 'Turkey', '', 90, 640, 811, 39, 31, 6, 0, 0),
(220, 6, 'Turkmenistan', '', 709, 0, 7, 38, 56, 6, 0, 0),
(221, 0, 'Turks and Caicos Islands', '', 118, 0, 5, 0, 0, 0, 0, 0),
(222, 0, 'Tuvalu', '', 688, 0, 9, 0, 0, 0, 0, 0),
(223, 5, 'Uganda', '', 256, 0, 99, 1, 32, 6, 0, 0),
(224, 3, 'Ukraine', '', 380, 680, 144, 49, 27, 6, 0, 0),
(225, 4, 'United Arab Emirates', '', 971, 1190, 1099, 23, 53, 7, 0, 0),
(226, 3, 'United Kingdom', '', 44, 50890, 32115, 53, -4, 6, 1, 0),
(227, 1, 'USA', 'us', 1, 1971298, 678403, 37, -106, 5, 2, 144),
(228, 2, 'Uruguay', '', 598, 0, 2, -33, -57, 6, 0, 0),
(229, 6, 'Uzbekistan', '', 711, 10, 11, 41, 59, 6, 0, 0),
(230, 0, 'Vanuatu', '', 678, 0, 3, 0, 0, 0, 0, 0),
(231, 3, 'Vatican City', '', 379, 0, 2, 41, 12, 9, 0, 0),
(232, 2, 'Venezuela', '', 58, 30, 275, 7, -69, 6, 0, 0),
(233, 7, 'Vietnam', '', 84, 560, 504, 15, 104, 6, 0, 0),
(234, 2, 'Virgin Islands (British)', '', 0, 0, 0, 18, -64, 7, 0, 0),
(235, 2, 'Virgin Islands (US)', '', 123, 20, 25, 17, -64, 7, 0, 0),
(236, 0, 'Wallis and Futuna Islands', '', 681, 0, 3, 0, 0, 0, 0, 0),
(237, 4, 'Western Sahara', '', 0, 0, 0, 25, -13, 6, 0, 0),
(238, 4, 'Yemen', '', 967, 110, 46, 15, 45, 7, 0, 0),
(240, 5, 'Zambia', '', 260, 0, 6, -13, 27, 6, 0, 0),
(241, 5, 'Zimbabwe', '', 263, 330, 120, -18, 30, 6, 0, 0),
(243, 6, 'Chechnya', '', 0, 0, 0, 44, 42, 6, 0, 0),
(244, 4, 'Kurdistan', '', 0, 0, 0, 37, 42, 7, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_currency_crypto`
--

CREATE TABLE IF NOT EXISTS `tbl_currency_crypto` (
  `crypto_code` varchar(10) NOT NULL,
  `crypto_name` varchar(24) NOT NULL,
  `exchange` varchar(10) NOT NULL,
  `rate` varchar(13) NOT NULL,
  `buyprice` varchar(10) NOT NULL,
  `sellprice` varchar(10) NOT NULL,
  `date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_currency_crypto`
--

INSERT INTO `tbl_currency_crypto` (`crypto_code`, `crypto_name`, `exchange`, `rate`, `buyprice`, `sellprice`, `date`) VALUES
('btc', 'bitcoin', 'bitstamp', '560', '', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_currency_fiat`
--

CREATE TABLE IF NOT EXISTS `tbl_currency_fiat` (
  `currency_id` int(11) NOT NULL AUTO_INCREMENT,
  `currency_name` varchar(64) DEFAULT NULL,
  `currency_code` char(3) DEFAULT NULL,
  `currency_rate_USD` decimal(10,2) NOT NULL,
  `currency_rate_BTC` decimal(16,7) NOT NULL,
  `date` int(11) NOT NULL,
  `sortid` tinyint(3) NOT NULL,
  `countryid` int(11) NOT NULL,
  PRIMARY KEY (`currency_id`),
  KEY `idx_currency_name` (`currency_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=168 ;

--
-- Dumping data for table `tbl_currency_fiat`
--

INSERT INTO `tbl_currency_fiat` (`currency_id`, `currency_name`, `currency_code`, `currency_rate_USD`, `currency_rate_BTC`, `date`, `sortid`, `countryid`) VALUES
(1, 'Andorran Peseta', 'ADP', '0.00', '0.0000000', 0, 0, 0),
(2, 'United Arab Emirates Dirham', 'AED', '0.00', '0.0000000', 0, 0, 0),
(3, 'Afghanistan Afghani', 'AFA', '0.00', '0.0000000', 0, 0, 0),
(4, 'Albanian Lek', 'ALL', '0.00', '0.0000000', 0, 0, 0),
(5, 'Netherlands Antillian Guilder', 'ANG', '0.00', '0.0000000', 0, 0, 0),
(6, 'Angolan Kwanza', 'AOK', '0.00', '0.0000000', 0, 0, 0),
(7, 'Argentine Peso', 'ARS', '0.00', '0.0000000', 0, 0, 0),
(9, 'Australian Dollar', 'AUD', '0.00', '0.0000000', 0, 3, 0),
(10, 'Aruban Florin', 'AWG', '0.00', '0.0000000', 0, 0, 0),
(11, 'Barbados Dollar', 'BBD', '0.00', '0.0000000', 0, 0, 0),
(12, 'Bangladeshi Taka', 'BDT', '0.00', '0.0000000', 0, 0, 0),
(14, 'Bulgarian Lev', 'BGN', '0.00', '0.0000000', 0, 0, 0),
(15, 'Bahraini Dinar', 'BHD', '0.00', '0.0000000', 0, 0, 0),
(16, 'Burundi Franc', 'BIF', '0.00', '0.0000000', 0, 0, 0),
(17, 'Bermudian Dollar', 'BMD', '0.00', '0.0000000', 0, 0, 0),
(18, 'Brunei Dollar', 'BND', '0.00', '0.0000000', 0, 0, 0),
(19, 'Bolivian Boliviano', 'BOB', '0.00', '0.0000000', 0, 0, 0),
(20, 'Brazilian Real', 'BRL', '0.00', '0.0000000', 0, 0, 0),
(21, 'Bahamian Dollar', 'BSD', '0.00', '0.0000000', 0, 0, 0),
(22, 'Bhutan Ngultrum', 'BTN', '0.00', '0.0000000', 0, 0, 0),
(23, 'Burma Kyat', 'BUK', '0.00', '0.0000000', 0, 0, 0),
(24, 'Botswanian Pula', 'BWP', '0.00', '0.0000000', 0, 0, 0),
(25, 'Belize Dollar', 'BZD', '0.00', '0.0000000', 0, 0, 0),
(26, 'Canadian Dollar', 'CAD', '0.00', '0.0000000', 0, 3, 0),
(27, 'Swiss Franc', 'CHF', '0.00', '0.0000000', 0, 0, 0),
(28, 'Chilean Unidades de Fomento', 'CLF', '0.00', '0.0000000', 0, 0, 0),
(29, 'Chilean Peso', 'CLP', '0.00', '0.0000000', 0, 0, 0),
(30, 'Yuan (Chinese) Renminbi', 'CNY', '0.00', '0.0000000', 0, 0, 0),
(31, 'Colombian Peso', 'COP', '0.00', '0.0000000', 0, 0, 0),
(32, 'Costa Rican Colon', 'CRC', '0.00', '0.0000000', 0, 0, 0),
(33, 'Czech Republic Koruna', 'CZK', '0.00', '0.0000000', 0, 0, 0),
(34, 'Cuban Peso', 'CUP', '0.00', '0.0000000', 0, 0, 0),
(35, 'Cape Verde Escudo', 'CVE', '0.00', '0.0000000', 0, 0, 0),
(36, 'Cyprus Pound', 'CYP', '0.00', '0.0000000', 0, 0, 0),
(40, 'Danish Krone', 'DKK', '0.00', '0.0000000', 0, 0, 0),
(41, 'Dominican Peso', 'DOP', '0.00', '0.0000000', 0, 0, 0),
(42, 'Algerian Dinar', 'DZD', '80.07', '0.0000000', 1404083676, 0, 0),
(43, 'Ecuador Sucre', 'ECS', '0.00', '0.0000000', 0, 0, 0),
(44, 'Egyptian Pound', 'EGP', '7.15', '0.0000000', 1404086854, 0, 0),
(45, 'Estonian Kroon (EEK)', 'EEK', '0.00', '0.0000000', 1404092031, 0, 0),
(46, 'Ethiopian Birr', 'ETB', '0.00', '0.0000000', 0, 0, 0),
(47, 'Euro', 'EUR', '0.74', '0.0000000', 1391648185, 8, 0),
(49, 'Fiji Dollar', 'FJD', '0.00', '0.0000000', 0, 0, 0),
(50, 'Falkland Islands Pound', 'FKP', '0.00', '0.0000000', 0, 0, 0),
(52, 'British Pound', 'GBP', '0.59', '0.0000000', 1404085763, 0, 0),
(53, 'Ghanaian Cedi', 'GHC', '0.00', '0.0000000', 0, 0, 0),
(54, 'Gibraltar Pound', 'GIP', '0.00', '0.0000000', 0, 0, 0),
(55, 'Gambian Dalasi', 'GMD', '0.00', '0.0000000', 0, 0, 0),
(56, 'Guinea Franc', 'GNF', '0.00', '0.0000000', 0, 0, 0),
(58, 'Guatemalan Quetzal', 'GTQ', '0.00', '0.0000000', 0, 0, 0),
(59, 'Guinea-Bissau Peso', 'GWP', '0.00', '0.0000000', 0, 0, 0),
(60, 'Guyanan Dollar', 'GYD', '0.00', '0.0000000', 0, 0, 0),
(61, 'Hong Kong Dollar', 'HKD', '0.00', '0.0000000', 0, 3, 0),
(62, 'Honduran Lempira', 'HNL', '0.00', '0.0000000', 0, 0, 0),
(63, 'Haitian Gourde', 'HTG', '0.00', '0.0000000', 0, 0, 0),
(64, 'Hungarian Forint', 'HUF', '0.00', '0.0000000', 0, 0, 0),
(65, 'Indonesian Rupiah', 'IDR', '0.00', '0.0000000', 0, 3, 0),
(66, 'Irish Punt', 'IEP', '0.00', '0.0000000', 0, 0, 0),
(67, 'Israeli Shekel', 'ILS', '0.00', '0.0000000', 0, 0, 0),
(68, 'Indian Rupee', 'INR', '60.02', '0.0000000', 1404092274, 0, 0),
(69, 'Iraqi Dinar', 'IQD', '0.00', '0.0000000', 0, 0, 0),
(70, 'Iranian Rial', 'IRR', '0.00', '0.0000000', 0, 0, 0),
(73, 'Jamaican Dollar', 'JMD', '0.00', '0.0000000', 0, 0, 0),
(74, 'Jordanian Dinar', 'JOD', '0.00', '0.0000000', 0, 0, 0),
(75, 'Japanese Yen', 'JPY', '0.00', '0.0000000', 0, 3, 0),
(76, 'Kenyan Schilling', 'KES', '0.00', '0.0000000', 0, 0, 0),
(77, 'Kampuchean (Cambodian) Riel', 'KHR', '0.00', '0.0000000', 0, 0, 0),
(78, 'Comoros Franc', 'KMF', '0.00', '0.0000000', 0, 0, 0),
(79, 'North Korean Won', 'KPW', '0.00', '0.0000000', 0, 0, 0),
(80, '(South) Korean Won', 'KRW', '0.00', '0.0000000', 0, 3, 0),
(81, 'Kuwaiti Dinar', 'KWD', '0.00', '0.0000000', 0, 0, 0),
(82, 'Cayman Islands Dollar', 'KYD', '0.00', '0.0000000', 0, 0, 0),
(83, 'Lao Kip', 'LAK', '0.00', '0.0000000', 0, 0, 0),
(84, 'Lebanese Pound', 'LBP', '0.00', '0.0000000', 0, 0, 0),
(85, 'Sri Lanka Rupee', 'LKR', '0.00', '0.0000000', 0, 0, 0),
(86, 'Liberian Dollar', 'LRD', '0.00', '0.0000000', 0, 0, 0),
(87, 'Lesotho Loti', 'LSL', '0.00', '0.0000000', 0, 0, 0),
(89, 'Libyan Dinar', 'LYD', '0.00', '0.0000000', 0, 0, 0),
(90, 'Moroccan Dirham', 'MAD', '0.00', '0.0000000', 0, 0, 0),
(91, 'Malagasy Franc', 'MGF', '0.00', '0.0000000', 0, 0, 0),
(92, 'Mongolian Tugrik', 'MNT', '0.00', '0.0000000', 0, 0, 0),
(93, 'Macau Pataca', 'MOP', '0.00', '0.0000000', 0, 0, 0),
(94, 'Mauritanian Ouguiya', 'MRO', '0.00', '0.0000000', 0, 0, 0),
(95, 'Maltese Lira', 'MTL', '0.00', '0.0000000', 0, 0, 0),
(96, 'Mauritius Rupee', 'MUR', '0.00', '0.0000000', 0, 0, 0),
(97, 'Maldive Rufiyaa', 'MVR', '0.00', '0.0000000', 0, 0, 0),
(98, 'Malawi Kwacha', 'MWK', '0.00', '0.0000000', 0, 0, 0),
(99, 'Mexican Peso', 'MXP', '0.00', '0.0000000', 0, 0, 0),
(100, 'Malaysian Ringgit', 'MYR', '0.00', '0.0000000', 0, 0, 0),
(101, 'Mozambique Metical', 'MZM', '0.00', '0.0000000', 0, 0, 0),
(102, 'Namibian Dollar', 'NAD', '0.00', '0.0000000', 0, 0, 0),
(103, 'Nigerian Naira', 'NGN', '0.00', '0.0000000', 0, 0, 0),
(104, 'Nicaraguan Cordoba', 'NIO', '0.00', '0.0000000', 0, 0, 0),
(105, 'Norwegian Kroner', 'NOK', '0.00', '0.0000000', 0, 0, 0),
(106, 'Nepalese Rupee', 'NPR', '0.00', '0.0000000', 0, 0, 0),
(107, 'New Zealand Dollar', 'NZD', '0.00', '0.0000000', 0, 0, 0),
(108, 'Omani Rial', 'OMR', '0.00', '0.0000000', 0, 0, 0),
(109, 'Panamanian Balboa', 'PAB', '0.00', '0.0000000', 0, 0, 0),
(110, 'Peruvian Nuevo Sol', 'PEN', '0.00', '0.0000000', 0, 0, 0),
(111, 'Papua New Guinea Kina', 'PGK', '0.00', '0.0000000', 0, 0, 0),
(112, 'Philippine Peso', 'PHP', '0.00', '0.0000000', 0, 0, 0),
(113, 'Pakistan Rupee', 'PKR', '0.00', '0.0000000', 0, 0, 0),
(114, 'Polish Zloty', 'PLN', '0.00', '0.0000000', 0, 0, 0),
(116, 'Paraguay Guarani', 'PYG', '0.00', '0.0000000', 0, 0, 0),
(117, 'Qatari Rial', 'QAR', '0.00', '0.0000000', 0, 0, 0),
(118, 'Romanian Leu', 'RON', '0.00', '0.0000000', 0, 0, 0),
(119, 'Rwanda Franc', 'RWF', '0.00', '0.0000000', 0, 0, 0),
(120, 'Saudi Arabian Riyal', 'SAR', '0.00', '0.0000000', 0, 0, 0),
(121, 'Solomon Islands Dollar', 'SBD', '0.00', '0.0000000', 0, 0, 0),
(122, 'Seychelles Rupee', 'SCR', '0.00', '0.0000000', 0, 0, 0),
(123, 'Sudanese Pound', 'SDP', '0.00', '0.0000000', 0, 0, 0),
(124, 'Swedish Krona', 'SEK', '0.00', '0.0000000', 0, 0, 0),
(125, 'Singapore Dollar', 'SGD', '0.00', '0.0000000', 0, 0, 0),
(126, 'St. Helena Pound', 'SHP', '0.00', '0.0000000', 0, 0, 0),
(127, 'Sierra Leone Leone', 'SLL', '0.00', '0.0000000', 0, 0, 0),
(128, 'Somali Schilling', 'SOS', '0.00', '0.0000000', 0, 0, 0),
(129, 'Suriname Guilder', 'SRG', '0.00', '0.0000000', 0, 0, 0),
(130, 'Sao Tome and Principe Dobra', 'STD', '0.00', '0.0000000', 0, 0, 0),
(131, 'Russian Ruble', 'RUB', '0.00', '0.0000000', 0, 3, 0),
(132, 'El Salvador Colon', 'SVC', '0.00', '0.0000000', 0, 0, 0),
(133, 'Syrian Potmd', 'SYP', '0.00', '0.0000000', 0, 0, 0),
(134, 'Swaziland Lilangeni', 'SZL', '0.00', '0.0000000', 0, 0, 0),
(135, 'Thai Baht', 'THB', '0.00', '0.0000000', 0, 0, 0),
(136, 'Tunisian Dinar', 'TND', '0.00', '0.0000000', 0, 0, 0),
(137, 'Tongan Paanga', 'TOP', '0.00', '0.0000000', 0, 0, 0),
(138, 'East Timor Escudo', 'TPE', '0.00', '0.0000000', 0, 0, 0),
(139, 'Turkish Lira', 'TRY', '0.00', '0.0000000', 0, 0, 0),
(140, 'Trinidad and Tobago Dollar', 'TTD', '0.00', '0.0000000', 0, 0, 0),
(141, 'Taiwan Dollar', 'TWD', '0.00', '0.0000000', 0, 0, 0),
(142, 'Tanzanian Schilling', 'TZS', '0.00', '0.0000000', 0, 0, 0),
(143, 'Uganda Shilling', 'UGX', '0.00', '0.0000000', 0, 0, 0),
(144, 'US Dollar', 'USD', '1.00', '0.0000000', 1404096532, 9, 227),
(145, 'Uruguayan Peso', 'UYU', '0.00', '0.0000000', 0, 0, 0),
(146, 'Venezualan Bolivar', 'VEF', '0.00', '0.0000000', 0, 0, 0),
(147, 'Vietnamese Dong', 'VND', '0.00', '0.0000000', 0, 0, 0),
(148, 'Vanuatu Vatu', 'VUV', '0.00', '0.0000000', 0, 0, 0),
(149, 'Samoan Tala', 'WST', '0.00', '0.0000000', 0, 0, 0),
(150, 'CommunautÃ© FinanciÃ¨re Africaine BEAC, Francs', 'XAF', '0.00', '0.0000000', 0, 0, 0),
(151, 'Silver, Ounces', 'XAG', '0.00', '0.0000000', 0, 0, 0),
(152, 'Gold, Ounces', 'XAU', '0.00', '0.0000000', 0, 0, 0),
(153, 'East Caribbean Dollar', 'XCD', '0.00', '0.0000000', 0, 0, 0),
(154, 'International Monetary Fund (IMF) Special Drawing Rights', 'XDR', '0.00', '0.0000000', 0, 0, 0),
(155, 'CommunautÃ© FinanciÃ¨re Africaine BCEAO - Francs', 'XOF', '0.00', '0.0000000', 0, 0, 0),
(156, 'Palladium Ounces', 'XPD', '0.00', '0.0000000', 0, 0, 0),
(157, 'Comptoirs FranÃ§ais du Pacifique Francs', 'XPF', '0.00', '0.0000000', 0, 0, 0),
(158, 'Platinum, Ounces', 'XPT', '0.00', '0.0000000', 0, 0, 0),
(159, 'Democratic Yemeni Dinar', 'YDD', '0.00', '0.0000000', 0, 0, 0),
(160, 'Yemeni Rial', 'YER', '0.00', '0.0000000', 0, 0, 0),
(161, 'New Yugoslavia Dinar', 'YUD', '0.00', '0.0000000', 0, 0, 0),
(162, 'South African Rand', 'ZAR', '0.00', '0.0000000', 0, 3, 0),
(163, 'Zambian Kwacha', 'ZMK', '0.00', '0.0000000', 0, 0, 0),
(164, 'Zaire Zaire', 'ZRZ', '0.00', '0.0000000', 0, 0, 0),
(165, 'Zimbabwe Dollar', 'ZWD', '0.00', '0.0000000', 0, 0, 0),
(166, 'Slovak Koruna', 'SKK', '0.00', '0.0000000', 0, 0, 0),
(167, 'Armenian Dram', 'AMD', '0.00', '0.0000000', 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_escrow`
--

CREATE TABLE IF NOT EXISTS `tbl_escrow` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `user_email` varchar(24) DEFAULT NULL,
  `crypto_code` varchar(6) NOT NULL,
  `crypto_amount` int(11) DEFAULT NULL,
  `address_email` varchar(100) DEFAULT NULL,
  `verify_code` varchar(100) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_filled` datetime DEFAULT NULL,
  `status_id` tinyint(1) DEFAULT NULL,
  `status_msg` varchar(100) DEFAULT NULL,
  `crypto_rate` decimal(12,2) DEFAULT NULL,
  `label` varchar(1000) DEFAULT NULL,
  `transaction_id_send` int(11) DEFAULT NULL,
  `transaction_id_get` int(11) DEFAULT NULL,
  `user_id_received` int(11) DEFAULT NULL,
  `ipaddress` varchar(48) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_member`
--

CREATE TABLE IF NOT EXISTS `tbl_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_code` varchar(48) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(254) NOT NULL,
  `cellphone` varchar(24) NOT NULL,
  `first_name` varchar(24) NOT NULL,
  `last_name` varchar(24) NOT NULL,
  `address` varchar(24) NOT NULL,
  `address2` varchar(24) NOT NULL,
  `cityname` varchar(10) NOT NULL,
  `state` varchar(6) NOT NULL,
  `postal` varchar(10) NOT NULL,
  `country_id` int(11) NOT NULL,
  `country_phonecode` varchar(5) NOT NULL,
  `country_name` varchar(100) NOT NULL,
  `currency_id` int(11) NOT NULL,
  `currency_symbol` varchar(6) NOT NULL,
  `name` varchar(100) NOT NULL,
  `crypto_miner_fee` decimal(14,8) NOT NULL,
  `count_externalsends` int(11) NOT NULL,
  `flag_qrcodeimg` tinyint(1) NOT NULL,
  `date_joined` datetime NOT NULL,
  `emailcode` varchar(12) NOT NULL,
  `phonecode` varchar(12) NOT NULL,
  `verification_phone` tinyint(1) NOT NULL,
  `verification_email` tinyint(1) NOT NULL,
  `verification_id` tinyint(1) NOT NULL,
  `verification_address` tinyint(1) NOT NULL,
  `verification_level` tinyint(1) NOT NULL,
  `wallet_address` varchar(48) NOT NULL,
  `wallet_receive_on` tinyint(1) NOT NULL,
  `sendlocked` tinyint(1) NOT NULL,
  `admin` tinyint(1) NOT NULL,
  `lastlogin` datetime NOT NULL,
  `date_last_activity` int(11) NOT NULL,
  `flag_sms_on_get` tinyint(1) NOT NULL,
  `flag_sms_on_send` tinyint(1) NOT NULL,
  `flag_email_on_get` tinyint(1) NOT NULL,
  `flag_email_on_send` tinyint(1) NOT NULL,
  `date_passwordchanged` datetime NOT NULL,
  `date_lastlogin` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9170 ;

--
-- Dumping data for table `tbl_member`
--

INSERT INTO `tbl_member` (`id`, `id_code`, `email`, `password`, `cellphone`, `first_name`, `last_name`, `address`, `address2`, `cityname`, `state`, `postal`, `country_id`, `country_phonecode`, `country_name`, `currency_id`, `currency_symbol`, `name`, `crypto_miner_fee`, `count_externalsends`, `flag_qrcodeimg`, `date_joined`, `emailcode`, `phonecode`, `verification_phone`, `verification_email`, `verification_id`, `verification_address`, `verification_level`, `wallet_address`, `wallet_receive_on`, `sendlocked`, `admin`, `lastlogin`, `date_last_activity`, `flag_sms_on_get`, `flag_sms_on_send`, `flag_email_on_get`, `flag_email_on_send`, `date_passwordchanged`, `date_lastlogin`) VALUES
(9168, '71b57daeac56bb600bd6d0195cf1616ee26c4b907049c681', 'ray@easybitz.com', '$2y$10$.MNsNFHj6pMGwgQkMw9y0.BMgM.ii4DVuat.c7srf1iVymYPP/032', '', '', '', '', '', '', '', '', 0, '', '', 0, '', '', '0.00000000', 0, 1, '2014-06-08 20:37:22', '964857086915', '', 0, 1, 0, 0, 1, 'mk4rVHmv2SmnnMjDeGbtNTzBSRkZQLgUmZ', 1, 0, 0, '2014-06-28 21:00:48', 0, 0, 0, 0, 0, '0000-00-00 00:00:00', 0),
(9169, '5fe777cd24625560dda54161cecd2908ebfe61a0b46e32fc', 'info@gmail.com', '$2y$10$S3INVab7EmgK34MYznXw3ePNCWl8TrvOQAlTrP5vfzoo5Hnpwlj8S', '', '', '', '', '', '', '', '', 0, '', '', 0, '', '', '0.00000000', 0, 1, '2014-06-28 21:14:04', '947197112821', '', 0, 0, 0, 0, 0, 'mk3yFMozDHzE4KsXAV7VPsHz3axCfg8UME', 1, 0, 0, '2014-06-28 17:14:04', 0, 0, 0, 0, 0, '0000-00-00 00:00:00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_rates`
--

CREATE TABLE IF NOT EXISTS `tbl_rates` (
  `crypto` varchar(10) NOT NULL,
  `exchange` varchar(10) NOT NULL,
  `rate` varchar(13) NOT NULL,
  `buyprice` varchar(10) NOT NULL,
  `sellprice` varchar(10) NOT NULL,
  `date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_rates`
--

INSERT INTO `tbl_rates` (`crypto`, `exchange`, `rate`, `buyprice`, `sellprice`, `date`) VALUES
('btc', 'gox', '115.6844556', '', '', 1392945425),
('btc', 'coindesk', '691.8059631', '', '', 1393198639);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_transactions`
--

CREATE TABLE IF NOT EXISTS `tbl_transactions` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `callback_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_id_sentto` int(11) NOT NULL,
  `user_id_receivedfrom` int(11) NOT NULL,
  `currency_id` int(11) NOT NULL,
  `currency_code` varchar(6) NOT NULL,
  `debit` decimal(16,8) NOT NULL,
  `credit` decimal(16,8) NOT NULL,
  `balance_prev` decimal(16,8) NOT NULL,
  `balance_curr` decimal(16,8) NOT NULL,
  `sender_name` varchar(100) NOT NULL,
  `sender_email` varchar(100) NOT NULL,
  `sender_phone` varchar(100) NOT NULL,
  `receiver_name` varchar(100) NOT NULL,
  `receiver_email` varchar(100) NOT NULL,
  `receiver_phone` varchar(100) NOT NULL,
  `type` varchar(12) NOT NULL,
  `cryptotype` varchar(10) NOT NULL,
  `crypto_amt` decimal(14,8) NOT NULL,
  `crypto_miner_fee` decimal(14,8) NOT NULL,
  `crypto_total_outflow` decimal(14,8) NOT NULL,
  `crypto_rate_usd` decimal(8,2) NOT NULL,
  `balance_crypto_old` decimal(14,8) NOT NULL,
  `balance_crypto_new` decimal(14,8) NOT NULL,
  `fiat_amt` decimal(8,2) NOT NULL,
  `fiat_type` varchar(10) NOT NULL,
  `fiat_rate` decimal(8,2) NOT NULL,
  `walletaddress_sentto` varchar(100) NOT NULL,
  `walletaddress_from` varchar(100) NOT NULL,
  `hash_transaction` varchar(255) NOT NULL,
  `label` varchar(1000) NOT NULL,
  `datetime_created` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `status` tinyint(1) NOT NULL,
  `status_msg` varchar(256) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_code` varchar(24) NOT NULL,
  `ipaddress` varchar(24) NOT NULL,
  `confirmations` smallint(6) NOT NULL,
  `wallet_location` varchar(24) NOT NULL,
  PRIMARY KEY (`transaction_id`),
  KEY `walletaddress_sentto` (`walletaddress_sentto`),
  KEY `walletaddress_from` (`walletaddress_from`),
  KEY `user_id` (`user_id`),
  KEY `user_id_sentto` (`user_id_sentto`),
  KEY `type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4565 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_transactions_que`
--

CREATE TABLE IF NOT EXISTS `tbl_transactions_que` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_type` varchar(12) DEFAULT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `status_id` tinyint(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `transaction_amt` decimal(14,8) DEFAULT NULL,
  `transaction_txid` varchar(100) DEFAULT NULL,
  `transaction_address` varchar(100) DEFAULT NULL,
  `crypto_code` varchar(6) NOT NULL,
  `ipaddress` varchar(48) NOT NULL,
  `location` varchar(200) NOT NULL,
  `approval` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=147 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_wallet_addresses`
--

CREATE TABLE IF NOT EXISTS `tbl_wallet_addresses` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `wallet_address` varchar(48) DEFAULT NULL,
  `wallet_server` varchar(24) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `amount_total_received` decimal(14,8) DEFAULT NULL,
  `amount_balance` decimal(14,8) DEFAULT NULL,
  `crypto_code` varchar(6) NOT NULL,
  `user_name` varchar(48) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `tbl_wallet_addresses`
--

INSERT INTO `tbl_wallet_addresses` (`id`, `user_id`, `wallet_address`, `wallet_server`, `date_created`, `amount_total_received`, `amount_balance`, `crypto_code`, `user_name`) VALUES
(1, 15, 'n2P8GsAGgpCKY2H5QxuhKxukpcf9RjhBFv', NULL, '2014-06-26 20:24:57', NULL, NULL, '', NULL),
(2, 15, 'myQ7zZsQhXXJsBfmFAaLGNZ2v2Hsbo3h6C', NULL, '2014-06-26 20:26:21', NULL, NULL, '', NULL),
(3, 15, 'mscFij6E2QJctCXCaTuEe4Mux8NweTNK65', NULL, '2014-06-26 20:29:23', NULL, NULL, '', NULL),
(4, 15, 'n3q7kuxQYVCeB2fT5Dh3RjBSjnDNMrPfHs', NULL, '2014-06-26 20:30:53', NULL, NULL, '', NULL),
(5, 15, 'n2KUdH2N3XMMwb5b9vb7yci4hhQkhVs8BA', NULL, '2014-06-26 20:31:21', NULL, NULL, '', NULL),
(6, 15, 'mouHTzs1AuHcxsa5WyBJfFTvQUgxnHyf9M', NULL, '2014-06-26 20:31:24', NULL, NULL, '', NULL),
(7, 9169, 'mk3yFMozDHzE4KsXAV7VPsHz3axCfg8UME', NULL, '2014-06-28 17:14:06', NULL, NULL, '', NULL),
(8, 9168, 'mk4rVHmv2SmnnMjDeGbtNTzBSRkZQLgUmZ', NULL, '2014-06-28 18:10:55', NULL, NULL, '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_wallet_balances`
--

CREATE TABLE IF NOT EXISTS `tbl_wallet_balances` (
  `userid` int(11) NOT NULL,
  `currency_type` varchar(6) NOT NULL COMMENT 'crypto fiat',
  `currency_id` int(11) NOT NULL,
  `currency_code` varchar(6) NOT NULL COMMENT 'btc ltc usd eur',
  `debitcredit` decimal(16,8) NOT NULL,
  `balance` decimal(16,8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
