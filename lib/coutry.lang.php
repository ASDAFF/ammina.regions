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
}
