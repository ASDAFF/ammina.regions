<?

use \Bitrix\Main\Localization\Loc;

?>
<style type="text/css">
	.ammina-modreg-form {
		display: block;
		width: 100%;
		margin: 20px 0;
	}

	.ammina-modreg-wrapper {
		width: 100%;
		float: left;
		margin-right: -290px;
	}

	.ammina-modreg-left {
		margin-right: 290px;
		padding-right: 20px;
	}

	.ammina-modreg-right {
		width: 290px;
		float: right;
	}

	.ammina-modreg-left-text {
		display: block;
		color: #077a00;
		font-size: 14px;
		padding: 10px 0;
	}

	.ammina-modreg-left-block {
		display: block;
		margin: 20px 0 10px 0;
	}

	.ammina-modreg-left-block label {
		display: block;
		color: #686868;
		font-size: 14px;
		padding: 0 0 5px 0;
	}

	.ammina-modreg-left-block input {
		font-size: 16px;
		color: #000000;
		background-color: #ffffff !important;
		border: 1px solid #b6b9be !important;
		box-shadow: inset 0 1px 3px rgba(182, 185, 190, .1) !important;
		-webkit-box-shadow: inset 0 1px 3px rgba(182, 185, 190, .1) !important;
		border-radius: 3px !important;
		width: 400px !important;;
		max-width: 100% !important;;
		min-width: 100px !important;;
		height: 40px !important;;
		padding: 0 20px !important;
	}

	.ammina-modreg-left-block input.error {
		border-color: #ff0000 !important;
	}

	.ammina-modreg-left-block-error {
		display: block;
		color: #ff0000;
		font-size: 11px;
	}

	.ammina-modreg-left-note {
		display: block;
		padding: 10px 0;
		font-size: 11px;
		color: #808080;
	}

	.ammina-modreg-left-text-rules {
		display: block;
		color: #555555;
		font-size: 12px;
		padding: 10px 0 30px 0;
	}

	.ammina-modreg-left-text-rules a {
		color: #333333;
		font-weight: bold;
	}

	.ammina-modreg-left-text-rules a:hover {
		text-decoration: none;
	}

	.ammina-modreg-right-content {
		display: block;
		padding: 20px;
		background-color: #ffffff;
		border: 1px solid #b6b9be !important;
		box-shadow: inset 0 1px 3px rgba(182, 185, 190, .1) !important;
		-webkit-box-shadow: inset 0 1px 3px rgba(182, 185, 190, .1) !important;
		border-radius: 3px !important;
		width: 250px;
	}

	.ammina-modreg-right-text {
		display: block;
		padding: 10px 0 10px 0;
		text-align: center;
		color: #333333;
		font-weight: bold;
		font-size: 16px;
	}

	.ammina-modreg-right-list {
		padding: 0;
		margin: 0 0 0 20px;
		list-style: none;
	}

	.ammina-modreg-right-list li {
		list-style: disclosure-closed;
		color: #077a00;
		padding: 5px 0;
		font-size: 14px;
	}

	.ammina-modreg-right-list li span {
		color: #333333;
	}

</style>
<script type="text/javascript">
	function amInstModResetError(el) {
		$(el).parents(".ammina-modreg-left-block:first").find(".ammina-modreg-left-block-error").remove();
		$(el).removeClass("error");
	}

	function amInstModShowError(el, mess) {
		$(el).parents(".ammina-modreg-left-block:first").append('<div class="ammina-modreg-left-block-error">' + mess + '</div>');
		$(el).addClass("error");
	}

	$(document).ready(function () {
		$("#am-install-mod").click(function () {
			var form = $("form[name='ammina_form_install']");
			var fieldName = $("#AFIELDS_NAME");
			var fieldEmail = $("#AFIELDS_EMAIL");
			var fieldPhone = $("#AFIELDS_PHONE");
			var bIsError = false;
			amInstModResetError(fieldName);
			if (fieldName.val().length < 1) {
				bIsError = true;
				amInstModShowError(fieldName, '<?=Loc::getMessage("ammina.regions_INSTALLFORM_ERROR_NAME")?>');
			}
			amInstModResetError(fieldEmail);
			var regTest = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
			if (fieldEmail.val().length < 1) {
				bIsError = true;
				amInstModShowError(fieldEmail, '<?=Loc::getMessage("ammina.regions_INSTALLFORM_ERROR_EMAIL1")?>');
			} else if (!regTest.test(fieldEmail.val())) {
				bIsError = true;
				amInstModShowError(fieldEmail, '<?=Loc::getMessage("ammina.regions_INSTALLFORM_ERROR_EMAIL2")?>');
			}
			amInstModResetError(fieldPhone);
			if (fieldPhone.val().length < 9) {
				bIsError = true;
				amInstModShowError(fieldPhone, '<?=Loc::getMessage("ammina.regions_INSTALLFORM_ERROR_PHONE")?>');
			}
			if (!bIsError) {
				form.submit();
			}
		});
	});
</script>

<form action="<?= $APPLICATION->GetCurPage() ?>" name="ammina_form_install" method="post">
	<?= bitrix_sessid_post() ?>
	<input type="hidden" name="lang" value="<?= LANG ?>">
	<input type="hidden" name="id" value="ammina.regions">
	<input type="hidden" name="install" value="Y">
	<input type="hidden" name="step" value="2">
	<div class="ammina-modreg-form">
		<div class="ammina-modreg-wrapper">
			<div class="ammina-modreg-left">
				<div class="ammina-modreg-left-text">
					<?= Loc::getMessage("ammina.regions_INSTALLFORM_TITLE1") ?>
				</div>
				<div class="ammina-modreg-left-block">
					<label for="AFIELDS_NAME"><?= Loc::getMessage("ammina.regions_INSTALLFORM_FIELD_NAME") ?>:</label>
					<input type="text" name="AFIELDS[NAME]" id="AFIELDS_NAME" value="<?= $USER->GetFullName() ?>" placeholder="<?= Loc::getMessage("ammina.regions_INSTALLFORM_FIELD_NAME_PLACEHOLDER") ?>"/>
				</div>
				<div class="ammina-modreg-left-block">
					<label for="AFIELDS_EMAIL"><?= Loc::getMessage("ammina.regions_INSTALLFORM_FIELD_EMAIL") ?>:</label>
					<input type="text" name="AFIELDS[EMAIL]" id="AFIELDS_EMAIL" value="<?= $USER->GetEmail() ?>" placeholder="mail@example.ru"/>
				</div>
				<div class="ammina-modreg-left-block">
					<label for="AFIELDS_PHONE"><?= Loc::getMessage("ammina.regions_INSTALLFORM_FIELD_PHONE") ?>:</label>
					<input type="text" name="AFIELDS[PHONE]" id="AFIELDS_PHONE" value="" placeholder="+7 (495) 111-11-11"/>
				</div>
				<div class="ammina-modreg-left-note">
					<?= Loc::getMessage("ammina.regions_INSTALLFORM_FIELDS_REQUIRED") ?>
				</div>
				<div class="ammina-modreg-left-text-rules">
					<?= Loc::getMessage("ammina.regions_INSTALLFORM_RULES") ?>
				</div>
			</div>
		</div>
		<div class="ammina-modreg-right">
			<div class="ammina-modreg-right-content">
				<div class="ammina-modreg-right-text">
					<?= Loc::getMessage("ammina.regions_INSTALLFORM_MANAGER") ?>
				</div>
				<ul class="ammina-modreg-right-list">
					<li><span><?= Loc::getMessage("ammina.regions_INSTALLFORM_MANAGER1") ?></span></li>
					<li><span><?= Loc::getMessage("ammina.regions_INSTALLFORM_MANAGER2") ?></span></li>
					<li><span><?= Loc::getMessage("ammina.regions_INSTALLFORM_MANAGER3") ?></span></li>
					<li><span><?= Loc::getMessage("ammina.regions_INSTALLFORM_MANAGER4") ?></span></li>
					<li><span><?= Loc::getMessage("ammina.regions_INSTALLFORM_MANAGER5") ?></span></li>
				</ul>
			</div>
		</div>
	</div>
	<div style="clear: both;"></div>
	<input type="button" name="inst" value="<?= GetMessage("MOD_INSTALL") ?>" id="am-install-mod"/>
</form>