<?

namespace Ammina\Regions\Agent;

use Ammina\Regions\DomainTable;
use Ammina\Regions\PriceTable;
use Bitrix\Catalog\Model\Product;
use Bitrix\Catalog\Product\Sku;
use Bitrix\Catalog\ProductTable;
use Bitrix\Iblock\Property;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

class DomainAvailable
{
	static protected $arCurrentAgent = false;
	static protected $arDomains = array();
	static protected $arStores = array();
	static protected $arDomainsByStore = array();
	static protected $bSetEmptyNext = false;
	static protected $arCatalog = array();
	static protected $defaultProductSettings = array();

	public static function doExecute()
	{
		$iMemory = intval(\COption::GetOptionString("ammina.regions", "agent_available_domain_memorylimit", ""));
		if ($iMemory > 0) {
			@ini_set("memory_limit", $iMemory . "M");
		}
		if (\CModule::IncludeModule("iblock") && \CModule::IncludeModule("catalog") && \CModule::IncludeModule("currency")) {
			/**
			 * ѕровер€ем:
			 * 1. ≈сли выполнение в кроне, то провер€ем интервал запуска и выполн€ем за 1 шаг.
			 * 2. ≈сли запуск на хитах, то провер€ем интервал, включенность режима выполнени€ по шагам и разрешение выполнени€ по шагам
			 */
			self::$arCurrentAgent = \CAgent::GetList(
				array(),
				array(
					"NAME" => '\Ammina\Regions\Agent\DomainAvailable::doExecute();',
					"MODULE_ID" => "ammina.regions",
				)
			)->Fetch();
			if (\COption::GetOptionString("ammina.regions", "agent_available_domain_emptyexec", "N") == "Y") {
				\COption::SetOptionString("ammina.regions", "agent_available_domain_emptyexec", "N");
			} elseif ((defined("BX_CRONTAB") && BX_CRONTAB === true) || (defined("CHK_EVENT") && CHK_EVENT === true)) {
				if (self::$arCurrentAgent) {
					\CAgent::Update(self::$arCurrentAgent['ID'], array("NEXT_EXEC" => ConvertTimeStamp(time() + 3600 * 12, "FULL")));
				}
				if (self::$arCurrentAgent['ID'] > 0 && self::$arCurrentAgent['AGENT_INTERVAL'] != \COption::GetOptionString("ammina.regions", "agent_available_domain_period", 180) * 60) {
					\CAgent::Update(
						self::$arCurrentAgent['ID'],
						array(
							"AGENT_INTERVAL" => \COption::GetOptionString("ammina.regions", "agent_available_domain_period", 180) * 60,
						)
					);
					self::$bSetEmptyNext = true;
				}
				self::doExecuteWithCron();
			} else {
				if (\COption::GetOptionString("ammina.regions", "agent_available_domain_onlycron", "N") != "Y") {
					if (self::$arCurrentAgent['ID'] > 0 && self::$arCurrentAgent['AGENT_INTERVAL'] != \COption::GetOptionString("ammina.regions", "agent_available_domain_period_steps", 30)) {
						\CAgent::Update(
							self::$arCurrentAgent['ID'],
							array(
								"AGENT_INTERVAL" => \COption::GetOptionString("ammina.regions", "agent_available_domain_period_steps", 30),
							)
						);
						$GLOBALS['ARG_SETAGENT_NEXT'][self::$arCurrentAgent['ID']] = ConvertTimeStamp(time() + \COption::GetOptionString("ammina.regions", "agent_available_domain_period_steps", 30), "FULL");
					}
					self::doExecuteWithHit();
				}
			}
		}
		return '\Ammina\Regions\Agent\DomainAvailable::doExecute();';
	}

	public static function doManualExecuteFull()
	{
		self::doCheckCatalogProperties();
		self::doLoadStores();
		self::doLoadDomain();
		\COption::SetOptionString("ammina.regions", "agent_available_domain_nextId", "");
		\COption::SetOptionString("ammina.regions", "agent_available_domain_checkSku", "Y");
		$arFilter = self::getBaseFilterElements(true);
		$rElements = \CIBlockElement::GetList(array("ID" => "ASC"), $arFilter, false, false, array("ID"));
		while ($arElement = $rElements->Fetch()) {
			self::doCheckElement($arElement['ID']);
		}
		\COption::SetOptionString("ammina.regions", "agent_available_domain_nextId", "");
		\COption::SetOptionString("ammina.regions", "agent_available_domain_checkSku", "N");
		$arFilter = self::getBaseFilterElements(false);
		$rElements = \CIBlockElement::GetList(array("ID" => "ASC"), $arFilter, false, false, array("ID"));
		while ($arElement = $rElements->Fetch()) {
			self::doCheckElement($arElement['ID']);
		}
		\COption::SetOptionString("ammina.regions", "agent_available_domain_nextId", "");
		\COption::SetOptionString("ammina.regions", "agent_available_domain_checkSku", "Y");
	}

	protected static function getBaseFilterElements($onlySku = true)
	{
		$arResult = array(
			"IBLOCK_ID" => array(-1),
		);
		if (\CAmminaRegions::isIMExists()) {
			$rCatalog = \CCatalog::GetList();
			while ($arCatalog = $rCatalog->Fetch()) {
				self::$arCatalog[$arCatalog['IBLOCK_ID']] = $arCatalog;
				if ($onlySku) {
					if ($arCatalog['SKU_PROPERTY_ID'] > 0) {
						$arResult['IBLOCK_ID'][] = $arCatalog["IBLOCK_ID"];
					}
				} else {
					if ($arCatalog['SKU_PROPERTY_ID'] <= 0) {
						$arResult['IBLOCK_ID'][] = $arCatalog["IBLOCK_ID"];
					}
				}
			}
		}
		return $arResult;
	}

	protected static function doExecuteWithCron()
	{
		self::doCheckCatalogProperties();
		self::doLoadStores();
		self::doLoadDomain();
		\COption::SetOptionString("ammina.regions", "agent_available_domain_nextId", "");
		\COption::SetOptionString("ammina.regions", "agent_available_domain_checkSku", "Y");
		$arFilter = self::getBaseFilterElements(true);
		$rElements = \CIBlockElement::GetList(array("ID" => "ASC"), $arFilter, false, false, array("ID"));
		while ($arElement = $rElements->Fetch()) {
			self::doCheckElement($arElement['ID']);
		}
		\COption::SetOptionString("ammina.regions", "agent_available_domain_nextId", "");
		\COption::SetOptionString("ammina.regions", "agent_available_domain_checkSku", "N");
		$arFilter = self::getBaseFilterElements(false);
		$rElements = \CIBlockElement::GetList(array("ID" => "ASC"), $arFilter, false, false, array("ID"));
		while ($arElement = $rElements->Fetch()) {
			self::doCheckElement($arElement['ID']);
		}
		\COption::SetOptionString("ammina.regions", "agent_available_domain_nextId", "");
		\COption::SetOptionString("ammina.regions", "agent_available_domain_checkSku", "Y");
		if (self::$bSetEmptyNext) {
			\COption::SetOptionString("ammina.regions", "agent_available_domain_emptyexec", "Y");
		}
	}

	protected static function doExecuteWithHit()
	{
		self::doLoadStores();
		self::doLoadDomain();
		$endTime = time() + \COption::GetOptionString("ammina.regions", "agent_available_domain_maxtime_step", 5);
		$isSkuWork = \COption::GetOptionString("ammina.regions", "agent_available_domain_checkSku", "Y");
		if ($isSkuWork == "Y") {
			$nextId = \COption::GetOptionString("ammina.regions", "agent_available_domain_nextId", "");
			$arFilter = self::getBaseFilterElements(true);
			if ($nextId > 0) {
				$arFilter['>ID'] = $nextId;
			} else {
				self::doCheckCatalogProperties();
			}
			$rElements = \CIBlockElement::GetList(array("ID" => "ASC"), $arFilter, false, false, array("ID"));
			while ($arElement = $rElements->Fetch()) {
				self::doCheckElement($arElement['ID']);
				if (time() >= $endTime) {
					\COption::SetOptionString("ammina.regions", "agent_available_domain_nextId", $arElement['ID']);
					return;
				}
			}
			\COption::SetOptionString("ammina.regions", "agent_available_domain_nextId", "0");
			\COption::SetOptionString("ammina.regions", "agent_available_domain_checkSku", "N");
			$isSkuWork = "N";
		}
		if ($isSkuWork == "N") {
			$nextId = \COption::GetOptionString("ammina.regions", "agent_available_domain_nextId", "");
			$arFilter = self::getBaseFilterElements(false);
			if ($nextId > 0) {
				$arFilter['>ID'] = $nextId;
			}
			$rElements = \CIBlockElement::GetList(array("ID" => "ASC"), $arFilter, false, false, array("ID"));
			while ($arElement = $rElements->Fetch()) {
				self::doCheckElement($arElement['ID']);
				if (time() >= $endTime) {
					\COption::SetOptionString("ammina.regions", "agent_available_domain_nextId", $arElement['ID']);
					return;
				}
			}
		}

		\COption::SetOptionString("ammina.regions", "agent_available_domain_nextId", "0");
		\COption::SetOptionString("ammina.regions", "agent_available_domain_checkSku", "Y");
		if (self::$arCurrentAgent['ID'] > 0) {
			\CAgent::Update(
				self::$arCurrentAgent['ID'],
				array(
					"AGENT_INTERVAL" => \COption::GetOptionString("ammina.regions", "agent_available_domain_period", 180) * 60,
				)
			);
			$GLOBALS['ARG_SETAGENT_NEXT'][self::$arCurrentAgent['ID']] = ConvertTimeStamp(time() + \COption::GetOptionString("ammina.regions", "agent_available_domain_period", 180) * 60, "FULL");
		}
	}

	protected static function doCheckCatalogProperties()
	{
		if (\CAmminaRegions::isIMExists()) {
			$rCatalog = \CCatalog::GetList();
			while ($arCatalog = $rCatalog->Fetch()) {
				$arProp = \CIBlockProperty::GetList(
					array(),
					array(
						"IBLOCK_ID" => $arCatalog['IBLOCK_ID'],
						"CODE" => "SYS_DOMAIN_AVAILABLE",
						"MULTIPLE" => "Y",
						"USER_TYPE" => "AmminaRegionsDomain",
					)
				)->Fetch();
				if (!$arProp) {
					$arFields = array(
						"IBLOCK_ID" => $arCatalog['IBLOCK_ID'],
						"NAME" => Loc::getMessage("ammina.regions_IBLOCK_PROPERTY_SYS_DOMAIN_AVAILABLE_NAME"),
						"SORT" => 100000,
						"CODE" => "SYS_DOMAIN_AVAILABLE",
						"MULTIPLE" => "Y",
						"MULTIPLE_CNT" => 0,
						"ACTIVE" => "Y",
						"PROPERTY_TYPE" => "N",
						"USER_TYPE" => "AmminaRegionsDomain",
					);
					$oProp = new \CIBlockProperty();
					$oProp->Add($arFields);
				}
				$arProp = \CIBlockProperty::GetList(
					array(),
					array(
						"IBLOCK_ID" => $arCatalog['IBLOCK_ID'],
						"CODE" => "SYS_DOMAIN_AVAILABLE_SORT",
						"MULTIPLE" => "Y",
						"PROPERTY_TYPE" => "N",
					)
				)->Fetch();
				if (!$arProp) {
					$arFields = array(
						"IBLOCK_ID" => $arCatalog['IBLOCK_ID'],
						"NAME" => Loc::getMessage("ammina.regions_IBLOCK_PROPERTY_SYS_DOMAIN_AVAILABLE_SORT_NAME"),
						"SORT" => 100010,
						"CODE" => "SYS_DOMAIN_AVAILABLE_SORT",
						"MULTIPLE" => "Y",
						"MULTIPLE_CNT" => 0,
						"ACTIVE" => "Y",
						"PROPERTY_TYPE" => "N",
					);
					$oProp = new \CIBlockProperty();
					$oProp->Add($arFields);
				}

				$arProp = \CIBlockProperty::GetList(
					array(),
					array(
						"IBLOCK_ID" => $arCatalog['IBLOCK_ID'],
						"CODE" => "SYS_DOMAIN_AVAILABLE_LIST",
						"MULTIPLE" => "Y",
						"PROPERTY_TYPE" => "L",
					)
				)->Fetch();
				if (!$arProp) {
					$arFields = array(
						"IBLOCK_ID" => $arCatalog['IBLOCK_ID'],
						"NAME" => Loc::getMessage("ammina.regions_IBLOCK_PROPERTY_SYS_DOMAIN_AVAILABLE_LIST_NAME"),
						"SORT" => 100010,
						"CODE" => "SYS_DOMAIN_AVAILABLE_LIST",
						"MULTIPLE" => "Y",
						"MULTIPLE_CNT" => 0,
						"ACTIVE" => "Y",
						"PROPERTY_TYPE" => "L",
					);
					$oProp = new \CIBlockProperty();
					$iPropId = $oProp->Add($arFields);
				} else {
					$iPropId = $arProp['ID'];
				}

				$arProps = explode("|", \COption::GetOptionString("ammina.regions", "iblock_prop_domains"));
				if (!in_array($iPropId, $arProps)) {
					$arProps[] = $iPropId;
					\COption::SetOptionString("ammina.regions", "iblock_prop_domains", implode("|", $arProps));
					DomainTable::doCheckIBlockPropsList();
				}
			}
		}
	}

	protected static function doLoadStores()
	{
		if (\CAmminaRegions::isIMExists()) {
			$rStores = \CCatalogStore::GetList(array());
			while ($arStore = $rStores->Fetch()) {
				self::$arStores[$arStore['ID']] = $arStore;
			}
		}
		self::$defaultProductSettings = array(
			'QUANTITY_TRACE' => ((string)Option::get('catalog', 'default_quantity_trace') == 'Y' ? 'Y' : 'N'),
			'CAN_BUY_ZERO' => ((string)Option::get('catalog', 'default_can_buy_zero') == 'Y' ? 'Y' : 'N'),
			'NEGATIVE_AMOUNT_TRACE' => (Option::get('catalog', 'allow_negative_amount') == 'Y' ? 'Y' : 'N'),
			'SUBSCRIBE' => ((string)Option::get('catalog', 'default_subscribe') == 'N' ? 'N' : 'Y'),
		);
	}

	protected static function doLoadDomain()
	{
		$rDomains = DomainTable::getList(array());
		while ($arDomain = $rDomains->fetch()) {
			self::$arDomains[$arDomain['ID']] = $arDomain;
			foreach ($arDomain['STORES'] as $val) {
				self::$arDomainsByStore[$val][] = $arDomain['ID'];
			}
		}
	}

	protected static function doCheckElement($ID)
	{
		$arEl = false;
		if (\COption::GetOptionString("ammina.regions", "agent_available_domain_sum_storesku", "N") == "Y" && \COption::GetOptionString("ammina.regions", "agent_available_domain_checkSku", "Y") == "N") {
			$arCurrentCatalog = false;
			$arEl = \CIBlockElement::GetList(array(), array("ID" => $ID), false, false, array("ID", "IBLOCK_ID"))->Fetch();
			if ($arEl) {
				$arCurrentCatalog = self::$arCatalog[$arEl['IBLOCK_ID']];
			}
			$arProduct = \CCatalogProduct::GetByID($ID);
			if ($arProduct['TYPE'] == \CCatalogProduct::TYPE_SET) {
				$arSet = \CCatalogProductSet::getAllSetsByProduct($ID, \CCatalogProductSet::TYPE_SET);
				$arAllItemsId = array();
				$ak = array_keys($arSet);
				foreach ($arSet[$ak[0]]['ITEMS'] as $k => $v) {
					$arAllItemsId[$v['ITEM_ID']] = $v['QUANTITY'];
				}
				$arQntByStore = array();
				foreach ($arAllItemsId as $k => $v) {
					foreach (self::$arStores as $k1 => $v1) {
						$arQntByStore[$k][$v1['ID']] = 0;
					}
				}
				$arSetQntByStore = array();
				foreach (self::$arStores as $k => $v) {
					$arSetQntByStore[$v['ID']] = false;
				}
				if (!empty($arAllItemsId) && \CAmminaRegions::isIMExists()) {
					$rCatalogStore = \CCatalogStoreProduct::GetList(array(), array("@PRODUCT_ID" => array_keys($arAllItemsId)));
					while ($arCatalogStore = $rCatalogStore->Fetch()) {
						$arQntByStore[$arCatalogStore['PRODUCT_ID']][$arCatalogStore['STORE_ID']] += $arCatalogStore['AMOUNT'];
					}
					foreach ($arSetQntByStore as $k => $v) {
						$iMaxAllowSet = false;
						foreach ($arAllItemsId as $id => $qnt) {
							$iCurQnt = $arQntByStore[$id][$k] / $qnt;
							if ($iMaxAllowSet === false) {
								$iMaxAllowSet = $iCurQnt;
							} elseif ($iCurQnt < $iMaxAllowSet) {
								$iMaxAllowSet = $iCurQnt;
							}
						}
						$arSetQntByStore[$k] = intval($iMaxAllowSet);
					}
					foreach ($arSetQntByStore as $storeId => $amount) {
						$arCatalogStore = \CCatalogStoreProduct::GetList(array(), array("PRODUCT_ID" => $ID, "STORE_ID" => $storeId))->Fetch();
						if ($arCatalogStore) {
							\CCatalogStoreProduct::Update($arCatalogStore['ID'], array("AMOUNT" => $amount));
						} else {
							\CCatalogStoreProduct::Add(
								array(
									"PRODUCT_ID" => $ID,
									"STORE_ID" => $storeId,
									"AMOUNT" => $amount,
								)
							);
						}
					}
				}
			} elseif ($arCurrentCatalog && $arCurrentCatalog['OFFERS_IBLOCK_ID'] > 0 && $arCurrentCatalog['OFFERS_PROPERTY_ID'] > 0) {
				//»щем по торгпредам и обновл€ем склады текущего
				$arAllSkuId = array();
				$rDb = \CIBlockElement::GetList(array(), array("IBLOCK_ID" => $arCurrentCatalog['OFFERS_IBLOCK_ID'], "PROPERTY_" . $arCurrentCatalog['OFFERS_PROPERTY_ID'] => $ID), false, false, array("ID"));
				while ($ar = $rDb->Fetch()) {
					$arAllSkuId[] = $ar['ID'];
				}
				$arQntByStore = array();
				foreach (self::$arStores as $k => $v) {
					$arQntByStore[$v['ID']] = 0;
				}
				if (!empty($arAllSkuId) && \CAmminaRegions::isIMExists()) {
					$rCatalogStore = \CCatalogStoreProduct::GetList(array(), array("PRODUCT_ID" => $arAllSkuId));
					while ($arCatalogStore = $rCatalogStore->Fetch()) {
						$arQntByStore[$arCatalogStore['STORE_ID']] += $arCatalogStore['AMOUNT'];
					}
					/*}
					if (\CAmminaRegions::isIMExists()) {*/
					foreach ($arQntByStore as $storeId => $amount) {
						$arCatalogStore = \CCatalogStoreProduct::GetList(array(), array("PRODUCT_ID" => $ID, "STORE_ID" => $storeId))->Fetch();
						if ($arCatalogStore) {
							\CCatalogStoreProduct::Update($arCatalogStore['ID'], array("AMOUNT" => $amount));
						} else {
							\CCatalogStoreProduct::Add(
								array(
									"PRODUCT_ID" => $ID,
									"STORE_ID" => $storeId,
									"AMOUNT" => $amount,
								)
							);
						}
					}
				}
			}
		}

		$not_use_quantity_trace = (\COption::GetOptionString("ammina.regions", "agent_available_domain_not_use_quantity_trace", "N") == "Y");
		$not_use_can_buy_zero = (\COption::GetOptionString("ammina.regions", "agent_available_domain_not_use_can_buy_zero", "N") == "Y");
		$totalCnt = 0;
		$arAllowDomains = array();
		$arDisallowDomains = array();
		if (\CAmminaRegions::isIMExists()) {
			$arProduct = ProductTable::getRowById($ID);
			if ($arProduct['QUANTITY_TRACE'] == ProductTable::STATUS_DEFAULT) {
				$arProduct['QUANTITY_TRACE'] = self::$defaultProductSettings['QUANTITY_TRACE'];
			}
			if ($arProduct['CAN_BUY_ZERO'] == ProductTable::STATUS_DEFAULT) {
				$arProduct['CAN_BUY_ZERO'] = self::$defaultProductSettings['CAN_BUY_ZERO'];
			}

			$rCatalogStore = \CCatalogStoreProduct::GetList(array(), array("PRODUCT_ID" => $ID));
			while ($arCatalogStore = $rCatalogStore->Fetch()) {
				$totalCnt += $arCatalogStore['AMOUNT'];
				$available = true;
				if ((float)$arCatalogStore['AMOUNT'] <= 0 && ($arProduct['QUANTITY_TRACE'] == ProductTable::STATUS_YES || $not_use_quantity_trace) && ($arProduct['CAN_BUY_ZERO'] == ProductTable::STATUS_NO || $not_use_can_buy_zero)) {
					$available = false;
				}
				if ($available) {
					foreach (self::$arDomainsByStore[$arCatalogStore['STORE_ID']] as $domain) {
						if (!in_array($domain, $arAllowDomains)) {
							$arAllowDomains[] = $domain;
						}
					}
				}
			}
		}
		$obElement = new \CIBlockElement();
		$obElement->SetPropertyValueCode($ID, "SYS_DOMAIN_AVAILABLE", $arAllowDomains);

		$arValue = array();
		foreach (self::$arDomains as $domainId => $arDomain) {
			if (in_array($domainId, $arAllowDomains)) {
				$arValue[] = $domainId * 2 - 1;
			} else {
				$arValue[] = $domainId * 2;
			}
		}
		$obElement->SetPropertyValueCode($ID, "SYS_DOMAIN_AVAILABLE_SORT", $arValue);

		if (!$arEl) {
			$arEl = \CIBlockElement::GetList(array(), array("ID" => $ID), false, false, array("ID", "IBLOCK_ID"))->Fetch();
		}
		$arEnum = self::getEnumValues($arEl['IBLOCK_ID']);
		$arVal = array();
		foreach ($arAllowDomains as $v) {
			$arVal[] = $arEnum[$v];
		}
		$obElement->SetPropertyValueCode($ID, "SYS_DOMAIN_AVAILABLE_LIST", $arVal);
		if (\COption::GetOptionString("ammina.regions", "agent_available_domain_sum_quantity_by_store", "N") == "Y" && \CAmminaRegions::isIMExists()) {
			if ($arProduct) {
				Product::update($ID, array("QUANTITY" => $totalCnt));
			} else {
				Product::add(array("ID" => $ID, "QUANTITY" => $totalCnt));
			}
		}
	}

	protected static function getEnumValues($IBLOCK_ID)
	{
		static $arEnum = false;
		if (!isset($arEnum[$IBLOCK_ID])) {
			$rPropEnum = \CIBlockPropertyEnum::GetList(
				array(),
				array(
					"IBLOCK_ID" => $IBLOCK_ID,
					"CODE" => "SYS_DOMAIN_AVAILABLE_LIST"
				)
			);
			while ($arProp = $rPropEnum->Fetch()) {
				$arEnum[$IBLOCK_ID][amreg_substr($arProp['XML_ID'], 1)] = $arProp['ID'];
			}
		}
		return $arEnum[$IBLOCK_ID];
	}
}
