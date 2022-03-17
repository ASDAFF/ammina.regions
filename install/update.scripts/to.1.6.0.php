<?
/**
 * ОБРАТИТЕ ВНИМАНИЕ!<br>
 * Обновление вносит значительные изменения в структуру таблиц базы данных. Обязательно перед обновлением модуля создайте резервную копию сайта (либо дамп таблиц модуля am_multiregions_* и архив файлов модуля /bitrix/modules/kit.multiregions/)<br><br>
 * 1. Добавлена поддержка множества языковых названий для стран, регионов, городов, доменов, геозависимого контента и переменных доменов.<br>
 * 2. В настройках модуля добавлена возможность выбора доступных языков для перевода указанных выше блоков данных<br>
 * 3. В настройках модуля добавлены синонимы языков (с применением цепочек синонимов), для подстановки языковых названий и значений из доступных значений языков-синонимов.
 */
if (IsModuleInstalled('kit.multiregions')) {
	if (is_dir(dirname(__FILE__) . '/install/components'))
		$updater->CopyFiles("install/components", "components/");

	if (is_dir(dirname(__FILE__) . '/install/themes/.default'))
		$updater->CopyFiles("install/themes/.default", "themes/.default/");
}
global $DB;
if ($updater->CanUpdateDatabase()) {
	if (!$updater->TableExists("am_multiregions_country_lang")) {
		$updater->Query(
			array(
				"MySQL" => "CREATE TABLE IF NOT EXISTS `am_multiregions_country_lang` (`ID` int(11) NOT NULL AUTO_INCREMENT,`COUNTRY_ID` int(11) NOT NULL,`LID` char(2) NOT NULL,`NAME` varchar(255) NOT NULL,PRIMARY KEY (`ID`),KEY `IX_COUNTRYLID` (`COUNTRY_ID`,`LID`),KEY `IX_LIDNAME` (`LID`,`NAME`));",
			)
		);
		$updater->Query(
			array(
				"MySQL" => "INSERT INTO am_multiregions_country_lang (`COUNTRY_ID`, `LID`, `NAME`) (SELECT `ID`, 'en', `NAME_EN` FROM `am_multiregions_country`);",
			)
		);
	}
	if ($updater->TableExists("am_multiregions_country")) {
		$arFields = $DB->GetTableFields("am_multiregions_country");
		if (!isset($arFields['NAME'])) {
			$updater->Query(
				array(
					"MySQL" => "ALTER TABLE `am_multiregions_country` CHANGE `NAME_RU` `NAME` VARCHAR(255) NULL DEFAULT NULL;",
				)
			);
		}
		if (!isset($arFields['NAME_EN'])) {
			$updater->Query(
				array(
					"MySQL" => "ALTER TABLE `am_multiregions_country` DROP `NAME_EN`;",
				)
			);
		}
	}

	if (!$updater->TableExists("am_multiregions_region_lang")) {
		$updater->Query(
			array(
				"MySQL" => "CREATE TABLE IF NOT EXISTS `am_multiregions_region_lang` (`ID` int(11) NOT NULL AUTO_INCREMENT,`REGION_ID` int(11) NOT NULL,`LID` char(2) NOT NULL,`NAME` varchar(255) NOT NULL,PRIMARY KEY (`ID`),KEY `IX_REGIONLID` (`REGION_ID`,`LID`),KEY `IX_LIDNAME` (`LID`,`NAME`));",
			)
		);
		$updater->Query(
			array(
				"MySQL" => "INSERT INTO am_multiregions_region_lang (`REGION_ID`, `LID`, `NAME`) (SELECT `ID`, 'en', `NAME_EN` FROM `am_multiregions_region`);",
			)
		);
	}
	if ($updater->TableExists("am_multiregions_region")) {
		$arFields = $DB->GetTableFields("am_multiregions_region");
		if (!isset($arFields['NAME'])) {
			$updater->Query(
				array(
					"MySQL" => "ALTER TABLE `am_multiregions_region` CHANGE `NAME_RU` `NAME` VARCHAR(255) NULL DEFAULT NULL;",
				)
			);
		}
		if (!isset($arFields['NAME_EN'])) {
			$updater->Query(
				array(
					"MySQL" => "ALTER TABLE `am_multiregions_region` DROP `NAME_EN`;",
				)
			);
		}
	}

	if (!$updater->TableExists("am_multiregions_city_lang")) {
		$updater->Query(
			array(
				"MySQL" => "CREATE TABLE IF NOT EXISTS `am_multiregions_city_lang` (`ID` int(11) NOT NULL AUTO_INCREMENT,`CITY_ID` int(11) NOT NULL,`LID` char(2) NOT NULL,`NAME` varchar(255) NOT NULL,PRIMARY KEY (`ID`),KEY `IX_CITYLID` (`CITY_ID`,`LID`),KEY `IX_LIDNAME` (`LID`,`NAME`));",
			)
		);
		$updater->Query(
			array(
				"MySQL" => "INSERT INTO am_multiregions_city_lang (`CITY_ID`, `LID`, `NAME`) (SELECT `ID`, 'en', `NAME_EN` FROM `am_multiregions_city`);",
			)
		);
	}
	if ($updater->TableExists("am_multiregions_city")) {
		$arFields = $DB->GetTableFields("am_multiregions_city");
		if (!isset($arFields['NAME'])) {
			$updater->Query(
				array(
					"MySQL" => "ALTER TABLE `am_multiregions_city` CHANGE `NAME_RU` `NAME` VARCHAR(255) NULL DEFAULT NULL;",
				)
			);
		}
		if (!isset($arFields['NAME_EN'])) {
			$updater->Query(
				array(
					"MySQL" => "ALTER TABLE `am_multiregions_city` DROP `NAME_EN`;",
				)
			);
		}
	}

	if ($updater->TableExists("am_multiregions_domain")) {
		$arFields = $DB->GetTableFields("am_multiregions_domain");
		if (!isset($arFields['NAME_LANG'])) {
			$updater->Query(
				array(
					"MySQL" => "ALTER TABLE `am_multiregions_domain` ADD `NAME_LANG` LONGTEXT NULL DEFAULT NULL AFTER `NAME`;",
				)
			);
		}
	}

	if ($updater->TableExists("am_multiregions_domain_var")) {
		$arFields = $DB->GetTableFields("am_multiregions_domain_var");
		if (!isset($arFields['VALUE_LANG'])) {
			$updater->Query(
				array(
					"MySQL" => "ALTER TABLE `am_multiregions_domain_var` ADD `VALUE_LANG` LONGTEXT NULL DEFAULT NULL AFTER `VALUE`;",
				)
			);
		}
	}

	if ($updater->TableExists("am_multiregions_content")) {
		$arFields = $DB->GetTableFields("am_multiregions_content");
		if (!isset($arFields['CONTENT_LANG'])) {
			$updater->Query(
				array(
					"MySQL" => "ALTER TABLE `am_multiregions_content` ADD `CONTENT_LANG` LONGTEXT NULL DEFAULT NULL AFTER `CONTENT`;",
				)
			);
		}
	}

}

?>