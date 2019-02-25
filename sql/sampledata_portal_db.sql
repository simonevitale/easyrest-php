SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- --------------------------------------------------------

--
-- Data dump for table `Author`
--

INSERT INTO `Author` (`AuthorId`, `Name`, `Image`, `Active`, `Description`, `UserId`) VALUES
(1, 'Admin', '' 1, NULL, 1),
(2, 'Simone Vitale', '' 1, NULL, 1);

--
-- Data dump for table `Category`
--

INSERT INTO `Category` (`CategoryId`, `Name`, `Entity`) VALUES
(1, 'News', 'Article');

--
-- Data dump for table `Language`
--

INSERT INTO `Language` (`Name`, `Code`) VALUES
('English', 'en'),
('Italiano', 'it');

--
-- Data dump for table `Location`
--

INSERT INTO `Location` (`LocationId`, `Name`, `Address1`, `Address2`, `PostCode`, `City`, `Country`, `Description`, `Phone`, `Email`, `WebsiteLink`, `FacebookLink`, `FlickrLink`, `Active`, `UserId`) VALUES
(1, 'Tower Bridge', '', '', 'SE1 2UP', 'London', 'United Kingdom', '', '+44123456789', 'example@location.com', 'www.location.com', '', NULL, 1, 1);

--
-- Data dump for table `Organization`
--

INSERT INTO `Organization` (`OrganizationId`, `Name`, `Address1`, `Address2`, `PostCode`, `City`, `Country`, `WebsiteLink`, `FacebookLink`, `TwitterLink`) VALUES
(1, 'Microsoft', '', '', '', 'Redmond', 'United States', 'www.microsoft.com', "", 'https://twitter.com/Microsoft'),
(1, 'Apple', '', '', '', 'Cupertino', 'United States', 'www.apple.com', "", 'https://twitter.com/Apple');

--
-- Data dump for table `Role`
--

INSERT INTO `Role` (`RoleId`, `Description`) VALUES
(1, 'Admin'),
(2, 'Full'),
(3, 'Writer'),
(4, 'Events');

--
-- Data dump for table `User`
--

INSERT INTO `User` (`UserId`, `Email`, `Username`, `FirstName`, `LastName`, `CountryId`, `Salt`, `PasswordHash`, `RegistrationToken`, `RegistrationDateTime`, `LastLoginDateTime`, `PasswordResetToken`, `PasswordResetDateTime`, `UserStateId`, `LoginAttempts`, `MobilePhone`, `Language`, `RoleId`) VALUES
(1, 'admin', 'admin', '', '', 1, NULL, 'c4ca4238a0b923820dcc509a6f75849b', NULL, '1449749758', '1449749758', NULL, NULL, 1, 0, NULL, 'en', 1);
(2, 'simone.vitale1987@gmail.com', 'simone.vitale', 'Simone', 'Vitale', 226, NULL, 'c4ca4238a0b923820dcc509a6f75849b', NULL, '1449749758', '1449749758', NULL, NULL, 1, 0, NULL, 'it', 1);

--
-- Data dump for table `Country`
--

INSERT INTO `Country` (CountryId, Code, Name) VALUES (1, 'US', 'United States');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (2, 'CA', 'Canada');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (3, 'AF', 'Afghanistan');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (4, 'AL', 'Albania');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (5, 'DZ', 'Algeria');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (6, 'DS', 'American Samoa');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (7, 'AD', 'Andorra');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (8, 'AO', 'Angola');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (9, 'AI', 'Anguilla');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (10, 'AQ', 'Antarctica');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (11, 'AG', 'Antigua and/or Barbuda');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (12, 'AR', 'Argentina');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (13, 'AM', 'Armenia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (14, 'AW', 'Aruba');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (15, 'AU', 'Australia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (16, 'AT', 'Austria');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (17, 'AZ', 'Azerbaijan');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (18, 'BS', 'Bahamas');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (19, 'BH', 'Bahrain');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (20, 'BD', 'Bangladesh');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (21, 'BB', 'Barbados');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (22, 'BY', 'Belarus');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (23, 'BE', 'Belgium');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (24, 'BZ', 'Belize');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (25, 'BJ', 'Benin');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (26, 'BM', 'Bermuda');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (27, 'BT', 'Bhutan');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (28, 'BO', 'Bolivia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (29, 'BA', 'Bosnia and Herzegovina');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (30, 'BW', 'Botswana');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (31, 'BV', 'Bouvet Island');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (32, 'BR', 'Brazil');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (33, 'IO', 'British lndian Ocean Territory');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (34, 'BN', 'Brunei Darussalam');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (35, 'BG', 'Bulgaria');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (36, 'BF', 'Burkina Faso');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (37, 'BI', 'Burundi');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (38, 'KH', 'Cambodia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (39, 'CM', 'Cameroon');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (40, 'CV', 'Cape Verde');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (41, 'KY', 'Cayman Islands');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (42, 'CF', 'Central African Republic');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (43, 'TD', 'Chad');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (44, 'CL', 'Chile');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (45, 'CN', 'China');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (46, 'CX', 'Christmas Island');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (47, 'CC', 'Cocos (Keeling) Islands');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (48, 'CO', 'Colombia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (49, 'KM', 'Comoros');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (50, 'CG', 'Congo');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (51, 'CK', 'Cook Islands');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (52, 'CR', 'Costa Rica');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (53, 'HR', 'Croatia (Hrvatska)');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (54, 'CU', 'Cuba');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (55, 'CY', 'Cyprus');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (56, 'CZ', 'Czech Republic');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (57, 'DK', 'Denmark');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (58, 'DJ', 'Djibouti');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (59, 'DM', 'Dominica');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (60, 'DO', 'Dominican Republic');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (61, 'TP', 'East Timor');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (62, 'EC', 'Ecuador');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (63, 'EG', 'Egypt');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (64, 'SV', 'El Salvador');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (65, 'GQ', 'Equatorial Guinea');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (66, 'ER', 'Eritrea');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (67, 'EE', 'Estonia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (68, 'ET', 'Ethiopia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (69, 'FK', 'Falkland Islands (Malvinas)');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (70, 'FO', 'Faroe Islands');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (71, 'FJ', 'Fiji');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (72, 'FI', 'Finland');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (73, 'FR', 'France');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (74, 'FX', 'France, Metropolitan');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (75, 'GF', 'French Guiana');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (76, 'PF', 'French Polynesia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (77, 'TF', 'French Southern Territories');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (78, 'GA', 'Gabon');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (79, 'GM', 'Gambia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (80, 'GE', 'Georgia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (81, 'DE', 'Germany');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (82, 'GH', 'Ghana');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (83, 'GI', 'Gibraltar');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (84, 'GR', 'Greece');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (85, 'GL', 'Greenland');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (86, 'GD', 'Grenada');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (87, 'GP', 'Guadeloupe');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (88, 'GU', 'Guam');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (89, 'GT', 'Guatemala');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (90, 'GN', 'Guinea');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (91, 'GW', 'Guinea-Bissau');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (92, 'GY', 'Guyana');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (93, 'HT', 'Haiti');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (94, 'HM', 'Heard and Mc Donald Islands');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (95, 'HN', 'Honduras');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (96, 'HK', 'Hong Kong');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (97, 'HU', 'Hungary');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (98, 'IS', 'Iceland');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (99, 'IN', 'India');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (100, 'ID', 'Indonesia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (101, 'IR', 'Iran (Islamic Republic of)');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (102, 'IQ', 'Iraq');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (103, 'IE', 'Ireland');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (104, 'IL', 'Israel');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (105, 'IT', 'Italy');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (106, 'CI', 'Ivory Coast');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (107, 'JM', 'Jamaica');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (108, 'JP', 'Japan');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (109, 'JO', 'Jordan');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (110, 'KZ', 'Kazakhstan');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (111, 'KE', 'Kenya');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (112, 'KI', 'Kiribati');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (113, 'KP', 'Korea, Democratic People''s Republic of');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (114, 'KR', 'Korea, Republic of');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (115, 'XK', 'Kosovo');

INSERT INTO `Country` (CountryId, Code, Name) VALUES (116, 'KW', 'Kuwait');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (117, 'KG', 'Kyrgyzstan');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (118, 'LA', 'Lao People''s Democratic Republic');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (119, 'LV', 'Latvia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (120, 'LB', 'Lebanon');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (121, 'LS', 'Lesotho');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (122, 'LR', 'Liberia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (123, 'LY', 'Libyan Arab Jamahiriya');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (124, 'LI', 'Liechtenstein');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (125, 'LT', 'Lithuania');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (126, 'LU', 'Luxembourg');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (127, 'MO', 'Macau');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (128, 'MK', 'Macedonia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (129, 'MG', 'Madagascar');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (130, 'MW', 'Malawi');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (131, 'MY', 'Malaysia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (132, 'MV', 'Maldives');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (133, 'ML', 'Mali');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (134, 'MT', 'Malta');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (135, 'MH', 'Marshall Islands');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (136, 'MQ', 'Martinique');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (137, 'MR', 'Mauritania');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (138, 'MU', 'Mauritius');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (139, 'TY', 'Mayotte');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (140, 'MX', 'Mexico');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (141, 'FM', 'Micronesia, Federated States of');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (142, 'MD', 'Moldova, Republic of');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (143, 'MC', 'Monaco');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (144, 'MN', 'Mongolia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (145, 'ME', 'Montenegro');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (146, 'MS', 'Montserrat');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (147, 'MA', 'Morocco');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (148, 'MZ', 'Mozambique');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (149, 'MM', 'Myanmar');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (150, 'NA', 'Namibia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (151, 'NR', 'Nauru');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (152, 'NP', 'Nepal');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (153, 'NL', 'Netherlands');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (154, 'AN', 'Netherlands Antilles');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (155, 'NC', 'New Caledonia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (156, 'NZ', 'New Zealand');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (157, 'NI', 'Nicaragua');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (158, 'NE', 'Niger');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (159, 'NG', 'Nigeria');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (160, 'NU', 'Niue');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (161, 'NF', 'Norfork Island');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (162, 'MP', 'Northern Mariana Islands');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (163, 'NO', 'Norway');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (164, 'OM', 'Oman');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (165, 'PK', 'Pakistan');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (166, 'PW', 'Palau');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (167, 'PA', 'Panama');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (168, 'PG', 'Papua New Guinea');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (169, 'PY', 'Paraguay');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (170, 'PE', 'Peru');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (171, 'PH', 'Philippines');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (172, 'PN', 'Pitcairn');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (173, 'PL', 'Poland');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (174, 'PT', 'Portugal');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (175, 'PR', 'Puerto Rico');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (176, 'QA', 'Qatar');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (177, 'RE', 'Reunion');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (178, 'RO', 'Romania');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (179, 'RU', 'Russian Federation');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (180, 'RW', 'Rwanda');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (181, 'KN', 'Saint Kitts and Nevis');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (182, 'LC', 'Saint Lucia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (183, 'VC', 'Saint Vincent and the Grenadines');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (184, 'WS', 'Samoa');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (185, 'SM', 'San Marino');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (186, 'ST', 'Sao Tome and Principe');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (187, 'SA', 'Saudi Arabia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (188, 'SN', 'Senegal');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (189, 'RS', 'Serbia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (190, 'SC', 'Seychelles');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (191, 'SL', 'Sierra Leone');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (192, 'SG', 'Singapore');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (193, 'SK', 'Slovakia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (194, 'SI', 'Slovenia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (195, 'SB', 'Solomon Islands');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (196, 'SO', 'Somalia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (197, 'ZA', 'South Africa');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (198, 'GS', 'South Georgia South Sandwich Islands');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (199, 'ES', 'Spain');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (200, 'LK', 'Sri Lanka');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (201, 'SH', 'St. Helena');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (202, 'PM', 'St. Pierre and Miquelon');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (203, 'SD', 'Sudan');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (204, 'SR', 'Suriname');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (205, 'SJ', 'Svalbarn and Jan Mayen Islands');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (206, 'SZ', 'Swaziland');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (207, 'SE', 'Sweden');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (208, 'CH', 'Switzerland');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (209, 'SY', 'Syrian Arab Republic');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (210, 'TW', 'Taiwan');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (211, 'TJ', 'Tajikistan');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (212, 'TZ', 'Tanzania, United Republic of');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (213, 'TH', 'Thailand');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (214, 'TG', 'Togo');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (215, 'TK', 'Tokelau');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (216, 'TO', 'Tonga');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (217, 'TT', 'Trinidad and Tobago');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (218, 'TN', 'Tunisia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (219, 'TR', 'Turkey');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (220, 'TM', 'Turkmenistan');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (221, 'TC', 'Turks and Caicos Islands');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (222, 'TV', 'Tuvalu');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (223, 'UG', 'Uganda');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (224, 'UA', 'Ukraine');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (225, 'AE', 'United Arab Emirates');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (226, 'GB', 'United Kingdom');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (227, 'UM', 'United States minor outlying islands');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (228, 'UY', 'Uruguay');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (229, 'UZ', 'Uzbekistan');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (230, 'VU', 'Vanuatu');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (231, 'VA', 'Vatican City State');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (232, 'VE', 'Venezuela');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (233, 'VN', 'Vietnam');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (234, 'VG', 'Virgin Islands (British)');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (235, 'VI', 'Virgin Islands (U.S.)');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (236, 'WF', 'Wallis and Futuna Islands');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (237, 'EH', 'Western Sahara');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (238, 'YE', 'Yemen');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (239, 'YU', 'Yugoslavia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (240, 'ZR', 'Zaire');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (241, 'ZM', 'Zambia');
INSERT INTO `Country` (CountryId, Code, Name) VALUES (242, 'ZW', 'Zimbabwe');

--
-- Data dump for table `Event`
--

INSERT INTO `Event` (`Title`, `Image`, `Description`, `CreationDateTime`, `DateTime`, `FacebookLink`, `YouTubeLink`, `FlickrLink`, `Statistics`, `Language`, `Published`, `UserId`, `AuthorId`, `LocationId`) VALUES
('Sample Event 1 - en', '', 'Sample Event Description', '', '', 'Facebook Link', 'YouTube Link', 'FlickR Link', '', 'en', 1, 1, 1, 1),
('Sample Event 2 - en', '', 'Sample Event Description', '', '', 'Facebook Link', 'YouTube Link', 'FlickR Link', '', 'en', 1, 1, 1, 1),
('Sample Event 3 - en', '', 'Sample Event Description', '', '', 'Facebook Link', 'YouTube Link', 'FlickR Link', '', 'en', 1, 1, 1, 1),
('Sample Event 4 - en', '', 'Sample Event Description', '', '', 'Facebook Link', 'YouTube Link', 'FlickR Link', '', 'en', 1, 1, 1, 1),
('Sample Event 5 - en', '', 'Sample Event Description', '', '', 'Facebook Link', 'YouTube Link', 'FlickR Link', '', 'en', 1, 1, 1, 1),
('Sample Event 6 - en', '', 'Sample Event Description', '', '', 'Facebook Link', 'YouTube Link', 'FlickR Link', '', 'en', 1, 1, 1, 1),
('Sample Event 7 - en', '', 'Sample Event Description', '', '', 'Facebook Link', 'YouTube Link', 'FlickR Link', '', 'en', 1, 1, 1, 1),
('Sample Event 8 - en', '', 'Sample Event Description', '', '', 'Facebook Link', 'YouTube Link', 'FlickR Link', '', 'en', 1, 1, 1, 1),
('Sample Event 9 - en', '', 'Sample Event Description', '', '', 'Facebook Link', 'YouTube Link', 'FlickR Link', '', 'en', 1, 1, 1, 1),
('Sample Event 10 - en', '', 'Sample Event Description', '', '', 'Facebook Link', 'YouTube Link', 'FlickR Link', '', 'en', 1, 1, 1, 1),
('Sample Event 1 in ita', '', 'Sample Event Description', '', '', 'Facebook Link', 'YouTube Link', 'FlickR Link', '', 'it', 1, 1, 1, 1),
('Sample Event 2 in ita', '', 'Sample Event Description', '', '', 'Facebook Link', 'YouTube Link', 'FlickR Link', '', 'it', 1, 1, 1, 1),
('Sample Event 3 in ita', '', 'Sample Event Description', '', '', 'Facebook Link', 'YouTube Link', 'FlickR Link', '', 'it', 1, 1, 1, 1),
('Sample Event 4 in ita', '', 'Sample Event Description', '', '', 'Facebook Link', 'YouTube Link', 'FlickR Link', '', 'it', 1, 1, 1, 1),
('Sample Event 5 in ita', '', 'Sample Event Description', '', '', 'Facebook Link', 'YouTube Link', 'FlickR Link', '', 'it', 1, 1, 1, 1),
('Sample Event 6 in ita', '', 'Sample Event Description', '', '', 'Facebook Link', 'YouTube Link', 'FlickR Link', '', 'it', 1, 1, 1, 1),
('Sample Event 7 in ita', '', 'Sample Event Description', '', '', 'Facebook Link', 'YouTube Link', 'FlickR Link', '', 'it', 1, 1, 1, 1),
('Sample Event 8 in ita', '', 'Sample Event Description', '', '', 'Facebook Link', 'YouTube Link', 'FlickR Link', '', 'it', 1, 1, 1, 1),
('Sample Event 9 in ita', '', 'Sample Event Description', '', '', 'Facebook Link', 'YouTube Link', 'FlickR Link', '', 'it', 1, 1, 1, 1),
('Sample Event 10 in ita', '', 'Sample Event Description', '', '', 'Facebook Link', 'YouTube Link', 'FlickR Link', '', 'it', 1, 1, 1, 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
