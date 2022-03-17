<?php

namespace Kit\MultiRegions\Helpers\Admin\Blocks;

use Kit\MultiRegions\CountryLangTable;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Country
{
	public static function getEdit($arItem)
	{
		global $APPLICATION;
		$isSaleModule = \CKitMultiRegions::isIMExists();
		$strSelectContinent = '';
		$arContinents = array("AF", "AN", "AS", "EU", "NA", "OC", "SA");
		foreach ($arContinents as $strContinent) {
			$strSelectContinent .= '<option value="' . $strContinent . '"' . ($arItem['CONTINENT'] == $strContinent ? ' selected="selected"' : '') . '>' . htmlspecialcharsbx(Loc::getMessage("kit.multiregions_CONTINENT_" . $strContinent)) . '</option>';
		}
		$strLocationContent = '';
		if ($isSaleModule) {
			ob_start();
			$APPLICATION->IncludeComponent("bitrix:sale.location.selector." . self::getWidgetAppearance(), "", array(
				"ID" => $arItem['LOCATION_ID'],
				"CODE" => "",
				"INPUT_NAME" => "FIELDS[LOCATION_ID]",
				"PROVIDE_LINK_BY" => "id",
				"SHOW_ADMIN_CONTROLS" => 'Y',
				"SELECT_WHEN_SINGLE" => 'N',
				"FILTER_BY_SITE" => 'N',
				"SHOW_DEFAULT_LOCATIONS" => 'N',
				"SEARCH_BY_PRIMARY" => 'Y',
				"EXCLUDE_SUBTREE" => ""//$arItem['LOCATION_ID'],
			),
				false
			);
			$strLocationContent = ob_get_contents();
			ob_end_clean();
		}
		$arCurrentLang = array();
		if ($arItem['ID'] > 0) {
			$rCurrentLang = CountryLangTable::getList(array(
				"filter" => array(
					"COUNTRY_ID" => $arItem['ID']
				)
			));
			while ($ar = $rCurrentLang->fetch()) {
				$arCurrentLang[$ar['LID']] = $ar['NAME'];
			}
		}
		$arExternalLang = array();
		$arAllowLangs = explode("|", \COption::GetOptionString("kit.multiregions", "use_lang", ""));
		$rLang = \CLanguage::GetList($b, $o, array());
		while ($arLang = $rLang->Fetch()) {
			if ($arLang['LID'] == "ru" || !in_array($arLang['LID'], $arAllowLangs)) {
				continue;
			}
			$arExternalLang[$arLang['LID']] = "[" . $arLang['LID'] . "] " . $arLang['NAME'];
		}
		$strLangEdit = '';
		foreach ($arExternalLang as $k => $v) {
			$strLangEdit .= '<tr>
				<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_NAME") . ' (' . $v . '):</td>
				<td class="adm-detail-content-cell-r">
					<input type="text" class="adm-bus-input" name="FIELDS[LANG][' . $k . ']" maxlength="255" id="FIELD_LANG_' . $k . '" value="' . htmlspecialcharsbx($arCurrentLang[$k]) . '" />
				</td>
			</tr>';
		}

		$result = '
			<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table">
				<tbody>
					' . ($arItem['ID'] > 0 ? '<tr>
						<td class="adm-detail-content-cell-l">ID:</td>
						<td class="adm-detail-content-cell-r">' . htmlspecialcharsbx($arItem['ID']) . '</td>
					</tr>' : '') . '
					<tr>
						<td class="adm-detail-content-cell-l fwb" width="40%">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_CODE") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="text" class="adm-bus-input" name="FIELDS[CODE]" maxlength="2" size="2" id="FIELD_CODE" value="' . htmlspecialcharsbx($arItem['CODE']) . '" />
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_CONTINENT") . ':</td>
						<td class="adm-detail-content-cell-r">
							<select class="adm-bus-select" name="FIELDS[CONTINENT]" id="FIELD_CONTINENT">' . $strSelectContinent . '</select>
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_NAME") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="text" class="adm-bus-input" name="FIELDS[NAME]" maxlength="255" id="FIELD_NAME" value="' . htmlspecialcharsbx($arItem['NAME']) . '" />
						</td>
					</tr>
					' . $strLangEdit . '
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_LAT") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="text" class="adm-bus-input" name="FIELDS[LAT]" id="FIELD_LAT" value="' . htmlspecialcharsbx($arItem['LAT']) . '" />
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_LON") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="text" class="adm-bus-input" name="FIELDS[LON]"id="FIELD_LON" value="' . htmlspecialcharsbx($arItem['LON']) . '" />
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_TIMEZONE") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="text" class="adm-bus-input" name="FIELDS[TIMEZONE]"id="FIELD_TIMEZONE" value="' . htmlspecialcharsbx($arItem['TIMEZONE']) . '" />
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_LOCATION_ID") . ':</td>
						<td class="adm-detail-content-cell-r">' . ($strLocationContent ? $strLocationContent : '<input type="text" class="adm-bus-input" name="FIELDS[LOCATION_ID]"id="FIELD_LOCATION_ID" value="' . htmlspecialcharsbx($arItem['LOCATION_ID']) . '" />') . '</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_EXT_ID") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="text" class="adm-bus-input" name="FIELDS[EXT_ID]"id="FIELD_EXT_ID" value="' . htmlspecialcharsbx($arItem['EXT_ID']) . '" disabled="disabled" />
						</td>
					</tr>
				</tbody>
			</table>';

		return $result;
	}

	public static function getWidgetAppearance()
	{
		$appearance = Option::get("sale", "sale_location_selector_appearance");

		if (!amreg_strlen($appearance) || !in_array($appearance, array('search', 'steps')))
			return 'steps';

		return $appearance;
	}
}