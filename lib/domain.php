<?

namespace Ammina\Regions;

use Bitrix\Catalog\Product\Price\Calculation;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\EventResult;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class DomainTable extends DataManager
{

	public static function getTableName()
	{
		return 'am_regions_domain';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'NAME' => array(
				'data_type' => 'string',
			),
			'NAME_LANG' => array(
				'data_type' => 'string',
			),
			"ACTIVE" => array(
				'data_type' => 'enum',
				'values' => array('N', 'Y'),
			),
			"IS_DEFAULT" => array(
				'data_type' => 'enum',
				'values' => array('N', 'Y'),
			),
			'DOMAIN' => array(
				'data_type' => 'string',
			),
			'PATHCODE' => array(
				'data_type' => 'string',
			),
			'ORDER_PREFIX' => array(
				'data_type' => 'string',
			),
			'SITE_ID' => array(
				'data_type' => 'string',
			),
			'SITE' => array(
				'data_type' => '\Bitrix\Main\Site',
				'reference' => array('=this.SITE_ID' => 'ref.LID'),
			),
			'SITE_EXT' => array(
				'data_type' => 'string',
			),
			'CITY_ID' => array(
				'data_type' => 'integer',
			),
			'CITY' => array(
				'data_type' => '\Ammina\Regions\City',
				'reference' => array('=this.CITY_ID' => 'ref.ID'),
			),
			"NOINDEX" => array(
				'data_type' => 'enum',
				'values' => array('N', 'Y'),
			),
			'SALE_UID' => array(
				'data_type' => 'integer',
			),
			'SALE_USER' => array(
				'data_type' => '\Bitrix\Main\User',
				'reference' => array('=this.SALE_UID' => 'ref.ID'),
			),
			'SALE_COMPANY_ID' => array(
				'data_type' => 'integer',
			),
			'SALE_COMPANY' => array(
				'data_type' => '\Bitrix\Sale\Internals\CompanyTable',
				'reference' => array('=this.SALE_COMPANY_ID' => 'ref.ID'),
			),
			'DEFAULT_EMAIL' => array(
				'data_type' => 'string',
			),
			'VARIABLE_SEPARATOR' => array(
				'data_type' => 'string',
			),
			'PRICES' => array(
				'data_type' => 'string',
			),
			'STORES' => array(
				'data_type' => 'string',
			),
			'COUNTERS' => array(
				'data_type' => 'string',
			),
			'HEAD_STRING' => array(
				'data_type' => 'string',
			),
			'DOMAIN_LOCATIONS' => array(
				'data_type' => '\Ammina\Regions\DomainLocation',
				'reference' => array('=this.ID' => 'ref.DOMAIN_ID'),
			),
		);

		return $fieldsMap;
	}

	public static function onBeforeUpdate(Event $event)
	{
		$result = new EventResult();
		$data = $event->getParameter("fields");
		$arUpdateFields = false;
		if (isset($data['PRICES'])) {
			foreach ($data['PRICES'] as $k => $v) {
				if ($v == -1) {
					unset($data['PRICES'][$k]);
				}
			}
			$data['PRICES'] = array_values($data['PRICES']);
			$arUpdateFields['PRICES'] = serialize($data['PRICES']);
		}
		if (isset($data['NAME_LANG'])) {
			$arAllowLangs = explode("|", \COption::GetOptionString("ammina.regions", "use_lang", ""));
			foreach ($data['NAME_LANG'] as $k => $v) {
				if (!in_array($k, $arAllowLangs)) {
					unset($data['NAME_LANG'][$k]);
				}
			}
			$arUpdateFields['NAME_LANG'] = serialize($data['NAME_LANG']);
		}
		if (isset($data['STORES'])) {
			foreach ($data['STORES'] as $k => $v) {
				if ($v == -1) {
					unset($data['STORES'][$k]);
				}
			}
			$data['STORES'] = array_values($data['STORES']);
			$arUpdateFields['STORES'] = serialize($data['STORES']);
		}
		if (isset($data['SITE_EXT'])) {
			foreach ($data['SITE_EXT'] as $k => $v) {
				$data['SITE_EXT'][$k] = trim($v);
				if (amreg_strlen($data['SITE_EXT'][$k]) <= 0) {
					unset($data['SITE_EXT'][$k]);
				}
			}
			$data['SITE_EXT'] = array_values($data['SITE_EXT']);
			$arUpdateFields['SITE_EXT'] = "-" . implode("-", $data['SITE_EXT']) . "-";
		}

		if (is_array($arUpdateFields)) {
			$result->modifyFields($arUpdateFields);
		}
		return $result;
	}

	public static function onBeforeAdd(Event $event)
	{
		$result = new EventResult();
		$data = $event->getParameter("fields");
		$arUpdateFields = false;
		if (isset($data['PRICES'])) {
			foreach ($data['PRICES'] as $k => $v) {
				if ($v == -1) {
					unset($data['PRICES'][$k]);
				}
			}
			$data['PRICES'] = array_values($data['PRICES']);
			$arUpdateFields['PRICES'] = serialize($data['PRICES']);
		}
		if (isset($data['NAME_LANG'])) {
			$arAllowLangs = explode("|", \COption::GetOptionString("ammina.regions", "use_lang", ""));
			foreach ($data['NAME_LANG'] as $k => $v) {
				if (!in_array($k, $arAllowLangs)) {
					unset($data['NAME_LANG'][$k]);
				}
			}
			$arUpdateFields['NAME_LANG'] = serialize($data['NAME_LANG']);
		}
		if (isset($data['STORES'])) {
			foreach ($data['STORES'] as $k => $v) {
				if ($v == -1) {
					unset($data['STORES'][$k]);
				}
			}
			$data['STORES'] = array_values($data['STORES']);
			$arUpdateFields['STORES'] = serialize($data['STORES']);
		}
		if (isset($data['SITE_EXT'])) {
			foreach ($data['SITE_EXT'] as $k => $v) {
				$data['SITE_EXT'][$k] = trim($v);
				if (amreg_strlen($data['SITE_EXT'][$k]) <= 0) {
					unset($data['SITE_EXT'][$k]);
				}
			}
			$data['SITE_EXT'] = array_values($data['SITE_EXT']);
			$arUpdateFields['SITE_EXT'] = "-" . implode("-", $data['SITE_EXT']) . "-";
		}

		if (is_array($arUpdateFields)) {
			$result->modifyFields($arUpdateFields);
		}
		return $result;
	}

	public static function onAfterAdd(Event $event)
	{
		$result = new EventResult();
		$data = $event->getParameter("fields");
		$id = $event->getParameter("id");
		$recordId = $id;
		if (is_array($recordId) && isset($recordId['ID'])) {
			$recordId = $id['ID'];
		}
		if (isset($data['IS_DEFAULT']) && $data['IS_DEFAULT'] == "Y" && $recordId > 0) {
			$rDomains = DomainTable::getList(
				array(
					"filter" => array(
						"IS_DEFAULT" => "Y",
						"!ID" => $recordId,
					),
				)
			);
			while ($arDomain = $rDomains->fetch()) {
				DomainTable::update($arDomain['ID'], array("IS_DEFAULT" => "N"));
			}
		}
		if ($id['ID'] > 0) {
			\Ammina\Regions\DomainVariableTable::doFillAllSystemVariables($recordId);
		}
		$o = new \CPHPCache();
		$o->CleanDir("ammina/regions/domain");
		$o->CleanDir("ammina/regions/domain.list");
		self::doCheckDomainHostInSite($recordId);
		self::doCheckIBlockPropsList();
	}

	public static function onAfterUpdate(Event $event)
	{
		$result = new EventResult();
		$data = $event->getParameter("fields");
		$id = $event->getParameter("id");
		$recordId = $id;
		if (is_array($recordId) && isset($recordId['ID'])) {
			$recordId = $id['ID'];
		}
		if (isset($data['IS_DEFAULT']) && $data['IS_DEFAULT'] == "Y" && $recordId > 0) {
			$rDomains = DomainTable::getList(
				array(
					"filter" => array(
						"IS_DEFAULT" => "Y",
						"SITE_ID" => $data['SITE_ID'],
						"!ID" => $recordId,
					),
				)
			);
			while ($arDomain = $rDomains->fetch()) {
				DomainTable::update($arDomain['ID'], array("IS_DEFAULT" => "N"));
			}
		}
		if ($id['ID'] > 0) {
			\Ammina\Regions\DomainVariableTable::doFillAllSystemVariables($recordId);
		}
		$o = new \CPHPCache();
		$o->CleanDir("ammina/regions/domain");
		$o->CleanDir("ammina/regions/domain.list");
		self::doCheckDomainHostInSite($recordId);
		self::doCheckIBlockPropsList();
	}

	public static function onAfterDelete(Event $event)
	{
		self::doCheckIBlockPropsList();
	}

	public static function getList(array $parameters = array())
	{
		if (isset($parameters['filter']) && isset($parameters['filter']['SITE_ID']) && !isset($parameters['filter']['SITE_EXT'])) {
			$parameters['filter'][] = array(
				"LOGIC" => "OR",
				array(
					"SITE_ID" => $parameters['filter']['SITE_ID']
				),
				array(
					"%SITE_EXT" => "-" . $parameters['filter']['SITE_ID'] . "-"
				)
			);
			unset($parameters['filter']['SITE_ID']);
		}
		$result = parent::getList($parameters);
		$result->setSerializedFields(array("PRICES", "STORES", "NAME_LANG"));
		$result->addFetchDataModifier(function (&$data) use ($parameters) {
			//\CAmminaRegions::langNamesForResult($data, self::$langFields, isset($parameters['select']) ? $parameters['select'] : false);
			if (isset($data['SITE_EXT'])) {
				$data['SITE_EXT'] = explode("-", $data['SITE_EXT']);
				foreach ($data['SITE_EXT'] as $k => $v) {
					if (amreg_strlen($v) <= 0) {
						unset($data['SITE_EXT'][$k]);
					}
				}
				$data['SITE_EXT'] = array_values($data['SITE_EXT']);
			}
		});
		return $result;
	}

	public static function doMakeRobotsForDomain($ID)
	{
		$arCurrentDomain = self::getRowById($ID);
		if ($arCurrentDomain) {
			CheckDirPath($_SERVER['DOCUMENT_ROOT'] . "/seofiles/robots/");
			$strFileName = $_SERVER['DOCUMENT_ROOT'] . "/seofiles/robots/" . $arCurrentDomain['DOMAIN'] . ".robots.txt";
			if ($arCurrentDomain['NOINDEX'] == "Y") {
				$strRobots = 'User-Agent: *
Disallow: /
';
			} else {
				if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/seofiles/templates/robots_" . $arCurrentDomain['SITE_ID'] . ".txt")) {
					$strRobots = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/seofiles/templates/robots_" . $arCurrentDomain['SITE_ID'] . ".txt");
				} elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . "/seofiles/templates/robots.txt")) {
					$strRobots = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/seofiles/templates/robots.txt");
				} else {
					$strRobots = 'User-Agent: *
Disallow: */index.php
Disallow: /bitrix/
Disallow: /*show_include_exec_time=
Disallow: /*show_page_exec_time=
Disallow: /*show_sql_stat=
Disallow: /*bitrix_include_areas=
Disallow: /*clear_cache=
Disallow: /*clear_cache_session=
Disallow: /*ADD_TO_COMPARE_LIST
Disallow: /*ORDER_BY
Disallow: /*PAGEN
Disallow: /*?print=
Disallow: /*&print=
Disallow: /*print_course=
Disallow: /*?action=
Disallow: /*&action=
Disallow: /*register=
Disallow: /*forgot_password=
Disallow: /*change_password=
Disallow: /*login=
Disallow: /*logout=
Disallow: /*auth=
Disallow: /*backurl=
Disallow: /*back_url=
Disallow: /*BACKURL=
Disallow: /*BACK_URL=
Disallow: /*back_url_admin=
Disallow: /*?utm_source=
Disallow: /*?bxajaxid=
Disallow: /*&bxajaxid=
Disallow: /*?view_result=
Disallow: /*&view_result=
Allow: /bitrix/components/
Allow: /bitrix/cache/
Allow: /bitrix/js/
Allow: /bitrix/templates/
Allow: /bitrix/panel/
Allow: /local/templates/
Allow: /local/components/
Host: #PROTOCOL_HOST##DOMAIN#
Sitemap: #PROTOCOL#://#DOMAIN#/seofiles/sitemaps/#DOMAIN#.sitemap.xml
Crawl-delay: 2.0
';
					\Bitrix\Main\IO\File::putFileContents($_SERVER['DOCUMENT_ROOT'] . "/seofiles/templates/robots.txt", $strRobots);
				}
				$strRobots = str_replace("#DOMAIN#", $arCurrentDomain['DOMAIN'], $strRobots);
				if (\CMain::IsHTTPS()) {
					$strRobots = str_replace("#PROTOCOL_HOST#", "https://", $strRobots);
					$strRobots = str_replace("#PROTOCOL#", "https", $strRobots);
				} else {
					$strRobots = str_replace("#PROTOCOL_HOST#", "", $strRobots);
					$strRobots = str_replace("#PROTOCOL#", "http", $strRobots);
				}
			}
			\Bitrix\Main\IO\File::putFileContents($strFileName, $strRobots);
			//}
		}
	}

	public static function doMakeSitemapSettingsForDomain($ID)
	{
		$arCurrentDomain = self::getRowById($ID);
		if ($arCurrentDomain) {
			CheckDirPath($_SERVER['DOCUMENT_ROOT'] . "/seofiles/sitemaps/");

			$arSiteMap = \Bitrix\Seo\SitemapTable::getList(
				array(
					"filter" => array(
						"SITE_ID" => $arCurrentDomain['SITE_ID'],
						"NAME" => $arCurrentDomain['DOMAIN'],
					),
				)
			)->fetch();
			if (!$arSiteMap) {
				$arIBlockActive = array();
				$arIBlockList = array();
				$arIBlockSection = array();
				$arIBlockElement = array();
				$rIBlock = \CIBlock::GetList(array("ID" => "ASC"), array("SITE_ID" => $arCurrentDomain['SITE_ID']));
				while ($arIBlock = $rIBlock->Fetch()) {
					$arIBlockActive[$arIBlock['ID']] = ($arIBlock['INDEX_ELEMENT'] == "Y" || $arIBlock['INDEX_SECTION'] == "Y") ? "Y" : "N";
					$arIBlockList[$arIBlock['ID']] = ($arIBlockActive[$arIBlock['ID']] == "Y" && amreg_strlen($arIBlock['LIST_PAGE_URL']) > 0) ? "Y" : "N";
					$arIBlockSection[$arIBlock['ID']] = ($arIBlock['INDEX_SECTION'] == "Y" && amreg_strlen($arIBlock['SECTION_PAGE_URL']) > 0) ? "Y" : "N";
					$arIBlockElement[$arIBlock['ID']] = ($arIBlock['INDEX_ELEMENT'] == "Y" && amreg_strlen($arIBlock['DETAIL_PAGE_URL']) > 0) ? "Y" : "N";
				}
				$arFieldsSitemap = array(
					"SITE_ID" => $arCurrentDomain['SITE_ID'],
					"NAME" => $arCurrentDomain['DOMAIN'],
					"ACTIVE" => "Y",
					"SETTINGS" => array(
						'FILE_MASK' => '*.php,*.html',
						'ROBOTS' => 'N',
						'logical' => 'Y',
						'DIR' => array(
							'/' => 'Y',
						),
						'FILE' => array(),
						'PROTO' => \CMain::IsHTTPS() ? '1' : '0',
						'DOMAIN' => $arCurrentDomain['DOMAIN'],
						'FILENAME_INDEX' => 'seofiles/sitemaps/' . $arCurrentDomain['DOMAIN'] . '.sitemap.xml',
						'FILENAME_FILES' => 'seofiles/sitemaps/' . $arCurrentDomain['DOMAIN'] . '.sitemap_files.xml',
						'FILENAME_IBLOCK' => 'seofiles/sitemaps/' . $arCurrentDomain['DOMAIN'] . '.sitemap_iblock_#IBLOCK_ID#.xml',
						'FILENAME_FORUM' => 'seofiles/sitemaps/' . $arCurrentDomain['DOMAIN'] . '.sitemap_forum_#FORUM_ID#.xml',
						'IBLOCK_ACTIVE' => $arIBlockActive,
						'IBLOCK_LIST' => $arIBlockList,
						'IBLOCK_SECTION' => $arIBlockSection,
						'IBLOCK_ELEMENT' => $arIBlockElement,
						'IBLOCK_SECTION_SECTION' => null,
						'IBLOCK_SECTION_ELEMENT' => null,
						'FORUM_ACTIVE' => null,
						'FORUM_TOPIC' => null,
						'FILE_MASK_REGEXP' => '/^(.*?\\.php|.*?\\.html)$/iu',
					),
				);
				$arFieldsSitemap['SETTINGS'] = serialize($arFieldsSitemap['SETTINGS']);
				\Bitrix\Seo\SitemapTable::add($arFieldsSitemap);
			}
		}
	}

	public static function doMakeSaleCompanyForDomain($ID)
	{
		$arCurrentDomain = self::getRowById($ID);
		if ($arCurrentDomain && \CAmminaRegions::isIMExists()) {
			$domainLocationId = false;
			if ($arCurrentDomain['CITY_ID'] > 0) {
				$arCityInfo = \Ammina\Regions\CityTable::getRowById($arCurrentDomain['CITY_ID']);
				if ($arCityInfo['LOCATION_ID'] > 0) {
					$arLocation = \Bitrix\Sale\Location\LocationTable::getRowById($arCityInfo['LOCATION_ID']);
					$domainLocationId = $arLocation['CODE'];
				}
			}
			$arCompanyFields = array(
				"NAME" => $arCurrentDomain['NAME'],
				"LOCATION_ID" => $domainLocationId,
				"ACTIVE" => "Y",
			);
			$oResultCompany = \Bitrix\Sale\Internals\CompanyTable::add($arCompanyFields);
			if ($oResultCompany->isSuccess()) {
				\Ammina\Regions\DomainTable::update(
					$arCurrentDomain['ID'],
					array(
						"SALE_COMPANY_ID" => $oResultCompany->getId(),
					)
				);
				$arCurrentDomain['SALE_COMPANY_ID'] = $oResultCompany->getId();
			}
		}
	}

	public static function doMakeSaleCompanyRestrictionsForDomain($ID)
	{
		$arCurrentDomain = self::getRowById($ID);
		if ($arCurrentDomain && \CAmminaRegions::isIMExists()) {
			if ($arCurrentDomain['SALE_COMPANY_ID'] > 0) {
				$rRestrictions = \Bitrix\Sale\Internals\ServiceRestrictionTable::getList(
					array(
						"filter" => array(
							"SERVICE_ID" => $arCurrentDomain['SALE_COMPANY_ID'],
							"SERVICE_TYPE" => \Bitrix\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_COMPANY,
						),
					)
				);
				while ($arRestriction = $rRestrictions->fetch()) {
					if ($arRestriction['CLASS_NAME'] == '\Ammina\Regions\Rules\Sale\CompanyRules\Domain') {
						break;
					}
				}
				if ($arRestriction) {
					if (!in_array($arCurrentDomain['ID'], $arRestriction['PARAMS']['DOMAIN'])) {
						$arRestriction['PARAMS']['DOMAIN'][] = $arCurrentDomain['ID'];
						\Bitrix\Sale\Internals\ServiceRestrictionTable::update(
							$arRestriction['ID'],
							array(
								"PARAMS" => $arRestriction['PARAMS'],
							)
						);
					}
				} else {
					$arFieldsRestriction = array(
						"SERVICE_ID" => $arCurrentDomain['SALE_COMPANY_ID'],
						"CLASS_NAME" => '\Ammina\Regions\Rules\Sale\CompanyRules\Domain',
						"SERVICE_TYPE" => \Bitrix\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_COMPANY,
						"SORT" => 100,
						"PARAMS" => array(
							"DOMAIN" => array(
								$arCurrentDomain['ID'],
							),
						),
					);
					\Bitrix\Sale\Internals\ServiceRestrictionTable::add($arFieldsRestriction);
				}
			}
		}
	}

	public static function doMakeSaleCompanyGroupsForDomain($ID)
	{
		$arCurrentDomain = self::getRowById($ID);
		if ($arCurrentDomain) {
			$iCompanyGroup = false;
			$iUserGroup = false;
			$arGroup = \Bitrix\Main\GroupTable::getList(
				array(
					"filter" => array(
						"NAME" => Loc::getMessage("ammina.regions_USER_GROUP_COMPANY") . ": " . $arCurrentDomain['NAME'],
					),
				)
			)->fetch();
			if (!$arGroup) {
				$oRes = \Bitrix\Main\GroupTable::add(
					array(
						"NAME" => Loc::getMessage("ammina.regions_USER_GROUP_COMPANY") . ": " . $arCurrentDomain['NAME'],
						"C_SORT" => 10000,
						"ACTIVE" => "Y",
					)
				);
				if ($oRes->isSuccess()) {
					$iCompanyGroup = $oRes->getId();
				}
			} else {
				$iCompanyGroup = $arGroup['ID'];
			}
			$arGroup = \Bitrix\Main\GroupTable::getList(
				array(
					"filter" => array(
						"NAME" => Loc::getMessage("ammina.regions_USER_GROUP_COMPANY_RESPONSIBLE") . ": " . $arCurrentDomain['NAME'],
					),
				)
			)->fetch();
			if (!$arGroup) {
				$oRes = \Bitrix\Main\GroupTable::add(
					array(
						"NAME" => Loc::getMessage("ammina.regions_USER_GROUP_COMPANY_RESPONSIBLE") . ": " . $arCurrentDomain['NAME'],
						"C_SORT" => 10000,
						"ACTIVE" => "Y",
					)
				);
				if ($oRes->isSuccess()) {
					$iUserGroup = $oRes->getId();
				}
			} else {
				$iUserGroup = $arGroup['ID'];
			}
			if (\CAmminaRegions::isIMExists()) {
				if ($arCurrentDomain['SALE_COMPANY_ID'] > 0 && $iCompanyGroup > 0) {
					$isExists = false;
					$rAllCompanyGroup = \Bitrix\Sale\Internals\CompanyGroupTable::getList(
						array(
							"filter" => array(
								"COMPANY_ID" => $arCurrentDomain['SALE_COMPANY_ID'],
							),
						)
					);
					while ($arAllCompanyGroup = $rAllCompanyGroup->fetch()) {
						if ($arAllCompanyGroup['GROUP_ID'] == $iCompanyGroup) {
							$isExists = true;
						}
					}
					if (!$isExists) {
						\Bitrix\Sale\Internals\CompanyGroupTable::add(
							array(
								"COMPANY_ID" => $arCurrentDomain['SALE_COMPANY_ID'],
								"GROUP_ID" => $iCompanyGroup,
							)
						);
					}
				}
				if ($arCurrentDomain['SALE_COMPANY_ID'] > 0 && $iUserGroup > 0) {
					$isExists = false;
					$rAllCompanyGroup = \Bitrix\Sale\Internals\CompanyResponsibleGroupTable::getList(
						array(
							"filter" => array(
								"COMPANY_ID" => $arCurrentDomain['SALE_COMPANY_ID'],
							),
						)
					);
					while ($arAllCompanyGroup = $rAllCompanyGroup->fetch()) {
						if ($arAllCompanyGroup['GROUP_ID'] == $iUserGroup) {
							$isExists = true;
						}
					}
					if (!$isExists) {
						\Bitrix\Sale\Internals\CompanyResponsibleGroupTable::add(
							array(
								"COMPANY_ID" => $arCurrentDomain['SALE_COMPANY_ID'],
								"GROUP_ID" => $iUserGroup,
							)
						);
					}
				}
			}
		}
	}

	public static function doMakeSaleCompanyLinkGroupsForDomain($ID)
	{
		$arCurrentDomain = self::getRowById($ID);
		if ($arCurrentDomain) {
			$arAllGroupsCompany = array();
			if (\CAmminaRegions::isIMExists()) {
				$rAllCompanyGroup = \Bitrix\Sale\Internals\CompanyGroupTable::getList(
					array(
						"filter" => array(
							"COMPANY_ID" => $arCurrentDomain['SALE_COMPANY_ID'],
						),
					)
				);
				while ($arAllCompanyGroup = $rAllCompanyGroup->fetch()) {
					$arAllGroupsCompany[$arAllCompanyGroup['GROUP_ID']] = $arAllCompanyGroup['GROUP_ID'];
				}
				$rAllCompanyGroup = \Bitrix\Sale\Internals\CompanyResponsibleGroupTable::getList(
					array(
						"filter" => array(
							"COMPANY_ID" => $arCurrentDomain['SALE_COMPANY_ID'],
						),
					)
				);
				while ($arAllCompanyGroup = $rAllCompanyGroup->fetch()) {
					$arAllGroupsCompany[$arAllCompanyGroup['GROUP_ID']] = $arAllCompanyGroup['GROUP_ID'];
				}
			}
			$rUserGroups = \Bitrix\Main\UserGroupTable::getList(
				array(
					"filter" => array(
						"USER_ID" => $arCurrentDomain['SALE_UID'],
					),
				)
			);
			while ($arUserGroups = $rUserGroups->fetch()) {
				if (isset($arAllGroupsCompany[$arUserGroups['GROUP_ID']])) {
					unset($arAllGroupsCompany[$arUserGroups['GROUP_ID']]);
				}
			}
			foreach ($arAllGroupsCompany as $groupId) {
				\Bitrix\Main\UserGroupTable::add(
					array(
						"USER_ID" => $arCurrentDomain['SALE_UID'],
						"GROUP_ID" => $groupId,
					)
				);
			}
		}
	}

	public static function doFindDomainByCity($CITY_ID, $SITE_ID = false)
	{
		$iDomainId = false;
		if ($SITE_ID === false) {
			$SITE_ID = SITE_ID;
		}
		if ($CITY_ID > 0) {
			$arCity = CityTable::getList(
				array(
					"filter" => array("ID" => $CITY_ID),
					"select" => array(
						"CITY_ID" => "ID",
						"REGION_ID" => "REGION_ID",
						"COUNTRY_ID" => "REGION.COUNTRY_ID",
					),
				)
			)->Fetch();
			if ($arCity) {
				$arFilter = array(
					"ACTIVE" => "Y",
					"CITY_ID" => $arCity['CITY_ID'],
				);
				if (amreg_strlen($SITE_ID) > 0) {
					$arFilter['SITE_ID'] = $SITE_ID;
				}
				$arDomain = DomainTable::getList(
					array(
						"filter" => $arFilter,
					)
				)->fetch();
				if ($arDomain) {
					$iDomainId = $arDomain['ID'];
				}
				if (!$iDomainId) {
					unset($arFilter['CITY_ID']);
					$arFilter['DOMAIN_LOCATIONS.CITY_ID'] = $arCity['CITY_ID'];
					$arDomain = DomainTable::getList(
						array(
							"filter" => $arFilter,
						)
					)->fetch();
					if ($arDomain) {
						$iDomainId = $arDomain['ID'];
					}
				}
				if (!$iDomainId) {
					unset($arFilter['DOMAIN_LOCATIONS.CITY_ID']);
					$arFilter['DOMAIN_LOCATIONS.REGION_ID'] = $arCity['REGION_ID'];
					$arDomain = DomainTable::getList(
						array(
							"filter" => $arFilter,
						)
					)->fetch();
					if ($arDomain) {
						$iDomainId = $arDomain['ID'];
					}
				}
				if (!$iDomainId) {
					unset($arFilter['DOMAIN_LOCATIONS.REGION_ID']);
					$arFilter['DOMAIN_LOCATIONS.COUNTRY_ID'] = $arCity['COUNTRY_ID'];
					$arDomain = DomainTable::getList(
						array(
							"filter" => $arFilter,
						)
					)->fetch();
					if ($arDomain) {
						$iDomainId = $arDomain['ID'];
					}
				}
			}
		}
		if (!$iDomainId) {
			$arFilter = array(
				"ACTIVE" => "Y",
				"IS_DEFAULT" => "Y",
				"SITE_ID" => SITE_ID,
			);
			if (amreg_strlen($SITE_ID) > 0) {
				$arFilter['SITE_ID'] = $SITE_ID;
			}
			$arDomain = DomainTable::getList(
				array(
					"filter" => $arFilter,
				)
			)->fetch();
			if ($arDomain) {
				$iDomainId = $arDomain['ID'];
			}/* else {
				//unset($arFilter['SITE_ID']);
				$arDomain = DomainTable::getList(array(
					"filter" => $arFilter,
				))->fetch();
				if ($arDomain) {
					$iDomainId = $arDomain['ID'];
				}
			}*/
		}
		return $iDomainId;
	}

	public static function doGetOriginalUrl($strRegionalUrl)
	{
		if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/urlrewrite.regions.php")) {
			$arRegionsUrl = @include($_SERVER['DOCUMENT_ROOT'] . "/urlrewrite.regions.php");
			foreach ($arRegionsUrl as $regionUrlPath => $arRegionalUrl) {
				foreach ($arRegionalUrl as $k => $v) {
					if (amreg_strpos($strRegionalUrl, $v['REGIONAL']) === 0) {
						$strRegionalUrl = str_replace($v["REGIONAL"], $v['ORIGINAL'], $strRegionalUrl);
						break(2);
					}
				}
			}
		}
		return $strRegionalUrl;
	}

	public static function doGetRedirectLinkByDomainId($DOMAIN_ID, $CITY_ID = false, $path = "")
	{
		global $APPLICATION;
		$strResult = false;
		if ($DOMAIN_ID > 0) {
			$arDomain = self::getRowById($DOMAIN_ID);
			if ($arDomain) {
				if (\COption::GetOptionString("ammina.regions", "use_one_domain", "N") == "Y") {
					if (amreg_strlen($path) <= 0) {
						$path = $APPLICATION->GetCurPageParam();
					}
					if ($CITY_ID !== false) {
						if (amreg_strpos($path, '?') !== false) {
							$path .= '&argcity=' . $CITY_ID;
						} else {
							$path .= '?argcity=' . $CITY_ID;
						}
					}
					$strResult = $path;
				} else {
					$arHost = array(amreg_strtolower($_SERVER[\COption::GetOptionString("ammina.regions", "host_var_name", "HTTP_HOST")]));
					if (amreg_strpos(amreg_strtolower($_SERVER[\COption::GetOptionString("ammina.regions", "host_var_name", "HTTP_HOST")]), "www.") === 0) {
						$arHost[] = amreg_substr(amreg_strtolower($_SERVER[\COption::GetOptionString("ammina.regions", "host_var_name", "HTTP_HOST")]), 4);
					} else {
						$arHost[] = "www." . amreg_strtolower($_SERVER[\COption::GetOptionString("ammina.regions", "host_var_name", "HTTP_HOST")]);
					}
					if (!in_array(amreg_strtolower($arDomain['DOMAIN']), $arHost)) {
						if (\CMain::IsHTTPS()) {
							$strResult = 'https://';
						} else {
							$strResult = 'http://';
						}
						if (amreg_strlen($path) <= 0) {
							$path = $APPLICATION->GetCurPageParam();
						}
						if ($CITY_ID !== false) {
							if (amreg_strpos($path, '?') !== false) {
								$path .= '&argcity=' . $CITY_ID;
							} else {
								$path .= '?argcity=' . $CITY_ID;
							}
						}
						$strResult .= $arDomain['DOMAIN'] . $path;
					}
				}
			}
		}
		$strResult = \CAmminaRegions::ConvertUrlToPathRegion($strResult, $DOMAIN_ID);
		return $strResult;
	}

	public static function doHackCurrency()
	{

		if (!empty($GLOBALS['AMMINA_REGIONS']['SYS_CURRENCY'])) {
			Calculation::setConfig(
				array(
					"CURRENCY" => $GLOBALS['AMMINA_REGIONS']['SYS_CURRENCY'],
				)
			);
			$setCacheSiteCurrency = static function ($siteId, $currency) {
				self::$cache[$siteId] = array(
					"LID" => $siteId,
					"CURRENCY" => $currency,
				);
			};
			$bsetCacheSiteCurrency = \Closure::bind($setCacheSiteCurrency, null, '\Bitrix\Sale\Internals\SiteCurrencyTable');
			$bsetCacheSiteCurrency(SITE_ID, $GLOBALS['AMMINA_REGIONS']['SYS_CURRENCY']);
		}
	}

	public static function doFillVariableLocativeCityName($DOMAIN_ID)
	{
		$arVarLocativeCityName = \Ammina\Regions\VariableTable::getList(
			array(
				"filter" => array(
					"CODE" => "SYS_LOCATIVE_CITY_NAME",
				),
			)
		)->fetch();
		$arRecord = \Ammina\Regions\DomainTable::getRowById($DOMAIN_ID);
		if ($arRecord['CITY_ID'] > 0) {
			$arFormatName = \Ammina\Regions\DomainVariableTable::getCityFormatInfo($arRecord['CITY_ID']);
			$strName = \morphos\Russian\RussianLanguage::in(\morphos\Russian\GeographicalNamesInflection::getCase($arFormatName['CITY_NAME'], \morphos\Russian\Cases::PREDLOJ));
			$rVariable = \Ammina\Regions\DomainVariableTable::getList(
				array(
					"filter" => array(
						"VARIABLE_ID" => $arVarLocativeCityName['ID'],
						"DOMAIN_ID" => $DOMAIN_ID,
					),
				)
			);
			if ($arVariable = $rVariable->fetch()) {
				\Ammina\Regions\DomainVariableTable::update(
					$arVariable['ID'],
					array(
						"VALUE" => $strName,
					)
				);
			} else {
				\Ammina\Regions\DomainVariableTable::add(
					array(
						"VARIABLE_ID" => $arVarLocativeCityName['ID'],
						"DOMAIN_ID" => $DOMAIN_ID,
						"VALUE" => $strName,
					)
				);
			}
		}
	}

	public static function doFillVariableLocativeRegionName($DOMAIN_ID)
	{
		$arVarLocativeCityRegionName = \Ammina\Regions\VariableTable::getList(
			array(
				"filter" => array(
					"CODE" => "SYS_LOCATIVE_REGION_NAME",
				),
			)
		)->fetch();
		$arRecord = \Ammina\Regions\DomainTable::getRowById($DOMAIN_ID);
		if ($arRecord['CITY_ID'] > 0) {
			$arFormatName = \Ammina\Regions\DomainVariableTable::getCityFormatInfo($arRecord['CITY_ID']);
			$strName = \morphos\Russian\RussianLanguage::in(\morphos\Russian\GeographicalNamesInflection::getCase($arFormatName['REGION_NAME'], \morphos\Russian\Cases::PREDLOJ));
			$rVariable = \Ammina\Regions\DomainVariableTable::getList(
				array(
					"filter" => array(
						"VARIABLE_ID" => $arVarLocativeCityRegionName['ID'],
						"DOMAIN_ID" => $DOMAIN_ID,
					),
				)
			);
			if ($arVariable = $rVariable->fetch()) {
				\Ammina\Regions\DomainVariableTable::update(
					$arVariable['ID'],
					array(
						"VALUE" => $strName,
					)
				);
			} else {
				\Ammina\Regions\DomainVariableTable::add(
					array(
						"VARIABLE_ID" => $arVarLocativeCityRegionName['ID'],
						"DOMAIN_ID" => $DOMAIN_ID,
						"VALUE" => $strName,
					)
				);
			}
		}
	}

	public static function doFillVariableLocativeCityRegionName($DOMAIN_ID)
	{
		$arVarLocativeCityRegionName = \Ammina\Regions\VariableTable::getList(
			array(
				"filter" => array(
					"CODE" => "SYS_LOCATIVE_CITY_REGION_NAME",
				),
			)
		)->fetch();
		$arRecord = \Ammina\Regions\DomainTable::getRowById($DOMAIN_ID);
		if ($arRecord['CITY_ID'] > 0) {
			$arFormatName = \Ammina\Regions\DomainVariableTable::getCityFormatInfo($arRecord['CITY_ID']);
			if ($arFormatName['CITY_NAME'] == $arFormatName['REGION_NAME']) {
				$strName = \morphos\Russian\RussianLanguage::in(\morphos\Russian\GeographicalNamesInflection::getCase($arFormatName['CITY_NAME'], \morphos\Russian\Cases::PREDLOJ));
			} else {
				$strName = \morphos\Russian\RussianLanguage::in(\morphos\Russian\GeographicalNamesInflection::getCase($arFormatName['CITY_NAME'] . " &&& " . $arFormatName['REGION_NAME'], \morphos\Russian\Cases::PREDLOJ));
			}
			$strName = str_replace("&&&", Loc::getMessage("AMMINA_REGIONS_PREDLOG_I"), $strName);
			$rVariable = \Ammina\Regions\DomainVariableTable::getList(
				array(
					"filter" => array(
						"VARIABLE_ID" => $arVarLocativeCityRegionName['ID'],
						"DOMAIN_ID" => $DOMAIN_ID,
					),
				)
			);
			if ($arVariable = $rVariable->fetch()) {
				\Ammina\Regions\DomainVariableTable::update(
					$arVariable['ID'],
					array(
						"VALUE" => $strName,
					)
				);
			} else {
				\Ammina\Regions\DomainVariableTable::add(
					array(
						"VARIABLE_ID" => $arVarLocativeCityRegionName['ID'],
						"DOMAIN_ID" => $DOMAIN_ID,
						"VALUE" => $strName,
					)
				);
			}
		}
	}

	public static function doFillVariablesPadezhCityName($DOMAIN_ID)
	{
		$arVariables = array(
			"SYS_PADEZH_IMENITELNIY_CITY_NAME" => "nominative",
			"SYS_PADEZH_RODITELNIY_CITY_NAME" => "genitive",
			"SYS_PADEZH_DATELNIY_CITY_NAME" => "dative",
			"SYS_PADEZH_VINITELNIY_CITY_NAME" => "accusative",
			"SYS_PADEZH_TVORITELNIY_CITY_NAME" => "ablative",
			"SYS_PADEZH_PREDLOJNIY_CITY_NAME" => "prepositional",
		);
		$arVarsCityName = \Ammina\Regions\VariableTable::getList(
			array(
				"filter" => array(
					"CODE" => array_keys($arVariables),
				),
			)
		)->fetchAll();
		$arRecord = \Ammina\Regions\DomainTable::getRowById($DOMAIN_ID);
		if ($arRecord['CITY_ID'] > 0) {
			$arFormatName = \Ammina\Regions\DomainVariableTable::getCityFormatInfo($arRecord['CITY_ID']);
			$arName = \morphos\Russian\GeographicalNamesInflection::getCases($arFormatName['CITY_NAME']);

			foreach ($arVarsCityName as $arVar) {
				$rVariable = \Ammina\Regions\DomainVariableTable::getList(
					array(
						"filter" => array(
							"VARIABLE_ID" => $arVar['ID'],
							"DOMAIN_ID" => $DOMAIN_ID,
						),
					)
				);
				$strName = $arName[$arVariables[$arVar['CODE']]];
				if ($arVariable = $rVariable->fetch()) {
					\Ammina\Regions\DomainVariableTable::update(
						$arVariable['ID'],
						array(
							"VALUE" => $strName,
						)
					);
				} else {
					\Ammina\Regions\DomainVariableTable::add(
						array(
							"VARIABLE_ID" => $arVar['ID'],
							"DOMAIN_ID" => $DOMAIN_ID,
							"VALUE" => $strName,
						)
					);
				}
			}
		}
	}

	public static function doFillVariablesPadezhRegionName($DOMAIN_ID)
	{
		$arVariables = array(
			"SYS_PADEZH_IMENITELNIY_REGION_NAME" => "nominative",
			"SYS_PADEZH_RODITELNIY_REGION_NAME" => "genitive",
			"SYS_PADEZH_DATELNIY_REGION_NAME" => "dative",
			"SYS_PADEZH_VINITELNIY_REGION_NAME" => "accusative",
			"SYS_PADEZH_TVORITELNIY_REGION_NAME" => "ablative",
			"SYS_PADEZH_PREDLOJNIY_REGION_NAME" => "prepositional",
		);
		$arVarsCityName = \Ammina\Regions\VariableTable::getList(
			array(
				"filter" => array(
					"CODE" => array_keys($arVariables),
				),
			)
		)->fetchAll();
		$arRecord = \Ammina\Regions\DomainTable::getRowById($DOMAIN_ID);
		if ($arRecord['CITY_ID'] > 0) {
			$arFormatName = \Ammina\Regions\DomainVariableTable::getCityFormatInfo($arRecord['CITY_ID']);
			$arName = \morphos\Russian\GeographicalNamesInflection::getCases($arFormatName['REGION_NAME']);

			foreach ($arVarsCityName as $arVar) {
				$rVariable = \Ammina\Regions\DomainVariableTable::getList(
					array(
						"filter" => array(
							"VARIABLE_ID" => $arVar['ID'],
							"DOMAIN_ID" => $DOMAIN_ID,
						),
					)
				);
				$strName = $arName[$arVariables[$arVar['CODE']]];
				if ($arVariable = $rVariable->fetch()) {
					\Ammina\Regions\DomainVariableTable::update(
						$arVariable['ID'],
						array(
							"VALUE" => $strName,
						)
					);
				} else {
					\Ammina\Regions\DomainVariableTable::add(
						array(
							"VARIABLE_ID" => $arVar['ID'],
							"DOMAIN_ID" => $DOMAIN_ID,
							"VALUE" => $strName,
						)
					);
				}
			}
		}
	}

	public static function doFillVariablesPadezhCityRegionName($DOMAIN_ID)
	{
		$arVariables = array(
			"SYS_PADEZH_IMENITELNIY_CITY_REGION_NAME" => "nominative",
			"SYS_PADEZH_RODITELNIY_CITY_REGION_NAME" => "genitive",
			"SYS_PADEZH_DATELNIY_CITY_REGION_NAME" => "dative",
			"SYS_PADEZH_VINITELNIY_CITY_REGION_NAME" => "accusative",
			"SYS_PADEZH_TVORITELNIY_CITY_REGION_NAME" => "ablative",
			"SYS_PADEZH_PREDLOJNIY_CITY_REGION_NAME" => "prepositional",
		);
		$arVarsCityName = \Ammina\Regions\VariableTable::getList(
			array(
				"filter" => array(
					"CODE" => array_keys($arVariables),
				),
			)
		)->fetchAll();
		$arRecord = \Ammina\Regions\DomainTable::getRowById($DOMAIN_ID);
		if ($arRecord['CITY_ID'] > 0) {
			$arFormatName = \Ammina\Regions\DomainVariableTable::getCityFormatInfo($arRecord['CITY_ID']);
			if ($arFormatName['CITY_NAME'] == $arFormatName['REGION_NAME']) {
				$arName = \morphos\Russian\GeographicalNamesInflection::getCases($arFormatName['CITY_NAME']);
			} else {
				$arName = \morphos\Russian\GeographicalNamesInflection::getCases($arFormatName['CITY_NAME'] . " &&& " . $arFormatName['REGION_NAME']);
			}

			foreach ($arVarsCityName as $arVar) {
				$rVariable = \Ammina\Regions\DomainVariableTable::getList(
					array(
						"filter" => array(
							"VARIABLE_ID" => $arVar['ID'],
							"DOMAIN_ID" => $DOMAIN_ID,
						),
					)
				);
				$strName = $arName[$arVariables[$arVar['CODE']]];
				$strName = str_replace("&&&", Loc::getMessage("AMMINA_REGIONS_PREDLOG_I"), $strName);
				if ($arVariable = $rVariable->fetch()) {
					\Ammina\Regions\DomainVariableTable::update(
						$arVariable['ID'],
						array(
							"VALUE" => $strName,
						)
					);
				} else {
					\Ammina\Regions\DomainVariableTable::add(
						array(
							"VARIABLE_ID" => $arVar['ID'],
							"DOMAIN_ID" => $DOMAIN_ID,
							"VALUE" => $strName,
						)
					);
				}
			}
		}
	}

	public static function doCheckDomainHostInSite($DOMAIN_ID)
	{
		global $CACHE_MANAGER;
		if ($DOMAIN_ID > 0) {
			$arRecord = \Ammina\Regions\DomainTable::getRowById($DOMAIN_ID);
			if ($arRecord) {
				$arSiteDomain = \Bitrix\Main\SiteDomainTable::getList(
					array(
						"filter" => array(
							"LID" => $arRecord['SITE_ID'],
							"DOMAIN" => amreg_strtolower($arRecord['DOMAIN']),
						),
					)
				)->fetch();
				if (!$arSiteDomain) {
					\Bitrix\Main\SiteDomainTable::add(
						array(
							"LID" => $arRecord['SITE_ID'],
							"DOMAIN" => amreg_strtolower($arRecord['DOMAIN']),
						)
					);
				}
				if (CACHED_b_lang !== false) {
					$CACHE_MANAGER->CleanDir("b_lang");
				}
				if (CACHED_b_lang_domain !== false) {
					$CACHE_MANAGER->CleanDir("b_lang_domain");
				}
			}
		}
	}

	public static function doCheckIBlockPropsList()
	{
		$strProp = trim(\COption::GetOptionString("ammina.regions", "iblock_prop_domains"));
		$arPropCheck = explode("|", $strProp);
		if (amreg_strlen($strProp) > 0 && !empty($arPropCheck)) {
			$obPropEnum = new \CIBlockPropertyEnum();
			$arAllDomains = array();
			$rDomains = DomainTable::getList(
				array(
					"order" => array("NAME" => "ASC"),
					"select" => array("ID", "NAME")
				)
			);
			while ($arDomain = $rDomains->fetch()) {
				$arAllDomains[$arDomain['ID']] = $arDomain['NAME'];
			}
			$arAllPropValues = array();
			foreach ($arPropCheck as $val) {
				$arAllPropValues[$val] = array();
			}
			foreach ($arPropCheck as $val) {
				if ($val > 0) {
					$rPropEnum = \CIBlockPropertyEnum::GetList(
						array(),
						array(
							"PROPERTY_ID" => $val
						)
					);
					while ($arPropEnum = $rPropEnum->Fetch()) {
						$arAllPropValues[$arPropEnum['PROPERTY_ID']][$arPropEnum['XML_ID']] = $arPropEnum;
					}
				}
			}
			foreach ($arAllDomains as $k => $v) {
				foreach ($arAllPropValues as $k1 => $v1) {
					if ($k1 <= 0) {
						continue;
					}
					if (isset($v1["D" . $k])) {
						if ($v1["D" . $k] != $v) {
							$obPropEnum->Update($v1['ID'], array("VALUE" => $v));
						}
						unset($arAllPropValues[$k1]["D" . $k]);
					} else {
						$obPropEnum->Add(
							array(
								"PROPERTY_ID" => $k1,
								"XML_ID" => "D" . $k,
								"VALUE" => $v
							)
						);
					}
				}
			}
			foreach ($arAllPropValues as $k => $v) {
				foreach ($v as $k1 => $v1) {
					if (amreg_strpos($k1, 'D') === 0) {
						$obPropEnum->Delete($v1['ID']);
					}
				}
			}
		}
	}

	public static function getLangName($domainId, $lang = LANGUAGE_ID)
	{
		$arAllNames = array();
		$arDomain = self::getList(array(
			"filter" => array(
				"ID" => $domainId
			),
			"select" => array(
				"ID", "NAME", "NAME_LANG"
			)
		))->fetch();
		if ($arDomain) {
			$arAllNames['ru'] = $arDomain['NAME'];
			if (is_array($arDomain['NAME_LANG'])) {
				$arAllNames = array_merge($arAllNames, $arDomain['NAME_LANG']);
			}
		}
		return \CAmminaRegions::getLangFirstName($arAllNames, $lang);
	}
}