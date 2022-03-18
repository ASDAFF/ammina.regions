<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("AMMINA_COMPONENT_REGIONS_SELECTOR_NAME"),
	"DESCRIPTION" => GetMessage("AMMINA_COMPONENT_REGIONS_SELECTOR_DESCRIPTION"),
	"ICON" => "/images/icon.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 100,
	"PATH" => array(
		"ID" => "ammina",
		"NAME" => GetMessage("AMMINA_COMPONENT_REGIONS_SELECTOR_PATH_MAINGROUP_NAME"),
		"CHILD" => array(
			"ID" => "regions",
			"NAME" => GetMessage("AMMINA_COMPONENT_REGIONS_SELECTOR_PATH_REGIONS_NAME"),
			"SORT" => 10000
		)
	),
);
