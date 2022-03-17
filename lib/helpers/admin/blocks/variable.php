<?php

namespace Kit\MultiRegions\Helpers\Admin\Blocks;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Variable
{
	public static function getEdit($arItem)
	{
		global $APPLICATION;
		$arSelectSystem=array(
			"Y" => GetMessage("KIT_MULTIREGIONS_FIELD_IS_SYSTEM_Y"),
			"E" => GetMessage("KIT_MULTIREGIONS_FIELD_IS_SYSTEM_E"),
			"N" => GetMessage("KIT_MULTIREGIONS_FIELD_IS_SYSTEM_N"),
		);
		$result = '
			<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table">
				<tbody>
					' . ($arItem['ID'] > 0 ? '<tr>
						<td class="adm-detail-content-cell-l">ID:</td>
						<td class="adm-detail-content-cell-r">' . htmlspecialcharsbx($arItem['ID']) . '</td>
					</tr>' : '') . '
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_NAME") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="text" class="adm-bus-input" name="FIELDS[NAME]" maxlength="255" id="FIELD_NAME" value="' . htmlspecialcharsbx($arItem['NAME']) . '" />
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_DESCRIPTION") . ':</td>
						<td class="adm-detail-content-cell-r">
							<textarea class="adm-bus-textarea" name="FIELDS[DESCRIPTION]" id="FIELD_DESCRIPTION">' . htmlspecialcharsbx($arItem['DESCRIPTION']) . '</textarea>
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_CODE") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="text" class="adm-bus-input" name="FIELDS[CODE]" maxlength="255" id="FIELD_CODE" value="' . htmlspecialcharsbx($arItem['CODE']) . '" />
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("KIT_MULTIREGIONS_FIELD_IS_SYSTEM") . ':</td>
						<td class="adm-detail-content-cell-r">' . $arSelectSystem[$arItem['IS_SYSTEM']] . '</td>
					</tr>
				</tbody>
			</table>';

		return $result;
	}
}