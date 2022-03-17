<?php

namespace Kit\MultiRegions\Helpers\Admin\Blocks;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CountryMap
{
	public static function getEdit($arItem)
	{
		global $APPLICATION;
		ob_start();
		$arInfo = array(
			"yandex_lat" => $arItem['LAT'],
			"yandex_lon" => $arItem['LON'],
			"yandex_scale" => 4,
			"PLACEMARKS" => array(
				array(
					"LAT" => $arItem['LAT'],
					"LON" => $arItem['LON'],
					"TEXT" => $arItem['NAME'],
				),
			),
		);
		$APPLICATION->IncludeComponent(
			"bitrix:map.yandex.view",
			"",
			Array(
				"CONTROLS" => array("ZOOM", "MINIMAP", "TYPECONTROL", "SCALELINE"),
				"INIT_MAP_TYPE" => "MAP",
				"MAP_DATA" => serialize($arInfo),
				"MAP_HEIGHT" => "500",
				"MAP_ID" => "",
				"MAP_WIDTH" => "100%",
				"OPTIONS" => array("ENABLE_DBLCLICK_ZOOM", "ENABLE_DRAGGING"),
			)
		);
		$strMap = ob_get_contents();
		ob_end_clean();
		$result = '
			<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table">
				<tbody>
					<tr>
						<td colspan="2">' . $strMap . '</td>
					</tr>
				</tbody>
			</table>';

		return $result;
	}
}