<?

use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
Bitrix\Main\Loader::includeModule('ammina.regions');
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/ammina.regions/prolog.php");

Loc::loadMessages(__FILE__);

$modulePermissions = $APPLICATION->GetGroupRight("ammina.regions");
if ($modulePermissions < "W") {
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

if (CAmminaRegions::getTestPeriodInfo() == \Bitrix\Main\Loader::MODULE_DEMO_EXPIRED) {
	require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
	CAdminMessage::ShowMessage(array("MESSAGE" => Loc::getMessage("AMMINA_REGIONS_SYS_MODULE_IS_DEMO_EXPIRED"), "HTML" => true));
	require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$isSaleModule = CAmminaRegions::isIMExists();

$sTableID = "tbl_ammina_regions_city";

$oSort = new CAdminSorting($sTableID, "BLOCK_START", "asc");
$arOrder = (amreg_strtoupper($by) === "ID" ? array($by => $order) : array($by => $order, "ID" => "ASC"));
$lAdmin = new CAdminUiList($sTableID, $oSort);

$arFilterFields = Array(
	"find_id",
	"find_country_id",
	"find_region_id",
	"find_city_name",
	"find_ip",
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array(
	"CITY.REGION.COUNTRY_ID" => $find_country_id,
	"CITY.REGION_ID" => $find_region_id,
	"?CITY.NAME" => $find_city_name,
	"IP" => $find_ip,
);

$arCountryList = array();
$rCountry = \Ammina\Regions\CountryTable::getList(array(
	"select" => array("ID", "NAME"),
	"order" => array("NAME" => "ASC"),
));
while ($arCountry = $rCountry->fetch()) {
	$arCountryList[$arCountry['ID']] = "[" . $arCountry['ID'] . "] " . $arCountry['NAME'];
}
$arRegionList = array();
$rRegion = \Ammina\Regions\RegionTable::getList(array(
	"select" => array("ID", "NAME"),
	"order" => array("NAME" => "ASC"),
));
while ($arRegion = $rRegion->fetch()) {
	$arRegionList[$arRegion['ID']] = "[" . $arRegion['ID'] . "] " . $arRegion['NAME'];
}
$filterFields = array(
	array(
		"id" => "CITY.REGION.COUNTRY_ID",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_COUNTRY_ID"),
		"filterable" => "",
		"type" => "list",
		"items" => $arCountryList,
		"default" => false,
		"params" => array("multiple" => "Y"),
	),
	array(
		"id" => "CITY.REGION_ID",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_REGION_ID"),
		"filterable" => "",
		"type" => "list",
		"items" => $arRegionList,
		"default" => false,
		"params" => array("multiple" => "Y"),
	),
	array(
		"id" => "CITY.NAME",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_CITY_NAME"),
		"filterable" => "?",
		"quickSearch" => "?",
		"default" => true,
	),
	array(
		"id" => "IP",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_IP"),
		"filterable" => "",
		"quickSearch" => "",
		"default" => true,
	),
);

$arFilter = array();
$lAdmin->AddFilter($filterFields, $arFilter);
if (isset($arFilter['IP'])) {
	$find_ipnum = false;
	if (amreg_strlen($arFilter['IP']) > 0) {
		$arIP = explode(".", trim($arFilter['IP']));
		if (count($arIP) == 4) {
			$find_ipnum = $arIP[0] * 16777216 + $arIP[1] * 65536 + $arIP[2] * 256 + $arIP[3];
		}
	}
	$arFilter['<=BLOCK_START'] = $find_ipnum;
	$arFilter['>=BLOCK_END'] = $find_ipnum;
	unset($arFilter['IP']);
}
$arHeader = array(
	array(
		"id" => "COUNTRY_ID",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_COUNTRY_ID"),
		"sort" => "CITY.REGION.COUNTRY.NAME",
		"default" => true,
	),
	array(
		"id" => "REGION_ID",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_REGION_ID"),
		"sort" => "CITY.REGION.NAME",
		"default" => true,
	),
	array(
		"id" => "CITY_ID",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_CITY_ID"),
		"sort" => "CITY.NAME",
		"default" => true,
	),
	array(
		"id" => "BLOCK_START_1",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_BLOCK_START_1"),
		"sort" => "BLOCK_START_1",
		"default" => false,
	),
	array(
		"id" => "BLOCK_START_2",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_BLOCK_START_2"),
		"sort" => "BLOCK_START_2",
		"default" => false,
	),
	array(
		"id" => "BLOCK_START_3",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_BLOCK_START_3"),
		"sort" => "BLOCK_START_3",
		"default" => false,
	),
	array(
		"id" => "BLOCK_START_4",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_BLOCK_START_4"),
		"sort" => "BLOCK_START_4",
		"default" => false,
	),
	array(
		"id" => "BLOCK_END_1",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_BLOCK_END_1"),
		"sort" => "BLOCK_END_1",
		"default" => false,
	),
	array(
		"id" => "BLOCK_END_2",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_BLOCK_END_2"),
		"sort" => "BLOCK_END_2",
		"default" => false,
	),
	array(
		"id" => "BLOCK_END_3",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_BLOCK_END_3"),
		"sort" => "BLOCK_END_3",
		"default" => false,
	),
	array(
		"id" => "BLOCK_END_4",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_BLOCK_END_4"),
		"sort" => "BLOCK_END_4",
		"default" => false,
	),
	array(
		"id" => "BLOCK_START",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_BLOCK_START"),
		"sort" => "BLOCK_START",
		"default" => true,
	),
	array(
		"id" => "BLOCK_END",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_BLOCK_END"),
		"sort" => "BLOCK_END",
		"default" => true,
	),
);

$lAdmin->AddHeaders($arHeader);

$usePageNavigation = true;
$navyParams = array();
if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'excel') {
	$usePageNavigation = false;
} else {
	$navyParams = CDBResult::GetNavParams(CAdminUiResult::GetNavSize($sTableID));
	if ($navyParams['SHOW_ALL']) {
		$usePageNavigation = false;
	} else {
		$navyParams['PAGEN'] = (int)$navyParams['PAGEN'];
		$navyParams['SIZEN'] = (int)$navyParams['SIZEN'];
	}
}
$getListParams = array(
	"order" => $arOrder,
	"filter" => $arFilter,
	"select" => array("*", "CITY_NAME" => "CITY.NAME", "REGION_ID" => "CITY.REGION_ID", "REGION_NAME" => "CITY.REGION.NAME", "COUNTRY_ID" => "CITY.REGION.COUNTRY_ID", "COUNTRY_NAME" => "CITY.REGION.COUNTRY.NAME"));


if ($usePageNavigation) {
	$getListParams['limit'] = $navyParams['SIZEN'];
	$getListParams['offset'] = $navyParams['SIZEN'] * ($navyParams['PAGEN'] - 1);
}
$totalCount = 0;
$totalPages = 0;
if ($usePageNavigation) {
	$totalCount = \Ammina\Regions\BlockTable::getCount($getListParams['filter']);
	if ($totalCount > 0) {
		$totalPages = ceil($totalCount / $navyParams['SIZEN']);
		if ($navyParams['PAGEN'] > $totalPages)
			$navyParams['PAGEN'] = $totalPages;
	} else {
		$navyParams['PAGEN'] = 1;
	}
	$getListParams['limit'] = $navyParams['SIZEN'];
	$getListParams['offset'] = $navyParams['SIZEN'] * ($navyParams['PAGEN'] - 1);
}

$rsItems = \Ammina\Regions\BlockTable::getList($getListParams);
$rsItems = new CAdminUiResult($rsItems, $sTableID);
if ($usePageNavigation) {
	$rsItems->NavStart($getListParams['limit'], $navyParams['SHOW_ALL'], $navyParams['PAGEN']);
	$rsItems->NavRecordCount = $totalCount;
	$rsItems->NavPageCount = $totalPages;
	$rsItems->NavPageNomer = $navyParams['PAGEN'];
} else {
	$rsItems->NavStart();
}
$lAdmin->SetNavigationParams($rsItems);

while ($arData = $rsItems->NavNext()) {
	$row =& $lAdmin->AddRow($arData['ID'], $arData);
	$row->AddViewField("COUNTRY_ID", $arData['COUNTRY_ID'] > 0 ? '[' . $arData['COUNTRY_ID'] . '] <a href="/bitrix/admin/ammina.regions.country.edit.php?ID=' . $arData['COUNTRY_ID'] . '&lang=' . LANGUAGE_ID . '">' . $arData['COUNTRY_NAME'] . '</a>' : "");
	$row->AddViewField("REGION_ID", $arData['REGION_ID'] > 0 ? '[' . $arData['REGION_ID'] . '] <a href="/bitrix/admin/ammina.regions.region.edit.php?ID=' . $arData['REGION_ID'] . '&lang=' . LANGUAGE_ID . '">' . $arData['REGION_NAME'] . '</a>' : "");
	$row->AddViewField("CITY_ID", $arData['CITY_ID'] > 0 ? '[' . $arData['CITY_ID'] . '] <a href="/bitrix/admin/ammina.regions.city.edit.php?ID=' . $arData['CITY_ID'] . '&lang=' . LANGUAGE_ID . '">' . $arData['CITY_NAME'] . '</a>' : "");
	$ipStart = array();
	$ip = $arData['BLOCK_START'];
	$ipStart[0] = intval($ip / 16777216);
	$ip = $ip - $ipStart[0] * 16777216;
	$ipStart[1] = intval($ip / 65536);
	$ip = $ip - $ipStart[1] * 65536;
	$ipStart[2] = intval($ip / 256);
	$ip = $ip - $ipStart[2] * 256;
	$ipStart[3] = $ip;
	$ipStart = implode(".", $ipStart);
	$row->AddViewField("BLOCK_START", $ipStart);
	$ipEnd = array();
	$ip = $arData['BLOCK_END'];
	$ipEnd[0] = intval($ip / 16777216);
	$ip = $ip - $ipEnd[0] * 16777216;
	$ipEnd[1] = intval($ip / 65536);
	$ip = $ip - $ipEnd[1] * 65536;
	$ipEnd[2] = intval($ip / 256);
	$ip = $ip - $ipEnd[2] * 256;
	$ipEnd[3] = $ip;
	$ipEnd = implode(".", $ipEnd);
	$row->AddViewField("BLOCK_END", $ipEnd);

	$arActions = array();
	if ($modulePermissions >= "W") {
		/*$arActions[] = array(
			"ICON" => "view",
			"TEXT" => Loc::getMessage("MAIN_ADMIN_MENU_VIEW"),
			"DEFAULT" => true,
			"ACTION" => $lAdmin->ActionRedirect("ammina.regions.block.view.php?ID=" . $arData['ID'] . "&lang=" . LANGUAGE_ID),
		);
		*/
	}

	if (count($arActions) > 0) {
		$row->AddActions($arActions);
	}
}

$lAdmin->AddFooter(
	array(
		array("title" => Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $rsItems->SelectedRowsCount()),
		array("counter" => true, "title" => Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"), "value" => "0"),
	)
);

if ($modulePermissions >= "W") {
	$aContext = array();

	$lAdmin->AddAdminContextMenu($aContext);

	$lAdmin->AddGroupActionTable(Array());
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("AMMINA_REGIONS_PAGE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filterFields);

if (CAmminaRegions::getTestPeriodInfo() == \Bitrix\Main\Loader::MODULE_DEMO) {
	CAdminMessage::ShowMessage(array("MESSAGE" => Loc::getMessage("AMMINA_REGIONS_SYS_MODULE_IS_DEMO"), "HTML" => true));
} elseif (CAmminaRegions::getTestPeriodInfo() == \Bitrix\Main\Loader::MODULE_DEMO_EXPIRED) {
	CAdminMessage::ShowMessage(array("MESSAGE" => Loc::getMessage("AMMINA_REGIONS_SYS_MODULE_IS_DEMO_EXPIRED"), "HTML" => true));
}

$lAdmin->DisplayList();
CAmminaRegions::showSupportForm();
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");