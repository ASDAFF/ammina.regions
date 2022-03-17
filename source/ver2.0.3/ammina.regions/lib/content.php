<?

namespace Ammina\Regions;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\EventResult;
use Bitrix\Main\ORM\Event;

class ContentTable extends DataManager
{
	public static function getTableName()
	{
		return 'am_regions_content';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'SITE_ID' => array(
				'data_type' => 'string',
			),
			'SITE' => array(
				'data_type' => '\Bitrix\Main\Site',
				'reference' => array('=this.SITE_ID' => 'ref.LID'),
			),
			'TYPE_ID' => array(
				'data_type' => 'integer',
			),
			'TYPE' => array(
				'data_type' => '\Ammina\Regions\ContentTypes',
				'reference' => array('=this.TYPE_ID' => 'ref.ID'),
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
			'DOMAIN_ID' => array(
				'data_type' => 'integer',
			),
			'DOMAIN' => array(
				'data_type' => '\Ammina\Regions\Domain',
				'reference' => array('=this.DOMAIN_ID' => 'ref.ID'),
			),
			"ACTIVE" => array(
				'data_type' => 'enum',
				'values' => array('N', 'Y'),
			),
			'CONTENT' => array(
				'data_type' => 'string',
			),
			'CONTENT_LANG' => array(
				'data_type' => 'string',
			),
			'CONTENT_EXT' => array(
				'data_type' => 'string',
			),
		);

		return $fieldsMap;
	}

	public static function onBeforeUpdate(Event $event)
	{
		$result = new EventResult();
		$data = $event->getParameter("fields");
		$arUpdateFields = false;
		if (isset($data['CONTENT_EXT'])) {
			foreach ($data['CONTENT_EXT'] as $k => $v) {
				if ($v == -1) {
					unset($data['CONTENT_EXT'][$k]);
				}
			}
			$data['CONTENT_EXT'] = array_values($data['CONTENT_EXT']);
			$arUpdateFields['CONTENT_EXT'] = serialize($data['CONTENT_EXT']);
		}
		if (isset($data['CONTENT_LANG'])) {
			$arUpdateFields['CONTENT_LANG'] = serialize($data['CONTENT_LANG']);
		}

		if (is_array($arUpdateFields)) {
			$result->modifyFields($arUpdateFields);
		}
		return $result;
	}

	public static function onBeforeAdd(Event $event)
	{
		$result = new EventResult();
		$data = $event->getParameter("fields");
		$arUpdateFields = false;
		if (isset($data['CONTENT_EXT'])) {
			foreach ($data['CONTENT_EXT'] as $k => $v) {
				if ($v == -1) {
					unset($data['CONTENT_EXT'][$k]);
				}
			}
			$data['CONTENT_EXT'] = array_values($data['CONTENT_EXT']);
			$arUpdateFields['CONTENT_EXT'] = serialize($data['CONTENT_EXT']);
		}
		if (isset($data['CONTENT_LANG'])) {
			$arUpdateFields['CONTENT_LANG'] = serialize($data['CONTENT_LANG']);
		}

		if (is_array($arUpdateFields)) {
			$result->modifyFields($arUpdateFields);
		}
		return $result;
	}

	public static function getList(array $parameters = array())
	{
		$result = parent::getList($parameters);
		$result->setSerializedFields(array("CONTENT_EXT", "CONTENT_LANG"));
		return $result;
	}
}