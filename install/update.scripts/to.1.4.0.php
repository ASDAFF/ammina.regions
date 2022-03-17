<?

if (IsModuleInstalled('kit.multiregions')) {
	$arVal = \Kit\MultiRegions\VariableTable::getList(array("filter" => array("CODE" => "SYS_ORDER_PREFIX")))->fetch();
	if (!$arVal) {
		\Kit\MultiRegions\VariableTable::add(
			array(
				"NAME" => "Префикс заказов",
				"DESCRIPTION" => "Префикс заказов",
				"CODE" => "SYS_ORDER_PREFIX",
				"IS_SYSTEM" => "Y",
			)
		);
	}
}

if ($updater->CanUpdateDatabase()) {
	if ($updater->TableExists("am_multiregions_domain")) {
		global $DB;
		$arFields = $DB->GetTableFields("am_multiregions_domain");
		if (!isset($arFields['SITE_EXT'])) {
			$updater->Query(
				array(
					"MySQL" => "ALTER TABLE `am_multiregions_domain` ADD `SITE_EXT` text DEFAULT NULL AFTER `SITE_ID`;",
				)
			);
		}
		if (!isset($arFields['ORDER_PREFIX'])) {
			$updater->Query(
				array(
					"MySQL" => "ALTER TABLE `am_multiregions_domain` ADD `ORDER_PREFIX` varchar(64) DEFAULT NULL AFTER `PATHCODE`;",
				)
			);
		}
	}
}

?>