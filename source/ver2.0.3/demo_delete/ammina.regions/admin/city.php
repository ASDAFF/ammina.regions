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

$isSaleModule = CAmminaRegions::isIMExists();

$sTableID = "tbl_ammina_regions_city";

$oSort = new CAdminSorting($sTableID, "NAME", "asc");
$arOrder = (amreg_strtoupper($by) === "ID" ? array($by => $order) : array($by => $order, "ID" => "ASC"));
$lAdmin = new CAdminUiList($sTableID, $oSort);

$arCountryList = array();
$rCountry = \Ammina\Regions\CountryTable::getList(
	array(
		"select" => array("ID", "NAME"),
		"order" => array("NAME" => "ASC"),
	)
);
while ($arCountry = $rCountry->fetch()) {
	$arCountryList[$arCountry['ID']] = "[" . $arCountry['ID'] . "] " . $arCountry['NAME'];
}
$arRegionList = array();
$rRegion = \Ammina\Regions\RegionTable::getList(
	array(
		"select" => array("ID", "NAME", "COUNTRY_NAME" => "COUNTRY.NAME"),
		"order" => array("COUNTRY_NAME" => "ASC", "NAME" => "ASC"),
	)
);
while ($arRegion = $rRegion->fetch()) {
	$arRegionList[$arRegion['ID']] = "[" . $arRegion['ID'] . "] " . $arRegion['COUNTRY_NAME'] . ", " . $arRegion['NAME'];
}
$filterFields = array(
	array(
		"id" => "ID",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_ID"),
		"type" => "number",
		"filterable" => "",
	),
	array(
		"id" => "REGION.COUNTRY_ID",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_COUNTRY_ID"),
		"filterable" => "",
		"type" => "list",
		"items" => $arCountryList,
		"default" => true,
		"params" => array("multiple" => "Y"),
	),
	array(
		"id" => "REGION_ID",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_REGION_ID"),
		"filterable" => "",
		"type" => "list",
		"items" => $arRegionList,
		"default" => true,
		"params" => array("multiple" => "Y"),
	),
	array(
		"id" => "NAME",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_NAME"),
		"filterable" => "?",
		"quickSearch" => "?",
		"default" => true,
	),
);

$arFilter = array();
$lAdmin->AddFilter($filterFields, $arFilter);

if (($arID = $lAdmin->GroupAction()) && $modulePermissions >= "W") {
	if ($_REQUEST['action_target'] == 'selected') {
		$arID = array();
		$dbResultList = \Ammina\Regions\CityTable::getList(
			array(
				"order" => $arOrder,
				"filter" => $arFilter,
				"select" => array("ID")
			)
		);
		while ($arResult = $dbResultList->Fetch()) {
			$arID[] = $arResult['ID'];
		}
	}
	$arVarLocativeCityName = \Ammina\Regions\VariableTable::getList(
		array(
			"filter" => array(
				"CODE" => "SYS_LOCATIVE_CITY_NAME",
			),
		)
	)->fetch();
	$arVarLocativeCityRegionName = \Ammina\Regions\VariableTable::getList(
		array(
			"filter" => array(
				"CODE" => "SYS_LOCATIVE_CITY_REGION_NAME",
			),
		)
	)->fetch();
	foreach ($arID as $ID) {
		if (amreg_strlen($ID) <= 0) {
			continue;
		}

		switch ($_REQUEST['action']) {
			case "delete":
				@set_time_limit(0);
				$rRecord = \Ammina\Regions\CityTable::getList(
					array(
						"filter" => array("ID" => $ID),
						"select" => array("ID"),
					)
				);
				$arRecordOld = $rRecord->Fetch();
				$DB->StartTransaction();
				$rOperation = \Ammina\Regions\CityTable::delete($ID);
				if (!$rOperation->isSuccess()) {
					$DB->Rollback();
					if ($ex = $APPLICATION->GetException()) {
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					} else {
						$lAdmin->AddGroupError(Loc::getMessage("AMMINA_REGIONS_DELETE_ERROR"), $ID);
					}
				}
				$DB->Commit();
				break;
			case "favorite":
				@set_time_limit(0);
				$rRecord = \Ammina\Regions\CityTable::getList(
					array(
						"filter" => array("ID" => $ID),
						"select" => array("ID"),
					)
				);
				$arRecordOld = $rRecord->Fetch();
				$DB->StartTransaction();
				$rOperation = \Ammina\Regions\CityTable::update($ID, array("IS_FAVORITE" => "Y"));
				if (!$rOperation->isSuccess()) {
					$DB->Rollback();
					if ($ex = $APPLICATION->GetException()) {
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					} else {
						$lAdmin->AddGroupError(Loc::getMessage("AMMINA_REGIONS_FAVORITE_ERROR"), $ID);
					}
				}
				$DB->Commit();
				break;
			case "add_domain":
			case "add_domain_region":
				@set_time_limit(0);
				$bComplete = true;
				$arRecord = \Ammina\Regions\CityTable::getRowById($ID);
				$DB->StartTransaction();
				$arDomain = \Ammina\Regions\DomainTable::getList(
					array(
						"filter" => array(
							"CITY_ID" => $arRecord['ID'],
						),
					)
				)->fetch();
				if (!$arDomain) {
					$arFormatName = \Ammina\Regions\DomainVariableTable::getCityFormatInfo($arRecord['ID']);
					if (COption::GetOptionString("ammina.regions", "use_rus_domain", "N") == "Y") {
						$strDomain = $arFormatName['CITY_NAME'];
					} else {
						$strDomain = CUtil::translit(
							$arFormatName['CITY_NAME'],
							LANG,
							array(
								"max_len" => 100,
								"change_case" => 'L',
								"replace_space" => '-',
								"replace_other" => '-',
								"delete_repeat_replace" => true,
								"safe_chars" => '',
							)
						);
					}
					$arFieldsDomain = array(
						"NAME" => $arFormatName['CITY_NAME'],
						"ACTIVE" => "Y",
						"DOMAIN" => $strDomain . "." . CAmminaRegions::getBaseDomain(),
						"PATHCODE" => $strDomain,
						"SITE_ID" => COption::GetOptionString("ammina.regions", "base_sid", ""),
						"CITY_ID" => $arRecord['ID'],
						"PRICES" => explode("|", COption::GetOptionString("ammina.regions", "prices_default", "")),
						"STORES" => explode("|", COption::GetOptionString("ammina.regions", "stores_default", "")),
					);
					$oTableResult = \Ammina\Regions\DomainTable::add($arFieldsDomain);
					$DOMAIN_ID = $oTableResult->getId();
					if ($DOMAIN_ID > 0) {
						if ($_REQUEST['action'] == "add_domain_region") {
							$arLoc = array(
								"REGION_ID" => $arFormatName["REGION_ID"],
								"DOMAIN_ID" => $DOMAIN_ID,
							);
							\Ammina\Regions\DomainLocationTable::add($arLoc);
						}
						if (COption::GetOptionString("ammina.regions", "make_settings_sitemap", "Y") == "Y") {
							\Ammina\Regions\DomainTable::doMakeSitemapSettingsForDomain($DOMAIN_ID);
						}
						if (COption::GetOptionString("ammina.regions", "make_robots_file", "Y") == "Y") {
							\Ammina\Regions\DomainTable::doMakeRobotsForDomain($DOMAIN_ID);
						}
						\Ammina\Regions\DomainVariableTable::doFillAllSystemVariables($DOMAIN_ID);
						\Ammina\Regions\DomainTable::doFillVariablesPadezhCityName($DOMAIN_ID);
						\Ammina\Regions\DomainTable::doFillVariablesPadezhRegionName($DOMAIN_ID);
						\Ammina\Regions\DomainTable::doFillVariablesPadezhCityRegionName($DOMAIN_ID);
						\Ammina\Regions\DomainTable::doFillVariableLocativeCityName($DOMAIN_ID);
						\Ammina\Regions\DomainTable::doFillVariableLocativeRegionName($DOMAIN_ID);
						\Ammina\Regions\DomainTable::doFillVariableLocativeCityRegionName($DOMAIN_ID);
					}
				}
				$DB->Commit();
				break;
		}
	}
}

$arHeader = array(
	array(
		"id" => "ID",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_ID"),
		"sort" => "ID",
		"default" => true,
	),
	array(
		"id" => "COUNTRY_ID",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_COUNTRY_ID"),
		"sort" => "REGION.COUNTRY_ID",
		"default" => true,
	),
	array(
		"id" => "REGION_ID",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_REGION_ID"),
		"sort" => "REGION_ID",
		"default" => true,
	),
	array(
		"id" => "NAME",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_NAME"),
		"sort" => "NAME",
		"default" => true,
	),
	array(
		"id" => "LAT",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_LAT"),
		"sort" => "LAT",
		"default" => false,
	),
	array(
		"id" => "LON",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_LON"),
		"sort" => "LON",
		"default" => false,
	),
	array(
		"id" => "OKATO",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_OKATO"),
		"sort" => "LAT",
		"default" => false,
	),
	array(
		"id" => "LOCATION_ID",
		"content" => Loc::getMessage("AMMINA_REGIONS_LOCATION_ID"),
		"sort" => "LOCATION_ID",
		"default" => $isSaleModule,
	),
	array(
		"id" => "IS_FAVORITE",
		"content" => Loc::getMessage("AMMINA_REGIONS_IS_FAVORITE"),
		"sort" => "IS_FAVORITE",
		"default" => $isSaleModule,
	),
	array(
		"id" => "IS_DEFAULT",
		"content" => Loc::getMessage("AMMINA_REGIONS_IS_DEFAULT"),
		"sort" => "IS_DEFAULT",
		"default" => $isSaleModule,
	),
	array(
		"id" => "EXT_ID",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_EXT_ID"),
		"sort" => "EXT_ID",
		"default" => false,
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
	"select" => array("*", "REGION_NAME" => "REGION.NAME", "COUNTRY_ID" => "REGION.COUNTRY_ID", "COUNTRY_NAME" => "REGION.COUNTRY.NAME"),
);

if ($usePageNavigation) {
	$getListParams['limit'] = $navyParams['SIZEN'];
	$getListParams['offset'] = $navyParams['SIZEN'] * ($navyParams['PAGEN'] - 1);
}
$totalCount = 0;
$totalPages = 0;
if ($usePageNavigation) {
	$totalCount = \Ammina\Regions\CityTable::getCount($getListParams['filter']);
	if ($totalCount > 0) {
		$totalPages = ceil($totalCount / $navyParams['SIZEN']);
		if ($navyParams['PAGEN'] > $totalPages) {
			$navyParams['PAGEN'] = $totalPages;
		}
	} else {
		$navyParams['PAGEN'] = 1;
	}
	$getListParams['limit'] = $navyParams['SIZEN'];
	$getListParams['offset'] = $navyParams['SIZEN'] * ($navyParams['PAGEN'] - 1);
}

$rsItems = \Ammina\Regions\CityTable::getList($getListParams);
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
	$row =& $lAdmin->AddRow($arData['ID'], $arData, 'ammina.regions.city.edit.php?ID=' . $arData['ID'] . '&lang=' . LANGUAGE_ID, Loc::getMessage("AMMINA_REGIONS_RECORD_EDIT"));
	$row->AddViewField("ID", '<a href="ammina.regions.city.edit.php?ID=' . $arData['ID'] . '&lang=' . LANGUAGE_ID . '">' . $arData['ID'] . '</a>');
	$row->AddViewField("COUNTRY_ID", '[' . $arData['COUNTRY_ID'] . '] <a href="/bitrix/admin/ammina.regions.country.edit.php?ID=' . $arData['COUNTRY_ID'] . '&lang=' . LANGUAGE_ID . '">' . $arData['COUNTRY_NAME'] . '</a>');
	$row->AddViewField("REGION_ID", '[' . $arData['REGION_ID'] . '] <a href="/bitrix/admin/ammina.regions.region.edit.php?ID=' . $arData['REGION_ID'] . '&lang=' . LANGUAGE_ID . '">' . $arData['REGION_NAME'] . '</a>');
	$row->AddCheckField("IS_DEFAULT");
	$row->AddCheckField("IS_FAVORITE");
	if ($isSaleModule) {
		if ($arData['LOCATION_ID'] > 0) {
			$arLocation = Bitrix\Sale\Location\LocationTable::getList(
				array(
					"filter" => array(
						"ID" => $arData['LOCATION_ID'],
						"NAME.LANGUAGE_ID" => LANGUAGE_ID,
					),
					"select" => array("*", "NAME_LANG" => "NAME.NAME"),
				)
			)->fetch();
			$row->AddViewField("LOCATION_ID", '[' . $arData['LOCATION_ID'] . '] <a href="/bitrix/admin/sale_location_node_edit.php?id=' . $arData['LOCATION_ID'] . '&lang=' . LANGUAGE_ID . '">' . $arLocation['NAME_LANG'] . '</a>');
		} else {
			$row->AddViewField("LOCATION_ID", "&nbsp;");
		}
	}
	$arActions = array();
	if ($modulePermissions >= "W") {
		$arActions[] = array(
			"ICON" => "edit",
			"TEXT" => Loc::getMessage("MAIN_ADMIN_MENU_EDIT"),
			"DEFAULT" => true,
			"ACTION" => $lAdmin->ActionRedirect("ammina.regions.city.edit.php?ID=" . $arData['ID'] . "&lang=" . LANGUAGE_ID),
		);
		$arActions[] = array(
			"ICON" => "favorite",
			"TEXT" => Loc::getMessage("AMMINA_REGIONS_ACTION_FAVORITE"),
			"DEFAULT" => true,
			"ACTION" => $lAdmin->ActionDoGroup($arData['ID'], "favorite"),
		);
		$arActions[] = array(
			"SEPARATOR" => true,
		);
		$arActions[] = array(
			"ICON" => "add",
			"TEXT" => Loc::getMessage("AMMINA_REGIONS_DOMAIN_ADD"),
			"DEFAULT" => true,
			"ACTION" => $lAdmin->ActionDoGroup($arData['ID'], "add_domain"),
		);
		$arActions[] = array(
			"ICON" => "add",
			"TEXT" => Loc::getMessage("AMMINA_REGIONS_DOMAIN_WITH_REGION_ADD"),
			"DEFAULT" => true,
			"ACTION" => $lAdmin->ActionDoGroup($arData['ID'], "add_domain_region"),
		);
		$arActions[] = array(
			"SEPARATOR" => true,
		);
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("AMMINA_REGIONS_ACTION_DELETE"),
			"ACTION" => "if(confirm('" . GetMessage('AMMINA_REGIONS_ACTION_DELETE_CONFIRM') . "')) " . $lAdmin->ActionDoGroup($arData['ID'], "delete"),
		);
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
	$aContext = array(
		array(
			"TEXT" => Loc::getMessage("AMMINA_REGIONS_LOAD_CITY"),
			"ICON" => "btn_new",
			"LINK" => "ammina.regions.city.load.php?lang=" . LANGUAGE_ID,
			"TITLE" => Loc::getMessage("AMMINA_REGIONS_LOAD_CITY_TITLE"),
		),
	);

	$lAdmin->AddAdminContextMenu($aContext);

	$lAdmin->AddGroupActionTable(
		array(
			"delete" => true,
			"add_domain" => Loc::getMessage("AMMINA_REGIONS_DOMAIN_ADD"),
			"add_domain_region" => Loc::getMessage("AMMINA_REGIONS_DOMAIN_WITH_REGION_ADD"),
			"favorite" => Loc::getMessage("AMMINA_REGIONS_ACTION_FAVORITE")
		)
	);
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("AMMINA_REGIONS_PAGE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filterFields);

$lAdmin->DisplayList();
CAmminaRegions::showSupportForm();
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
