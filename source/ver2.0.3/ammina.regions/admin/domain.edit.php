<?

use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
Bitrix\Main\Loader::includeModule('ammina.regions');
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/ammina.regions/prolog.php");

Loc::loadMessages(__FILE__);
$ID = isset($_REQUEST["ID"]) ? intval($_REQUEST["ID"]) : 0;

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

$isSaleModule = CAmminaRegions::isIMExists();
$needFieldsRestore = $_SERVER["REQUEST_METHOD"] == "POST" && !$isSavingOperation;
$isNewItem = ($ID <= 0);
$arCurrentItem = false;
if ($ID > 0) {
	$arCurrentItem = \Ammina\Regions\DomainTable::getById($ID)->fetch();
}
if (!$arCurrentItem) {
	$isNewItem = true;
	$ID = false;
	$arCurrentItem = array(
		"VARIABLE_SEPARATOR" => ", ",
	);
}
$result = new \Bitrix\Main\Entity\Result();

$customTabber = new CAdminTabEngine("OnAdminAmminaRegionsDomainEdit");
$customDraggableBlocks = new CAdminDraggableBlockEngine('OnAdminAmminaRegionsDomainEditDraggable');

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
	$arLocations = $_POST['FIELDS']['LOCATION'];
	$arVariables = $_POST['FIELDS']['VARIABLES'];
	$arActions = $_POST['FIELDS']['ACTION'];
	unset($_POST['FIELDS']['LOCATION']);
	unset($_POST['FIELDS']['VARIABLES']);
	unset($_POST['FIELDS']['ACTION']);

	if ($isNewItem) {
		$oTableResult = \Ammina\Regions\DomainTable::add($_POST['FIELDS']);
		$ID = $oTableResult->getId();
	} else {
		$oTableResult = \Ammina\Regions\DomainTable::update($ID, $_POST['FIELDS']);
	}
	if (!$oTableResult->isSuccess()) {
		$result->addErrors($oTableResult->getErrors());
	} else {
		foreach ($arLocations as $k => $v) {
			if (intval($k) > 0) {
				if ($v['DELETE'] == "Y") {
					\Ammina\Regions\DomainLocationTable::delete($k);
				} else {
					\Ammina\Regions\DomainLocationTable::update($k, $v);
				}
			} elseif ($v['DELETE'] != "Y") {
				if ($v['COUNTRY_ID'] > 0 || $v['REGION_ID'] > 0 || $v['CITY_ID'] > 0) {
					$v['DOMAIN_ID'] = $ID;
					\Ammina\Regions\DomainLocationTable::add($v);
				}
			}
		}
		$arAllSystemVariables = array();
		$rVariable = \Ammina\Regions\VariableTable::getList(array(
			"filter" => array(
				"IS_SYSTEM" => "Y",
			),
		));
		while ($arVariable = $rVariable->fetch()) {
			$arAllSystemVariables[] = $arVariable['ID'];
		}
		$arCurrentVariables = array();
		$rCurrent = \Ammina\Regions\DomainVariableTable::getList(array(
			"filter" => array(
				"DOMAIN_ID" => $ID,
			),
		));
		while ($arCurrent = $rCurrent->fetch()) {
			if (!in_array($arCurrent['VARIABLE_ID'], $arAllSystemVariables)) {
				$arCurrentVariables[$arCurrent['VARIABLE_ID']] = $arCurrent;
			}
		}
		foreach ($arVariables as $k => $v) {
			if (in_array($k, $arAllSystemVariables)) {
				unset($arCurrentVariables[$k]);
				continue;
			}
			if (isset($arCurrentVariables[$k])) {
				\Ammina\Regions\DomainVariableTable::update($arCurrentVariables[$k]['ID'], array(
					"VALUE" => $v['VALUE'],
					"VALUE_LANG" => $v['VALUE_LANG'],
				));
				unset($arCurrentVariables[$k]);
			} else {
				\Ammina\Regions\DomainVariableTable::add(array(
					"VARIABLE_ID" => $k,
					"DOMAIN_ID" => $ID,
					"VALUE" => $v['VALUE'],
					"VALUE_LANG" => $v['VALUE_LANG'],
				));
			}
		}
		foreach ($arCurrentVariables as $k => $v) {
			\Ammina\Regions\DomainVariableTable::delete($v['ID']);
		}

		$arCurrentDomain = \Ammina\Regions\DomainTable::getRowById($ID);
		/*//Проверяем наличие доменного имени в сайте и обновляем
		$arSite = CSite::GetArrayByID($arCurrentDomain['SITE_ID']);
		$ar = explode("\n", str_replace("\r", "\n", $arSite["DOMAINS"]));
		$arDomains = array();
		if (is_array($ar) && count($ar) > 0) {
			foreach ($ar as $v) {
				if (amreg_strlen(trim($v)) > 0) {
					$arDomains[] = amreg_strtolower($v);
				}
			}
		}
		if (!in_array($arCurrentDomain['DOMAIN'], $arDomains)) {
			$arDomains[] = $arCurrentDomain['DOMAIN'];
			$oSite = new CSite();
			$oSite->Update($arSite['LID'], array(
				"DOMAINS" => implode("\n", $arDomains),
			));
		}
		*/
		//Проверяем для СЕО (сайтмап и robots)
		if ($arActions['MAKE_SETTINGS_SITEMAP'] == "Y") {
			\Ammina\Regions\DomainTable::doMakeSitemapSettingsForDomain($arCurrentDomain['ID']);
		}
		if ($arActions['MAKE_ROBOTS_FILE'] == "Y") {
			\Ammina\Regions\DomainTable::doMakeRobotsForDomain($arCurrentDomain['ID']);
		}
		//Проверяем действия с компанией
		if ($arActions['SALE_COMPANY_CREATE'] == "Y") {
			\Ammina\Regions\DomainTable::doMakeSaleCompanyForDomain($arCurrentDomain['ID']);
		}

		if ($arActions['SALE_COMPANY_RESTRICTION'] == "Y") {
			\Ammina\Regions\DomainTable::doMakeSaleCompanyRestrictionsForDomain($arCurrentDomain['ID']);
		}

		if ($arActions['SALE_COMPANY_GROUPS_CREATE'] == "Y") {
			\Ammina\Regions\DomainTable::doMakeSaleCompanyGroupsForDomain($arCurrentDomain['ID']);
		}

		if ($arActions['SALE_COMPANY_GROUPS_LINK'] == "Y" && $arCurrentDomain['SALE_UID'] > 0 && $arCurrentDomain['SALE_COMPANY_ID'] > 0) {
			\Ammina\Regions\DomainTable::doMakeSaleCompanyLinkGroupsForDomain($arCurrentDomain['ID']);
		}

		\Ammina\Regions\DomainVariableTable::doFillAllSystemVariables($ID);
	}
	if ($result->isSuccess()) {
		if (isset($_POST["save"])) {
			LocalRedirect("/bitrix/admin/ammina.regions.domain.php?lang=" . LANGUAGE_ID . GetFilterParams("filter_", false));
		} else {
			LocalRedirect("/bitrix/admin/ammina.regions.domain.edit.php?lang=" . LANGUAGE_ID . "&ID=" . $ID . GetFilterParams("filter_", false));
		}
	}
}

if ($ID > 0) {
	$APPLICATION->SetTitle(Loc::getMessage("AMMINA_REGIONS_PAGE_TITLE_EDIT"));
} else {
	$APPLICATION->SetTitle(Loc::getMessage("AMMINA_REGIONS_PAGE_TITLE_ADD"));
}

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
	"LINK" => "/bitrix/admin/ammina.regions.domain.php?lang=" . LANGUAGE_ID . GetFilterParams("filter_"),
);


$context = new CAdminContextMenu($aMenu);
$context->Show();

//errors
$errorMessage = "";

if (!$result->isSuccess())
	foreach ($result->getErrors() as $error) {
		$errorMessage .= $error->getMessage() . "<br>\n";
	}

if (!empty($errorMessage)) {
	$admMessage = new CAdminMessage($errorMessage);
	echo $admMessage->Show();
}

//prepare blocks order
$defaultBlocksPage = array(
	"domain",
	"domain.location",
	"domain.variable",
);

$formId = "ammina_regions_domain_edit";

$aTabs = array(
	array("DIV" => "tab_ammina", "TAB" => Loc::getMessage("AMMINA_REGIONS_TAB_DOMAIN"), "SHOW_WRAP" => "N", "IS_DRAGGABLE" => "Y"),
);
echo \Ammina\Regions\Helpers\Admin\Blocks\Domain::getScripts();
echo \Ammina\Regions\Helpers\Admin\Blocks\DomainLocation::getScripts();
echo \Ammina\Regions\Helpers\Admin\Blocks\DomainVariable::getScripts();
?>
<form method="POST" action="<?= $APPLICATION->GetCurPage() . "?lang=" . LANGUAGE_ID . GetFilterParams("filter_", false) ?>" name="<?= $formId ?>_form" id="<?= $formId ?>_form" enctype="multipart/form-data">
	<input type="hidden" name="ID" value="<?= $arCurrentItem['ID'] ?>"/>
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
			$fastNavItems[$item] = Loc::getMessage("AMMINA_REGIONS_BLOCK_TITLE_" . toUpper(str_replace(".", "_", $item)));
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
						case "domain":
							echo \Ammina\Regions\Helpers\Admin\Blocks\Domain::getEdit($arCurrentItem);
							break;
						case "domain.location":
							echo \Ammina\Regions\Helpers\Admin\Blocks\DomainLocation::getEdit($arCurrentItem);
							break;
						case "domain.variable":
							echo \Ammina\Regions\Helpers\Admin\Blocks\DomainVariable::getEdit($arCurrentItem);
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
		"back_url" => "/bitrix/admin/ammina.regions.domain.php?lang=" . LANGUAGE_ID . GetFilterParams("filter_"))
);

$tabControl->End();
CAmminaRegions::showSupportForm();
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");