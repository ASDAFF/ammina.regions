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

class Domain
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
					$("#FIELD_CITY").kitMultiRegionsAdminBlockContent();
				});
			</script>
		';
	}

	public static function getEdit($arItem)
	{
		global $APPLICATION;
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
		if (!is_array($arItem['PRICES'])) {
			$arItem['PRICES'] = array();
		}
		if (!is_array($arItem['STORES'])) {
			$arItem['STORES'] = array();
		}
		if (\CKitMultiRegions::isIMExists()) {
			$strSelectPrices = "";
			$rPrices = \CCatalogGroup::GetList(array(
				"SORT" => "ASC",
				"NAME_LANG" => "ASC",
			));
			while ($arPrice = $rPrices->Fetch()) {
				$strSelectPrices .= '<option value="' . $arPrice['ID'] . '"' . (in_array($arPrice['ID'], $arItem['PRICES']) ? ' selected="selected"' : '') . '>[' . $arPrice['ID'] . '] ' . htmlspecialcharsbx($arPrice['NAME_LANG']) . '</option>';
			}
			$strSelectStores = "";
			$rStores = \CCatalogStore::GetList(array());
			while ($arStore = $rStores->Fetch()) {
				$strSelectStores .= '<option value="' . $arStore['ID'] . '"' . (in_array($arStore['ID'], $arItem['STORES']) ? ' selected="selected"' : '') . '>[' . $arStore['ID'] . '] ' . htmlspecialcharsbx($arStore['TITLE']) . '</option>';
			}
		}
		$strSelectSites = "";
		$strSelectSitesExt = "";
		$b = "SORT";
		$o = "ASC";
		$rSites = \CSite::GetList($b, $o, array());
		while ($arSite = $rSites->Fetch()) {
			$strSelectSites .= '<option value="' . $arSite['LID'] . '"' . ($arSite['LID'] == $arItem['SITE_ID'] ? ' selected="selected"' : '') . '>[' . $arSite['LID'] . '] ' . htmlspecialcharsbx($arSite['NAME']) . '</option>';
			$strSelectSitesExt .= '<option value="' . $arSite['LID'] . '"' . (in_array($arSite['LID'], $arItem['SITE_EXT']) ? ' selected="selected"' : '') . '>[' . $arSite['LID'] . '] ' . htmlspecialcharsbx($arSite['NAME']) . '</option>';
		}
		$user_name = "";
		if ($arItem['SALE_UID'] > 0) {
			$arUser = \CUser::GetByID($arItem['SALE_UID'])->Fetch();
			$urlToUser = "/bitrix/admin/user_edit.php?ID=" . $arItem['SALE_UID'] . "&lang=" . LANGUAGE_ID;
			$user_name = '[<a href="' . $urlToUser . '">' . $arItem['SALE_UID'] . '</a>] (' . $arUser['LOGIN'] . ') ' . $arUser['NAME'] . " " . $arUser['LAST_NAME'];
		}
		if (\CKitMultiRegions::isIMExists()) {
			$strSelectCompany = '<option value="">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_SALE_COMPANY_ID_SELECT") . '</option>';
			$rCompanies = \Bitrix\Sale\Internals\CompanyTable::getList(array(
				"order" => array(
					"SORT" => "ASC",
					"NAME" => "ASC",
				),
			));
			while ($arCompanies = $rCompanies->fetch()) {
				$strSelectCompany .= '<option value="' . $arCompanies['ID'] . '"' . ($arCompanies['ID'] == $arItem['SALE_COMPANY_ID'] ? ' selected="selected"' : '') . '>[' . $arCompanies['ID'] . '] ' . htmlspecialcharsbx($arCompanies['NAME']) . '</option>';
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
		$strNameLangEdit = '';
		foreach ($arExternalLang as $k => $v) {
			$strNameLangEdit .= '<tr><td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_NAME") . " (" . htmlspecialchars($v) . '):</td><td class="adm-detail-content-cell-r"><input type="text" class="adm-bus-input" name="FIELDS[NAME_LANG][' . $k . ']" maxlength="255" id="FIELD_NAME_LANG_' . $k . '" value="' . htmlspecialcharsbx($arItem['NAME_LANG'][$k]) . '" /></td></tr>';
		}
		$result = '
			<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table">
				<tbody>
					' . ($arItem['ID'] > 0 ? '<tr>
						<td class="adm-detail-content-cell-l">ID:</td>
						<td class="adm-detail-content-cell-r">' . htmlspecialcharsbx($arItem['ID']) . '</td>
					</tr>' : '') . '
					<tr>
						<td class="adm-detail-content-cell-l" style="width:40%;">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_NAME") . ':</td>
						<td class="adm-detail-content-cell-r" style="width:60%;">
							<input type="text" class="adm-bus-input" name="FIELDS[NAME]" maxlength="255" id="FIELD_NAME" value="' . htmlspecialcharsbx($arItem['NAME']) . '" />
						</td>
					</tr>
					' . $strNameLangEdit . '
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_ACTIVE") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="hidden" name="FIELDS[ACTIVE]" value="N"/>
							<input type="checkbox" class="adm-bus-input" name="FIELDS[ACTIVE]" id="FIELD_ACTIVE" value="Y"' . ($arItem['ACTIVE'] == "Y" ? ' checked="checked"' : '') . ' />
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_IS_DEFAULT") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="hidden" name="FIELDS[IS_DEFAULT]" value="N"/>
							<input type="checkbox" class="adm-bus-input" name="FIELDS[IS_DEFAULT]" id="FIELD_IS_DEFAULT" value="Y"' . ($arItem['IS_DEFAULT'] == "Y" ? ' checked="checked"' : '') . ' />
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_DOMAIN") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="text" class="adm-bus-input" name="FIELDS[DOMAIN]" maxlength="255" id="FIELD_DOMAIN" value="' . htmlspecialcharsbx($arItem['DOMAIN']) . '" />
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_PATHCODE") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="text" class="adm-bus-input" name="FIELDS[PATHCODE]" maxlength="255" id="FIELD_PATHCODE" value="' . htmlspecialcharsbx($arItem['PATHCODE']) . '" />
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_ORDER_PREFIX") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="text" class="adm-bus-input" name="FIELDS[ORDER_PREFIX]" maxlength="64" id="FIELD_ORDER_PREFIX" value="' . htmlspecialcharsbx($arItem['ORDER_PREFIX']) . '" />
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_SITE_ID") . ':</td>
						<td class="adm-detail-content-cell-r">
							<select class="adm-bus-select" name="FIELDS[SITE_ID]" id="FIELD_SITE_ID">' . $strSelectSites . '</select>
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_SITE_EXT") . ':</td>
						<td class="adm-detail-content-cell-r">
							<select class="adm-bus-select" name="FIELDS[SITE_EXT][]" id="FIELD_SITE_EXT" multiple="multiple">' . $strSelectSitesExt . '</select>
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
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_NOINDEX") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="hidden" name="FIELDS[NOINDEX]" value="N"/>
							<input type="checkbox" class="adm-bus-input" name="FIELDS[NOINDEX]" id="FIELD_NOINDEX" value="Y"' . ($arItem['NOINDEX'] == "Y" ? ' checked="checked"' : '') . ' />
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_SALE_UID") . ':</td>
						<td class="adm-detail-content-cell-r">
							' . FindUserID("FIELDS[SALE_UID]", $arItem['SALE_UID'], $user_name, 'kit_multiregions_domain_edit_form') . '
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_SALE_COMPANY_ID") . ':</td>
						<td class="adm-detail-content-cell-r">
							<select class="adm-bus-select" name="FIELDS[SALE_COMPANY_ID]" id="FIELD_SALE_COMPANY_ID">' . $strSelectCompany . '</select>
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_SALE_COMPANY_CREATE") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="checkbox" class="adm-bus-input" name="FIELDS[ACTION][SALE_COMPANY_CREATE]" value="Y" />
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_SALE_COMPANY_RESTRICTION") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="checkbox" class="adm-bus-input" name="FIELDS[ACTION][SALE_COMPANY_RESTRICTION]" value="Y" />
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_SALE_COMPANY_GROUPS_CREATE") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="checkbox" class="adm-bus-input" name="FIELDS[ACTION][SALE_COMPANY_GROUPS_CREATE]" value="Y" />
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_SALE_COMPANY_GROUPS_LINK") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="checkbox" class="adm-bus-input" name="FIELDS[ACTION][SALE_COMPANY_GROUPS_LINK]" value="Y" />
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_DEFAULT_EMAIL") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="text" class="adm-bus-input" name="FIELDS[DEFAULT_EMAIL]" maxlength="255" id="FIELD_DEFAULT_EMAIL" value="' . htmlspecialcharsbx($arItem['DEFAULT_EMAIL']) . '" />
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_PRICES") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="hidden" name="FIELDS[PRICES][]" value="-1"/>
							<select class="adm-bus-select" name="FIELDS[PRICES][]" id="FIELD_PRICES" size="10" multiple="multiple">' . $strSelectPrices . '</select>
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_STORES") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="hidden" name="FIELDS[STORES][]" value="-1"/>
							<select class="adm-bus-select" name="FIELDS[STORES][]" id="FIELD_STORES" size="10" multiple="multiple">' . $strSelectStores . '</select>
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_COUNTERS") . ':</td>
						<td class="adm-detail-content-cell-r">
							<textarea class="adm-bus-textarea" name="FIELDS[COUNTERS]" id="FIELD_COUNTERS" style="width: 100%;height: 200px;">' . htmlspecialcharsbx($arItem['COUNTERS']) . '</textarea>
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_HEAD_STRING") . ':</td>
						<td class="adm-detail-content-cell-r">
							<textarea class="adm-bus-textarea" name="FIELDS[HEAD_STRING]" id="FIELD_HEAD_STRING" style="width: 100%;height: 120px;">' . htmlspecialcharsbx($arItem['HEAD_STRING']) . '</textarea>
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_MAKE_SETTINGS_SITEMAP") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="checkbox" class="adm-bus-input" name="FIELDS[ACTION][MAKE_SETTINGS_SITEMAP]" value="Y" />
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_MAKE_ROBOTS_FILE") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="checkbox" class="adm-bus-input" name="FIELDS[ACTION][MAKE_ROBOTS_FILE]" value="Y" />
						</td>
					</tr>
				</tbody>
			</table>';

		return $result;
	}
}