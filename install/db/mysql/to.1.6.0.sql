CREATE TABLE IF NOT EXISTS `am_multiregions_country_lang` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `COUNTRY_ID` int(11) NOT NULL,
  `LID` char(2) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_COUNTRYLID` (`COUNTRY_ID`,`LID`),
  KEY `IX_LIDNAME` (`LID`,`NAME`)
);

INSERT INTO am_multiregions_country_lang (`COUNTRY_ID`, `LID`, `NAME`) (SELECT `ID`, 'en', `NAME_EN` FROM `am_multiregions_country`);

ALTER TABLE `am_multiregions_country` DROP `NAME_EN`, CHANGE `NAME_RU` `NAME` VARCHAR(255) NULL DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `am_multiregions_region_lang` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `REGION_ID` int(11) NOT NULL,
  `LID` char(2) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_REGIONLID` (`REGION_ID`,`LID`),
  KEY `IX_LIDNAME` (`LID`,`NAME`)
);

INSERT INTO am_multiregions_region_lang (`REGION_ID`, `LID`, `NAME`) (SELECT `ID`, 'en', `NAME_EN` FROM `am_multiregions_region`);

ALTER TABLE `am_multiregions_region` DROP `NAME_EN`, CHANGE `NAME_RU` `NAME` VARCHAR(255) NULL DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `am_multiregions_city_lang` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CITY_ID` int(11) NOT NULL,
  `LID` char(2) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_CITYLID` (`CITY_ID`,`LID`),
  KEY `IX_LIDNAME` (`LID`,`NAME`)
);

INSERT INTO am_multiregions_city_lang (`CITY_ID`, `LID`, `NAME`) (SELECT `ID`, 'en', `NAME_EN` FROM `am_multiregions_city`);

ALTER TABLE `am_multiregions_city` DROP `NAME_EN`, CHANGE `NAME_RU` `NAME` VARCHAR(255) NULL DEFAULT NULL;

ALTER TABLE `am_multiregions_domain` ADD `NAME_LANG` LONGTEXT NULL DEFAULT NULL AFTER `NAME`;

ALTER TABLE `am_multiregions_domain_var` ADD `VALUE_LANG` LONGTEXT NULL DEFAULT NULL AFTER `VALUE`;

ALTER TABLE `am_multiregions_content` ADD `CONTENT_LANG` LONGTEXT NULL DEFAULT NULL AFTER `CONTENT`;
