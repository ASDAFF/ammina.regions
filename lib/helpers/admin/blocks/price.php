<?php

namespace Kit\MultiRegions\Helpers\Admin\Blocks;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Price
{
	public static function getEdit($arItem)
	{
		global $APPLICATION;
		$arAllPrices = array();
		if (\CKitMultiRegions::isIMExists()) {
			$rPrices = \CCatalogGroup::GetList(array(
				"SORT" => "ASC",
				"NAME_LANG" => "ASC",
			));
			while ($arPrice = $rPrices->Fetch()) {
				$arAllPrices[$arPrice['ID']] = '[' . $arPrice['ID'] . '] ' . htmlspecialcharsbx($arPrice['NAME_LANG']);
			}
		}
		$strSelectPriceFrom = '';
		$strSelectPriceTo = '';
		foreach ($arAllPrices as $k => $v) {
			$strSelectPriceFrom .= '<option value="' . $k . '"' . ($k == $arItem['PRICE_FROM_ID'] ? ' selected="selected"' : '') . '>' . htmlspecialcharsbx($v) . '</option>';
			$strSelectPriceTo .= '<option value="' . $k . '"' . ($k == $arItem['PRICE_TO_ID'] ? ' selected="selected"' : '') . '>' . htmlspecialcharsbx($v) . '</option>';
		}
		$arPriceChange = array(
			'NC' => Loc::getMessage("KIT_MULTIREGIONS_FIELD_PRICE_CHANGE_NC"),
			'SU' => Loc::getMessage("KIT_MULTIREGIONS_FIELD_PRICE_CHANGE_SU"),
			'SD' => Loc::getMessage("KIT_MULTIREGIONS_FIELD_PRICE_CHANGE_SD"),
			'PU' => Loc::getMessage("KIT_MULTIREGIONS_FIELD_PRICE_CHANGE_PU"),
			'PD' => Loc::getMessage("KIT_MULTIREGIONS_FIELD_PRICE_CHANGE_PD"),
		);
		$strSelectPriceChange = "";
		foreach ($arPriceChange as $k => $v) {
			$strSelectPriceChange .= '<option value="' . $k . '"' . ($k == $arItem['PRICE_CHANGE'] ? ' selected="selected"' : '') . '>' . htmlspecialcharsbx($v) . '</option>';
		}
		if (\CModule::IncludeModule("currency")) {
			$strSelectCurrency = '<option value="">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_CURRENCY_NOT_CHANGE") . '</option>';
			$rCurrency = \Bitrix\Currency\CurrencyTable::getList(array(
				"order" => array("SORT" => "ASC"),
				"select" => array("*", "LANG_NAME" => "CURRENT_LANG_FORMAT.FULL_NAME"),
			));
			while ($arCurrency = $rCurrency->fetch()) {
				$strSelectCurrency .= '<option value="' . $arCurrency['CURRENCY'] . '"' . ($arCurrency['CURRENCY'] == $arItem['CURRENCY'] ? ' selected="selected"' : '') . '>' . htmlspecialcharsbx('[' . $arCurrency['CURRENCY'] . '] ' . $arCurrency['LANG_NAME']) . '</option>';
			}
		}
		$result = '
			<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table">
				<tbody>
					' . ($arItem['ID'] > 0 ? '<tr>
						<td class="adm-detail-content-cell-l">ID:</td>
						<td class="adm-detail-content-cell-r">' . htmlspecialcharsbx($arItem['ID']) . '</td>
					</tr>' : '') . '
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_ACTIVE") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="hidden" name="FIELDS[ACTIVE]" value="N"/>
							<input type="checkbox" class="adm-bus-input" name="FIELDS[ACTIVE]" id="FIELD_ACTIVE" value="Y"' . ($arItem['ACTIVE'] == "Y" ? ' checked="checked"' : '') . ' />
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_SORT") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="text" class="adm-bus-input" name="FIELDS[SORT]" maxlength="255" id="FIELD_SORT" value="' . htmlspecialcharsbx($arItem['SORT']) . '" />
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_CURRENCY") . ':</td>
						<td class="adm-detail-content-cell-r">
							<select class="adm-bus-select" name="FIELDS[CURRENCY]" id="FIELD_CURRENCY">' . $strSelectCurrency . '</select>
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_PRICE_FROM_ID") . ':</td>
						<td class="adm-detail-content-cell-r">
							<select class="adm-bus-select" name="FIELDS[PRICE_FROM_ID]" id="FIELD_PRICE_FROM_ID">' . $strSelectPriceFrom . '</select>
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_PRICE_TO_ID") . ':</td>
						<td class="adm-detail-content-cell-r">
							<select class="adm-bus-select" name="FIELDS[PRICE_TO_ID]" id="FIELD_PRICE_TO_ID">' . $strSelectPriceTo . '</select>
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_PRICE_CHANGE") . ':</td>
						<td class="adm-detail-content-cell-r">
							<select class="adm-bus-select" name="FIELDS[PRICE_CHANGE]" id="FIELD_PRICE_CHANGE">' . $strSelectPriceChange . '</select>
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_PRICE_CHANGE_VALUE") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="text" class="adm-bus-input" name="FIELDS[PRICE_CHANGE_VALUE]" maxlength="255" id="FIELD_PRICE_CHANGE_VALUE" value="' . htmlspecialcharsbx($arItem['PRICE_CHANGE_VALUE']) . '" />
						</td>
					</tr>
				</tbody>
			</table>';

		return $result;
	}
}