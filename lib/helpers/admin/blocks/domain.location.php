<?php

namespace Kit\MultiRegions\Helpers\Admin\Blocks;

use Kit\MultiRegions\CityTable;
use Kit\MultiRegions\CountryTable;
use Kit\MultiRegions\DomainLocationTable;
use Kit\MultiRegions\RegionTable;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class DomainLocation
{

	public static function getScripts()
	{
		global $APPLICATION;
		\CJSCore::Init(array("jquery2"));
		\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/kit.multiregions/admin/domain.location.js");
		$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/kit.multiregions.css");
		/*
		\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/kit.multiregions/admin/content.js");
		*/
		return '
			<script type="text/javascript">
				$(document).ready(function(){
					$(".kit-multiregions-domain-location-table input[data-action=\'country\']").kitMultiRegionsAdminBlockContent();
					$(".kit-multiregions-domain-location-table input[data-action=\'region\']").kitMultiRegionsAdminBlockContent();
					$(".kit-multiregions-domain-location-table input[data-action=\'city\']").kitMultiRegionsAdminBlockContent();
				});
			</script>
		';
	}

	public static function getEdit($arItem)
	{
		global $APPLICATION;
		$strCurrentLocations = '';
		if ($arItem['ID'] > 0) {
			ob_start();
			$rLocations = DomainLocationTable::getList(array(
				"filter" => array(
					"DOMAIN_ID" => $arItem['ID'],
				),
			));
			while ($arLoc = $rLocations->fetch()) {
				if ($arLoc['COUNTRY_ID'] > 0) {
					$arCountry = CountryTable::getList(array(
						"filter" => array(
							"ID" => $arLoc['COUNTRY_ID'],
						),
					))->fetch();
					$arLoc['COUNTRY'] = $arCountry['NAME'];
				}
				if ($arLoc['REGION_ID'] > 0) {
					$arRegion = RegionTable::getList(array(
						"filter" => array(
							"ID" => $arLoc['REGION_ID'],
						),
						"select" => array(
							"*", "COUNTRY_NAME" => "COUNTRY.NAME",
						),
					))->fetch();
					$arLoc['REGION'] = $arRegion['COUNTRY_NAME'] . ", " . $arRegion['NAME'];
				}
				if ($arLoc['CITY_ID'] > 0) {
					$arCity = CityTable::getList(array(
						"filter" => array(
							"ID" => $arLoc['CITY_ID'],
						),
						"select" => array(
							"*", "COUNTRY_NAME" => "REGION.COUNTRY.NAME", "REGION_NAME" => "REGION.NAME"
						),
					))->fetch();
					$arLoc['CITY'] = $arCity['COUNTRY_NAME'] . ", " . $arCity['REGION_NAME'] . ", " . $arCity['NAME'];
				}

				?>
				<tr>
					<td>
						<div class="bammultiregionsadm-area-item">
							<input type="text" class="adm-bus-input" maxlength="255"
								   id="FIELD_LOCATION_COUNTRY_<?= $arLoc['ID'] ?>"
								   value="<?= htmlspecialcharsbx($arLoc['COUNTRY']) ?>" data-action="country"
								   data-result-id="FIELD_LOCATION_COUNTRY_ID_<?= $arLoc['ID'] ?>" autocomplete="off"/>
							<input type="hidden" name="FIELDS[LOCATION][<?= $arLoc['ID'] ?>][COUNTRY_ID]"
								   id="FIELD_LOCATION_COUNTRY_ID_<?= $arLoc['ID'] ?>"
								   value="<?= $arLoc['COUNTRY_ID'] ?>"/>
						</div>
					</td>
					<td>
						<div class="bammultiregionsadm-area-item">
							<input type="text" class="adm-bus-input" maxlength="255"
								   id="FIELD_LOCATION_REGION_<?= $arLoc['ID'] ?>"
								   value="<?= htmlspecialcharsbx($arLoc['REGION']) ?>" data-action="region"
								   data-result-id="FIELD_LOCATION_REGION_ID_<?= $arLoc['ID'] ?>" autocomplete="off"/>
							<input type="hidden" name="FIELDS[LOCATION][<?= $arLoc['ID'] ?>][REGION_ID]"
								   id="FIELD_LOCATION_REGION_ID_<?= $arLoc['ID'] ?>"
								   value="<?= $arLoc['REGION_ID'] ?>"/>
						</div>
					</td>
					<td>
						<div class="bammultiregionsadm-area-item">
							<input type="text" class="adm-bus-input" maxlength="255"
								   id="FIELD_LOCATION_CITY_<?= $arLoc['ID'] ?>"
								   value="<?= htmlspecialcharsbx($arLoc['CITY']) ?>" data-action="city"
								   data-result-id="FIELD_LOCATION_CITY_ID_<?= $arLoc['ID'] ?>" autocomplete="off"/>
							<input type="hidden" name="FIELDS[LOCATION][<?= $arLoc['ID'] ?>][CITY_ID]"
								   id="FIELD_LOCATION_CITY_ID_<?= $arLoc['ID'] ?>" value="<?= $arLoc['CITY_ID'] ?>"/>
						</div>
					</td>
					<td style="text-align:center;">
						<input type="checkbox" name="FIELDS[LOCATION][<?= $arLoc['ID'] ?>][DELETE]" value="Y"/>
					</td>
				</tr>
				<?
			}
			$strCurrentLocations = ob_get_contents();
			ob_end_clean();
		}
		$result = '
			<table border="0" cellspacing="5" cellpadding="5" width="100%" class="adm-detail-content-table edit-table kit-multiregions-domain-location-table">
				<thead>
					<tr class="heading">
						<td style="text-align: left !important;">' . Loc::getMessage("KIT_MULTIREGIONS_HEADER_COUNTRY") . '</td>
						<td style="text-align: left !important;">' . Loc::getMessage("KIT_MULTIREGIONS_HEADER_REGION") . '</td>
						<td style="text-align: left !important;">' . Loc::getMessage("KIT_MULTIREGIONS_HEADER_CITY") . '</td>
						<td>' . Loc::getMessage("KIT_MULTIREGIONS_HEADER_DELETE") . '</td>					
					</tr>				
				</thead>
				<tbody data-next="1">
				' . $strCurrentLocations . '
					<tr>
						<td>
							<div class="bammultiregionsadm-area-item">
								<input type="text" class="adm-bus-input" maxlength="255" id="FIELD_LOCATION_COUNTRY_n0" value="" data-action="country" data-result-id="FIELD_LOCATION_COUNTRY_ID_n0" autocomplete="off" />
								<input type="hidden" name="FIELDS[LOCATION][n0][COUNTRY_ID]" id="FIELD_LOCATION_COUNTRY_ID_n0" value="" />
							</div>
						</td>
						<td>
							<div class="bammultiregionsadm-area-item">
								<input type="text" class="adm-bus-input" maxlength="255" id="FIELD_LOCATION_REGION_n0" value="" data-action="region" data-result-id="FIELD_LOCATION_REGION_ID_n0" autocomplete="off" />
								<input type="hidden" name="FIELDS[LOCATION][n0][REGION_ID]" id="FIELD_LOCATION_REGION_ID_n0" value="" />
							</div>
						</td>
						<td>
							<div class="bammultiregionsadm-area-item">
								<input type="text" class="adm-bus-input" maxlength="255" id="FIELD_LOCATION_CITY_n0" value="" data-action="city" data-result-id="FIELD_LOCATION_CITY_ID_n0" autocomplete="off" />
								<input type="hidden" name="FIELDS[LOCATION][n0][CITY_ID]" id="FIELD_LOCATION_CITY_ID_n0" value="" />
							</div>
						</td>
						<td style="text-align:center;">
							<input type="checkbox" name="FIELDS[LOCATION][n0][DELETE]" value="Y" />
						</td>						
					</tr>				
				</tbody>
				<tfoot>
					<tr>
						<td colspan="4" style="text-align: right; padding-top:20px;">
							<div class="adm-workarea">
								<input type="button" value="' . Loc::getMessage("KIT_MULTIREGIONS_BUTTON_ADD") . '" title="' . Loc::getMessage("KIT_MULTIREGIONS_BUTTON_ADD") . '" id="AR_DOMAIN_LOCATION_ADD" />
							</div>
						</td>
					</tr>
				</tfoot>
			</table>';

		return $result;
	}
}