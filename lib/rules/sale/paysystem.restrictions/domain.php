<?

namespace Ammina\Regions\Rules\Sale\PaySystemRestrictions;

use Ammina\Regions\DomainTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Services\Base;
use Bitrix\Sale\Internals\Entity;

Loc::loadMessages(__FILE__);

class Domain extends Base\Restriction
{
	public static function onSalePaySystemRestrictionsClassNamesBuildList()
	{
		return new \Bitrix\Main\EventResult(
			\Bitrix\Main\EventResult::SUCCESS,
			array(
				'\Ammina\Regions\Rules\Sale\PaySystemRestrictions\Domain' => '/bitrix/modules/ammina.regions/lib/rules/sale/paysystem.restrictions/domain.php',
			)
		);
	}

	public static function getClassTitle()
	{
		return Loc::getMessage("AMMINA_REGIONS_COMPANY_RULES_DOMAIN_NAME");
	}

	public static function getClassDescription()
	{
		return Loc::getMessage("AMMINA_REGIONS_COMPANY_RULES_DOMAIN_DESCRIPTION");
	}

	public static function check($params, array $restrictionParams, $serviceId = 0)
	{
		if (isset($restrictionParams) && is_array($restrictionParams['DOMAIN']))
			return in_array($params, $restrictionParams['DOMAIN']);

		return true;
	}

	protected static function extractParams(Entity $entity)
	{
		return $GLOBALS['AMMINA_REGIONS']['SYS_CURRENT_DOMAIN_ID'];
	}

	public static function getParamsStructure($entityId = 0)
	{
		$arDomainList = array();
		$rDomain = DomainTable::getList(array(
			"select" => array("ID", "NAME", "DOMAIN"),
			"order" => array("NAME" => "ASC", "DOMAIN" => "ASC"),
		));
		while ($arDomain = $rDomain->fetch()) {
			$arDomainList[$arDomain['ID']] = "[" . $arDomain['ID'] . "] " . $arDomain['NAME'] . " (" . $arDomain['DOMAIN'] . ")";
		}
		return array(
			"DOMAIN" => array(
				"TYPE" => "ENUM",
				'MULTIPLE' => 'Y',
				"LABEL" => Loc::getMessage("AMMINA_REGIONS_COMPANY_RULES_DOMAIN_PARAMETERS_DOMAIN"),
				"OPTIONS" => $arDomainList,
			),
		);
	}

}