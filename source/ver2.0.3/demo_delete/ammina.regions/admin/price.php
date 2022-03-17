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

$sTableID = "tbl_ammina_regions_price";
$isSaleModule = CAmminaRegions::isIMExists();

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$arOrder = (amreg_strtoupper($by) === "ID" ? array($by => $order) : array($by => $order, "ID" => "ASC"));
$lAdmin = new CAdminUiList($sTableID, $oSort);

$arAllPrices = array();
if ($isSaleModule) {
	$rPrices = \CCatalogGroup::GetList(array(
		"SORT" => "ASC",
		"NAME_LANG" => "ASC",
	));
	while ($arPrice = $rPrices->Fetch()) {
		$arAllPrices[$arPrice['ID']] = '[' . $arPrice['ID'] . '] ' . htmlspecialcharsbx($arPrice['NAME_LANG']);
	}
}
$filterFields = array(
	array(
		"id" => "ID",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_ID"),
		"type" => "number",
		"filterable" => "",
	),
	array(
		"id" => "ACTIVE",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_ACTIVE"),
		"filterable" => "",
		"type" => "list",
		"items" => array(
			"Y" => GetMessage("MAIN_YES"),
			"N" => GetMessage("MAIN_NO"),
		),
		"default" => false,
	),
	array(
		"id" => "PRICE_FROM_ID",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_PRICE_FROM_ID"),
		"filterable" => "",
		"type" => "list",
		"items" => $arAllPrices,
		"default" => false,
	),
	array(
		"id" => "PRICE_TO_ID",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_PRICE_TO_ID"),
		"filterable" => "",
		"type" => "list",
		"items" => $arAllPrices,
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
			"SORT",
			"PRICE_CHANGE",
			"PRICE_CHANGE_VALUE",
		);
		$arFields = array();
		foreach ($allowedFields as $fieldId) {
			if (array_key_exists($fieldId, $postFields))
				$arFields[$fieldId] = $postFields[$fieldId];
		}

		$oUpdate = \Ammina\Regions\PriceTable::update($ID, $arFields);
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
		$dbResultList = \Ammina\Regions\PriceTable::getList(array(
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
				$bComplete = true;
				$rRecord = \Ammina\Regions\PriceTable::getList(array(
					"filter" => array("ID" => $ID),
					"select" => array("ID"),
				));
				$arRecordOld = $rRecord->Fetch();
				$DB->StartTransaction();
				$bComplete = true;
				$rOperation = \Ammina\Regions\PriceTable::delete($ID);
				if (!$bComplete || !$rOperation->isSuccess()) {
					$DB->Rollback();
					if ($ex = $APPLICATION->GetException()) {
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					} else {
						$lAdmin->AddGroupError(Loc::getMessage("AMMINA_REGIONS_DELETE_ERROR"), $ID);
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
		"id" => "ACTIVE",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_ACTIVE"),
		"sort" => "ACTIVE",
		"default" => true,
	),
	array(
		"id" => "SORT",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_SORT"),
		"sort" => "SORT",
		"default" => true,
	),
	array(
		"id" => "CURRENCY",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_CURRENCY"),
		"sort" => "CURRENCY",
		"default" => true,
	),
	array(
		"id" => "PRICE_FROM_ID",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_PRICE_FROM_ID"),
		"sort" => "PRICE_FROM.NAME_LANG",
		"default" => true,
	),
	array(
		"id" => "PRICE_TO_ID",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_PRICE_TO_ID"),
		"sort" => "PRICE_TO.NAME_LANG",
		"default" => true,
	),
	array(
		"id" => "PRICE_CHANGE",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_PRICE_CHANGE"),
		"sort" => "PRICE_CHANGE",
		"default" => true,
	),
	array(
		"id" => "PRICE_CHANGE_VALUE",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_PRICE_CHANGE_VALUE"),
		"sort" => "PRICE_CHANGE_VALUE",
		"default" => true,
	),
);

$lAdmin->AddHeaders($arHeader);

$rsItems = \Ammina\Regions\PriceTable::getList(array(
	"order" => $arOrder,
	"filter" => $arFilter,
	"select" => array("*")));
$rsItems = new CAdminUiResult($rsItems, $sTableID);
$rsItems->NavStart();

$lAdmin->SetNavigationParams($rsItems);
$arSelectPriceChange = array(
	'NC' => Loc::getMessage("AMMINA_REGIONS_FIELD_PRICE_CHANGE_NC"),
	'SU' => Loc::getMessage("AMMINA_REGIONS_FIELD_PRICE_CHANGE_SU"),
	'SD' => Loc::getMessage("AMMINA_REGIONS_FIELD_PRICE_CHANGE_SD"),
	'PU' => Loc::getMessage("AMMINA_REGIONS_FIELD_PRICE_CHANGE_PU"),
	'PD' => Loc::getMessage("AMMINA_REGIONS_FIELD_PRICE_CHANGE_PD"),
);
$arAllCurrency = array();
$rCurrency = \Bitrix\Currency\CurrencyTable::getList(array(
	"order" => array("SORT" => "ASC"),
	"select" => array("*", "LANG_NAME" => "CURRENT_LANG_FORMAT.FULL_NAME"),
));
while ($arCurrency = $rCurrency->fetch()) {
	$arAllCurrency[$arCurrency['CURRENCY']] = '[' . $arCurrency['CURRENCY'] . '] ' . $arCurrency['LANG_NAME'];
}

while ($arData = $rsItems->NavNext()) {
	$row =& $lAdmin->AddRow($arData['ID'], $arData, 'ammina.regions.price.edit.php?ID=' . $arData['ID'] . '&lang=' . LANGUAGE_ID, Loc::getMessage("AMMINA_REGIONS_RECORD_EDIT"));
	$row->AddViewField("ID", '<a href="ammina.regions.price.edit.php?ID=' . $arData['ID'] . '&lang=' . LANGUAGE_ID . '">' . $arData['ID'] . '</a>');
	$row->AddCheckField("ACTIVE");
	$row->AddInputField("PRICE_CHANGE_VALUE");
	$row->AddInputField("SORT");
	$row->AddViewField("PRICE_FROM_ID", $arAllPrices[$arData['PRICE_FROM_ID']]);
	$row->AddViewField("PRICE_TO_ID", $arAllPrices[$arData['PRICE_TO_ID']]);
	$row->AddSelectField("PRICE_CHANGE", $arSelectPriceChange);
	$row->AddViewField("CURRENCY", $arAllCurrency[$arData['CURRENCY']]);
	$arActions = array();
	if ($modulePermissions >= "W") {
		$arActions[] = array(
			"ICON" => "edit",
			"TEXT" => Loc::getMessage("MAIN_ADMIN_MENU_EDIT"),
			"DEFAULT" => true,
			"ACTION" => $lAdmin->ActionRedirect("ammina.regions.price.edit.php?ID=" . $arData['ID'] . "&lang=" . LANGUAGE_ID),
		);

		$arActions[] = array(
			"SEPARATOR" => true,
		);
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("AMMINA_REGIONS_ACTION_DELETE"),
			"ACTION" => "if(confirm('" . GetMessage('AMMINA_REGIONS_ACTION_DELETE_CONFIRM') . "')) " . $lAdmin->ActionDoGroup($arData['ID'], "delete"),
		);
		if (count($arActions) > 0) {
			$row->AddActions($arActions);
		}
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
			"LINK" => "ammina.regions.price.edit.php?lang=" . LANGUAGE_ID,
			"TITLE" => Loc::getMessage("AMMINA_REGIONS_NEW_RECORD_TITLE"),
		),
	);

	$lAdmin->AddAdminContextMenu($aContext);

	$lAdmin->AddGroupActionTable(Array(
		"edit" => true,
		"delete" => true,
	));
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("AMMINA_REGIONS_PAGE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filterFields);

$lAdmin->DisplayList();
CAmminaRegions::showSupportForm();
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");