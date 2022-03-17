<?
IncludeModuleLangFile(__FILE__);

if ($APPLICATION->GetGroupRight("kit.multiregions") >= "R") {
	$aMenu = array(
		"parent_menu" => (CModule::IncludeModule("sale") ? "global_menu_store" : "global_menu_services"),
		"section" => "kit.multiregions",
		"sort" => 10000,
		"text" => GetMessage("KIT_MULTIREGIONS_MENU_TEXT"),
		"title" => GetMessage("KIT_MULTIREGIONS_MENU_TITLE"),
		"icon" => "KIT_MULTIREGIONS_menu_icon",
		"page_icon" => "KIT_MULTIREGIONS_page_icon",
		"items_id" => "menu_kit_multiregions",
		"items" => array(
			array(
				"text" => GetMessage("KIT_MULTIREGIONS_MENU_FILIALS_TEXT"),
				"title" => GetMessage("KIT_MULTIREGIONS_MENU_FILIALS_TITLE"),
				"items_id" => "menu_kit_multiregions_filials",
				"items" => array(
					array(
						"text" => GetMessage("KIT_MULTIREGIONS_MENU_DOMAIN_TEXT"),
						"title" => GetMessage("KIT_MULTIREGIONS_MENU_DOMAIN_TITLE"),
						"url" => "kit.multiregions.domain.php?lang=" . LANGUAGE_ID,
						"more_url" => array(
							"kit.multiregions.domain.edit.php",
						),
					),
					array(
						"text" => GetMessage("KIT_MULTIREGIONS_MENU_VARIABLE_TEXT"),
						"title" => GetMessage("KIT_MULTIREGIONS_MENU_VARIABLE_TITLE"),
						"url" => "kit.multiregions.variable.php?lang=" . LANGUAGE_ID,
						"more_url" => array(
							"kit.multiregions.variable.edit.php",
						),
					),
					array(
						"text" => GetMessage("KIT_MULTIREGIONS_MENU_PRICE_TEXT"),
						"title" => GetMessage("KIT_MULTIREGIONS_MENU_PRICE_TITLE"),
						"url" => "kit.multiregions.price.php?lang=" . LANGUAGE_ID,
						"more_url" => array(
							"kit.multiregions.price.edit.php",
						),
					),
				),
			),
			array(
				"text" => GetMessage("KIT_MULTIREGIONS_MENU_GEOCONTENT_TEXT"),
				"title" => GetMessage("KIT_MULTIREGIONS_MENU_GEOCONTENT_TITLE"),
				"items_id" => "menu_kit_multiregions_geocontent",
				"items" => array(
					array(
						"text" => GetMessage("KIT_MULTIREGIONS_MENU_CONTENT_TYPES_TEXT"),
						"title" => GetMessage("KIT_MULTIREGIONS_MENU_CONTENT_TYPES_TITLE"),
						"url" => "kit.multiregions.content.types.php?lang=" . LANGUAGE_ID,
						"more_url" => array(
							"kit.multiregions.content.types.edit.php",
						),
					),
					array(
						"text" => GetMessage("KIT_MULTIREGIONS_MENU_CONTENT_TEXT"),
						"title" => GetMessage("KIT_MULTIREGIONS_MENU_CONTENT_TITLE"),
						"url" => "kit.multiregions.content.php?lang=" . LANGUAGE_ID,
						"more_url" => array(
							"kit.multiregions.content.edit.php",
						),
					),
				),
			),
			array(
				"text" => GetMessage("KIT_MULTIREGIONS_MENU_GEOIP_TEXT"),
				"title" => GetMessage("KIT_MULTIREGIONS_MENU_GEOIP_TITLE"),
				"items_id" => "menu_kit_multiregions_geoip",
				"items" => array(
					array(
						"text" => GetMessage("KIT_MULTIREGIONS_MENU_COUNTRY_TEXT"),
						"title" => GetMessage("KIT_MULTIREGIONS_MENU_COUNTRY_TITLE"),
						"url" => "kit.multiregions.country.php?lang=" . LANGUAGE_ID,
						"more_url" => array(
							"kit.multiregions.country.edit.php",
						),
					),
					array(
						"text" => GetMessage("KIT_MULTIREGIONS_MENU_REGION_TEXT"),
						"title" => GetMessage("KIT_MULTIREGIONS_MENU_REGION_TITLE"),
						"url" => "kit.multiregions.region.php?lang=" . LANGUAGE_ID,
						"more_url" => array(
							"kit.multiregions.region.edit.php",
						),
					),
					array(
						"text" => GetMessage("KIT_MULTIREGIONS_MENU_CITY_TEXT"),
						"title" => GetMessage("KIT_MULTIREGIONS_MENU_CITY_TITLE"),
						"url" => "kit.multiregions.city.php?lang=" . LANGUAGE_ID,
						"more_url" => array(
							"kit.multiregions.city.edit.php",
							"kit.multiregions.city.load.php",
						),
					),
					array(
						"text" => GetMessage("KIT_MULTIREGIONS_MENU_BLOCK_TEXT"),
						"title" => GetMessage("KIT_MULTIREGIONS_MENU_BLOCK_TITLE"),
						"url" => "kit.multiregions.block.php?lang=" . LANGUAGE_ID,
						"more_url" => array(),
					),
					array(
						"text" => GetMessage("KIT_MULTIREGIONS_MENU_UPDATE_TEXT"),
						"title" => GetMessage("KIT_MULTIREGIONS_MENU_UPDATE_TITLE"),
						"url" => "kit.multiregions.update.php?lang=" . LANGUAGE_ID,
						"more_url" => array(),
					),
				),
			),
		),
	);

	return $aMenu;
}
return false;
