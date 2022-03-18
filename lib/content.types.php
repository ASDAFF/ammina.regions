<?

namespace Ammina\Regions;

use Bitrix\Main\ORM\Data\DataManager;

class ContentTypesTable extends DataManager
{
	public static function getTableName()
	{
		return 'am_regions_content_types';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'NAME' => array(
				'data_type' => 'string',
			),
			'IDENT' => array(
				'data_type' => 'string',
			),
			'CLASS' => array(
				'data_type' => 'string',
			),
		);

		return $fieldsMap;
	}

}