<?
if (IsModuleInstalled('ammina.regions')) {
	$eventManager = \Bitrix\Main\EventManager::getInstance();
	$eventManager->registerEventHandler('main', '\Bitrix\Main\Mail\Internal\Event::OnBeforeAdd', "ammina.regions", 'CAmminaRegions', 'OnEventBeforeAddBitrixMainEvent');
}