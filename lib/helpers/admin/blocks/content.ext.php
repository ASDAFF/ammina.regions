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

class ContentExt
{

	public static function getScripts()
	{
		global $APPLICATION;
		\CJSCore::Init(array("jquery2"));
		\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/kit.multiregions/admin/content.ext.js");
		$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/kit.multiregions.css");
		/*
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
		*/
	}

	public static function getEdit($arItem)
	{
		global $APPLICATION;
		$arExternalLang = array();
		$arAllowLangs = explode("|", \COption::GetOptionString("kit.multiregions", "use_lang", ""));
		$rLang = \CLanguage::GetList($b, $o, array());
		while ($arLang = $rLang->Fetch()) {
			if ($arLang['LID'] == "ru" || !in_array($arLang['LID'], $arAllowLangs)) {
				continue;
			}
			$arExternalLang[$arLang['LID']] = "[" . $arLang['LID'] . "] " . $arLang['NAME'];
		}

		$i = 1;
		ob_start();
		if (is_array($arItem['CONTENT_EXT'])) {
			foreach ($arItem['CONTENT_EXT'] as $arCurrentExt) {
				$strContentLangEdit = '';
				foreach ($arExternalLang as $k => $v) {
					$strContentLangEdit .= '<p><strong>' . $v . '</strong>:</p><textarea class="adm-bus-textarea" name="FIELDS[CONTENT_EXT][' . $i . '][CONTENT_LANG][' . $k . ']" id="FIELD_CONTENT_EXT_CONTENT_LANG_' . $i . '_' . $k . '">' . htmlspecialcharsbx($arCurrentExt['CONTENT_LANG'][$k]) . '</textarea>';
				}
				?>
				<tr>
					<td style="text-align:center;">
						<input type="hidden" name="FIELDS[CONTENT_EXT][<?= $i ?>][ACTIVE]" value="N"/>
						<input type="checkbox" name="FIELDS[CONTENT_EXT][<?= $i ?>][ACTIVE]" value="Y"<?= ($arCurrentExt['ACTIVE'] == "Y" ? ' checked="checked"' : '') ?> />
					</td>
					<td>
						<input type="text" class="adm-bus-input" name="FIELDS[CONTENT_EXT][<?= $i ?>][SORT]" id="FIELD_CONTENT_EXT_SORT_<?= $i ?>" value="<?= htmlspecialcharsbx($arCurrentExt['SORT']) ?>" style="max-width:100px;"/>
					</td>
					<td>
						<select class="adm-bus-select kit-multiregions-contentext-selecttype" name="FIELDS[CONTENT_EXT][<?= $i ?>][TYPE_RULE]">
							<option value="TIMEFROMTO"<?= ($arCurrentExt['TYPE_RULE'] == "TIMEFROMTO" ? ' selected="selected"' : '') ?>><?= Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_TYPE_TIMEFROMTO") ?></option>
							<option value="WEEKDAYS"<?= ($arCurrentExt['TYPE_RULE'] == "WEEKDAYS" ? ' selected="selected"' : '') ?>><?= Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_TYPE_WEEKDAYS") ?></option>
							<option value="DATEFROMTO"<?= ($arCurrentExt['TYPE_RULE'] == "DATEFROMTO" ? ' selected="selected"' : '') ?>><?= Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_TYPE_DATEFROMTO") ?></option>
							<option value="PHPCONDITION"<?= ($arCurrentExt['TYPE_RULE'] == "PHPCONDITION" ? ' selected="selected"' : '') ?>><?= Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_TYPE_PHPCONDITION") ?></option>
						</select>
					</td>
					<td>
						<div class="kit-multiregions-contentext-type kit-multiregions-contentext-type-timefromto"<?= ($arCurrentExt['TYPE_RULE'] == "TIMEFROMTO" ? ' style="display: block;"' : '') ?>>
							<input type="text" class="adm-bus-input" name="FIELDS[CONTENT_EXT][<?= $i ?>][TYPE][TIMEFROMTO][FROM]" id="FIELD_CONTENT_EXT_TYPE_TIMEFROMTO_FROM_<?= $i ?>" value="<?= htmlspecialcharsbx($arCurrentExt['TYPE']['TIMEFROMTO']['FROM']) ?>" style="max-width:65px;"/>&nbsp;-&nbsp;<input type="text" class="adm-bus-input" name="FIELDS[CONTENT_EXT][<?= $i ?>][TYPE][TIMEFROMTO][TO]" id="FIELD_CONTENT_EXT_TYPE_TIMEFROMTO_TO_<?= $i ?>" value="<?= htmlspecialcharsbx($arCurrentExt['TYPE']['TIMEFROMTO']['TO']) ?>" style="max-width:65px;"/>
						</div>
						<div class="kit-multiregions-contentext-type kit-multiregions-contentext-type-weekdays"<?= ($arCurrentExt['TYPE_RULE'] == "WEEKDAYS" ? ' style="display: block;"' : '') ?>>
							<label><input type="checkbox" name="FIELDS[CONTENT_EXT][<?= $i ?>][TYPE][WEEKDAYS][]" value="1"<?= (in_array(1, $arCurrentExt['TYPE']['WEEKDAYS']) ? ' checked="checked"' : '') ?> />&nbsp;<?= Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_TYPE_WEEKDAYS_1") ?>
							</label><br/>
							<label><input type="checkbox" name="FIELDS[CONTENT_EXT][<?= $i ?>][TYPE][WEEKDAYS][]" value="2"<?= (in_array(2, $arCurrentExt['TYPE']['WEEKDAYS']) ? ' checked="checked"' : '') ?> />&nbsp;<?= Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_TYPE_WEEKDAYS_2") ?>
							</label><br/>
							<label><input type="checkbox" name="FIELDS[CONTENT_EXT][<?= $i ?>][TYPE][WEEKDAYS][]" value="3"<?= (in_array(3, $arCurrentExt['TYPE']['WEEKDAYS']) ? ' checked="checked"' : '') ?> />&nbsp;<?= Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_TYPE_WEEKDAYS_3") ?>
							</label><br/>
							<label><input type="checkbox" name="FIELDS[CONTENT_EXT][<?= $i ?>][TYPE][WEEKDAYS][]" value="4"<?= (in_array(4, $arCurrentExt['TYPE']['WEEKDAYS']) ? ' checked="checked"' : '') ?> />&nbsp;<?= Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_TYPE_WEEKDAYS_4") ?>
							</label><br/>
							<label><input type="checkbox" name="FIELDS[CONTENT_EXT][<?= $i ?>][TYPE][WEEKDAYS][]" value="5"<?= (in_array(5, $arCurrentExt['TYPE']['WEEKDAYS']) ? ' checked="checked"' : '') ?> />&nbsp;<?= Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_TYPE_WEEKDAYS_5") ?>
							</label><br/>
							<label><input type="checkbox" name="FIELDS[CONTENT_EXT][<?= $i ?>][TYPE][WEEKDAYS][]" value="6"<?= (in_array(6, $arCurrentExt['TYPE']['WEEKDAYS']) ? ' checked="checked"' : '') ?> />&nbsp;<?= Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_TYPE_WEEKDAYS_6") ?>
							</label><br/>
							<label><input type="checkbox" name="FIELDS[CONTENT_EXT][<?= $i ?>][TYPE][WEEKDAYS][]" value="7"<?= (in_array(7, $arCurrentExt['TYPE']['WEEKDAYS']) ? ' checked="checked"' : '') ?> />&nbsp;<?= Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_TYPE_WEEKDAYS_7") ?>
							</label><br/>
						</div>
						<div class="kit-multiregions-contentext-type kit-multiregions-contentext-type-datefromto"<?= ($arCurrentExt['TYPE_RULE'] == "DATEFROMTO" ? ' style="display: block;"' : '') ?>>
							<input type="text" class="adm-bus-input" name="FIELDS[CONTENT_EXT][<?= $i ?>][TYPE][DATEFROMTO][FROM]" id="FIELD_CONTENT_EXT_TYPE_DATEFROMTO_FROM_<?= $i ?>" value="<?= htmlspecialcharsbx($arCurrentExt['TYPE']['DATEFROMTO']['FROM']) ?>" style="max-width:135px;"/>&nbsp;-&nbsp;<input type="text" class="adm-bus-input" name="FIELDS[CONTENT_EXT][<?= $i ?>][TYPE][DATEFROMTO][TO]" id="FIELD_CONTENT_EXT_TYPE_DATEFROMTO_TO_<?= $i ?>" value="<?= htmlspecialcharsbx($arCurrentExt['TYPE']['DATEFROMTO']['TO']) ?>" style="max-width:135px;"/>
						</div>
						<div class="kit-multiregions-contentext-type kit-multiregions-contentext-type-phpcondition"<?= ($arCurrentExt['TYPE_RULE'] == "PHPCONDITION" ? ' style="display: block;"' : '') ?>>
							<textarea class="adm-bus-textarea" name="FIELDS[CONTENT_EXT][<?= $i ?>][TYPE][PHPCONDITION]" id="FIELD_CONTENT_EXT_TYPE_PHPCONDITION_<?= $i ?>"><?= htmlspecialcharsbx($arCurrentExt['TYPE']['PHPCONDITION']) ?></textarea>
						</div>
					</td>
					<td>
						<textarea class="adm-bus-textarea" name="FIELDS[CONTENT_EXT][<?= $i ?>][CONTENT]" id="FIELD_CONTENT_EXT_CONTENT_<?= $i ?>"><?= htmlspecialcharsbx($arCurrentExt['CONTENT']) ?></textarea><?= $strContentLangEdit ?>
					</td>
					<td style="text-align:center;">
						<input type="checkbox" name="FIELDS[CONTENT_EXT][<?= $i ?>][DELETE]" value="Y"/>
					</td>
				</tr>
				<?
				$i++;
			}
		}
		$strCurrentRules = ob_get_contents();
		ob_end_clean();
		ob_start();
		$strContentLangEdit = '';
		foreach ($arExternalLang as $k => $v) {
			$strContentLangEdit .= '<p><strong>' . $v . '</strong>:</p><textarea class="adm-bus-textarea" name="FIELDS[CONTENT_EXT][nNEXTNUM][CONTENT_LANG][' . $k . ']" id="FIELD_CONTENT_EXT_CONTENT_LANG_nNEXTNUM_' . $k . '"></textarea>';
		}
		?>
		<tr>
			<td style="text-align:center;">
				<input type="hidden" name="FIELDS[CONTENT_EXT][nNEXTNUM][ACTIVE]" value="N"/>
				<input type="checkbox" name="FIELDS[CONTENT_EXT][nNEXTNUM][ACTIVE]" value="Y"/>
			</td>
			<td>
				<input type="text" class="adm-bus-input" name="FIELDS[CONTENT_EXT][nNEXTNUM][SORT]" id="FIELD_CONTENT_EXT_SORT_nNEXTNUM" value="100" style="max-width:100px;"/>
			</td>
			<td>
				<select class="adm-bus-select kit-multiregions-contentext-selecttype" name="FIELDS[CONTENT_EXT][nNEXTNUM][TYPE_RULE]">
					<option value="TIMEFROMTO"><?= Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_TYPE_TIMEFROMTO") ?></option>
					<option value="WEEKDAYS"><?= Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_TYPE_WEEKDAYS") ?></option>
					<option value="DATEFROMTO"><?= Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_TYPE_DATEFROMTO") ?></option>
					<option value="PHPCONDITION"><?= Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_TYPE_PHPCONDITION") ?></option>
				</select>
			</td>
			<td>
				<div class="kit-multiregions-contentext-type kit-multiregions-contentext-type-timefromto" style="display: block;">
					<input type="text" class="adm-bus-input" name="FIELDS[CONTENT_EXT][nNEXTNUM][TYPE][TIMEFROMTO][FROM]" id="FIELD_CONTENT_EXT_TYPE_TIMEFROMTO_FROM_nNEXTNUM" value="00:00" style="max-width:65px;"/>&nbsp;-&nbsp;<input type="text" class="adm-bus-input" name="FIELDS[CONTENT_EXT][nNEXTNUM][TYPE][TIMEFROMTO][TO]" id="FIELD_CONTENT_EXT_TYPE_TIMEFROMTO_TO_nNEXTNUM" value="23:59" style="max-width:65px;"/>
				</div>
				<div class="kit-multiregions-contentext-type kit-multiregions-contentext-type-weekdays">
					<label><input type="checkbox" name="FIELDS[CONTENT_EXT][nNEXTNUM][TYPE][WEEKDAYS][]" value="1"/>&nbsp;<?= Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_TYPE_WEEKDAYS_1") ?>
					</label><br/>
					<label><input type="checkbox" name="FIELDS[CONTENT_EXT][nNEXTNUM][TYPE][WEEKDAYS][]" value="2"/>&nbsp;<?= Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_TYPE_WEEKDAYS_2") ?>
					</label><br/>
					<label><input type="checkbox" name="FIELDS[CONTENT_EXT][nNEXTNUM][TYPE][WEEKDAYS][]" value="3"/>&nbsp;<?= Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_TYPE_WEEKDAYS_3") ?>
					</label><br/>
					<label><input type="checkbox" name="FIELDS[CONTENT_EXT][nNEXTNUM][TYPE][WEEKDAYS][]" value="4"/>&nbsp;<?= Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_TYPE_WEEKDAYS_4") ?>
					</label><br/>
					<label><input type="checkbox" name="FIELDS[CONTENT_EXT][nNEXTNUM][TYPE][WEEKDAYS][]" value="5"/>&nbsp;<?= Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_TYPE_WEEKDAYS_5") ?>
					</label><br/>
					<label><input type="checkbox" name="FIELDS[CONTENT_EXT][nNEXTNUM][TYPE][WEEKDAYS][]" value="6"/>&nbsp;<?= Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_TYPE_WEEKDAYS_6") ?>
					</label><br/>
					<label><input type="checkbox" name="FIELDS[CONTENT_EXT][nNEXTNUM][TYPE][WEEKDAYS][]" value="7"/>&nbsp;<?= Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_TYPE_WEEKDAYS_7") ?>
					</label><br/>
				</div>
				<div class="kit-multiregions-contentext-type kit-multiregions-contentext-type-datefromto">
					<input type="text" class="adm-bus-input" name="FIELDS[CONTENT_EXT][nNEXTNUM][TYPE][DATEFROMTO][FROM]" id="FIELD_CONTENT_EXT_TYPE_DATEFROMTO_FROM_nNEXTNUM" value="<?= date("d.m.Y H:i:s") ?>" style="max-width:135px;"/>&nbsp;-&nbsp;<input type="text" class="adm-bus-input" name="FIELDS[CONTENT_EXT][nNEXTNUM][TYPE][DATEFROMTO][TO]" id="FIELD_CONTENT_EXT_TYPE_DATEFROMTO_TO_nNEXTNUM" value="<?= date("d.m.Y H:i:s", time() + 3600 * 24) ?>" style="max-width:135px;"/>
				</div>
				<div class="kit-multiregions-contentext-type kit-multiregions-contentext-type-phpcondition">
					<textarea class="adm-bus-textarea" name="FIELDS[CONTENT_EXT][nNEXTNUM][TYPE][PHPCONDITION]" id="FIELD_CONTENT_EXT_TYPE_PHPCONDITION_nNEXTNUM"></textarea>
				</div>
			</td>
			<td>
				<textarea class="adm-bus-textarea" name="FIELDS[CONTENT_EXT][nNEXTNUM][CONTENT]" id="FIELD_CONTENT_EXT_CONTENT_nNEXTNUM"></textarea><?= $strContentLangEdit ?>
			</td>
			<td style="text-align:center;">
				<input type="checkbox" name="FIELDS[CONTENT_EXT][nNEXTNUM][DELETE]" value="Y"/>
			</td>
		</tr>
		<?
		$newRowContent = ob_get_contents();
		ob_end_clean();
		$result = '
			<table border="0" cellspacing="5" cellpadding="5" width="100%" class="adm-detail-content-table edit-table kit-multiregions-contentext-table">
				<thead>
					<tr class="heading">
						<td style="text-align: left !important;">' . Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_ACTIVE") . '</td>
						<td style="text-align: left !important;">' . Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_SORT") . '</td>
						<td style="text-align: left !important;">' . Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_TYPE") . '</td>
						<td style="text-align: left !important;">' . Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE") . '</td>
						<td style="text-align: left !important;">' . Loc::getMessage("KIT_MULTIREGIONS_HEADER_CONTENT") . '</td>
						<td>' . Loc::getMessage("KIT_MULTIREGIONS_HEADER_RULE_DELETE") . '</td>					
					</tr>				
				</thead>
				<tbody data-next="1">
				' . $strCurrentRules . '				
				</tbody>
				<tfoot>
					<tr>
						<td colspan="6" style="text-align: right; padding-top:20px;">
							<div class="adm-workarea">
								<input type="button" value="' . Loc::getMessage("KIT_MULTIREGIONS_RULE_BUTTON_ADD") . '" title="' . Loc::getMessage("KIT_MULTIREGIONS_RULE_BUTTON_ADD") . '" id="AR_DOMAIN_RULE_ADD" data-html="' . htmlspecialcharsbx($newRowContent) . '" />
							</div>
						</td>
					</tr>
				</tfoot>
			</table>';

		return $result;
	}
}