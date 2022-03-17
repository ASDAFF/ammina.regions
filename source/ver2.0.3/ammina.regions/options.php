<?

use Bitrix\Main\Localization\Loc;

$module_id = "ammina.regions";
CModule::IncludeModule($module_id);

if (CAmminaRegions::getTestPeriodInfo() == \Bitrix\Main\Loader::MODULE_DEMO) {
	CAdminMessage::ShowMessage(array("MESSAGE" => Loc::getMessage("AMMINA_REGIONS_SYS_MODULE_IS_DEMO"), "HTML" => true));
} elseif (CAmminaRegions::getTestPeriodInfo() == \Bitrix\Main\Loader::MODULE_DEMO_EXPIRED) {
	CAdminMessage::ShowMessage(array("MESSAGE" => Loc::getMessage("AMMINA_REGIONS_SYS_MODULE_IS_DEMO_EXPIRED"), "HTML" => true));
}

$modulePermissions = $APPLICATION->GetGroupRight($module_id);
if ($modulePermissions >= "R") {
	global $MESS;
	Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/options.php");
	Loc::loadMessages(__FILE__);

	$arAllCountryCode = explode(",", "AD,AE,AF,AG,AI,AL,AM,AO,AQ,AR,AS,AT,AU,AW,AX,AZ,BA,BB,BD,BE,BF,BG,BH,BI,BJ,BL,BM,BN,BO,BQ,BR,BS,BT,BW,BY,BZ,CA,CC,CD,CF,CG,CH,CI,CK,CL,CM,CN,CO,CR,CU,CV,CW,CX,CY,CZ,DE,DJ,DK,DM,DO,DZ,EC,EE,EG,ER,ES,ET,FI,FJ,FK,FM,FO,FR,GA,GB,GD,GE,GF,GG,GH,GI,GL,GM,GN,GP,GQ,GR,GS,GT,GU,GW,GY,HK,HN,HR,HT,HU,ID,IE,IL,IM,IN,IO,IQ,IR,IS,IT,JE,JM,JO,JP,KE,KG,KH,KI,KM,KN,KP,KR,KW,KY,KZ,LA,LB,LC,LI,LK,LR,LS,LT,LU,LV,LY,MA,MC,MD,ME,MF,MG,MH,MK,ML,MM,MN,MO,MP,MQ,MR,MS,MT,MU,MV,MW,MX,MY,MZ,NA,NC,NE,NF,NG,NI,NL,NO,NP,NR,NU,NZ,OM,PA,PE,PF,PG,PH,PK,PL,PM,PN,PR,PS,PT,PW,PY,QA,RE,RO,RS,RU,RW,SA,SB,SC,SD,SE,SG,SH,SI,SJ,SK,SL,SM,SN,SO,SR,SS,ST,SV,SX,SY,SZ,TC,TD,TF,TG,TH,TJ,TK,TL,TM,TN,TO,TR,TT,TV,TW,TZ,UA,UG,UM,US,UY,UZ,VA,VC,VE,VG,VI,VN,VU,WF,WS,XK,YE,YT,ZA,ZM,ZW");
	$arAllCountry = array();
	foreach ($arAllCountryCode as $strCode) {
		$arAllCountry[$strCode] = Loc::getMessage("ammina.regions_OPTION_COUNTRY_" . $strCode);
	}
	asort($arAllCountry, SORT_ASC);
	$arAllPropList = array();
	$rAllIblock = CIBlock::GetList(array(), array());
	while ($arAllIblock = $rAllIblock->Fetch()) {
		$arAllPropList[$arAllIblock['ID']] = array(
			"NAME" => "[" . $arAllIblock['ID'] . "] " . $arAllIblock['NAME'],
			"PROP" => array()
		);
	}
	$rProp = CIBlockProperty::GetList(array("SORT" => "ASC"), array("PROPERTY_TYPE" => "L"));
	while ($arProp = $rProp->Fetch()) {
		if (amreg_strlen($arProp['USER_TYPE']) >= 1) {
			continue;
		}
		$arAllPropList[$arProp['IBLOCK_ID']]['PROP'][$arProp['ID']] = "[" . $arProp['ID'] . "] " . $arProp['NAME'];
	}
	if ($REQUEST_METHOD == "GET" && amreg_strlen($RestoreDefaults) > 0 && $modulePermissions >= "W" && check_bitrix_sessid()) {
		COption::RemoveOption($module_id);
		$z = CGroup::GetList($v1 = "id", $v2 = "asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
		while ($zr = $z->Fetch()) {
			$APPLICATION->DelGroupRight($module_id, array($zr["ID"]));
		}
	}
	$strDomain = amreg_strtolower($_SERVER['HTTP_HOST']);
	if (amreg_strpos($strDomain, 'www.') === 0) {
		$strDomain = amreg_substr($strDomain, 4);
	}
	$b = "SORT";
	$o = "ASC";
	$arSitesSelect = array();
	$rSites = \CSite::GetList($b, $o, array());
	while ($arSite = $rSites->Fetch()) {
		$arSitesSelect[$arSite['LID']] = "[" . $arSite['LID'] . "] " . $arSite['NAME'];
	}

	$arPricesDefault = array();
	$arStoresDefault = array();
	if (\CAmminaRegions::isIMExists()) {
		$rPrices = \CCatalogGroup::GetList(
			array(
				"SORT" => "ASC",
				"NAME_LANG" => "ASC",
			)
		);
		while ($arPrice = $rPrices->Fetch()) {
			$arPricesDefault[$arPrice['ID']] = "[" . $arPrice['ID'] . "] " . $arPrice['NAME_LANG'];
		}
		$rStores = \CCatalogStore::GetList(array());
		while ($arStore = $rStores->Fetch()) {
			$arStoresDefault[$arStore['ID']] = "[" . $arStore['ID'] . "] " . $arStore['TITLE'];
		}
	}

	$arExternalLang = array();
	$arExternalLangForSyn = array("" => Loc::getMessage("ammina.regions_OPTION_PLEASE_SELECT"));
	$arExternalLangForSynList = array();
	$rLang = CLanguage::GetList($b, $o, array());
	while ($arLang = $rLang->Fetch()) {
		$arExternalLangForSyn[$arLang['LID']] = "[" . $arLang['LID'] . "] " . $arLang['NAME'];
		$arExternalLangForSynList[$arLang['LID']] = "[" . $arLang['LID'] . "] " . $arLang['NAME'];
		if ($arLang['LID'] == "ru") {
			continue;
		}
		$arExternalLang[$arLang['LID']] = "[" . $arLang['LID'] . "] " . $arLang['NAME'];
	}

	$arAllOptions = array(
		array("use_one_domain", Loc::getMessage("ammina.regions_OPTION_USE_ONE_DOMAIN"), "N", array("checkbox")),
		array("use_rus_domain", Loc::getMessage("ammina.regions_OPTION_USE_RUS_DOMAIN"), "N", array("checkbox")),
		array("host_var_name", Loc::getMessage("ammina.regions_OPTION_HOST_VAR_NAME"), "HTTP_HOST", array("text", 30)),
		array("only_exists_domains", Loc::getMessage("ammina.regions_OPTION_ONLY_EXISTS_DOMAINS"), "Y", array("checkbox")),
		array("use_lang", Loc::getMessage("ammina.regions_OPTION_USE_LANG"), "", array("selectboxm2"), $arExternalLang),
	);
	$arCurrentUseLang = explode("|", COption::GetOptionString($module_id, "use_lang", ""));
	$arCurrentUseLang[] = "ru";
	foreach ($arCurrentUseLang as $lang) {
		if (amreg_strlen($lang) <= 0) {
			continue;
		}
		$tmpLang = $arExternalLangForSyn;
		unset($tmpLang[$lang]);
		$arAllOptions[] = array(
			"lang_syn_" . $lang, Loc::getMessage("ammina.regions_OPTION_LANG_SYNONYM") . " " . $arExternalLangForSynList[$lang], "", array("selectbox"), $tmpLang
		);
	}
	$arAllOptions = array_merge($arAllOptions, array(
		//array("show_support_form", Loc::getMessage("ammina.regions_OPTION_SHOW_SUPPORT_FORM"), "Y", array("checkbox")),
		//array("separator", Loc::getMessage("ammina.regions_OPTION_SEPARATOR_PATHURL")),
		array("use_path_domain", Loc::getMessage("ammina.regions_OPTION_USE_PATH_DOMAIN"), "N", array("checkbox")),
		array("pathdomain_list", Loc::getMessage("ammina.regions_OPTION_PATHDOMAIN_LIST"), "", array("textarea", 6, 50)),
		array("separator", Loc::getMessage("ammina.regions_OPTION_SEPARATOR_ORDER_PREFIX")),
		array("use_order_prefix", Loc::getMessage("ammina.regions_OPTION_USE_ORDER_PREFIX"), "N", array("checkbox")),
		array("separator", Loc::getMessage("ammina.regions_OPTION_SEPARATOR_IMPORT")),
		array("not_load_ip_block", Loc::getMessage("ammina.regions_OPTION_NOT_LOAD_IP_BLOCK"), "Y", array("checkbox")),
		array("only_country", Loc::getMessage("ammina.regions_OPTION_ONLY_COUNTRY"), "", array("selectboxm"), $arAllCountry),
		array("iblock_prop_domains", Loc::getMessage("ammina.regions_OPTION_IBLOCK_PROP_DOMAINS"), "", array("selectboxprop"), $arAllPropList),
		array(
			"mode_import",
			Loc::getMessage("ammina.regions_OPTION_MODE_IMPORT"),
			"2",
			array("selectbox"),
			array(
				0 => Loc::getMessage("ammina.regions_OPTION_MODE_IMPORT_FILE"),
				1 => Loc::getMessage("ammina.regions_OPTION_MODE_IMPORT_MEMORY"),
				2 => Loc::getMessage("ammina.regions_OPTION_MODE_IMPORT_BATCH"),
			)
		),

		array("separator", Loc::getMessage("ammina.regions_OPTION_SEPARATOR_DOMAIN_ONECLICK")),
		array("base_domain", Loc::getMessage("ammina.regions_OPTION_BASE_DOMAIN"), $strDomain, array("text", 50)),
		array(
			"base_sid",
			Loc::getMessage("ammina.regions_OPTION_BASE_SID"),
			"",
			array("selectbox"),
			$arSitesSelect
		),
		array("make_settings_sitemap", Loc::getMessage("ammina.regions_OPTION_MAKE_SETTINGS_SITEMAP"), "Y", array("checkbox")),
		array("make_robots_file", Loc::getMessage("ammina.regions_OPTION_MAKE_ROBOTS_FILE"), "Y", array("checkbox")),
		array("prices_default", Loc::getMessage("ammina.regions_OPTION_PRICES_DEFAULT"), "", array("selectboxm2"), $arPricesDefault),
		array("stores_default", Loc::getMessage("ammina.regions_OPTION_STORES_DEFAULT"), "", array("selectboxm2"), $arStoresDefault),

		array("separator", Loc::getMessage("ammina.regions_OPTION_SEPARATOR_PRICE_AGENT")),
		array("priceagent_active", Loc::getMessage("ammina.regions_OPTION_PRICEAGENT_ACTIVE"), "N", array("checkbox")),
		array("priceagent_onlycron", Loc::getMessage("ammina.regions_OPTION_PRICEAGENT_ONLYCRON"), "N", array("checkbox")),
		array("priceagent_period", Loc::getMessage("ammina.regions_OPTION_PRICEAGENT_PERIOD"), "180", array("text", 10)),
		array("priceagent_maxtime_step", Loc::getMessage("ammina.regions_OPTION_PRICEAGENT_MAXTIME_STEP"), "5", array("text", 10)),
		array("priceagent_period_steps", Loc::getMessage("ammina.regions_OPTION_PRICEAGENT_PERIOD_STEPS"), "30", array("text", 10)),
		array("priceagent_memorylimit", Loc::getMessage("ammina.regions_OPTION_PRICEAGENT_MEMORYLIMIT"), "", array("text", 10)),

		array("separator", Loc::getMessage("ammina.regions_OPTION_SEPARATOR_AGENT_AVAILABLE")),
		array("agent_available_domain_active", Loc::getMessage("ammina.regions_OPTION_AGENT_AVAILABLE_DOMAIN_ACTIVE"), "N", array("checkbox")),
		array("agent_available_domain_sum_storesku", Loc::getMessage("ammina.regions_OPTION_AGENT_AVAILABLE_DOMAIN_SUM_STORESKU"), "N", array("checkbox")),
		array("agent_available_domain_sum_quantity_by_store", Loc::getMessage("ammina.regions_OPTION_AGENT_AVAILABLE_DOMAIN_SUM_QUANTITY_BY_STORE"), "N", array("checkbox")),
		array("agent_available_domain_not_use_quantity_trace", Loc::getMessage("ammina.regions_OPTION_AGENT_AVAILABLE_DOMAIN_NOT_USE_QUANTITY_TRACE"), "N", array("checkbox")),
		array("agent_available_domain_not_use_can_buy_zero", Loc::getMessage("ammina.regions_OPTION_AGENT_AVAILABLE_DOMAIN_NOT_USE_CAN_BUY_ZERO"), "N", array("checkbox")),
		array("agent_available_domain_onlycron", Loc::getMessage("ammina.regions_OPTION_AGENT_AVAILABLE_DOMAIN_ONLYCRON"), "N", array("checkbox")),
		array("agent_available_domain_period", Loc::getMessage("ammina.regions_OPTION_AGENT_AVAILABLE_DOMAIN_PERIOD"), "180", array("text", 10)),
		array("agent_available_domain_maxtime_step", Loc::getMessage("ammina.regions_OPTION_AGENT_AVAILABLE_DOMAIN_MAXTIME_STEP"), "5", array("text", 10)),
		array("agent_available_domain_period_steps", Loc::getMessage("ammina.regions_OPTION_AGENT_AVAILABLE_DOMAIN_PERIOD_STEPS"), "30", array("text", 10)),
		array("agent_available_domain_memorylimit", Loc::getMessage("ammina.regions_OPTION_AGENT_AVAILABLE_DOMAIN_MEMORYLIMIT"), "", array("text", 10)),

		array("separator", Loc::getMessage("ammina.regions_OPTION_SEPARATOR_PRODUCT_PROVIDER")),
		array("product_provider_active", Loc::getMessage("ammina.regions_OPTION_PRODUCT_PROVIDER_ACTIVE"), "N", array("checkbox")),
		array("event_get_optimal_price", Loc::getMessage("ammina.regions_OPTION_EVENT_GET_OPTIMAL_PRICE"), "Y", array("checkbox")),
		//array("product_provider_update_exists", Loc::getMessage("ammina.regions_OPTION_PRODUCT_PROVIDER_UPDATE_EXISTS_RECORD"), "N", Array("checkbox")),
		//array("product_provider_class_name", Loc::getMessage("ammina.regions_OPTION_PRODUCT_PROVIDER_CLASS_NAME"), "", Array("text", 50)),

		array("separator", Loc::getMessage("ammina.regions_OPTION_SEPARATOR_AGENT_SITEMAP")),
		array("sitemapagent_active", Loc::getMessage("ammina.regions_OPTION_SITEMAPAGENT_ACTIVE"), "N", array("checkbox")),
		array("sitemapagent_onlycron", Loc::getMessage("ammina.regions_OPTION_SITEMAPAGENT_ONLYCRON"), "N", array("checkbox")),
		array("sitemapagent_period", Loc::getMessage("ammina.regions_OPTION_SITEMAPAGENT_PERIOD"), "60", array("text", 10)),
		array("sitemapagent_period_run", Loc::getMessage("ammina.regions_OPTION_SITEMAPAGENT_PERIOD_RUN"), "7", array("text", 10)),
		array("sitemapagent_memorylimit", Loc::getMessage("ammina.regions_OPTION_SITEMAPAGENT_MEMORYLIMIT"), "", array("text", 10)),
		array("sitemapagent_take_prop", Loc::getMessage("ammina.regions_OPTION_SITEMAPAGENT_TAKE_PROP"), "N", array("checkbox")),
		array("sitemapagent_prop_domain_available", Loc::getMessage("ammina.regions_OPTION_SITEMAPAGENT_PROP_DOMAIN_AVAILABLE"), "SYS_DOMAIN_AVAILABLE", array("textarea", 4, 40)),
		array("sitemapagent_prop_show_domain", Loc::getMessage("ammina.regions_OPTION_SITEMAPAGENT_PROP_SHOW_DOMAIN"), "SYS_SHOW_DOMAIN", array("textarea", 4, 40)),
		array("sitemapagent_prop_hide_domain", Loc::getMessage("ammina.regions_OPTION_SITEMAPAGENT_PROP_HIDE_DOMAIN"), "SYS_HIDE_DOMAIN", array("textarea", 4, 40)),
	));

	$strWarning = "";
	if ($REQUEST_METHOD == "POST" && amreg_strlen($Update) > 0 && $modulePermissions == "W" && check_bitrix_sessid()) {
		foreach ($arAllOptions as $option) {
			if ($option[0] == "separator") {
				continue;
			}
			$name = $option[0];
			$val = $$name;
			if ($option[3][0] == "checkbox" && $val != "Y") {
				$val = "N";
			}
			if ($option[3][0] == "selectboxm") {
				if (!is_array($val)) {
					$val = array();
				}
				$val = implode("|", $val);
			}
			if ($option[3][0] == "selectboxm2") {
				if (!is_array($val)) {
					$val = array();
				}
				$val = implode("|", $val);
			}
			if ($option[3][0] == "selectboxprop") {
				if (!is_array($val)) {
					$val = array();
				}
				$val = implode("|", $val);
			}
			if ($name == "pathdomain_list") {
				$arData = explode("\n", trim($val));
				if ($use_one_domain != "Y" || $use_path_domain != "Y") {
					$arData = array();
				}
				foreach ($arData as $k => $v) {
					$arData[$k] = trim($v);
					if (amreg_strlen($arData[$k]) <= 0) {
						unset($arData[$k]);
					} else {
						if (amreg_substr($arData[$k], -1, 1) != '/') {
							$arData[$k] .= '/';
						}
					}
				}
				$arData = array_values($arData);
				$arNewData = array();
				$rDomains = \Ammina\Regions\DomainTable::getList(
					array(
						"filter" => array("ACTIVE" => "Y"),
						"select" => array("ID", "PATHCODE")
					)
				);
				while ($arDomain = $rDomains->fetch()) {
					foreach ($arData as $strTemplate) {
						$iLevel = 0;
						if (amreg_strpos($strTemplate, '*[') !== false) {
							$ar = explode("[", $strTemplate);
							$ar2 = explode("]", $ar[1]);
							$iLevel = $ar2[0];
							$ar[1] = $ar2[1];
							$strTemplate = implode("", $ar);
						}
						$strNewLink = str_replace('*', $arDomain['PATHCODE'], $strTemplate);
						$strNewLink = str_replace('//', '/', $strNewLink);
						$strOriginalLink = str_replace('*', '', $strTemplate);
						$strOriginalLink = str_replace('//', '/', $strOriginalLink);
						$arNewData[amreg_strlen($arDomain['PATHCODE']) > 0 ? $arDomain['PATHCODE'] : '-'][] = array(
							"ID" => $arDomain['ID'],
							"REGIONAL" => $strNewLink,
							"ORIGINAL" => $strOriginalLink,
							"LEVEL" => $iLevel
						);
					}
				}
				krsort($arNewData);
				file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/urlrewrite.regions.php", '<' . '? return ' . var_export($arNewData, true) . ';');
			}
			COption::SetOptionString($module_id, $name, $val, $option[1]);
		}
		$arAgent = CAgent::GetList(
			array(),
			array(
				"NAME" => '\Ammina\Regions\Agent\Price::doExecute();',
				"MODULE_ID" => $module_id,
			)
		)->Fetch();
		if (COption::GetOptionString($module_id, "priceagent_active", "N") == "Y") {
			$arFields = array(
				"NAME" => '\Ammina\Regions\Agent\Price::doExecute();',
				"MODULE_ID" => $module_id,
				"ACTIVE" => "Y",
				"SORT" => 100,
				"IS_PERIOD" => "N",
				"AGENT_INTERVAL" => COption::GetOptionString($module_id, "priceagent_period_steps", 30),
				"USER_ID" => false,
				"NEXT_EXEC" => ConvertTimeStamp(false, "FULL"),
			);
			if ($arAgent) {
				CAgent::Update($arAgent['ID'], $arFields);
			} else {
				CAgent::Add($arFields);
			}
			\COption::SetOptionString("ammina.regions", "priceagent_nextId", "");
			\COption::SetOptionString("ammina.regions", "priceagent_emptyexec", "N");
		} else {
			if ($arAgent) {
				CAgent::Delete($arAgent['ID']);
			}
		}

		$arAgent = CAgent::GetList(
			array(),
			array(
				"NAME" => '\Ammina\Regions\Agent\DomainAvailable::doExecute();',
				"MODULE_ID" => $module_id,
			)
		)->Fetch();
		if (COption::GetOptionString($module_id, "agent_available_domain_active", "N") == "Y") {
			$arFields = array(
				"NAME" => '\Ammina\Regions\Agent\DomainAvailable::doExecute();',
				"MODULE_ID" => $module_id,
				"ACTIVE" => "Y",
				"SORT" => 100,
				"IS_PERIOD" => "N",
				"AGENT_INTERVAL" => COption::GetOptionString($module_id, "agent_available_domain_period_steps", 30),
				"USER_ID" => false,
				"NEXT_EXEC" => ConvertTimeStamp(false, "FULL"),
			);
			if ($arAgent) {
				CAgent::Update($arAgent['ID'], $arFields);
			} else {
				CAgent::Add($arFields);
			}
			\COption::SetOptionString("ammina.regions", "agent_available_domain_nextId", "");
			\COption::SetOptionString("ammina.regions", "agent_available_domain_emptyexec", "N");
			\COption::SetOptionString("ammina.regions", "agent_available_domain_checkSku", "Y");
		} else {
			if ($arAgent) {
				CAgent::Delete($arAgent['ID']);
			}
		}
		if (COption::GetOptionString($module_id, "product_provider_active", "N") == "Y") {
			$eventManager = \Bitrix\Main\EventManager::getInstance();
			$eventManager->registerEventHandler('sale', 'OnBeforeSaleBasketItemSetFields', $module_id, '\CAmminaRegions', 'OnBeforeBasketItemSetFields');
			$eventManager->registerEventHandler('sale', 'OnSaleBasketItemRefreshData', $module_id, '\CAmminaRegions', 'OnSaleBasketItemRefreshData');
		} else {
			$eventManager = \Bitrix\Main\EventManager::getInstance();
			$eventManager->unregisterEventHandler('sale', 'OnBeforeSaleBasketItemSetFields', $module_id, '\CAmminaRegions', 'OnBeforeBasketItemSetFields');
			$eventManager->unregisterEventHandler('sale', 'OnSaleBasketItemRefreshData', $module_id, '\CAmminaRegions', 'OnSaleBasketItemRefreshData');
		}
		if (COption::GetOptionString($module_id, "event_get_optimal_price", "Y") == "Y") {
			RegisterModuleDependences('catalog', 'OnGetOptimalPrice', "ammina.regions", 'CAmminaRegions', 'OnGetOptimalPrice');
		} else {
			UnRegisterModuleDependences('catalog', 'OnGetOptimalPrice', "ammina.regions", 'CAmminaRegions', 'OnGetOptimalPrice');
		}
		if (COption::GetOptionString($module_id, "use_order_prefix", "N") == "Y") {
			RegisterModuleDependences('sale', 'OnBeforeOrderAccountNumberSet', "ammina.regions", 'CAmminaRegions', 'OnBeforeOrderAccountNumberSet');
		} else {
			UnRegisterModuleDependences('sale', 'OnBeforeOrderAccountNumberSet', "ammina.regions", 'CAmminaRegions', 'OnBeforeOrderAccountNumberSet');
		}

		$arAgent = CAgent::GetList(
			array(),
			array(
				"NAME" => '\Ammina\Regions\Agent\SiteMapGenerate::doExecute();',
				"MODULE_ID" => $module_id,
			)
		)->Fetch();
		if (COption::GetOptionString($module_id, "sitemapagent_active", "N") == "Y") {
			$arFields = array(
				"NAME" => '\Ammina\Regions\Agent\SiteMapGenerate::doExecute();',
				"MODULE_ID" => $module_id,
				"ACTIVE" => "Y",
				"SORT" => 100,
				"IS_PERIOD" => "N",
				"AGENT_INTERVAL" => COption::GetOptionString($module_id, "sitemapagent_period", 60),
				"USER_ID" => false,
				"NEXT_EXEC" => ConvertTimeStamp(false, "FULL"),
			);
			if ($arAgent) {
				CAgent::Update($arAgent['ID'], $arFields);
			} else {
				CAgent::Add($arFields);
			}
			if (CModule::IncludeModule("seo")) {
				$rSitemap = \Bitrix\Seo\SitemapTable::getList(array(
					"filter" => array(
						"ACTIVE" => "Y"
					)
				));
				while ($arSitemap = $rSitemap->fetch()) {
					\Bitrix\Seo\SitemapTable::update($arSitemap['ID'], array("DATE_RUN" => false));
				}
			}
		} else {
			if ($arAgent) {
				CAgent::Delete($arAgent['ID']);
			}
		}
	}

	if (amreg_strlen($strWarning) > 0) {
		CAdminMessage::ShowMessage($strWarning);
	}

	$aTabs = array();
	$aTabs[] = array(
		'DIV' => 'edit1',
		'TAB' => Loc::getMessage('ammina.regions_TAB_SETTINGS_TITLE'),
		'TITLE' => Loc::getMessage('ammina.regions_TAB_SETTINGS_DESC'),
	);
	$aTabs[] = array(
		'DIV' => 'edit2',
		'TAB' => Loc::getMessage('ammina.regions_TAB_SUPPORT_TITLE'),
		'TITLE' => Loc::getMessage('ammina.regions_TAB_SUPPORT_DESC'),
	);
	$aTabs[] = array(
		'DIV' => 'editrights',
		'TAB' => Loc::getMessage('ammina.regions_TAB_RIGHTS_TITLE'),
		'TITLE' => Loc::getMessage('ammina.regions_TAB_RIGHTS_DESC'),
	);
	$tabControl = new CAdminTabControl('tabControl', $aTabs);

	$tabControl->Begin();
	?>
	<form method="POST" action="<?
	echo $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx($mid) ?>&lang=<?= LANGUAGE_ID ?>"><?
		bitrix_sessid_post();
		$tabControl->BeginNextTab();
		foreach ($arAllOptions as $Option) {
			$val = COption::GetOptionString($module_id, $Option[0], $Option[2]);
			$type = $Option[3];
			if ($Option[0] == "separator") {
				?>
				<tr class="heading">
					<td colspan="2"><?= $Option[1] ?></td>
				</tr>
				<?
			} else {
				?>
				<tr>
					<td valign="top" width="50%"><?
						if ($type[0] == "checkbox") {
							echo "<label for=\"" . htmlspecialcharsbx($Option[0]) . "\">" . $Option[1] . "</label>";
							if ($Option[0] == "use_path_domain") {
								?><br>
								<small><?= Loc::getMessage("ammina.regions_OPTION_USE_PATH_DOMAIN_NOTE") ?></small><?
							} elseif ($Option[0] == "use_order_prefix") {
								?><br>
								<small><?= Loc::getMessage("ammina.regions_OPTION_USE_ORDER_PREFIX_NOTE") ?></small><?
							} elseif ($Option[0] == "sitemapagent_active") {
								?><br>
								<small><?= Loc::getMessage("ammina.regions_OPTION_SITEMAPAGENT_ACTIVE_DESCRIPTION") ?></small><?
							}
						} else {
							echo $Option[1];
							if ($Option[0] == "pathdomain_list") {
								?><br>
								<small><?= Loc::getMessage("ammina.regions_OPTION_PATHDOMAIN_LIST_NOTE") ?></small><?
							}
						}
						?></td>
					<td valign="middle" width="50%">
						<?
						if ($type[0] == "checkbox") {
							?>
							<input type="checkbox" name="<?
							echo htmlspecialcharsbx($Option[0]) ?>" id="<?
							echo htmlspecialcharsbx($Option[0]) ?>" value="Y"<?
							if ($val == "Y") {
								echo " checked";
							} ?>>
						<? } elseif ($type[0] == "text") {
							?>
							<input type="text" size="<?
							echo $type[1] ?>" value="<?
							echo htmlspecialcharsbx($val) ?>" name="<?
							echo htmlspecialcharsbx($Option[0]) ?>">
						<? } elseif ($type[0] == "textarea") {
							?>
							<textarea rows="<?
							echo $type[1] ?>" cols="<?
							echo $type[2] ?>" name="<?
							echo htmlspecialcharsbx($Option[0]) ?>"><?
								echo htmlspecialcharsbx($val) ?></textarea>
						<? } elseif ($type[0] == "selectbox") {
							?>
							<select name="<?
							echo htmlspecialcharsbx($Option[0]) ?>" id="<?
							echo htmlspecialcharsbx($Option[0]) ?>">
								<?
								foreach ($Option[4] as $v => $k) {
									?>
									<option value="<?= $v ?>"<?
									if ($val == $v) {
										echo " selected";
									} ?>><?= $k ?></option><?
								}
								?>
							</select>
						<? } elseif ($type[0] == "selectboxm") {
							$val = explode("|", $val);
							?>
							<select name="<?
							echo htmlspecialcharsbx($Option[0]) ?>[]" id="<?
							echo htmlspecialcharsbx($Option[0]) ?>" size="15" multiple="multiple">
								<?
								foreach ($Option[4] as $v => $k) {
									?>
									<option value="<?= $v ?>"<?
									if (in_array($v, $val)) {
										echo " selected";
									} ?>><?= $k ?></option><?
								}
								?>
							</select>
						<? } elseif ($type[0] == "selectboxm2") {
							$val = explode("|", $val);
							?>
							<select name="<?
							echo htmlspecialcharsbx($Option[0]) ?>[]" id="<?
							echo htmlspecialcharsbx($Option[0]) ?>" size="8" multiple="multiple">
								<?
								foreach ($Option[4] as $v => $k) {
									?>
									<option value="<?= $v ?>"<?
									if (in_array($v, $val)) {
										echo " selected";
									} ?>><?= $k ?></option><?
								}
								?>
							</select>
						<? } elseif ($type[0] == "selectboxprop") {
							$val = explode("|", $val);
							?>
							<select name="<?
							echo htmlspecialcharsbx($Option[0]) ?>[]" id="<?
							echo htmlspecialcharsbx($Option[0]) ?>" size="15" multiple="multiple">
								<?
								foreach ($Option[4] as $k => $v) {
									if (empty($v['PROP'])) {
										continue;
									}
									?>
									<optgroup label="<?= $v['NAME'] ?>"></optgroup>
									<?
									foreach ($v['PROP'] as $k1 => $v1) {
										?>
										<option value="<?= $k1 ?>"<?
										if (in_array($k1, $val)) {
											echo " selected";
										} ?>><?= $v1 ?></option>
										<?
									}
								}
								?>
							</select>
						<? } ?>
					</td>
				</tr>
				<?
			}
		}
		$tabControl->BeginNextTab();
		?>
		<tr>
			<td>
				<? echo Loc::getMessage("ammina.regions_TAB_SUPPORT_CONTENT"); ?>
				<?
				/*
				if (COption::GetOptionString($module_id, "support", "N") != "Y") {
					?>
					<script data-skip-moving="true">
						(function (w, d, u) {
							var s = d.createElement('script');
							s.async = 1;
							s.src = u + '?' + (Date.now() / 60000 | 0);
							var h = d.getElementsByTagName('script')[0];
							h.parentNode.insertBefore(s, h);
						})(window, document, 'https://www.ammina24.ru/upload/crm/site_button/loader_2_pyvnrh.js');
					</script>
					<?
				}
				*/
				?>
			</td>
		</tr>
		<?
		$tabControl->BeginNextTab();
		require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php"); ?>
		<?
		$tabControl->Buttons(); ?>
		<script language="JavaScript">
			function RestoreDefaults() {
				if (confirm('<?echo AddSlashes(Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
					window.location = "<?echo $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?echo LANG?>&mid=<?echo urlencode($mid) . "&" . bitrix_sessid_get();?>";
			}
		</script>

		<input type="submit" <?
		if ($modulePermissions < "W") echo "disabled" ?> name="Update" value="<?= Loc::getMessage("MAIN_SAVE") ?>">
		<input type="hidden" name="Update" value="Y">
		<input type="reset" name="reset" value="<?= Loc::getMessage("MAIN_RESET") ?>">
		<input type="button" <?
		if ($modulePermissions < "W") echo "disabled" ?> title="<?= Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS") ?>"
			   onclick="RestoreDefaults();" value="<?= Loc::getMessage("MAIN_RESTORE_DEFAULTS") ?>">
		<?
		$tabControl->End();
		?>
	</form>
	<?
	CAmminaRegions::showSupportForm();
}
