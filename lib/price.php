<?

namespace Ammina\Regions;

use Bitrix\Main\ORM\Data\DataManager;

class PriceTable extends DataManager
{
	public static function getTableName()
	{
		return 'am_regions_price';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			"ACTIVE" => array(
				'data_type' => 'enum',
				'values' => array('N', 'Y'),
			),
			'SORT' => array(
				'data_type' => 'integer',
			),
			'CURRENCY' => array(
				'data_type' => 'string',
			),
			'PRICE_FROM_ID' => array(
				'data_type' => 'integer',
			),
			'PRICE_FROM' => array(
				'data_type' => '\Bitrix\Catalog\Group',
				'reference' => array('=this.PRICE_FROM_ID' => 'ref.ID'),
			),
			'PRICE_TO_ID' => array(
				'data_type' => 'integer',
			),
			'PRICE_TO' => array(
				'data_type' => '\Bitrix\Catalog\Group',
				'reference' => array('=this.PRICE_TO_ID' => 'ref.ID'),
			),
			"PRICE_CHANGE" => array(
				'data_type' => 'enum',
				'values' => array('NC', 'SU', 'SD', 'PU', 'PD'),
			),
			'PRICE_CHANGE_VALUE' => array(
				'data_type' => 'float',
			),
		);

		return $fieldsMap;
	}

}