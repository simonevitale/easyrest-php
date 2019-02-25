SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- --------------------------------------------------------

--
-- Table structure `Country`
--

CREATE TABLE IF NOT EXISTS `Country` (
  `CountryId` int(11) NOT NULL AUTO_INCREMENT,
  `Name` char(50) NOT NULL,
  `Code` char(3) NOT NULL,
  `TimeZones` json DEFAULT NULL,
  PRIMARY KEY (`CountryId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure `Log`
--

CREATE TABLE IF NOT EXISTS `Log` (
  `LogId` int(11) NOT NULL AUTO_INCREMENT,
  `Action` char(30) NOT NULL,
  `OrganizationName` char(50) DEFAULT NULL,
  `UserEmail` char(50) DEFAULT NULL,
  `Agent` varchar(30) NOT NULL,
  `AgentVersion` varchar(5) NOT NULL,
  `DateTime` char(20) DEFAULT NULL,
  `Ip` char(15) DEFAULT NULL,
  `Location` varchar(100) NOT NULL,
  PRIMARY KEY (`LogId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure `Role`
--

CREATE TABLE IF NOT EXISTS `Role` (
  `RoleId` int(11) NOT NULL AUTO_INCREMENT,
  `Name` char(30) NOT NULL,
  `Modules` char(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`RoleId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure `License`
--

CREATE TABLE IF NOT EXISTS `License` (
  `LicenseId` int(11) NOT NULL AUTO_INCREMENT,
  `Name` char(30) NOT NULL,
  PRIMARY KEY (`LicenseId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure `UserState`
--

CREATE TABLE IF NOT EXISTS `UserState` (
  `UserStateId` int(11) NOT NULL,
  `Description` text NOT NULL,
  PRIMARY KEY (`UserStateId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Data dump for table `UserState`
--

INSERT INTO `UserState` (`UserStateId`, `Description`) VALUES
(0, 'Not activated'),
(1, 'Active'),
(2, 'Suspended');

-- --------------------------------------------------------

--
-- Table structure `Language`
--

CREATE TABLE IF NOT EXISTS `Language` (
  `Name` char(20) NOT NULL,
  `Code` char(4) NOT NULL,
  PRIMARY KEY (`Name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure `User`
--

CREATE TABLE IF NOT EXISTS `User` (
  `UserId` int(11) NOT NULL AUTO_INCREMENT,
  `Email` char(50) NOT NULL,
  `Username` char(50) DEFAULT '',
  `FirstName` char(50) DEFAULT '',
  `LastName` char(50) DEFAULT '',
  `Image` char(255) DEFAULT '',
  `CountryId` int(11) NOT NULL,
  `TimeZone` int(11) NOT NULL DEFAULT '0',
  `Salt` char(50) DEFAULT NULL,
  `PasswordHash` char(32) DEFAULT NULL,
  `RegistrationToken` varchar(10) DEFAULT NULL,
  `RegistrationDateTime` varchar(20) NOT NULL,
  `LastLoginDateTime` varchar(20) NOT NULL,
  `PasswordResetToken` char(32) DEFAULT NULL,
  `PasswordResetDateTime` char(20) DEFAULT NULL,
  `UserStateId` int(11) DEFAULT '0',
  `LoginAttempts` int(11) DEFAULT '0',
  `MobilePhone` char(15) DEFAULT NULL,
  `Language` char(2) DEFAULT 'en',
  `PortalLanguage` varchar(2) NOT NULL DEFAULT 'en',
  `RoleId` int(11) NOT NULL DEFAULT '2',
  `LicenseId` int(11) NOT NULL DEFAULT '2',
  `RegistrationCode` varchar(25) NOT NULL DEFAULT '',
  `Organization` varchar(25) DEFAULT '',
  `Properties` json DEFAULT NULL,
  PRIMARY KEY (`UserId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Asset`
--

CREATE TABLE IF NOT EXISTS `Asset` (
  `AssetId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `CreationDateTime` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Version` int(11) NOT NULL DEFAULT '1',
  `IsPublic` tinyint(1) NOT NULL DEFAULT '0',
  `AssetTypeId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `AssetType`
--

CREATE TABLE IF NOT EXISTS `AssetType` (
  `AssetTypeId` int(11) NOT NULL,
  `Type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `File`
--

CREATE TABLE `File` (
  `FileId` int(11) NOT NULL,
  `FileTypeId` int(11) NOT NULL,
  `OwnerUserId` int(11) NOT NULL,
  `IsPublic` tinyint(1) NOT NULL,
  `OriginalFileName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `FileName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `filerole`
--

CREATE TABLE `FileRole` (
  `FileRoleId` int(11) NOT NULL,
  `Name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `filetype`
--

CREATE TABLE `FileType` (
  `FileTypeId` int(11) NOT NULL,
  `Name` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `AssetFile`
--

CREATE TABLE IF NOT EXISTS `AssetFile` (
  `AssetId` int(11) NOT NULL,
  `FileId` int(11) NOT NULL,
  `FileRoleId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure `Organization`
--

CREATE TABLE IF NOT EXISTS `Organization` (
  `OrganizationId` int(11) NOT NULL AUTO_INCREMENT,
  `Name` VARCHAR(30) NOT NULL,
  `Address1` char(50) DEFAULT NULL,
  `Address2` char(50) DEFAULT NULL,
  `PostCode` char(10) DEFAULT NULL,
  `City` char(50) DEFAULT NULL,
  `Country` char(50) DEFAULT NULL,
  `WebsiteLink` char(255) DEFAULT NULL,
  `FacebookLink` char(255) DEFAULT NULL,
  `TwitterLink` char(255) DEFAULT NULL,
  PRIMARY KEY (`OrganizationId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure `OrganizationTag`
--

CREATE TABLE IF NOT EXISTS `OrganizationTag` (
  `OrganizationId` int(11) NOT NULL,
  `TagId` int(11) NOT NULL,
  `RedirectedTagId` int(11) NOT NULL,
  PRIMARY KEY (`OrganizationId`, `TagId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure `OrganizationUser`
--

CREATE TABLE IF NOT EXISTS `OrganizationUser` (
  `OrganizationId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  PRIMARY KEY (`OrganizationId`, `UserId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure `Tag`
--

CREATE TABLE IF NOT EXISTS `Tag` (
  `TagId` int(11) NOT NULL AUTO_INCREMENT,
  `Value` char(20) NOT NULL,
  PRIMARY KEY (`TagId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure `Article`
--

CREATE TABLE IF NOT EXISTS `Article` (
  `ArticleId` int(11) NOT NULL AUTO_INCREMENT,
  `Title` char(50) DEFAULT '',
  `Category` char(20) DEFAULT '',
  `Image` char(255) DEFAULT '',
  `Description` text DEFAULT '',
  `CreationDateTime` char(20) DEFAULT '',
  `DateTime` char(20) DEFAULT '',
  `YouTubeLink` char(255) DEFAULT '',
  `FlickrLink` char(255) DEFAULT '',
  `TwitterLink` char(255) DEFAULT '',
  `Language` char(2) DEFAULT '',
  `Published` tinyint(1) DEFAULT '0',
  `UserId` int(11) NOT NULL,
  `AuthorId` int(11) DEFAULT NULL,
  PRIMARY KEY (`ArticleId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure `Author`
--

CREATE TABLE IF NOT EXISTS `Author` (
  `AuthorId` int(11) NOT NULL AUTO_INCREMENT,
  `UniqueName` char(10) DEFAULT '',
  `Name` char(30) DEFAULT '',
  `Image` char(255) DEFAULT '',
  `Active` tinyint(1) DEFAULT '1',
  `Description` text DEFAULT '',
  `UserId` int(11) NOT NULL,
  PRIMARY KEY (`AuthorId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure `Category`
--

CREATE TABLE IF NOT EXISTS `Category` (
  `CategoryId` int(11) NOT NULL AUTO_INCREMENT,
  `Name` char(20) NOT NULL,
  `Entity` char(20) DEFAULT '',
  PRIMARY KEY (`CategoryId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure `Event`
--

CREATE TABLE IF NOT EXISTS `Event` (
  `EventId` int(11) NOT NULL AUTO_INCREMENT,
  `Title` char(50) DEFAULT '',
  `Image` char(255) DEFAULT '',
  `Description` text DEFAULT '',
  `CreationDateTime` char(20) DEFAULT '',
  `DateTime` char(20) DEFAULT '',
  `FacebookLink` char(255) DEFAULT '',
  `YouTubeLink` char(255) DEFAULT '',
  `FlickrLink` char(255) DEFAULT '',
  `Statistics` text DEFAULT '',
  `Language` char(2) DEFAULT '',
  `Published` tinyint(1) DEFAULT '0',
  `UserId` int(11) NOT NULL,
  `AuthorId` int(11) DEFAULT NULL,
  `LocationId` int(11) DEFAULT NULL,
  PRIMARY KEY (`EventId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure `Location`
--

CREATE TABLE IF NOT EXISTS `Location` (
  `LocationId` int(11) NOT NULL AUTO_INCREMENT,
  `Name` char(30) DEFAULT NULL,
  `Address1` char(50) DEFAULT NULL,
  `Address2` char(50) DEFAULT NULL,
  `PostCode` char(10) DEFAULT NULL,
  `City` char(50) DEFAULT NULL,
  `Country` char(50) DEFAULT NULL,
  `Description` text,
  `Phone` char(15) DEFAULT NULL,
  `Email` char(50) DEFAULT NULL,
  `WebsiteLink` char(255) DEFAULT NULL,
  `FacebookLink` char(255) DEFAULT NULL,
  `FlickrLink` char(255) DEFAULT NULL,
  `Active` tinyint(1) DEFAULT '1',
  `UserId` int(11) DEFAULT NULL,
  PRIMARY KEY (`LocationId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
