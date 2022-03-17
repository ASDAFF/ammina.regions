<?

namespace Ammina\Regions\Agent;

use Ammina\Regions\PriceTable;

class Price
{
	static protected $arCurrentAgent = false;
	static protected $arPriceRules = false;
	static protected $arPrices = false;
	static protected $bSetEmptyNext = false;

	public static function doExecute()
	{
		$iMemory = intval(\COption::GetOptionString("ammina.regions", "priceagent_memorylimit", ""));
		if ($iMemory > 0) {
			@ini_set("memory_limit", $iMemory . "M");
		}
		if (\CModule::IncludeModule("iblock") && \CModule::IncludeModule("catalog") && \CModule::IncludeModule("currency")) {
			/**
			 * ѕровер€ем:
			 * 1. ≈сли выполнение в кроне, то провер€ем интервал запуска и выполн€ем за 1 шаг.
			 * 2. ≈сли запуск на хитах, то провер€ем интервал, включенность режима выполнени€ по шагам и разрешение выполнени€ по шагам
			 */
			self::$arCurrentAgent = \CAgent::GetList(array(), array(
				"NAME" => '\Ammina\Regions\Agent\Price::doExecute();',
				"MODULE_ID" => "ammina.regions",
			))->Fetch();
			if (\COption::GetOptionString("ammina.regions", "priceagent_emptyexec", "N") == "Y") {
				\COption::SetOptionString("ammina.regions", "priceagent_emptyexec", "N");
			} elseif ((defined("BX_CRONTAB") && BX_CRONTAB === true) || (defined("CHK_EVENT") && CHK_EVENT === true)) {
				if (self::$arCurrentAgent) {
					\CAgent::Update(self::$arCurrentAgent['ID'], array("NEXT_EXEC" => ConvertTimeStamp(time() + 3600 * 12, "FULL")));
				}
				if (self::$arCurrentAgent['ID'] > 0 && self::$arCurrentAgent['AGENT_INTERVAL'] != \COption::GetOptionString("ammina.regions", "priceagent_period", 180) * 60) {
					\CAgent::Update(self::$arCurrentAgent['ID'], array(
						"AGENT_INTERVAL" => \COption::GetOptionString("ammina.regions", "priceagent_period", 180) * 60,
					));
					self::$bSetEmptyNext = true;
				}
				self::doExecuteWithCron();
			} else {
				if (\COption::GetOptionString("ammina.regions", "priceagent_onlycron", "N") != "Y") {
					if (self::$arCurrentAgent['ID'] > 0 && self::$arCurrentAgent['AGENT_INTERVAL'] != \COption::GetOptionString("ammina.regions", "priceagent_period_steps", 30)) {
						\CAgent::Update(self::$arCurrentAgent['ID'], array(
							"AGENT_INTERVAL" => \COption::GetOptionString("ammina.regions", "priceagent_period_steps", 30),
						));
						$GLOBALS['ARG_SETAGENT_NEXT'][self::$arCurrentAgent['ID']] = ConvertTimeStamp(time() + \COption::GetOptionString("ammina.regions", "priceagent_period_steps", 30), "FULL");
					}
					self::doExecuteWithHit();
				}
			}
		}
		return '\Ammina\Regions\Agent\Price::doExecute();';
	}

	protected static function getBaseFilterElements()
	{
		$arResult = array(
			"IBLOCK_ID" => array(-1),
		);
		$rCatalog = \CCatalog::GetList();
		while ($arCatalog = $rCatalog->Fetch()) {
			$arResult['IBLOCK_ID'][] = $arCatalog["IBLOCK_ID"];
		}
		return $arResult;
	}

	protected static function doLoadPrices()
	{
		$rPrices = PriceTable::getList(array(
			"order" => array(
				"SORT" => "ASC",
				"ID" => "ASC",
			),
		));
		while ($arPrice = $rPrices->fetch()) {
			self::$arPriceRules[$arPrice['ID']] = $arPrice;
		}
		$rPrices = \CCatalogGroup::GetList(array(
			"SORT" => "ASC",
			"NAME_LANG" => "ASC",
		));
		while ($arPrice = $rPrices->Fetch()) {
			self::$arPrices[$arPrice['ID']] = $arPrice;
		}
	}

	protected static function doCheckElementPrices($ID)
	{
		$arAllElementPrices = array();
		$arAllElementGroup = array();
		$rPrices = \CPrice::GetList(array(), array("PRODUCT_ID" => $ID));
		while ($arPrice = $rPrices->Fetch()) {
			$arAllElementPrices[$arPrice['ID']] = $arPrice;
			$arAllElementGroup[$arPrice['CATALOG_GROUP_ID']][] = $arPrice['ID'];
		}
		$arNewPrices = array();
		foreach (self::$arPriceRules as $arRule) {
			if ($arRule['ACTIVE'] == "Y") {
				if (isset($arNewPrices[$arRule['PRICE_FROM_ID']])) {
					foreach ($arNewPrices[$arRule['PRICE_FROM_ID']] as $arBasePrice) {
						$arNewPrice = array(
							"PRODUCT_ID" => $ID,
							"CATALOG_GROUP_ID" => $arRule['PRICE_TO_ID'],
							"EXTRA_ID" => $arBasePrice["EXTRA_ID"],
							"PRICE" => $arBasePrice["PRICE"],
							"CURRENCY" => $arBasePrice["CURRENCY"],
							"QUANTITY_FROM" => $arBasePrice["QUANTITY_FROM"],
							"QUANTITY_TO" => $arBasePrice["QUANTITY_TO"],
						);
						if (amreg_strlen($arRule['CURRENCY']) > 0 && $arRule['CURRENCY'] != $arBasePrice['CURRENCY']) {
							$arNewPrice['PRICE'] = \CCurrencyRates::ConvertCurrency($arNewPrice['PRICE'], $arNewPrice['CURRENCY'], $arRule['CURRENCY']);
							$arNewPrice['CURRENCY'] = $arRule['CURRENCY'];
						}
						if ($arRule['PRICE_CHANGE'] == "SU") {
							$arNewPrice['PRICE'] += $arRule['PRICE_CHANGE_VALUE'];
						} elseif ($arRule['PRICE_CHANGE'] == "SD") {
							$arNewPrice['PRICE'] -= $arRule['PRICE_CHANGE_VALUE'];
						} elseif ($arRule['PRICE_CHANGE'] == "PU") {
							$arNewPrice['PRICE'] = $arNewPrice['PRICE'] * ((100 + $arRule['PRICE_CHANGE_VALUE']) / 100);
						} elseif ($arRule['PRICE_CHANGE'] == "PD") {
							$arNewPrice['PRICE'] = $arNewPrice['PRICE'] * ((100 - $arRule['PRICE_CHANGE_VALUE']) / 100);
						}
						$arNewPrices[$arRule['PRICE_TO_ID']][] = $arNewPrice;
					}
				} elseif (isset($arAllElementGroup[$arRule['PRICE_FROM_ID']])) {
					foreach ($arAllElementGroup[$arRule['PRICE_FROM_ID']] as $iPriceRecordId) {
						$arBasePrice = $arAllElementPrices[$iPriceRecordId];
						$arNewPrice = array(
							"PRODUCT_ID" => $ID,
							"CATALOG_GROUP_ID" => $arRule['PRICE_TO_ID'],
							"EXTRA_ID" => $arBasePrice["EXTRA_ID"],
							"PRICE" => $arBasePrice["PRICE"],
							"CURRENCY" => $arBasePrice["CURRENCY"],
							"QUANTITY_FROM" => $arBasePrice["QUANTITY_FROM"],
							"QUANTITY_TO" => $arBasePrice["QUANTITY_TO"],
						);
						if (amreg_strlen($arRule['CURRENCY']) > 0 && $arRule['CURRENCY'] != $arBasePrice['CURRENCY']) {
							$arNewPrice['PRICE'] = \CCurrencyRates::ConvertCurrency($arNewPrice['PRICE'], $arNewPrice['CURRENCY'], $arRule['CURRENCY']);
							$arNewPrice['CURRENCY'] = $arRule['CURRENCY'];
						}
						if ($arRule['PRICE_CHANGE'] == "SU") {
							$arNewPrice['PRICE'] += $arRule['PRICE_CHANGE_VALUE'];
						} elseif ($arRule['PRICE_CHANGE'] == "SD") {
							$arNewPrice['PRICE'] -= $arRule['PRICE_CHANGE_VALUE'];
						} elseif ($arRule['PRICE_CHANGE'] == "PU") {
							$arNewPrice['PRICE'] = $arNewPrice['PRICE'] * ((100 + $arRule['PRICE_CHANGE_VALUE']) / 100);
						} elseif ($arRule['PRICE_CHANGE'] == "PD") {
							$arNewPrice['PRICE'] = $arNewPrice['PRICE'] * ((100 - $arRule['PRICE_CHANGE_VALUE']) / 100);
						}
						$arNewPrices[$arRule['PRICE_TO_ID']][] = $arNewPrice;
					}
				}
			}
		}
		foreach ($arNewPrices as $iGroupPriceId => $arGroupPrices) {
			foreach ($arGroupPrices as $arNewPrice) {
				if (count($arAllElementGroup[$iGroupPriceId]) > 0) {
					$ak = array_keys($arAllElementGroup[$iGroupPriceId]);
					$arCheckPrice = $arAllElementPrices[$arAllElementGroup[$iGroupPriceId][$ak[0]]];
					if ($arCheckPrice['EXTRA_ID'] != $arNewPrice['EXTRA_ID'] || $arCheckPrice['PRICE'] != $arNewPrice['PRICE'] || $arCheckPrice['CURRENCY'] != $arNewPrice['CURRENCY'] || $arCheckPrice['QUANTITY_FROM'] != $arNewPrice['QUANTITY_FROM'] || $arCheckPrice['QUANTITY_TO'] != $arNewPrice['QUANTITY_TO']) {
						$result = \Bitrix\Catalog\Model\Price::update($arAllElementGroup[$iGroupPriceId][$ak[0]], $arNewPrice);
					}
					unset($arAllElementGroup[$iGroupPriceId][$ak[0]]);
				} else {
					$result = \Bitrix\Catalog\Model\Price::add($arNewPrice);
				}
			}
			if (count($arAllElementGroup[$iGroupPriceId]) > 0) {
				foreach ($arAllElementGroup[$iGroupPriceId] as $oldId) {
					\Bitrix\Catalog\Model\Price::delete($oldId);
				}
				unset($arAllElementGroup[$iGroupPriceId]);
			}
		}
	}

	protected static function doExecuteWithCron()
	{
		self::doLoadPrices();
		$arFilter = self::getBaseFilterElements();
		$rElements = \CIBlockElement::GetList(array("ID" => "ASC"), $arFilter, false, false, array("ID"));
		while ($arElement = $rElements->Fetch()) {
			self::doCheckElementPrices($arElement['ID']);
		}
		if (self::$bSetEmptyNext) {
			\COption::SetOptionString("ammina.regions", "priceagent_emptyexec", "Y");
		}
	}

	protected static function doExecuteWithHit()
	{
		self::doLoadPrices();
		$arFilter = self::getBaseFilterElements();
		$endTime = time() + \COption::GetOptionString("ammina.regions", "priceagent_maxtime_step", 5);
		$nextId = \COption::GetOptionString("ammina.regions", "priceagent_nextId", "");
		if ($nextId > 0) {
			$arFilter['>ID'] = $nextId;
		}
		$rElements = \CIBlockElement::GetList(array("ID" => "ASC"), $arFilter, false, false, array("ID"));
		while ($arElement = $rElements->Fetch()) {
			self::doCheckElementPrices($arElement['ID']);
			if (time() >= $endTime) {
				\COption::SetOptionString("ammina.regions", "priceagent_nextId", $arElement['ID']);
				return;
			}
		}
		\COption::SetOptionString("ammina.regions", "priceagent_nextId", "0");
		if (self::$arCurrentAgent['ID'] > 0) {
			\CAgent::Update(self::$arCurrentAgent['ID'], array(
				"AGENT_INTERVAL" => \COption::GetOptionString("ammina.regions", "priceagent_period", 180) * 60,
			));
			$GLOBALS['ARG_SETAGENT_NEXT'][self::$arCurrentAgent['ID']] = ConvertTimeStamp(time() + \COption::GetOptionString("ammina.regions", "priceagent_period", 180) * 60, "FULL");
		}
	}
}
