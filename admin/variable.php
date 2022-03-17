<?

use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
Bitrix\Main\Loader::includeModule('kit.multiregions');
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/kit.multiregions/prolog.php");

Loc::loadMessages(__FILE__);

$modulePermissions = $APPLICATION->GetGroupRight("kit.multiregions");
if ($modulePermissions < "W") {
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

$isSaleModule = CKitMultiRegions::isIMExists();

$sTableID = "tbl_kit_multiregions_variable";

$oSort = new CAdminSorting($sTableID, "NAME", "asc");
$arOrder = (amreg_strtoupper($by) === "ID" ? array($by => $order) : array($by => $order, "ID" => "ASC"));
$lAdmin = new CAdminUiList($sTableID, $oSort);

$filterFields = array(
	array(
		"id" => "ID",
		"name" => Loc::getMessage("KIT_MULTIREGIONS_FILTER_ID"),
		"type" => "number",
		"filterable" => "",
	),
	array(
		"id" => "NAME",
		"name" => Loc::getMessage("KIT_MULTIREGIONS_FILTER_NAME"),
		"filterable" => "?",
		"quickSearch" => "?",
		"default" => true,
	),
	array(
		"id" => "DESCRIPTION",
		"name" => Loc::getMessage("KIT_MULTIREGIONS_FILTER_DESCRIPTION"),
		"filterable" => "?",
		"quickSearch" => "?",
		"default" => false,
	),
	array(
		"id" => "CODE",
		"name" => Loc::getMessage("KIT_MULTIREGIONS_FILTER_CODE"),
		"filterable" => "?",
		"quickSearch" => "?",
		"default" => true,
	),
	array(
		"id" => "IS_SYSTEM",
		"name" => Loc::getMessage("KIT_MULTIREGIONS_FILTER_IS_SYSTEM"),
		"filterable" => "",
		"type" => "list",
		"items" => array(
			"Y" => GetMessage("KIT_MULTIREGIONS_FILTER_IS_SYSTEM_Y"),
			"E" => GetMessage("KIT_MULTIREGIONS_FILTER_IS_SYSTEM_E"),
			"N" => GetMessage("KIT_MULTIREGIONS_FILTER_IS_SYSTEM_N"),
		),
		"default" => false,
	),
);
$arFilter = array();
$lAdmin->AddFilter($filterFields, $arFilter);

if (($arID = $lAdmin->GroupAction()) && $modulePermissions >= "W") {
	if ($_REQUEST['action_target'] == 'selected') {
		$arID = Array();
		$dbResultList = \Kit\MultiRegions\VariableTable::getList(array(
			"order" => $arOrder,
			"filter" => $arFilter,
			"select" => array("ID", "IS_SYSTEM")));
		while ($arResult = $dbResultList->Fetch()) {
			if (!in_array($arResult['IS_SYSTEM'], array("E", "Y"))) {
				$arID[] = $arResult['ID'];
			}
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
				$rRecord = \Kit\MultiRegions\VariableTable::getList(array(
					"filter" => array("ID" => $ID),
					"select" => array("ID"),
				));
				$arRecordOld = $rRecord->Fetch();
				$DB->StartTransaction();
				$rLinkRecord = \Kit\MultiRegions\DomainVariableTable::getList(array(
					"filter" => array(
						"VARIABLE_ID" => $ID,
					),
				));
				while ($arLinkRecord = $rLinkRecord->fetch()) {
					$rOperation = \Kit\MultiRegions\DomainVariableTable::delete($arLinkRecord['ID']);
					if (!$rOperation->isSuccess()) {
						$bComplete = false;
					}
				}
				if ($bComplete) {
					$rOperation = \Kit\MultiRegions\VariableTable::delete($ID);
				}
				if (!$bComplete || !$rOperation->isSuccess()) {
					$DB->Rollback();
					if ($ex = $APPLICATION->GetException()) {
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					} else {
						$lAdmin->AddGroupError(Loc::getMessage("KIT_MULTIREGIONS_DELETE_ERROR"), $ID);
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
		"content" => Loc::getMessage("KIT_MULTIREGIONS_FIELD_ID"),
		"sort" => "ID",
		"default" => true,
	),
	array(
		"id" => "NAME",
		"content" => Loc::getMessage("KIT_MULTIREGIONS_FIELD_NAME"),
		"sort" => "NAME",
		"default" => true,
	),
	array(
		"id" => "DESCRIPTION",
		"content" => Loc::getMessage("KIT_MULTIREGIONS_FIELD_DESCRIPTION"),
		"default" => true,
	),
	array(
		"id" => "CODE",
		"content" => Loc::getMessage("KIT_MULTIREGIONS_FIELD_CODE"),
		"sort" => "CODE",
		"default" => true,
	),
	array(
		"id" => "IS_SYSTEM",
		"content" => Loc::getMessage("KIT_MULTIREGIONS_FIELD_IS_SYSTEM"),
		"sort" => "IS_SYSTEM",
		"default" => true,
	),
);

$lAdmin->AddHeaders($arHeader);

$rsItems = \Kit\MultiRegions\VariableTable::getList(array(
	"order" => $arOrder,
	"filter" => $arFilter,
	"select" => array("*")));
$rsItems = new CAdminUiResult($rsItems, $sTableID);
$rsItems->NavStart();

$lAdmin->SetNavigationParams($rsItems);
$arSelectSystem = array(
	"Y" => GetMessage("KIT_MULTIREGIONS_FIELD_IS_SYSTEM_Y"),
	"E" => GetMessage("KIT_MULTIREGIONS_FIELD_IS_SYSTEM_E"),
	"N" => GetMessage("KIT_MULTIREGIONS_FIELD_IS_SYSTEM_N"),
);
while ($arData = $rsItems->NavNext()) {
	$row =& $lAdmin->AddRow($arData['ID'], $arData, 'kit.multiregions.variable.edit.php?ID=' . $arData['ID'] . '&lang=' . LANGUAGE_ID, Loc::getMessage("KIT_MULTIREGIONS_RECORD_EDIT"));
	$row->AddViewField("ID", '<a href="kit.multiregions.variable.edit.php?ID=' . $arData['ID'] . '&lang=' . LANGUAGE_ID . '">' . $arData['ID'] . '</a>');
	$row->AddViewField("IS_SYSTEM", $arSelectSystem[$arData['IS_SYSTEM']]);
	$arActions = array();
	if ($modulePermissions >= "W") {
		if ($arData['IS_SYSTEM'] != "Y" && $arData['IS_SYSTEM'] != "E") {
			$arActions[] = array(
				"ICON" => "edit",
				"TEXT" => Loc::getMessage("MAIN_ADMIN_MENU_EDIT"),
				"DEFAULT" => true,
				"ACTION" => $lAdmin->ActionRedirect("kit.multiregions.variable.edit.php?ID=" . $arData['ID'] . "&lang=" . LANGUAGE_ID),
			);

			$arActions[] = array(
				"SEPARATOR" => true,
			);
			$arActions[] = array(
				"ICON" => "delete",
				"TEXT" => GetMessage("KIT_MULTIREGIONS_ACTION_DELETE"),
				"ACTION" => "if(confirm('" . GetMessage('KIT_MULTIREGIONS_ACTION_DELETE_CONFIRM') . "')) " . $lAdmin->ActionDoGroup($arData['ID'], "delete"),
			);
		} else {
			$arActions[] = array(
				"ICON" => "view",
				"TEXT" => Loc::getMessage("KIT_MULTIREGIONS_ACTION_VIEW"),
				"DEFAULT" => true,
				"ACTION" => $lAdmin->ActionRedirect("kit.multiregions.variable.edit.php?ID=" . $arData['ID'] . "&lang=" . LANGUAGE_ID),
			);
		}
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
			"TEXT" => Loc::getMessage("KIT_MULTIREGIONS_NEW_RECORD"),
			"ICON" => "btn_new",
			"LINK" => "kit.multiregions.variable.edit.php?lang=" . LANGUAGE_ID,
			"TITLE" => Loc::getMessage("KIT_MULTIREGIONS_NEW_RECORD_TITLE"),
		),
	);

	$lAdmin->AddAdminContextMenu($aContext);

	$lAdmin->AddGroupActionTable(Array(
		"delete" => true,
	));
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("KIT_MULTIREGIONS_PAGE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filterFields);

$lAdmin->DisplayList();
CKitMultiRegions::showSupportForm();
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");