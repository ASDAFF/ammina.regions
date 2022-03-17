<?

namespace Ammina\Regions;

use Bitrix\Main\ORM\Data\DataManager;

class DomainLocationTable extends DataManager
{
	public static function getTableName()
	{
		return 'am_regions_domain_loc';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'DOMAIN_ID' => array(
				'data_type' => 'integer',
			),
			'DOMAIN' => array(
				'data_type' => '\Ammina\Regions\Domain',
				'reference' => array('=this.DOMAIN_ID' => 'ref.ID'),
			),
			'COUNTRY_ID' => array(
				'data_type' => 'integer',
			),
			'COUNTRY' => array(
				'data_type' => '\Ammina\Regions\Country',
				'reference' => array('=this.COUNTRY_ID' => 'ref.ID'),
			),
			'REGION_ID' => array(
				'data_type' => 'integer',
			),
			'REGION' => array(
				'data_type' => '\Ammina\Regions\Region',
				'reference' => array('=this.REGION_ID' => 'ref.ID'),
			),
			'CITY_ID' => array(
				'data_type' => 'integer',
			),
			'CITY' => array(
				'data_type' => '\Ammina\Regions\City',
				'reference' => array('=this.CITY_ID' => 'ref.ID'),
			),
		);

		return $fieldsMap;
	}

}