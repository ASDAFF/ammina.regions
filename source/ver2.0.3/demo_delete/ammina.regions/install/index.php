<?php
IncludeModuleLangFile(__FILE__);
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/update_client.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/update_client_partner.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/ammina.regions/mbfunc.php');

class ammina_regions extends CModule
{
    const MODULE_ID = 'ammina.regions';
    var $MODULE_ID = 'ammina.regions';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $_1321965346 = '';

    function __construct()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__) . '/version.php');
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = GetMessage('ammina.regions_MODULE_NAME');
        $this->MODULE_DESCRIPTION = GetMessage('ammina.regions_MODULE_DESC');
        $this->PARTNER_NAME = GetMessage('ammina.regions_PARTNER_NAME');
        $this->PARTNER_URI = GetMessage('ammina.regions_PARTNER_URI');
    }

    function InstallEvents()
    {
        return true;
    }

    function UnInstallEvents()
    {
        return true;
    }

    function DoInstall()
    {
        global $APPLICATION;
            $this->InstallFiles();
            $this->InstallDB();
            RegisterModule(self::MODULE_ID);
            CModule::IncludeModule(self::MODULE_ID);
            $this->doInstallSystemVariables();
            $GLOBALS['errors'] = $this->_979922129;
            $APPLICATION->IncludeAdminFile(GetMessage('ammina.regions_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/step2.php');
    }

    function InstallFiles($_287463587 = array())
    {
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin', true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/components', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components', true, true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/js/ammina.regions', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/js/ammina.regions', true, true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/themes/.default', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/.default', true, true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/tools', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tools', true);
        return true;
    }

    function InstallDB($_287463587 = array())
    {
        global $DB, $DBType, $APPLICATION;
        $_979922129 = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/db/' . $DBType . '/install.sql');
        if (!empty($_979922129)) {
            $APPLICATION->ThrowException(implode('', $_979922129));
            return false;
        }
        RegisterModuleDependences('main', 'OnBuildGlobalMenu', self::MODULE_ID, 'CAmminaRegions', 'OnBuildGlobalMenu');
        RegisterModuleDependences('main', 'onMainGeoIpHandlersBuildList', self::MODULE_ID, 'CAmminaRegions', 'onMainGeoIpHandlersBuildList');
        RegisterModuleDependences('main', 'OnProlog', self::MODULE_ID, 'CAmminaRegions', 'OnProlog');
        RegisterModuleDependences('catalog', 'OnGetOptimalPrice', self::MODULE_ID, 'CAmminaRegions', 'OnGetOptimalPrice');
        RegisterModuleDependences('main', 'OnEndBufferContent', self::MODULE_ID, 'CAmminaRegions', 'OnEndBufferContent');
        RegisterModuleDependences('sale', 'OnSaleOrderBeforeSaved', self::MODULE_ID, 'CAmminaRegions', 'OnSaleOrderBeforeSaved');
        RegisterModuleDependences('main', 'OnBeforeEventAdd', self::MODULE_ID, 'CAmminaRegions', 'OnBeforeEventAdd');
        RegisterModuleDependences('sale', 'OnCondSaleControlBuildList', self::MODULE_ID, 'CAmminaRegionsSaleCondCtrlDomain', 'GetControlDescr', 10000);
        $_597196128 = \Bitrix\Main\EventManager::getInstance();
        $_597196128->registerEventHandler('sale', 'onSaleCompanyRulesClassNamesBuildList', self::MODULE_ID, '\Ammina\Regions\Rules\Sale\CompanyRules\Domain', 'onSaleCompanyRulesClassNamesBuildList');
        $_597196128->registerEventHandler('sale', 'onSaleDeliveryRestrictionsClassNamesBuildList', self::MODULE_ID, '\Ammina\Regions\Rules\Sale\DeliveryRestrictions\Domain', 'onSaleDeliveryRestrictionsClassNamesBuildList');
        $_597196128->registerEventHandler('sale', 'onSalePaySystemRestrictionsClassNamesBuildList', self::MODULE_ID, '\Ammina\Regions\Rules\Sale\PaySystemRestrictions\Domain', 'onSalePaySystemRestrictionsClassNamesBuildList');
        $_597196128->registerEventHandler('main', '\Bitrix\Main\Mail\Internal\Event::OnBeforeAdd', self::MODULE_ID, 'CAmminaRegions', 'OnEventBeforeAddBitrixMainEvent');
        RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', self::MODULE_ID, '\Ammina\Regions\IblockProp\Domain', 'GetUserTypeDescription');
        RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', self::MODULE_ID, '\Ammina\Regions\IblockProp\Country', 'GetUserTypeDescription');
        RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', self::MODULE_ID, '\Ammina\Regions\IblockProp\Region', 'GetUserTypeDescription');
        RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', self::MODULE_ID, '\Ammina\Regions\IblockProp\City', 'GetUserTypeDescription');
        RegisterModuleDependences('main', 'OnUserTypeBuildList', self::MODULE_ID, '\Ammina\Regions\UserProp\Domain', 'GetUserTypeDescription');
        RegisterModuleDependences('main', 'OnUserTypeBuildList', self::MODULE_ID, '\Ammina\Regions\UserProp\Country', 'GetUserTypeDescription');
        RegisterModuleDependences('main', 'OnUserTypeBuildList', self::MODULE_ID, '\Ammina\Regions\UserProp\Region', 'GetUserTypeDescription');
        RegisterModuleDependences('main', 'OnUserTypeBuildList', self::MODULE_ID, '\Ammina\Regions\UserProp\City', 'GetUserTypeDescription');
        return true;
    }

    function doInstallSystemVariables()
    {
        $_1314200475 = include(dirname(__FILE__) . '/system.variables.php');
        foreach ($_1314200475 as $_1916429574) {
            $_1812464428 = \Ammina\Regions\VariableTable::getList(array('filter' => array('IS_SYSTEM' => array('Y', 'E'), 'CODE' => $_1916429574['CODE'],),))->fetch();
            if (!$_1812464428) {
                \Ammina\Regions\VariableTable::add(array('NAME' => $_1916429574['NAME'], 'DESCRIPTION' => $_1916429574['DESCRIPTION'], 'CODE' => $_1916429574['CODE'], 'IS_SYSTEM' => ($_1916429574['IS_EDIT'] == 'Y' ? 'E' : 'Y'),));
            }
        }
    }

    function DoUninstall()
    {
        global $APPLICATION, $step;
        $step = IntVal($step);
        if ($step < 2) {
            $APPLICATION->IncludeAdminFile(GetMessage('ammina.regions_UNINSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/unstep1.php');
        } elseif ($step == 2) {
            UnRegisterModule(self::MODULE_ID);
            $this->UnInstallDB(array('savedata' => $_REQUEST['savedata'],));
            $this->UnInstallFiles();
            $GLOBALS['errors'] = $this->_979922129;
            $APPLICATION->IncludeAdminFile(GetMessage('ammina.regions_UNINSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/unstep2.php');
        }
    }

    function UnInstallDB($_287463587 = array())
    {
        global $DB, $DBType, $APPLICATION;
        if (array_key_exists('savedata', $_287463587) && $_287463587['savedata'] != 'Y') {
            $_979922129 = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/db/' . $DBType . '/uninstall.sql');
            if (!empty($_979922129)) {
                $APPLICATION->ThrowException(implode('', $_979922129));
                return false;
            }
        }
        UnRegisterModuleDependences('sale', 'OnCondSaleControlBuildList', self::MODULE_ID, 'CAmminaRegionsSaleCondCtrlDomain', 'GetControlDescr');
        UnRegisterModuleDependences('main', 'OnProlog', self::MODULE_ID, 'CAmminaRegions', 'OnProlog');
        UnRegisterModuleDependences('main', 'onMainGeoIpHandlersBuildList', self::MODULE_ID, 'CAmminaRegions', 'onMainGeoIpHandlersBuildList');
        UnRegisterModuleDependences('main', 'OnBuildGlobalMenu', self::MODULE_ID, 'CAmminaRegions', 'OnBuildGlobalMenu');
        UnRegisterModuleDependences('catalog', 'OnGetOptimalPrice', self::MODULE_ID, 'CAmminaRegions', 'OnGetOptimalPrice');
        UnRegisterModuleDependences('main', 'OnEndBufferContent', self::MODULE_ID, 'CAmminaRegions', 'OnEndBufferContent');
        UnRegisterModuleDependences('sale', 'OnSaleOrderBeforeSaved', self::MODULE_ID, 'CAmminaRegions', 'OnSaleOrderBeforeSaved');
        UnRegisterModuleDependences('main', 'OnBeforeEventAdd', self::MODULE_ID, 'CAmminaRegions', 'OnBeforeEventAdd');
        $_597196128 = \Bitrix\Main\EventManager::getInstance();
        $_597196128->unregisterEventHandler('main', '\Bitrix\Main\Mail\Internal\Event::OnBeforeAdd', self::MODULE_ID, 'CAmminaRegions', 'OnEventBeforeAddBitrixMainEvent');
        $_597196128->unregisterEventHandler('sale', 'onSaleCompanyRulesClassNamesBuildList', self::MODULE_ID, '\Ammina\Regions\Rules\SaleCompanyRules\Domain', 'onSaleCompanyRulesClassNamesBuildList');
        $_597196128->unregisterEventHandler('sale', 'onSaleDeliveryRestrictionsClassNamesBuildList', self::MODULE_ID, '\Ammina\Regions\Rules\Sale\DeliveryRestrictions\Domain', 'onSaleDeliveryRestrictionsClassNamesBuildList');
        $_597196128->unregisterEventHandler('sale', 'onSalePaySystemRestrictionsClassNamesBuildList', self::MODULE_ID, '\Ammina\Regions\Rules\Sale\PaySystemRestrictions\Domain', 'onSalePaySystemRestrictionsClassNamesBuildList');
        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', self::MODULE_ID, '\Ammina\Regions\IblockProp\Domain', 'GetUserTypeDescription');
        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', self::MODULE_ID, '\Ammina\Regions\IblockProp\Country', 'GetUserTypeDescription');
        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', self::MODULE_ID, '\Ammina\Regions\IblockProp\Region', 'GetUserTypeDescription');
        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', self::MODULE_ID, '\Ammina\Regions\IblockProp\City', 'GetUserTypeDescription');
        UnRegisterModuleDependences('main', 'OnUserTypeBuildList', self::MODULE_ID, '\Ammina\Regions\UserProp\Domain', 'GetUserTypeDescription');
        UnRegisterModuleDependences('main', 'OnUserTypeBuildList', self::MODULE_ID, '\Ammina\Regions\UserProp\Country', 'GetUserTypeDescription');
        UnRegisterModuleDependences('main', 'OnUserTypeBuildList', self::MODULE_ID, '\Ammina\Regions\UserProp\Region', 'GetUserTypeDescription');
        UnRegisterModuleDependences('main', 'OnUserTypeBuildList', self::MODULE_ID, '\Ammina\Regions\UserProp\City', 'GetUserTypeDescription');
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/tools', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tools');
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/components', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components');
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/js/ammina.regions', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/js/ammina.regions');
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/themes/.default/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/.default');
        return true;
    }
} ?>