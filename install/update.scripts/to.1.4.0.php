<?

if (IsModuleInstalled('ammina.regions')) {
	$arVal = \Ammina\Regions\VariableTable::getList(array("filter" => array("CODE" => "SYS_ORDER_PREFIX")))->fetch();
	if (!$arVal) {
		\Ammina\Regions\VariableTable::add(
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
	if ($updater->TableExists("am_regions_domain")) {
		global $DB;
		$arFields = $DB->GetTableFields("am_regions_domain");
		if (!isset($arFields['SITE_EXT'])) {
			$updater->Query(
				array(
					"MySQL" => "ALTER TABLE `am_regions_domain` ADD `SITE_EXT` text DEFAULT NULL AFTER `SITE_ID`;",
				)
			);
		}
		if (!isset($arFields['ORDER_PREFIX'])) {
			$updater->Query(
				array(
					"MySQL" => "ALTER TABLE `am_regions_domain` ADD `ORDER_PREFIX` varchar(64) DEFAULT NULL AFTER `PATHCODE`;",
				)
			);
		}
	}
}

?>