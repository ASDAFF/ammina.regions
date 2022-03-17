<?

namespace Kit\MultiRegions;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;

class CountryTable extends DataManager
{

	protected static $countryLangData = false;
	public static $notDeleteLang = false;

	public static function getTableName()
	{
		return 'am_multiregions_country';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'CODE' => array(
				'data_type' => 'string',
			),
			'CONTINENT' => array(
				'data_type' => 'string',
			),
			'NAME' => array(
				'data_type' => 'string',
			),
			'LAT' => array(
				'data_type' => 'float',
			),
			'LON' => array(
				'data_type' => 'float',
			),
			'TIMEZONE' => array(
				'data_type' => 'string',
			),
			'LOCATION_ID' => array(
				'data_type' => 'integer',
			),
			'LOCATION' => array(
				'data_type' => '\Bitrix\Sale\Location\Location',
				'reference' => array('=this.LOCATION_ID' => 'ref.ID'),
			),
			'EXT_ID' => array(
				'data_type' => 'integer',
			),
			'COUNTRY_LANG' => array(
				'data_type' => '\Kit\MultiRegions\CountryLang',
				'reference' => array(
					'=this.ID' => 'ref.COUNTRY_ID'
				)
			),
			'REGION' => array(
				'data_type' => '\Kit\MultiRegions\Region',
				'reference' => array(
					'=this.ID' => 'ref.COUNTRY_ID'
				)
			),
			'REGION_CNT' => array(
				'data_type' => 'integer',
				'expression' => array(
					'count(DISTINCT %s)',
					'REGION.ID'
				)
			),
			'CITY' => array(
				'data_type' => '\Kit\MultiRegions\City',
				'reference' => array(
					'=this.REGION.ID' => 'ref.REGION_ID'
				)
			),
			'CITY_CNT' => array(
				'data_type' => 'integer',
				'expression' => array(
					'count(DISTINCT %s)',
					'CITY.ID'
				)
			),
		);

		return $fieldsMap;
	}

	public static function getList(array $parameters = array())
	{
		$result = parent::getList($parameters);
		/*$result->addFetchDataModifier(function (&$data) use ($parameters) {
			\CKitMultiRegions::langNamesForResult($data, self::$langFields, isset($parameters['select']) ? $parameters['select'] : false);
		});*/
		return $result;
	}

	public static function onBeforeAdd(Event $event)
	{
		$data = $event->getParameter("fields");
		self::$countryLangData = false;
		if (isset($data['LANG'])) {
			self::$countryLangData = $data['LANG'];
		}
	}

	public static function onAfterAdd(Event $event)
	{
		$result = new EventResult();
		$dataLang = self::$countryLangData;
		$id = $event->getParameter("id");
		if ($dataLang !== false) {
			self::_setLangForItem($id, $dataLang);
		}
	}

	public static function onBeforeUpdate(Event $event)
	{
		$data = $event->getParameter("fields");
		self::$countryLangData = false;
		if (isset($data['LANG'])) {
			self::$countryLangData = $data['LANG'];
		}
	}

	public static function onAfterUpdate(Event $event)
	{
		$result = new EventResult();
		$dataLang = self::$countryLangData;
		$id = $event->getParameter("id");
		if ($dataLang !== false) {
			self::_setLangForItem($id['ID'], $dataLang);
		}
	}

	public static function onDelete(Event $event)
	{
		$id = $event->getParameter("id");
		if ($id > 0) {
			$rCurrentLang = CountryLangTable::getList(array(
				"filter" => array(
					"COUNTRY_ID" => $id
				)
			));
			while ($ar = $rCurrentLang->fetch()) {
				CountryLangTable::delete($ar['ID']);
			}
		}
	}

	protected static function _setLangForItem($id, $arLang)
	{
		$arCurrentLang = array();
		if ($id > 0) {
			$rCurrentLang = CountryLangTable::getList(array(
				"filter" => array(
					"COUNTRY_ID" => $id
				)
			));
			while ($ar = $rCurrentLang->fetch()) {
				$arCurrentLang[$ar['LID']] = $ar['ID'];
			}

			$arAllowLangs = explode("|", \COption::GetOptionString("kit.multiregions", "use_lang", ""));
			$rDbLang = \CLanguage::GetList($b, $o, array());
			while ($arDbLang = $rDbLang->Fetch()) {
				if ($arDbLang['LID'] == "ru" || !in_array($arDbLang['LID'], $arAllowLangs)) {
					continue;
				}
				if (isset($arLang[$arDbLang['LID']])) {
					if (isset($arCurrentLang[$arDbLang['LID']])) {
						CountryLangTable::update($arCurrentLang[$arDbLang['LID']], array("NAME" => $arLang[$arDbLang['LID']]));
						unset($arCurrentLang[$arDbLang['LID']]);
					} else {
						CountryLangTable::add(array("COUNTRY_ID" => $id, "LID" => $arDbLang['LID'], "NAME" => $arLang[$arDbLang['LID']]));
					}
				}
			}
			if (!self::$notDeleteLang) {
				foreach ($arCurrentLang as $k => $v) {
					CountryLangTable::delete($v);
				}
			}
		}
	}
}