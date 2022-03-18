<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

$this->setFrameMode($arParams['NO_FRAME_MODE'] === "Y");

if ($arParams['SET_TAG_IDENT'] == "Y") {
	?>
	<<?=$arParams['SET_TAG_TYPE']?><?= (amreg_strlen($arResult['TYPE_IDENT']) > 0 ? ' id="' . $arResult['TYPE_IDENT'] . '"' : '') ?><?= (amreg_strlen($arResult['TYPE_CLASS']) > 0 ? ' class="' . $arResult['TYPE_CLASS'] . '"' : '') ?>><?= $arResult['ACTIVE_CONTENT'] ?></<?=$arParams['SET_TAG_TYPE']?>>
	<?
} else {
	echo $arResult['ACTIVE_CONTENT'];
}