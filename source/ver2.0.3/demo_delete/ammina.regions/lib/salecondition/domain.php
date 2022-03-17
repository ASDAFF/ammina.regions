<?

use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\EventResult,
	Bitrix\Sale;
use Bitrix\Sale\Discount\CumulativeCalculator;


class CAmminaRegionsSaleCondCtrlDomain extends CCatalogCondCtrlComplex
{
	public static function GetControlDescr()
	{
		$description = parent::GetControlDescr();
		$description['EXECUTE_MODULE'] = 'all';
		$description['SORT'] = 2000;
		return $description;
	}

	public static function GetControlShow($arParams)
	{
		$arControls = static::GetControls();
		$arResult = array(
			'controlgroup' => true,
			'group' => false,
			'label' => Loc::getMessage('ammina.regions_DISCOUNT_GROUP_LABEL'),
			'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
			'children' => array(),
		);
		foreach ($arControls as &$arOneControl) {
			$arResult['children'][] = array(
				'controlId' => $arOneControl['ID'],
				'group' => false,
				'label' => $arOneControl['LABEL'],
				'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
				'control' => array(
					$arOneControl['PREFIX'],
					static::GetLogicAtom($arOneControl['LOGIC']),
					static::GetValueAtom($arOneControl['JS_VALUE']),
				),
			);
		}
		if (isset($arOneControl))
			unset($arOneControl);

		return $arResult;
	}

	public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false)
	{
		$strResult = '';

		if (is_string($arControl)) {
			$arControl = static::GetControls($arControl);
		}
		$boolError = !is_array($arControl);

		$arValues = array();
		if (!$boolError) {
			$arValues = static::Check($arOneCondition, $arOneCondition, $arControl, false);
			$boolError = ($arValues === false);
		}

		if (!$boolError) {
			$arLogic = static::SearchLogic($arValues['logic'], $arControl['LOGIC']);
			if (!isset($arLogic['OP'][$arControl['MULTIPLE']]) || empty($arLogic['OP'][$arControl['MULTIPLE']])) {
				$boolError = true;
			} else {
				$boolMulti = false;
				if (isset($arControl['JS_VALUE']['multiple']) && 'Y' == $arControl['JS_VALUE']['multiple']) {
					$boolMulti = true;
				}
				$currentDomain = '$GLOBALS[\'AMMINA_REGIONS\'][\'SYS_CURRENT_DOMAIN_ID\']';
				if (!$boolMulti) {
					$strResult = str_replace(array('#FIELD#', '#VALUE#'), array($currentDomain, $arValues['value']), $arLogic['OP'][$arControl['MULTIPLE']]);
				} else {
					$arResult = array();
					foreach ($arValues['value'] as &$mxValue) {
						$arResult[] = str_replace(array('#FIELD#', '#VALUE#'), array($currentDomain, $mxValue), $arLogic['OP'][$arControl['MULTIPLE']]);
					}
					if (isset($mxValue))
						unset($mxValue);
					$strResult = '((' . implode(') || (', $arResult) . '))';
				}
			}
		}

		return (!$boolError ? $strResult : false);
	}

	/**
	 * @param bool|string $strControlID
	 *
	 * @return array|bool
	 */
	public static function GetControls($strControlID = false)
	{
		$rDomains = \Ammina\Regions\DomainTable::getList(array(
			"order" => array(
				"NAME" => "ASC",
			),
		));
		$arAllDomains = array();
		while ($arDomain = $rDomains->fetch()) {
			$arAllDomains[$arDomain['ID']] = "[" . $arDomain['ID'] . "] " . $arDomain['NAME'];
		}
		$arControlList = array(
			'CondAMRCmnDomain' => array(
				'ID' => 'CondAMRCmnDomain',
				'EXECUTE_MODULE' => 'all',
				'MODULE_ID' => 'ammina.regions',
				'MODULE_ENTITY' => 'datetime',
				'FIELD' => 'AMR_DOMAIN',
				'FIELD_TYPE' => 'int',
				'MULTIPLE' => 'N',
				'GROUP' => 'N',
				'LABEL' => Loc::getMessage('ammina.regions_DISCOUNT_DOMAIN_LABEL'),
				'PREFIX' => Loc::getMessage('ammina.regions_DISCOUNT_DOMAIN_PREFIX'),
				'LOGIC' => static::GetLogic(array(BT_COND_LOGIC_EQ, BT_COND_LOGIC_NOT_EQ)),
				'JS_VALUE' => array(
					'type' => 'select',
					'multiple' => 'Y',
					'values' => $arAllDomains,
				),
				'PHP_VALUE' => array(
					'VALIDATE' => 'list',
				),
			),
		);

		if (false === $strControlID) {
			return $arControlList;
		} elseif (isset($arControlList[$strControlID])) {
			return $arControlList[$strControlID];
		} else {
			return false;
		}
	}

	public static function GetShowIn($arControls)
	{
		$arControls = array(CSaleCondCtrlGroup::GetControlID());
		return $arControls;
	}
}