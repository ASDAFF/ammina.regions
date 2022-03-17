<?

namespace Kit\MultiRegions;

use Bitrix\Main\ORM\Data\DataManager;

class RegionLangTable extends DataManager
{

	public static function getTableName()
	{
		return 'am_multiregions_region_lang';
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
				'data_type' => '\Kit\MultiRegions\Region',
				'reference' => array('=this.REGION_ID' => 'ref.ID'),
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

	public static function getLangNames($regionId, $lang = LANGUAGE_ID)
	{
		$arAllNames = array();
		$arRegionName = RegionTable::getList(array(
			"filter" => array(
				"ID" => $regionId
			),
			"select" => array(
				"ID", "NAME"
			)
		))->fetch();
		if ($arRegionName) {
			$arAllNames['ru'] = $arRegionName['NAME'];
		}
		$rNames = self::getList(array(
			"filter" => array(
				"REGION_ID" => $regionId
			),
			"select" => array("LID", "NAME")
		));
		while ($arName = $rNames->fetch()) {
			$arAllNames[$arName['LID']] = $arName['NAME'];
		}
		return \CKitMultiRegions::getListLangNames($arAllNames, $lang);
	}
}