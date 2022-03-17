<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

$this->setFrameMode(true);
?>

<div class="bam-regions">
	<?= Loc::getMessage("AMMINA_COMPONENT_REGIONS_CITY_TITLE") ?>:
	<a href="javascript:void(0)" title="<?= $arResult['FULL_NAME'] ?>"
	   class="bam-regions-link"><?= $arResult['CITY_NAME_NO_SUBREGION'] ?></a>
</div>

<div class="bam-regions-confirm">
	<?
	if ($arResult['CONFIRM_REQUEST_SHOW']) {
		?>
		<div class="bam-regions-confirm-content">
			<div class="bam-regions-confirm-content-arrow"></div>
			<div class="bam-regions-confirm-title">
				<span><?= Loc::getMessage("AMMINA_COMPONENT_REGIONS_CITY_TITLE") ?></span>
				<p><strong><?= $arResult['CITY_INFO']['CITY']['NAME'] ?></strong> (<?= $arResult['FULL_NAME_NO_CITY'] ?>
					)?</p>
			</div>
			<div class="bam-regions-confirm-buttons">
				<a href="javascript:void(0)"
				   class="bam-regions-confirm-button bam-regions-confirm-button-no"><?= Loc::getMessage("AMMINA_COMPONENT_REGIONS_NO") ?></a><a
						href="javascript:void(0)"
						class="bam-regions-confirm-button bam-regions-confirm-button-yes"><?= Loc::getMessage("AMMINA_COMPONENT_REGIONS_YES") ?></a>
			</div>
		</div>
		<?
	}
	?>
</div>

<div class="bam-regions-popup"></div>

<div class="bam-regions-popupbg"></div>
<script type="text/javascript">
	$(document).ready(function () {
		$(".bam-regions").amminaRegions(<?=CUtil::PhpToJSObject($arResult)?>, <?=CUtil::PhpToJSObject($arParams)?>);
	});
</script>
