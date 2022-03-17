<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

$this->setFrameMode(true);
?>

<div class="bam-multiregions">
	<?= Loc::getMessage("KIT_COMPONENT_MULTIREGIONS_CITY_TITLE") ?>:
	<a href="javascript:void(0)" title="<?= $arResult['FULL_NAME'] ?>"
	   class="bam-multiregions-link"><?= $arResult['CITY_NAME_NO_SUBREGION'] ?></a>
</div>

<div class="bam-multiregions-confirm">
	<?
	if ($arResult['CONFIRM_REQUEST_SHOW']) {
		?>
		<div class="bam-multiregions-confirm-content">
			<div class="bam-multiregions-confirm-title">
				<span><?= Loc::getMessage("KIT_COMPONENT_MULTIREGIONS_CITY_TITLE") ?></span>
				<strong><?= $arResult['CITY_INFO']['CITY']['NAME'] ?></strong>
				(<?= $arResult['FULL_NAME_NO_CITY'] ?>)?
			</div>
			<a href="javascript:void(0)"
			   class="bam-multiregions-confirm-button bam-multiregions-confirm-button-yes"><?= Loc::getMessage("KIT_COMPONENT_MULTIREGIONS_YES") ?></a>
			<a href="javascript:void(0)"
			   class="bam-multiregions-confirm-button bam-multiregions-confirm-button-no"><?= Loc::getMessage("KIT_COMPONENT_MULTIREGIONS_NO") ?></a>
		</div>
		<?
	}
	?>
</div>

<div class="bam-multiregions-popup"></div>

<div class="bam-multiregions-popupbg"></div>

<script type="text/javascript">
	$(document).ready(function () {
		$(".bam-multiregions").kitMultiRegions(<?=CUtil::PhpToJSObject($arResult)?>, <?=CUtil::PhpToJSObject($arParams)?>);
	});
</script>
