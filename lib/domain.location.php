<?

namespace Kit\MultiRegions;

use Bitrix\Main\ORM\Data\DataManager;

class DomainLocationTable extends DataManager
{
	public static function getTableName()
	{
		return 'am_multiregions_domain_loc';
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
				'data_type' => '\Kit\MultiRegions\Domain',
				'reference' => array('=this.DOMAIN_ID' => 'ref.ID'),
			),
			'COUNTRY_ID' => array(
				'data_type' => 'integer',
			),
			'COUNTRY' => array(
				'data_type' => '\Kit\MultiRegions\Country',
				'reference' => array('=this.COUNTRY_ID' => 'ref.ID'),
			),
			'REGION_ID' => array(
				'data_type' => 'integer',
			),
			'REGION' => array(
				'data_type' => '\Kit\MultiRegions\Region',
				'reference' => array('=this.REGION_ID' => 'ref.ID'),
			),
			'CITY_ID' => array(
				'data_type' => 'integer',
			),
			'CITY' => array(
				'data_type' => '\Kit\MultiRegions\City',
				'reference' => array('=this.CITY_ID' => 'ref.ID'),
			),
		);

		return $fieldsMap;
	}

}