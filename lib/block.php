<?

namespace Kit\MultiRegions;

use Kit\MultiRegions\Parser\SypexGeo;
use Bitrix\Main\ORM\Data\DataManager;

class BlockTable extends DataManager
{
	public static function getTableName()
	{
		return 'am_multiregions_block';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'BLOCK_START_1' => array(
				'data_type' => 'integer',
			),
			'BLOCK_START_2' => array(
				'data_type' => 'integer',
			),
			'BLOCK_START_3' => array(
				'data_type' => 'integer',
			),
			'BLOCK_START_4' => array(
				'data_type' => 'integer',
			),
			'BLOCK_END_1' => array(
				'data_type' => 'integer',
			),
			'BLOCK_END_2' => array(
				'data_type' => 'integer',
			),
			'BLOCK_END_3' => array(
				'data_type' => 'integer',
			),
			'BLOCK_END_4' => array(
				'data_type' => 'integer',
			),
			'BLOCK_START' => array(
				'data_type' => 'integer',
			),
			'BLOCK_END' => array(
				'data_type' => 'integer',
			),
			'CITY_ID' => array(
				'data_type' => 'integer',
			),
			'CITY' => array(
				'data_type' => '\Kit\MultiRegions\City',
				'reference' => array('=this.CITY_ID' => 'ref.ID'),
			),
		);

		return $fieldsMap;
	}

	static public function getCityIdByIP($IP = false)
	{
		$arResult = false;
		if ($IP === false) {
			if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
				$IP = $_SERVER['HTTP_CLIENT_IP'];
			} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$IP = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$IP = $_SERVER['REMOTE_ADDR'];
			}
		}
		if (\COption::GetOptionString("kit.multiregions", "not_load_ip_block", "Y") == "Y") {
			/**
			 * @var SypexGeo
			 */
			$oSypexGeo = new SypexGeo();
			if (!file_exists($oSypexGeo->strDataLocalDir . "city/SxGeoCity.dat")) {
				$isOk = false;
				if ($oSypexGeo->doDownloadFileInfo()) {
					if ($oSypexGeo->doExtractFileInfo()) {
						if ($oSypexGeo->doDownloadFileCity()) {
							if ($oSypexGeo->doExtractFileCity()) {
								$isOk = true;
							}
						}
					}
				}
				if (!$isOk) {
					return false;
				}
			}
			$oSypexGeo->loadCityFile(SypexGeo::SXGEO_FILE);
			$arCityData = $oSypexGeo->getCityFull($IP);
			if ($arCityData['city']['id'] > 0) {
				$arCityInfo = CityTable::getList(array(
					"filter" => array(
						"EXT_ID" => $arCityData['city']['id'],
					),
				))->fetch();
				return $arCityInfo['ID'];
			}
		} else {
			$arIP = explode(".", $IP);
			$arSelect = array("*");
			//—начала смотрим совпадение по блоку
			$rData = self::getList(array(
				"filter" =>
					array(
						array(
							"LOGIC" => "OR",
							array(
								"=BLOCK_START_1" => $arIP[0],
								"=BLOCK_START_2" => $arIP[1],
								"=BLOCK_START_3" => $arIP[2],
							),
							array(
								"=BLOCK_END_1" => $arIP[0],
								"=BLOCK_END_2" => $arIP[1],
								"=BLOCK_END_3" => $arIP[2],
							),
						),
					),
				"select" => $arSelect,
			));
			$arData = $rData->fetch();
			if ($arData) {
				$arResult = $arData;
			} else {
				$rData = self::getList(array(
					"filter" =>
						array(
							"=BLOCK_START_1" => $arIP[0],
							"=BLOCK_START_2" => $arIP[1],
							"<=BLOCK_START_3" => $arIP[2],
							"=BLOCK_END_1" => $arIP[0],
							"=BLOCK_END_2" => $arIP[1],
							">=BLOCK_END_3" => $arIP[2],
						),
					"select" => $arSelect,
				));
				$arData = $rData->fetch();
				if ($arData) {
					$arResult = $arData;
				} else {
					$ipNum = $arIP[0] * 16777216 + $arIP[1] * 65536 + $arIP[2] * 256 + $arIP[3];

					$rData = self::getList(array(
						"filter" =>
							array(
								array(
									"LOGIC" => "OR",
									array(
										"=BLOCK_START_1" => $arIP[0],
									),
									array(
										"=BLOCK_END_1" => $arIP[0],
									),
								),
								"<=BLOCK_START" => $ipNum,
								">=BLOCK_END" => $ipNum,
							),
						"select" => $arSelect,
					));
					$arData = $rData->fetch();
					if ($arData) {
						$arResult = $arData;
					} else {
						$rData = self::getList(array(
							"filter" =>
								array(
									"<=BLOCK_START" => $ipNum,
									">=BLOCK_END" => $ipNum,
								),
							"select" => $arSelect,
						));
						$arData = $rData->fetch();
						$arResult = $arData;
					}
				}
			}
			return $arResult['CITY_ID'];
		}
		return false;
	}

	static function getCityFullInfoByID($iCityID)
	{
		$arResult = false;
		if ($iCityID > 0) {
			$arInfo = CityTable::getList(array(
				"filter" => array(
					"ID" => $iCityID,
				),
				"select" => array(
					"CITY_ID" => "ID",
					"REGION_ID" => "REGION_ID",
					"COUNTRY_ID" => "REGION.COUNTRY_ID",
					"CITY_NAME" => "NAME",
					"CITY_LAT" => "LAT",
					"CITY_LON" => "LON",
					"CITY_OKATO" => "OKATO",
					"CITY_LOCATION_ID" => "LOCATION_ID",
					"CITY_IS_DEFAULT" => "IS_DEFAULT",
					"CITY_IS_FAVORITE" => "IS_FAVORITE",
					"CITY_EXT_ID" => "EXT_ID",
					"REGION_CODE" => "REGION.CODE",
					"REGION_NAME" => "REGION.NAME",
					"REGION_OKATO" => "REGION.OKATO",
					"REGION_TIMEZONE" => "REGION.TIMEZONE",
					"REGION_LOCATION_ID" => "REGION.LOCATION_ID",
					"REGION_EXT_ID" => "REGION.EXT_ID",
					"COUNTRY_CODE" => "REGION.COUNTRY.CODE",
					"COUNTRY_CONTINENT" => "REGION.COUNTRY.CONTINENT",
					"COUNTRY_NAME" => "REGION.COUNTRY.NAME",
					"COUNTRY_LAT" => "REGION.COUNTRY.LAT",
					"COUNTRY_LON" => "REGION.COUNTRY.LON",
					"COUNTRY_TIMEZONE" => "REGION.COUNTRY.TIMEZONE",
					"COUNTRY_LOCATION_ID" => "REGION.COUNTRY.LOCATION_ID",
					"COUNTRY_EXT_ID" => "REGION.COUNTRY.EXT_ID",
				),
			))->fetch();
			if ($arInfo) {
				if ($arInfo['CITY_LOCATION_ID'] === false) {
					/*
					 * @todo ƒобавить определение местоположени€ города
					 */
				}
				foreach ($arInfo as $k => $v) {
					if (amreg_strpos($k, "CITY_") === 0) {
						$arResult['CITY'][amreg_substr($k, 5)] = $v;
					} elseif (amreg_strpos($k, "REGION_") === 0) {
						$arResult['REGION'][amreg_substr($k, 7)] = $v;
					} elseif (amreg_strpos($k, "COUNTRY_") === 0) {
						$arResult['COUNTRY'][amreg_substr($k, 8)] = $v;
					}
				}
				$arResult['CITY']['NAME'] = \CKitMultiRegions::getFirstNotEmpty(CityLangTable::getLangNames($arResult['CITY']['ID']));
				$arResult['REGION']['NAME'] = \CKitMultiRegions::getFirstNotEmpty(RegionLangTable::getLangNames($arResult['REGION']['ID']));
				$arResult['COUNTRY']['NAME'] = \CKitMultiRegions::getFirstNotEmpty(CountryLangTable::getLangNames($arResult['COUNTRY']['ID']));
			}
		}
		return $arResult;
	}

	static public function getCityByIP($IP = false, $iPreventCity = false)
	{
		$arResult = false;
		if ($iPreventCity > 0) {
			$cityId = $iPreventCity;
		} else {
			$cityId = self::getCityIdByIP($IP);
		}
		if ($cityId > 0) {
			$arResult = self::getCityFullInfoByID($cityId);
		}
		return $arResult;
	}
}