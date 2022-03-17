<?

namespace Ammina\Regions;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;

class RegionTable extends DataManager
{

	protected static $regionLangData = false;
	public static $notDeleteLang = false;

	public static function getTableName()
	{
		return 'am_regions_region';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'COUNTRY_ID' => array(
				'data_type' => 'integer',
			),
			'COUNTRY' => array(
				'data_type' => '\Ammina\Regions\Country',
				'reference' => array('=this.COUNTRY_ID' => 'ref.ID'),
			),
			'CODE' => array(
				'data_type' => 'string',
			),
			'NAME' => array(
				'data_type' => 'string',
			),
			'OKATO' => array(
				'data_type' => 'string',
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
			'CITY' => array(
				'data_type' => '\Ammina\Regions\City',
				'reference' => array(
					'=this.ID' => 'ref.REGION_ID'
				)
			),
			'CITY_CNT' => array(
				'data_type' => 'integer',
				'expression' => array(
					'count(DISTINCT %s)',
					'CITY.ID'
				)
			),
			'REGION_LANG' => array(
				'data_type' => '\Ammina\Regions\RegionLang',
				'reference' => array(
					'=this.ID' => 'ref.REGION_ID'
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
		self::$regionLangData = false;
		if (isset($data['LANG'])) {
			self::$regionLangData = $data['LANG'];
		}
	}

	public static function onAfterAdd(Event $event)
	{
		$result = new EventResult();
		$dataLang = self::$regionLangData;
		$id = $event->getParameter("id");
		if ($dataLang !== false) {
			self::_setLangForItem($id, $dataLang);
		}
	}

	public static function onBeforeUpdate(Event $event)
	{
		$data = $event->getParameter("fields");
		self::$regionLangData = false;
		if (isset($data['LANG'])) {
			self::$regionLangData = $data['LANG'];
		}
	}

	public static function onAfterUpdate(Event $event)
	{
		$result = new EventResult();
		$dataLang = self::$regionLangData;
		$id = $event->getParameter("id");
		if ($dataLang !== false) {
			self::_setLangForItem($id['ID'], $dataLang);
		}
	}

	public static function onDelete(Event $event)
	{
		$id = $event->getParameter("id");
		if ($id > 0) {
			$rCurrentLang = RegionLangTable::getList(array(
				"filter" => array(
					"REGION_ID" => $id
				)
			));
			while ($ar = $rCurrentLang->fetch()) {
				RegionLangTable::delete($ar['ID']);
			}
		}
	}

	protected static function _setLangForItem($id, $arLang)
	{
		$arCurrentLang = array();
		if ($id > 0) {
			$rCurrentLang = RegionLangTable::getList(array(
				"filter" => array(
					"REGION_ID" => $id
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
						RegionLangTable::update($arCurrentLang[$arDbLang['LID']], array("NAME" => $arLang[$arDbLang['LID']]));
						unset($arCurrentLang[$arDbLang['LID']]);
					} else {
						RegionLangTable::add(array("REGION_ID" => $id, "LID" => $arDbLang['LID'], "NAME" => $arLang[$arDbLang['LID']]));
					}
				}
			}
			if (!self::$notDeleteLang) {
				foreach ($arCurrentLang as $k => $v) {
					RegionLangTable::delete($v);
				}
			}
		}
	}
}