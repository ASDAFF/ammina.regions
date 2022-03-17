<?

use Bitrix\Main\IO\Path;
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

$sTableID = "tbl_ammina_regions_domain";
$isSaleModule = CAmminaRegions::isIMExists();

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
		"id" => "DOMAIN",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_DOMAIN"),
		"filterable" => "?",
		"quickSearch" => "?",
		"default" => false,
	),
	array(
		"id" => "PATHCODE",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_PATHCODE"),
		"filterable" => "?",
		"quickSearch" => "?",
		"default" => false,
	),
	array(
		"id" => "ORDER_PREFIX",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_ORDER_PREFIX"),
		"filterable" => "?",
		"quickSearch" => "?",
		"default" => false,
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
		"id" => "IS_DEFAULT",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_IS_DEFAULT"),
		"filterable" => "",
		"type" => "list",
		"items" => array(
			"Y" => GetMessage("MAIN_YES"),
			"N" => GetMessage("MAIN_NO"),
		),
		"default" => false,
	),
	array(
		"id" => "NOINDEX",
		"name" => Loc::getMessage("AMMINA_REGIONS_FILTER_NOINDEX"),
		"filterable" => "",
		"type" => "list",
		"items" => array(
			"Y" => GetMessage("MAIN_YES"),
			"N" => GetMessage("MAIN_NO"),
		),
		"default" => false,
	),
);
$arFilter = array();
$lAdmin->AddFilter($filterFields, $arFilter);

if ($lAdmin->EditAction()) {
	$arAllAllowVariables = array();
	$rVars = \Ammina\Regions\VariableTable::getList(
		array(
			"filter" => array(
				"!IS_SYSTEM" => "Y"
			)
		)
	);
	while ($arVar = $rVars->fetch()) {
		$arAllAllowVariables[$arVar['ID']] = $arVar;
	}
	foreach ($FIELDS as $ID => $postFields) {
		$DB->StartTransaction();
		$ID = IntVal($ID);

		if (!$lAdmin->IsUpdated($ID)) {
			continue;
		}

		$allowedFields = array(
			"ACTIVE",
			"IS_DEFAULT",
			"NAME",
			"DOMAIN",
			"PATHCODE",
			"ORDER_PREFIX",
			"DEFAULT_EMAIL",
		);
		$arFields = array();
		foreach ($allowedFields as $fieldId) {
			if (array_key_exists($fieldId, $postFields)) {
				$arFields[$fieldId] = $postFields[$fieldId];
			}
		}

		$oUpdate = \Ammina\Regions\DomainTable::update($ID, $arFields);
		if (!$oUpdate->isSuccess()) {
			$lAdmin->AddUpdateError(GetMessage("AMMINA_REGIONS_UPDATE_ERROR", array("#ID#" => $ID, "#ERROR_TEXT#" => implode(", ", $oUpdate->getErrorMessages()))), $ID);
			$DB->Rollback();
		} else {
			if (isset($_POST['FIELDS'][$ID]['VAR'])) {
				$vars = $_POST['FIELDS'][$ID]['VAR'];
				$arCurrentVariables = array();
				$rCurrent = \Ammina\Regions\DomainVariableTable::getList(
					array(
						"filter" => array(
							"DOMAIN_ID" => $ID,
							"!VARIABLE.IS_SYSTEM" => "Y"
						),
						"select" => array("*", "CODE" => "VARIABLE.CODE", "IS_SYSTEM" => "VARIABLE.IS_SYSTEM"),
					)
				);
				while ($arCurrent = $rCurrent->fetch()) {
					$arCurrentVariables[$arCurrent['VARIABLE_ID']][] = $arCurrent;
				}
				foreach ($vars as $varId => $arVar) {
					if (isset($arAllAllowVariables[$varId])) {
						foreach ($arVar as $k1 => $val) {
							$val = trim($val);
							if (amreg_strlen($val) <= 0) {
								unset($arVar[$k1]);
							}
						}
						$arVar = array_values($arVar);
						if (isset($arCurrentVariables[$varId][0])) {
							\Ammina\Regions\DomainVariableTable::update($arCurrentVariables[$varId][0]['ID'], array("VALUE" => $arVar));
							unset($arCurrentVariables[$varId][0]);
						} else {
							$arVarFields = array(
								"DOMAIN_ID" => $ID,
								"VARIABLE_ID" => $varId,
								"VALUE" => $arVar
							);
							\Ammina\Regions\DomainVariableTable::add($arVarFields);
						}
						foreach ($arCurrentVariables[$varId] as $k => $v) {
							\Ammina\Regions\DomainVariableTable::delete($v['ID']);
						}
					}
				}
			}
		}
		$DB->Commit();
	}
}

if (($arID = $lAdmin->GroupAction()) && $modulePermissions >= "W") {
	$bUseYandexEngine = false;
	$bUseGoogleEngine = false;
	/**
	 * @var \Bitrix\Seo\Engine\Yandex
	 */
	$oYandexEngine = null;
	/**
	 * @var \Bitrix\Seo\Engine\Google
	 */
	$oGoogleEngine = null;
	$arYandexFeed = array();
	$arGoogleFeed = array();
	if (in_array($_REQUEST['action'], array("seo_yandex_bind", "seo_yandex_verify", "seo_google_bind", "seo_google_verify")) && CModule::IncludeModule("seo") && CModule::IncludeModule("socialservices") && $USER->CanDoOperation('seo_tools')) {
		$bUseYandexEngine = true;
		$oYandexEngine = new \Bitrix\Seo\Engine\Yandex();
		try {
			$arYandexFeed = $oYandexEngine->getFeeds();
		} catch (\Bitrix\Seo\Engine\YandexException $e) {
		}
		$bUseGoogleEngine = true;
		try {
			$oGoogleEngine = new \Bitrix\Seo\Engine\Google();
		} catch (Exception $e) {
		}
		$arGoogleFeed = $oGoogleEngine->getFeeds();
	}
	if ($_REQUEST['action_target'] == 'selected') {
		$arID = array();
		$dbResultList = \Ammina\Regions\DomainTable::getList(
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

	foreach ($arID as $ID) {
		if (amreg_strlen($ID) <= 0) {
			continue;
		}

		switch ($_REQUEST['action']) {
			case "delete":
				@set_time_limit(0);
				$bComplete = true;
				$rRecord = \Ammina\Regions\DomainTable::getList(
					array(
						"filter" => array("ID" => $ID),
						"select" => array("ID"),
					)
				);
				$arRecordOld = $rRecord->Fetch();
				$DB->StartTransaction();
				$rLinkRecord = \Ammina\Regions\DomainVariableTable::getList(
					array(
						"filter" => array(
							"DOMAIN_ID" => $ID,
						),
					)
				);
				while ($arLinkRecord = $rLinkRecord->fetch()) {
					$rOperation = \Ammina\Regions\DomainVariableTable::delete($arLinkRecord['ID']);
					if (!$rOperation->isSuccess()) {
						$bComplete = false;
					}
				}
				$rLinkRecord = \Ammina\Regions\DomainLocationTable::getList(
					array(
						"filter" => array(
							"DOMAIN_ID" => $ID,
						),
					)
				);
				while ($arLinkRecord = $rLinkRecord->fetch()) {
					$rOperation = \Ammina\Regions\DomainLocationTable::delete($arLinkRecord['ID']);
					if (!$rOperation->isSuccess()) {
						$bComplete = false;
					}
				}
				if ($bComplete) {
					$rOperation = \Ammina\Regions\DomainTable::delete($ID);
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
			case "fill_locative":
				@set_time_limit(0);
				$bComplete = true;
				$DB->StartTransaction();
				\Ammina\Regions\DomainVariableTable::doFillAllSystemVariables($ID);
				\Ammina\Regions\DomainTable::doFillVariablesPadezhCityName($ID);
				\Ammina\Regions\DomainTable::doFillVariablesPadezhRegionName($ID);
				\Ammina\Regions\DomainTable::doFillVariablesPadezhCityRegionName($ID);
				\Ammina\Regions\DomainTable::doFillVariableLocativeCityName($ID);
				\Ammina\Regions\DomainTable::doFillVariableLocativeRegionName($ID);
				\Ammina\Regions\DomainTable::doFillVariableLocativeCityRegionName($ID);
				$DB->Commit();
				break;
			case "robots_generate":
				@set_time_limit(0);
				$bComplete = true;
				$DB->StartTransaction();
				\Ammina\Regions\DomainTable::doMakeRobotsForDomain($ID);
				$DB->Commit();
				break;
			case "sitemap_generate":
				@set_time_limit(0);
				$bComplete = true;
				$DB->StartTransaction();
				\Ammina\Regions\DomainTable::doMakeSitemapSettingsForDomain($ID);
				$DB->Commit();
				break;
			case "set_noindex":
				@set_time_limit(0);
				$bComplete = true;
				$DB->StartTransaction();
				\Ammina\Regions\DomainTable::update($ID, array("NOINDEX" => "Y"));
				$DB->Commit();
				break;
			case "set_index":
				@set_time_limit(0);
				$bComplete = true;
				$DB->StartTransaction();
				\Ammina\Regions\DomainTable::update($ID, array("NOINDEX" => "N"));
				$DB->Commit();
				break;
			case "seo_yandex_bind":
				@set_time_limit(0);
				$bComplete = true;
				$arRecord = \Ammina\Regions\DomainTable::getList(
					array(
						"filter" => array("ID" => $ID),
						"select" => array("ID", "DOMAIN", "SITE_DIR" => "SITE.DIR"),
					)
				)->fetch();
				if ($arRecord && !isset($arYandexFeed[amreg_strtolower($arRecord['DOMAIN'])])) {
					if ($bUseYandexEngine) {
						try {
							$res = $oYandexEngine->addSite($arRecord['DOMAIN'], $arRecord['SITE_DIR']);
							$res['_domain'] = $arRecord['DOMAIN'];
						} catch (\Bitrix\Seo\Engine\YandexException $e) {
							$bComplete = false;
						}
					}
				}
				break;
			case "seo_yandex_verify":
				@set_time_limit(0);
				$bComplete = true;
				$arRecord = \Ammina\Regions\DomainTable::getList(
					array(
						"filter" => array("ID" => $ID),
						"select" => array("ID", "DOMAIN", "SITE_DIR" => "SITE.DIR", "SITE_DOC_ROOT" => "SITE.DOC_ROOT"),
					)
				)->fetch();
				if ($arRecord && isset($arYandexFeed[amreg_strtolower($arRecord['DOMAIN'])]) && !$arYandexFeed[amreg_strtolower($arRecord['DOMAIN'])]['verified']) {
					if ($bUseYandexEngine) {
						try {
							$uin = $oYandexEngine->getVerifySiteUin(amreg_strtolower($arRecord['DOMAIN']));
							if ($uin) {
								$filename = "yandex_" . $uin . ".html";

								$path = \Bitrix\Main\IO\Path::combine(
									(
									amreg_strlen($arRecord['SITE_DOC_ROOT']) > 0
										? $arRecord['SITE_DOC_ROOT']
										: $_SERVER['DOCUMENT_ROOT']
									),
									$arRecord['SITE_DIR'],
									$filename
								);
								$obFile = new \Bitrix\Main\IO\File($path);
								$obFile->putContents('<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>Verification: ' . $uin . '</body></html>');

								$res = $oYandexEngine->verifySite($arRecord['DOMAIN']);
								//$obFile->delete();
							}
							$res['_domain'] = $arRecord['DOMAIN'];
						} catch (\Bitrix\Seo\Engine\YandexException $e) {
							$bComplete = false;
						}
					}
				}
				break;
			case "seo_google_bind":
				@set_time_limit(0);
				$bComplete = true;
				$arRecord = \Ammina\Regions\DomainTable::getList(
					array(
						"filter" => array("ID" => $ID),
						"select" => array("ID", "DOMAIN", "SITE_DIR" => "SITE.DIR"),
					)
				)->fetch();
				if ($arRecord && !isset($arGoogleFeed[amreg_strtolower($arRecord['DOMAIN'])]) && !isset($arGoogleFeed[amreg_strtolower($arRecord['DOMAIN'])]['binded'])) {
					if ($bUseGoogleEngine) {
						try {
							$res = $oGoogleEngine->addSite($arRecord['DOMAIN'], $arRecord['SITE_DIR']);
							$res['_domain'] = $arRecord['DOMAIN'];
						} catch (Exception $e) {
							$bComplete = false;
						}
					}
				}
				break;
			case "seo_google_verify":
				@set_time_limit(0);
				$bComplete = true;
				$arRecord = \Ammina\Regions\DomainTable::getList(
					array(
						"filter" => array("ID" => $ID),
						"select" => array("ID", "DOMAIN", "SITE_DIR" => "SITE.DIR", "SITE_DOC_ROOT" => "SITE.DOC_ROOT"),
					)
				)->fetch();
				if ($arRecord && isset($arGoogleFeed[amreg_strtolower($arRecord['DOMAIN'])]) && !$arGoogleFeed[amreg_strtolower($arRecord['DOMAIN'])]['verified']) {
					if ($bUseGoogleEngine) {
						try {
							$filename = $oGoogleEngine->verifyGetToken($arRecord['DOMAIN'], $arRecord['SITE_DIR']);
							$filename = preg_replace("/^(.*?)\..*$/", "\\1.html", $filename);
							if (amreg_strlen($filename) > 0) {
								$path = \Bitrix\Main\IO\Path::combine(
									(
									amreg_strlen($arRecord['SITE_DOC_ROOT']) > 0
										? $arRecord['SITE_DOC_ROOT']
										: $_SERVER['DOCUMENT_ROOT']
									),
									$arRecord['SITE_DIR'],
									$filename
								);

								$obFile = new \Bitrix\Main\IO\File($path);

								/*
								if($obFile->isExists())
								{
									$obFile->delete();
								}*/

								$obFile->putContents('google-site-verification: ' . $filename);

								$res = $oGoogleEngine->verifySite($arRecord['DOMAIN'], $arRecord['SITE_DIR']);
							}
							$res['_domain'] = $arRecord['DOMAIN'];
						} catch (Exception $e) {
							$bComplete = false;
						}
					}
				}
				break;
			case "make_pathcode":
				@set_time_limit(0);
				$bComplete = true;
				$DB->StartTransaction();
				$bUpdated = false;
				$arDomain = \Ammina\Regions\DomainTable::getRowById($ID);
				if ($arDomain) {
					$ar = explode(".", $arDomain["DOMAIN"]);
					if (amreg_strlen($ar[0]) > 0) {
						\Ammina\Regions\DomainTable::update($ID, array("PATHCODE" => $ar[0]));
						$bUpdated = true;
					}
					if (!$bUpdated && $arDomain['CITY_ID'] > 0) {
						$arCity = \Ammina\Regions\CityTable::getRowById($arDomain['CITY_ID']);
						$arFormatName = \Ammina\Regions\DomainVariableTable::getCityFormatInfo($arCity['ID']);
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
						\Ammina\Regions\DomainTable::update($ID, array("PATHCODE" => $strDomain));
					}
				}
				$DB->Commit();
				break;
			case "make_order_prefix":
				@set_time_limit(0);
				$bComplete = true;
				$DB->StartTransaction();
				$bUpdated = false;
				$arDomain = \Ammina\Regions\DomainTable::getRowById($ID);
				if ($arDomain) {
					$ar = explode(".", $arDomain["DOMAIN"]);
					if (amreg_strlen($ar[0]) > 0) {
						\Ammina\Regions\DomainTable::update($ID, array("ORDER_PREFIX" => $ar[0]));
						$bUpdated = true;
					}
					if (!$bUpdated && $arDomain['CITY_ID'] > 0) {
						$arCity = \Ammina\Regions\CityTable::getRowById($arDomain['CITY_ID']);
						$arFormatName = \Ammina\Regions\DomainVariableTable::getCityFormatInfo($arCity['ID']);
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
						\Ammina\Regions\DomainTable::update($ID, array("ORDER_PREFIX" => $strDomain));
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
		"id" => "NAME_LANG",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_NAME_LANG"),
		"sort" => "NAME_LANG",
		"default" => false,
	),
	array(
		"id" => "ACTIVE",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_ACTIVE"),
		"sort" => "ACTIVE",
		"default" => true,
	),
	array(
		"id" => "IS_DEFAULT",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_IS_DEFAULT"),
		"sort" => "IS_DEFAULT",
		"default" => true,
	),
	array(
		"id" => "DOMAIN",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_DOMAIN"),
		"sort" => "DOMAIN",
		"default" => true,
	),
	array(
		"id" => "PATHCODE",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_PATHCODE"),
		"sort" => "PATHCODE",
		"default" => true,
	),
	array(
		"id" => "ORDER_PREFIX",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_ORDER_PREFIX"),
		"sort" => "ORDER_PREFIX",
		"default" => true,
	),
	array(
		"id" => "SITE_ID",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_SITE_ID"),
		"sort" => "SITE_ID",
		"default" => true,
	),
	array(
		"id" => "SITE_EXT",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_SITE_EXT"),
		"default" => true,
	),
	array(
		"id" => "CITY_ID",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_CITY_ID"),
		"sort" => "CITY_ID",
		"default" => true,
	),
	array(
		"id" => "NOINDEX",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_NOINDEX"),
		"sort" => "NOINDEX",
		"default" => true,
	),
	array(
		"id" => "SALE_UID",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_SALE_UID"),
		"sort" => "SALE_UID",
		"default" => true,
	),
	array(
		"id" => "SALE_COMPANY_ID",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_SALE_COMPANY_ID"),
		"sort" => "SALE_COMPANY_ID",
		"default" => true,
	),
	array(
		"id" => "DEFAULT_EMAIL",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_DEFAULT_EMAIL"),
		"sort" => "DEFAULT_EMAIL",
		"default" => true,
	),
	array(
		"id" => "VARIABLE_SEPARATOR",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_VARIABLE_SEPARATOR"),
		"default" => true,
	),
	array(
		"id" => "PRICES",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_PRICES"),
		"default" => true,
	),
	array(
		"id" => "STORES",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_STORES"),
		"default" => true,
	),
	array(
		"id" => "COUNTERS",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_COUNTERS"),
		"default" => false,
	),
	array(
		"id" => "HEAD_STRING",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_HEAD_STRING"),
		"default" => false,
	),
);

if (CModule::IncludeModule("seo") && CModule::IncludeModule("socialservices") && $USER->CanDoOperation('seo_tools')) {
	$arHeader[] = array(
		"id" => "SEO_YANDEX_BINDED",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_SEO_YANDEX_BINDED"),
		"default" => false,
	);
	$arHeader[] = array(
		"id" => "SEO_YANDEX_VERIFIED",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_SEO_YANDEX_VERIFIED"),
		"default" => false,
	);
	$arHeader[] = array(
		"id" => "SEO_GOOGLE_BINDED",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_SEO_GOOGLE_BINDED"),
		"default" => false,
	);
	$arHeader[] = array(
		"id" => "SEO_GOOGLE_VERIFIED",
		"content" => Loc::getMessage("AMMINA_REGIONS_FIELD_SEO_GOOGLE_VERIFIED"),
		"default" => false,
	);
}

$rVar = \Ammina\Regions\VariableTable::getList(
	array(
		"order" => array("IS_SYSTEM" => "ASC", "NAME" => "ASC"),
		"filter" => array("!IS_SYSTEM" => "Y"),
	)
);
while ($arVar = $rVar->fetch()) {
	$arHeader[] = array(
		"id" => "VAR_" . $arVar['CODE'],
		"content" => "var: " . $arVar['NAME'],
		"default" => false,
	);
}

$lAdmin->AddHeaders($arHeader);

$rsItems = \Ammina\Regions\DomainTable::getList(
	array(
		"order" => $arOrder,
		"filter" => $arFilter,
		"select" => array("*", "CITY_NAME" => "CITY.NAME", "USER_LOGIN" => "SALE_USER.LOGIN", "USER_NAME" => "SALE_USER.NAME", "USER_LAST_NAME" => "SALE_USER.LAST_NAME")
	)
);
$rsItems = new CAdminUiResult($rsItems, $sTableID);
$rsItems->NavStart();

$lAdmin->SetNavigationParams($rsItems);

$arAllPrices = array();
$arAllStores = array();
if ($isSaleModule) {
	$rPrices = \CCatalogGroup::GetList(
		array(
			"SORT" => "ASC",
			"NAME_LANG" => "ASC",
		)
	);
	while ($arPrice = $rPrices->Fetch()) {
		$arAllPrices[$arPrice['ID']] = '[' . $arPrice['ID'] . '] ' . htmlspecialcharsbx($arPrice['NAME_LANG']);
	}
	$rStores = \CCatalogStore::GetList(array());
	while ($arStore = $rStores->Fetch()) {
		$arAllStores[$arStore['ID']] = '[' . $arStore['ID'] . '] ' . htmlspecialcharsbx($arStore['TITLE']);
	}
	$arAllCompany = array();
	$rCompanies = \Bitrix\Sale\Internals\CompanyTable::getList(
		array(
			"order" => array(
				"SORT" => "ASC",
				"NAME" => "ASC",
			),
		)
	);
	while ($arCompanies = $rCompanies->fetch()) {
		$arAllCompany[$arCompanies['ID']] = "[" . $arCompanies['ID'] . "] " . $arCompanies['NAME'];
	}
}

$bUseYandexEngine = false;
$bUseGoogleEngine = false;
/**
 * @var \Bitrix\Seo\Engine\Yandex
 */
$oYandexEngine = null;
/**
 * @var \Bitrix\Seo\Engine\Google
 */
$oGoogleEngine = null;
$arYandexFeed = array();
$arGoogleFeed = array();
if (CModule::IncludeModule("seo") && CModule::IncludeModule("socialservices") && $USER->CanDoOperation('seo_tools')) {
	if (isset($lAdmin->aVisibleHeaders['SEO_YANDEX_BINDED']) || isset($lAdmin->aVisibleHeaders['SEO_YANDEX_VERIFIED'])) {
		$bUseYandexEngine = true;
		$oYandexEngine = new \Bitrix\Seo\Engine\Yandex();
		try {
			$arYandexFeed = $oYandexEngine->getFeeds();
		} catch (\Bitrix\Seo\Engine\YandexException $e) {
		}
	}
	if (isset($lAdmin->aVisibleHeaders['SEO_GOOGLE_BINDED']) || isset($lAdmin->aVisibleHeaders['SEO_GOOGLE_VERIFIED'])) {
		$bUseGoogleEngine = true;
		$oGoogleEngine = new \Bitrix\Seo\Engine\Google();
		try {
			$arGoogleFeed = $oGoogleEngine->getFeeds();
		} catch (Exception $e) {
		}
	}
}

while ($arData = $rsItems->NavNext()) {
	$row =& $lAdmin->AddRow($arData['ID'], $arData, 'ammina.regions.domain.edit.php?ID=' . $arData['ID'] . '&lang=' . LANGUAGE_ID, Loc::getMessage("AMMINA_REGIONS_RECORD_EDIT"));
	$row->AddViewField("ID", '<a href="ammina.regions.domain.edit.php?ID=' . $arData['ID'] . '&lang=' . LANGUAGE_ID . '">' . $arData['ID'] . '</a>');
	$row->AddInputField("NAME");
	$arNameLang = array();
	if (is_array($arData['NAME_LANG'])) {
		foreach ($arData['NAME_LANG'] as $k => $v) {
			$arNameLang[] = $k . ": " . $v;
		}
	}
	$row->AddViewField("NAME_LANG", implode("<br>", $arNameLang));
	$row->AddCheckField("ACTIVE");
	$row->AddCheckField("IS_DEFAULT");
	$row->AddCheckField("NOINDEX");
	$row->AddViewField("DOMAIN", '<a href="http://' . $arData['DOMAIN'] . '" target="_blank">' . htmlspecialcharsbx($arData['DOMAIN']) . '</a>');
	$row->AddViewField("SITE_EXT", implode(", ", $arData['SITE_EXT']));
	$row->AddInputField("DOMAIN");
	$row->AddInputField("PATHCODE");
	$row->AddInputField("ORDER_PREFIX");
	$row->AddInputField("DEFAULT_EMAIL");
	$row->AddViewField("CITY_ID", $arData['CITY_ID'] > 0 ? '[' . $arData['CITY_ID'] . '] <a href="/bitrix/admin/ammina.regions.city.edit.php?ID=' . $arData['CITY_ID'] . '&lang=' . LANGUAGE_ID . '">' . $arData['CITY_NAME'] . '</a>' : "");
	$row->AddViewField("SALE_UID", $arData['SALE_UID'] > 0 ? '[<a href="/bitrix/admin/user_edit.php?ID=' . $arData['SALE_UID'] . '&lang=' . LANGUAGE_ID . '">' . $arData['SALE_UID'] . '</a>] (' . $arData['USER_LOGIN'] . ') ' . $arData['USER_NAME'] . " " . $arData['USER_LAST_NAME'] : "");
	$row->AddViewField("SALE_COMPANY_ID", $arData['SALE_COMPANY_ID'] > 0 ? '<a href="/bitrix/admin/sale_company_edit.php?ID=' . $arData['SALE_COMPANY_ID'] . '&lang=' . LANGUAGE_ID . '">' . $arAllCompany[$arData['SALE_COMPANY_ID']] . '</a>' : "");
	$arValue = array();
	if (!empty($arData['PRICES'])) {
		foreach ($arData['PRICES'] as $v) {
			$arValue[] = $arAllPrices[$v];
		}
	}
	$row->AddViewField("PRICES", implode(", ", $arValue));
	$arValue = array();
	if (!empty($arData['STORES'])) {
		foreach ($arData['STORES'] as $v) {
			$arValue[] = $arAllStores[$v];
		}
	}
	$row->AddViewField("STORES", implode(", ", $arValue));

	$bIsExistsVariables = false;
	$arAllVariables = array("-1");
	foreach ($lAdmin->aVisibleHeaders as $k => $v) {
		if (amreg_strpos($k, 'VAR_') === 0) {
			$arAllVariables[] = amreg_substr($k, 4);
			$bIsExistsVariables = true;
		}
	}
	if ($bIsExistsVariables) {
		$arCurrentVariables = array();
		$rVariables = \Ammina\Regions\VariableTable::getList(
			array(
				"filter" => array(
					"CODE" => $arAllVariables
				)
			)
		);
		$rCurrent = \Ammina\Regions\DomainVariableTable::getList(
			array(
				"filter" => array(
					"DOMAIN_ID" => $arData['ID'],
					"VARIABLE.CODE" => $arAllVariables,
				),
				"select" => array("*", "CODE" => "VARIABLE.CODE", "IS_SYSTEM" => "VARIABLE.IS_SYSTEM"),
			)
		);
		while ($arCurrent = $rCurrent->fetch()) {
			$row->AddViewField("VAR_" . $arCurrent['CODE'], (is_array($arCurrent['VALUE']) ? implode("<hr/>", $arCurrent['VALUE']) : $arCurrent['VALUE']));
			if ($arCurrent['IS_SYSTEM'] != "Y") {
				$val = $arCurrent['VALUE'];
				if (!is_array($val)) {
					$val = array($val);
				}
				$val[] = "";
				$strEditContent = "";
				$i = 1;
				foreach ($val as $v) {
					$strEditContent .= '<textarea name="FIELDS[' . $arData['ID'] . '][VAR][' . $arCurrent['VARIABLE_ID'] . '][' . $i . ']">' . htmlspecialchars($v) . '</textarea><br/>';
					$i++;
				}
				$row->AddEditField("VAR_" . $arCurrent['CODE'], $strEditContent);
			}
		}
	}

	$bAllowActionYandexBinded = false;
	$bAllowActionYandexVerified = false;
	$bAllowActionGoogleBinded = false;
	$bAllowActionGoogleVerified = false;
	if ($bUseYandexEngine) {
		if (isset($lAdmin->aVisibleHeaders['SEO_YANDEX_BINDED'])) {
			if (isset($arYandexFeed[amreg_strtolower($arData['DOMAIN'])])) {
				$row->AddViewField("SEO_YANDEX_BINDED", Loc::getMessage("MAIN_YES"));
			} else {
				$row->AddViewField("SEO_YANDEX_BINDED", Loc::getMessage("MAIN_NO"));
				$bAllowActionYandexBinded = true;
			}
		}
		if (isset($lAdmin->aVisibleHeaders['SEO_YANDEX_VERIFIED'])) {
			if (isset($arYandexFeed[amreg_strtolower($arData['DOMAIN'])]) && $arYandexFeed[amreg_strtolower($arData['DOMAIN'])]['verified']) {
				$row->AddViewField("SEO_YANDEX_VERIFIED", Loc::getMessage("MAIN_YES"));
			} else {
				$row->AddViewField("SEO_YANDEX_VERIFIED", Loc::getMessage("MAIN_NO"));
				$bAllowActionYandexVerified = true;
			}
		}
	}
	if ($bUseGoogleEngine) {
		if (isset($lAdmin->aVisibleHeaders['SEO_GOOGLE_BINDED'])) {
			if (isset($arGoogleFeed[amreg_strtolower($arData['DOMAIN'])]) && $arGoogleFeed[amreg_strtolower($arData['DOMAIN'])]['binded']) {
				$row->AddViewField("SEO_GOOGLE_BINDED", Loc::getMessage("MAIN_YES"));
			} else {
				$row->AddViewField("SEO_GOOGLE_BINDED", Loc::getMessage("MAIN_NO"));
				$bAllowActionGoogleBinded = true;
			}
		}
		if (isset($lAdmin->aVisibleHeaders['SEO_GOOGLE_VERIFIED'])) {
			if (isset($arGoogleFeed[amreg_strtolower($arData['DOMAIN'])]) && $arGoogleFeed[amreg_strtolower($arData['DOMAIN'])]['verified']) {
				$row->AddViewField("SEO_GOOGLE_VERIFIED", Loc::getMessage("MAIN_YES"));
			} else {
				$row->AddViewField("SEO_GOOGLE_VERIFIED", Loc::getMessage("MAIN_NO"));
				$bAllowActionGoogleVerified = true;
			}
		}
	}

	$arActions = array();
	if ($modulePermissions >= "W") {
		$arActions[] = array(
			"ICON" => "edit",
			"TEXT" => Loc::getMessage("MAIN_ADMIN_MENU_EDIT"),
			"DEFAULT" => true,
			"ACTION" => $lAdmin->ActionRedirect("ammina.regions.domain.edit.php?ID=" . $arData['ID'] . "&lang=" . LANGUAGE_ID),
		);
		$arActions[] = array(
			"SEPARATOR" => true,
		);
		$arActions[] = array(
			"ICON" => "fill",
			"TEXT" => Loc::getMessage("AMMINA_REGIONS_ACTION_FILL_LOCATIVE"),
			"ACTION" => $lAdmin->ActionDoGroup($arData['ID'], "fill_locative"),
		);
		$arActions[] = array(
			"ICON" => "sitemap",
			"TEXT" => Loc::getMessage("AMMINA_REGIONS_ACTION_SITEMAP_GENERATE"),
			"ACTION" => $lAdmin->ActionDoGroup($arData['ID'], "sitemap_generate"),
		);
		$arActions[] = array(
			"ICON" => "robots",
			"TEXT" => Loc::getMessage("AMMINA_REGIONS_ACTION_ROBOTS_GENERATE"),
			"ACTION" => $lAdmin->ActionDoGroup($arData['ID'], "robots_generate"),
		);
		/*$arActions[] = array(
			"ICON" => "fill_seo",
			"TEXT" => Loc::getMessage("AMMINA_REGIONS_ACTION_FILL_SEO"),
			"ACTION" => $lAdmin->ActionDoGroup($arData['ID'], "fill_seo"),
		);*/
		if ($bAllowActionYandexBinded || $bAllowActionYandexVerified || $bAllowActionGoogleBinded || $bAllowActionGoogleVerified) {
			$arActions[] = array(
				"SEPARATOR" => true,
			);
			if ($bAllowActionYandexBinded) {
				$arActions[] = array(
					"ICON" => "seo_yandex_bind",
					"TEXT" => Loc::getMessage("AMMINA_REGIONS_ACTION_SEO_YANDEX_BIND"),
					"ACTION" => $lAdmin->ActionDoGroup($arData['ID'], "seo_yandex_bind"),
				);
			}
			if ($bAllowActionYandexVerified) {
				$arActions[] = array(
					"ICON" => "seo_yandex_verify",
					"TEXT" => Loc::getMessage("AMMINA_REGIONS_ACTION_SEO_YANDEX_VERIFY"),
					"ACTION" => $lAdmin->ActionDoGroup($arData['ID'], "seo_yandex_verify"),
				);
			}
			if ($bAllowActionGoogleBinded) {
				$arActions[] = array(
					"ICON" => "seo_google_bind",
					"TEXT" => Loc::getMessage("AMMINA_REGIONS_ACTION_SEO_GOOGLE_BIND"),
					"ACTION" => $lAdmin->ActionDoGroup($arData['ID'], "seo_google_bind"),
				);
			}
			if ($bAllowActionGoogleVerified) {
				$arActions[] = array(
					"ICON" => "seo_google_verify",
					"TEXT" => Loc::getMessage("AMMINA_REGIONS_ACTION_SEO_GOOGLE_VERIFY"),
					"ACTION" => $lAdmin->ActionDoGroup($arData['ID'], "seo_google_verify"),
				);
			}
		}
		$arActions[] = array(
			"SEPARATOR" => true,
		);
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("AMMINA_REGIONS_ACTION_DELETE"),
			"ACTION" => "if(confirm('" . GetMessage('AMMINA_REGIONS_ACTION_DELETE_CONFIRM') . "')) " . $lAdmin->ActionDoGroup($arData['ID'], "delete"),
		);
		$arActions[] = array(
			"SEPARATOR" => true,
		);
		if ($arData['NOINDEX'] == "Y") {
			$arActions[] = array(
				"ICON" => "set_index",
				"TEXT" => Loc::getMessage("AMMINA_REGIONS_ACTION_SET_INDEX"),
				"ACTION" => $lAdmin->ActionDoGroup($arData['ID'], "set_index"),
			);
		} else {
			$arActions[] = array(
				"ICON" => "set_noindex",
				"TEXT" => Loc::getMessage("AMMINA_REGIONS_ACTION_SET_NOINDEX"),
				"ACTION" => $lAdmin->ActionDoGroup($arData['ID'], "set_noindex"),
			);
		}
		$arActions[] = array(
			"ICON" => "make_pathcode",
			"TEXT" => Loc::getMessage("AMMINA_REGIONS_ACTION_MAKE_PATHCODE"),
			"ACTION" => $lAdmin->ActionDoGroup($arData['ID'], "make_pathcode"),
		);
		$arActions[] = array(
			"ICON" => "make_order_prefix",
			"TEXT" => Loc::getMessage("AMMINA_REGIONS_ACTION_MAKE_ORDER_PREFIX"),
			"ACTION" => $lAdmin->ActionDoGroup($arData['ID'], "make_order_prefix"),
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
			"LINK" => "ammina.regions.domain.edit.php?lang=" . LANGUAGE_ID,
			"TITLE" => Loc::getMessage("AMMINA_REGIONS_NEW_RECORD_TITLE"),
		),
	);

	$lAdmin->AddAdminContextMenu($aContext);

	$lAdmin->AddGroupActionTable(
		array(
			"edit" => true,
			"delete" => true,
			"fill_locative" => Loc::getMessage("AMMINA_REGIONS_ACTION_FILL_LOCATIVE"),
			"sitemap_generate" => Loc::getMessage("AMMINA_REGIONS_ACTION_SITEMAP_GENERATE"),
			"robots_generate" => Loc::getMessage("AMMINA_REGIONS_ACTION_ROBOTS_GENERATE"),
			"seo_yandex_bind" => Loc::getMessage("AMMINA_REGIONS_ACTION_SEO_YANDEX_BIND"),
			"seo_yandex_verify" => Loc::getMessage("AMMINA_REGIONS_ACTION_SEO_YANDEX_VERIFY"),
			"seo_google_bind" => Loc::getMessage("AMMINA_REGIONS_ACTION_SEO_GOOGLE_BIND"),
			"seo_google_verify" => Loc::getMessage("AMMINA_REGIONS_ACTION_SEO_GOOGLE_VERIFY"),
			"set_noindex" => Loc::getMessage("AMMINA_REGIONS_ACTION_SET_NOINDEX"),
			"set_index" => Loc::getMessage("AMMINA_REGIONS_ACTION_SET_INDEX"),
			"make_pathcode" => Loc::getMessage("AMMINA_REGIONS_ACTION_MAKE_PATHCODE"),
			"make_order_prefix" => Loc::getMessage("AMMINA_REGIONS_ACTION_MAKE_ORDER_PREFIX"),
		)
	);
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