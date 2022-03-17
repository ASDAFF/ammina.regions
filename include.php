<?php

IncludeModuleLangFile(__FILE__);
CModule::IncludeModule('iblock');
CModule::IncludeModule('catalog');
CModule::IncludeModule('sale');

use Bitrix\Catalog;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;

include_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/kit.multiregions/mbfunc.php');

class CKitMultiRegions
{
    const LANG_DOMAIN = "DOMAIN";
    const LANG_CITY = "CITY";
    const LANG_REGION = "REGION";
    const LANG_COUNTRY = "COUNTRY";
    protected static $_271151327 = null;
    protected static $_1654000536 = -1;
    protected static $_961438933 = null;
    static protected $_1518145332 = false;
    protected static $_1408898308 = array(
        "mnu_IPROPERTY_TEMPLATES_SECTION_META_TITLE",
        "mnu_IPROPERTY_TEMPLATES_SECTION_META_KEYWORDS",
        "mnu_IPROPERTY_TEMPLATES_SECTION_META_DESCRIPTION",
        "mnu_IPROPERTY_TEMPLATES_SECTION_PAGE_TITLE",
        "mnu_IPROPERTY_TEMPLATES_ELEMENT_META_TITLE",
        "mnu_IPROPERTY_TEMPLATES_ELEMENT_META_KEYWORDS",
        "mnu_IPROPERTY_TEMPLATES_ELEMENT_META_DESCRIPTION",
        "mnu_IPROPERTY_TEMPLATES_ELEMENT_PAGE_TITLE",
        "mnu_IPROPERTY_TEMPLATES_SECTION_PICTURE_FILE_ALT",
        "mnu_IPROPERTY_TEMPLATES_SECTION_PICTURE_FILE_TITLE",
        "mnu_IPROPERTY_TEMPLATES_SECTION_PICTURE_FILE_NAME",
        "mnu_IPROPERTY_TEMPLATES_SECTION_DETAIL_PICTURE_FILE_ALT",
        "mnu_IPROPERTY_TEMPLATES_SECTION_DETAIL_PICTURE_FILE_TITLE",
        "mnu_IPROPERTY_TEMPLATES_SECTION_DETAIL_PICTURE_FILE_NAME",
        "mnu_IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_ALT",
        "mnu_IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_TITLE",
        "mnu_IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_NAME",
        "mnu_IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_ALT",
        "mnu_IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_TITLE",
        "mnu_IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_NAME",
    );
    protected static $_1659335636 = false;
    private static $_1243235367 = false;

    public static function onMainGeoIpHandlersBuildList()
    {
        return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS, array('\Kit\MultiRegions\GeoIPHandler' => '/bitrix/modules/kit.multiregions/lib/geoip.php',));
    }

    public static function OnProlog()
    {

        if (amreg_strpos($_SERVER[COption::GetOptionString('kit.multiregions', 'host_var_name', 'HTTP_HOST')], ':') !== false) {
            $_1524616046 = explode(':', $_SERVER[COption::GetOptionString('kit.multiregions', 'host_var_name', 'HTTP_HOST')]);
            unset($_1524616046[count($_1524616046) - 1]);
            $_SERVER[COption::GetOptionString('kit.multiregions', 'host_var_name', 'HTTP_HOST')] = implode(':', $_1524616046);
        }
        CModule::IncludeModule('catalog');
        CModule::IncludeModule('sale');
        global $APPLICATION;
        if (isset($GLOBALS['ARG_SETAGENT_NEXT']) && is_array($GLOBALS['ARG_SETAGENT_NEXT'])) {
            foreach ($GLOBALS['ARG_SETAGENT_NEXT'] as $_304928896 => $_173724304) {
                \CAgent::Update($_304928896, array('NEXT_EXEC' => $_173724304,));
            }
        }
        if (!self::isAllowPageWork()) {
            return;
        }
        self::doCheckExistsDomain();
        if (amreg_strlen($_REQUEST['argcity']) > 0) {
            $_1478384894 = \Bitrix\Main\Application::getInstance();
            $_742466237 = new \Bitrix\Main\Web\Cookie('ARG_CITY', $_REQUEST['argcity'], 3600 * 24 * 365 + time());
            $_742466237->setHttpOnly(false);
            $_1478384894->getContext()->getResponse()->addCookie($_742466237);
            unset($_SESSION['BX_GEO_IP']);
            if (COption::GetOptionString('kit.multiregions', 'use_one_domain', 'N') == 'Y') {
                $_910246949 = self::ConvertUrlToPathRegion($APPLICATION->GetCurPageParam('', array('argcity')), false, $_REQUEST['argcity']);
            } else {
                $_910246949 = 'http' . ($APPLICATION->IsHttps() ? 's' : '') . '://' . $_SERVER[COption::GetOptionString('kit.multiregions', 'host_var_name', 'HTTP_HOST')] . self::ConvertUrlToPathRegion($APPLICATION->GetCurPageParam('', array('argcity')), false, $_REQUEST['argcity']);
            }
            LocalRedirect($_910246949);
        }
        $_277830158 = false;
        if (COption::GetOptionString('kit.multiregions', 'use_one_domain', 'N') == 'Y') {
            $_1594366934 = false;
            if (amreg_strlen($_REQUEST['argcity']) > 0) {
                $_1594366934 = $_REQUEST['argcity'];
            } else {
                $_1478384894 = \Bitrix\Main\Application::getInstance();
                $_1594366934 = intval($_1478384894->getContext()->getRequest()->getCookie('ARG_CITY'));
            }
            if ($_1594366934 <= 0) {
                $_1594366934 = \Kit\MultiRegions\BlockTable::getCityIdByIP();
            }
            $_323813335 = \Kit\MultiRegions\DomainTable::doFindDomainByCity($_1594366934, SITE_ID);
            if (isset($_SERVER['REQUIRED_KIT_REGION_CODE']) && isset($_SERVER['REQUIRED_KIT_DOMAIN_ID'])) {
                if ($_SERVER['REQUIRED_KIT_DOMAIN_ID'] != $_323813335) {
                    $_873581518 = \Kit\MultiRegions\DomainTable::getRowById(intval($_SERVER['REQUIRED_KIT_DOMAIN_ID']));
                    if ($_873581518) {
                        $_1594366934 = $_873581518['CITY_ID'];
                        $_1478384894 = \Bitrix\Main\Application::getInstance();
                        $_742466237 = new \Bitrix\Main\Web\Cookie('ARG_CITY', $_1594366934, 3600 * 24 * 365 + time());
                        $_742466237->setHttpOnly(false);
                        $_1478384894->getContext()->getResponse()->addCookie($_742466237);
                        unset($_SESSION['BX_GEO_IP']);
                        $_323813335 = $_873581518['ID'];
                    }
                }
            }
            $_1427624177 = new CPHPCache();
            if ($_1427624177->InitCache(3600, 'kit_region_domains' . $_323813335, 'kit/multiregions/onedomain')) {
                $_1228595998 = $_1427624177->GetVars();
                $_277830158 = $_1228595998['DOMAIN'];
                $_1474516739 = $_1228595998['LOCATIONS'];
                $_1489560310 = $_1228595998['VARIABLES'];
                $GLOBALS['KIT_MULTIREGIONS'] = $_1228595998['GLOBAL_VAR'];
                $GLOBALS['AMR_TEMPLATES'] = $_1228595998['GLOBAL_TMPL'];
            }
            if ($_1427624177->StartDataCache()) {
                $_1474516739 = false;
                $_1489560310 = false;
                if ($_323813335 > 0) {
                    $_277830158 = \Kit\MultiRegions\DomainTable::getList(array('filter' => array('ID' => $_323813335, 'SITE_ID' => SITE_ID,),))->fetch();
                } else {
                    $_277830158 = \Kit\MultiRegions\DomainTable::getList(array('filter' => array('SITE_ID' => SITE_ID, 'ACTIVE' => 'Y', 'IS_DEFAULT' => 'Y',),))->fetch();
                }
                if (!$_277830158) {
                    $_277830158 = \Kit\MultiRegions\DomainTable::getList(array('filter' => array('SITE_ID' => SITE_ID, 'ACTIVE' => 'Y',), 'order' => array('ID' => 'ASC'),))->fetch();
                }
                if ($_277830158) {
                    list($_277830158, $_1474516739, $_1489560310) = self::doLoadDomainById($_277830158['ID']);
                }
                $_1427624177->EndDataCache(array('DOMAIN' => $_277830158, 'LOCATIONS' => $_1474516739, 'VARIABLES' => $_1489560310, 'GLOBAL_VAR' => $GLOBALS['KIT_MULTIREGIONS'], 'GLOBAL_TMPL' => $GLOBALS['AMR_TEMPLATES'],));
            }
        } else {
            $_460093401 = array(amreg_strtolower($_SERVER[COption::GetOptionString('kit.multiregions', 'host_var_name', 'HTTP_HOST')]));
            if (amreg_strpos(amreg_strtolower($_SERVER[COption::GetOptionString('kit.multiregions', 'host_var_name', 'HTTP_HOST')]), 'www.') === 0) {
                $_460093401[] = amreg_substr(amreg_strtolower($_SERVER[COption::GetOptionString('kit.multiregions', 'host_var_name', 'HTTP_HOST')]), 4);
            } else {
                $_460093401[] = 'www.' . amreg_strtolower($_SERVER[COption::GetOptionString('kit.multiregions', 'host_var_name', 'HTTP_HOST')]);
            }
            $_1427624177 = new CPHPCache();
            if ($_1427624177->InitCache(3600, 'kit_region_domains' . implode('|', $_460093401), 'kit/multiregions/domain')) {
                $_1228595998 = $_1427624177->GetVars();
                $_277830158 = $_1228595998['DOMAIN'];
                $_1474516739 = $_1228595998['LOCATIONS'];
                $_1489560310 = $_1228595998['VARIABLES'];
                $GLOBALS['KIT_MULTIREGIONS'] = $_1228595998['GLOBAL_VAR'];
                $GLOBALS['AMR_TEMPLATES'] = $_1228595998['GLOBAL_TMPL'];
            }
            if (empty($_277830158)) {
                $_1427624177->Clean('kit_region_domains' . implode('|', $_460093401), 'kit/multiregions/domain');
            }
            if ($_1427624177->StartDataCache()) {
                $_1474516739 = false;
                $_1489560310 = false;
                $_277830158 = \Kit\MultiRegions\DomainTable::getList(array('filter' => array('SITE_ID' => SITE_ID, 'ACTIVE' => 'Y', 'DOMAIN' => $_460093401,),))->fetch();
                if (!$_277830158) {
                    $_277830158 = \Kit\MultiRegions\DomainTable::getList(array('filter' => array('SITE_ID' => SITE_ID, 'ACTIVE' => 'Y', 'IS_DEFAULT' => 'Y',),))->fetch();
                }
                if (!$_277830158) {
                    $_277830158 = \Kit\MultiRegions\DomainTable::getList(array('filter' => array('SITE_ID' => SITE_ID, 'ACTIVE' => 'Y',), 'order' => array('ID' => 'ASC'),))->fetch();
                }
                if ($_277830158) {
                    list($_277830158, $_1474516739, $_1489560310) = self::doLoadDomainById($_277830158['ID']);
                }
                $_1427624177->EndDataCache(array('DOMAIN' => $_277830158, 'LOCATIONS' => $_1474516739, 'VARIABLES' => $_1489560310, 'GLOBAL_VAR' => $GLOBALS['KIT_MULTIREGIONS'], 'GLOBAL_TMPL' => $GLOBALS['AMR_TEMPLATES'],));
            }
        }
        $_1388636660 = self::getAllGeocontentId();
        foreach ($_1388636660 as $_1521960729 => $_1636743103) {
            ob_start();
            $APPLICATION->IncludeComponent('kit:multiregions.geocontent', '', array('CACHE_TIME' => '300', 'CACHE_TYPE' => 'A', 'CONTENT_TYPE' => $_1521960729, 'IP' => '', 'SET_TAG_IDENT' => 'N', 'SET_TAG_TYPE' => '', 'NO_FRAME_MODE' => 'Y'), null, array('HIDE_ICONS' => 'Y'));
            $GLOBALS['AMR_TEMPLATES']['#KIT_GC_' . $_1521960729 . '#'] = ob_get_contents();
            $GLOBALS['KIT_MULTIREGIONS']['GC_' . $_1521960729] = $GLOBALS['AMR_TEMPLATES']['#KIT_GC_' . $_1521960729 . '#'];
            $GLOBALS['AMR_TEMPLATES']['#KIT_GC_' . $_1521960729 . '_NOHTML#'] = htmlspecialchars(strip_tags($GLOBALS['AMR_TEMPLATES']['#KIT_GC_' . $_1521960729 . '#']));
            $GLOBALS['KIT_MULTIREGIONS']['GC_' . $_1521960729 . '_NOHTML'] = $GLOBALS['AMR_TEMPLATES']['#KIT_GC_' . $_1521960729 . '_NOHTML#'];
            ob_end_clean();
        }
        $_1041176984 = $APPLICATION->GetCurPage();
        if (amreg_strpos($_1041176984, '/bitrix/admin/') !== 0) {
            if (amreg_strlen($GLOBALS['KIT_MULTIREGIONS']['SYS_COUNTERS']) > 0) {
                Main\Page\Asset::getInstance()->addString($GLOBALS['KIT_MULTIREGIONS']['SYS_COUNTERS']);
            }
            if (amreg_strlen($GLOBALS['KIT_MULTIREGIONS']['SYS_HEAD_STRING']) > 0) {
                Main\Page\Asset::getInstance()->addString($GLOBALS['KIT_MULTIREGIONS']['SYS_HEAD_STRING']);
            }
        }
        \Kit\MultiRegions\DomainTable::doHackCurrency();
        self::doCheckAdminPages();
        self::doAddMailTemplateFields();
        self::doCheckAdminOrderEditPageInterface();
        self::doCheckAdminOrderRefreshCurrency();

    }

    static public function isAllowPageWork()
    {
        global $APPLICATION;
        if (defined('KIT_MULTIREGIONS_STOP') && KIT_MULTIREGIONS_STOP === true) {
            return false;
        }
        if ((defined('BX_CRONTAB') && BX_CRONTAB === true) || (defined('CHK_EVENT') && CHK_EVENT === true)) {
            return false;
        }
        if (!isset($_SERVER['HTTP_HOST'])) {
            return false;
        }
        if (\CKitMultiRegions::doMathPageToRules(array('/bitrix/admin/', '/bitrix/services/', '/bitrix/activities/', '/bitrix/gadgets/', '/bitrix/panel/', '/bitrix/tools/', '/bitrix/wizards/', '/bitrix/components/bitrix/sender.', '/bitrix/components/bitrix/report.', '/bitrix/components/bitrix/rest.', '/bitrix/components/bitrix/b24connector.', '/bitrix/components/bitrix/bitrixcloud.', '/bitrix/components/bitrix/bitrixcloud.', '/bitrix/components/bitrix/ui.',), $APPLICATION->GetCurPage())) {
            return false;
        }
        return true;
    }

    public static function doMathPageToRules($_1165668140, $_1200989976)
    {
        if (is_array($_1165668140)) {
            $_1524131531 = $_1165668140;
        } else {
            $_1524131531 = explode('
', $_1165668140);
        }
        foreach ($_1524131531 as $_494365947) {
            $_494365947 = trim($_494365947);
            if (amreg_strlen($_494365947) > 0) {
                if (amreg_strpos($_494365947, 'PREG:') === 0) {
                    $_494365947 = trim(amreg_substr($_494365947, 5));
                    $_2001828445 = '/' . $_494365947 . '/ui';
                    $_2073668601 = array();
                    if (preg_match($_2001828445, $_1200989976, $_2073668601)) {
                        return true;
                    }
                } elseif (amreg_strpos($_494365947, 'PART:') === 0) {
                    $_494365947 = trim(amreg_substr($_494365947, 5));
                    if (amreg_stripos($_1200989976, $_494365947) !== false) {
                        return true;
                    }
                } else {
                    if (amreg_stripos($_1200989976, $_494365947) === 0) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public static function doCheckExistsDomain()
    {
        if ((defined("ADMIN_SECTION") && ADMIN_SECTION === true) || (defined("BX_CRONTAB") && BX_CRONTAB === true) || (defined("CHK_EVENT") && CHK_EVENT === true)) {
            return;
        }
        if (COption::GetOptionString('kit.multiregions', 'use_one_domain', 'N') == 'Y') {
            return;
        }
        $_89171257 = amreg_strtolower(trim($_SERVER[COption::GetOptionString('kit.multiregions', 'host_var_name', 'HTTP_HOST')]));
        if (COption::GetOptionString('kit.multiregions', 'only_exists_domains', 'Y') == 'Y') {
            $_1427624177 = new CPHPCache();
            $_421704317 = 'kit_region_domains_exists|' . SITE_ID;
            if ($_1427624177->InitCache(3600, $_421704317, 'kit/multiregions/domain.list')) {
                $_1228595998 = $_1427624177->GetVars();
                $_212156784 = $_1228595998['DOMAINS'];
            }
            if ($_1427624177->StartDataCache()) {
                $_212156784 = array();
                $_235779931 = \Kit\MultiRegions\DomainTable::getList(array('filter' => array('SITE_ID' => SITE_ID, 'ACTIVE' => 'Y'), 'select' => array('ID', 'IS_DEFAULT', 'DOMAIN'), 'order' => array('ID' => 'ASC')));
                while ($_277830158 = $_235779931->fetch()) {
                    $_212156784[amreg_strtolower(trim($_277830158['DOMAIN']))] = $_277830158;
                }
                $_1427624177->EndDataCache(array('DOMAINS' => $_212156784));
            }
            if (is_array($_212156784) && count($_212156784) > 0 && !isset($_212156784[$_89171257])) {
                $_1114465324 = false;
                if (amreg_strpos($_89171257, 'www.') === 0) {
                    $_89171257 = amreg_substr($_89171257, 4);
                    if (isset($_212156784[$_89171257])) {
                        $_1114465324 = $_212156784[$_89171257]['ID'];
                    }
                }
                if ($_1114465324 === false) {
                    foreach ($_212156784 as $_304928896 => $_173724304) {
                        if ($_173724304['IS_DEFAULT'] == 'Y') {
                            $_1114465324 = $_173724304['ID'];
                            break;
                        }
                    }
                }
                if ($_1114465324 === false && count($_212156784) > 0) {
                    $_126976587 = array_keys($_212156784);
                    $_1114465324 = $_212156784[$_126976587[0]]['ID'];
                }
                if ($_1114465324 !== false) {
                    $_879800218 = \Kit\MultiRegions\DomainTable::doGetRedirectLinkByDomainId($_1114465324);
                    if (amreg_strlen($_879800218) > 0) {
                        LocalRedirect($_879800218, '301 Moved Permanently');
                        die();
                    }
                }
            }
        }
    }

    public static function ConvertUrlToPathRegion($_910246949, $_2125521713 = false, $_445082040 = false)
    {
        if (COption::GetOptionString("kit.multiregions", "use_one_domain", "N") == "Y" && COption::GetOptionString("kit.multiregions", "use_path_domain", "N") == "Y") {
            $_306338610 = explode("\n", COption::GetOptionString("kit.multiregions", "pathdomain_list", ""));
            foreach ($_306338610 as $_304928896 => $_173724304) {
                $_306338610[$_304928896] = trim($_173724304);
                if (amreg_strlen($_306338610[$_304928896]) <= 0) {
                    unset($_306338610[$_304928896]);
                } else {
                    if (amreg_substr($_306338610[$_304928896], -1, 1) != '/') {
                        $_306338610[$_304928896] .= '/';
                    }
                }
            }
            if ($_2125521713 <= 0) {
                if ($_445082040 > 0) {
                    $_2125521713 = \Kit\MultiRegions\DomainTable::doFindDomainByCity($_445082040, SITE_ID);
                }
            }
            if ($_2125521713 > 0) {
                $_46203454 = array();
                $_277830158 = \Kit\MultiRegions\DomainTable::getRowById($_2125521713);
                if ($_277830158 && amreg_strlen($_277830158['PATHCODE']) > 0) {
                    foreach ($_306338610 as $_304928896 => $_995049603) {
                        $_1806270310 = 0;
                        if (amreg_strpos($_995049603, '*[') !== false) {
                            $_1524616046 = explode('[', $_995049603);
                            $_1338829613 = explode(']', $_1524616046[1]);
                            $_1806270310 = $_1338829613[0];
                            $_1524616046[1] = $_1338829613[1];
                            $_995049603 = implode('', $_1524616046);
                        }
                        $_739903730 = str_replace('*', $_277830158['PATHCODE'], $_995049603);
                        $_739903730 = str_replace('//', '/', $_739903730);
                        $_1366210027 = str_replace('*', '', $_995049603);
                        $_1366210027 = str_replace('//', '/', $_1366210027);
                        $_46203454[$_304928896] = array('FROM' => $_1366210027, 'TO' => $_739903730, 'LEVEL' => $_1806270310);
                    }
                }
                if (!empty($_46203454)) {
                    foreach ($_46203454 as $_304928896 => $_173724304) {
                        if (amreg_strpos($_910246949, $_173724304['FROM']) !== false) {
                            $_58605981 = true;
                            if ($_173724304['LEVEL'] > 0) {
                                $_959958512 = explode('/', amreg_substr($_910246949, amreg_strpos($_910246949, $_173724304['FROM']) + amreg_strlen($_173724304['FROM'])));
                                if (count($_959958512) > $_173724304['LEVEL']) {
                                    $_58605981 = false;
                                }
                            }
                            if ($_58605981) {
                                $_910246949 = str_replace($_173724304['FROM'], $_173724304['TO'], $_910246949);
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $_910246949;
    }

    protected static function doLoadDomainById($_279617631)
    {
        $_192101498 = array();
        $_277830158 = false;
        $_1474516739 = false;
        $_1489560310 = false;
        if ($_279617631 > 0) {
            $_277830158 = \Kit\MultiRegions\DomainTable::getList(array('filter' => array('ID' => $_279617631,),))->fetch();
        }
        if (!$_277830158) {
            $_277830158 = \Kit\MultiRegions\DomainTable::getList(array('filter' => array('SITE_ID' => SITE_ID, 'ACTIVE' => 'Y', 'IS_DEFAULT' => 'Y',),))->fetch();
        }
        if (!$_277830158) {
            $_277830158 = \Kit\MultiRegions\DomainTable::getList(array('filter' => array('SITE_ID' => SITE_ID, 'ACTIVE' => 'Y', 'IS_DEFAULT' => 'Y',),))->fetch();
        }
        if ($_277830158) {
            $_277830158['NAME'] = \Kit\MultiRegions\DomainTable::getLangName($_277830158['ID']);
            if ($_277830158['CITY_ID'] > 0) {
                $_1474516739['CITY_ID'][] = $_277830158['CITY_ID'];
            }
            $_1909020636 = \Kit\MultiRegions\DomainLocationTable::getList(array('filter' => array('DOMAIN_ID' => $_277830158['ID'],),));
            while ($_452315059 = $_1909020636->fetch()) {
                if ($_452315059['COUNTRY_ID'] > 0) {
                    $_1474516739['COUNTRY_ID'][] = $_452315059['COUNTRY_ID'];
                }
                if ($_452315059['REGION_ID'] > 0) {
                    $_1474516739['REGION_ID'][] = $_452315059['REGION_ID'];
                }
                if ($_452315059['CITY_ID'] > 0) {
                    $_1474516739['CITY_ID'][] = $_452315059['CITY_ID'];
                }
            }
            $_1861127605 = array();
            $_451588349 = \Kit\MultiRegions\VariableTable::getList(array('select' => array('ID', 'CODE', 'NAME')));
            while ($_909312842 = $_451588349->fetch()) {
                $_1861127605[$_909312842['ID']] = $_909312842;
            }
            $_1429214694 = \Kit\MultiRegions\DomainVariableTable::getList(array('filter' => array('DOMAIN_ID' => $_277830158['ID'],), 'select' => array('*', 'VARIABLE_NAME' => 'VARIABLE.NAME', 'VARIABLE_CODE' => 'VARIABLE.CODE'),));
            while ($_861549254 = $_1429214694->fetch()) {
                unset($_1861127605[$_861549254['VARIABLE_ID']]);
                $_1489560310[] = $_861549254;
                if (is_array($_861549254['VALUE'])) {
                    $GLOBALS['AMR_TEMPLATES']['#KIT_MULTIREGIONS_' . $_861549254['VARIABLE_CODE'] . '#'] = implode($_277830158['VARIABLE_SEPARATOR'], $_861549254['VALUE']);
                } else {
                    $GLOBALS['AMR_TEMPLATES']['#KIT_MULTIREGIONS_' . $_861549254['VARIABLE_CODE'] . '#'] = $_861549254['VALUE'];
                }
                $GLOBALS['AMR_TEMPLATES_NAME']['#KIT_MULTIREGIONS_' . $_861549254['VARIABLE_CODE'] . '#'] = $_861549254['VARIABLE_NAME'];
                if (!empty($_861549254['VARIABLE_CODE'])) {
                    $GLOBALS['KIT_MULTIREGIONS'][$_861549254['VARIABLE_CODE']] = $_861549254['VALUE'];
                }
            }
            $GLOBALS['KIT_MULTIREGIONS']['SYS_CURRENT_DOMAIN_ID'] = $_277830158['ID'];
            foreach ($_1861127605 as $_1521960729 => $_861549254) {
                $GLOBALS['AMR_TEMPLATES']['#KIT_MULTIREGIONS_' . $_861549254['CODE'] . '#'] = '';
                if (amreg_strlen($_861549254['CODE']) > 0) {
                    $GLOBALS['KIT_MULTIREGIONS'][$_861549254['CODE']] = '';
                }
                $GLOBALS['AMR_TEMPLATES_NAME']['#KIT_MULTIREGIONS_' . $_861549254['CODE'] . '#'] = $_861549254['NAME'];
            }
        }
        if (!is_array($_277830158['NAME_LANG'])) {
            $_277830158['NAME_LANG'] = array($_277830158['NAME_LANG']);
        }
        $_277830158['NAME'] = self::getLangFirstName(array_merge(array('ru' => $_277830158['NAME']), $_277830158['NAME_LANG']));
        foreach ($_1489560310 as $_304928896 => $_173724304) {
            if (!empty($_173724304['VALUE_LANG'])) {
                if (!is_array($_277830158['NAME_LANG'])) {
                    $_173724304['VALUE_LANG'] = array($_173724304['VALUE_LANG']);
                }
                $_1489560310[$_304928896]['VALUE'] = self::getLangFirstName(array_merge(array('ru' => $_173724304['VALUE']), $_173724304['VALUE_LANG']));
            }
        }
        $_192101498 = array($_277830158, $_1474516739, $_1489560310);
        return $_192101498;
    }

    public static function getLangFirstName($_1379108498, $_1221050208 = LANGUAGE_ID)
    {
        return self::getFirstNotEmpty(self::getListLangNames($_1379108498, $_1221050208));
    }

    public static function getFirstNotEmpty($_1882211949)
    {
        foreach ($_1882211949 as $_1414695565) {
            if (is_array($_1414695565)) {
                $_1137794976 = trim(implode("", $_1414695565));
            } else {
                $_1414695565 = trim($_1414695565);
                $_1137794976 = trim($_1414695565);
            }
            if (amreg_strlen($_1137794976) > 0) {
                return $_1414695565;
            }
        }
        return false;
    }

    public static function getListLangNames($_1379108498, $_1221050208 = LANGUAGE_ID)
    {
        $_192101498 = array();
        $_1223839210 = array();
        $_1958206292 = false;
        $_792887050 = $_1221050208;
        while (!$_1958206292) {
            if (amreg_strlen($_792887050) <= 0 || isset($_1223839210[$_792887050])) {
                $_1958206292 = true;
            } else {
                $_1223839210[$_792887050] = $_792887050;
                $_192101498[] = $_1379108498[$_792887050];
                $_792887050 = \COption::GetOptionString('kit.multiregions', 'lang_syn_' . $_792887050, '');
            }
        }
        if (!isset($_1223839210['ru'])) {
            $_192101498[] = $_1379108498['ru'];
        }
        if (!isset($_1223839210['en'])) {
            $_192101498[] = $_1379108498['en'];
        }
        return $_192101498;
    }

    public static function getAllGeocontentId()
    {
        $_1427624177 = new CPHPCache();
        $_192101498 = array();
        if ($_1427624177->InitCache(3600 * 24, 'kit_all_geocontentid', 'kit/multiregions')) {
            $_1228595998 = $_1427624177->GetVars();
            $_192101498 = $_1228595998['IDS'];
        }
        if ($_1427624177->StartDataCache()) {
            $_905724234 = \Kit\MultiRegions\ContentTypesTable::getList(array('select' => array('ID', 'NAME')));
            while ($_735282879 = $_905724234->fetch()) {
                $_192101498[$_735282879['ID']] = $_735282879['NAME'];
            }
            $_1427624177->EndDataCache(array('IDS' => $_192101498,));
        }
        return $_192101498;
    }

    protected static function doCheckAdminPages()
    {
        global $APPLICATION;
        $_1357232196 = false;
        $_136206658 = false;
        $_1041176984 = $APPLICATION->GetCurPageParam();
        if (amreg_strpos($_1041176984, '/bitrix/admin/sale_order_edit.php') === 0 && $_REQUEST['ID'] > 0) {
            $_1357232196 = true;
            $_136206658 = $_REQUEST['ID'];
        } elseif (amreg_strpos($_1041176984, '/bitrix/admin/sale_order_view.php') === 0 && $_REQUEST['ID'] > 0) {
            $_1357232196 = true;
            $_136206658 = $_REQUEST['ID'];
        } elseif (amreg_strpos($_1041176984, '/bitrix/admin/sale_order_create.php') === 0 && $_REQUEST['ID'] > 0) {
            $_1357232196 = true;
            $_136206658 = $_REQUEST['ID'];
        } elseif (amreg_strpos($_1041176984, '/bitrix/admin/sale_order_print.php') === 0 && $_REQUEST['ORDER_ID'] > 0) {
            $_1357232196 = true;
            $_136206658 = $_REQUEST['ORDER_ID'];
        } elseif (amreg_strpos($_1041176984, '/bitrix/admin/sale_order_payment_edit.php') === 0 && $_REQUEST['order_id'] > 0) {
            $_1357232196 = true;
            $_136206658 = $_REQUEST['order_id'];
        } elseif (amreg_strpos($_1041176984, '/bitrix/admin/sale_order_shipment_edit.php') === 0 && $_REQUEST['order_id'] > 0) {
            $_1357232196 = true;
            $_136206658 = $_REQUEST['order_id'];
        }
        if ($_1357232196 && $_136206658 > 0) {
            $_104444584 = Sale\Order::load($_136206658);
            $_1383947309 = $_104444584->getPropertyCollection();
            $_1213280956 = $_1383947309->getArray();
            $_99025990 = '';
            if (isset($_1213280956['properties']) && is_array($_1213280956['properties'])) {
                foreach ($_1213280956['properties'] as $_85797636) {
                    if ($_85797636['CODE'] == 'SYS_DOMAIN') {
                        $_99025990 = $_85797636['VALUE'];
                        if (is_array($_99025990)) {
                            $_99025990 = implode('|', $_99025990);
                        }
                        break;
                    }
                }
            }
            $_279617631 = false;
            if (amreg_strlen($_99025990) > 0) {
                if (amreg_strpos($_99025990, '[') === 0) {
                    $_279617631 = intval(amreg_substr($_99025990, 1));
                } else {
                    $_279617631 = intval($_99025990);
                }
            }
            if ($_279617631 > 0) {
                self::doLoadDomainById($_279617631);
            }
        }
    }

    protected static function doAddMailTemplateFields()
    {
        global $APPLICATION;
        if (amreg_strpos($APPLICATION->GetCurPage(), '/bitrix/admin/message_edit.php') === 0) {
            CJSCore::Init(array('jquery2'));
            ob_start();
            echo '<hr/>';
            $_1429214694 = \Kit\MultiRegions\VariableTable::getList(array('order' => array('CODE' => 'ASC',),));
            while ($_861549254 = $_1429214694->fetch()) { ?>
                <a title="<?= $_861549254['DESCRIPTION'] ?>"
                   href="javascript:PutString('#KIT_MULTIREGIONS_<?= $_861549254['CODE'] ?>#')">#KIT_MULTIREGIONS_<?= $_861549254['CODE'] ?>
                    #</a> - <?= $_861549254['NAME'] ?>
                <br/>
            <? }
            $_943311826 = ob_get_contents();
            ob_end_clean();
            $_943311826 = array('cont' => $_943311826);
            ob_start(); ?>
            <script type="text/javascript">
                <!--
                $(document).ready(function () {
                    var argEmailTemplateExtFields = <?=CUtil::PhpToJSObject($_943311826)?>;
                    $("#edit1_edit_table td:last").append(argEmailTemplateExtFields['cont']);
                });
                -->
            </script>
            <? $_943311826 = ob_get_contents();
            ob_end_clean();
            Main\Page\Asset::getInstance()->addString($_943311826);
        }
    }

    protected static function doCheckAdminOrderEditPageInterface()
    {
        global $APPLICATION;
        $_1041176984 = $APPLICATION->GetCurPageParam();
        if (amreg_strpos($_1041176984, '/bitrix/admin/sale_order_edit.php') === 0) {
            \CJSCore::Init(array('jquery2'));
            \Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/kit.multiregions/admin/queryfield.js');
            $APPLICATION->SetAdditionalCSS('/bitrix/themes/.default/kit.multiregions.css');
            $_1354194682 = array();
            $_614805236 = CSaleOrderProps::GetList(array(), array('CODE' => 'SYS_DOMAIN'));
            while ($_85797636 = $_614805236->Fetch()) {
                $_1354194682[] = $_85797636['ID'];
            }
            if (!empty($_1354194682) && is_array($_1354194682)) {
                ob_start(); ?>
                <script type="text/javascript">
                    $(document).ready(function () {
                        <?  foreach($_1354194682 as $_1960254778){ ?>
                        $("input[name='PROPERTIES[<?=$_1960254778?>]']").data("action", "domain");
                        $("input[name='PROPERTIES[<?=$_1960254778?>]']").data("min-length", "0");
                        $("input[name='PROPERTIES[<?=$_1960254778?>]']").data("cnt", "30");
                        $("input[name='PROPERTIES[<?=$_1960254778?>]']").attr("autocomplete", "off");
                        $("input[name='PROPERTIES[<?=$_1960254778?>]']").wrap('<div class="bammultiregionsadm-area-item"></div>');
                        $("input[name='PROPERTIES[<?=$_1960254778?>]']").kitMultiRegionsAdminQueryField();
                        <? } ?>
                    });
                </script>
                <? $_943311826 = ob_get_contents();
                ob_end_clean();
                \Bitrix\Main\Page\Asset::getInstance()->addString($_943311826);
            }
        }
    }

    protected static function doCheckAdminOrderRefreshCurrency()
    {
        global $APPLICATION;
        if (amreg_strpos($APPLICATION->GetCurPage(), '/bitrix/admin/sale_order_edit.php') === 0 && $_REQUEST['ID'] > 0 && $_REQUEST['refresh_data_and_save'] == 'Y') {
            $_160692516 = CSaleOrder::GetByID($_REQUEST['ID']);
            if ($_160692516 && $_160692516['CURRENCY'] != $GLOBALS['KIT_MULTIREGIONS']['SYS_CURRENCY']) {
                CSaleOrder::Update($_REQUEST['ID'], array('CURRENCY' => $GLOBALS['KIT_MULTIREGIONS']['SYS_CURRENCY'],));
            }
            return;
        }
    }

    public static function getGlobalVariable($_813482940, $_1469865342 = array(), $_1564294829 = false)
    {
        $_192101498 = $_1469865342;
        if (!empty($_813482940)) {
            $_192101498 = $_813482940;
        }
        if (!is_array($_192101498) && $_1564294829) {
            $_192101498 = array($_192101498);
        }
        return $_192101498;
    }

    public static function OnEndBufferContent(&$_993497444)
    {
        global $APPLICATION;
        if (amreg_strpos($APPLICATION->GetCurPage(), '/bitrix/admin/') === false) {
            if (isset($GLOBALS['AMR_TEMPLATES']) && is_array($GLOBALS['AMR_TEMPLATES'])) {
                foreach ($GLOBALS['AMR_TEMPLATES'] as $_304928896 => $_173724304) {
                    $_993497444 = str_replace($_304928896, $_173724304, $_993497444);
                }
            }
            if (isset($GLOBALS['AMR_EXT_TEMPLATES']) && is_array($GLOBALS['AMR_EXT_TEMPLATES'])) {
                foreach ($GLOBALS['AMR_EXT_TEMPLATES'] as $_304928896 => $_173724304) {
                    $_993497444 = str_replace('#' . $_304928896 . '#', $_173724304, $_993497444);
                }
            }
        }
        if (in_array($APPLICATION->GetCurPage(), array('/bitrix/admin/iblock_element_edit.php', '/bitrix/admin/iblock_section_edit.php', '/bitrix/admin/cat_section_edit.php', '/bitrix/admin/cat_product_edit.php', '/bitrix/admin/iblock_edit.php'))) {
            $_472993189 = 0;
            $_1418176836 = 0;
            $_765933040 = amreg_strpos($_993497444, "BX.adminShowMenu(this, [{'TEXT':", $_472993189);
            while ($_765933040 !== false) {
                $_918434772 = amreg_strpos($_993497444, "], '');", $_765933040 + 1);
                $_1937464415 = false;
                $_271595665 = amreg_strpos($_993497444, "BX.bind(BX('", $_765933040 - 200);
                while ($_271595665 !== false) {
                    if ($_271595665 >= $_765933040) {
                        break;
                    }
                    $_1937464415 = $_271595665;
                    $_271595665 = amreg_strpos($_993497444, "BX.bind(BX('", $_271595665 + 1);
                    break;
                }
                if ($_918434772 !== false && $_1937464415 !== false) {
                    $_1937110160 = amreg_strpos($_993497444, "'", $_1937464415 + 12);
                    $_917118445 = amreg_substr($_993497444, $_1937464415 + 12, $_1937110160 - $_1937464415 - 12);
                    if (in_array($_917118445, self::$_1408898308)) {
                        $_651496756 = array('TEXT' => Loc::getMessage('kit.multiregions_SEO_MENU_TITLE'), 'MENU' => array(),);
                        $_1429214694 = \Kit\MultiRegions\VariableTable::getList(array('order' => array('CODE' => 'ASC',),));
                        while ($_861549254 = $_1429214694->fetch()) {
                            $_651496756['MENU'][] = array('TEXT' => $_861549254['NAME'], 'ONCLICK' => "InheritedPropertiesTemplates.insertIntoInheritedPropertiesTemplate('#KIT_MULTIREGIONS_" . $_861549254["CODE"] . "#', '" . $_917118445 . "', '" . amreg_substr($_917118445, 4) . "')",);
                        }
                        $_651496756['MENU'][] = array('SEPARATOR' => 'Y');
                        $_1388636660 = self::getAllGeocontentId();
                        foreach ($_1388636660 as $_1521960729 => $_1636743103) {
                            $_651496756["MENU"][] = array("TEXT" => Loc::getMessage("kit.multiregions_GEOCONTENT_SEO_NAME") . " [" . $_1521960729 . "]: " . $_1636743103, "ONCLICK" => "InheritedPropertiesTemplates.insertIntoInheritedPropertiesTemplate('#KIT_GC_" . $_1521960729 . "#', '" . Loc::getMessage("kit.multiregions_GEOCONTENT_SEO_NAME") . " [" . $_1521960729 . "]: " . $_1636743103 . "', '" . amreg_substr($_917118445, 4) . "')",);
                            $_651496756["MENU"][] = array("TEXT" => Loc::getMessage("kit.multiregions_GEOCONTENT_NOHTML_SEO_NAME") . " [" . $_1521960729 . "]: " . $_1636743103, "ONCLICK" => "InheritedPropertiesTemplates.insertIntoInheritedPropertiesTemplate('#KIT_GC_" . $_1521960729 . "_NOHTML#', '" . Loc::getMessage("kit.multiregions_GEOCONTENT_NOHTML_SEO_NAME") . " [" . $_1521960729 . "]: " . $_1636743103 . "', '" . amreg_substr($_917118445, 4) . "')",);
                        }
                        $_993497444 = amreg_substr($_993497444, 0, $_918434772) . ',' . CUtil::PhpToJSObject($_651496756) . amreg_substr($_993497444, $_918434772);
                    }
                }
                $_472993189 = $_765933040 + 1;
                $_765933040 = amreg_strpos($_993497444, "BX.adminShowMenu(this, [{'TEXT':", $_472993189);
            }
        }
        if (amreg_strpos($APPLICATION->GetCurPage(), '/bitrix/admin/') === false) {
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/urlrewrite.multiregions.php')) {
                $_507454388 = @include($_SERVER['DOCUMENT_ROOT'] . '/urlrewrite.multiregions.php');
                $_455398214 = $GLOBALS['KIT_MULTIREGIONS']['SYS_PATHCODE'];
                if (!empty($_507454388) && amreg_strlen($_455398214) > 0 && isset($_507454388[$_455398214])) {
                    $_1329901037 = array();
                    preg_match_all("/]*href=['\"]([^'\"]+)['\"][^<>]*>/si" . BX_UTF_PCRE_MODIFIER, $_993497444, $_1329901037);
                    $_1515005269 = array();
                    foreach ($_507454388[$_455398214] as $_304928896 => $_173724304) {
                        foreach ($_1329901037[1] as $_1904016688 => $_812895649) {
                            if (amreg_strpos($_812895649, $_173724304['ORIGINAL']) !== false) {
                                $_58605981 = true;
                                if ($_173724304['LEVEL'] > 0) {
                                    $_959958512 = explode('/', amreg_substr($_812895649, amreg_strpos($_812895649, $_173724304['ORIGINAL']) + amreg_strlen($_173724304['ORIGINAL'])));
                                    if (count($_959958512) > $_173724304['LEVEL']) {
                                        $_58605981 = false;
                                    }
                                }
                                if ($_58605981) {
                                    $_1515005269[$_1329901037[0][$_1904016688]] = self::strReplaceOne($_812895649, self::strReplaceOne($_173724304['ORIGINAL'], $_173724304['REGIONAL'], $_812895649), $_1329901037[0][$_1904016688]);
                                }
                            }
                        }
                    }
                    foreach ($_1515005269 as $_304928896 => $_173724304) {
                        $_993497444 = str_replace($_304928896, $_173724304, $_993497444);
                    }
                }
            }
        }
    }

    public static function strReplaceOne(string $_1035673932, string $_517449014, string $_1089046821)
    {
        return implode($_517449014, explode($_1035673932, $_1089046821, 2));
    }

    public static function OnSaleOrderBeforeSaved($_626299312, $_1116472689)
    {
        global $APPLICATION;
        if (amreg_strpos($APPLICATION->GetCurPage(), '/bitrix/admin/') === 0) {
            return;
        }
        if ($_626299312 instanceof Sale\Order) {
            list($_645390032) = self::doGetPropertiesOrder($_626299312->getPersonTypeId());
            if ($GLOBALS['KIT_MULTIREGIONS']['SYS_SALE_UID'] > 0) {
                $_626299312->setField('RESPONSIBLE_ID', $GLOBALS['KIT_MULTIREGIONS']['SYS_SALE_UID']);
            }
            if ($GLOBALS['KIT_MULTIREGIONS']['SYS_SALE_COMPANY_ID'] > 0) {
                $_626299312->setField('COMPANY_ID', $GLOBALS['KIT_MULTIREGIONS']['SYS_SALE_COMPANY_ID']);
            }
            $_24940383 = $_626299312->getPropertyCollection();
            if ($_645390032 > 0) {
                $_1761620925 = $_24940383->getItemByOrderPropertyId($_645390032);
                if ($_1761620925) {
                    $_1761620925->setValue('[' . $GLOBALS['KIT_MULTIREGIONS']['SYS_CURRENT_DOMAIN_ID'] . '] ' . $GLOBALS['KIT_MULTIREGIONS']['SYS_NAME'] . ' (' . $GLOBALS['KIT_MULTIREGIONS']['SYS_DOMAIN'] . ')');
                } else {
                    $_1852781578 = Sale\Internals\OrderPropsGroupTable::getList(array('filter' => array('PERSON_TYPE_ID' => $_626299312->getPersonTypeId(),), 'order' => array('SORT' => 'ASC'),))->fetch();
                    $_1188265940 = $_1852781578['ID'];
                    $_978627392 = array('ID' => $_645390032, 'PERSON_TYPE_ID' => $_626299312->getPersonTypeId(), 'NAME' => Loc::getMessage('kit.multiregions_ORDER_PROP_SYS_DOMAIN_NAME'), 'TYPE' => 'STRING', 'REQUIRED' => 'N', 'DEFAULT_VALUE' => '', 'SORT' => '10000', 'USER_PROPS' => 'N', 'IS_LOCATION' => 'N', 'PROPS_GROUP_ID' => $_1188265940, 'DESCRIPTION' => Loc::getMessage('kit.multiregions_ORDER_PROP_SYS_DOMAIN_DESCRIPTION'), 'IS_EMAIL' => 'N', 'IS_PROFILE_NAME' => 'N', 'IS_PAYER' => 'N', 'IS_LOCATION4TAX' => 'N', 'IS_FILTERED' => 'Y', 'CODE' => 'SYS_DOMAIN', 'IS_ZIP' => 'N', 'IS_PHONE' => 'N', 'IS_ADDRESS' => 'N', 'ACTIVE' => 'Y', 'UTIL' => 'Y', 'MULTIPLE' => 'N', 'ENTITY_REGISTRY_TYPE' => \Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER,);
                    $_255664061 = $_24940383->createItem($_978627392);
                    $_255664061->setValue('[' . $GLOBALS['KIT_MULTIREGIONS']['SYS_CURRENT_DOMAIN_ID'] . '] ' . $GLOBALS['KIT_MULTIREGIONS']['SYS_NAME'] . ' (' . $GLOBALS['KIT_MULTIREGIONS']['SYS_DOMAIN'] . ')');
                }
            }
        }
    }

    protected static function doGetPropertiesOrder($_1964698255)
    {
        $_192101498 = array();
        if (self::isIMExists()) {
            $_485687951 = Sale\Internals\OrderPropsTable::getList(array('filter' => array('PERSON_TYPE_ID' => $_1964698255, 'CODE' => 'SYS_DOMAIN',),))->fetch();
            if ($_485687951) {
                $_192101498[] = $_485687951['ID'];
            } else {
                $_1852781578 = Sale\Internals\OrderPropsGroupTable::getList(array('filter' => array('PERSON_TYPE_ID' => $_1964698255,), 'order' => array('SORT' => 'ASC'),))->fetch();
                $_1188265940 = $_1852781578['ID'];
                $_978627392 = array('PERSON_TYPE_ID' => $_1964698255, 'NAME' => Loc::getMessage('kit.multiregions_ORDER_PROP_SYS_DOMAIN_NAME'), 'TYPE' => 'STRING', 'REQUIRED' => 'N', 'DEFAULT_VALUE' => '', 'SORT' => '10000', 'USER_PROPS' => 'N', 'IS_LOCATION' => 'N', 'PROPS_GROUP_ID' => $_1188265940, 'DESCRIPTION' => Loc::getMessage('kit.multiregions_ORDER_PROP_SYS_DOMAIN_DESCRIPTION'), 'IS_EMAIL' => 'N', 'IS_PROFILE_NAME' => 'N', 'IS_PAYER' => 'N', 'IS_LOCATION4TAX' => 'N', 'IS_FILTERED' => 'Y', 'CODE' => 'SYS_DOMAIN', 'IS_ZIP' => 'N', 'IS_PHONE' => 'N', 'IS_ADDRESS' => 'N', 'ACTIVE' => 'Y', 'UTIL' => 'Y', 'MULTIPLE' => 'N', 'ENTITY_REGISTRY_TYPE' => \Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER,);
                $_1168412246 = Sale\Internals\OrderPropsTable::add($_978627392);
                if ($_1168412246->isSuccess()) {
                    $_192101498[] = $_1168412246->getId();
                }
            }
        }
        return $_192101498;
    }

    public static function isIMExists()
    {
        if (self::$_1654000536 < 0) {
            self::$_1654000536 = (CModule::IncludeModule("catalog") && CModule::IncludeModule("sale"));
        }
        return self::$_1654000536;
    }

    public static function OnBeforeEventAdd(&$_1575820058, &$_1147978504, &$_978627392, &$_1153895397, &$_2107942633, &$_81329334)
    {
        if (isset($GLOBALS['AMR_TEMPLATES']) && is_array($GLOBALS['AMR_TEMPLATES'])) {
            foreach ($GLOBALS['AMR_TEMPLATES'] as $_304928896 => $_173724304) {
                $_978627392[amreg_substr($_304928896, 1, amreg_strlen($_304928896) - 2)] = $_173724304;
            }
            if (isset($GLOBALS['AMR_TEMPLATES']['#KIT_MULTIREGIONS_SYS_DEFAULT_EMAIL#']) && strlen($GLOBALS['AMR_TEMPLATES']['#KIT_MULTIREGIONS_SYS_DEFAULT_EMAIL#']) > 0) {
                $_978627392['DEFAULT_EMAIL_FROM'] = $GLOBALS['AMR_TEMPLATES']['#KIT_MULTIREGIONS_SYS_DEFAULT_EMAIL#'];
                $_978627392['SALE_EMAIL'] = $GLOBALS['AMR_TEMPLATES']['#KIT_MULTIREGIONS_SYS_DEFAULT_EMAIL#'];
            }
        }
    }

    public static function OnGetOptimalPrice($_263126609, $_1385945150, $_903806926, $_69980226, $_1902462090, $_640395638, $_211755118)
    {
        global $APPLICATION;
        $_263126609 = (int)$_263126609;
        if ($_263126609 <= 0) {
            $APPLICATION->ThrowException(Loc::getMessage('BT_MOD_CATALOG_PROD_ERR_PRODUCT_ID_ABSENT'), 'NO_PRODUCT_ID');
            return false;
        }
        $_1385945150 = (float)$_1385945150;
        if ($_1385945150 <= 0) {
            $APPLICATION->ThrowException(Loc::getMessage('BT_MOD_CATALOG_PROD_ERR_QUANTITY_ABSENT'), 'NO_QUANTITY');
            return false;
        }
        if (!is_array($_903806926) && (int)$_903806926 . '|' == (string)$_903806926 . '|') {
            $_903806926 = array((int)$_903806926);
        }
        if (!is_array($_903806926)) {
            $_903806926 = array();
        }
        if (!in_array(2, $_903806926)) {
            $_903806926[] = 2;
        }
        Main\Type\Collection::normalizeArrayValuesByInt($_903806926);
        $_69980226 = ($_69980226 == 'Y' ? 'Y' : 'N');
        if ($_640395638 === false) {
            $_640395638 = SITE_ID;
        }
        if (!empty($GLOBALS['KIT_MULTIREGIONS']['SYS_CURRENCY'])) {
            Catalog\Product\Price\Calculation::setConfig(array('CURRENCY' => $GLOBALS['KIT_MULTIREGIONS']['SYS_CURRENCY'],));
        }
        $_1711073079 = Catalog\Product\Price\Calculation::getCurrency();
        if (empty($_1711073079)) {
            $APPLICATION->ThrowException(Loc::getMessage('BT_MOD_CATALOG_PROD_ERR_NO_RESULT_CURRENCY'));
            return false;
        }
        $_1264108475 = (int)CIBlockElement::GetIBlockByID($_263126609);
        if ($_1264108475 <= 0) {
            $APPLICATION->ThrowException(Loc::getMessage('BT_MOD_CATALOG_PROD_ERR_ELEMENT_ID_NOT_FOUND', array('#ID#' => $_263126609)), 'NO_ELEMENT');
            return false;
        }
        if (!isset($_1902462090) || !is_array($_1902462090)) {
            $_1902462090 = array();
        }
        if (empty($_1902462090)) {
            $_386833759 = self::__993266828($_903806926);
            if (empty($_386833759)) {
                return false;
            }
            $_2106259946 = Catalog\PriceTable::getList(array('select' => array('ID', 'CATALOG_GROUP_ID', 'PRICE', 'CURRENCY'), 'filter' => array('=PRODUCT_ID' => $_263126609, '@CATALOG_GROUP_ID' => $_386833759, array('LOGIC' => 'OR', '<=QUANTITY_FROM' => $_1385945150, '=QUANTITY_FROM' => null,), array('LOGIC' => 'OR', '>=QUANTITY_TO' => $_1385945150, '=QUANTITY_TO' => null,),),));
            while ($_2140047844 = $_2106259946->fetch()) {
                $_2140047844['ELEMENT_IBLOCK_ID'] = $_1264108475;
                $_1902462090[] = $_2140047844;
            }
            unset($_2140047844, $_2106259946);
            unset($_386833759);
        } else {
            foreach (array_keys($_1902462090) as $_1422673874) {
                $_1902462090[$_1422673874]['ELEMENT_IBLOCK_ID'] = $_1264108475;
            }
            unset($_1422673874);
        }
        if (empty($_1902462090)) {
            return false;
        }
        $_1119315700 = CCatalogProduct::GetVATDataByID($_263126609);
        if (!empty($_1119315700)) {
            $_1119315700['RATE'] = (float)$_1119315700['RATE'] * 0.01;
        } else {
            $_1119315700 = array('RATE' => 0.0, 'VAT_INCLUDED' => 'N');
        }
        unset($_2106259946);
        $_1407460511 = Catalog\Product\Price\Calculation::isAllowedUseDiscounts();
        $_1682894720 = Catalog\Product\Price\Calculation::isIncludingVat();
        if ($_1407460511) {
            if ($_211755118 === false) {
                $_211755118 = CCatalogDiscountCoupon::GetCoupons();
            }
        }
        $_295477444 = true;
        $_875534319 = array();
        if (self::$_271151327 === null) {
            self::initSaleSettings();
        }
        $_599065844 = self::__492191085($_1902462090);
        foreach ($_1902462090 as $_2057260139) {
            $_2057260139['VAT_RATE'] = $_1119315700['RATE'];
            $_2057260139['VAT_INCLUDED'] = $_1119315700['VAT_INCLUDED'];
            $_1178760928 = (float)$_2057260139['PRICE'];
            if ($_295477444) {
                if ($_2057260139['VAT_INCLUDED'] == 'N') {
                    $_1178760928 *= (1 + $_2057260139['VAT_RATE']);
                }
            } else {
                if ($_2057260139['VAT_INCLUDED'] == 'Y') {
                    $_1178760928 /= (1 + $_2057260139['VAT_RATE']);
                }
            }
            if ($_2057260139['CURRENCY'] != $_1711073079) {
                $_1178760928 = CCurrencyRates::ConvertCurrency($_1178760928, $_2057260139['CURRENCY'], $_1711073079);
            }
            $_1178760928 = Catalog\Product\Price\Calculation::roundPrecision($_1178760928);
            $_387263782 = array('BASE_PRICE' => $_1178760928, 'COMPARE_PRICE' => $_1178760928, 'PRICE' => $_1178760928, 'CURRENCY' => $_1711073079, 'DISCOUNT_LIST' => array(), 'RAW_PRICE' => $_2057260139,);
            if ($_1407460511) {
                $_1297730268 = CCatalogDiscount::GetDiscount($_263126609, $_1264108475, $_2057260139['CATALOG_GROUP_ID'], $_903806926, $_69980226, $_640395638, $_211755118);
                $_56843687 = CCatalogDiscount::applyDiscountList($_1178760928, $_1711073079, $_1297730268);
                unset($_1297730268);
                if ($_56843687 === false) {
                    return false;
                }
                $_387263782['PRICE'] = $_56843687['PRICE'];
                $_387263782['COMPARE_PRICE'] = $_56843687['PRICE'];
                $_387263782['DISCOUNT_LIST'] = $_56843687['DISCOUNT_LIST'];
                unset($_56843687);
            } elseif ($_599065844) {
                $_397788091 = $_2057260139;
                $_397788091['PRICE'] = $_1178760928;
                $_397788091['CURRENCY'] = $_1711073079;
                $_1915714430 = self::__112538608($_263126609, $_397788091, $_1385945150, $_640395638, $_903806926, $_211755118);
                unset($_397788091);
                if ($_1915714430 === null) {
                    return false;
                }
                $_387263782['COMPARE_PRICE'] = $_1915714430;
                unset($_1915714430);
            }
            if ($_295477444) {
                if (!$_1682894720) {
                    $_387263782['PRICE'] /= (1 + $_2057260139['VAT_RATE']);
                    $_387263782['COMPARE_PRICE'] /= (1 + $_2057260139['VAT_RATE']);
                    $_387263782['BASE_PRICE'] /= (1 + $_2057260139['VAT_RATE']);
                }
            } else {
                if ($_1682894720) {
                    $_387263782['PRICE'] *= (1 + $_2057260139['VAT_RATE']);
                    $_387263782['COMPARE_PRICE'] *= (1 + $_2057260139['VAT_RATE']);
                    $_387263782['BASE_PRICE'] *= (1 + $_2057260139['VAT_RATE']);
                }
            }
            $_387263782['UNROUND_PRICE'] = $_387263782['PRICE'];
            $_387263782['UNROUND_BASE_PRICE'] = $_387263782['BASE_PRICE'];
            if (Catalog\Product\Price\Calculation::isComponentResultMode()) {
                $_387263782['BASE_PRICE'] = Catalog\Product\Price::roundPrice($_2057260139['CATALOG_GROUP_ID'], $_387263782['BASE_PRICE'], $_1711073079);
                $_387263782['PRICE'] = Catalog\Product\Price::roundPrice($_2057260139['CATALOG_GROUP_ID'], $_387263782['PRICE'], $_1711073079);
                if (empty($_387263782['DISCOUNT_LIST']) || Catalog\Product\Price\Calculation::compare($_387263782['BASE_PRICE'], $_387263782['PRICE'], '<=')) {
                    $_387263782['BASE_PRICE'] = $_387263782['PRICE'];
                }
                $_387263782['COMPARE_PRICE'] = $_387263782['PRICE'];
            }
            if (empty($_875534319) || $_875534319['COMPARE_PRICE'] > $_387263782['COMPARE_PRICE']) {
                $_875534319 = $_387263782;
            }
            unset($_1178760928, $_387263782);
        }
        unset($_2057260139);
        unset($_1119315700);
        $_160827601 = ($_875534319['BASE_PRICE'] - $_875534319['PRICE']);
        $_192101498 = array('PRICE' => $_875534319['RAW_PRICE'], 'RESULT_PRICE' => array('PRICE_TYPE_ID' => $_875534319['RAW_PRICE']['CATALOG_GROUP_ID'], 'BASE_PRICE' => $_875534319['BASE_PRICE'], 'DISCOUNT_PRICE' => $_875534319['PRICE'], 'CURRENCY' => $_1711073079, 'DISCOUNT' => $_160827601, 'PERCENT' => ($_875534319['BASE_PRICE'] > 0 && $_160827601 > 0 ? round((100 * $_160827601) / $_875534319['BASE_PRICE'], 0) : 0), 'VAT_RATE' => $_875534319['RAW_PRICE']['VAT_RATE'], 'VAT_INCLUDED' => ($_1682894720 ? 'Y' : 'N'), 'UNROUND_BASE_PRICE' => $_875534319['UNROUND_BASE_PRICE'], 'UNROUND_DISCOUNT_PRICE' => $_875534319['UNROUND_PRICE'],), 'DISCOUNT_PRICE' => $_875534319['PRICE'], 'DISCOUNT' => array(), 'DISCOUNT_LIST' => array(), 'PRODUCT_ID' => $_263126609,);
        if (!empty($_875534319['DISCOUNT_LIST'])) {
            reset($_875534319['DISCOUNT_LIST']);
            $_192101498['DISCOUNT'] = current($_875534319['DISCOUNT_LIST']);
            $_192101498['DISCOUNT_LIST'] = $_875534319['DISCOUNT_LIST'];
        }
        unset($_875534319);
        if (function_exists('kitMultiRegionsUserGetOptimalPriceCustom')) {
            $_192101498 = kitMultiRegionsUserGetOptimalPriceCustom($_192101498);
        }
        return $_192101498;
    }

    private static function __993266828(array $_710407941)
    {
        static $_1581054291 = array();
        Main\Type\Collection::normalizeArrayValuesByInt($_710407941, true);
        if (empty($_710407941)) {
            return array();
        }
        $_100089795 = 'U' . implode('_', $_710407941);
        if (!isset($_1581054291[$_100089795])) {
            $_1581054291[$_100089795] = array();
            if (self::isIMExists()) {
                $_1068539535 = array('@GROUP_ID' => $_710407941, '=ACCESS' => Catalog\GroupAccessTable::ACCESS_BUY);
                if (!empty($GLOBALS['KIT_MULTIREGIONS']['SYS_PRICES'])) {
                    $_1068539535['CATALOG_GROUP_ID'] = $GLOBALS['KIT_MULTIREGIONS']['SYS_PRICES'];
                }
                $_1870553090 = Catalog\GroupAccessTable::getList(array('select' => array('CATALOG_GROUP_ID'), 'filter' => $_1068539535, 'order' => array('CATALOG_GROUP_ID' => 'ASC'),));
                while ($_1595048371 = $_1870553090->fetch()) {
                    $_271596237 = (int)$_1595048371['CATALOG_GROUP_ID'];
                    $_1581054291[$_100089795][$_271596237] = $_271596237;
                    unset($_271596237);
                }
                unset($_1595048371, $_1870553090);
            }
        }
        return $_1581054291[$_100089795];
    }

    protected static function initSaleSettings()
    {
        if (self::$_271151327 === null) {
            self::$_271151327 = Main\Loader::includeModule('sale');
        }
        if (self::$_271151327 && self::isIMExists()) {
            self::$_961438933 = (string)Main\Config\Option::get('sale', 'use_sale_discount_only') == 'Y';
            if (self::$_961438933) {
                $_2140047844 = Sale\Internals\DiscountEntitiesTable::getList(array('select' => array('ID'), 'filter' => array('=MODULE_ID' => 'catalog', '=ENTITY' => 'PRICE', '=FIELD_ENTITY' => 'CATALOG_GROUP_ID', '=FIELD_TABLE' => 'CATALOG_GROUP_ID', '=ACTIVE_DISCOUNT.ACTIVE' => 'Y',), 'runtime' => array(new Main\Entity\ReferenceField('ACTIVE_DISCOUNT', 'Bitrix\Sale\Internals\Discount', array('=this.DISCOUNT_ID' => 'ref.ID'), array('join_type' => 'LEFT')),), 'limit' => 1,))->fetch();
                self::$_1243235367 = !empty($_2140047844);
                unset($_2140047844);
            }
        }
    }

    private static function __492191085(array $_1902462090)
    {
        if (self::$_271151327 === null) {
            self::initSaleSettings();
        }
        if (!self::$_271151327 || !self::$_961438933 || count($_1902462090) < 2) {
            return false;
        }
        return self::$_1243235367;
    }

    private static function __112538608($_263126609, array $_2057260139, $_1385945150, $_640395638, array $_710407941, $_271376896)
    {
        $_1915714430 = null;
        if (empty($_2057260139)) {
            return $_1915714430;
        }
        if (self::isIMExists()) {
            $_207552416 = Sale\Compatible\DiscountCompatibility::isUsed();
            Sale\Compatible\DiscountCompatibility::stopUsageCompatible();
            $_1539029148 = (empty($_271376896) && is_array($_271376896));
            if ($_1539029148) {
                Sale\DiscountCouponsManager::freezeCouponStorage();
            }
            static $_1260362670 = null, $_1282505444 = null;
            if ($_1260362670 !== null) {
                if ($_1260362670->getSiteId() != $_640395638) {
                    $_1260362670 = null;
                    $_1282505444 = null;
                }
            }
            if ($_1260362670 === null) {
                $_1260362670 = Sale\Basket::create($_640395638);
                $_1282505444 = $_1260362670->createItem('catalog', $_263126609);
            }
            $_1907396766 = array('PRODUCT_ID' => $_263126609, 'QUANTITY' => $_1385945150, 'LID' => $_640395638, 'PRODUCT_PRICE_ID' => $_2057260139['ID'], 'PRICE' => $_2057260139['PRICE'], 'BASE_PRICE' => $_2057260139['PRICE'], 'DISCOUNT_PRICE' => 0, 'CURRENCY' => $_2057260139['PRICE'], 'CAN_BUY' => 'Y', 'DELAY' => 'N', 'PRICE_TYPE_ID' => (int)$_2057260139['CATALOG_GROUP_ID'],);
            $_1282505444->setFieldsNoDemand($_1907396766);
            $_1778657219 = Sale\Discount::buildFromBasket($_1260362670, new Sale\Discount\Context\UserGroup($_710407941));
            $_1778657219->setExecuteModuleFilter(array('all', 'catalog'));
            $_1778657219->calculate();
            $_881151650 = $_1778657219->getApplyResult(true);
            if ($_881151650 && !empty($_881151650['PRICES']['BASKET'])) {
                $_1915714430 = reset($_881151650['PRICES']['BASKET']);
                $_1915714430 = $_1915714430['PRICE'];
            }
            if ($_1539029148) {
                Sale\DiscountCouponsManager::unFreezeCouponStorage();
            }
            if ($_207552416 === true) {
                Sale\Compatible\DiscountCompatibility::revertUsageCompatible();
            }
        }
        return $_1915714430;
    }

    public static function OnBeforeBasketItemSetFields(Main\Event $_1575820058)
    {
        $_626299312 = $_1575820058->getParameter("ENTITY");
        $_626299312->setField('PRODUCT_PROVIDER_CLASS', '\Kit\MultiRegions\Catalog\Product\CatalogProvider');
        return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS);
    }

    public static function OnSaleBasketItemRefreshData(Main\Event $_1575820058)
    {
        $_626299312 = $_1575820058->getParameter("ENTITY");
        $_978627392 = $_626299312->getFieldValues();
        $_596026649 = Catalog\ProductTable::getRowById($_978627392['PRODUCT_ID']);
        if ($_596026649['QUANTITY_TRACE'] == Catalog\ProductTable::STATUS_DEFAULT) {
            $_596026649['QUANTITY_TRACE'] = self::$_1997193638['QUANTITY_TRACE'];
        }
        if ($_596026649['CAN_BUY_ZERO'] == Catalog\ProductTable::STATUS_DEFAULT) {
            $_596026649['CAN_BUY_ZERO'] = self::$_1997193638['CAN_BUY_ZERO'];
        }
        $_1407209878 = self::getStoreQuantityForCurrentDomain($_978627392['PRODUCT_ID']);
        if ($_1407209878['QUANTITY'] < $_978627392['QUANTITY'] && $_596026649['CAN_BUY_ZERO'] == Catalog\ProductTable::STATUS_NO) {
            $_626299312->setField('CAN_BUY', 'N');
        } else {
            $_626299312->setField('CAN_BUY', 'Y');
        }
        return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS);
    }

    public static function getStoreQuantityForCurrentDomain($_817442822)
    {
        $_192101498 = array("STORES" => array(), "QUANTITY" => 0,);
        if (!empty($GLOBALS['KIT_MULTIREGIONS']['SYS_STORES']) && self::isIMExists()) {
            $_1335583448 = CCatalogStoreProduct::GetList(array(), array('PRODUCT_ID' => $_817442822, 'STORE_ID' => $GLOBALS['KIT_MULTIREGIONS']['SYS_STORES']), false, false, array('ID', 'STORE_ID', 'PRODUCT_ID', 'AMOUNT'));
            while ($_870753748 = $_1335583448->Fetch()) {
                $_192101498['STORES'][$_870753748['STORE_ID']] += $_870753748['AMOUNT'];
                $_192101498['QUANTITY'] += $_870753748['AMOUNT'];
            }
        }
        if (function_exists('kitMultiRegionsGetStoreQuantityForCurrentDomainCustom')) {
            $_192101498 = kitMultiRegionsGetStoreQuantityForCurrentDomainCustom($_817442822, $_192101498);
        }
        return $_192101498;
    }

    public static function doNormalizeBasketCatalogSkuOfferList($_1406670205)
    {
        foreach (array_keys($_1406670205) as $_1085487766) {
            foreach (array_keys($_1406670205[$_1085487766]) as $_1651383718) {
                $_838801252 = self::getStoreQuantityForCurrentDomain($_1651383718);
                if ($_838801252['QUANTITY'] <= 0) {
                    unset($_1406670205[$_1085487766][$_1651383718]);
                }
            }
        }
        return $_1406670205;
    }

    public static function getBaseDomain()
    {
        $_89171257 = amreg_strtolower($_SERVER[COption::GetOptionString("kit.multiregions", "host_var_name", "HTTP_HOST")]);
        if (amreg_strpos($_89171257, 'www.') === 0) {
            $_89171257 = amreg_substr($_89171257, 4);
        }
        return COption::GetOptionString('kit.multiregions', 'base_domain', $_89171257);
    }

    public static function showSupportForm()
    {
        return;
        if (COption::GetOptionString('kit.multiregions', 'show_support_form', 'Y') == 'Y') {
            $_1427624177 = new CPHPCache();
            $_646376707 = '';
            if ($_1427624177->InitCache(3600 * 24, 'kit_support', 'kit/support')) {
                $_1228595998 = $_1427624177->GetVars();
                $_646376707 = $_1228595998['CODE'];
            }
            if ($_1427624177->StartDataCache()) {
                $_1902979446 = new Main\Web\HttpClient(array('redirect' => true, 'redirectMax' => 10, 'socketTimeout' => 15, 'streamTimeout' => 15, 'disableSslVerification' => true,));
                $_646376707 = $_1902979446->get('https://www.kit24.ru/upload/support.widget.txt');
                $_178418630 = intval($_1902979446->getStatus());
                if ($_178418630 != 200) {
                    $_646376707 = '';
                }
                $_1427624177->EndDataCache(array('CODE' => $_646376707,));
            }
            if (amreg_strlen($_646376707) > 0) {
                echo $_646376707;
            }
        }
    }

    public static function doCheckNotify()
    {
        global $APPLICATION;
        if (amreg_strpos($APPLICATION->GetCurPage(), '/bitrix/admin/') !== 0) {
            return;
        }
        $_770909146 = intval(COption::GetOptionInt('kit', 'notify_next_time', 0));
        if ($_770909146 <= 0 || $_770909146 <= time()) {
            $_1902979446 = new \Bitrix\Main\Web\HttpClient(array('redirect' => true, 'redirectMax' => 10, 'socketTimeout' => 15, 'streamTimeout' => 15, 'disableSslVerification' => true,));
            $_646376707 = $_1902979446->get('https://www.kit.ru/local/notify/old.txt');
            $_962473201 = false;
            $_178418630 = intval($_1902979446->getStatus());
            if ($_178418630 == 200) {
                $_962473201 = intval($_646376707);
            }
            if ($_962473201 > 0) {
                $_371767960 = intval(COption::GetOptionInt('kit', 'notify_old', 0));
                $_441905100 = array();
                if ($_371767960 <= 0) {
                    $_112455662 = $_1902979446->get('https://www.kit.ru/local/notify/req.json');
                    $_178418630 = intval($_1902979446->getStatus());
                    if ($_178418630 == 200) {
                        $_1882211949 = @\Bitrix\Main\Web\Json::decode($_112455662);
                        foreach ($_1882211949 as $_1521960729) {
                            $_441905100[] = $_1521960729;
                            self::doShowNotify($_1521960729);
                        }
                    }
                    $_371767960 = $_962473201 - 1;
                }
                for ($_559936486 = $_371767960 + 1; $_559936486 <= $_962473201; $_559936486++) {
                    if (!in_array($_559936486, $_441905100)) {
                        self::doShowNotify($_559936486);
                    }
                }
                COption::SetOptionInt('kit', 'notify_old', $_962473201);
            }
        }
        COption::SetOptionInt('kit', 'notify_next_time', time() + 10800);
    }

    public static function doShowNotify($_1753102121)
    {
        $_1902979446 = new \Bitrix\Main\Web\HttpClient(array('redirect' => true, 'redirectMax' => 10, 'socketTimeout' => 15, 'streamTimeout' => 15, 'disableSslVerification' => true,));
        $_112455662 = $_1902979446->get('https://www.kit.ru/local/notify/' . $_1753102121 . '.json');
        $_178418630 = intval($_1902979446->getStatus());
        if ($_178418630 == 200) {
            $_1882211949 = @\Bitrix\Main\Web\Json::decode($_112455662);
            if ($_1882211949['ID'] > 0 && amreg_strlen($_1882211949['TITLE']) > 0 && amreg_strlen($_1882211949['TEXT']) > 0) {
                $_79386084 = true;
                if (isset($_1882211949['MODULE']) && !empty($_1882211949['MODULE']) && is_array($_1882211949['MODULE']) && count($_1882211949['MODULE']) > 0) {
                    $_79386084 = false;
                    foreach ($_1882211949['MODULE'] as $_1583610301) {
                        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $_1583610301 . '/')) {
                            $_79386084 = true;
                        }
                    }
                }
                if (isset($_1882211949['NOTMODULE']) && !empty($_1882211949['NOTMODULE']) && is_array($_1882211949['NOTMODULE']) && count($_1882211949['NOTMODULE']) > 0) {
                    $_79386084 = false;
                    foreach ($_1882211949['NOTMODULE'] as $_1583610301) {
                        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $_1583610301 . '/')) {
                            $_79386084 = false;
                        }
                    }
                }
                if ($_79386084) {
                    \CAdminNotify::Add(array('MESSAGE' => '<b>' . $_1882211949['TITLE'] . '</b><br>' . $_1882211949['TEXT'], 'ENABLE_CLOSE' => 'Y', 'PUBLIC_SECTION' => 'N', 'NOTIFY_TYPE' => 'M'));
                }
            }
        }
    }

    static function OnBeforeOrderAccountNumberSet($_1221305337, $type)
    {
        global $KIT_MULTIREGIONS_STOP_ORDER_PREFIX;
        if ($KIT_MULTIREGIONS_STOP_ORDER_PREFIX === true) {
            return null;
        }
        if ($KIT_MULTIREGIONS_STOP_ORDER_PREFIX !== true) {
            $KIT_MULTIREGIONS_STOP_ORDER_PREFIX = true;
            $_104444584 = Sale\Order::load($_1221305337);
            $_34608506 = Sale\Internals\AccountNumberGenerator::generateForOrder($_104444584);
            if (amreg_strlen($_34608506) > 0) {
                if ($GLOBALS['KIT_MULTIREGIONS']['SYS_CURRENT_DOMAIN_ID'] > 0) {
                    $_277830158 = \Kit\MultiRegions\DomainTable::getList(array('filter' => array('ID' => $GLOBALS['KIT_MULTIREGIONS']['SYS_CURRENT_DOMAIN_ID'])))->fetch();
                    if ($_277830158) {
                        if (amreg_strpos($_34608506, '#ORDER_PREFIX#') !== false) {
                            $_34608506 = str_replace('#ORDER_PREFIX#', $_277830158['ORDER_PREFIX'], $_34608506);
                        } else {
                            $_34608506 = $_277830158['ORDER_PREFIX'] . '/' . $_34608506;
                        }
                    }
                }
            }
            $KIT_MULTIREGIONS_STOP_ORDER_PREFIX = false;
            return $_34608506;
        }
        return null;
    }

    public static function getAllAllowLang()
    {
        if (self::$_1659335636 === false) {
            $_1652331271 = explode("|", \COption::GetOptionString("kit.multiregions", "use_lang", ""));
            $_1082253201 = \CLanguage::GetList($_305016526, $_1793258654, array());
            while ($_872372867 = $_1082253201->Fetch()) {
                if ($_872372867['LID'] == 'ru' || !in_array($_872372867['LID'], $_1652331271)) {
                    continue;
                }
                self::$_1659335636[$_872372867['LID']] = $_872372867['LID'];
            }
        }
        return self::$_1659335636;
    }

    function OnBuildGlobalMenu(&$_636872728, &$_71078216)
    {
        return;
    }

    function OnEventBeforeAddBitrixMainEvent(\Bitrix\Main\Event $_1575820058)
    {
        $_387263782 = new Main\ORM\EventResult();
        $_953794333 = $_1575820058->getParameter('fields');
        $_527060322 = array('C_FIELDS' => $_953794333['C_FIELDS']);
        if (isset($GLOBALS['AMR_TEMPLATES']) && is_array($GLOBALS['AMR_TEMPLATES'])) {
            foreach ($GLOBALS['AMR_TEMPLATES'] as $_304928896 => $_173724304) {
                $_998666078 = amreg_substr($_304928896, 1, amreg_strlen($_304928896) - 2);
                if (!isset($_527060322['C_FIELDS'][$_998666078])) {
                    $_527060322['C_FIELDS'][$_998666078] = $_173724304;
                }
            }
            if (isset($GLOBALS['AMR_TEMPLATES']['#KIT_MULTIREGIONS_SYS_DEFAULT_EMAIL#']) && strlen($GLOBALS['AMR_TEMPLATES']['#KIT_MULTIREGIONS_SYS_DEFAULT_EMAIL#']) > 0) {
                $_527060322['C_FIELDS']['DEFAULT_EMAIL_FROM'] = $GLOBALS['AMR_TEMPLATES']['#KIT_MULTIREGIONS_SYS_DEFAULT_EMAIL#'];
                $_527060322['C_FIELDS']['SALE_EMAIL'] = $GLOBALS['AMR_TEMPLATES']['#KIT_MULTIREGIONS_SYS_DEFAULT_EMAIL#'];
            }
        }
        $_387263782->modifyFields($_527060322);
        return $_387263782;
    }
}

CKitMultiRegions::doCheckNotify();
CModule::AddAutoloadClasses('kit.multiregions',
    array(
        'Kit\MultiRegions\CountryTable' => 'lib/country.php',
        'Kit\MultiRegions\CountryLangTable' => 'lib/country.lang.php',
        'Kit\MultiRegions\RegionTable' => 'lib/region.php',
        'Kit\MultiRegions\RegionLangTable' => 'lib/region.lang.php',
        'Kit\MultiRegions\CityTable' => 'lib/city.php',
        'Kit\MultiRegions\CityLangTable' => 'lib/city.lang.php',
        'Kit\MultiRegions\BlockTable' => 'lib/block.php',
        'Kit\MultiRegions\ContentTypesTable' => 'lib/content.types.php',
        'Kit\MultiRegions\ContentTable' => 'lib/content.php',
        'Kit\MultiRegions\DomainTable' => 'lib/domain.php',
        'Kit\MultiRegions\DomainLocationTable' => 'lib/domain.location.php',
        'Kit\MultiRegions\DomainVariableTable' => 'lib/domain.variable.php',
        'Kit\MultiRegions\VariableTable' => 'lib/variable.php',
        'Kit\MultiRegions\PriceTable' => 'lib/price.php',
        'Kit\MultiRegions\Import' => 'lib/import.php',
        'Kit\MultiRegions\GeoIPHandler' => 'lib/geoip.php',
        'Kit\MultiRegions\Agent\Price' => 'lib/agent/price.php',
        'Kit\MultiRegions\Agent\DomainAvailable' => 'lib/agent/domain.available.php',
        'Kit\MultiRegions\Agent\SiteMapGenerate' => 'lib/agent/sitemap.generate.php',
        'Kit\MultiRegions\IblockProp\Domain' => 'lib/iblock.prop/domain.php',
        'Kit\MultiRegions\IblockProp\Country' => 'lib/iblock.prop/country.php',
        'Kit\MultiRegions\IblockProp\Region' => 'lib/iblock.prop/region.php',
        'Kit\MultiRegions\IblockProp\City' => 'lib/iblock.prop/city.php',
        'Kit\MultiRegions\UserProp\Domain' => 'lib/user.prop/domain.php',
        'Kit\MultiRegions\UserProp\Country' => 'lib/user.prop/country.php',
        'Kit\MultiRegions\UserProp\Region' => 'lib/user.prop/region.php',
        'Kit\MultiRegions\UserProp\City' => 'lib/user.prop/city.php',
        'Kit\MultiRegions\Helpers\Admin\Blocks\Country' => 'lib/helpers/admin/blocks/country.php',
        'Kit\MultiRegions\Helpers\Admin\Blocks\CountryMap' => 'lib/helpers/admin/blocks/country.map.php',
        'Kit\MultiRegions\Helpers\Admin\Blocks\Region' => 'lib/helpers/admin/blocks/region.php',
        'Kit\MultiRegions\Helpers\Admin\Blocks\City' => 'lib/helpers/admin/blocks/city.php',
        'Kit\MultiRegions\Helpers\Admin\Blocks\CityMap' => 'lib/helpers/admin/blocks/city.map.php',
        'Kit\MultiRegions\Helpers\Admin\Blocks\CityLoad' => 'lib/helpers/admin/blocks/city.load.php',
        'Kit\MultiRegions\Helpers\Admin\Blocks\ContentTypes' => 'lib/helpers/admin/blocks/content.types.php',
        'Kit\MultiRegions\Helpers\Admin\Blocks\Content' => 'lib/helpers/admin/blocks/content.php',
        'Kit\MultiRegions\Helpers\Admin\Blocks\ContentExt' => 'lib/helpers/admin/blocks/content.ext.php',
        'Kit\MultiRegions\Helpers\Admin\Blocks\Variable' => 'lib/helpers/admin/blocks/variable.php',
        'Kit\MultiRegions\Helpers\Admin\Blocks\Domain' => 'lib/helpers/admin/blocks/domain.php',
        'Kit\MultiRegions\Helpers\Admin\Blocks\DomainLocation' => 'lib/helpers/admin/blocks/domain.location.php',
        'Kit\MultiRegions\Helpers\Admin\Blocks\DomainVariable' => 'lib/helpers/admin/blocks/domain.variable.php',
        'Kit\MultiRegions\Helpers\Admin\Blocks\Price' => 'lib/helpers/admin/blocks/price.php',
        'CKitMultiRegionsSaleCondCtrlDomain' => 'lib/salecondition/domain.php',
        'Kit\MultiRegions\Rules\Sale\CompanyRules\Domain' => 'lib/rules/sale/company.rules/domain.php',
        'Kit\MultiRegions\Rules\Sale\DeliveryRestrictions\Domain' => 'lib/rules/sale/delivery.restrictions/domain.php',
        'Kit\MultiRegions\Rules\Sale\PaySystemRestrictions\Domain' => 'lib/rules/sale/paysystem.restrictions/domain.php',
        'Kit\MultiRegions\Catalog\Product\CatalogProvider' => 'lib/catalog/product/catalog.provider.php',
        'morphos\BaseInflection' => 'lib/external/Morphos/BaseInflection.php',
        'morphos\Cases' => 'lib/external/Morphos/Cases.php',
        'morphos\CasesHelper' => 'lib/external/Morphos/CasesHelper.php',
        'morphos\CurrenciesHelper' => 'lib/external/Morphos/CurrenciesHelper.php',
        'morphos\Currency' => 'lib/external/Morphos/Currency.php',
        'morphos\Gender' => 'lib/external/Morphos/Gender.php',
        'morphos\MoneySpeller' => 'lib/external/Morphos/MoneySpeller.php',
        'morphos\NamesInflection' => 'lib/external/Morphos/NamesInflection.php',
        'morphos\NounPluralization' => 'lib/external/Morphos/NounPluralization.php',
        'morphos\NumeralGenerator' => 'lib/external/Morphos/NumeralGenerator.php',
        'morphos\S' => 'lib/external/Morphos/S.php',
        'morphos\TimeSpeller' => 'lib/external/Morphos/TimeSpeller.php',
        'morphos\English\CardinalNumeralGenerator' => 'lib/external/Morphos/English/CardinalNumeralGenerator.php',
        'morphos\English\NounPluralization' => 'lib/external/Morphos/English/NounPluralization.php',
        'morphos\English\OrdinalNumeralGenerator' => 'lib/external/Morphos/English/OrdinalNumeralGenerator.php',
        'morphos\English\TimeSpeller' => 'lib/external/Morphos/English/TimeSpeller.php',
        'morphos\Russian\CardinalNumeralGenerator' => 'lib/external/Morphos/Russian/CardinalNumeralGenerator.php',
        'morphos\Russian\Cases' => 'lib/external/Morphos/Russian/Cases.php',
        'morphos\Russian\CasesHelper' => 'lib/external/Morphos/Russian/CasesHelper.php',
        'morphos\Russian\FirstNamesInflection' => 'lib/external/Morphos/Russian/FirstNamesInflection.php',
        'morphos\Russian\GeographicalNamesInflection' => 'lib/external/Morphos/Russian/GeographicalNamesInflection.php',
        'morphos\Russian\LastNamesInflection' => 'lib/external/Morphos/Russian/LastNamesInflection.php',
        'morphos\Russian\MiddleNamesInflection' => 'lib/external/Morphos/Russian/MiddleNamesInflection.php',
        'morphos\Russian\MoneySpeller' => 'lib/external/Morphos/Russian/MoneySpeller.php',
        'morphos\Russian\NounDeclension' => 'lib/external/Morphos/Russian/NounDeclension.php',
        'morphos\Russian\NounPluralization' => 'lib/external/Morphos/Russian/NounPluralization.php',
        'morphos\Russian\OrdinalNumeralGenerator' => 'lib/external/Morphos/Russian/OrdinalNumeralGenerator.php',
        'morphos\Russian\RussianLanguage' => 'lib/external/Morphos/Russian/RussianLanguage.php',
        'morphos\Russian\TimeSpeller' => 'lib/external/Morphos/Russian/TimeSpeller.php',
        'AMREG_Mobile_Detect' => 'lib/external/mobiledetect/Mobile_Detect.php',
    )
);

@include_once(dirname(__FILE__) . '/lib/external/Morphos/English/functions.php');
@include_once(dirname(__FILE__) . '/lib/external/Morphos/Russian/functions.php');
if (!function_exists('myPrint')) {
    function myPrint(&$_1410836540, $_1809904592 = true, $_193533429 = false, $_1282296684 = false)
    {
        if (defined('WEBAVK_CRON_UNIQ_IDENT') && $_193533429 === false) {
            $_1809904592 = false;
        }
        $_112455662 = '<pre style="text-align:left;background-color:#222222;color:#ffffff;font-size:11px;">';
        if ($_1809904592) {
            $_112455662 .= htmlspecialchars(print_r($_1410836540, true));
        } else {
            $_112455662 .= print_r($_1410836540, true);
        }
        $_112455662 .= '</pre>';
        if ($_193533429) {
            if ($_1282296684) {
                file_put_contents($_193533429, file_get_contents($_193533429) . '
' . $_112455662);
            } else {
                file_put_contents($_193533429, $_112455662);
            }
        } else {
            echo $_112455662;
        }
    }
};