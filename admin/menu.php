<?
IncludeModuleLangFile(__FILE__);

if ($APPLICATION->GetGroupRight("ammina.regions") >= "R") {
	$aMenu = array(
		"parent_menu" => (CModule::IncludeModule("sale") ? "global_menu_store" : "global_menu_services"),
		"section" => "ammina.regions",
		"sort" => 10000,
		"text" => GetMessage("AMMINA_REGIONS_MENU_TEXT"),
		"title" => GetMessage("AMMINA_REGIONS_MENU_TITLE"),
		"icon" => "AMMINA_REGIONS_menu_icon",
		"page_icon" => "AMMINA_REGIONS_page_icon",
		"items_id" => "menu_ammina_regions",
		"items" => array(
			array(
				"text" => GetMessage("AMMINA_REGIONS_MENU_FILIALS_TEXT"),
				"title" => GetMessage("AMMINA_REGIONS_MENU_FILIALS_TITLE"),
				"items_id" => "menu_ammina_regions_filials",
				"items" => array(
					array(
						"text" => GetMessage("AMMINA_REGIONS_MENU_DOMAIN_TEXT"),
						"title" => GetMessage("AMMINA_REGIONS_MENU_DOMAIN_TITLE"),
						"url" => "ammina.regions.domain.php?lang=" . LANGUAGE_ID,
						"more_url" => array(
							"ammina.regions.domain.edit.php",
						),
					),
					array(
						"text" => GetMessage("AMMINA_REGIONS_MENU_VARIABLE_TEXT"),
						"title" => GetMessage("AMMINA_REGIONS_MENU_VARIABLE_TITLE"),
						"url" => "ammina.regions.variable.php?lang=" . LANGUAGE_ID,
						"more_url" => array(
							"ammina.regions.variable.edit.php",
						),
					),
					array(
						"text" => GetMessage("AMMINA_REGIONS_MENU_PRICE_TEXT"),
						"title" => GetMessage("AMMINA_REGIONS_MENU_PRICE_TITLE"),
						"url" => "ammina.regions.price.php?lang=" . LANGUAGE_ID,
						"more_url" => array(
							"ammina.regions.price.edit.php",
						),
					),
				),
			),
			array(
				"text" => GetMessage("AMMINA_REGIONS_MENU_GEOCONTENT_TEXT"),
				"title" => GetMessage("AMMINA_REGIONS_MENU_GEOCONTENT_TITLE"),
				"items_id" => "menu_ammina_regions_geocontent",
				"items" => array(
					array(
						"text" => GetMessage("AMMINA_REGIONS_MENU_CONTENT_TYPES_TEXT"),
						"title" => GetMessage("AMMINA_REGIONS_MENU_CONTENT_TYPES_TITLE"),
						"url" => "ammina.regions.content.types.php?lang=" . LANGUAGE_ID,
						"more_url" => array(
							"ammina.regions.content.types.edit.php",
						),
					),
					array(
						"text" => GetMessage("AMMINA_REGIONS_MENU_CONTENT_TEXT"),
						"title" => GetMessage("AMMINA_REGIONS_MENU_CONTENT_TITLE"),
						"url" => "ammina.regions.content.php?lang=" . LANGUAGE_ID,
						"more_url" => array(
							"ammina.regions.content.edit.php",
						),
					),
				),
			),
			array(
				"text" => GetMessage("AMMINA_REGIONS_MENU_GEOIP_TEXT"),
				"title" => GetMessage("AMMINA_REGIONS_MENU_GEOIP_TITLE"),
				"items_id" => "menu_ammina_regions_geoip",
				"items" => array(
					array(
						"text" => GetMessage("AMMINA_REGIONS_MENU_COUNTRY_TEXT"),
						"title" => GetMessage("AMMINA_REGIONS_MENU_COUNTRY_TITLE"),
						"url" => "ammina.regions.country.php?lang=" . LANGUAGE_ID,
						"more_url" => array(
							"ammina.regions.country.edit.php",
						),
					),
					array(
						"text" => GetMessage("AMMINA_REGIONS_MENU_REGION_TEXT"),
						"title" => GetMessage("AMMINA_REGIONS_MENU_REGION_TITLE"),
						"url" => "ammina.regions.region.php?lang=" . LANGUAGE_ID,
						"more_url" => array(
							"ammina.regions.region.edit.php",
						),
					),
					array(
						"text" => GetMessage("AMMINA_REGIONS_MENU_CITY_TEXT"),
						"title" => GetMessage("AMMINA_REGIONS_MENU_CITY_TITLE"),
						"url" => "ammina.regions.city.php?lang=" . LANGUAGE_ID,
						"more_url" => array(
							"ammina.regions.city.edit.php",
							"ammina.regions.city.load.php",
						),
					),
					array(
						"text" => GetMessage("AMMINA_REGIONS_MENU_BLOCK_TEXT"),
						"title" => GetMessage("AMMINA_REGIONS_MENU_BLOCK_TITLE"),
						"url" => "ammina.regions.block.php?lang=" . LANGUAGE_ID,
						"more_url" => array(),
					),
					array(
						"text" => GetMessage("AMMINA_REGIONS_MENU_UPDATE_TEXT"),
						"title" => GetMessage("AMMINA_REGIONS_MENU_UPDATE_TITLE"),
						"url" => "ammina.regions.update.php?lang=" . LANGUAGE_ID,
						"more_url" => array(),
					),
				),
			),
		),
	);

	return $aMenu;
}
return false;
