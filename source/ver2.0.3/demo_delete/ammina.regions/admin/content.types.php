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

$sTableID = "tbl_ammina_regions_content_types";

$oSort = new CAdminSorting($sTableID, "NAME", "asc");
$arOrder = (amreg_strtoupper($by) === "ID" ? array($by => $order) : array($by => $order, "ID" => "ASC"));
$lAdmin = new CAdminUiList($sTableID, $oSort);

$filterFields = array(
	array(
		"id" => "ID",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_ID"),
		"type" => "number",
		"filterable" => "",
	),
	array(
		"id" => "NAME",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_NAME"),
		"filterable" => "?",
		"quickSearch" => "?",
		"default" => true,
	),
	array(
		"id" => "IDENT",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_IDENT"),
		"filterable" => "?",
		"quickSearch" => "?",
		"default" => false,
	),
	array(
		"id" => "CLASS",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_CLASS"),
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
		$dbResultList = \Ammina\Regions\ContentTypesTable::getList(array(
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
				$rRecord = \Ammina\Regions\ContentTypesTable::getList(array(
					"filter" => array("ID" => $ID),
					"select" => array("ID"),
				));
				$arRecordOld = $rRecord->Fetch();
				$DB->StartTransaction();
				$rLinkRecord = \Ammina\Regions\ContentTable::getList(array(
					"filter" => array(
						"TYPE_ID" => $ID,
					),
				));
				$bComplete = true;
				while ($arLinkRecord = $rLinkRecord->fetch()) {
					$rOperation = \Ammina\Regions\ContentTable::delete($arLinkRecord['ID']);
					if (!$rOperation->isSuccess()) {
						$bComplete = false;
					}
				}
				if ($bComplete) {
					$rOperation = \Ammina\Regions\ContentTypesTable::delete($ID);
				}
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
		"id" => "NAME",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_NAME"),
		"sort" => "NAME",
		"default" => true,
	),
	array(
		"id" => "IDENT",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_IDENT"),
		"sort" => "IDENT",
		"default" => true,
	),
	array(
		"id" => "CLASS",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_CLASS"),
		"sort" => "CLASS",
		"default" => true,
	),
	array(
        "id" => "GEOCONTENT_TEMPLATE",
        "content" => Loc::getMessage("AMMINA_REGIONS_FIELD_GEOCONTENT_TEMPLATE"),
        "sort" => "",
        "default" => true,
    ),
    array(
        "id" => "GEOCONTENT_GLOBAL",
        "content" => Loc::getMessage("AMMINA_REGIONS_FIELD_GEOCONTENT_GLOBAL"),
        "sort" => "",
        "default" => true,
    ),
    array(
        "id" => "GEOCONTENT_NOHTML_TEMPLATE",
        "content" => Loc::getMessage("AMMINA_REGIONS_FIELD_GEOCONTENT_NOHTML_TEMPLATE"),
        "sort" => "",
        "default" => true,
    ),
    array(
        "id" => "GEOCONTENT_NOHTML_GLOBAL",
        "content" => Loc::getMessage("AMMINA_REGIONS_FIELD_GEOCONTENT_NOHTML_GLOBAL"),
        "sort" => "",
        "default" => true,
    ),
);

$lAdmin->AddHeaders($arHeader);

$rsItems = \Ammina\Regions\ContentTypesTable::getList(array(
	"order" => $arOrder,
	"filter" => $arFilter,
	"select" => array("*")));
$rsItems = new CAdminUiResult($rsItems, $sTableID);
$rsItems->NavStart();

$lAdmin->SetNavigationParams($rsItems);

while ($arData = $rsItems->NavNext()) {
	$row =& $lAdmin->AddRow($arData['ID'], $arData, 'ammina.regions.content.types.edit.php?ID=' . $arData['ID'] . '&lang=' . LANGUAGE_ID, Loc::getMessage("AMMINA_REGIONS_RECORD_EDIT"));
	$row->AddViewField("ID", '<a href="ammina.regions.content.types.edit.php?ID=' . $arData['ID'] . '&lang=' . LANGUAGE_ID . '">' . $arData['ID'] . '</a>');
	$row->AddViewField('GEOCONTENT_TEMPLATE', '#AMMINA_GC_' . $arData['ID'] . '#');
	$row->AddViewField('GEOCONTENT_GLOBAL', '$GLOBALS[\'AMMINA_REGIONS\'][\'GC_' . $arData['ID'] . '\']');
    $row->AddViewField('GEOCONTENT_NOHTML_TEMPLATE', '#AMMINA_GC_' . $arData['ID'] . '_NOHTML#');
    $row->AddViewField('GEOCONTENT_NOHTML_GLOBAL', '$GLOBALS[\'AMMINA_REGIONS\'][\'GC_' . $arData['ID'] . '_NOHTML\']');
	$arActions = array();
	if ($modulePermissions >= "W") {
		$arActions[] = array(
			"ICON" => "edit",
			"TEXT" => Loc::getMessage("MAIN_ADMIN_MENU_EDIT"),
			"DEFAULT" => true,
			"ACTION" => $lAdmin->ActionRedirect("ammina.regions.content.types.edit.php?ID=" . $arData['ID'] . "&lang=" . LANGUAGE_ID),
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
			"LINK" => "ammina.regions.content.types.edit.php?lang=" . LANGUAGE_ID,
			"TITLE" => Loc::getMessage("AMMINA_REGIONS_NEW_RECORD_TITLE"),
		),
	);

	$lAdmin->AddAdminContextMenu($aContext);

	$lAdmin->AddGroupActionTable(Array(
		"delete" => true,
	));
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
