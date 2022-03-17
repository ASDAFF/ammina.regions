<?php

namespace Ammina\Regions\Helpers\Admin\Blocks;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CityLoad
{
	public static function getEdit()
	{
		global $APPLICATION;
		$isSaleModule = \CAmminaRegions::isIMExists();

		$result = '
			<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table">
				<tbody>
					<tr>
						<td class="adm-detail-content-cell-l" width="40%">' . Loc::getMessage("AMMINA_REGIONS_FIELD_LOAD_CONTENT") . ':<br/><small>' . Loc::getMessage("AMMINA_REGIONS_FIELD_LOAD_CONTENT_NOTE") . '</small></td>
						<td class="adm-detail-content-cell-r">
							<textarea class="adm-bus-textarea" name="FIELDS[LOAD_CONTENT]" id="FIELD_LOAD_CONTENT" rows="20" cols="50"></textarea>
						</td>
					</tr>
				</tbody>
			</table>';

		return $result;
	}
}
