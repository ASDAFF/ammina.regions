<?php

namespace Ammina\Regions\Helpers\Admin\Blocks;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ContentTypes
{
	public static function getEdit($arItem)
	{
		global $APPLICATION;
		$result = '
			<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table">
				<tbody>
					' . ($arItem['ID'] > 0 ? '<tr>
						<td class="adm-detail-content-cell-l">ID:</td>
						<td class="adm-detail-content-cell-r">' . htmlspecialcharsbx($arItem['ID']) . '</td>
					</tr>' : '') . '
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("AMMINA_REGIONS_FIELD_NAME") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="text" class="adm-bus-input" name="FIELDS[NAME]" maxlength="255" id="FIELD_NAME" value="' . htmlspecialcharsbx($arItem['NAME']) . '" />
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("AMMINA_REGIONS_FIELD_IDENT") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="text" class="adm-bus-input" name="FIELDS[IDENT]" maxlength="255" id="FIELD_IDENT" value="' . htmlspecialcharsbx($arItem['IDENT']) . '" />
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">' . Loc::getMessage("AMMINA_REGIONS_FIELD_CLASS") . ':</td>
						<td class="adm-detail-content-cell-r">
							<input type="text" class="adm-bus-input" name="FIELDS[CLASS]" maxlength="255" id="FIELD_CLASS" value="' . htmlspecialcharsbx($arItem['CLASS']) . '" />
						</td>
					</tr>
				</tbody>
			</table>';

		return $result;
	}
}