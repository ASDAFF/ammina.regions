<?php

namespace Kit\MultiRegions\Helpers\Admin\Blocks;

use Kit\MultiRegions\CityTable;
use Kit\MultiRegions\CountryTable;
use Kit\MultiRegions\RegionTable;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Content
{

	public static function getScripts()
	{
		global $APPLICATION;
		\CJSCore::Init(array("jquery2"));
		\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/kit.multiregions/admin/content.js");
		$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/kit.multiregions.css");
		return '
			<script type="text/javascript">
				$(document).ready(function(){
					$("#FIELD_COUNTRY").kitMultiRegionsAdminBlockContent();
					$("#FIELD_REGION").kitMultiRegionsAdminBlockContent();
					$("#FIELD_CITY").kitMultiRegionsAdminBlockContent();
				});
			</script>
		';
	}

	public static function getEdit($arItem)
	{
		global $APPLICATION;
		$strSelectType = '';
		$rType = \Kit\MultiRegions\ContentTypesTable::getList(array(
			"select" => array("ID", "NAME"),
			"order" => array("NAME" => "ASC"),
		));
		while ($arType = $rType->fetch()) {
			$strSelectType .= '<option value="' . $arType['ID'] . '"' . ($arItem['TYPE_ID'] == $arType['ID'] ? ' selected="selected"' : '') . '>' . htmlspecialcharsbx("[" . $arType['ID'] . "] " . $arType['NAME']) . '</option>';
		}
		$strSelectSite = '<option value=""></option>';
		$b = "SORT";
		$o = "ASC";
		$rSites = \CSite::GetList($b, $o, array());
		while ($arSite = $rSites->Fetch()) {
			$strSelectSite .= '<option value="' . $arSite['LID'] . '"' . ($arSite['LID'] == $arItem['SITE_ID'] ? ' selected="selected"' : '') . '>[' . $arSite['LID'] . '] ' . htmlspecialcharsbx($arSite['NAME']) . '</option>';
		}
		if ($arItem['COUNTRY_ID'] > 0) {
			$arCountry = CountryTable::getList(array(
				"filter" => array(
					"ID" => $arItem['COUNTRY_ID'],
				),
			))->fetch();
			$arItem['COUNTRY'] = $arCountry['NAME'];
		}
		if ($arItem['REGION_ID'] > 0) {
			$arRegion = RegionTable::getList(array(
				"filter" => array(
					"ID" => $arItem['REGION_ID'],
				),
				"select" => array(
					"*", "COUNTRY_NAME" => "COUNTRY.NAME"
				),
			))->fetch();
			$arItem['REGION'] = $arRegion['COUNTRY_NAME'] . ", " . $arRegion['NAME'];
		}
		if ($arItem['CITY_ID'] > 0) {
			$arCity = CityTable::getList(array(
				"filter" => array(
					"ID" => $arItem['CITY_ID'],
				),
				"select" => array(
					"*", "COUNTRY_NAME" => "REGION.COUNTRY.NAME", "REGION_NAME" => "REGION.NAME"
				),
			))->fetch();
			$arItem['CITY'] = $arCity['COUNTRY_NAME'] . ", " . $arCity['REGION_NAME'] . ", " . $arCity['NAME'];
		}
		$strSelectDomain = '<option value="">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_DOMAIN_ID_SELECT") . '</option>';
		$rDomain = \Kit\MultiRegions\DomainTable::getList(array(
			"select" => array("ID", "NAME", "DOMAIN"),
			"order" => array("NAME" => "ASC", "DOMAIN" => "ASC"),
		));
		while ($arDomain = $rDomain->fetch()) {
			$strSelectDomain .= '<option value="' . $arDomain['ID'] . '"' . ($arItem['DOMAIN_ID'] == $arDomain['ID'] ? ' selected="selected"' : '') . '>' . htmlspecialcharsbx("[" . $arDomain['ID'] . "] " . $arDomain['NAME'] . " (" . $arDomain['DOMAIN'] . ")") . '</option>';
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
		$strContentLangEdit = '';
		foreach ($arExternalLang as $k => $v) {
			$strContentLangEdit .= '<tr><td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_CONTENT") . " (" . htmlspecialchars($v) . '):</td><td class="adm-detail-content-cell-r"><textarea class="adm-bus-textarea" name="FIELDS[CONTENT_LANG][' . $k . ']" id="FIELD_CONTENT_LANG_' . $k . '">' . htmlspecialcharsbx($arItem['CONTENT_LANG'][$k]) . '</textarea></td></tr>';
		}
		$result = '
			<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table">
				<tbody>
					' . ($arItem['ID'] > 0 ? '<tr>
						<td class="adm-detail-content-cell-l">ID:</td>
						<td class="adm-detail-content-cell-r">' . htmlspecialcharsbx($arItem['ID']) . '</td>
					</tr>' : '') . '
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_SITE_ID") . ':</td>
						<td class="adm-detail-content-cell-r">
							<select class="adm-bus-select" name="FIELDS[SITE_ID]" id="FIELD_SITE_ID">' . $strSelectSite . '</select>
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_TYPE_ID") . ':</td>
						<td class="adm-detail-content-cell-r">
							<select class="adm-bus-select" name="FIELDS[TYPE_ID]" id="FIELD_TYPE_ID">' . $strSelectType . '</select>
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_COUNTRY_ID") . ':</td>
						<td class="adm-detail-content-cell-r">
							<div class="bammultiregionsadm-area-item">
								<input type="text" class="adm-bus-input" maxlength="255" id="FIELD_COUNTRY" value="' . htmlspecialcharsbx($arItem['COUNTRY']) . '" data-action="country" data-result-id="FIELD_COUNTRY_ID" autocomplete="off" />
								<input type="hidden" name="FIELDS[COUNTRY_ID]" id="FIELD_COUNTRY_ID" value="' . htmlspecialcharsbx($arItem['COUNTRY_ID']) . '" />
							</div>
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_REGION_ID") . ':</td>
						<td class="adm-detail-content-cell-r">
							<div class="bammultiregionsadm-area-item">
								<input type="text" class="adm-bus-input" maxlength="255" id="FIELD_REGION" value="' . htmlspecialcharsbx($arItem['REGION']) . '" data-action="region" data-result-id="FIELD_REGION_ID" autocomplete="off" />
								<input type="hidden" name="FIELDS[REGION_ID]" id="FIELD_REGION_ID" value="' . htmlspecialcharsbx($arItem['REGION_ID']) . '" />
							</div>
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_CITY_ID") . ':</td>
						<td class="adm-detail-content-cell-r">
							<div class="bammultiregionsadm-area-item">
								<input type="text" class="adm-bus-input" maxlength="255" id="FIELD_CITY" value="' . htmlspecialcharsbx($arItem['CITY']) . '" data-action="city" data-result-id="FIELD_CITY_ID" autocomplete="off" />
								<input type="hidden" name="FIELDS[CITY_ID]" id="FIELD_CITY_ID" value="' . htmlspecialcharsbx($arItem['CITY_ID']) . '" />
							</div>
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l" width="40%">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_DOMAIN_ID") . ':</td>
						<td class="adm-detail-content-cell-r">
							<select class="adm-bus-select" name="FIELDS[DOMAIN_ID]" id="FIELD_DOMAIN_ID">' . $strSelectDomain . '</select>
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_ACTIVE") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="hidden" name="FIELDS[ACTIVE]" value="N"/>
							<input type="checkbox" class="adm-bus-input" name="FIELDS[ACTIVE]" id="FIELD_ACTIVE" value="Y"' . ($arItem['ACTIVE'] == "Y" ? ' checked="checked"' : '') . ' />
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_CONTENT") . ':</td>
						<td class="adm-detail-content-cell-r">
							<textarea class="adm-bus-textarea" name="FIELDS[CONTENT]" id="FIELD_CONTENT">' . htmlspecialcharsbx($arItem['CONTENT']) . '</textarea>
						</td>
					</tr>
					' . $strContentLangEdit . '
				</tbody>
			</table>';

		return $result;
	}
}