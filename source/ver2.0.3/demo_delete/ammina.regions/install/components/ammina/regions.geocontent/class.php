<?

use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Error;
use \Bitrix\Main\ErrorCollection;
use \Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('ammina.regions')) {
	ShowError(Loc::getMessage('AMMINA_COMPONENT_REGIONS_MODULE_NOT_INSTALLED'));
	return;
}

class AmminaRegionsGeoContentComponent extends CBitrixComponent
{
	private $cacheUsage = true;
	/** @var ErrorCollection */
	protected $errorCollection;

	public function __construct($component = NULL)
	{
		parent::__construct($component);
		$this->errorCollection = new ErrorCollection();
	}

	public function onPrepareComponentParams($params)
	{
		if (!CModule::IncludeModule("ammina.regions")) {
			return;
		}
		$params['SET_TAG_IDENT'] = ($params['SET_TAG_IDENT'] != "N" ? "Y" : "N");
		if (amreg_strlen($params['SET_TAG_TYPE']) <= 0) {
			$params['SET_TAG_TYPE'] = "span";
		}
		$params['SET_TAG_TYPE'] = trim(amreg_strtolower($params['SET_TAG_TYPE']));
		$params['IP'] = filter_var($params['IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
		if ($params['IP'] === false || amreg_strlen($params['IP']) <= 0) {
			$params['IP'] = $this->getIP();
		}
		if ($params['CACHE_TYPE'] == 'Y' || ($params['CACHE_TYPE'] == 'A' && Bitrix\Main\Config\Option::get('main', 'component_cache_on', 'Y') == 'Y')) {
			$params['CACHE_TIME'] = intval($params['CACHE_TIME']);
		} else {
			$params['CACHE_TIME'] = 0;
		}

		$app = \Bitrix\Main\Application::getInstance();
		if ($params['PREVENT_CITY'] <= 0) {
			$params['PREVENT_CITY'] = intval($app->getContext()->getRequest()->getCookie("ARG_CITY"));
		}
		if ($params['PREVENT_DOMAIN_ID'] <= 0) {
			$params['PREVENT_DOMAIN_ID'] = $GLOBALS['AMMINA_REGIONS']['SYS_CURRENT_DOMAIN_ID'];
		}
		if (!isset($params['SITE_ID']) || amreg_strlen($params['SITE_ID']) <= 0) {
			$params['SITE_ID'] = SITE_ID;
		}
		return $params;
	}

	protected function getIP()
	{
		$strIP = $_SERVER['REMOTE_ADDR'];
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$strIP = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$strIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		return $strIP;
	}

	public function executeComponent()
	{
		if (!CModule::IncludeModule("ammina.regions")) {
			return;
		}
		if (CAmminaRegions::isTestPeriodEnd()) {
			return false;
		}
		if ($this->isCacheDisabled() || $this->startResultCache(false, $this->getAdditionalCacheId(), $this->getComponentCachePath())) {
			$this->processResultData();
			$this->initResultCache();
			$this->includeComponentTemplate();
		}
	}

	protected function setCacheUsage($state)
	{
		$this->cacheUsage = (bool)$state;

		return $this;
	}

	public function isCacheDisabled()
	{
		return (bool)$this->cacheUsage === false;
	}

	protected function getAdditionalCacheId()
	{
		return array();
	}

	protected function getComponentCachePath()
	{
		return "/ammina/regions/geocontent/";
	}

	protected function initResultCache()
	{
		$this->setResultCacheKeys($this->getCacheKeys());
	}

	protected function getCacheKeys()
	{
		return array();
	}

	protected function processResultData()
	{
		if ($this->arParams['PREVENT_CITY'] <= 0) {
			$this->arParams['PREVENT_CITY'] = \Ammina\Regions\BlockTable::getCityIdByIP($this->arParams['IP']);
		}
		if ($this->arParams['PREVENT_CITY'] <= 0) {
			$arCity = \Ammina\Regions\CityTable::getList(array(
				"filter" => array(
					"IS_DEFAULT" => "Y",
				),
			))->fetch();
			if ($arCity) {
				$this->arParams['PREVENT_CITY'] = $arCity['ID'];
			}
		}
		/**
		 * Ищем геоконтент:
		 * 1. Если совпадает город
		 * 2. Если совпадает регион
		 * 3. Если совпадает страна
		 * 4. Если совпадает домен
		 * 5. Без страны, региона, города
		 *
		 * Если город по IP не определен, то выводим геоконтент без страны, города, региона
		 */
		if ($this->arParams['PREVENT_CITY'] > 0) {
			$arCity = \Ammina\Regions\CityTable::getList(array(
				"filter" => array(
					"ID" => $this->arParams['PREVENT_CITY'],
				),
				"select" => array(
					"CITY_ID" => "ID",
					"REGION_ID" => "REGION_ID",
					"COUNTRY_ID" => "REGION.COUNTRY_ID",
				),
			))->fetch();
			$rContent = \Ammina\Regions\ContentTable::getList(array(
				"filter" => array(
					"TYPE_ID" => $this->arParams['CONTENT_TYPE'],
					"ACTIVE" => "Y",
					array(
						"LOGIC" => "OR",
						array(
							"SITE_ID" => $this->arParams['SITE_ID'],
						),
						array(
							"SITE_ID" => "",
						),
					),
					array(
						"LOGIC" => "OR",
						array(
							"CITY_ID" => $arCity['CITY_ID'],
						),
						array(
							"REGION_ID" => $arCity['REGION_ID'],
						),
						array(
							"COUNTRY_ID" => $arCity['COUNTRY_ID'],
						),
						array(
							"DOMAIN_ID" => ($this->arParams['PREVENT_DOMAIN_ID'] > 0 ? $this->arParams['PREVENT_DOMAIN_ID'] : -1),
						),
						array(
							"CITY_ID" => array(0, false),
							"REGION_ID" => array(0, false),
							"COUNTRY_ID" => array(0, false),
							"DOMAIN_ID" => array(0, false),
						),
					),
				),
				"order" => array(
					"CITY_ID" => "DESC",
					"REGION_ID" => "DESC",
					"COUNTRY_ID" => "DESC",
					"DOMAIN_ID" => "DESC",
				),
				"select" => array(
					"ID", "TYPE_ID", "COUNTRY_ID", "REGION_ID", "CITY_ID", "DOMAIN_ID", "ACTIVE", "CONTENT", "CONTENT_LANG", "CONTENT_EXT", "TYPE_NAME" => "TYPE.NAME", "TYPE_IDENT" => "TYPE.IDENT", "TYPE_CLASS" => "TYPE.CLASS",
				),
			));
			$arData = false;
			while ($arContent = $rContent->fetch()) {
				if ($arContent['CITY_ID'] == $arCity['CITY_ID']) {
					$arData = $arContent;
				} elseif ($arContent['REGION_ID'] == $arCity['REGION_ID']) {
					$arData = $arContent;
				} elseif ($arContent['COUNTRY_ID'] == $arCity['COUNTRY_ID']) {
					$arData = $arContent;
				} elseif ($this->arParams['PREVENT_DOMAIN_ID'] > 0 && $arContent['DOMAIN_ID'] == $this->arParams['PREVENT_DOMAIN_ID']) {
					$arData = $arContent;
				} elseif ($arContent['CITY_ID'] <= 0 && $arContent['REGION_ID'] <= 0 && $arContent['COUNTRY_ID'] <= 0) {
					$arData = $arContent;
				}
				if ($arData) {
					break;
				}
			}
		} else {
			$arData = \Ammina\Regions\ContentTable::getList(array(
				"filter" => array(
					"TYPE_ID" => $this->arParams['CONTENT_TYPE'],
					"ACTIVE" => "Y",
					"CITY_ID" => array(0, false),
					"REGION_ID" => array(0, false),
					"COUNTRY_ID" => array(0, false),
					"DOMAIN_ID" => array(0, false),
				),
				"select" => array(
					"ID", "TYPE_ID", "COUNTRY_ID", "REGION_ID", "CITY_ID", "DOMAIN_ID", "ACTIVE", "CONTENT", "CONTENT_LANG", "CONTENT_EXT", "TYPE_NAME" => "TYPE.NAME", "TYPE_IDENT" => "TYPE.IDENT", "TYPE_CLASS" => "TYPE.CLASS",
				),
			))->fetch();
		}
		if (!empty($arData)) {
			$arData['ACTIVE_CONTENT'] = $arData['CONTENT'];
			$arData['ACTIVE_CONTENT_LANG'] = $arData['CONTENT_LANG'];
			if (!empty($arData['CONTENT_EXT']) && is_array($arData['CONTENT_EXT'])) {
				foreach ($arData['CONTENT_EXT'] as $arCurrentRule) {
					$isOk = false;
					if ($arCurrentRule['ACTIVE'] == "Y") {
						switch ($arCurrentRule['TYPE_RULE']) {
							case "TIMEFROMTO";
								$arCurTime = explode(":", date("H:i:s"));
								$iCurTime = $arCurTime[0] * 3600 + $arCurTime[1] * 60 + $arCurTime[2];
								$arFromTime = explode(":", trim($arCurrentRule['TYPE']['TIMEFROMTO']['FROM']));
								$iFromTime = $arFromTime[0] * 3600 + $arFromTime[1] * 60 + $arFromTime[2];
								if ($iFromTime < 0) {
									$iFromTime = 0;
								}
								$arToTime = explode(":", trim($arCurrentRule['TYPE']['TIMEFROMTO']['TO']));
								$iToTime = $arToTime[0] * 3600 + $arToTime[1] * 60 + $arToTime[2];
								if ($iToTime > 86399 || $iToTime <= 0) {
									$iToTime = 86399;
								}
								if ($iCurTime >= $iFromTime && $iCurTime <= $iToTime) {
									$isOk = true;
									$arData['ACTIVE_CONTENT'] = $arCurrentRule['CONTENT'];
									$arData['ACTIVE_CONTENT_LANG'] = $arCurrentRule['CONTENT_LANG'];
								}
								break;
							case "WEEKDAYS";
								if (in_array(date("N"), $arCurrentRule['TYPE']['WEEKDAYS'])) {
									$isOk = true;
									$arData['ACTIVE_CONTENT'] = $arCurrentRule['CONTENT'];
									$arData['ACTIVE_CONTENT_LANG'] = $arCurrentRule['CONTENT_LANG'];
								}
								break;
							case
							"DATEFROMTO";
								$strFrom = trim($arCurrentRule['TYPE']['DATEFROMTO']['FROM']);
								$strTo = $arCurrentRule['TYPE']['DATEFROMTO']['TO'];
								$iFrom = 0;
								$iTo = time() + 3600 * 24 * 365;
								if (amreg_strlen($strFrom) > 0) {
									$iFrom = MakeTimeStamp($strFrom, "DD.MM.YYYY HH:MI:SS");
								}
								if (amreg_strlen($strTo) > 0) {
									$iTo = MakeTimeStamp($strTo, "DD.MM.YYYY HH:MI:SS");
								}
								if (time() >= $iFrom && time() <= $iTo) {
									$isOk = true;
									$arData['ACTIVE_CONTENT'] = $arCurrentRule['CONTENT'];
									$arData['ACTIVE_CONTENT_LANG'] = $arCurrentRule['CONTENT_LANG'];
								}
								break;
							case
							"PHPCONDITION";
								if (amreg_strlen($arCurrentRule['TYPE']['PHPCONDITION']) > 0 && @eval("return " . $arCurrentRule['TYPE']['PHPCONDITION'] . ";")) {
									$isOk = true;
									$arData['ACTIVE_CONTENT'] = $arCurrentRule['CONTENT'];
									$arData['ACTIVE_CONTENT_LANG'] = $arCurrentRule['CONTENT_LANG'];
								}
								break;
						}
					}
					if ($isOk) {
						break;
					}
				}
			}
			if (!is_array($arData['ACTIVE_CONTENT_LANG'])) {
				$arData['ACTIVE_CONTENT_LANG'] = array($arData['ACTIVE_CONTENT_LANG']);
			}
			$arData['ACTIVE_CONTENT'] = CAmminaRegions::getLangFirstName(array_merge(array("ru" => $arData['ACTIVE_CONTENT']), $arData['ACTIVE_CONTENT_LANG']));
		}
		$this->arResult = $arData;
	}
}
