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

$sTableID = "tbl_kit_multiregions_content_types";

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
		"id" => "IDENT",
		"name" => Loc::getMessage("KIT_MULTIREGIONS_FILTER_IDENT"),
		"filterable" => "?",
		"quickSearch" => "?",
		"default" => false,
	),
	array(
		"id" => "CLASS",
		"name" => Loc::getMessage("KIT_MULTIREGIONS_FILTER_CLASS"),
		"filterable" => "?",
		"quickSearch" => "?",
		"default" => false,
	)
);
$arFilter = array();
$lAdmin->AddFilter($filterFields, $arFilter);

if (($arID = $lAdmin->GroupAction()) && $modulePermissions >= "W") {
	if ($_REQUEST['action_target'] == 'selected') {
		$arID = Array();
		$dbResultList = \Kit\MultiRegions\ContentTypesTable::getList(array(
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
				$rRecord = \Kit\MultiRegions\ContentTypesTable::getList(array(
					"filter" => array("ID" => $ID),
					"select" => array("ID"),
				));
				$arRecordOld = $rRecord->Fetch();
				$DB->StartTransaction();
				$rLinkRecord = \Kit\MultiRegions\ContentTable::getList(array(
					"filter" => array(
						"TYPE_ID" => $ID,
					),
				));
				$bComplete = true;
				while ($arLinkRecord = $rLinkRecord->fetch()) {
					$rOperation = \Kit\MultiRegions\ContentTable::delete($arLinkRecord['ID']);
					if (!$rOperation->isSuccess()) {
						$bComplete = false;
					}
				}
				if ($bComplete) {
					$rOperation = \Kit\MultiRegions\ContentTypesTable::delete($ID);
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
		"id" => "IDENT",
		"content" => Loc::getMessage("KIT_MULTIREGIONS_FIELD_IDENT"),
		"sort" => "IDENT",
		"default" => true,
	),
	array(
		"id" => "CLASS",
		"content" => Loc::getMessage("KIT_MULTIREGIONS_FIELD_CLASS"),
		"sort" => "CLASS",
		"default" => true,
	),
	array(
        "id" => "GEOCONTENT_TEMPLATE",
        "content" => Loc::getMessage("KIT_MULTIREGIONS_FIELD_GEOCONTENT_TEMPLATE"),
        "sort" => "",
        "default" => true,
    ),
    array(
        "id" => "GEOCONTENT_GLOBAL",
        "content" => Loc::getMessage("KIT_MULTIREGIONS_FIELD_GEOCONTENT_GLOBAL"),
        "sort" => "",
        "default" => true,
    ),
    array(
        "id" => "GEOCONTENT_NOHTML_TEMPLATE",
        "content" => Loc::getMessage("KIT_MULTIREGIONS_FIELD_GEOCONTENT_NOHTML_TEMPLATE"),
        "sort" => "",
        "default" => true,
    ),
    array(
        "id" => "GEOCONTENT_NOHTML_GLOBAL",
        "content" => Loc::getMessage("KIT_MULTIREGIONS_FIELD_GEOCONTENT_NOHTML_GLOBAL"),
        "sort" => "",
        "default" => true,
    ),
);

$lAdmin->AddHeaders($arHeader);

$rsItems = \Kit\MultiRegions\ContentTypesTable::getList(array(
	"order" => $arOrder,
	"filter" => $arFilter,
	"select" => array("*")));
$rsItems = new CAdminUiResult($rsItems, $sTableID);
$rsItems->NavStart();

$lAdmin->SetNavigationParams($rsItems);

while ($arData = $rsItems->NavNext()) {
	$row =& $lAdmin->AddRow($arData['ID'], $arData, 'kit.multiregions.content.types.edit.php?ID=' . $arData['ID'] . '&lang=' . LANGUAGE_ID, Loc::getMessage("KIT_MULTIREGIONS_RECORD_EDIT"));
	$row->AddViewField("ID", '<a href="kit.multiregions.content.types.edit.php?ID=' . $arData['ID'] . '&lang=' . LANGUAGE_ID . '">' . $arData['ID'] . '</a>');
	$row->AddViewField('GEOCONTENT_TEMPLATE', '#KIT_GC_' . $arData['ID'] . '#');
	$row->AddViewField('GEOCONTENT_GLOBAL', '$GLOBALS[\'KIT_MULTIREGIONS\'][\'GC_' . $arData['ID'] . '\']');
    $row->AddViewField('GEOCONTENT_NOHTML_TEMPLATE', '#KIT_GC_' . $arData['ID'] . '_NOHTML#');
    $row->AddViewField('GEOCONTENT_NOHTML_GLOBAL', '$GLOBALS[\'KIT_MULTIREGIONS\'][\'GC_' . $arData['ID'] . '_NOHTML\']');
	$arActions = array();
	if ($modulePermissions >= "W") {
		$arActions[] = array(
			"ICON" => "edit",
			"TEXT" => Loc::getMessage("MAIN_ADMIN_MENU_EDIT"),
			"DEFAULT" => true,
			"ACTION" => $lAdmin->ActionRedirect("kit.multiregions.content.types.edit.php?ID=" . $arData['ID'] . "&lang=" . LANGUAGE_ID),
		);
		$arActions[] = array(
			"SEPARATOR" => true,
		);
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("KIT_MULTIREGIONS_ACTION_DELETE"),
			"ACTION" => "if(confirm('" . GetMessage('KIT_MULTIREGIONS_ACTION_DELETE_CONFIRM') . "')) " . $lAdmin->ActionDoGroup($arData['ID'], "delete"),
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
			"TEXT" => Loc::getMessage("KIT_MULTIREGIONS_NEW_RECORD"),
			"ICON" => "btn_new",
			"LINK" => "kit.multiregions.content.types.edit.php?lang=" . LANGUAGE_ID,
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
