<?

use Kit\MultiRegions\CityLangTable;
use Kit\MultiRegions\CountryLangTable;
use Kit\MultiRegions\RegionLangTable;
use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Error;
use \Bitrix\Main\ErrorCollection;
use \Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('kit.multiregions')) {
	ShowError(Loc::getMessage('KIT_COMPONENT_MULTIREGIONS_MODULE_NOT_INSTALLED'));
	return;
}

class KitMultiRegionsSelectorComponent extends CBitrixComponent
{
	private $action = '';
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
		global $APPLICATION;
		if (!CModule::IncludeModule("kit.multiregions")) {
			return;
		}
		$params['CHANGE_CITY_MANUAL'] = ($params['CHANGE_CITY_MANUAL'] == "Y" ? "Y" : "N");
		$params['CITY_VERIFYCATION'] = ($params['CITY_VERIFYCATION'] != "Y" ? "N" : "Y");
		$params['USE_GPS'] = ($params['USE_GPS'] == "Y" ? "Y" : "N");
		if (!in_array($params['SHOW_CITY_TYPE'], array("R", "F", "D"))) {
			$params['SHOW_CITY_TYPE'] = "R";
		}
		if (!in_array($params['SEARCH_CITY_TYPE'], array("R", "Q"))) {
			$params['SEARCH_CITY_TYPE'] = "R";
		}
		$params['COUNT_SHOW_CITY'] = intval($params['COUNT_SHOW_CITY']);
		if ($params['COUNT_SHOW_CITY'] <= 0) {
			$params['COUNT_SHOW_CITY'] = 24;
		}

		$params['MOBILE_CHANGE_CITY_MANUAL'] = ($params['MOBILE_CHANGE_CITY_MANUAL'] == "Y" ? "Y" : "N");
		$params['MOBILE_CITY_VERIFYCATION'] = ($params['MOBILE_CITY_VERIFYCATION'] != "Y" ? "N" : "Y");
		$params['MOBILE_USE_GPS'] = ($params['MOBILE_USE_GPS'] == "Y" ? "Y" : "N");
		if (!in_array($params['MOBILE_SHOW_CITY_TYPE'], array("R", "F", "D"))) {
			$params['MOBILE_SHOW_CITY_TYPE'] = "R";
		}
		if (!in_array($params['MOBILE_SEARCH_CITY_TYPE'], array("R", "Q"))) {
			$params['MOBILE_SEARCH_CITY_TYPE'] = "R";
		}
		$params['MOBILE_COUNT_SHOW_CITY'] = intval($params['MOBILE_COUNT_SHOW_CITY']);
		if ($params['MOBILE_COUNT_SHOW_CITY'] <= 0) {
			$params['MOBILE_COUNT_SHOW_CITY'] = 24;
		}

		$params['SEPARATE_SETTINGS_MOBILE'] = ($params['SEPARATE_SETTINGS_MOBILE'] == "Y" ? "Y" : "N");

		if ($params['SEPARATE_SETTINGS_MOBILE'] == "Y") {
			$oDetector = new \AMREG_Mobile_Detect();
			if ($oDetector->isMobile() || $oDetector->isTablet()) {
				$params['CHANGE_CITY_MANUAL'] = $params['MOBILE_CHANGE_CITY_MANUAL'];
				$params['CITY_VERIFYCATION'] = $params['MOBILE_CITY_VERIFYCATION'];
				$params['USE_GPS'] = $params['MOBILE_USE_GPS'];
				$params['SHOW_CITY_TYPE'] = $params['MOBILE_SHOW_CITY_TYPE'];
				$params['SEARCH_CITY_TYPE'] = $params['MOBILE_SEARCH_CITY_TYPE'];
				$params['COUNT_SHOW_CITY'] = $params['MOBILE_COUNT_SHOW_CITY'];
			}
		}

		$params['PRIORITY_DOMAIN'] = ($params['PRIORITY_DOMAIN'] == "Y" ? "Y" : "N");
		$params['INCLUDE_JQUERY'] = ($params['INCLUDE_JQUERY'] != "Y" ? "N" : "Y");
		$params['ALLOW_REDIRECT'] = ($params['ALLOW_REDIRECT'] == "Y" ? "Y" : "N");

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
			if (amreg_strlen($_REQUEST['argcity']) > 0) {
				$params['PREVENT_CITY'] = $_REQUEST['argcity'];
			} elseif (amreg_strlen($_REQUEST['PREVENT_CITY']) > 0) {
				$params['PREVENT_CITY'] = $_REQUEST['PREVENT_CITY'];
			} else {
				$params['PREVENT_CITY'] = intval($app->getContext()->getRequest()->getCookie("ARG_CITY"));
			}
		}
		if ($params['PREVENT_CITY'] <= 0) {
			$params['PREVENT_CITY'] = \Kit\MultiRegions\BlockTable::getCityIdByIP($params['IP']);
			$params['CONFIRM_REQUEST_SHOW'] = true;
		}
		if ($params['PREVENT_CITY'] <= 0 && $GLOBALS['KIT_MULTIREGIONS']['SYS_MAIN_CITY_ID'] > 0) {
			$params['PREVENT_CITY'] = $GLOBALS['KIT_MULTIREGIONS']['SYS_MAIN_CITY_ID'];
			$params['CONFIRM_REQUEST_SHOW'] = true;
		}
		$bNotInDomain = false;
		if ($params['PREVENT_CITY'] > 0) {
			//Проверим - попадает ли выбранный город в обслуживаемые филиалом. если нет - принудительный запрос города
			$iDomain = \Kit\MultiRegions\DomainTable::doFindDomainByCity($params['PREVENT_CITY']);
			if ($iDomain != $GLOBALS['KIT_MULTIREGIONS']['SYS_CURRENT_DOMAIN_ID']) {
				$bNotInDomain = true;
				$params['CONFIRM_REQUEST_SHOW'] = true;
			}
		}

		if ($params['PRIORITY_DOMAIN'] == "Y") {
			$params['CONFIRM_REQUEST_SHOW'] = false;
			if ($bNotInDomain) {
				$params['PREVENT_CITY'] = \Kit\MultiRegions\BlockTable::getCityIdByIP($params['IP']);
				$iDomain = \Kit\MultiRegions\DomainTable::doFindDomainByCity($params['PREVENT_CITY']);
				if ($iDomain != $GLOBALS['KIT_MULTIREGIONS']['SYS_CURRENT_DOMAIN_ID']) {
					$params['PREVENT_CITY'] = $GLOBALS['KIT_MULTIREGIONS']['SYS_MAIN_CITY_ID'];
				}
				if (intval($app->getContext()->getRequest()->getCookie("ARG_CITY")) != $params['PREVENT_CITY']) {
					$this->setPreventCityCookie($params['PREVENT_CITY']);
				}
			}
		}

		if ($_SESSION['ARG_OLD_CITY'] > 0) {
			$params['ARG_OLD_CITY'] = $_SESSION['ARG_OLD_CITY'];
		}

		if (!isset($params['SITE_ID']) || amreg_strlen($params['SITE_ID']) <= 0) {
			$params['SITE_ID'] = SITE_ID;
		}
		if ($params['ALLOW_REDIRECT'] == "Y" && COption::GetOptionString("kit.multiregions", "use_one_domain", "N") != "Y" && $params['PREVENT_CITY'] > 0) {
			$iDomain = \Kit\MultiRegions\DomainTable::doFindDomainByCity($params['PREVENT_CITY']);
			if ($iDomain != $GLOBALS['KIT_MULTIREGIONS']['SYS_CURRENT_DOMAIN_ID']) {
				$strLocation = \Kit\MultiRegions\DomainTable::doGetRedirectLinkByDomainId($iDomain, $params['PREVENT_CITY'], $APPLICATION->GetCurPageParam("", array("")));
				$APPLICATION->AddHeadString('<script type="text/javascript" data-skip-moving="true">window.location="' . $strLocation . '";</script>');
			}
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

	public function getAction()
	{
		return $this->action;
	}

	protected function setAction($action)
	{
		$this->action = $action;
	}

	protected function prepareAction()
	{
		$requestAction = $this->request->getQuery('action');
		if (amreg_strlen($requestAction) <= 0) {
			$requestAction = $this->request->getPost('action');
		}

		if ($this->request->isAjaxRequest() && $requestAction === 'getCityForm') {
			$action = 'getCityForm';
		} elseif ($this->request->isAjaxRequest() && $requestAction === 'setPreventCity') {
			$action = 'setPreventCity';
		} else {
			$action = 'initialLoad';
		}

		return $action;
	}

	protected function doAction()
	{
		$action = $this->getAction();

		if (is_callable(array($this, $action . 'Action'))) {
			call_user_func(array($this, $action . 'Action'));
		}
	}

	public function executeComponent()
	{
		if (!CModule::IncludeModule("kit.multiregions")) {
			return;
		}

		if ($this->arParams['INCLUDE_JQUERY'] == "Y") {
			CJSCore::Init(array("jquery2"));
		}
		$action = $this->prepareAction();
		$this->setAction($action);
		$this->doAction();
		if ($this->hasErrors()) {
			return $this->processErrors();
		} elseif ($this->action == "getCityForm") {
			return $this->arResult;
		}
	}

	protected function hasErrors()
	{
		return (bool)count($this->errorCollection);
	}

	protected function processErrors()
	{
		if (!empty($this->errorCollection)) {
			/** @var Error $error */
			foreach ($this->errorCollection as $error) {
				$code = $error->getCode();

				if ($code == self::ERROR_TEXT) {
					ShowError($error->getMessage());
				}
			}
		}

		return false;
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
		return array($this->arParams['PREVENT_CITY']);
	}

	protected function getComponentCachePath()
	{
		return "/kit/multiregions/selector/";
	}

	protected function initResultCache()
	{
		$this->setResultCacheKeys($this->getCacheKeys());
	}

	protected function getCacheKeys()
	{
		return array();
	}

	protected function setPreventCityAction()
	{
		if ($this->request->getPost("PREVENT_CITY") > 0) {
			$this->setPreventCityCookie($this->request->getPost("PREVENT_CITY"));
		}
		unset($_SESSION['BX_GEO_IP']);
	}

	protected function initialLoadAction()
	{
		if ($this->isCacheDisabled() || $this->startResultCache(false, $this->getAdditionalCacheId(), $this->getComponentCachePath())) {
			$this->processResultData();
			if (!$this->hasErrors()) {
				$this->initResultCache();
				$this->includeComponentTemplate();
			}
		}
	}

	protected function getCityFormAction()
	{
		if ($this->arParams['PREVENT_CITY'] <= 0) {
			$this->arParams['PREVENT_CITY'] = \Kit\MultiRegions\BlockTable::getCityIdByIP($this->arParams['IP']);
		}
		$this->arResult = array(
			"IS_OK" => true,
			"TITLE" => Loc::getMessage("KIT_COMPONENT_MULTIREGIONS_FORM_TITLE"),
			"INPUT_PLACEHOLDER" => Loc::getMessage("KIT_COMPONENT_MULTIREGIONS_INPUT_PLACEHOLDER_TITLE"),
			"ITEMS" => array(),
		);
		/**
		 * Если нет GPS координат и нет запроса - то ищем избранное + текущий город по IP.
		 * Если нет GPS координат и есть запрос - то ищем в соответствии с запросом. сортировка по позиции вхождения в название города
		 * Если есть GPS координаты и нет запроса - то ищем в радиусе от GPS, выводим ограниченный набор ближайших с сортировкой по расстоянию от текущей точки
		 * Если есть GPS координаты и запрос - то ищем в радиусе от GPS и запросм с расширением зоны до coords/accuracy с сортировкой по расстоянию. если не находим - расширяем полностью на всю базу без сортировки по расстоянию
		 **/
		$gps = $this->request->getPost("GPS");
		$query = trim($_REQUEST["qcity"]);
		if (amreg_strlen($query) < 2) {
			$query = "";
		}

		if ($this->arParams['SHOW_CITY_TYPE'] == "D" && !is_array($gps) && amreg_strlen($query) <= 0) {
			$this->obtainCityFormDataNoGpsNoQueryDomain();
		} elseif ($this->arParams['SHOW_CITY_TYPE'] == "F" && !is_array($gps) && amreg_strlen($query) <= 0) {
			$this->obtainCityFormDataNoGpsNoQuery();
		} elseif (!is_array($gps) && amreg_strlen($query) <= 0) {
			$this->obtainCityFormDataNoGpsNoQuery();
		} elseif (!is_array($gps) && amreg_strlen($query) > 0) {
			$this->obtainCityFormDataNoGpsQuery($query);
		} elseif (is_array($gps) && amreg_strlen($query) <= 0) {
			$this->obtainCityFormDataGpsNoQuery($gps['coords']['latitude'], $gps['coords']['longitude']);
		} elseif (is_array($gps) && amreg_strlen($query) > 0) {
			$this->obtainCityFormDataGpsQuery($query, $gps['coords']['latitude'], $gps['coords']['longitude']);
		}
	}

	protected function obtainCityFormDataNoGpsNoQueryDomain()
	{
		$rDomains = \Kit\MultiRegions\DomainTable::getList(array(
			"filter" => array(
				"SITE_ID" => $this->arParams['SITE_ID'],
				"ACTIVE" => "Y",
			),
			"select" => array("ID", "NAME", "DOMAIN", "CITY_ID"),
		));
		$arAllDomains = array(
			"BY_ID" => array(),
			"BY_CITY_ID" => array(),
		);
		while ($arDomain = $rDomains->fetch()) {
			$arDomain['NAME'] = \Kit\MultiRegions\DomainTable::getLangName($arDomain['ID'], LANGUAGE_ID);
			$arAllDomains['BY_ID'][$arDomain['ID']] = $arDomain;
			$arAllDomains['BY_CITY_ID'][$arDomain['CITY_ID']] = $arDomain;
		}
		$arAllCityId = array_keys($arAllDomains['BY_CITY_ID']);
		$arAllCityId[] = -1;
		$arSelect = array(
			"CITY_ID" => "ID",
			"REGION_ID" => "REGION_ID",
			"COUNTRY_ID" => "REGION.COUNTRY_ID",
			"CITY_NAME" => "NAME",
			"REGION_NAME" => "REGION.NAME",
			"COUNTRY_NAME" => "REGION.COUNTRY.NAME",
		);
		$arFilter = array(
			"@ID" => $arAllCityId,
		);
		if ($this->arParams['PREVENT_CITY'] > 0) {
			$arFilter = array(
				"LOGIC" => "OR",
				array("ID" => $this->arParams['PREVENT_CITY']),
				$arFilter,
			);
		}
		$rCity = \Kit\MultiRegions\CityTable::getList(array(
			"filter" => $arFilter,
			"limit" => $this->arParams['COUNT_SHOW_CITY'],
			"order" => array(
				"NAME" => "ASC",
			),
			"select" => $arSelect,
		));
		while ($arCity = $rCity->fetch()) {
			if ($arCity['CITY_ID'] == $this->arParams['PREVENT_CITY']) {
				$arCity['IS_CURRENT'] = "Y";
			}
			$arCity['DOMAIN_NAME'] = $arAllDomains['BY_CITY_ID'][$arCity['CITY_ID']]['NAME'];
			$this->arResult['ITEMS'][] = $this->normalizeCityNames($arCity);
		}
	}

	protected function obtainCityFormDataNoGpsNoQuery()
	{
		$arSelect = array(
			"CITY_ID" => "ID",
			"REGION_ID" => "REGION_ID",
			"COUNTRY_ID" => "REGION.COUNTRY_ID",
			"CITY_NAME" => "NAME",
			"REGION_NAME" => "REGION.NAME",
			"COUNTRY_NAME" => "REGION.COUNTRY.NAME",
		);
		$arFilter = array(
			"IS_FAVORITE" => "Y",
		);
		if ($this->arParams['PREVENT_CITY'] > 0) {
			$arFilter = array(
				"LOGIC" => "OR",
				array("ID" => $this->arParams['PREVENT_CITY']),
				$arFilter,
			);
		}
		$rCity = \Kit\MultiRegions\CityTable::getList(array(
			"filter" => $arFilter,
			"limit" => $this->arParams['COUNT_SHOW_CITY'],
			"order" => array(
				"NAME" => "ASC",
			),
			"select" => $arSelect,
		));
		if (($rCity->getSelectedRowsCount() <= 1 && $this->arParams['PREVENT_CITY'] > 0) || ($rCity->getSelectedRowsCount() <= 0 && $this->arParams['PREVENT_CITY'] <= 0)) {
			return $this->obtainCityFormDataNoGpsNoQueryDomain();
		}
		while ($arCity = $rCity->fetch()) {
			if ($arCity['CITY_ID'] == $this->arParams['PREVENT_CITY']) {
				$arCity['IS_CURRENT'] = "Y";
			}
			$this->arResult['ITEMS'][] = $this->normalizeCityNames($arCity);
		}
	}

	protected function obtainCityFormDataNoGpsQuery($query)
	{
		$arSelect = array(
			"IDD",
			"CITY_ID" => "ID",
			"REGION_ID" => "REGION_ID",
			"COUNTRY_ID" => "REGION.COUNTRY_ID",
			"CITY_NAME" => "NAME",
			"REGION_NAME" => "REGION.NAME",
			"COUNTRY_NAME" => "REGION.COUNTRY.NAME",
			"POSINSTR",
		);
		$arFilter = array(
			"LOGIC" => "OR",
			array(
				"%NAME" => $query,
			),
			array(
				"%CITY_LANG.NAME" => $query,
			),
		);

		$rCity = \Kit\MultiRegions\CityTable::getList(array(
			"filter" => $arFilter,
			"limit" => $this->arParams['COUNT_SHOW_CITY'],
			"order" => array(
				"POSINSTR" => "ASC",
				"NAME" => "ASC",
			),
			"select" => $arSelect,
			"runtime" => array(
				new \Bitrix\Main\Entity\ExpressionField('POSINSTR', 'IF(LOCATE(\'' . amreg_strtolower($query) . '\', LOWER(`kit_multiregions_city`.`NAME`))>0,LOCATE(\'' . strtolower($query) . '\', LOWER(`kit_multiregions_city`.`NAME`)),IF(LOCATE(\'' . strtolower($query) . '\', LOWER(`kit_multiregions_city_city_lang`.`NAME`))>0,LOCATE(\'' . strtolower($query) . '\', LOWER(`kit_multiregions_city_city_lang`.`NAME`)),1000000000))'),
				new \Bitrix\Main\ORM\Fields\ExpressionField('IDD',
					'DISTINCT %s', array("ID")
				)
			),
		));
		while ($arCity = $rCity->fetch()) {
			if ($arCity['CITY_ID'] == $this->arParams['PREVENT_CITY']) {
				$arCity['IS_CURRENT'] = "Y";
			}
			$arNewItem = $this->normalizeCityNames($arCity);
			$arNewItem['CITY_NAME'] = $this->doMarkSearchText($arNewItem['CITY_NAME'], $query);
			$this->arResult['ITEMS'][] = $arNewItem;
		}
	}

	protected function obtainCityFormDataGpsNoQuery($fGpsLat, $fGpsLon)
	{
		$arSelect = array(
			"CITY_ID" => "ID",
			"REGION_ID" => "REGION_ID",
			"COUNTRY_ID" => "REGION.COUNTRY_ID",
			"CITY_NAME" => "NAME",
			"REGION_NAME" => "REGION.NAME",
			"COUNTRY_NAME" => "REGION.COUNTRY.NAME",
			"LENGTH_GPS",
			"LENGTH_KM_PER_GRAD",
		);
		$deltaGrad = 5;
		do {
			$this->arResult['ITEMS'] = array();
			$arFilter = array();
			$fLatFrom = $fGpsLat - $deltaGrad;
			$fLatTo = $fGpsLat + $deltaGrad;
			$fLonFrom = $fGpsLon - $deltaGrad;
			$fLonTo = $fGpsLon + $deltaGrad;
			if ($fLatFrom < -180) {
				$arFilter[">=LAT"] = $fLatFrom + 360;
			} else {
				$arFilter[">=LAT"] = $fLatFrom;
			}
			if ($fLatTo > 180) {
				$arFilter["<=LAT"] = $fLatTo - 360;
			} else {
				$arFilter["<=LAT"] = $fLatTo;
			}
			if ($fLonFrom < -180) {
				$arFilter[">=LON"] = $fLonFrom + 360;
			} else {
				$arFilter[">=LON"] = $fLonFrom;
			}
			if ($fLonTo > 180) {
				$arFilter["<=LON"] = $fLonTo - 360;
			} else {
				$arFilter["<=LON"] = $fLonTo;
			}

			if ($this->arParams['PREVENT_CITY'] > 0) {
				$arFilter = array(
					"LOGIC" => "OR",
					array("ID" => $this->arParams['PREVENT_CITY']),
					$arFilter,
				);
			}
			$this->arResult['FILTER'] = $arFilter;
			$rCity = \Kit\MultiRegions\CityTable::getList(array(
				"filter" => $arFilter,
				"limit" => $this->arParams['COUNT_SHOW_CITY'],
				"order" => array(
					"LENGTH_GPS" => "ASC",
					"NAME" => "ASC",
				),
				"select" => $arSelect,
				"runtime" => array(
					new \Bitrix\Main\Entity\ExpressionField('LENGTH_GPS', 'SQRT(POW(`kit_multiregions_city`.`LAT`-' . floatval($fGpsLat) . ',2)+POW(`kit_multiregions_city`.`LON`-' . floatval($fGpsLon) . ',2))'),
					new \Bitrix\Main\Entity\ExpressionField('LENGTH_KM_PER_GRAD', 'COS(RADIANS(ABS((`kit_multiregions_city`.`LAT`-' . floatval($fGpsLat) . ')/2+' . floatval($fGpsLat) . ')))*40000/360'),
				),
			));
			while ($arCity = $rCity->fetch()) {
				if ($arCity['CITY_ID'] == $this->arParams['PREVENT_CITY']) {
					$arCity['IS_CURRENT'] = "Y";
				}
				$arCity = $this->normalizeCityNames($arCity);
				$arCity['CITY_NAME'] = $arCity['CITY_NAME'] . ", ~" . (round($arCity['LENGTH_GPS'] * $arCity['LENGTH_KM_PER_GRAD'], 1)) . " " . Loc::getMessage("KIT_COMPONENT_MULTIREGIONS_UNIT_KM");
				$this->arResult['ITEMS'][] = $arCity;
			}
			$deltaGrad += 10;
		} while (count($this->arResult['ITEMS']) <= 0 && $deltaGrad <= 45);
	}

	protected function obtainCityFormDataGpsQuery($query, $fGpsLat, $fGpsLon)
	{
		$arSelect = array(
			"IDD",
			"CITY_ID" => "ID",
			"REGION_ID" => "REGION_ID",
			"COUNTRY_ID" => "REGION.COUNTRY_ID",
			"CITY_NAME" => "NAME",
			"REGION_NAME" => "REGION.NAME",
			"COUNTRY_NAME" => "REGION.COUNTRY.NAME",
			"LENGTH_GPS",
			"LENGTH_KM_PER_GRAD",
		);
		$deltaGrad = 5;
		do {
			$this->arResult['ITEMS'] = array();

			$fLatFrom = $fGpsLat - $deltaGrad;
			$fLatTo = $fGpsLat + $deltaGrad;
			$fLonFrom = $fGpsLon - $deltaGrad;
			$fLonTo = $fGpsLon + $deltaGrad;
			$arExtFilter = array();
			if ($fLatFrom < -180) {
				$arExtFilter[">=LAT"] = $fLatFrom + 360;
			} else {
				$arExtFilter[">=LAT"] = $fLatFrom;
			}
			if ($fLatTo > 180) {
				$arExtFilter["<=LAT"] = $fLatTo - 360;
			} else {
				$arExtFilter["<=LAT"] = $fLatTo;
			}
			if ($fLonFrom < -180) {
				$arExtFilter[">=LON"] = $fLonFrom + 360;
			} else {
				$arExtFilter[">=LON"] = $fLonFrom;
			}
			if ($fLonTo > 180) {
				$arExtFilter["<=LON"] = $fLonTo - 360;
			} else {
				$arExtFilter["<=LON"] = $fLonTo;
			}
			$arFilter = array(
				"LOGIC" => "OR",
				array(
					"NAME" => $query . "%",
				),
				array(
					"CITY_LANG.NAME" => $query . "%",
				),
				array(
					"LOGIC" => "AND",
					array(
						"LOGIC" => "OR",
						array(
							"%NAME" => $query,
						),
						array(
							"%CITY_LANG.NAME" => $query,
						),
					),
					$arExtFilter,
				),
			);
			$this->arResult['FILTER'] = $arFilter;
			$arOrder = array(
				"LENGTH_GPS" => "ASC",
				"NAME" => "ASC",
			);
			if ($this->arParams['SEARCH_CITY_TYPE'] == "Q") {
				$arOrder = array(
					"NAME" => "ASC",
				);
			}
			$rCity = \Kit\MultiRegions\CityTable::getList(array(
				"filter" => $arFilter,
				"limit" => $this->arParams['COUNT_SHOW_CITY'],
				"order" => $arOrder,
				"select" => $arSelect,
				"runtime" => array(
					new \Bitrix\Main\Entity\ExpressionField('LENGTH_GPS', 'SQRT(POW(`kit_multiregions_city`.`LAT`-' . floatval($fGpsLat) . ',2)+POW(`kit_multiregions_city`.`LON`-' . floatval($fGpsLon) . ',2))'),
					new \Bitrix\Main\Entity\ExpressionField('LENGTH_KM_PER_GRAD', 'COS(RADIANS(ABS((`kit_multiregions_city`.`LAT`-' . floatval($fGpsLat) . ')/2+' . floatval($fGpsLat) . ')))*40075.7/360'),
					new \Bitrix\Main\ORM\Fields\ExpressionField('IDD',
						'DISTINCT %s', array("ID")
					)
				),
			));
			while ($arCity = $rCity->fetch()) {
				if ($arCity['CITY_ID'] == $this->arParams['PREVENT_CITY']) {
					$arCity['IS_CURRENT'] = "Y";
				}
				$arCity = $this->normalizeCityNames($arCity);
				$arCity['CITY_NAME'] = $this->doMarkSearchText($arCity['CITY_NAME'], $query);
				$arCity['CITY_NAME'] = $arCity['CITY_NAME'] . ", ~" . (round($arCity['LENGTH_GPS'] * $arCity['LENGTH_KM_PER_GRAD'], 1)) . " " . Loc::getMessage("KIT_COMPONENT_MULTIREGIONS_UNIT_KM");
				$this->arResult['ITEMS'][] = $arCity;
			}
			$deltaGrad += 10;
		} while (count($this->arResult['ITEMS']) <= 0 && $deltaGrad <= 180);
	}

	protected function doMarkSearchText($strName, $strQuery)
	{
		$test1 = amreg_strtolower($strName);
		$test2 = amreg_strtolower($strQuery);
		$iPos = amreg_strpos($test1, $test2);
		if ($iPos !== false) {
			$s1 = amreg_substr($strName, 0, $iPos);
			$s2 = amreg_substr($strName, $iPos, strlen($strQuery));
			$s3 = amreg_substr($strName, $iPos + strlen($strQuery));
			$strName = $s1 . '<strong>' . $s2 . '</strong>' . $s3;
		}
		return $strName;
	}

	protected function normalizeCityNames($arCity)
	{
		$arCity['CITY_NAME'] = CKitMultiRegions::getFirstNotEmpty(CityLangTable::getLangNames($arCity['CITY_ID']));
		$arCity['REGION_NAME'] = CKitMultiRegions::getFirstNotEmpty(RegionLangTable::getLangNames($arCity['REGION_ID']));
		$arCity['COUNTRY_NAME'] = CKitMultiRegions::getFirstNotEmpty(CountryLangTable::getLangNames($arCity['COUNTRY_ID']));
		$arNames = array(
			$arCity['COUNTRY_NAME'],
			$arCity['REGION_NAME'],
			$arCity['CITY_NAME'],
		);
		$strOldName = "";
		foreach ($arNames as $k => $val) {
			$val = trim($val);
			if (amreg_strlen($val) <= 0) {
				unset($arNames[$k]);
			} elseif ($strOldName == $val) {
				unset($arNames[$k]);
			}
			$strOldName = $val;
		}
		$arCity['FULL_NAME'] = trim(implode(", ", $arNames));
		if (amreg_strlen($arCity['CITY_NAME']) > 0 && count($arNames) == 2) {
			$arNames = array_values($arNames);
			unset($arNames[1]);
		} else {
			unset($arNames[2]);
		}
		$arCity['FULL_NAME_NO_CITY'] = trim(implode(", ", $arNames));

		return $arCity;
	}

	protected function setPreventCityCookie($cityId)
	{
		$app = \Bitrix\Main\Application::getInstance();
		$cookie = new \Bitrix\Main\Web\Cookie("ARG_CITY", $cityId, 3600 * 24 * 365 + time());
		$cookie->setHttpOnly(false);
		$app->getContext()->getResponse()->addCookie($cookie);
	}

	protected function processResultData()
	{
		$this->arResult['CONFIRM_REQUEST_SHOW'] = false;
		if ($this->arParams['PREVENT_CITY'] <= 0) {
			$this->arResult['CONFIRM_REQUEST_SHOW'] = true;
		} elseif ($this->arParams['CONFIRM_REQUEST_SHOW']) {
			$this->arResult['CONFIRM_REQUEST_SHOW'] = true;
		}
		/*
		if ($this->arParams['PREVENT_CITY'] <= 0 && $this->arParams['ARG_OLD_CITY'] > 0) {
			$arCity = \Kit\MultiRegions\CityTable::getList(array(
				"filter" => array(
					"ID" => $this->arParams['ARG_OLD_CITY'],
				),
			))->fetch();
			if ($arCity) {
				$this->arParams['PREVENT_CITY'] = $arCity['ID'];
				//$this->setPreventCityCookie($this->arParams['PREVENT_CITY']);
			}
		}*/
		if ($this->arParams['PREVENT_CITY'] <= 0) {
			$this->arParams['PREVENT_CITY'] = \Kit\MultiRegions\BlockTable::getCityIdByIP($this->arParams['IP']);
			//$this->setPreventCityCookie($this->arParams['PREVENT_CITY']);
		}

		if ($this->arParams['PREVENT_CITY'] <= 0) {
			$arCity = \Kit\MultiRegions\CityTable::getList(array(
				"filter" => array(
					"IS_DEFAULT" => "Y",
				),
			))->fetch();
			if ($arCity) {
				$this->arParams['PREVENT_CITY'] = $arCity['ID'];
				//$this->setPreventCityCookie($this->arParams['PREVENT_CITY']);
			}
		}
		if ($this->arParams['PREVENT_CITY'] > 0) {
			$this->arResult['CITY_INFO'] = \Kit\MultiRegions\BlockTable::getCityFullInfoByID($this->arParams['PREVENT_CITY']);
			$arNames = array(
				$this->arResult['CITY_INFO']['COUNTRY']['NAME'],
				$this->arResult['CITY_INFO']['REGION']['NAME'],
				$this->arResult['CITY_INFO']['CITY']['NAME'],
			);
			$strOldName = "";
			foreach ($arNames as $k => $val) {
				$val = trim($val);
				if (amreg_strlen($val) <= 0) {
					unset($arNames[$k]);
				} elseif ($strOldName == $val) {
					unset($arNames[$k]);
				}
				$strOldName = $val;
			}
			$this->arResult['FULL_NAME'] = trim(implode(", ", $arNames));
			if (amreg_strlen($this->arResult['CITY_INFO']['CITY']['NAME']) > 0 && count($arNames) == 2) {
				$arNames = array_values($arNames);
				unset($arNames[1]);
			} else {
				unset($arNames[2]);
			}
			$this->arResult['FULL_NAME_NO_CITY'] = trim(implode(", ", $arNames));
			$this->arResult['CITY_NAME_NO_SUBREGION'] = $this->arResult['CITY_INFO']['CITY']['NAME'];
			if (amreg_strpos($this->arResult['CITY_NAME_NO_SUBREGION'], '(') !== false) {
				$this->arResult['CITY_NAME_NO_SUBREGION'] = trim(amreg_substr($this->arResult['CITY_NAME_NO_SUBREGION'], 0, amreg_strpos($this->arResult['CITY_NAME_NO_SUBREGION'], '(') - 1));
			}
		}
	}
}
