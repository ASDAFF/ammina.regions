<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

if (!Loader::includeModule('ammina.regions')) {
	return;
}

$arComponentParameters = array(
	"GROUPS" => array(
		"MAIN_SETTINGS" => array(
			"NAME" => GetMessage("AMMINA_COMPONENT_REGIONS_MAIN_SETTINGS"),
		),
		"MOBILE_SETTINGS" => array(
			"NAME" => GetMessage("AMMINA_COMPONENT_REGIONS_MOBILE_SETTINGS"),
		),
	),
	"PARAMETERS" => array(
		"SEPARATE_SETTINGS_MOBILE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage('AMMINA_COMPONENT_REGIONS_SEPARATE_SETTINGS_MOBILE'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),

		"CHANGE_CITY_MANUAL" => array(
			"PARENT" => "MAIN_SETTINGS",
			"NAME" => GetMessage('AMMINA_COMPONENT_REGIONS_ALLOW_CHANGE_CITY_MANUAL'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"CITY_VERIFYCATION" => array(
			"PARENT" => "MAIN_SETTINGS",
			"NAME" => GetMessage('AMMINA_COMPONENT_REGIONS_CITY_VERIFYCATION'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"USE_GPS" => array(
			"PARENT" => "MAIN_SETTINGS",
			"NAME" => GetMessage('AMMINA_COMPONENT_REGIONS_USE_GPS'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"SHOW_CITY_TYPE" => array(
			"PARENT" => "MAIN_SETTINGS",
			"NAME" => GetMessage('AMMINA_COMPONENT_REGIONS_SHOW_CITY_TYPE'),
			"TYPE" => "LIST",
			"DEFAULT" => "R",
			"VALUES" => array(
				"R" => GetMessage('AMMINA_COMPONENT_REGIONS_SHOW_CITY_TYPE_R'),
				"F" => GetMessage('AMMINA_COMPONENT_REGIONS_SHOW_CITY_TYPE_F'),
				"D" => GetMessage('AMMINA_COMPONENT_REGIONS_SHOW_CITY_TYPE_D'),
			),
		),
		"SEARCH_CITY_TYPE" => array(
			"PARENT" => "MAIN_SETTINGS",
			"NAME" => GetMessage('AMMINA_COMPONENT_REGIONS_SEARCH_CITY_TYPE'),
			"TYPE" => "LIST",
			"DEFAULT" => "R",
			"VALUES" => array(
				"R" => GetMessage('AMMINA_COMPONENT_REGIONS_SEARCH_CITY_TYPE_R'),
				"Q" => GetMessage('AMMINA_COMPONENT_REGIONS_SEARCH_CITY_TYPE_Q'),
			),
		),
		"COUNT_SHOW_CITY" => array(
			"PARENT" => "MAIN_SETTINGS",
			"NAME" => GetMessage("AMMINA_COMPONENT_REGIONS_COUNT_SHOW_CITY"),
			"TYPE" => "STRING",
			"DEFAULT" => 24,
		),

		"MOBILE_CHANGE_CITY_MANUAL" => array(
			"PARENT" => "MOBILE_SETTINGS",
			"NAME" => GetMessage('AMMINA_COMPONENT_REGIONS_ALLOW_CHANGE_CITY_MANUAL'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"MOBILE_CITY_VERIFYCATION" => array(
			"PARENT" => "MOBILE_SETTINGS",
			"NAME" => GetMessage('AMMINA_COMPONENT_REGIONS_CITY_VERIFYCATION'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"MOBILE_USE_GPS" => array(
			"PARENT" => "MOBILE_SETTINGS",
			"NAME" => GetMessage('AMMINA_COMPONENT_REGIONS_USE_GPS'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"MOBILE_SHOW_CITY_TYPE" => array(
			"PARENT" => "MOBILE_SETTINGS",
			"NAME" => GetMessage('AMMINA_COMPONENT_REGIONS_SHOW_CITY_TYPE'),
			"TYPE" => "LIST",
			"DEFAULT" => "R",
			"VALUES" => array(
				"R" => GetMessage('AMMINA_COMPONENT_REGIONS_SHOW_CITY_TYPE_R'),
				"F" => GetMessage('AMMINA_COMPONENT_REGIONS_SHOW_CITY_TYPE_F'),
				"D" => GetMessage('AMMINA_COMPONENT_REGIONS_SHOW_CITY_TYPE_D'),
			),
		),
		"MOBILE_SEARCH_CITY_TYPE" => array(
			"PARENT" => "MOBILE_SETTINGS",
			"NAME" => GetMessage('AMMINA_COMPONENT_REGIONS_SEARCH_CITY_TYPE'),
			"TYPE" => "LIST",
			"DEFAULT" => "R",
			"VALUES" => array(
				"R" => GetMessage('AMMINA_COMPONENT_REGIONS_SEARCH_CITY_TYPE_R'),
				"Q" => GetMessage('AMMINA_COMPONENT_REGIONS_SEARCH_CITY_TYPE_Q'),
			),
		),
		"MOBILE_COUNT_SHOW_CITY" => array(
			"PARENT" => "MOBILE_SETTINGS",
			"NAME" => GetMessage("AMMINA_COMPONENT_REGIONS_COUNT_SHOW_CITY"),
			"TYPE" => "STRING",
			"DEFAULT" => 24,
		),

		"INCLUDE_JQUERY" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage('AMMINA_COMPONENT_REGIONS_INCLUDE_JQUERY'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"IP" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("AMMINA_COMPONENT_REGIONS_IP"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"PRIORITY_DOMAIN" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage('AMMINA_COMPONENT_REGIONS_PRIORITY_DOMAIN'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"ALLOW_REDIRECT" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage('AMMINA_COMPONENT_REGIONS_ALLOW_REDIRECT'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"CACHE_TIME" => Array("DEFAULT" => "86400"),
	),
);
