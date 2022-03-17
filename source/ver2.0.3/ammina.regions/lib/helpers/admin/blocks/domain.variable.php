<?php

namespace Ammina\Regions\Helpers\Admin\Blocks;

use Ammina\Regions\CityTable;
use Ammina\Regions\CountryTable;
use Ammina\Regions\DomainVariableTable;
use Ammina\Regions\RegionTable;
use Ammina\Regions\VariableTable;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class DomainVariable
{

	public static function getScripts()
	{
		global $APPLICATION;
		\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/ammina.regions/admin/domain.variable.js");
	}

	public static function getEdit($arItem)
	{
		global $APPLICATION;
		$arCurrentVariables = array();
		if ($arItem['ID'] > 0) {
			$rCurrent = DomainVariableTable::getList(array(
				"filter" => array(
					"DOMAIN_ID" => $arItem['ID'],
				),
			));
			while ($arCurrent = $rCurrent->fetch()) {
				$arCurrentVariables[$arCurrent['VARIABLE_ID']] = $arCurrent;
			}
		}
		$arExternalLang = array();
		$arAllowLangs = explode("|", \COption::GetOptionString("ammina.regions", "use_lang", ""));
		$rLang = \CLanguage::GetList($b, $o, array());
		while ($arLang = $rLang->Fetch()) {
			if ($arLang['LID'] == "ru" || !in_array($arLang['LID'], $arAllowLangs)) {
				continue;
			}
			$arExternalLang[$arLang['LID']] = "[" . $arLang['LID'] . "] " . $arLang['NAME'];
		}
		$rVariables = VariableTable::getList(array(
			"filter" => array(),
			"select" => array("*"),
			"order" => array("IS_SYSTEM" => "ASC", "NAME" => "ASC"),
		));
		ob_start();
		while ($arVariable = $rVariables->fetch()) {
			?>
			<tr>
				<td>
					<?= $arVariable['NAME'] ?><br/>
					<small>#AMMINA_REGIONS_<?= $arVariable['CODE'] ?>#</small><br/>
					<small>$GLOBALS['AMMINA_REGIONS']['<?= $arVariable['CODE'] ?>']</small>
				</td>
				<td>
					<?
					if ($arVariable['IS_SYSTEM'] == "Y") {
						if (is_array($arCurrentVariables[$arVariable['ID']]['VALUE'])) {
							foreach ($arCurrentVariables[$arVariable['ID']]['VALUE'] as $k => $v) {
								?>
								<textarea name="FIELDS[VARIABLES][<?= $arVariable['ID'] ?>][VALUE][]" rows="1" id="FIELD_VARIABLES_<?= $arVariable['ID'] ?>_<?= intval($k) ?>" class="adm-bus-textarea" disabled="disabled" style="height:26px;"><?= htmlspecialcharsbx($v) ?></textarea>
								<br/>
								<?
							}
						} else {
							?>
							<textarea name="FIELDS[VARIABLES][<?= $arVariable['ID'] ?>][VALUE]" rows="1" id="FIELD_VARIABLES_<?= $arVariable['ID'] ?>" class="adm-bus-textarea" disabled="disabled" style="height:26px;"><?= htmlspecialcharsbx($arCurrentVariables[$arVariable['ID']]['VALUE']) ?></textarea>
							<?
						}
					} else {
						if (!is_array($arCurrentVariables[$arVariable['ID']]['VALUE'])) {
							$arCurrentVariables[$arVariable['ID']]['VALUE'] = array($arCurrentVariables[$arVariable['ID']]['VALUE']);
						}
						foreach ($arCurrentVariables[$arVariable['ID']]['VALUE'] as $val) {
							$val = trim($val);
							if (amreg_strlen($val) > 0) {
								?>
								<textarea name="FIELDS[VARIABLES][<?= $arVariable['ID'] ?>][VALUE][]" rows="1" class="adm-bus-textarea"><?= htmlspecialcharsbx($val) ?></textarea>
								<br/>
								<?
							}
						}
						$strHtmlCode = '<textarea name="FIELDS[VARIABLES][' . $arVariable['ID'] . '][VALUE][]" rows="1" class="adm-bus-textarea"></textarea><br/>';
						?>
						<textarea name="FIELDS[VARIABLES][<?= $arVariable['ID'] ?>][VALUE][]" rows="1" class="adm-bus-textarea"></textarea>
						<br/>
						<input type="button" class="AR_DOMAIN_VARIABLE_ADD" value="+" data-html="<?= htmlspecialcharsbx($strHtmlCode) ?>"/>
						<?
					}
					?>
				</td>
				<?
				foreach ($arExternalLang as $langCode => $langName) {
					?>
					<td>
						<?
						if ($arVariable['IS_SYSTEM'] == "Y") {
							if (is_array($arCurrentVariables[$arVariable['ID']]['VALUE_LANG'][$langCode])) {
								foreach ($arCurrentVariables[$arVariable['ID']]['VALUE_LANG'][$langCode] as $k => $v) {
									?>
									<textarea name="FIELDS[VARIABLES][<?= $arVariable['ID'] ?>][VALUE_LANG][<?= $langCode ?>][]" rows="1" id="FIELD_VARIABLES_<?= $arVariable['ID'] ?>_LANG_<?= $langCode ?>_<?= intval($k) ?>" class="adm-bus-textarea" disabled="disabled" style="height:26px;"><?= htmlspecialcharsbx($v) ?></textarea>
									<br/>
									<?
								}
							} else {
								?>
								<textarea name="FIELDS[VARIABLES][<?= $arVariable['ID'] ?>][VALUE_LANG][<?= $langCode ?>]" rows="1" id="FIELD_VARIABLES_<?= $arVariable['ID'] ?>_LANG_<?= $langCode ?>" class="adm-bus-textarea" disabled="disabled" style="height:26px;"><?= htmlspecialcharsbx($arCurrentVariables[$arVariable['ID']]['VALUE_LANG'][$langCode]) ?></textarea>
								<?
							}
						} else {
							if (!is_array($arCurrentVariables[$arVariable['ID']]['VALUE_LANG'][$langCode])) {
								$arCurrentVariables[$arVariable['ID']]['VALUE_LANG'][$langCode] = array($arCurrentVariables[$arVariable['ID']]['VALUE_LANG'][$langCode]);
							}
							foreach ($arCurrentVariables[$arVariable['ID']]['VALUE_LANG'][$langCode] as $val) {
								$val = trim($val);
								if (amreg_strlen($val) > 0) {
									?>
									<textarea name="FIELDS[VARIABLES][<?= $arVariable['ID'] ?>][VALUE_LANG][<?= $langCode ?>][]" rows="1" class="adm-bus-textarea"><?= htmlspecialcharsbx($val) ?></textarea>
									<br/>
									<?
								}
							}
							$strHtmlCode = '<textarea name="FIELDS[VARIABLES][' . $arVariable['ID'] . '][VALUE_LANG][<?=$langCode?>][]" rows="1" class="adm-bus-textarea"></textarea><br/>';
							?>
							<textarea name="FIELDS[VARIABLES][<?= $arVariable['ID'] ?>][VALUE_LANG][<?= $langCode ?>][]" rows="1" class="adm-bus-textarea"></textarea>
							<br/>
							<input type="button" class="AR_DOMAIN_VARIABLE_ADD" value="+" data-html="<?= htmlspecialcharsbx($strHtmlCode) ?>"/>
							<?
						}
						?>
					</td>
					<?
				}
				?>
			</tr>
			<?
		}
		$strCurrentVariables = ob_get_contents();
		ob_end_clean();
		$strLangTitles = '';
		foreach ($arExternalLang as $k => $v) {
			$strLangTitles .= '<td style="text-align: left !important;">' . htmlspecialchars($v) . '</td>';
		}
		$result = '
			<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table">
				<tbody>
					<tr>
						<td class="adm-detail-content-cell-l" width="40%">' . Loc::getMessage("AMMINA_REGIONS_FIELD_VARIABLE_SEPARATOR") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="text" class="adm-bus-input" name="FIELDS[VARIABLE_SEPARATOR]" maxlength="255" id="FIELD_VARIABLE_SEPARATOR" value="' . htmlspecialcharsbx($arItem['VARIABLE_SEPARATOR']) . '" />
						</td>
					</tr>
				</tbody>
			</table>
			<table border="0" cellspacing="5" cellpadding="5" width="100%" class="adm-detail-content-table edit-table ammina-regions-domain-variable-table">
				<thead>
					<tr class="heading">
						<td style="text-align: left !important;">' . Loc::getMessage("AMMINA_REGIONS_HEADER_VARIABLE_NAME") . ', ' . Loc::getMessage("AMMINA_REGIONS_HEADER_VARIABLE_TEMPLATE") . ', ' . Loc::getMessage("AMMINA_REGIONS_HEADER_VARIABLE_GLOBALS") . '</td>
						<td style="text-align: left !important;">' . Loc::getMessage("AMMINA_REGIONS_HEADER_VARIABLE_VALUE") . '</td>
						' . $strLangTitles . '					
					</tr>				
				</thead>
				<tbody data-next="1">
				' . $strCurrentVariables . '
				</tbody>
			</table>';

		return $result;
	}
}
