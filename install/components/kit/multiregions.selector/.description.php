<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("KIT_COMPONENT_MULTIREGIONS_SELECTOR_NAME"),
	"DESCRIPTION" => GetMessage("KIT_COMPONENT_MULTIREGIONS_SELECTOR_DESCRIPTION"),
	"ICON" => "/images/icon.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 100,
	"PATH" => array(
		"ID" => "kit",
		"NAME" => GetMessage("KIT_COMPONENT_MULTIREGIONS_SELECTOR_PATH_MAINGROUP_NAME"),
		"CHILD" => array(
			"ID" => "multiregions",
			"NAME" => GetMessage("KIT_COMPONENT_MULTIREGIONS_SELECTOR_PATH_MULTIREGIONS_NAME"),
			"SORT" => 10000
		)
	),
);
