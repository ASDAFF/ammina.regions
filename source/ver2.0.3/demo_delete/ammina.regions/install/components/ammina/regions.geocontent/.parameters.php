<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

if (!Loader::includeModule('ammina.regions')) {
	return;
}

$arContentTypes = array();
$rContentTypes = \Ammina\Regions\ContentTypesTable::getList(array(
	"order" => array("NAME" => "ASC"),
));
while ($ar = $rContentTypes->fetch()) {
	$arContentTypes[$ar['ID']] = "[" . $ar['ID'] . "] " . $ar['NAME'];
}

$arComponentParameters = array(
	"GROUPS" => array(),
	"PARAMETERS" => array(
		"CONTENT_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage('AMMINA_COMPONENT_REGIONS_CONTENT_TYPE'),
			"TYPE" => "LIST",
			"VALUES" => $arContentTypes,
		),
		"SET_TAG_IDENT" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage('AMMINA_COMPONENT_REGIONS_SET_TAG_IDENT'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"SET_TAG_TYPE"=>array(
			"PARENT" => "BASE",
			"NAME" => GetMessage('AMMINA_COMPONENT_REGIONS_SET_TAG_TYPE'),
			"TYPE" => "STRING",
			"VALUES" => "span",
		),
		"IP" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("AMMINA_COMPONENT_REGIONS_IP"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"CACHE_TIME" => Array("DEFAULT" => "86400"),
	),
);
