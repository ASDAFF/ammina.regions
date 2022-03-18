<?

namespace Ammina\Regions;

use Bitrix\Main\ORM\Data\DataManager;

class CountryLangTable extends DataManager
{

	public static function getTableName()
	{
		return 'am_regions_country_lang';
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
			'LID' => array(
				'data_type' => 'string',
			),
			'NAME' => array(
				'data_type' => 'string',
			),
		);

		return $fieldsMap;
	}

	public static function getLangNames($countryId, $lang = LANGUAGE_ID)
	{
		$arAllNames = array();
		$arCountryName = CountryTable::getList(array(
			"filter" => array(
				"ID" => $countryId
			),
			"select" => array(
				"ID", "NAME"
			)
		))->fetch();
		if ($arCountryName) {
			$arAllNames['ru'] = $arCountryName['NAME'];
		}
		$rNames = self::getList(array(
			"filter" => array(
				"COUNTRY_ID" => $countryId
			),
			"select" => array("LID", "NAME")
		));
		while ($arName = $rNames->fetch()) {
			$arAllNames[$arName['LID']] = $arName['NAME'];
		}
		return \CAmminaRegions::getListLangNames($arAllNames, $lang);
	}
}
