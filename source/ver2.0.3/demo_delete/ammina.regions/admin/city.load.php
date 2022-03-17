<?

use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
Bitrix\Main\Loader::includeModule('ammina.regions');
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/ammina.regions/prolog.php");

Loc::loadMessages(__FILE__);
$ID = isset($_REQUEST["ID"]) ? intval($_REQUEST["ID"]) : 0;

$isSaleModule = CAmminaRegions::isIMExists();

$isSavingOperation = (
	$_SERVER["REQUEST_METHOD"] == "POST"
	&& (
		isset($_POST["apply"])
		|| isset($_POST["save"])
	)
	&& check_bitrix_sessid()
);

$arUserGroups = $USER->GetUserGroupArray();
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
/*
$needFieldsRestore = $_SERVER["REQUEST_METHOD"] == "POST" && !$isSavingOperation;
$isNewItem = ($ID <= 0);
$arCurrentItem = false;
if ($ID > 0) {
	$arCurrentItem = \Ammina\Regions\CityTable::getById($ID)->fetch();
}
if (!$arCurrentItem) {
	$isNewItem = true;
	$ID = false;
	$arCurrentItem = array();
}

if ($isNewItem) {
	LocalRedirect("/bitrix/admin/ammina.regions.city.php?lang=" . LANGUAGE_ID);
}*/
$result = new \Bitrix\Main\Entity\Result();

$customTabber = new CAdminTabEngine("OnAdminAmminaRegionsCityLoad");
$customDraggableBlocks = new CAdminDraggableBlockEngine('OnAdminAmminaRegionsCityLoadDraggable');

if ($isSavingOperation) {

	$errorMessage = '';
	if (!$customTabber->Check()) {
		if ($ex = $APPLICATION->GetException())
			$errorMessage .= $ex->GetString();
		else
			$errorMessage .= "Custom tabber check unknown error!";

		$result->addError(new \Bitrix\Main\Entity\EntityError($errorMessage));
	}

	if (!$customDraggableBlocks->check()) {
		if ($ex = $APPLICATION->GetException())
			$errorMessage .= $ex->GetString();
		else
			$errorMessage .= "Custom draggable block check unknown error!";

		$result->addError(new \Bitrix\Main\Entity\EntityError($errorMessage));
	}

	$strLoadContent = trim($_REQUEST['FIELDS']['LOAD_CONTENT']);
	if (strlen($strLoadContent) > 0) {
		$APPLICATION->SaveFileContent($_SERVER['DOCUMENT_ROOT'] . "/upload/tmp/ammina.regions.city.load.csv", $strLoadContent);
		$oCsv = new CCSVData();
		$oCsv->LoadFile($_SERVER['DOCUMENT_ROOT'] . "/upload/tmp/ammina.regions.city.load.csv");
		set_time_limit(1800);
		ignore_user_abort(true);
		$arCache = array();
		while ($arData = $oCsv->Fetch()) {
			if (strlen($arData[0]) > 0 && strlen($arData[1]) > 0 && strlen($arData[2]) > 0) {
				if (!isset($arCache['COUNTRY'][$arData[0]])) {
					$arCountry = \Ammina\Regions\CountryTable::getList(array(
						"filter" => array(
							array(
								"LOGIC" => "OR",
								array(
									"NAME" => $arData[0]
								),
								array(
									"COUNTRY_LANG.NAME" => $arData[0]
								)
							)
						)
					))->fetch();
					if ($arCountry) {
						$arCache['COUNTRY'][$arData[0]] = $arCountry['ID'];
					}
				}
				if ($arCache['COUNTRY'][$arData[0]] > 0) {
					if (!isset($arCache['REGION'][$arData[1]])) {
						$arRegion = \Ammina\Regions\RegionTable::getList(array(
							"filter" => array(
								"COUNTRY_ID" => $arCache['COUNTRY'][$arData[0]],
								array(
									"LOGIC" => "OR",
									array(
										"NAME" => $arData[1]
									),
									array(
										"REGION_LANG.NAME" => $arData[1]
									)
								)
							)
						))->fetch();
						if ($arRegion) {
							$arCache['REGION'][$arData[1]] = $arRegion['ID'];
						}
					}
					if ($arCache['REGION'][$arData[1]] > 0) {
						$arCity = \Ammina\Regions\CityTable::getList(array(
							"filter" => array(
								"REGION_ID" => $arCache['REGION'][$arData[1]],
								array(
									"LOGIC" => "OR",
									array(
										"NAME" => $arData[2]
									),
									array(
										"CITY_LANG.NAME" => $arData[2]
									)
								)
							)
						))->fetch();
						if (!$arCity) {
							$arFields=array(
								"REGION_ID" => $arCache['REGION'][$arData[1]],
								"NAME"=>$arData[2]
							);
							\Ammina\Regions\CityTable::add($arFields);
						}
					}
				}
			}
		}

		@unlink($_SERVER['DOCUMENT_ROOT'] . "/upload/tmp/ammina.regions.city.load.csv");
	}

	if ($result->isSuccess()) {
		if (isset($_POST["save"])) {
			LocalRedirect("/bitrix/admin/ammina.regions.city.php?lang=" . LANGUAGE_ID . GetFilterParams("filter_", false));
		} else {
			LocalRedirect("/bitrix/admin/ammina.regions.city.load.php?lang=" . LANGUAGE_ID . GetFilterParams("filter_", false));
		}
	}
}

$APPLICATION->SetTitle(Loc::getMessage("AMMINA_REGIONS_PAGE_TITLE_LOAD"));

CUtil::InitJSCore();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
/*
Blocks\OrderBasket::getCatalogMeasures();
*/
// context menu
$aMenu = array();
$aMenu[] = array(
	"ICON" => "btn_list",
	"TEXT" => Loc::getMessage("AMMINA_REGIONS_TO_LIST"),
	"TITLE" => Loc::getMessage("AMMINA_REGIONS_TO_LIST_TITLE"),
	"LINK" => "/bitrix/admin/ammina.regions.city.php?lang=" . LANGUAGE_ID . GetFilterParams("filter_"),
);


$context = new CAdminContextMenu($aMenu);
$context->Show();

//errors
$errorMessage = "";
/*
if (!$result->isSuccess())
	foreach ($result->getErrors() as $error) {
		$errorMessage .= $error->getMessage() . "<br>\n";
	}

if (!empty($errorMessage)) {
	$admMessage = new CAdminMessage($errorMessage);
	echo $admMessage->Show();
}
*/
//prepare blocks order
$defaultBlocksPage = array(
	"city_load",
);

$formId = "ammina_regions_city_load";

$aTabs = array(
	array("DIV" => "tab_ammina", "TAB" => Loc::getMessage("AMMINA_REGIONS_TAB_CITY_LOAD"), "SHOW_WRAP" => "N", "IS_DRAGGABLE" => "Y"),
);

?>
<form method="POST" action="<?= $APPLICATION->GetCurPage() . "?lang=" . LANGUAGE_ID . GetFilterParams("filter_", false) ?>" name="<?= $formId ?>_form" id="<?= $formId ?>_form" enctype="multipart/form-data">
<?
$tabControl = new CAdminTabControlDrag($formId, $aTabs, $moduleId, false, true);
$tabControl->AddTabs($customTabber);
$tabControl->Begin();

$tabControl->BeginNextTab();
$customFastNavItems = array();
$customBlocksPage = array();
$fastNavItems = array();

foreach ($customDraggableBlocks->getBlocksBrief() as $blockId => $blockParams) {
	$defaultBlocksPage[] = $blockId;
	$customFastNavItems[$blockId] = $blockParams['TITLE'];
	$customBlocksPage[] = $blockId;
}

$blocksPage = $tabControl->getCurrentTabBlocksOrder($defaultBlocksPage);
$customNewBlockIds = array_diff($customBlocksPage, $blocksPage);
$blocksPage = array_merge($blocksPage, $customNewBlockIds);

foreach ($blocksPage as $item) {
	if (isset($customFastNavItems[$item]))
		$fastNavItems[$item] = $customFastNavItems[$item];
	else {
		$fastNavItems[$item] = Loc::getMessage("AMMINA_REGIONS_BLOCK_TITLE_" . toUpper($item));
	}
}
?>
	<tr>
		<td>
			<?= bitrix_sessid_post() ?>
			<div style="position: relative; vertical-align: top">
				<? $tabControl->DraggableBlocksStart(); ?>
				<?
				foreach ($blocksPage as $blockCode) {
					echo '<a id="' . $blockCode . '" class="adm-ammina-regions-fastnav-anchor"></a>';
					$tabControl->DraggableBlockBegin($fastNavItems[$blockCode], $blockCode);
					switch ($blockCode) {
						case "city_load":
							echo \Ammina\Regions\Helpers\Admin\Blocks\CityLoad::getEdit();
							break;
						default:
							echo $customDraggableBlocks->getBlockContent($blockCode, $tabControl->selectedTab);
							break;
					}
					$tabControl->DraggableBlockEnd();
				}
				?>
			</div>
		</td>
	</tr>
<?

$tabControl->EndTab();

$tabControl->Buttons(
	array(
		"back_url" => "/bitrix/admin/ammina.regions.city.php?lang=" . LANGUAGE_ID . GetFilterParams("filter_"))
);

$tabControl->End();
CAmminaRegions::showSupportForm();
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
