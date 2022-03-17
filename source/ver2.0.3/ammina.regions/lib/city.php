<?

namespace Ammina\Regions;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;

class CityTable extends DataManager
{
	protected static $cityLangData = false;
	public static $notDeleteLang = false;

	public static function getTableName()
	{
		return 'am_regions_city';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'REGION_ID' => array(
				'data_type' => 'integer',
			),
			'REGION' => array(
				'data_type' => '\Ammina\Regions\Region',
				'reference' => array('=this.REGION_ID' => 'ref.ID'),
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
			'OKATO' => array(
				'data_type' => 'string',
			),
			'LOCATION_ID' => array(
				'data_type' => 'integer',
			),
			'LOCATION' => array(
				'data_type' => '\Bitrix\Sale\Location\Location',
				'reference' => array('=this.LOCATION_ID' => 'ref.ID'),
			),
			"IS_DEFAULT" => array(
				'data_type' => 'enum',
				'values' => array('N', 'Y'),
			),
			"IS_FAVORITE" => array(
				'data_type' => 'enum',
				'values' => array('N', 'Y'),
			),
			'EXT_ID' => array(
				'data_type' => 'integer',
			),
			'CITY_LANG' => array(
				'data_type' => '\Ammina\Regions\CityLang',
				'reference' => array(
					'=this.ID' => 'ref.CITY_ID'
				)
			),
		);

		return $fieldsMap;
	}

	public static function getList(array $parameters = array())
	{
		$result = parent::getList($parameters);
		/*$result->addFetchDataModifier(function (&$data) use ($parameters) {
			\CAmminaRegions::langNamesForResult($data, self::$langFields, isset($parameters['select']) ? $parameters['select'] : false);
		});*/
		return $result;
	}

	public static function onBeforeAdd(Event $event)
	{
		$data = $event->getParameter("fields");
		self::$cityLangData = false;
		if (isset($data['LANG'])) {
			self::$cityLangData = $data['LANG'];
		}
	}

	public static function onAfterAdd(Event $event)
	{
		$result = new EventResult();
		$dataLang = self::$cityLangData;
		$id = $event->getParameter("id");
		if ($dataLang !== false) {
			self::_setLangForItem($id, $dataLang);
		}
	}

	public static function onBeforeUpdate(Event $event)
	{
		$data = $event->getParameter("fields");
		self::$cityLangData = false;
		if (isset($data['LANG'])) {
			self::$cityLangData = $data['LANG'];
		}
	}

	public static function onAfterUpdate(Event $event)
	{
		$result = new EventResult();
		$dataLang = self::$cityLangData;
		$id = $event->getParameter("id");
		if ($dataLang !== false) {
			self::_setLangForItem($id['ID'], $dataLang);
		}
	}

	public static function onDelete(Event $event)
	{
		$id = $event->getParameter("id");
		if ($id > 0) {
			$rCurrentLang = CityLangTable::getList(array(
				"filter" => array(
					"CITY_ID" => $id
				)
			));
			while ($ar = $rCurrentLang->fetch()) {
				CityLangTable::delete($ar['ID']);
			}
		}
	}

	protected static function _setLangForItem($id, $arLang)
	{
		$arCurrentLang = array();
		if ($id > 0) {
			$rCurrentLang = CityLangTable::getList(array(
				"filter" => array(
					"CITY_ID" => $id
				)
			));
			while ($ar = $rCurrentLang->fetch()) {
				$arCurrentLang[$ar['LID']] = $ar['ID'];
			}

			$arAllowLangs = explode("|", \COption::GetOptionString("ammina.regions", "use_lang", ""));
			$rDbLang = \CLanguage::GetList($b, $o, array());
			while ($arDbLang = $rDbLang->Fetch()) {
				if ($arDbLang['LID'] == "ru" || !in_array($arDbLang['LID'], $arAllowLangs)) {
					continue;
				}
				if (isset($arLang[$arDbLang['LID']])) {
					if (isset($arCurrentLang[$arDbLang['LID']])) {
						CityLangTable::update($arCurrentLang[$arDbLang['LID']], array("NAME" => $arLang[$arDbLang['LID']]));
						unset($arCurrentLang[$arDbLang['LID']]);
					} else {
						CityLangTable::add(array("CITY_ID" => $id, "LID" => $arDbLang['LID'], "NAME" => $arLang[$arDbLang['LID']]));
					}
				}
			}
			if (!self::$notDeleteLang) {
				foreach ($arCurrentLang as $k => $v) {
					CityLangTable::delete($v);
				}
			}
		}
	}
}