<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/*
use Bitrix\Main\Loader;

global $APPLICATION;
$loadCurrency = false;
if (!empty($arResult['CURRENCIES']))
	$loadCurrency = Loader::includeModule('currency');
CJSCore::Init(array("currency"));
$currencyList = CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true);
if ($loadCurrency) {
	?>
	<script type="text/javascript">
		BX.Currency.setCurrencies(<?=$currencyList?>);
	</script>
	<?
}*/