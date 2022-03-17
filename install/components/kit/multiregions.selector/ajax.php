<?
/** @global CMain $APPLICATION */
define('STOP_STATISTICS', true);
define('PUBLIC_AJAX_MODE', true);
define('NOT_CHECK_PERMISSIONS', true);

use Bitrix\Main,
	Bitrix\Catalog;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
if (isset($_REQUEST['AJAX']) && $_REQUEST['AJAX'] == 'Y') {
	if (!defined("BX_UTF") || BX_UTF !== true) {
		$_REQUEST = $APPLICATION->ConvertCharsetArray($_REQUEST, "utf-8", "windows-1251");
	}
	if ($_REQUEST['action'] == "setPreventCity" && $_REQUEST['PREVENT_CITY'] > 0) {
		$APPLICATION->IncludeComponent(
			"kit:multiregions.selector",
			"",
			array()
		);
		$APPLICATION->RestartBuffer();
		$arData = array("STATUS" => "SUCCESS");
		if (COption::GetOptionString("kit.multiregions", "use_one_domain", "N") != "Y") {
			$iDomain = \Kit\MultiRegions\DomainTable::doFindDomainByCity($_REQUEST['PREVENT_CITY'], $_REQUEST['sid']);
			if ($iDomain > 0) {
				$strReferer = $_SERVER['HTTP_REFERER'];
				if (amreg_strlen($strReferer) > 0) {
					$ar = parse_url($strReferer);
					if ($ar['host'] == $_SERVER[\COption::GetOptionString("kit.multiregions", "host_var_name", "HTTP_HOST")]) {
						$strReferer = $ar['path'];
						if (amreg_strlen($ar['query']) > 0) {
							$strReferer .= '?' . $ar['query'];
						}
					} else {
						$strReferer = '/';
					}
				}
				$strLocation = \Kit\MultiRegions\DomainTable::doGetRedirectLinkByDomainId($iDomain, $_REQUEST['PREVENT_CITY'], $strReferer);
				if (amreg_strlen($strLocation) > 0) {
					$arData['LOCATION'] = $strLocation;
				}
			}
		} elseif (COption::GetOptionString("kit.multiregions", "use_one_domain", "N") == "Y" && COption::GetOptionString("kit.multiregions", "use_path_domain", "N") == "Y") {
			$iDomain = \Kit\MultiRegions\DomainTable::doFindDomainByCity($_REQUEST['PREVENT_CITY'], $_REQUEST['sid']);
			if ($iDomain > 0) {
				$strReferer = $_SERVER['HTTP_REFERER'];
				if (amreg_strlen($strReferer) > 0) {
					$ar = parse_url($strReferer);
					if ($ar['host'] == $_SERVER[\COption::GetOptionString("kit.multiregions", "host_var_name", "HTTP_HOST")]) {
						$strReferer = $ar['path'];
						if (amreg_strlen($ar['query']) > 0) {
							$strReferer .= '?' . $ar['query'];
						}
					} else {
						$strReferer = '/';
					}
				}
				$strReferer = \Kit\MultiRegions\DomainTable::doGetOriginalUrl($strReferer);
				$strLocation = \Kit\MultiRegions\DomainTable::doGetRedirectLinkByDomainId($iDomain, $_REQUEST['PREVENT_CITY'], $strReferer);
				if (amreg_strlen($strLocation) > 0) {
					$arData['LOCATION'] = $strLocation;
				}
			}
		}
		header('Content-Type: application/json');
		echo Main\Web\Json::encode($arData);
		//require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
		CMain::FinalActions();
		die();
	} elseif ($_REQUEST['action'] == "getCityForm") {
		$arParams = array();
		foreach ($_REQUEST as $k => $v) {
			if (amreg_strpos($k, '~') === 0) {
				$arParams[amreg_substr($k, 1)] = $v;
			}
		}
		$arParams['PREVENT_CITY'] = $_REQUEST['PREVENT_CITY'];
		$arResult = $APPLICATION->IncludeComponent(
			"kit:multiregions.selector",
			"",
			$arParams
		);
		$APPLICATION->RestartBuffer();
		header('Content-Type: application/json');
		echo Main\Web\Json::encode(array("STATUS" => "SUCCESS", "RESULT" => $arResult));
		//require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
		CMain::FinalActions();
		die();
	}

	die();
}