<?

namespace Ammina\Regions;

use Ammina\Regions\Parser\SypexGeo;
use Bitrix\Landing\Block;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\Location\TypeTable;

Loc::loadMessages(__FILE__);

class Import
{
	protected $NS = array();
	protected $startTime = false;
	protected $arImportData = array();
	protected $arErrors = array();
	protected $arCacheData = array();
	protected $arAllowCountryCode = array();
	/**
	 * @var SypexGeo
	 */
	protected $oSypexGeo = NULL;

	public function __construct($NS, $startTime = false)
	{
		if ($startTime === false) {
			$startTime = time();
		}
		$this->NS = $NS;
		$this->startTime = $startTime;
		$this->oSypexGeo = new SypexGeo();
		$this->arAllowCountryCode = explode("|", \COption::GetOptionString("ammina.regions", "only_country", ""));
		foreach ($this->arAllowCountryCode as $k => $v) {
			$this->arAllowCountryCode[$k] = trim($v);
			if (amreg_strlen($this->arAllowCountryCode[$k]) <= 0) {
				unset($this->arAllowCountryCode[$k]);
			}
		}
		if ($this->NS['DELETE_CITY'] != "Y") {
			CountryTable::$notDeleteLang = true;
			RegionTable::$notDeleteLang = true;
			CityTable::$notDeleteLang = true;
		}
	}

	public function isAllowCountry($strCountryCode)
	{
		$strCountryCode = trim(amreg_strtoupper($strCountryCode));
		if (count($this->arAllowCountryCode) > 0) {
			if (!in_array($strCountryCode, $this->arAllowCountryCode)) {
				return false;
			}
		}
		return true;
	}

	public function getErrors()
	{
		return $this->arErrors;
	}

	public function getNSData()
	{
		return $this->NS;
	}

	public function getImportData()
	{
		return $this->arImportData;
	}

	public function doImportProcess($arImportData)
	{
		$this->arImportData = $arImportData;

		if ($this->NS['STEP'] <= 0) {
			return $this->doStep0();
		} elseif ($this->NS['STEP'] == 1) {
			return $this->doStep1();
		} elseif ($this->NS['STEP'] == 2) {
			return $this->doStep2();
		} elseif ($this->NS['STEP'] == 3) {
			return $this->doStep3();
		} elseif ($this->NS['STEP'] == 4) {
			return $this->doStep4();
		} elseif ($this->NS['STEP'] == 5) {
			return $this->doStep5();
		} elseif ($this->NS['STEP'] == 6) {
			return $this->doStep6();
		} elseif ($this->NS['STEP'] == 7) {
			return $this->doStep7();
		} elseif ($this->NS['STEP'] == 8) {
			return $this->doStep8();
		} elseif ($this->NS['STEP'] == 9) {
			return $this->doStep9();
		} elseif ($this->NS['STEP'] == 10) {
			return $this->doStep10();
		} elseif ($this->NS['STEP'] == 11) {
			return $this->doStep11();
		}
		return true;
	}

	protected function doEndTimeInterval()
	{
		if ((time() - $this->startTime) >= $this->NS['INTERVAL']) {
			return true;
		}
		return false;
	}

	protected function getAllIndexesBlock()
	{
		global $DB;
		$arIndexes = array();
		$res = $DB->query("show index from " . BlockTable::getTableName(), true);
		while ($item = $res->fetch()) {
			if (isset($item['Key_name']) && amreg_strlen($item['Key_name']) > 0) {
				$arIndexes[] = amreg_strtoupper($item['Key_name']);
			}
			if (isset($item['KEY_NAME']) && amreg_strlen($item['KEY_NAME']) > 0) {
				$arIndexes[] = amreg_strtoupper($item['KEY_NAME']);
			}
		}
		return $arIndexes;
	}

	/**
	 * Шаг 0: Очистка таблиц и файлов
	 */
	protected function doStep0()
	{
		global $DB;
		if (!$DB->Query("TRUNCATE TABLE `" . BlockTable::getTableName() . "`;", true)) {
			$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_TRUNCATE_BLOCK");
			return false;
		}
		$arIndexes = $this->getAllIndexesBlock();
		if (in_array("IX_BLOCK_START_1", $arIndexes) && !$DB->Query("ALTER TABLE `" . BlockTable::getTableName() . "` DROP KEY `IX_BLOCK_START_1`;", true)) {
			$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_ALTER_DROP_KEY_BLOCK");
			return false;
		}
		if (in_array("IX_BLOCK_START_2", $arIndexes) && !$DB->Query("ALTER TABLE `" . BlockTable::getTableName() . "` DROP KEY `IX_BLOCK_START_2`;", true)) {
			$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_ALTER_DROP_KEY_BLOCK");
			return false;
		}
		if (in_array("IX_BLOCK_START_3", $arIndexes) && !$DB->Query("ALTER TABLE `" . BlockTable::getTableName() . "` DROP KEY `IX_BLOCK_START_3`;", true)) {
			$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_ALTER_DROP_KEY_BLOCK");
			return false;
		}
		if (in_array("IX_BLOCK_START_4", $arIndexes) && !$DB->Query("ALTER TABLE `" . BlockTable::getTableName() . "` DROP KEY `IX_BLOCK_START_4`;", true)) {
			$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_ALTER_DROP_KEY_BLOCK");
			return false;
		}
		if (in_array("IX_BLOCK_END_1", $arIndexes) && !$DB->Query("ALTER TABLE `" . BlockTable::getTableName() . "` DROP KEY `IX_BLOCK_END_1`;", true)) {
			$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_ALTER_DROP_KEY_BLOCK");
			return false;
		}
		if (in_array("IX_BLOCK_END_2", $arIndexes) && !$DB->Query("ALTER TABLE `" . BlockTable::getTableName() . "` DROP KEY `IX_BLOCK_END_2`;", true)) {
			$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_ALTER_DROP_KEY_BLOCK");
			return false;
		}
		if (in_array("IX_BLOCK_END_3", $arIndexes) && !$DB->Query("ALTER TABLE `" . BlockTable::getTableName() . "` DROP KEY `IX_BLOCK_END_3`;", true)) {
			$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_ALTER_DROP_KEY_BLOCK");
			return false;
		}
		if (in_array("IX_BLOCK_END_4", $arIndexes) && !$DB->Query("ALTER TABLE `" . BlockTable::getTableName() . "` DROP KEY `IX_BLOCK_END_4`;", true)) {
			$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_ALTER_DROP_KEY_BLOCK");
			return false;
		}
		if (in_array("IX_BLOCK_START", $arIndexes) && !$DB->Query("ALTER TABLE `" . BlockTable::getTableName() . "` DROP KEY `IX_BLOCK_START`;", true)) {
			$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_ALTER_DROP_KEY_BLOCK");
			return false;
		}
		if (in_array("IX_BLOCK_END", $arIndexes) && !$DB->Query("ALTER TABLE `" . BlockTable::getTableName() . "` DROP KEY `IX_BLOCK_END`;", true)) {
			$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_ALTER_DROP_KEY_BLOCK");
			return false;
		}
		if ($this->NS['DELETE_CITY'] === "Y") {
			if (!$DB->Query("TRUNCATE TABLE `" . CityTable::getTableName() . "`;", true)) {
				$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_TRUNCATE_CITY");
				return false;
			}
			if (!$DB->Query("TRUNCATE TABLE `" . CityLangTable::getTableName() . "`;", true)) {
				$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_TRUNCATE_CITY");
				return false;
			}
			if (!$DB->Query("TRUNCATE TABLE `" . RegionTable::getTableName() . "`;", true)) {
				$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_TRUNCATE_REGION");
				return false;
			}
			if (!$DB->Query("TRUNCATE TABLE `" . RegionLangTable::getTableName() . "`;", true)) {
				$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_TRUNCATE_REGION");
				return false;
			}
			if (!$DB->Query("TRUNCATE TABLE `" . CountryTable::getTableName() . "`;", true)) {
				$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_TRUNCATE_COUNTRY");
				return false;
			}
			if (!$DB->Query("TRUNCATE TABLE `" . CountryLangTable::getTableName() . "`;", true)) {
				$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_TRUNCATE_COUNTRY");
				return false;
			}
		}
		$this->doDeleteDirectory($_SERVER['DOCUMENT_ROOT'] . "/bitrix/ammina/regions/db/sypexgeo/");
		$this->NS['STEP'] = 1;
		return true;
	}

	/**
	 * Загрузка файлов
	 */
	protected function doStep1()
	{
		$bResult = true;
		if (!$this->arImportData['LOADED_INFO_FILE']) {
			$bResult = $this->oSypexGeo->doDownloadFileInfo();
			if ($bResult) {
				$this->arImportData['LOADED_INFO_FILE'] = true;
			} else {
				$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_STEP1_LOAD_INFO");
			}
		} else {
			$bResult = $this->oSypexGeo->doDownloadFileCity();
			if ($bResult) {
				$this->NS['STEP'] = 2;
			} else {
				$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_STEP1_LOAD_CITY");
			}
		}
		return $bResult;
	}

	/**
	 * Распаковка файлов
	 */
	protected function doStep2()
	{
		$bResult = true;
		if (!$this->arImportData['EXTRACT_INFO_FILE']) {
			$bResult = $this->oSypexGeo->doExtractFileInfo();
			if ($bResult) {
				$this->arImportData['EXTRACT_INFO_FILE'] = true;
			} else {
				$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_STEP2_EXTRACT_INFO");
			}
		} else {
			$bResult = $this->oSypexGeo->doExtractFileCity();
			if ($bResult) {
				$this->NS['STEP'] = 3;
			} else {
				$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_STEP2_EXTRACT_CITY");
			}
		}
		return $bResult;
	}

	/**
	 * Загрузка блоков
	 */
	protected function doStep3()
	{
		$bResult = true;
		if (amreg_strlen($this->arImportData['CURRENT_IP']) <= 0) {
			$this->arImportData['CURRENT_IP'] = "0.0.0.0";
		}
		if (!isset($this->arImportData['STEP3_TOTAL'])) {
			$this->arImportData['STEP3_TOTAL'] = 256 * 256 * 256;
		}
		$arIP = explode(".", $this->arImportData['CURRENT_IP']);
		$oldCityId = 0;
		$strStart = "";
		$strEnd = "";
		$arEnd = array();
		$this->oSypexGeo->loadCityFile(\COption::GetOptionString("ammina.regions", "mode_import", SypexGeo::SXGEO_BATCH));
		for ($i1 = $arIP[0]; $i1 <= 255; $i1++) {
			for ($i2 = $arIP[1]; $i2 <= 255; $i2++) {
				for ($i3 = $arIP[2]; $i3 <= 255; $i3++) {
					$this->arImportData['STEP3_CNT']++;
					$arIP[0] = 0;
					$arIP[1] = 0;
					$arIP[2] = 0;
					$arCityData = $this->oSypexGeo->getCityFull($i1 . "." . $i2 . "." . $i3 . ".0");
					if ($arCityData && !$this->isAllowCountry($arCityData['country']['iso'])) {
						$arCityData = false;
					}
					$arCityData = $this->doCheckCityData($arCityData);
					$COUNTRY_ID = false;
					$REGION_ID = false;
					$CITY_ID = false;
					if (isset($arCityData['country']) && !empty($arCityData['country']) && $arCityData['country']['id'] > 0) {
						$arFields = array(
							"CODE" => $arCityData['country']['iso'],
							"NAME" => $arCityData['country']['name_ru'],
							"LANG" => array(
								"en" => $arCityData['country']['name_en'],
							),
							"LAT" => $arCityData['country']['lat'],
							"LON" => $arCityData['country']['lon'],
							"EXT_ID" => $arCityData['country']['id'],
						);
						if (!$this->doCheckCountryDB($arFields)) {
							$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_STEP3_CHECK_COUNTRY");
							return false;
						}
						$COUNTRY_ID = $this->arCacheData['COUNTRY'][$arFields['EXT_ID']];
					}
					if (isset($arCityData['region']) && !empty($arCityData['region']) && $arCityData['region']['id'] > 0) {
						$arFields = array(
							"COUNTRY_ID" => $COUNTRY_ID,
							"CODE" => $arCityData['region']['iso'],
							"NAME" => $arCityData['region']['name_ru'],
							"LANG" => array(
								"en" => $arCityData['region']['name_en'],
							),
							"EXT_ID" => $arCityData['region']['id'],
						);
						if (!$this->doCheckRegionDB($arFields)) {
							$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_STEP3_CHECK_REGION");
							return false;
						}
						$REGION_ID = $this->arCacheData['REGION'][$arFields['EXT_ID']];
					}
					if (isset($arCityData['city']) && !empty($arCityData['city']) && $arCityData['city']['id'] > 0) {
						$arFields = array(
							"REGION_ID" => $REGION_ID,
							"LAT" => $arCityData['city']['lat'],
							"LON" => $arCityData['city']['lon'],
							"NAME" => $arCityData['city']['name_ru'],
							"LANG" => array(
								"en" => $arCityData['city']['name_en'],
							),
							"EXT_ID" => $arCityData['city']['id'],
						);
						if (!$this->doCheckCityDB($arFields)) {
							$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_STEP3_CHECK_CITY");
							return false;
						}
						$CITY_ID = $this->arCacheData['CITY'][$arFields['EXT_ID']];
					}

					$newCityId = false;
					if (isset($arCityData['city']['id']) && $arCityData['city']['id'] > 0) {
						$newCityId = $arCityData['city']['id'];
					}
					if ($newCityId !== $oldCityId) {
						if (amreg_strlen($strStart) > 0) {
							if ($this->doCheckIPBlock($strStart, $strEnd, $oldCityId)) {
								$this->arImportData['CURRENT_IP'] = $this->getNextIpAddr($i1 . "." . $i2 . "." . $i3 . ".255");
								if ($this->arImportData['CURRENT_IP'] == "0.0.0.0") {
									$this->NS['STEP'] = 4;
									$this->oSypexGeo->closeCityFile();
									return true;
								}
							} else {
								$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_STEP3_CHECK_BLOCK");
								return false;
							}
							if ($this->doEndTimeInterval()) {
								$this->oSypexGeo->closeCityFile();
								return true;
							}
						}
						$strStart = $i1 . "." . $i2 . "." . $i3 . ".0";
					} elseif ($this->doEndTimeInterval()) {
						if (amreg_strlen($strStart) > 0) {
							if ($this->doCheckIPBlock($strStart, $strEnd, $oldCityId)) {
								$this->arImportData['CURRENT_IP'] = $i1 . "." . $i2 . "." . $i3 . ".0";
								if ($this->arImportData['CURRENT_IP'] == "0.0.0.0") {
									$this->NS['STEP'] = 4;
									$this->oSypexGeo->closeCityFile();
									return true;
								}
							} else {
								$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_STEP3_CHECK_BLOCK");
								return false;
							}
							if ($this->doEndTimeInterval()) {
								$this->oSypexGeo->closeCityFile();
								return true;
							}
						}
						$strStart = $i1 . "." . $i2 . "." . $i3 . ".0";
					}
					$strEnd = $i1 . "." . $i2 . "." . $i3 . ".255";
					$oldCityId = $newCityId;
				}
			}
		}
		if (amreg_strlen($strStart) > 0) {
			if ($this->doCheckIPBlock($strStart, $strEnd, $oldCityId)) {
				$this->NS['STEP'] = 4;
				$this->oSypexGeo->closeCityFile();
				return true;
			} else {
				$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_STEP3_CHECK_BLOCK");
				return false;
			}
		} else {
			$this->NS['STEP'] = 4;
		}
		$this->oSypexGeo->closeCityFile();
		return $bResult;
	}

	/**
	 * Обновление стран из CSV
	 */
	protected function doStep4()
	{
		$strFile = $_SERVER['DOCUMENT_ROOT'] . "/bitrix/ammina/regions/db/sypexgeo/info/country.tsv";
		if (!defined("BX_UTF") || BX_UTF !== true) {
			if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/bitrix/ammina/regions/db/sypexgeo/info/country.win.tsv")) {
				file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/bitrix/ammina/regions/db/sypexgeo/info/country.win.tsv", iconv("utf-8", "windows-1251", file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/bitrix/ammina/regions/db/sypexgeo/info/country.tsv")));
			}
			$strFile = $_SERVER['DOCUMENT_ROOT'] . "/bitrix/ammina/regions/db/sypexgeo/info/country.win.tsv";
		}
		if (!isset($this->arImportData['STEP4_TOTAL'])) {
			$this->arImportData['STEP4_TOTAL'] = 0;
			$f = fopen($strFile, "rt");
			if ($f) {
				while ($arFileData = fgetcsv($f, 1000, "\t", '"')) {
					if ($this->isAllowCountry($arFileData[1])) {
						$this->arImportData['STEP4_TOTAL']++;
					}
				}
				fclose($f);
			}
		}
		$f = fopen($strFile, "rt");
		if ($f) {
			$i = 0;
			while ($arFileData = fgetcsv($f, 1000, "\t", '"')) {
				if (isset($this->arImportData['STEP4_NEXT']) && $this->arImportData['STEP4_NEXT'] > 0 && $i < $this->arImportData['STEP4_NEXT']) {
					$i++;
					continue;
				}
				if (!$this->isAllowCountry($arFileData[1])) {
					$i++;
					continue;
				}
				$this->arImportData['STEP4_CNT']++;
				$arFields = array(
					"EXT_ID" => $arFileData[0],
					"CODE" => $arFileData[1],
					"CONTINENT" => $arFileData[2],
					"NAME" => $arFileData[3],
					"LANG" => array(
						"en" => $arFileData[4],
					),
					"LAT" => $arFileData[5],
					"LON" => $arFileData[6],
					"TIMEZONE" => $arFileData[7],
				);
				$arData = CountryTable::getList(array(
					"filter" => array(
						"EXT_ID" => $arFields['EXT_ID'],
					),
					"select" => array('ID', 'CODE', 'CONTINENT', 'NAME', 'LAT', 'LON', 'TIMEZONE', 'LOCATION_ID', 'EXT_ID')
				))->fetch();
				if ($arData) {
					$arUpdate = array();
					foreach ($arFields as $k => $v) {
						if ($k == "LANG") {
							continue;
						}
						if ($arData[$k] != $arFields[$k]) {
							$arUpdate[$k] = $v;
						}
					}
					if (!empty($arUpdate)) {
						$dbRes = CountryTable::update($arData['ID'], $arUpdate);
						if (!$dbRes->isSuccess()) {
							$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_STEP4_UPDATE_COUNTRY");
							return false;
						}
					}
				} else {
					$dbRes = CountryTable::add($arFields);
					if (!$dbRes->isSuccess()) {
						$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_STEP4_ADD_COUNTRY");
						return false;
					}
				}
				$i++;
				$this->arImportData['STEP4_NEXT'] = $i;
				if ($this->doEndTimeInterval()) {
					fclose($f);
					return true;
				}
			}
			fclose($f);
		}
		$this->NS['STEP'] = 5;
		return true;
	}

	/**
	 * Обновление регионов из CSV
	 */
	protected function doStep5()
	{
		$strFile = $_SERVER['DOCUMENT_ROOT'] . "/bitrix/ammina/regions/db/sypexgeo/info/region.tsv";
		if (!defined("BX_UTF") || BX_UTF !== true) {
			if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/bitrix/ammina/regions/db/sypexgeo/info/region.win.tsv")) {
				file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/bitrix/ammina/regions/db/sypexgeo/info/region.win.tsv", iconv("utf-8", "windows-1251", file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/bitrix/ammina/regions/db/sypexgeo/info/region.tsv")));
			}
			$strFile = $_SERVER['DOCUMENT_ROOT'] . "/bitrix/ammina/regions/db/sypexgeo/info/region.win.tsv";
		}
		if (!isset($this->arImportData['STEP5_TOTAL'])) {
			$this->arImportData['STEP5_TOTAL'] = 0;
			$f = fopen($strFile, "rt");
			if ($f) {
				while ($arFileData = fgetcsv($f, 1000, "\t", '"')) {
					$this->arImportData['STEP5_TOTAL']++;
				}
				fclose($f);
			}
		}
		$f = fopen($strFile, "rt");
		if ($f) {
			$i = 0;
			while ($arFileData = fgetcsv($f, 1000, "\t", '"')) {
				if (isset($this->arImportData['STEP5_NEXT']) && $this->arImportData['STEP5_NEXT'] > 0 && $i < $this->arImportData['STEP5_NEXT']) {
					$i++;
					continue;
				}
				$this->arImportData['STEP5_CNT']++;
				$arFileData = $this->doCheckRegionDataFromCsv($arFileData);
				$arFields = array(
					"EXT_ID" => $arFileData[0],
					"CODE" => $arFileData[1],
					"NAME" => $arFileData[3],
					"LANG" => array(
						"en" => $arFileData[4],
					),
					"TIMEZONE" => $arFileData[5],
					"OKATO" => $arFileData[6],
				);
				$arCountry = false;
				if (amreg_strlen($arFileData[2]) > 0) {
					if (!$this->isAllowCountry($arFileData[2])) {
						$i++;
						continue;
					}
					$arCountry = CountryTable::getList(array(
						"filter" => array(
							"CODE" => $arFileData[2],
						),
					))->fetch();
					if (!$arCountry) {
						$i++;
						continue;
					}
					$arFields['COUNTRY_ID'] = $arCountry['ID'];
				} else {
					$i++;
					continue;
				}
				$arData = RegionTable::getList(array(
					"filter" => array(
						"EXT_ID" => $arFields['EXT_ID'],
					),
				))->fetch();
				if ($arData) {
					$arUpdate = array();
					foreach ($arFields as $k => $v) {
						if ($k == "LANG") {
							continue;
						}
						if ($arData[$k] != $arFields[$k]) {
							$arUpdate[$k] = $v;
						}
					}
					if (!empty($arUpdate)) {
						$dbRes = RegionTable::update($arData['ID'], $arUpdate);
						if (!$dbRes->isSuccess()) {
							$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_STEP5_UPDATE_REGION");
							return false;
						}
					}
				} else {
					if ($arFields['COUNTRY_ID'] > 0) {
						$dbRes = RegionTable::add($arFields);
						if (!$dbRes->isSuccess()) {
							$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_STEP5_ADD_REGION");
							return false;
						}
					}
				}
				$i++;
				$this->arImportData['STEP5_NEXT'] = $i;
				if ($this->doEndTimeInterval()) {
					fclose($f);
					return true;
				}
			}
			fclose($f);
		}
		$this->NS['STEP'] = 6;
		return true;
	}

	/**
	 * Обновление городов из CSV
	 */
	protected function doStep6()
	{
		$strFile = $_SERVER['DOCUMENT_ROOT'] . "/bitrix/ammina/regions/db/sypexgeo/info/city.tsv";
		if (!defined("BX_UTF") || BX_UTF !== true) {
			if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/bitrix/ammina/regions/db/sypexgeo/info/city.win.tsv")) {
				file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/bitrix/ammina/regions/db/sypexgeo/info/city.win.tsv", iconv("utf-8", "windows-1251", file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/bitrix/ammina/regions/db/sypexgeo/info/city.tsv")));
			}
			$strFile = $_SERVER['DOCUMENT_ROOT'] . "/bitrix/ammina/regions/db/sypexgeo/info/city.win.tsv";
		}
		if (!isset($this->arImportData['STEP6_TOTAL'])) {
			$this->arImportData['STEP6_TOTAL'] = 0;
			$f = fopen($strFile, "rt");
			if ($f) {
				while ($arFileData = fgetcsv($f, 1000, "\t", '"')) {
					$this->arImportData['STEP6_TOTAL']++;
				}
				fclose($f);
			}
		}
		$f = fopen($strFile, "rt");
		if ($f) {
			$i = 0;
			while ($arFileData = fgetcsv($f, 1000, "\t", '"')) {
				if (isset($this->arImportData['STEP6_NEXT']) && $this->arImportData['STEP6_NEXT'] > 0 && $i < $this->arImportData['STEP6_NEXT']) {
					$i++;
					continue;
				}
				$this->arImportData['STEP6_CNT']++;
				$arFields = array(
					"EXT_ID" => $arFileData[0],
					"NAME" => $arFileData[2],
					"LANG" => array(
						"en" => $arFileData[3],
					),
					"LAT" => $arFileData[4],
					"LON" => $arFileData[5],
					"OKATO" => $arFileData[6],
				);
				$arRegion = false;
				if (amreg_strlen($arFileData[1]) > 0) {
					$arRegion = RegionTable::getList(array(
						"filter" => array(
							"EXT_ID" => $arFileData[1],
						),
					))->fetch();
					if (!$arRegion) {
						$i++;
						continue;
					}
					$arFields['REGION_ID'] = $arRegion['ID'];
				} else {
					$i++;
					continue;
				}
				$arData = CityTable::getList(array(
					"filter" => array(
						"EXT_ID" => $arFields['EXT_ID'],
					),
				))->fetch();
				if ($arData) {
					$arUpdate = array();
					foreach ($arFields as $k => $v) {
						if ($k == "LANG") {
							continue;
						}
						if ($arData[$k] != $arFields[$k]) {
							$arUpdate[$k] = $v;
						}
					}
					if (!empty($arUpdate)) {
						$dbRes = CityTable::update($arData['ID'], $arUpdate);
						if (!$dbRes->isSuccess()) {
							$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_STEP6_UPDATE_CITY");
							return false;
						}
					}
				} else {
					if ($arFields['REGION_ID'] > 0) {
						$dbRes = CityTable::add($arFields);
						if (!$dbRes->isSuccess()) {
							$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_STEP6_ADD_CITY");
							return false;
						}
					}
				}
				$i++;
				$this->arImportData['STEP6_NEXT'] = $i;
				if ($this->doEndTimeInterval()) {
					fclose($f);
					return true;
				}
			}
			fclose($f);
		}
		$this->NS['STEP'] = 7;
		return true;
	}

	/**
	 * Добавляем индексы
	 */
	protected function doStep7()
	{
		global $DB;
		$arIndexes = $this->getAllIndexesBlock();
		if (!in_array("IX_BLOCK_START_1", $arIndexes) && !$DB->Query("ALTER TABLE `" . BlockTable::getTableName() . "` ADD KEY `IX_BLOCK_START_1` (`BLOCK_START_1`);", true)) {
			$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_ALTER_ADD_KEY_BLOCK");
			return false;
		}
		if (!in_array("IX_BLOCK_START_2", $arIndexes) && !$DB->Query("ALTER TABLE `" . BlockTable::getTableName() . "` ADD KEY `IX_BLOCK_START_2` (`BLOCK_START_2`);", true)) {
			$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_ALTER_ADD_KEY_BLOCK");
			return false;
		}
		if (!in_array("IX_BLOCK_START_3", $arIndexes) && !$DB->Query("ALTER TABLE `" . BlockTable::getTableName() . "` ADD KEY `IX_BLOCK_START_3` (`BLOCK_START_3`);", true)) {
			$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_ALTER_ADD_KEY_BLOCK");
			return false;
		}
		if (!in_array("IX_BLOCK_START_4", $arIndexes) && !$DB->Query("ALTER TABLE `" . BlockTable::getTableName() . "` ADD KEY `IX_BLOCK_START_4` (`BLOCK_START_4`);", true)) {
			$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_ALTER_ADD_KEY_BLOCK");
			return false;
		}
		if (!in_array("IX_BLOCK_END_1", $arIndexes) && !$DB->Query("ALTER TABLE `" . BlockTable::getTableName() . "` ADD KEY `IX_BLOCK_END_1` (`BLOCK_END_1`);", true)) {
			$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_ALTER_ADD_KEY_BLOCK");
			return false;
		}
		if (!in_array("IX_BLOCK_END_2", $arIndexes) && !$DB->Query("ALTER TABLE `" . BlockTable::getTableName() . "` ADD KEY `IX_BLOCK_END_2` (`BLOCK_END_2`);", true)) {
			$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_ALTER_ADD_KEY_BLOCK");
			return false;
		}
		if (!in_array("IX_BLOCK_END_3", $arIndexes) && !$DB->Query("ALTER TABLE `" . BlockTable::getTableName() . "` ADD KEY `IX_BLOCK_END_3` (`BLOCK_END_3`);", true)) {
			$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_ALTER_ADD_KEY_BLOCK");
			return false;
		}
		if (!in_array("IX_BLOCK_END_4", $arIndexes) && !$DB->Query("ALTER TABLE `" . BlockTable::getTableName() . "` ADD KEY `IX_BLOCK_END_4` (`BLOCK_END_4`);", true)) {
			$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_ALTER_ADD_KEY_BLOCK");
			return false;
		}
		if (!in_array("IX_BLOCK_START", $arIndexes) && !$DB->Query("ALTER TABLE `" . BlockTable::getTableName() . "` ADD KEY `IX_BLOCK_START` (`BLOCK_START`);", true)) {
			$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_ALTER_ADD_KEY_BLOCK");
			return false;
		}
		if (!in_array("IX_BLOCK_END", $arIndexes) && !$DB->Query("ALTER TABLE `" . BlockTable::getTableName() . "` ADD KEY `IX_BLOCK_END` (`BLOCK_END`);", true)) {
			$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_TABLE_ALTER_ADD_KEY_BLOCK");
			return false;
		}
		$this->NS['STEP'] = 8;
		return true;
	}

	/**
	 * Привязка стран к местоположениям
	 */
	protected function doStep8()
	{
		if (\CModule::IncludeModule("sale")) {
			if (!isset($this->arImportData['STEP8_TOTAL'])) {
				$arCountry = CountryTable::getList(array(
					'select' => array('CNT'),
					'runtime' => array(
						new ExpressionField('CNT', 'COUNT(*)'),
					),
				))->fetch();
				$this->arImportData['STEP8_TOTAL'] = $arCountry['CNT'];

			}
			$arFilter = array();
			if (isset($this->arImportData['STEP_8_NEXT_ID']) && $this->arImportData['STEP_8_NEXT_ID'] > 0) {
				$arFilter['>ID'] = $this->arImportData['STEP_8_NEXT_ID'];
			}
			$rCountry = CountryTable::getList(array(
				"order" => array("ID" => "ASC"),
				"filter" => $arFilter,
			));
			while ($arCountry = $rCountry->fetch()) {
				$this->arImportData['STEP8_CNT']++;
				$bFinded = false;
				$arLocation = LocationTable::getList(array(
					"filter" => array(
						"PARENT_ID" => 0,
						"NAME.NAME" => $arCountry['NAME'],
					),
				))->fetch();
				if ($arLocation) {
					$bFinded = true;
				}
				$arCountryLang = CountryLangTable::getList(array("filter" => array("COUNTRY_ID" => $arCountry['ID'], "LID" => "en"), "select" => array("ID", "NAME_EN" => "NAME")))->fetch();
				if (!$bFinded && $arCountryLang) {
					$arLocation = LocationTable::getList(array(
						"filter" => array(
							"PARENT_ID" => 0,
							"NAME.NAME" => $arCountryLang['NAME_EN'],
						),
					))->fetch();
					if ($arLocation) {
						$bFinded = true;
					}
				}
				if ($bFinded) {
					if ($arCountry['LOCATION_ID'] != $arLocation['ID']) {
						$dbRes = CountryTable::update($arCountry['ID'], array(
							"LOCATION_ID" => $arLocation['ID'],
						));
						if (!$dbRes->isSuccess()) {
							$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_STEP8_UPDATE_COUNTRY_LOCATION");
							return false;
						}
					}
				}
				$this->arImportData['STEP_8_NEXT_ID'] = $arCountry['ID'];
				if ($this->doEndTimeInterval()) {
					return true;
				}
			}
		}
		$this->NS['STEP'] = 9;
		return true;
	}

	/**
	 * Привязка регионов к местоположениям
	 */
	protected function doStep9()
	{
		$CACHE_COUNTRY = array();
		if (\CModule::IncludeModule("sale")) {
			if (!isset($this->arImportData['STEP9_TOTAL'])) {
				$arRegion = RegionTable::getList(array(
					'select' => array('CNT'),
					'runtime' => array(
						new ExpressionField('CNT', 'COUNT(*)'),
					),
				))->fetch();
				$this->arImportData['STEP9_TOTAL'] = $arRegion['CNT'];
			}
			$arFilter = array();
			if (isset($this->arImportData['STEP_9_NEXT_ID']) && $this->arImportData['STEP_9_NEXT_ID'] > 0) {
				$arFilter['>ID'] = $this->arImportData['STEP_9_NEXT_ID'];
			}
			$arTypeRegion = TypeTable::getList(array(
				"filter" => array(
					"CODE" => "REGION",
				),
			))->fetch();
			$arTypeCity = TypeTable::getList(array(
				"filter" => array(
					"CODE" => "CITY",
				),
			))->fetch();
			$rRegion = RegionTable::getList(array(
				"order" => array("ID" => "ASC"),
				"filter" => $arFilter,
				"select" => array("ID", "NAME", "COUNTRY_ID", "COUNTRY_LOCATION_ID" => "COUNTRY.LOCATION_ID"),
			));
			while ($arRegion = $rRegion->fetch()) {
				$this->arImportData['STEP9_CNT']++;
				if ($arRegion['COUNTRY_LOCATION_ID'] > 0) {
					if (!isset($CACHE_COUNTRY[$arRegion['COUNTRY_LOCATION_ID']])) {
						$arCountryLocation = LocationTable::getList(array(
							"filter" => array(
								"ID" => $arRegion['COUNTRY_LOCATION_ID'],
							),
						))->fetch();
						$CACHE_COUNTRY[$arRegion['COUNTRY_LOCATION_ID']] = $arCountryLocation;
					} else {
						$arCountryLocation = $CACHE_COUNTRY[$arRegion['COUNTRY_LOCATION_ID']];
					}
					if ($arCountryLocation) {
						$bFinded = false;
						$arLocation = LocationTable::getList(array(
							"order" => array(
								"DEPTH_LEVEL" => "ASC",
							),
							"filter" => array(
								//"PARENT_ID" => $arRegion['COUNTRY_LOCATION_ID'],
								"NAME.NAME" => $arRegion['NAME'],
								">=LEFT_MARGIN" => $arCountryLocation['LEFT_MARGIN'],
								"<=RIGHT_MARGIN" => $arCountryLocation['RIGHT_MARGIN'],
								"TYPE_ID" => array($arTypeRegion['ID'], $arTypeCity['ID']),
							),
						))->fetch();
						if ($arLocation) {
							$bFinded = true;
						}
						$arRegionLang = RegionLangTable::getList(array("filter" => array("REGION_ID" => $arRegion['ID'], "LID" => "en"), "select" => array("ID", "NAME_EN" => "NAME")))->fetch();
						if (!$bFinded && $arRegionLang) {
							$arLocation = LocationTable::getList(array(
								"order" => array(
									"DEPTH_LEVEL" => "ASC",
								),
								"filter" => array(
									//"PARENT_ID" => $arRegion['COUNTRY_LOCATION_ID'],
									"NAME.NAME" => $arRegionLang['NAME_EN'],
									">=LEFT_MARGIN" => $arCountryLocation['LEFT_MARGIN'],
									"<=RIGHT_MARGIN" => $arCountryLocation['RIGHT_MARGIN'],
									"TYPE_ID" => array($arTypeRegion['ID'], $arTypeCity['ID']),
								),
							))->fetch();
							if ($arLocation) {
								$bFinded = true;
							}
						}
						if (!$bFinded) {
							$arLocation = LocationTable::getList(array(
								"order" => array(
									"DEPTH_LEVEL" => "ASC",
								),
								"filter" => array(
									//"PARENT_ID" => $arRegion['COUNTRY_LOCATION_ID'],
									"%NAME.NAME" => $arRegion['NAME'],
									">=LEFT_MARGIN" => $arCountryLocation['LEFT_MARGIN'],
									"<=RIGHT_MARGIN" => $arCountryLocation['RIGHT_MARGIN'],
									"TYPE_ID" => array($arTypeRegion['ID'], $arTypeCity['ID']),
								),
							))->fetch();
							if ($arLocation) {
								$bFinded = true;
							}
						}
						if (!$bFinded && $arRegionLang) {
							$arLocation = LocationTable::getList(array(
								"order" => array(
									"DEPTH_LEVEL" => "ASC",
								),
								"filter" => array(
									//"PARENT_ID" => $arRegion['COUNTRY_LOCATION_ID'],
									"%NAME.NAME" => $arRegionLang['NAME_EN'],
									">=LEFT_MARGIN" => $arCountryLocation['LEFT_MARGIN'],
									"<=RIGHT_MARGIN" => $arCountryLocation['RIGHT_MARGIN'],
									"TYPE_ID" => array($arTypeRegion['ID'], $arTypeCity['ID']),
								),
							))->fetch();
							if ($arLocation) {
								$bFinded = true;
							}
						}
						if (!$bFinded) {
							$arLocation = LocationTable::getList(array(
								"order" => array(
									"DEPTH_LEVEL" => "ASC",
								),
								"filter" => array(
									//"PARENT_ID" => $arRegion['COUNTRY_LOCATION_ID'],
									"NAME.NAME" => $this->doReplaceNameRegion($arRegion['NAME']),
									">=LEFT_MARGIN" => $arCountryLocation['LEFT_MARGIN'],
									"<=RIGHT_MARGIN" => $arCountryLocation['RIGHT_MARGIN'],
									"TYPE_ID" => array($arTypeRegion['ID'], $arTypeCity['ID']),
								),
							))->fetch();
							if ($arLocation) {
								$bFinded = true;
							}
						}
						if ($bFinded) {
							if ($arRegion['LOCATION_ID'] != $arLocation['ID']) {
								$dbRes = RegionTable::update($arRegion['ID'], array(
									"LOCATION_ID" => $arLocation['ID'],
								));
								if (!$dbRes->isSuccess()) {
									$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_STEP9_UPDATE_REGION_LOCATION");
									return false;
								}
							}
						}
					}
				}
				$this->arImportData['STEP_9_NEXT_ID'] = $arRegion['ID'];
				if ($this->doEndTimeInterval()) {
					return true;
				}
			}
		}
		$this->NS['STEP'] = 10;
		return true;
	}

	/**
	 * Привязка городов к местоположениям
	 */
	protected function doStep10()
	{
		if ($this->NS['LOCATION_CITY'] != "Y") {
			$this->NS['STEP'] = 11;
			return true;
		}
		$CACHE_REGION = array();
		if (\CModule::IncludeModule("sale")) {
			if (!isset($this->arImportData['STEP10_TOTAL'])) {
				$arCity = CityTable::getList(array(
					'select' => array('CNT'),
					'runtime' => array(
						new ExpressionField('CNT', 'COUNT(*)'),
					),
				))->fetch();
				$this->arImportData['STEP10_TOTAL'] = $arCity['CNT'];
			}
			$arFilter = array();
			if (isset($this->arImportData['STEP_10_NEXT_ID']) && $this->arImportData['STEP_10_NEXT_ID'] > 0) {
				$arFilter['>ID'] = $this->arImportData['STEP_10_NEXT_ID'];
			}
			$arTypeCity = TypeTable::getList(array(
				"filter" => array(
					"CODE" => "CITY",
				),
			))->fetch();
			$arTypeVillage = TypeTable::getList(array(
				"filter" => array(
					"CODE" => "VILLAGE",
				),
			))->fetch();
			$rCity = CityTable::getList(array(
				"order" => array("ID" => "ASC"),
				"filter" => $arFilter,
				"select" => array("ID", "NAME", "LOCATION_ID", "REGION_ID", "REGION_LOCATION_ID" => "REGION.LOCATION_ID"),
			));
			while ($arCity = $rCity->fetch()) {
				$this->arImportData['STEP10_CNT']++;
				if ($arCity['REGION_LOCATION_ID'] > 0 && $arCity['LOCATION_ID'] <= 0) {
					if (!isset($CACHE_REGION[$arCity['REGION_LOCATION_ID']])) {
						$arRegionLocation = LocationTable::getList(array(
							"filter" => array(
								"ID" => $arCity['REGION_LOCATION_ID'],
							),
						))->fetch();
						$CACHE_REGION[$arCity['REGION_LOCATION_ID']] = $arRegionLocation;
					} else {
						$arRegionLocation = $CACHE_REGION[$arCity['REGION_LOCATION_ID']];
					}
					if ($arRegionLocation) {
						$bFinded = false;
						if (!$bFinded) {
							$arLocation = LocationTable::getList(array(
								"order" => array(
									"DEPTH_LEVEL" => "ASC",
								),
								"filter" => array(
									//"PARENT_ID" => $arRegion['COUNTRY_LOCATION_ID'],
									"NAME.NAME" => $arCity['NAME'],
									">=LEFT_MARGIN" => $arRegionLocation['LEFT_MARGIN'],
									"<=RIGHT_MARGIN" => $arRegionLocation['RIGHT_MARGIN'],
									"TYPE_ID" => array($arTypeCity['ID'], $arTypeVillage['ID']),
								),
								"select" => array("ID"),
							))->fetch();
							if ($arLocation) {
								$bFinded = true;
							}
						}
						$arCityLang = CityLangTable::getList(array("filter" => array("CITY_ID" => $arCity['ID'], "LID" => "en"), "select" => array("ID", "NAME_EN" => "NAME")))->fetch();
						if (!$bFinded && $arCityLang) {
							$arLocation = LocationTable::getList(array(
								"order" => array(
									"DEPTH_LEVEL" => "ASC",
								),
								"filter" => array(
									//"PARENT_ID" => $arRegion['COUNTRY_LOCATION_ID'],
									"NAME.NAME" => $arCityLang['NAME'],
									">=LEFT_MARGIN" => $arRegionLocation['LEFT_MARGIN'],
									"<=RIGHT_MARGIN" => $arRegionLocation['RIGHT_MARGIN'],
									"TYPE_ID" => array($arTypeCity['ID'], $arTypeVillage['ID']),
								),
								"select" => array("ID"),
							))->fetch();
							if ($arLocation) {
								$bFinded = true;
							}
						}
						if (!$bFinded) {
							$arLocation = LocationTable::getList(array(
								"order" => array(
									"DEPTH_LEVEL" => "ASC",
								),
								"filter" => array(
									//"PARENT_ID" => $arRegion['COUNTRY_LOCATION_ID'],
									"%NAME.NAME" => $arCity['NAME'],
									">=LEFT_MARGIN" => $arRegionLocation['LEFT_MARGIN'],
									"<=RIGHT_MARGIN" => $arRegionLocation['RIGHT_MARGIN'],
									"TYPE_ID" => array($arTypeCity['ID'], $arTypeVillage['ID']),
								),
								"select" => array("ID"),
							))->fetch();
							if ($arLocation) {
								$bFinded = true;
							}
						}
						if (!$bFinded && $arCityLang) {
							$arLocation = LocationTable::getList(array(
								"order" => array(
									"DEPTH_LEVEL" => "ASC",
								),
								"filter" => array(
									//"PARENT_ID" => $arRegion['COUNTRY_LOCATION_ID'],
									"%NAME.NAME" => $arCityLang['NAME'],
									">=LEFT_MARGIN" => $arRegionLocation['LEFT_MARGIN'],
									"<=RIGHT_MARGIN" => $arRegionLocation['RIGHT_MARGIN'],
									"TYPE_ID" => array($arTypeCity['ID'], $arTypeVillage['ID']),
								),
								"select" => array("ID"),
							))->fetch();
							if ($arLocation) {
								$bFinded = true;
							}
						}
						if (!$bFinded) {
							$arLocation = LocationTable::getList(array(
								"order" => array(
									"DEPTH_LEVEL" => "ASC",
								),
								"filter" => array(
									//"PARENT_ID" => $arRegion['COUNTRY_LOCATION_ID'],
									"NAME.NAME" => $this->doReplaceNameCity($arCity['NAME']),
									">=LEFT_MARGIN" => $arRegionLocation['LEFT_MARGIN'],
									"<=RIGHT_MARGIN" => $arRegionLocation['RIGHT_MARGIN'],
									"TYPE_ID" => array($arTypeCity['ID'], $arTypeVillage['ID']),
								),
								"select" => array("ID"),
							))->fetch();
							if ($arLocation) {
								$bFinded = true;
							}
						}

						if ($arRegionLocation['PARENT_ID'] > 0) {
							if (!$bFinded) {
								$arLocation = LocationTable::getList(array(
									"order" => array(
										"DEPTH_LEVEL" => "ASC",
									),
									"filter" => array(
										"PARENT_ID" => $arRegionLocation['PARENT_ID'],
										"NAME.NAME" => $arCity['NAME'],
										//">=LEFT_MARGIN" => $arRegionLocation['LEFT_MARGIN'],
										//"<=RIGHT_MARGIN" => $arRegionLocation['RIGHT_MARGIN'],
										"TYPE_ID" => array($arTypeCity['ID'], $arTypeVillage['ID']),
									),
									"select" => array("ID"),
								))->fetch();
								if ($arLocation) {
									$bFinded = true;
								}
							}
							if (!$bFinded && $arCityLang) {
								$arLocation = LocationTable::getList(array(
									"order" => array(
										"DEPTH_LEVEL" => "ASC",
									),
									"filter" => array(
										"PARENT_ID" => $arRegionLocation['PARENT_ID'],
										"NAME.NAME" => $arCityLang['NAME'],
										//">=LEFT_MARGIN" => $arRegionLocation['LEFT_MARGIN'],
										//"<=RIGHT_MARGIN" => $arRegionLocation['RIGHT_MARGIN'],
										"TYPE_ID" => array($arTypeCity['ID'], $arTypeVillage['ID']),
									),
									"select" => array("ID"),
								))->fetch();
								if ($arLocation) {
									$bFinded = true;
								}
							}
							if (!$bFinded) {
								$arLocation = LocationTable::getList(array(
									"order" => array(
										"DEPTH_LEVEL" => "ASC",
									),
									"filter" => array(
										"PARENT_ID" => $arRegionLocation['PARENT_ID'],
										"%NAME.NAME" => $arCity['NAME'],
										//">=LEFT_MARGIN" => $arRegionLocation['LEFT_MARGIN'],
										//"<=RIGHT_MARGIN" => $arRegionLocation['RIGHT_MARGIN'],
										"TYPE_ID" => array($arTypeCity['ID'], $arTypeVillage['ID']),
									),
									"select" => array("ID"),
								))->fetch();
								if ($arLocation) {
									$bFinded = true;
								}
							}
							if (!$bFinded && $arCityLang) {
								$arLocation = LocationTable::getList(array(
									"order" => array(
										"DEPTH_LEVEL" => "ASC",
									),
									"filter" => array(
										"PARENT_ID" => $arRegionLocation['PARENT_ID'],
										"%NAME.NAME" => $arCityLang['NAME'],
										//">=LEFT_MARGIN" => $arRegionLocation['LEFT_MARGIN'],
										//"<=RIGHT_MARGIN" => $arRegionLocation['RIGHT_MARGIN'],
										"TYPE_ID" => array($arTypeCity['ID'], $arTypeVillage['ID']),
									),
									"select" => array("ID"),
								))->fetch();
								if ($arLocation) {
									$bFinded = true;
								}
							}
							if (!$bFinded) {
								$arLocation = LocationTable::getList(array(
									"order" => array(
										"DEPTH_LEVEL" => "ASC",
									),
									"filter" => array(
										"PARENT_ID" => $arRegionLocation['PARENT_ID'],
										"NAME.NAME" => $this->doReplaceNameCity($arCity['NAME']),
										//">=LEFT_MARGIN" => $arRegionLocation['LEFT_MARGIN'],
										//"<=RIGHT_MARGIN" => $arRegionLocation['RIGHT_MARGIN'],
										"TYPE_ID" => array($arTypeCity['ID'], $arTypeVillage['ID']),
									),
									"select" => array("ID"),
								))->fetch();
								if ($arLocation) {
									$bFinded = true;
								}
							}
						}
						if ($bFinded) {
							if ($arCity['LOCATION_ID'] != $arLocation['ID']) {
								$dbRes = CityTable::update($arCity['ID'], array(
									"LOCATION_ID" => $arLocation['ID'],
								));
								if (!$dbRes->isSuccess()) {
									$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_STEP10_UPDATE_CITY_LOCATION");
									return false;
								}
							}
						} else {
							$dbRes = CityTable::update($arCity['ID'], array(
								"LOCATION_ID" => '0',
							));
							if (!$dbRes->isSuccess()) {
								$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_STEP10_UPDATE_CITY_LOCATION");
								return false;
							}
						}
					}
				}
				$this->arImportData['STEP_10_NEXT_ID'] = $arCity['ID'];
				if ($this->doEndTimeInterval()) {
					return true;
				}
			}
		}
		$this->NS['STEP'] = 11;
		return true;
	}

	/**
	 * Загрузка местоположений битрикс
	 */
	protected function doStep11()
	{
		if ($this->NS['LOAD_LOCATION'] != "Y") {
			$this->NS['STEP'] = 12;
			return true;
		}
		//$CACHE_REGION = array();
		if (\CModule::IncludeModule("sale")) {
			if (!isset($this->arImportData['STEP11_TOTAL'])) {
				$arCity = RegionTable::getList(array(
					'select' => array('CNT'),
					'runtime' => array(
						new ExpressionField('CNT', 'COUNT(*)'),
					),
				))->fetch();
				$this->arImportData['STEP11_TOTAL'] = $arCity['CNT'];
			}
			$arFilter = array();
			if (isset($this->arImportData['STEP_11_NEXT_ID']) && $this->arImportData['STEP_11_NEXT_ID'] > 0) {
				$arFilter['>ID'] = $this->arImportData['STEP_11_NEXT_ID'];
			}
			$arTypeRegion = TypeTable::getList(array(
				"filter" => array(
					"CODE" => "REGION",
				),
			))->fetch();
			$arTypeSubregion = TypeTable::getList(array(
				"filter" => array(
					"CODE" => "SUBREGION",
				),
			))->fetch();
			$arTypeCity = TypeTable::getList(array(
				"filter" => array(
					"CODE" => "CITY",
				),
			))->fetch();
			$arTypeVillage = TypeTable::getList(array(
				"filter" => array(
					"CODE" => "VILLAGE",
				),
			))->fetch();
			$rRegion = RegionTable::getList(array(
				"order" => array("ID" => "ASC"),
				"filter" => $arFilter,
				"select" => array("ID", "NAME", "LOCATION_ID"),
			));
			while ($arRegion = $rRegion->fetch()) {
				$this->arImportData['STEP11_CNT']++;
				if ($arRegion['LOCATION_ID'] > 0) {
					$arCurrentCityList = array();
					$REGION_FIRST_CITY = false;
					$rCity = CityTable::getList(array(
						"order" => array("ID" => "ASC"),
						"filter" => array("REGION_ID" => $arRegion['ID']),
						"select" => array("ID", "LOCATION_ID", "LAT", "LON")
					));
					while ($arCity = $rCity->fetch()) {
						if (!$REGION_FIRST_CITY) {
							$REGION_FIRST_CITY = $arCity;
						}
						if ($arCity['LOCATION_ID'] > 0) {
							$arCurrentCityList[$arCity['LOCATION_ID']] = $arCity;
						}
					}
					$arRegionLocation = LocationTable::getList(array(
						"filter" => array(
							"ID" => $arRegion['LOCATION_ID'],
						),
					))->fetch();
					if ($arRegionLocation) {
						$rAllChildLocations = LocationTable::getList(array(
							"order" => array("left_margin" => "asc"),
							"filter" => array(
								">LEFT_MARGIN" => $arRegionLocation['LEFT_MARGIN'],
								"<RIGHT_MARGIN" => $arRegionLocation['RIGHT_MARGIN'],
							)
						));
						$CACHE_CHILD_LOCATIONS = array();
						while ($arAllChildLocations = $rAllChildLocations->fetch()) {
							$rNames = LocationTable::getList(array(
								"filter" => array(
									"ID" => $arAllChildLocations['ID']
								),
								"select" => array("NAME.NAME", "NAME.LANGUAGE_ID")
							));
							while ($arNames = $rNames->fetch()) {
								$arAllChildLocations['NAMES'][$arNames['SALE_LOCATION_LOCATION_NAME_LANGUAGE_ID']] = $arNames['SALE_LOCATION_LOCATION_NAME_NAME'];
							}
							$CACHE_CHILD_LOCATIONS[$arAllChildLocations['ID']] = $arAllChildLocations;
						}
						$arInsertLocations = array();
						foreach ($CACHE_CHILD_LOCATIONS as $arLocation) {
							if (!$arCurrentCityList[$arLocation['ID']]) {
								if ($arLocation['TYPE_ID'] === $arTypeCity['ID']) {
									$arInsertLocations[] = array(
										"ID" => $arLocation['ID'],
										"NAMES" => $arLocation['NAMES'],
										"LAT" => $arLocation['LATITUDE'] && $arLocation['LATITUDE'] != 0 ? $arLocation['LATITUDE'] : $REGION_FIRST_CITY['LAT'],
										"LON" => $arLocation['LONGITUDE'] && $arLocation['LONGITUDE'] != 0 ? $arLocation['LONGITUDE'] : $REGION_FIRST_CITY['LON']
									);
								} elseif ($arLocation['TYPE_ID'] === $arTypeVillage['ID'] && $this->NS['LOAD_LOCATION_VILLAGE'] == "Y") {
									$arLoc = array(
										"ID" => $arLocation['ID'],
										"NAMES" => $arLocation['NAMES'],
										"LAT" => $arLocation['LATITUDE'] && $arLocation['LATITUDE'] != 0 ? $arLocation['LATITUDE'] : $REGION_FIRST_CITY['LAT'],
										"LON" => $arLocation['LONGITUDE'] && $arLocation['LONGITUDE'] != 0 ? $arLocation['LONGITUDE'] : $REGION_FIRST_CITY['LON']
									);
									if (isset($CACHE_CHILD_LOCATIONS[$arLocation['PARENT_ID']])) {
										foreach ($CACHE_CHILD_LOCATIONS[$arLocation['PARENT_ID']]['NAMES'] as $lang => $name) {
											if (strlen($name) > 0 && isset($arLoc['NAMES'][$lang])) {
												$arLoc['NAMES'][$lang] .= ' (' . $name . ')';
											}
										}
									}
									$arInsertLocations[] = $arLoc;
								}
							}
						}
						foreach ($arInsertLocations as $arLocation) {
							$arFields = array(
								"EXT_ID" => false,
								"REGION_ID" => $arRegion['ID'],
								"LOCATION_ID" => $arLocation['ID'],
								"NAME" => strlen($arLocation['NAMES']['ru']) > 0 ? $arLocation['NAMES']['ru'] : $arLocation['NAMES']['en'],
								"LANG" => array(
									"en" => $arLocation['NAMES']['en']
								),
								"LAT" => $arLocation['LAT'],
								"LON" => $arLocation['LON'],
								"OKATO" => false
							);
							$dbRes = CityTable::add($arFields);
							if (!$dbRes->isSuccess()) {
								$this->arErrors[] = Loc::getMessage("ammina.regions_ERROR_STEP11_ADD_CITY" . '<br/><pre>' . print_r($arLocation, true) . '</pre>');
								return false;
							}
						}
					}
				}

				$this->arImportData['STEP_11_NEXT_ID'] = $arRegion['ID'];
				if ($this->doEndTimeInterval()) {
					return true;
				}
			}
		}
		$this->NS['STEP'] = 12;
		return true;
	}

	protected function doCheckCountryDB($arFields)
	{
		if (!isset($this->arCacheData['COUNTRY'][$arFields['EXT_ID']])) {
			$arData = CountryTable::getList(array(
				"filter" => array(
					"EXT_ID" => $arFields['EXT_ID'],
				),
			))->fetch();
			if ($arData) {
				$this->arCacheData['COUNTRY'][$arFields['EXT_ID']] = $arData['ID'];
			} else {
				$dbRes = CountryTable::add($arFields);
				if ($dbRes->isSuccess()) {
					$this->arCacheData['COUNTRY'][$arFields['EXT_ID']] = $dbRes->getId();
				} else {
					return false;
				}
			}
		}
		return true;
	}

	protected function doCheckRegionDB($arFields)
	{
		if (!isset($this->arCacheData['REGION'][$arFields['EXT_ID']])) {
			$arData = RegionTable::getList(array(
				"filter" => array(
					"EXT_ID" => $arFields['EXT_ID'],
				),
			))->fetch();
			if ($arData) {
				$this->arCacheData['REGION'][$arFields['EXT_ID']] = $arData['ID'];
			} else {
				$dbRes = RegionTable::add($arFields);
				if ($dbRes->isSuccess()) {
					$this->arCacheData['REGION'][$arFields['EXT_ID']] = $dbRes->getId();
				} else {
					return false;
				}
			}
		}
		return true;
	}

	protected function doCheckCityDB($arFields)
	{
		if (!isset($this->arCacheData['CITY'][$arFields['EXT_ID']])) {
			$arData = CityTable::getList(array(
				"filter" => array(
					"EXT_ID" => $arFields['EXT_ID'],
				),
			))->fetch();
			if ($arData) {
				$this->arCacheData['CITY'][$arFields['EXT_ID']] = $arData['ID'];
			} else {
				$dbRes = CityTable::add($arFields);
				if ($dbRes->isSuccess()) {
					$this->arCacheData['CITY'][$arFields['EXT_ID']] = $dbRes->getId();
				} else {
					return false;
				}
			}
		}
		return true;
	}

	protected function doCheckIPBlock($startIP, $endIP, $cityId)
	{
		if (\COption::GetOptionString("ammina.regions", "not_load_ip_block", "Y") == "Y") {
			return true;
		}
		$arIP1 = explode(".", $startIP);
		$arIP2 = explode(".", $endIP);
		$arFields = array(
			"BLOCK_START_1" => $arIP1[0],
			"BLOCK_START_2" => $arIP1[1],
			"BLOCK_START_3" => $arIP1[2],
			"BLOCK_START_4" => $arIP1[3],
			"BLOCK_END_1" => $arIP2[0],
			"BLOCK_END_2" => $arIP2[1],
			"BLOCK_END_3" => $arIP2[2],
			"BLOCK_END_4" => $arIP2[3],
			"BLOCK_START" => $arIP1[0] * 16777216 + $arIP1[1] * 65536 + $arIP1[2] * 256 + $arIP1[3],
			"BLOCK_END" => $arIP2[0] * 16777216 + $arIP2[1] * 65536 + $arIP2[2] * 256 + $arIP2[3],
			"CITY_ID" => $this->arCacheData['CITY'][$cityId],
		);
		$dbRes = BlockTable::add($arFields);
		if (!$dbRes->isSuccess()) {
			return false;
		}
		return true;
	}

	protected function getNextIpAddr($ip)
	{
		$ar = explode(".", trim($ip));
		$ar[3]++;
		if ($ar[3] > 255) {
			$ar[3] = 0;
			$ar[2]++;
		}
		if ($ar[2] > 255) {
			$ar[2] = 0;
			$ar[1]++;
		}
		if ($ar[1] > 255) {
			$ar[1] = 0;
			$ar[0]++;
		}
		if ($ar[0] > 255) {
			$ar[0] = 0;
		}
		return implode(".", $ar);
	}

	protected function doDeleteDirectory($strDir)
	{
		$arFiles = scandir($strDir);
		foreach ($arFiles as $strFile) {
			if (in_array($strFile, array(".", ".."))) {
				continue;
			}
			$strFullName = $strDir . $strFile;
			if (is_dir($strFullName)) {
				$this->doDeleteDirectory($strFullName . "/");
			} else {
				@unlink($strFullName);
			}
		}
		@rmdir($strDir);
	}

	protected function doReplaceNameRegion($strName)
	{
		/**
		 * @todo Добавить глобальную пользовательскую проверку переименований
		 */
		$arTmp = explode("===", Loc::getMessage("ammina.regions_SYSTEM_REPLACE_REGION"));
		$arData = array();
		foreach ($arTmp as $v) {
			$arV = explode("---", $v);
			$arData[amreg_strtolower($arV[0])] = $arV[1];
		}
		if (isset($arData[amreg_strtolower($strName)])) {
			return $arData[amreg_strtolower($strName)];
		}
		return $strName;
	}

	protected function doReplaceNameCity($strName)
	{

		return $strName;
	}

	protected function doCheckCityData($arData)
	{
		if ($arData['region']['name_ru'] == Loc::getMessage("ammina.regions_SYS_REPLACE_KRIM") || $arData['region']['name_en'] == "Avtonomna Respublika Krym") {
			$arData['country']['name_ru'] = Loc::getMessage("ammina.regions_SYS_REPLACE_KRIM_RUSSIA");
			$arData['country']['name_en'] = "Russia";
			$arData['country']['iso'] = "RU";
			$arCountry = CountryTable::getList(array(
				"filter" => array(
					"CODE" => "RU",
				),
				"select" => array("ID", "CODE", "EXT_ID"),
			))->fetch();
			$arData['country']['id'] = $arCountry['EXT_ID'];
			$arData['region']['iso'] = "RU-CR";
			$arData['region']['name_ru'] = Loc::getMessage("ammina.regions_SYS_REPLACE_KRIM_REGION");
			$arData['region']['name_en'] = "Krym";
		} elseif ($arData['region']['name_ru'] == Loc::getMessage("ammina.regions_SYS_REPLACE_SEVASTOPOL") || $arData['region']['name_en'] == "Sevastopol'" || $arData['region']['name_en'] == "Sevastopol") {
			$arData['country']['name_ru'] = Loc::getMessage("ammina.regions_SYS_REPLACE_SEVASTOPOL_RUSSIA");
			$arData['country']['name_en'] = "Russia";
			$arData['country']['iso'] = "RU";
			$arCountry = CountryTable::getList(array(
				"filter" => array(
					"CODE" => "RU",
				),
				"select" => array("ID", "CODE", "EXT_ID"),
			))->fetch();
			$arData['country']['id'] = $arCountry['EXT_ID'];
			$arData['region']['iso'] = "RU-SE";
			$arData['region']['name_ru'] = Loc::getMessage("ammina.regions_SYS_REPLACE_SEVASTOPOL_REGION");
			$arData['region']['name_en'] = "Sevastopol'";
		}
		return $arData;
	}

	protected function doCheckRegionDataFromCsv($arFields)
	{
		if ($arFields[3] == Loc::getMessage("ammina.regions_SYS_REPLACE_KRIM") || $arFields[4] == "Avtonomna Respublika Krym") {
			$arFields[1] = "RU-CR";
			$arFields[2] = "RU";
			$arFields[3] = Loc::getMessage("ammina.regions_SYS_REPLACE_KRIM_REGION");
			$arFields[4] = "Krym";
			$arFields[6] = "35";
		} elseif ($arFields[3] == Loc::getMessage("ammina.regions_SYS_REPLACE_SEVASTOPOL") || $arFields[4] == "Sevastopol'" || $arFields[4] == "Sevastopol") {
			$arFields[1] = "RU-SE";
			$arFields[2] = "RU";
			$arFields[3] = Loc::getMessage("ammina.regions_SYS_REPLACE_SEVASTOPOL_REGION");
			$arFields[4] = "Sevastopol'";
			$arFields[6] = "35";
		}
		return $arFields;
	}
}
