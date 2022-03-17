<?

use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
Bitrix\Main\Loader::includeModule('kit.multiregions');

Loc::loadMessages(__FILE__);

$modulePermissions = $APPLICATION->GetGroupRight("kit.multiregions");
if ($modulePermissions < "W") {
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_AGENT_CHECK", true);
define("NOT_CHECK_PERMISSIONS", true);
$isSaleModule = CKitMultiRegions::isIMExists();
$arResult = array(
	"STATUS" => "SUCCESS",
);

function amultiregions_doMarkSearchText($strName, $strQuery)
{
	$test1 = amreg_strtolower($strName);
	$test2 = amreg_strtolower($strQuery);
	$iPos = amreg_strpos($test1, $test2);
	if ($iPos !== false) {
		$s1 = amreg_substr($strName, 0, $iPos);
		$s2 = amreg_substr($strName, $iPos, amreg_strlen($strQuery));
		$s3 = amreg_substr($strName, $iPos + amreg_strlen($strQuery));
		$strName = $s1 . '<strong>' . $s2 . '</strong>' . $s3;
	}
	return $strName;
}

if (!defined("BX_UTF") || BX_UTF !== true) {
	$_REQUEST = $APPLICATION->ConvertCharsetArray($_REQUEST, "utf-8", "windows-1251");
}

if (in_array($_REQUEST['action'], array("country", "region", "city", "domain"))) {
	$arResult['ITEMS'][0] = array(
		"ID" => 0,
		"NAME" => Loc::getMessage("KIT_MULTIREGIONS_NOT_SELECTED"),
		"FULL_NAME" => Loc::getMessage("KIT_MULTIREGIONS_NOT_SELECTED"),
		"FORMAT_NAME" => Loc::getMessage("KIT_MULTIREGIONS_NOT_SELECTED"),
	);
	$cnt = intval($_REQUEST['cnt']);
	if ($cnt <= 0 || $cnt > 100) {
		$cnt = 50;
	}
	$minLength = 2;
	if (isset($_REQUEST['min-length'])) {
		$minLength = intval($_REQUEST['min-length']);
	}
	$query = $_REQUEST['q'];
	$ar = explode(",", $query);
	$query = trim($ar[count($ar) - 1]);
	if (amreg_strlen($query) >= $minLength) {
		if ($_REQUEST['action'] == "country") {
			$arSelect = array(
				"IDD",
				"NAME"
			);
			$arFilter = array();
			if (amreg_strlen($query) > 0) {
				$arFilter = array(
					"LOGIC" => "OR",
					array(
						"%NAME" => $query,
					),
					array(
						"%COUNTRY_LANG.NAME" => $query,
					),
				);
			}
			$rItems = \Kit\MultiRegions\CountryTable::getList(array(
				"filter" => $arFilter,
				"select" => $arSelect,
				"limit" => $cnt,
				"order" => array("NAME" => "ASC"),
				"runtime" => array(
					new \Bitrix\Main\ORM\Fields\ExpressionField('IDD',
						'DISTINCT %s', array("ID")
					)
				)
			));
			while ($arItem = $rItems->fetch()) {
				$arItem['ID'] = $arItem['IDD'];
				$arData = array(
					"ID" => $arItem['ID'],
					"NAME" => CKitMultiRegions::getFirstNotEmpty(\Kit\MultiRegions\CountryLangTable::getLangNames($arItem['ID'])),
				);
				$arData['FULL_NAME'] = $arData['NAME'];
				$arData['FORMAT_NAME'] = amultiregions_doMarkSearchText($arData['NAME'], $query);
				$arResult['ITEMS'][] = $arData;
			}
		} elseif ($_REQUEST['action'] == "region") {
			$arSelect = array(
				"IDD",
				"NAME",
				"COUNTRY_ID",
				"COUNTRY_NAME" => "COUNTRY.NAME",
			);
			$arFilter = array();
			if (amreg_strlen($query) > 0) {
				$arFilter = array(
					"LOGIC" => "OR",
					array(
						"%NAME" => $query,
					),
					array(
						"%REGION_LANG.NAME" => $query,
					),
				);
			}
			$rItems = \Kit\MultiRegions\RegionTable::getList(array(
				"filter" => $arFilter,
				"select" => $arSelect,
				"limit" => $cnt,
				"order" => array("NAME" => "ASC"),
				"runtime" => array(
					new \Bitrix\Main\ORM\Fields\ExpressionField('IDD',
						'DISTINCT %s', array("ID")
					)
				)
			));
			while ($arItem = $rItems->fetch()) {
				$arItem['ID'] = $arItem['IDD'];
				$arData = array(
					"ID" => $arItem['ID'],
					"NAME" => CKitMultiRegions::getFirstNotEmpty(\Kit\MultiRegions\RegionLangTable::getLangNames($arItem['ID'])),
					"COUNTRY_NAME" => CKitMultiRegions::getFirstNotEmpty(\Kit\MultiRegions\CountryLangTable::getLangNames($arItem['COUNTRY_ID'])),
				);
				$arData['FULL_NAME'] = $arData['COUNTRY_NAME'] . ", " . $arData['NAME'];
				$arData['FORMAT_NAME'] = amultiregions_doMarkSearchText($arData['NAME'], $query) . ", " . $arData['COUNTRY_NAME'];
				$arResult['ITEMS'][] = $arData;
			}
		} elseif ($_REQUEST['action'] == "city") {
			$arSelect = array(
				"IDD",
				"NAME",
				"REGION_ID",
				"COUNTRY_ID" => "REGION.COUNTRY_ID",
				"REGION_NAME" => "REGION.NAME",
				"COUNTRY_NAME" => "REGION.COUNTRY.NAME",
			);
			$arFilter = array();
			if (amreg_strlen($query) > 0) {
				$arFilter = array(
					"LOGIC" => "OR",
					array(
						"%NAME" => $query,
					),
					array(
						"%CITY_LANG.NAME" => $query,
					),
				);
			}

			$rItems = \Kit\MultiRegions\CityTable::getList(array(
				"filter" => $arFilter,
				"select" => $arSelect,
				"limit" => $cnt,
				"order" => array("NAME" => "ASC"),
				"runtime" => array(
					new \Bitrix\Main\ORM\Fields\ExpressionField('IDD',
						'DISTINCT %s', array("ID")
					)
				)
			));
			while ($arItem = $rItems->fetch()) {
				$arItem['ID'] = $arItem['IDD'];
				$arData = array(
					"ID" => $arItem['ID'],
					"NAME" => CKitMultiRegions::getFirstNotEmpty(\Kit\MultiRegions\CityLangTable::getLangNames($arItem['ID'])),
					"REGION_NAME" => CKitMultiRegions::getFirstNotEmpty(\Kit\MultiRegions\RegionLangTable::getLangNames($arItem['REGION_ID'])),
					"COUNTRY_NAME" => CKitMultiRegions::getFirstNotEmpty(\Kit\MultiRegions\CountryLangTable::getLangNames($arItem['COUNTRY_ID'])),
				);
				$arData['FULL_NAME'] = $arData['COUNTRY_NAME'] . ", " . $arData['REGION_NAME'] . ", " . $arData['NAME'];
				$arData['FORMAT_NAME'] = amultiregions_doMarkSearchText($arData['NAME'], $query) . ", " . $arData['REGION_NAME'] . ", " . $arData['COUNTRY_NAME'];
				$arResult['ITEMS'][] = $arData;
			}
		} elseif ($_REQUEST['action'] == "domain") {
			if (amreg_strpos($query, '[') === 0 && amreg_strpos($query, ']') !== false) {
				$query = trim(amreg_substr($query, amreg_strpos($query, ']') + 1));
			}
			if (amreg_strrpos($query, ')') === amreg_strlen($query) - 1) {
				$query = trim(amreg_substr($query, 0, amreg_strrpos($query, '(')));
			}
			$arSelect = array(
				"ID",
				"NAME",
				"DOMAIN",
			);
			$arFilter = array();
			if (amreg_strlen($query) > 0) {
				$arFilter = array(
					"LOGIC" => "OR",
					array(
						"%NAME" => $query,
					),
					array(
						"%NAME_LANG" => $query,
					),
					array(
						"%DOMAIN" => $query,
					),
				);
			}
			$cnt = intval($_REQUEST['cnt']);
			if ($cnt <= 0 || $cnt > 100) {
				$cnt = 50;
			}
			$rItems = \Kit\MultiRegions\DomainTable::getList(array(
				"filter" => $arFilter,
				"select" => $arSelect,
				"limit" => $cnt,
				"order" => array("NAME" => "ASC"),
			));
			while ($arItem = $rItems->fetch()) {
				$arData = array(
					"ID" => $arItem['ID'],
					"NAME" => \Kit\MultiRegions\DomainTable::getLangName($arItem['ID']),
					"DOMAIN" => $arItem['DOMAIN'],
				);
				$arData['FULL_NAME'] = "[" . $arData['ID'] . "] " . $arData['NAME'] . " (" . $arData['DOMAIN'] . ")";
				$arData['FORMAT_NAME'] = "[" . $arData['ID'] . "] " . amultiregions_doMarkSearchText($arData['NAME'], $query) . " (" . $arData['DOMAIN'] . ")";
				$arResult['ITEMS'][] = $arData;
			}
		}
	}
} else {
	$arResult['STATUS'] = "ERROR";
}

$APPLICATION->RestartBuffer();

header('Content-Type: application/json');
if (!defined("BX_UTF") || BX_UTF !== true) {
	$arResult = $APPLICATION->ConvertCharsetArray($arResult, "windows-1251", "utf-8");
}
echo json_encode($arResult);
\CMain::FinalActions();
die();
