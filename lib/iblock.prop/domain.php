<?

namespace Ammina\Regions\IblockProp;

use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock\PropertyTable;

Loc::loadMessages(__FILE__);

class Domain
{
	const USER_TYPE = 'AmminaRegionsDomain';

	public static function GetUserTypeDescription()
	{
		return array(
			"PROPERTY_TYPE" => PropertyTable::TYPE_NUMBER,
			"USER_TYPE" => self::USER_TYPE,
			"DESCRIPTION" => Loc::getMessage("AMMINA_REGION_IBPROP_DOMAIN_DESCRIPTION"),
			//"CheckFields" => array(__CLASS__, "CheckFields"),
			//"GetLength" => array(__CLASS__, "GetLength"),
			//"ConvertToDB" => array(__CLASS__, "ConvertToDB"),
			//"ConvertFromDB" => array(__CLASS__, "ConvertFromDB"),
			"GetPropertyFieldHtml" => array(__CLASS__, "GetPropertyFieldHtml"),
			"GetPropertyFieldHtmlMulty" => array(__CLASS__, "GetPropertyFieldHtmlMulty"),
			"GetAdminListViewHTML" => array(__CLASS__, "GetAdminListViewHTML"),
			//"GetPublicViewHTML" => array(__CLASS__, "GetPublicViewHTML"),
			//"GetPublicEditHTML" => array(__CLASS__, "GetPublicEditHTML"),
			"GetSettingsHTML" => array(__CLASS__, "GetSettingsHTML"),
			//"PrepareSettings" => array(__CLASS__, "PrepareSettings"),
		);
	}

	public static function GetPropertyFieldHtml($arProperty, $arValue, $arHTMLControlName)
	{
		global $APPLICATION;
		self::doInitJS();
		ob_start();
		$ident = $arHTMLControlName['VALUE'];
		$ident = str_replace("[", "_", $ident);
		$ident = str_replace("]", "_", $ident);
		$arValue['VALUE_TEXT'] = "";
		if ($arValue['VALUE'] > 0) {
			$arValue['VALUE_TEXT'] = self::getTextValue($arValue['VALUE']);
		}
		?>
		<div class="bamregionsadm-area-item">
			<input type="hidden" name="<?= htmlspecialcharsbx($arHTMLControlName['VALUE']) ?>" id="<?= htmlspecialcharsbx($ident) ?>" value="<?= $arValue['VALUE'] ?>"/>
			<input type="text" name="TEXTFIELD_<?= htmlspecialcharsbx($arHTMLControlName['VALUE']) ?>" id="TEXTFIELD_<?= htmlspecialcharsbx($ident) ?>" size="50" value="<?= $arValue['VALUE_TEXT'] ?>" data-action="domain" data-result-id="<?= htmlspecialcharsbx($ident) ?>" data-min-length="0" data-cnt="30" class="amr-request-field" autocomplete="off" />
		</div>
		<div clas="bamregionsadm-area-item-clear"></div>
		<?
		$strResult = ob_get_contents();
		ob_end_clean();
		return $strResult;
	}

	public static function GetPropertyFieldHtmlMulty($arProperty, $arValue, $arHTMLControlName)
	{
		global $APPLICATION;
		self::doInitJS();
		ob_start();

		foreach ($arValue as $intPropertyValueID => $arOneValue) {
			$strFieldName = $arHTMLControlName['VALUE'] . '[' . $intPropertyValueID . ']';
			$ident = $strFieldName;
			$ident = str_replace("[", "_", $ident);
			$ident = str_replace("]", "_", $ident);
			$arOneValue['VALUE_TEXT'] = "";
			if ($arOneValue['VALUE'] > 0) {
				$arOneValue['VALUE_TEXT'] = self::getTextValue($arOneValue['VALUE']);
			}
			?>
			<div class="bamregionsadm-area-item">
				<input type="hidden" name="<?= htmlspecialcharsbx($strFieldName) ?>" id="<?= htmlspecialcharsbx($ident) ?>" value="<?= $arOneValue['VALUE'] ?>"/>
				<input type="text" name="TEXTFIELD_<?= htmlspecialcharsbx($strFieldName) ?>" id="TEXTFIELD_<?= htmlspecialcharsbx($ident) ?>" size="50" value="<?= $arOneValue['VALUE_TEXT'] ?>" data-action="domain" data-result-id="<?= htmlspecialcharsbx($ident) ?>" data-min-length="0" data-cnt="30" class="amr-request-field" autocomplete="off" />
			</div>
			<div clas="bamregionsadm-area-item-clear"></div>
			<?
		}

		if ((int)$arProperty['MULTIPLE_CNT'] > 0) {
			for ($i = 0; $i < $arProperty['MULTIPLE_CNT']; $i++) {
				$strFieldName = $arHTMLControlName['VALUE'] . '[n' . $i . ']';
				$ident = $strFieldName;
				$ident = str_replace("[", "_", $ident);
				$ident = str_replace("]", "_", $ident);
				?>
				<div class="bamregionsadm-area-item">
					<input type="hidden" name="<?= htmlspecialcharsbx($strFieldName) ?>" id="<?= htmlspecialcharsbx($ident) ?>" value=""/>
					<input type="text" name="TEXTFIELD_<?= htmlspecialcharsbx($strFieldName) ?>" id="TEXTFIELD_<?= htmlspecialcharsbx($ident) ?>" size="50" value="" data-action="domain" data-result-id="<?= htmlspecialcharsbx($ident) ?>" data-min-length="0" data-cnt="30" class="amr-request-field" autocomplete="off" />
				</div>
				<div clas="bamregionsadm-area-item-clear"></div>
				<?
			}
		}
		$strResult = ob_get_contents();
		ob_end_clean();
		return $strResult;

	}

	public static function GetSettingsHTML($arFields, $strHTMLControlName, &$arPropertyFields)
	{
		$arPropertyFields = array(
			'HIDE' => array('ROW_COUNT', 'COL_COUNT', 'WITH_DESCRIPTION'),
		);
	}

	protected static function doInitJS()
	{
		global $APPLICATION;
		\CJSCore::Init(array("jquery2"));
		\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/ammina.regions/admin/queryfield.js");
		$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/ammina.regions.css");
	}

	protected static function getTextValue($ID)
	{
		$strResult = "";
		if ($ID > 0) {
			$arSelect = array(
				"ID",
				"NAME",
				"DOMAIN",
			);
			$arFilter = array(
				"ID" => $ID,
			);
			$arItem = \Ammina\Regions\DomainTable::getList(array(
				"filter" => $arFilter,
				"select" => $arSelect,
			))->fetch();
			$arData = array(
				"ID" => $arItem['ID'],
				"NAME" => $arItem['NAME'],
				"DOMAIN" => $arItem['DOMAIN'],
			);
			$strResult = "[" . $arData['ID'] . "] " . $arData['NAME'] . " (" . $arData['DOMAIN'] . ")";
		}
		return $strResult;
	}

	public static function GetAdminListViewHTML($arProperty, $arValue, $arHTMLControlName)
	{
		$strResult = '';
		$arResult = self::GetPropertyValue($arProperty, $arValue);
		if (is_array($arResult)) {
			$strResult = '<a href="/bitrix/admin/ammina.regions.domain.edit.php?ID=' . $arResult['ID'] . '" title="' . Loc::getMessage("MAIN_EDIT") . '">' . $arResult['NAME'] . '</a>';
		}
		return $strResult;
	}

	protected static function GetPropertyValue($arProperty, $arValue)
	{
		$mxResult = false;
		if ((int)$arValue['VALUE'] > 0) {
			$mxResult = array(
				"ID" => $arValue['VALUE'],
				"NAME" => self::getTextValue($arValue['VALUE']),
			);
		}
		return $mxResult;
	}
}
