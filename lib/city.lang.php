<?

namespace Kit\MultiRegions;

use Bitrix\Main\ORM\Data\DataManager;

class CityLangTable extends DataManager
{

	public static function getTableName()
	{
		return 'am_multiregions_city_lang';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'CITY_ID' => array(
				'data_type' => 'integer',
			),
			'CITY' => array(
				'data_type' => '\Kit\MultiRegions\City',
				'reference' => array('=this.CITY_ID' => 'ref.ID'),
			),
			'LID' => array(
				'data_type' => 'string',
			),
			'NAME' => array(
				'data_type' => 'string',
			),
		);

		return $fieldsMap;
	}

	public static function getLangNames($cityId, $lang = LANGUAGE_ID)
	{
		$arAllNames = array();
		$arCityName = CityTable::getList(array(
			"filter" => array(
				"ID" => $cityId
			),
			"select" => array(
				"ID", "NAME"
			)
		))->fetch();
		if ($arCityName) {
			$arAllNames['ru'] = $arCityName['NAME'];
		}
		$rNames = self::getList(array(
			"filter" => array(
				"CITY_ID" => $cityId
			),
			"select" => array("LID", "NAME")
		));
		while ($arName = $rNames->fetch()) {
			$arAllNames[$arName['LID']] = $arName['NAME'];
		}
		return \CKitMultiRegions::getListLangNames($arAllNames, $lang);
	}
}
