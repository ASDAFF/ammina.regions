<?

namespace Ammina\Regions;

use Bitrix\Catalog\GroupTable;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\EventResult;
use Bitrix\Main\ORM\Event;

class DomainVariableTable extends DataManager
{
	public static function getTableName()
	{
		return 'am_regions_domain_var';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'DOMAIN_ID' => array(
				'data_type' => 'integer',
			),
			'DOMAIN' => array(
				'data_type' => '\Ammina\Regions\Domain',
				'reference' => array('=this.DOMAIN_ID' => 'ref.ID'),
			),
			'VARIABLE_ID' => array(
				'data_type' => 'integer',
			),
			'VARIABLE' => array(
				'data_type' => '\Ammina\Regions\Variable',
				'reference' => array('=this.VARIABLE_ID' => 'ref.ID'),
			),
			'VALUE' => array(
				'data_type' => 'string',
			),
			'VALUE_LANG' => array(
				'data_type' => 'string',
			),
		);

		return $fieldsMap;
	}

	public static function onBeforeUpdate(Event $event)
	{
		$result = new EventResult();
		$data = $event->getParameter("fields");
		$arUpdateFields = false;
		if (is_array($data['VALUE'])) {
			foreach ($data['VALUE'] as $k => $v) {
				$v = trim($v);
				if (amreg_strlen($v) <= 0) {
					unset($data['VALUE'][$k]);
				}
			}
			if (count($data['VALUE']) <= 1) {
				$arUpdateFields['VALUE'] = implode("", $data['VALUE']);
			} else {
				$arUpdateFields['VALUE'] = serialize(array_values($data['VALUE']));
			}
		}
		if (is_array($data['VALUE_LANG'])) {
			foreach ($data['VALUE_LANG'] as $lang => $arValue) {
				if (!is_array($arValue)) {
					$arValue = array($arValue);
				}
				foreach ($arValue as $k => $v) {
					$v = trim($v);
					if (amreg_strlen($v) <= 0) {
						unset($arValue[$k]);
					}
				}
				if (count($arValue) <= 1) {
					$data['VALUE_LANG'][$lang] = implode("", $arValue);
				} else {
					$data['VALUE_LANG'][$lang] = array_values($arValue);
				}
			}
			$arUpdateFields['VALUE_LANG'] = serialize($data['VALUE_LANG']);
		}
		if (is_array($arUpdateFields)) {
			$result->modifyFields($arUpdateFields);
		}
		return $result;
	}

	public static function onBeforeAdd(Event $event)
	{
		$result = new EventResult();
		$data = $event->getParameter("fields");
		$arUpdateFields = false;
		if (is_array($data['VALUE'])) {
			foreach ($data['VALUE'] as $k => $v) {
				$v = trim($v);
				if (amreg_strlen($v) <= 0) {
					unset($data['VALUE'][$k]);
				}
			}
			if (count($data['VALUE']) <= 1) {
				$arUpdateFields['VALUE'] = implode("", $data['VALUE']);
			} else {
				$arUpdateFields['VALUE'] = serialize(array_values($data['VALUE']));
			}
		}
		if (is_array($data['VALUE_LANG'])) {
			foreach ($data['VALUE_LANG'] as $lang => $arValue) {
				if (!is_array($arValue)) {
					$arValue = array($arValue);
				}
				foreach ($arValue as $k => $v) {
					$v = trim($v);
					if (amreg_strlen($v) <= 0) {
						unset($arValue[$k]);
					}
				}
				if (count($arValue) <= 1) {
					$data['VALUE_LANG'][$lang] = implode("", $arValue);
				} else {
					$data['VALUE_LANG'][$lang] = array_values($arValue);
				}
			}
			$arUpdateFields['VALUE_LANG'] = serialize($data['VALUE_LANG']);
		}
		if (is_array($arUpdateFields)) {
			$result->modifyFields($arUpdateFields);
		}
		return $result;
	}

	public static function getList(array $parameters = array())
	{
		$result = parent::getList($parameters);
		$result->addFetchDataModifier(function (&$data) {
			if (isset($data['VALUE_LANG'])) {
				$data['VALUE_LANG'] = unserialize($data['VALUE_LANG']);
			}
			if (amreg_strpos($data['VALUE'], 'a:') === 0 && amreg_strpos($data['VALUE'], '{') !== false) {
				$data['VALUE'] = unserialize($data['VALUE']);
			}
			if (isset($data['PRICES'])) {
				foreach ($data['PRICES'] as $k => $v) {
					if (amreg_strlen($v) <= 0) {
						unset($data['PRICES'][$k]);
					}
				}
				$data['PRICES'] = array_values($data['PRICES']);
			}
			if (isset($data['STORES'])) {
				foreach ($data['STORES'] as $k => $v) {
					if (amreg_strlen($v) <= 0) {
						unset($data['STORES'][$k]);
					}
				}
				$data['STORES'] = array_values($data['STORES']);
			}
		});
		return $result;
	}

	public static function doFillAllSystemVariables($DOMAIN_ID)
	{
		$arAllSystemVariables = array();
		$arAllowLang = \CAmminaRegions::getAllAllowLang();

		$rVariable = \Ammina\Regions\VariableTable::getList(array(
			"filter" => array(
				"IS_SYSTEM" => "Y",
			),
		));
		while ($arVariable = $rVariable->fetch()) {
			$arAllSystemVariables[$arVariable['CODE']] = $arVariable['ID'];
		}
		/**
		 * @todo Заполнить системные переменные
		 */
		$arAllVariables = array();
		$arAllVariablesByLang = array();
		$arDomain = DomainTable::getRowById($DOMAIN_ID);
		$arAllVariables[$arAllSystemVariables['SYS_DOMAIN']] = $arDomain['DOMAIN'];
		$arAllVariables[$arAllSystemVariables['SYS_PATHCODE']] = $arDomain['PATHCODE'];
		$arAllVariables[$arAllSystemVariables['SYS_ORDER_PREFIX']] = $arDomain['ORDER_PREFIX'];
		$arAllVariables[$arAllSystemVariables['SYS_CURRENT_DOMAIN_ID']] = $arDomain['ID'];
		$arAllVariables[$arAllSystemVariables['SYS_NAME']] = $arDomain['NAME'];
		foreach ($arAllowLang as $lang) {
			$arAllVariablesByLang[$arAllSystemVariables['SYS_NAME']][$lang] = DomainTable::getLangName($arDomain['ID'], $lang);
		}
		$arAllVariables[$arAllSystemVariables['SYS_SALE_UID']] = $arDomain['SALE_UID'];
		$arAllVariables[$arAllSystemVariables['SYS_SALE_COMPANY_ID']] = $arDomain['SALE_COMPANY_ID'];
		$arAllVariables[$arAllSystemVariables['SYS_DEFAULT_EMAIL']] = $arDomain['DEFAULT_EMAIL'];
		$arAllVariables[$arAllSystemVariables['SYS_COUNTERS']] = $arDomain['COUNTERS'];
		$arAllVariables[$arAllSystemVariables['SYS_HEAD_STRING']] = $arDomain['HEAD_STRING'];
		$arAllVariables[$arAllSystemVariables['SYS_STORES']] = $arDomain['STORES'];
		$arAllVariables[$arAllSystemVariables['SYS_PRICES']] = $arDomain['PRICES'];
		$arAllVariables[$arAllSystemVariables['SYS_PRICE_CODE']] = array();
		if (!empty($arDomain['PRICES'])) {
			foreach ($arDomain['PRICES'] as $price_id) {
				if ($price_id > 0) {
					$arPrice = GroupTable::getRow(array(
						"filter" => array(
							"ID" => $price_id,
						),
					));
					$arAllVariables[$arAllSystemVariables['SYS_PRICE_CODE']][] = (amreg_strlen($arPrice['NAME']) > 0 ? $arPrice['NAME'] : $arPrice['ID']);
				}
			}
		}
		$arAllVariables[$arAllSystemVariables['SYS_MAIN_CITY_ID']] = $arDomain['CITY_ID'];
		$arAllVariables[$arAllSystemVariables['SYS_MAIN_CITY_FULL']] = "";
		$arAllVariables[$arAllSystemVariables['SYS_MAIN_CITY_COUNTRY_NAME']] = "";
		$arAllVariables[$arAllSystemVariables['SYS_MAIN_CITY_REGION_NAME']] = "";
		$arAllVariables[$arAllSystemVariables['SYS_MAIN_CITY_NAME']] = "";
		$arAllVariables[$arAllSystemVariables['SYS_MAIN_CITY_LOCATION_ID']] = "";

		if ($arDomain['CITY_ID'] > 0) {
			$arFormatName = self::getCityFormatInfo($arDomain['CITY_ID']);
			$arAllVariables[$arAllSystemVariables['SYS_MAIN_CITY_FULL']] = $arFormatName['FULL_NAME'];
			$arAllVariables[$arAllSystemVariables['SYS_MAIN_CITY_COUNTRY_NAME']] = $arFormatName['COUNTRY_NAME'];
			$arAllVariables[$arAllSystemVariables['SYS_MAIN_CITY_REGION_NAME']] = $arFormatName['FULL_NAME_NO_CITY'];
			$arAllVariables[$arAllSystemVariables['SYS_MAIN_CITY_NAME']] = $arFormatName['CITY_NAME'];
			$arAllVariables[$arAllSystemVariables['SYS_MAIN_CITY_LOCATION_ID']] = $arFormatName['CITY_LOCATION_ID'];
			foreach ($arAllowLang as $lang) {
				$arFormatName = self::getCityFormatInfo($arDomain['CITY_ID'], $lang);
				$arAllVariablesByLang[$arAllSystemVariables['SYS_MAIN_CITY_FULL']][$lang] = $arFormatName['FULL_NAME'];
				$arAllVariablesByLang[$arAllSystemVariables['SYS_MAIN_CITY_COUNTRY_NAME']][$lang] = $arFormatName['COUNTRY_NAME'];
				$arAllVariablesByLang[$arAllSystemVariables['SYS_MAIN_CITY_REGION_NAME']][$lang] = $arFormatName['FULL_NAME_NO_CITY'];
				$arAllVariablesByLang[$arAllSystemVariables['SYS_MAIN_CITY_NAME']][$lang] = $arFormatName['CITY_NAME'];
				$arAllVariablesByLang[$arAllSystemVariables['SYS_MAIN_CITY_LOCATION_ID']][$lang] = $arFormatName['CITY_LOCATION_ID'];
			}
		}

		$arAllVariables[$arAllSystemVariables['SYS_SERVICED_CITY_FULL_NAME']] = array();
		$arAllVariables[$arAllSystemVariables['SYS_SERVICED_CITY_ID']] = array();
		$arAllVariables[$arAllSystemVariables['SYS_SERVICED_CITY_NAME']] = array();
		$arAllVariables[$arAllSystemVariables['SYS_SERVICED_CITY_LOCATION_ID']] = array();
		$arAllVariables[$arAllSystemVariables['SYS_SERVICED_COUNTRY_ID']] = array();
		$arAllVariables[$arAllSystemVariables['SYS_SERVICED_COUNTRY_NAME']] = array();
		$arAllVariables[$arAllSystemVariables['SYS_SERVICED_COUNTRY_LOCATION_ID']] = array();
		$arAllVariables[$arAllSystemVariables['SYS_SERVICED_REGION_FULL_NAME']] = array();
		$arAllVariables[$arAllSystemVariables['SYS_SERVICED_REGION_ID']] = array();
		$arAllVariables[$arAllSystemVariables['SYS_SERVICED_REGION_NAME']] = array();
		$arAllVariables[$arAllSystemVariables['SYS_SERVICED_REGION_LOCATION_ID']] = array();

		$rServiced = DomainLocationTable::getList(array(
			"filter" => array(
				"DOMAIN_ID" => $DOMAIN_ID,
			),
		));
		while ($arServiced = $rServiced->fetch()) {
			if ($arServiced['CITY_ID'] > 0) {
				$arFormatName = self::getCityFormatInfo($arServiced['CITY_ID']);
				$arAllVariables[$arAllSystemVariables['SYS_SERVICED_CITY_ID']][] = $arServiced['CITY_ID'];
				$arAllVariables[$arAllSystemVariables['SYS_SERVICED_CITY_FULL_NAME']][] = $arFormatName['FULL_NAME'];
				$arAllVariables[$arAllSystemVariables['SYS_SERVICED_CITY_NAME']][] = $arFormatName['CITY_NAME'];
				$arAllVariables[$arAllSystemVariables['SYS_SERVICED_CITY_LOCATION_ID']][] = $arFormatName['CITY_LOCATION_ID'];

				foreach ($arAllowLang as $lang) {
					$arFormatName = self::getCityFormatInfo($arServiced['CITY_ID'], $lang);
					$arAllVariablesByLang[$arAllSystemVariables['SYS_SERVICED_CITY_ID']][$lang][] = $arServiced['CITY_ID'];
					$arAllVariablesByLang[$arAllSystemVariables['SYS_SERVICED_CITY_FULL_NAME']][$lang][] = $arFormatName['FULL_NAME'];
					$arAllVariablesByLang[$arAllSystemVariables['SYS_SERVICED_CITY_NAME']][$lang][] = $arFormatName['CITY_NAME'];
					$arAllVariablesByLang[$arAllSystemVariables['SYS_SERVICED_CITY_LOCATION_ID']][$lang][] = $arFormatName['CITY_LOCATION_ID'];
				}
			}
			if ($arServiced['REGION_ID'] > 0) {
				$arFormatName = self::getRegionFormatInfo($arServiced['REGION_ID']);
				$arAllVariables[$arAllSystemVariables['SYS_SERVICED_REGION_ID']][] = $arServiced['REGION_ID'];
				$arAllVariables[$arAllSystemVariables['SYS_SERVICED_REGION_FULL_NAME']][] = $arFormatName['FULL_NAME'];
				$arAllVariables[$arAllSystemVariables['SYS_SERVICED_REGION_NAME']][] = $arFormatName['REGION_NAME'];
				$arAllVariables[$arAllSystemVariables['SYS_SERVICED_REGION_LOCATION_ID']][] = $arFormatName['REGION_LOCATION_ID'];

				foreach ($arAllowLang as $lang) {
					$arFormatName = self::getRegionFormatInfo($arServiced['REGION_ID'], $lang);
					$arAllVariablesByLang[$arAllSystemVariables['SYS_SERVICED_REGION_ID']][$lang][] = $arServiced['REGION_ID'];
					$arAllVariablesByLang[$arAllSystemVariables['SYS_SERVICED_REGION_FULL_NAME']][$lang][] = $arFormatName['FULL_NAME'];
					$arAllVariablesByLang[$arAllSystemVariables['SYS_SERVICED_REGION_NAME']][$lang][] = $arFormatName['REGION_NAME'];
					$arAllVariablesByLang[$arAllSystemVariables['SYS_SERVICED_REGION_LOCATION_ID']][$lang][] = $arFormatName['REGION_LOCATION_ID'];
				}
			}
			if ($arServiced['COUNTRY_ID'] > 0) {
				$arFormatName = self::getCountryFormatInfo($arServiced['COUNTRY_ID']);
				$arAllVariables[$arAllSystemVariables['SYS_SERVICED_COUNTRY_ID']][] = $arServiced['COUNTRY_ID'];
				$arAllVariables[$arAllSystemVariables['SYS_SERVICED_COUNTRY_NAME']][] = $arFormatName['COUNTRY_NAME'];
				$arAllVariables[$arAllSystemVariables['SYS_SERVICED_COUNTRY_LOCATION_ID']][] = $arFormatName['COUNTRY_LOCATION_ID'];

				foreach ($arAllowLang as $lang) {
					$arFormatName = self::getCountryFormatInfo($arServiced['COUNTRY_ID'], $lang);
					$arAllVariablesByLang[$arAllSystemVariables['SYS_SERVICED_COUNTRY_ID']][$lang][] = $arServiced['COUNTRY_ID'];
					$arAllVariablesByLang[$arAllSystemVariables['SYS_SERVICED_COUNTRY_NAME']][$lang][] = $arFormatName['COUNTRY_NAME'];
					$arAllVariablesByLang[$arAllSystemVariables['SYS_SERVICED_COUNTRY_LOCATION_ID']][$lang][] = $arFormatName['COUNTRY_LOCATION_ID'];
				}
			}
		}

		$arAllVariables[$arAllSystemVariables['SYS_SERVICED_CITY_FULL_NAME']] = array_unique($arAllVariables[$arAllSystemVariables['SYS_SERVICED_CITY_FULL_NAME']]);
		$arAllVariables[$arAllSystemVariables['SYS_SERVICED_CITY_ID']] = array_unique($arAllVariables[$arAllSystemVariables['SYS_SERVICED_CITY_ID']]);
		$arAllVariables[$arAllSystemVariables['SYS_SERVICED_CITY_NAME']] = array_unique($arAllVariables[$arAllSystemVariables['SYS_SERVICED_CITY_NAME']]);
		$arAllVariables[$arAllSystemVariables['SYS_SERVICED_CITY_LOCATION_ID']] = array_unique($arAllVariables[$arAllSystemVariables['SYS_SERVICED_CITY_LOCATION_ID']]);

		$arAllVariables[$arAllSystemVariables['SYS_SERVICED_REGION_FULL_NAME']] = array_unique($arAllVariables[$arAllSystemVariables['SYS_SERVICED_REGION_FULL_NAME']]);
		$arAllVariables[$arAllSystemVariables['SYS_SERVICED_REGION_ID']] = array_unique($arAllVariables[$arAllSystemVariables['SYS_SERVICED_REGION_ID']]);
		$arAllVariables[$arAllSystemVariables['SYS_SERVICED_REGION_NAME']] = array_unique($arAllVariables[$arAllSystemVariables['SYS_SERVICED_REGION_NAME']]);
		$arAllVariables[$arAllSystemVariables['SYS_SERVICED_REGION_LOCATION_ID']] = array_unique($arAllVariables[$arAllSystemVariables['SYS_SERVICED_REGION_LOCATION_ID']]);

		$arAllVariables[$arAllSystemVariables['SYS_SERVICED_COUNTRY_ID']] = array_unique($arAllVariables[$arAllSystemVariables['SYS_SERVICED_COUNTRY_ID']]);
		$arAllVariables[$arAllSystemVariables['SYS_SERVICED_COUNTRY_NAME']] = array_unique($arAllVariables[$arAllSystemVariables['SYS_SERVICED_COUNTRY_NAME']]);
		$arAllVariables[$arAllSystemVariables['SYS_SERVICED_COUNTRY_LOCATION_ID']] = array_unique($arAllVariables[$arAllSystemVariables['SYS_SERVICED_COUNTRY_LOCATION_ID']]);

		$arCheckUniq = array($arAllSystemVariables['SYS_SERVICED_CITY_FULL_NAME'], $arAllSystemVariables['SYS_SERVICED_CITY_ID'], $arAllSystemVariables['SYS_SERVICED_CITY_NAME'], $arAllSystemVariables['SYS_SERVICED_CITY_LOCATION_ID'], $arAllSystemVariables['SYS_SERVICED_REGION_FULL_NAME'], $arAllSystemVariables['SYS_SERVICED_REGION_ID'], $arAllSystemVariables['SYS_SERVICED_REGION_NAME'], $arAllSystemVariables['SYS_SERVICED_REGION_LOCATION_ID'], $arAllSystemVariables['SYS_SERVICED_COUNTRY_ID'], $arAllSystemVariables['SYS_SERVICED_COUNTRY_NAME'], $arAllSystemVariables['SYS_SERVICED_COUNTRY_LOCATION_ID']);
		foreach ($arCheckUniq as $val) {
			if (isset($arAllVariablesByLang[$val])) {
				foreach ($arAllVariablesByLang[$val] as $k => $v) {
					$arAllVariablesByLang[$val][$k] = array_unique($arAllVariablesByLang[$val][$k]);
				}
			}
		}

		foreach ($arAllVariables as $k => $v) {
			$rVariable = \Ammina\Regions\DomainVariableTable::getList(array(
				"filter" => array(
					"VARIABLE_ID" => $k,
					"DOMAIN_ID" => $DOMAIN_ID,
				),
			));
			if ($arVariable = $rVariable->fetch()) {
				\Ammina\Regions\DomainVariableTable::update($arVariable['ID'], array(
					"VALUE" => $v,
					"VALUE_LANG" => $arAllVariablesByLang[$k]
				));
			} else {
				\Ammina\Regions\DomainVariableTable::add(array(
					"VARIABLE_ID" => $k,
					"DOMAIN_ID" => $DOMAIN_ID,
					"VALUE" => $v,
					"VALUE_LANG" => $arAllVariablesByLang[$k]
				));
			}
		}
	}

	public static function getCityFormatInfo($ID, $lang = LANGUAGE_ID)
	{
		$arSelect = array(
			"CITY_ID" => "ID",
			"REGION_ID" => "REGION_ID",
			"COUNTRY_ID" => "REGION.COUNTRY_ID",
			"CITY_NAME" => "NAME",
			"REGION_NAME" => "REGION.NAME",
			"COUNTRY_NAME" => "REGION.COUNTRY.NAME",
			"CITY_LOCATION_ID" => "LOCATION_ID",
			"REGION_LOCATION_ID" => "REGION.LOCATION_ID",
			"COUNTRY_LOCATION_ID" => "REGION.COUNTRY.LOCATION_ID",
		);
		$arCity = \Ammina\Regions\CityTable::getList(array(
			"filter" => array(
				"ID" => $ID,
			),
			"select" => $arSelect,
		))->fetch();
		if ($arCity) {
			$arCity = self::normalizeCityNames($arCity, $lang);
		}
		return $arCity;
	}

	public static function getRegionFormatInfo($ID, $lang = LANGUAGE_ID)
	{
		$arSelect = array(
			"REGION_ID" => "ID",
			"COUNTRY_ID" => "COUNTRY_ID",
			"REGION_NAME" => "NAME",
			"COUNTRY_NAME" => "COUNTRY.NAME",
			"REGION_LOCATION_ID" => "LOCATION_ID",
			"COUNTRY_LOCATION_ID" => "COUNTRY.LOCATION_ID",
		);
		$arRegion = \Ammina\Regions\RegionTable::getList(array(
			"filter" => array(
				"ID" => $ID,
			),
			"select" => $arSelect,
		))->fetch();
		if ($arRegion) {
			$arRegion = self::normalizeRegionNames($arRegion, $lang);
		}
		return $arRegion;
	}

	public static function getCountryFormatInfo($ID, $lang = LANGUAGE_ID)
	{
		$arSelect = array(
			"COUNTRY_ID" => "ID",
			"COUNTRY_NAME" => "NAME",
			"COUNTRY_LOCATION_ID" => "LOCATION_ID",
		);
		$arCountry = \Ammina\Regions\CountryTable::getList(array(
			"filter" => array(
				"ID" => $ID,
			),
			"select" => $arSelect,
		))->fetch();
		if ($arCountry) {
			$arCountry = self::normalizeCountryNames($arCountry, $lang);
		}
		return $arCountry;
	}

	protected static function normalizeCityNames($arCity, $lang = LANGUAGE_ID)
	{
		$arCity['CITY_NAME'] = \CAmminaRegions::getFirstNotEmpty(CityLangTable::getLangNames($arCity['CITY_ID'], $lang));
		$arCity['REGION_NAME'] = \CAmminaRegions::getFirstNotEmpty(RegionLangTable::getLangNames($arCity['REGION_ID'], $lang));
		$arCity['COUNTRY_NAME'] = \CAmminaRegions::getFirstNotEmpty(CountryLangTable::getLangNames($arCity['COUNTRY_ID'], $lang));

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

	protected static function normalizeRegionNames($arRegion, $lang = LANGUAGE_ID)
	{
		$arRegion['REGION_NAME'] = \CAmminaRegions::getFirstNotEmpty(RegionLangTable::getLangNames($arRegion['REGION_ID'], $lang));
		$arRegion['COUNTRY_NAME'] = \CAmminaRegions::getFirstNotEmpty(CountryLangTable::getLangNames($arRegion['COUNTRY_ID'], $lang));

		$arNames = array(
			$arRegion['COUNTRY_NAME'],
			$arRegion['REGION_NAME'],
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
		$arRegion['FULL_NAME'] = trim(implode(", ", $arNames));

		return $arRegion;
	}

	protected static function normalizeCountryNames($arCountry, $lang = LANGUAGE_ID)
	{
		$arCountry['COUNTRY_NAME'] = \CAmminaRegions::getFirstNotEmpty(CountryLangTable::getLangNames($arCountry['COUNTRY_ID'], $lang));
		$arNames = array(
			$arCountry['COUNTRY_NAME'],
		);
		$arCountry['FULL_NAME'] = trim(implode(", ", $arNames));

		return $arCountry;
	}

}