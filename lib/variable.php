<?

namespace Kit\MultiRegions;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class VariableTable extends DataManager
{
	public static function getTableName()
	{
		return 'am_multiregions_variable';
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
				'validation' => function () {
					return array(
						new LengthValidator(2),
					);
				},
				'title' => Loc::getMessage("KIT_MULTIREGIONS_FIELD_NAME"),
			),
			'DESCRIPTION' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage("KIT_MULTIREGIONS_FIELD_DESCRIPTION"),
			),
			'CODE' => array(
				'data_type' => 'string',
				'validation' => function () {
					return array(
						new LengthValidator(2),
					);
				},
				'title' => Loc::getMessage("KIT_MULTIREGIONS_FIELD_CODE"),
			),
			"IS_SYSTEM" => array(
				'data_type' => 'enum',
				'values' => array('N', 'E', 'Y'),
				'title' => Loc::getMessage("KIT_MULTIREGIONS_FIELD_IS_SYSTEM"),
			),
		);

		return $fieldsMap;
	}

}