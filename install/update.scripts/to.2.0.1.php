<?
if (IsModuleInstalled('kit.multiregions')) {
	$eventManager = \Bitrix\Main\EventManager::getInstance();
	$eventManager->registerEventHandler('main', '\Bitrix\Main\Mail\Internal\Event::OnBeforeAdd', "kit.multiregions", 'CKitMultiRegions', 'OnEventBeforeAddBitrixMainEvent');
}