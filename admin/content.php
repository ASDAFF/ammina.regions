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

$sTableID = "tbl_ammina_regions_content";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$arOrder = (amreg_strtoupper($by) === "ID" ? array($by => $order) : array($by => $order, "ID" => "ASC"));
$lAdmin = new CAdminUiList($sTableID, $oSort);

$arTypesList = array();
$rTypes = \Ammina\Regions\ContentTypesTable::getList(array(
	"select" => array("ID", "NAME"),
	"order" => array("NAME" => "ASC"),
));
while ($arTypes = $rTypes->fetch()) {
	$arTypesList[$arTypes['ID']] = "[" . $arTypes['ID'] . "] " . $arTypes['NAME'];
}

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
	"select" => array("ID", "NAME", "COUNTRY_NAME" => "COUNTRY.NAME"),
	"order" => array("COUNTRY_NAME" => "ASC", "NAME" => "ASC"),
));
while ($arRegion = $rRegion->fetch()) {
	$arRegionList[$arRegion['ID']] = "[" . $arRegion['ID'] . "] " . $arRegion['COUNTRY_NAME'] . ", " . $arRegion['NAME'];
}
$arDomainList = array();
$rDomain = \Ammina\Regions\DomainTable::getList(array(
	"select" => array("ID", "NAME", "DOMAIN"),
	"order" => array("NAME" => "ASC", "DOMAIN" => "ASC"),
));
while ($arDomain = $rDomain->fetch()) {
	$arDomainList[$arDomain['ID']] = "[" . $arDomain['ID'] . "] " . $arDomain['NAME'] . " (" . $arDomain['DOMAIN'] . ")";
}
$arSitesList = array();
$b = "SORT";
$o = "ASC";
$rSites = \CSite::GetList($b, $o, array());
while ($arSite = $rSites->Fetch()) {
	$arSitesList[$arSite['LID']] = '[' . $arSite['LID'] . '] ' . $arSite['NAME'];
}

$filterFields = array(
	array(
		"id" => "ID",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_ID"),
		"type" => "number",
		"filterable" => "",
	),
	array(
		"id" => "SITE_ID",
		"name" => Loc::getMessage("AMMINA_IP_FILTER_SITE_ID"),
		"filterable" => "",
		"type" => "list",
		"items" => $arSitesList,
		"default" => true,
		"params" => array("multiple" => "Y"),
	),
	array(
		"id" => "TYPE_ID",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_TYPE_ID"),
		"filterable" => "",
		"type" => "list",
		"items" => $arTypesList,
		"default" => true,
		"params" => array("multiple" => "Y"),
	),
	array(
		"id" => "COUNTRY_ID",
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
		"id" => "CITY.CODE",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_CITY_CODE"),
		"filterable" => "?",
		"quickSearch" => "?",
		"default" => false,
	),
	array(
		"id" => "CITY.NAME",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_CITY_NAME"),
		"filterable" => "?",
		"quickSearch" => "?",
		"default" => true,
	),
	array(
		"id" => "DOMAIN_ID",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_DOMAIN_ID"),
		"filterable" => "",
		"type" => "list",
		"items" => $arDomainList,
		"default" => true,
		"params" => array("multiple" => "Y"),
	),
	array(
		"id" => "ACTIVE",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_ACTIVE"),
		"filterable" => "?",
		"quickSearch" => "?",
		"default" => false,
	),
	array(
		"id" => "CONTENT",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_CONTENT"),
		"filterable" => "?",
		"quickSearch" => "?",
		"default" => false,
	),
);
$arFilter = array();
$lAdmin->AddFilter($filterFields, $arFilter);

if ($lAdmin->EditAction()) {
	foreach ($FIELDS as $ID => $postFields) {
		$DB->StartTransaction();
		$ID = IntVal($ID);

		if (!$lAdmin->IsUpdated($ID))
			continue;

		$allowedFields = array(
			"ACTIVE",
			"CONTENT",
		);
		$arFields = array();
		foreach ($allowedFields as $fieldId) {
			if (array_key_exists($fieldId, $postFields))
				$arFields[$fieldId] = $postFields[$fieldId];
		}

		$oUpdate = \Ammina\Regions\ContentTable::update($ID, $arFields);
		if (!$oUpdate->isSuccess()) {
			$lAdmin->AddUpdateError(GetMessage("AMMINA_REGIONS_UPDATE_ERROR", array("#ID#" => $ID, "#ERROR_TEXT#" => implode(", ", $oUpdate->getErrorMessages()))), $ID);
			$DB->Rollback();
		}
		$DB->Commit();
	}
}

if (($arID = $lAdmin->GroupAction()) && $modulePermissions >= "W") {
	if ($_REQUEST['action_target'] == 'selected') {
		$arID = Array();
		$dbResultList = \Ammina\Regions\ContentTable::getList(array(
			"order" => $arOrder,
			"filter" => $arFilter,
			"select" => array("ID")));
		while ($arResult = $dbResultList->Fetch()) {
			$arID[] = $arResult['ID'];
		}
	}

	foreach ($arID as $ID) {
		if (amreg_strlen($ID) <= 0) {
			continue;
		}

		switch ($_REQUEST['action']) {
			case "delete":
				@set_time_limit(0);
				$rRecord = \Ammina\Regions\ContentTable::getList(array(
					"filter" => array("ID" => $ID),
					"select" => array("ID"),
				));
				$arRecordOld = $rRecord->Fetch();
				$DB->StartTransaction();
				$rOperation = \Ammina\Regions\ContentTable::delete($ID);
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
			case "activate":
			case "deactivate":
				$arFields = Array("ACTIVE" => ($_REQUEST['action'] == "activate" ? "Y" : "N"));
				$oResult = \Ammina\Regions\ContentTable::update($ID, $arFields);
				if (!$oResult->isSuccess()) {
					$lAdmin->AddGroupError(GetMessage("AMMINA_REGIONS_UPDATE_ERROR") . implode(", ", $oResult->getErrorMessages()), $ID);
				}
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
		"id" => "SITE_ID",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_SITE_ID"),
		"sort" => "SITE_ID",
		"default" => true,
	),
	array(
		"id" => "TYPE_ID",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_TYPE_ID"),
		"sort" => "TYPE_ID",
		"default" => true,
	),
	array(
		"id" => "COUNTRY_ID",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_COUNTRY_ID"),
		"sort" => "COUNTRY_ID",
		"default" => true,
	),
	array(
		"id" => "REGION_ID",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_REGION_ID"),
		"sort" => "REGION_ID",
		"default" => true,
	),
	array(
		"id" => "CITY_ID",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_CITY_ID"),
		"sort" => "CITY_ID",
		"default" => true,
	),
	array(
		"id" => "DOMAIN_ID",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_DOMAIN_ID"),
		"sort" => "DOMAIN_ID",
		"default" => true,
	),
	array(
		"id" => "ACTIVE",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_ACTIVE"),
		"sort" => "ACTIVE",
		"default" => true,
	),
	array(
		"id" => "CONTENT",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_CONTENT"),
		"sort" => "CONTENT",
		"default" => true,
	),
);

$lAdmin->AddHeaders($arHeader);

$rsItems = \Ammina\Regions\ContentTable::getList(array(
	"order" => $arOrder,
	"filter" => $arFilter,
	"select" => array("*", "CITY_NAME" => "CITY.NAME", "REGION_NAME" => "REGION.NAME", "COUNTRY_NAME" => "COUNTRY.NAME", "TYPE_NAME" => "TYPE.NAME", "DOMAIN_NAME" => "DOMAIN.NAME", "DOMAIN_DOMAIN" => "DOMAIN.DOMAIN", "SITE_NAME" => "SITE.NAME")));
$rsItems = new CAdminUiResult($rsItems, $sTableID);
$rsItems->NavStart();

$lAdmin->SetNavigationParams($rsItems);

while ($arData = $rsItems->NavNext()) {
	if ($arData['COUNTRY_ID'] <= 0) {
		$arData['COUNTRY_ID'] = false;
	}
	if ($arData['REGION_ID'] <= 0) {
		$arData['REGION_ID'] = false;
	}
	if ($arData['CITY_ID'] <= 0) {
		$arData['CITY_ID'] = false;
	}
	if ($arData['DOMAIN_ID'] <= 0) {
		$arData['DOMAIN_ID'] = false;
	}
	$row =& $lAdmin->AddRow($arData['ID'], $arData, 'ammina.regions.content.edit.php?ID=' . $arData['ID'] . '&lang=' . LANGUAGE_ID, Loc::getMessage("AMMINA_REGIONS_RECORD_EDIT"));
	if (amreg_strlen($arData['SITE_ID']) > 0) {
		$row->AddViewField("SITE_ID", '[<a href="/bitrix/admin/site_edit.php?lang=' . LANGUAGE_ID . '&LID=' . $arData['SITE_ID'] . '">' . $arData['SITE_ID'] . '</a>] ' . $arData['SITE_NAME']);
	}
	$row->AddViewField("ID", '<a href="ammina.regions.content.edit.php?ID=' . $arData['ID'] . '&lang=' . LANGUAGE_ID . '">' . $arData['ID'] . '</a>');
	$row->AddViewField("TYPE_ID", $arData['TYPE_ID'] > 0 ? '[' . $arData['TYPE_ID'] . '] <a href="/bitrix/admin/ammina.regions.content.types.edit.php?ID=' . $arData['TYPE_ID'] . '&lang=' . LANGUAGE_ID . '">' . $arData['TYPE_NAME'] . '</a>' : "");
	$row->AddViewField("COUNTRY_ID", $arData['COUNTRY_ID'] > 0 ? '[' . $arData['COUNTRY_ID'] . '] <a href="/bitrix/admin/ammina.regions.country.edit.php?ID=' . $arData['COUNTRY_ID'] . '&lang=' . LANGUAGE_ID . '">' . $arData['COUNTRY_NAME'] . '</a>' : "");
	$row->AddViewField("REGION_ID", $arData['REGION_ID'] > 0 ? '[' . $arData['REGION_ID'] . '] <a href="/bitrix/admin/ammina.regions.region.edit.php?ID=' . $arData['REGION_ID'] . '&lang=' . LANGUAGE_ID . '">' . $arData['REGION_NAME'] . '</a>' : "");
	$row->AddViewField("CITY_ID", $arData['CITY_ID'] > 0 ? '[' . $arData['CITY_ID'] . '] <a href="/bitrix/admin/ammina.regions.city.edit.php?ID=' . $arData['CITY_ID'] . '&lang=' . LANGUAGE_ID . '">' . $arData['CITY_NAME'] . '</a>' : "");
	$row->AddViewField("DOMAIN_ID", $arData['DOMAIN_ID'] > 0 ? '[' . $arData['DOMAIN_ID'] . '] <a href="/bitrix/admin/ammina.regions.domain.edit.php?ID=' . $arData['DOMAIN_ID'] . '&lang=' . LANGUAGE_ID . '">' . $arData['DOMAIN_NAME'] . ' (' . $arData['DOMAIN_DOMAIN'] . ')</a>' : "");
	$row->AddCheckField("ACTIVE");
	/*$sHTML = '<textarea rows="5" cols="30" name="CONTENT">' . htmlspecialcharsbx($arData["CONTENT"]) . '</textarea>';
	$row->AddEditField("CONTENT", $sHTML);*/

	$arActions = array();
	if ($modulePermissions >= "W") {
		$arActions[] = array(
			"ICON" => "edit",
			"TEXT" => Loc::getMessage("MAIN_ADMIN_MENU_EDIT"),
			"DEFAULT" => true,
			"ACTION" => $lAdmin->ActionRedirect("ammina.regions.content.edit.php?ID=" . $arData['ID'] . "&lang=" . LANGUAGE_ID),
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
			"TEXT" => Loc::getMessage("AMMINA_REGIONS_NEW_RECORD"),
			"ICON" => "btn_new",
			"LINK" => "ammina.regions.content.edit.php?lang=" . LANGUAGE_ID,
			"TITLE" => Loc::getMessage("AMMINA_REGIONS_NEW_RECORD_TITLE"),
		),
	);

	$lAdmin->AddAdminContextMenu($aContext);

	$lAdmin->AddGroupActionTable(Array(
		"edit" => true,
		"delete" => true,
		"activate" => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate" => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
	));
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("AMMINA_REGIONS_PAGE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filterFields);

$lAdmin->DisplayList();
CAmminaRegions::showSupportForm();
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");