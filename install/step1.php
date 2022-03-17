<?

use \Bitrix\Main\Localization\Loc;

?>
<script type="text/javascript">
	$(document).ready(function () {
		$("#am-install-mod").click(function () {
            $("form[name='kit_form_install']").submit();
		});
	});
</script>

<form action="<?= $APPLICATION->GetCurPage() ?>" name="kit_form_install" method="post">
	<?= bitrix_sessid_post() ?>
	<input type="hidden" name="lang" value="<?= LANG ?>">
	<input type="hidden" name="id" value="kit.multiregions">
	<input type="hidden" name="install" value="Y">
	<input type="hidden" name="step" value="2">
	<input type="button" name="inst" value="<?= GetMessage("MOD_INSTALL") ?>" id="am-install-mod"/>
</form>