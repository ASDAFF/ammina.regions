<?

namespace Kit\MultiRegions\Agent;


use Kit\MultiRegions\DomainTable;
use Bitrix\Main\IO\File;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SiteTable;
use Bitrix\Main\Type\DateTime;
use Bitrix\Seo\SitemapRuntime;
use Bitrix\Seo\SitemapRuntimeTable;
use Bitrix\Seo\SitemapTable;
use Bitrix\Main;
use Bitrix\Main\IO;
use Bitrix\Seo\RobotsFile;
use Bitrix\Seo\SitemapIblock;
use Bitrix\Seo\SitemapIndex;

class SiteMapGenerate
{
	public static $arSitemapSettings = array();
	public static $NS = array();
	public static $arPropDomainAvailable = false;
	public static $arPropDomainShow = false;
	public static $arPropDomainHide = false;
	public static $arAllPropDomain = false;

	public static function doExecute()
	{
		@set_time_limit(0);
		\CModule::IncludeModule("kit.multiregions");
		$iMemory = intval(\COption::GetOptionString("kit.multiregions", "sitemapagent_memorylimit", ""));
		if ($iMemory > 0) {
			@ini_set("memory_limit", $iMemory . "M");
		}
		if (\CModule::IncludeModule("seo")) {
			$arCurrentAgent = \CAgent::GetList(array(), array(
				"NAME" => '\Kit\MultiRegions\Agent\SiteMapGenerate::doExecute();',
				"MODULE_ID" => "kit.multiregions",
			))->Fetch();
			if ($arCurrentAgent) {
				\CAgent::Update($arCurrentAgent['ID'], array("NEXT_EXEC" => ConvertTimeStamp(time() + 3600 * 12, "FULL")));
			}
			if ((defined("BX_CRONTAB") && BX_CRONTAB === true) || (defined("CHK_EVENT") && CHK_EVENT === true)) {
				self::doExecuteAgent();
			} else {
				if (\COption::GetOptionString("kit.multiregions", "sitemapagent_onlycron", "N") != "Y") {
					self::doExecuteAgent();
				}
			}
		}
		return '\Kit\MultiRegions\Agent\SiteMapGenerate::doExecute();';
	}

	public static function doExecuteAgent()
	{
		$minTimeRun = time() - \COption::GetOptionString("kit.multiregions", "sitemapagent_period_run", "7") * 3600 * 24;
		$arSitemap = SitemapTable::getList(array(
			"order" => array(
				"DATE_RUN" => "ASC",
				"ID" => "ASC",
			),
			"filter" => array(
				"ACTIVE" => "Y",
				array(
					"LOGIC" => "OR",
					array(
						"DATE_RUN" => false,
					),
					array(
						"<=DATE_RUN" => ConvertTimeStamp($minTimeRun, "FULL"),
					),
				),
			),
		))->fetch();
		if ($arSitemap) {
			$arSitemap['SETTINGS'] = unserialize($arSitemap['SETTINGS']);
			self::$arSitemapSettings = array(
				'SITE_ID' => $arSitemap['SITE_ID'],
				'PROTOCOL' => $arSitemap['SETTINGS']['PROTO'] == 1 ? 'https' : 'http',
				'DOMAIN' => $arSitemap['SETTINGS']['DOMAIN'],
				"DOMAIN_RECORD" => DomainTable::getList(array(
					"filter" => array(
						"DOMAIN" => $arSitemap['SETTINGS']['DOMAIN']
					)
				))->fetch()
			);
			if ($arSitemap) {
				self::doGenerateSiteMap($arSitemap);
			}
		}
	}

	public static function doGenerateSiteMap($arSitemap)
	{
		$sitemapFile = self::doStepInit($arSitemap['ID'], $arSitemap);
		self::doStepFiles($arSitemap['ID'], $arSitemap, $sitemapFile);
		self::doStepIblockIndex($arSitemap['ID'], $arSitemap, $sitemapFile);
		self::doStepIblock($arSitemap['ID'], $arSitemap, $sitemapFile);
		self::doStepForumIndex($arSitemap['ID'], $arSitemap, $sitemapFile);
		self::doStepForum($arSitemap['ID'], $arSitemap, $sitemapFile);
		self::doStepGenerate($arSitemap['ID'], $arSitemap, $sitemapFile);

		SitemapTable::update($arSitemap['ID'], array('DATE_RUN' => new DateTime()));
	}

	public static function doStepInit($ID, $arSitemap)
	{
		SitemapRuntimeTable::clearByPid($ID);

		self::$NS['time_start'] = microtime(true);
		self::$NS['files_count'] = 0;
		self::$NS['steps_count'] = 0;

		$bRootChecked = isset($arSitemap['SETTINGS']['DIR']['/']) && $arSitemap['SETTINGS']['DIR']['/'] == 'Y';

		$arRuntimeData = array(
			'PID' => $ID,
			'ITEM_TYPE' => SitemapRuntimeTable::ITEM_TYPE_DIR,
			'ITEM_PATH' => '/',
			'PROCESSED' => SitemapRuntimeTable::UNPROCESSED,
			'ACTIVE' => $bRootChecked ? SitemapRuntimeTable::ACTIVE : SitemapRuntimeTable::INACTIVE,
		);

		SitemapRuntimeTable::add($arRuntimeData);

		$sitemapFile = new SitemapRuntime($ID, $arSitemap['SETTINGS']['FILENAME_FILES'], self::$arSitemapSettings);
		return $sitemapFile;
	}

	public static function doStepFiles($PID, $arSitemap, $sitemapFile)
	{
		$bFinished = false;
		$bCheckFinished = false;

		$dbRes = NULL;

		while (!$bFinished) {
			if (!$dbRes) {
				$dbRes = SitemapRuntimeTable::getList(array(
					'order' => array('ITEM_PATH' => 'ASC'),
					'filter' => array(
						'PID' => $PID,
						'ITEM_TYPE' => SitemapRuntimeTable::ITEM_TYPE_DIR,
						'PROCESSED' => SitemapRuntimeTable::UNPROCESSED,
					),
					'limit' => 1000,
				));
			}

			if ($arRes = $dbRes->Fetch()) {
				self::seoSitemapGetFilesData($PID, $arSitemap, $arRes, $sitemapFile);
				$bCheckFinished = false;
			} elseif (!$bCheckFinished) {
				$dbRes = NULL;
				$bCheckFinished = true;
			} else {
				$bFinished = true;
			}
		}
		if (!is_array(self::$NS['XML_FILES']))
			self::$NS['XML_FILES'] = array();

		if ($sitemapFile->isNotEmpty()) {
			if ($sitemapFile->isCurrentPartNotEmpty()) {
				$sitemapFile->finish();
			} else {
				$sitemapFile->delete();
			}

			$xmlFiles = $sitemapFile->getNameList();
			$directory = $sitemapFile->getPathDirectory();
			foreach ($xmlFiles as &$xmlFile)
				$xmlFile = $directory . $xmlFile;
			self::$NS['XML_FILES'] = array_unique(array_merge(self::$NS['XML_FILES'], $xmlFiles));
		} else {
			$sitemapFile->delete();
		}
	}

	public static function doStepIblockIndex($PID, $arSitemap, $sitemapFile)
	{
		$arIBlockList = array();
		if (Main\Loader::includeModule('iblock')) {
			$arIBlockList = $arSitemap['SETTINGS']['IBLOCK_ACTIVE'];
			if (count($arIBlockList) > 0) {
				$arIBlocks = array();
				$dbIBlock = \CIBlock::GetList(array(), array('ID' => array_keys($arIBlockList)));
				while ($arIBlock = $dbIBlock->Fetch()) {
					$arIBlocks[$arIBlock['ID']] = $arIBlock;
				}

				foreach ($arIBlockList as $iblockId => $iblockActive) {
					if ($iblockActive !== 'Y' || !array_key_exists($iblockId, $arIBlocks)) {
						unset($arIBlockList[$iblockId]);
					} else {
						SitemapRuntimeTable::add(array(
							'PID' => $PID,
							'PROCESSED' => SitemapRuntimeTable::UNPROCESSED,
							'ITEM_ID' => $iblockId,
							'ITEM_TYPE' => SitemapRuntimeTable::ITEM_TYPE_IBLOCK,
						));

						$fileName = str_replace(
							array('#IBLOCK_ID#', '#IBLOCK_CODE#', '#IBLOCK_XML_ID#'),
							array($iblockId, $arIBlocks[$iblockId]['CODE'], $arIBlocks[$iblockId]['XML_ID']),
							$arSitemap['SETTINGS']['FILENAME_IBLOCK']
						);

						$sitemapFile = new SitemapRuntime($PID, $fileName, self::$arSitemapSettings);
						if ($sitemapFile->isExists()) {
							//$sitemapFile->delete();
						}
					}
				}
			}
		}

		self::$NS['LEFT_MARGIN'] = 0;
		self::$NS['IBLOCK_LASTMOD'] = 0;

		self::$NS['IBLOCK'] = array();
		self::$NS['IBLOCK_MAP'] = array();

		/*if(count($arIBlockList) <= 0)
		{
			$v = $arValueSteps['iblock'];
			$msg = Loc::getMessage('SITEMAP_RUN_IBLOCK_EMPTY');
		}
		else
		{
			$v = $arValueSteps['iblock_index'];
			$msg = Loc::getMessage('SITEMAP_RUN_IBLOCK');
		}
		*/
	}

	public static function doStepIblock($PID, $arSitemap, $sitemapFile)
	{
		$bFinished = false;
		$bCheckFinished = false;

		$currentIblock = false;
		$iblockId = 0;

		$dbOldIblockResult = NULL;
		$dbIblockResult = NULL;

		$bTakeProp = (\COption::GetOptionString("kit.multiregions", "sitemapagent_take_prop", "N") == "Y" ? true : false);

		while (!$bFinished && \CModule::IncludeModule("iblock")) {
			if (!$currentIblock) {
				$arCurrentIBlock = false;
				$dbRes = SitemapRuntimeTable::getList(array(
					'order' => array('ID' => 'ASC'),
					'filter' => array(
						'PID' => $PID,
						'ITEM_TYPE' => SitemapRuntimeTable::ITEM_TYPE_IBLOCK,
						'PROCESSED' => SitemapRuntimeTable::UNPROCESSED,
					),
					'limit' => 1,
				));

				$currentIblock = $dbRes->fetch();

				if ($currentIblock) {
					$iblockId = intval($currentIblock['ITEM_ID']);

					$dbIBlock = \CIBlock::GetByID($iblockId);
					$arCurrentIBlock = $dbIBlock->Fetch();

					if (!$arCurrentIBlock) {
						SitemapRuntimeTable::update($currentIblock['ID'], array(
							'PROCESSED' => SitemapRuntimeTable::PROCESSED,
						));

						self::$NS['LEFT_MARGIN'] = 0;
						self::$NS['IBLOCK_LASTMOD'] = 0;
						self::$NS['LAST_ELEMENT_ID'] = 0;
						unset(self::$NS['CURRENT_SECTION']);
					} else {
						if (amreg_strlen($arCurrentIBlock['LIST_PAGE_URL']) <= 0)
							$arSitemap['SETTINGS']['IBLOCK_LIST'][$iblockId] = 'N';
						if (amreg_strlen($arCurrentIBlock['SECTION_PAGE_URL']) <= 0)
							$arSitemap['SETTINGS']['IBLOCK_SECTION'][$iblockId] = 'N';
						if (amreg_strlen($arCurrentIBlock['DETAIL_PAGE_URL']) <= 0)
							$arSitemap['SETTINGS']['IBLOCK_ELEMENT'][$iblockId] = 'N';

						self::$NS['IBLOCK_LASTMOD'] = max(self::$NS['IBLOCK_LASTMOD'], MakeTimeStamp($arCurrentIBlock['TIMESTAMP_X']));

						if (self::$NS['LEFT_MARGIN'] <= 0 && $arSitemap['SETTINGS']['IBLOCK_ELEMENT'][$iblockId] != 'N') {
							self::$NS['CURRENT_SECTION'] = 0;
						}

						$fileName = str_replace(
							array('#IBLOCK_ID#', '#IBLOCK_CODE#', '#IBLOCK_XML_ID#'),
							array($iblockId, $arCurrentIBlock['CODE'], $arCurrentIBlock['XML_ID']),
							$arSitemap['SETTINGS']['FILENAME_IBLOCK']
						);
						$sitemapFile = new SitemapRuntime($PID, $fileName, self::$arSitemapSettings);
					}
				}
			}

			if (!$currentIblock) {
				$bFinished = true;
			} elseif (is_array($arCurrentIBlock)) {
				if ($dbIblockResult == NULL) {
					if (isset(self::$NS['CURRENT_SECTION'])) {
						$dbIblockResult = \CIBlockElement::GetList(
							array('ID' => 'ASC'),
							array(
								'IBLOCK_ID' => $iblockId,
								'ACTIVE' => 'Y',
								'SECTION_ID' => intval(self::$NS['CURRENT_SECTION']),
								'>ID' => intval(self::$NS['LAST_ELEMENT_ID']),
								'SITE_ID' => $arSitemap['SITE_ID'],
								"ACTIVE_DATE" => "Y",
							),
							false,
							array('nTopCount' => 1000),
							array('ID', 'TIMESTAMP_X', 'DETAIL_PAGE_URL')
						);
					} else {
						self::$NS['LAST_ELEMENT_ID'] = 0;
						$dbIblockResult = \CIBlockSection::GetList(
							array('LEFT_MARGIN' => 'ASC'),
							array(
								'IBLOCK_ID' => $iblockId,
								'GLOBAL_ACTIVE' => 'Y',
								'>LEFT_BORDER' => intval(self::$NS['LEFT_MARGIN']),
							),
							false,
							array(
								'ID', 'TIMESTAMP_X', 'SECTION_PAGE_URL', 'LEFT_MARGIN', 'IBLOCK_SECTION_ID',
							),
							array('nTopCount' => 100)
						);
					}
				}

				if (isset(self::$NS['CURRENT_SECTION'])) {
					$arElement = $dbIblockResult->fetch();

					if ($arElement) {
						if (!is_array(self::$NS['IBLOCK_MAP'][$iblockId])) {
							self::$NS['IBLOCK_MAP'][$iblockId] = array();
						}

						if (!array_key_exists($arElement['ID'], self::$NS['IBLOCK_MAP'][$iblockId])) {

							$bAllow = true;
							$domainId = self::$arSitemapSettings['DOMAIN_RECORD']['ID'];
							if ($bTakeProp && $domainId > 0) {
								$bAllow = self::doCheckElementAllowInDomain($iblockId, $arElement['ID'], $domainId);
							}
							if ($bAllow) {
								$arElement['LANG_DIR'] = $arSitemap['SITE']['DIR'];

								$bCheckFinished = false;
								$elementLastmod = MakeTimeStamp($arElement['TIMESTAMP_X']);
								self::$NS['IBLOCK_LASTMOD'] = max(self::$NS['IBLOCK_LASTMOD'], $elementLastmod);

								self::$NS['IBLOCK'][$iblockId]['E']++;
								self::$NS['IBLOCK_MAP'][$iblockId][$arElement["ID"]] = 1;

								//							remove or replace SERVER_NAME
								$url = SitemapIblock::prepareUrlToReplace($arElement['DETAIL_PAGE_URL'], $arSitemap['SITE_ID']);
								$url = \CIBlock::ReplaceDetailUrl($url, $arElement, false, "E");

								$sitemapFile->addIBlockEntry($url, $elementLastmod);
							}
							self::$NS['LAST_ELEMENT_ID'] = $arElement['ID'];
						}
					} elseif (!$bCheckFinished) {
						$bCheckFinished = true;
						$dbIblockResult = NULL;
					} else {
						$bCheckFinished = false;
						unset(self::$NS['CURRENT_SECTION']);
						self::$NS['LAST_ELEMENT_ID'] = 0;

						$dbIblockResult = NULL;
						if ($dbOldIblockResult) {
							$dbIblockResult = $dbOldIblockResult;
							$dbOldIblockResult = NULL;
						}
					}
				} else {
					$arSection = $dbIblockResult->fetch();

					if ($arSection) {
						$bCheckFinished = false;
						$sectionLastmod = MakeTimeStamp($arSection['TIMESTAMP_X']);
						self::$NS['LEFT_MARGIN'] = $arSection['LEFT_MARGIN'];
						self::$NS['IBLOCK_LASTMOD'] = max(self::$NS['IBLOCK_LASTMOD'], $sectionLastmod);

						$bActive = false;
						$bActiveElement = false;

						if (isset($arSitemap['SETTINGS']['IBLOCK_SECTION_SECTION'][$iblockId][$arSection['ID']])) {
							$bActive = $arSitemap['SETTINGS']['IBLOCK_SECTION_SECTION'][$iblockId][$arSection['ID']] == 'Y';
							$bActiveElement = $arSitemap['SETTINGS']['IBLOCK_SECTION_ELEMENT'][$iblockId][$arSection['ID']] == 'Y';
						} elseif ($arSection['IBLOCK_SECTION_ID'] > 0) {
							$dbRes = SitemapRuntimeTable::getList(array(
								'filter' => array(
									'PID' => $PID,
									'ITEM_TYPE' => SitemapRuntimeTable::ITEM_TYPE_SECTION,
									'ITEM_ID' => $arSection['IBLOCK_SECTION_ID'],
									'PROCESSED' => SitemapRuntimeTable::PROCESSED,
								),
								'select' => array('ACTIVE', 'ACTIVE_ELEMENT'),
								'limit' => 1,
							));

							$parentSection = $dbRes->fetch();
							if ($parentSection) {
								$bActive = $parentSection['ACTIVE'] == SitemapRuntimeTable::ACTIVE;
								$bActiveElement = $parentSection['ACTIVE_ELEMENT'] == SitemapRuntimeTable::ACTIVE;
							}
						} else {
							$bActive = $arSitemap['SETTINGS']['IBLOCK_SECTION'][$iblockId] == 'Y';
							$bActiveElement = $arSitemap['SETTINGS']['IBLOCK_ELEMENT'][$iblockId] == 'Y';
						}

						$arRuntimeData = array(
							'PID' => $PID,
							'ITEM_ID' => $arSection['ID'],
							'ITEM_TYPE' => SitemapRuntimeTable::ITEM_TYPE_SECTION,
							'ACTIVE' => $bActive ? SitemapRuntimeTable::ACTIVE : SitemapRuntimeTable::INACTIVE,
							'ACTIVE_ELEMENT' => $bActiveElement ? SitemapRuntimeTable::ACTIVE : SitemapRuntimeTable::INACTIVE,
							'PROCESSED' => SitemapRuntimeTable::PROCESSED,
						);

						if ($bActive) {
							self::$NS['IBLOCK'][$iblockId]['S']++;

							$arSection['LANG_DIR'] = $arSitemap['SITE']['DIR'];

							//							remove or replace SERVER_NAME
							$url = SitemapIblock::prepareUrlToReplace($arSection['SECTION_PAGE_URL'], $arSitemap['SITE_ID']);
							$url = \CIBlock::ReplaceDetailUrl($url, $arSection, false, "S");

							$sitemapFile->addIBlockEntry($url, $sectionLastmod);
						}

						SitemapRuntimeTable::add($arRuntimeData);

						if ($bActiveElement) {
							self::$NS['CURRENT_SECTION'] = $arSection['ID'];
							self::$NS['LAST_ELEMENT_ID'] = 0;

							$dbOldIblockResult = $dbIblockResult;
							$dbIblockResult = NULL;
						}

					} elseif (!$bCheckFinished) {
						unset(self::$NS['CURRENT_SECTION']);
						$bCheckFinished = true;
						$dbIblockResult = NULL;
					} else {
						$bCheckFinished = false;
						// we have finished current iblock

						SitemapRuntimeTable::update($currentIblock['ID'], array(
							'PROCESSED' => SitemapRuntimeTable::PROCESSED,
						));

						if ($arSitemap['SETTINGS']['IBLOCK_LIST'][$iblockId] == 'Y' && amreg_strlen($arCurrentIBlock['LIST_PAGE_URL']) > 0) {
							self::$NS['IBLOCK'][$iblockId]['I']++;

							$arCurrentIBlock['IBLOCK_ID'] = $arCurrentIBlock['ID'];
							$arCurrentIBlock['LANG_DIR'] = $arSitemap['SITE']['DIR'];

							//							remove or replace SERVER_NAME
							$url = SitemapIblock::prepareUrlToReplace($arCurrentIBlock['LIST_PAGE_URL'], $arSitemap['SITE_ID']);
							$url = \CIBlock::ReplaceDetailUrl($url, $arCurrentIBlock, false, "");

							$sitemapFile->addIBlockEntry($url, self::$NS['IBLOCK_LASTMOD']);
						}

						if ($sitemapFile->isNotEmpty()) {
							if ($sitemapFile->isCurrentPartNotEmpty()) {
								$sitemapFile->finish();
							} else {
								$sitemapFile->delete();
							}

							if (!is_array(self::$NS['XML_FILES']))
								self::$NS['XML_FILES'] = array();

							$xmlFiles = $sitemapFile->getNameList();
							$directory = $sitemapFile->getPathDirectory();
							foreach ($xmlFiles as &$xmlFile)
								$xmlFile = $directory . $xmlFile;
							self::$NS['XML_FILES'] = array_unique(array_merge(self::$NS['XML_FILES'], $xmlFiles));
						} else {
							$sitemapFile->delete();
						}

						$currentIblock = false;
						self::$NS['LEFT_MARGIN'] = 0;
						self::$NS['IBLOCK_LASTMOD'] = 0;
						unset(self::$NS['CURRENT_SECTION']);
						self::$NS['LAST_ELEMENT_ID'] = 0;
					}
				}
			}
		}
	}

	public static function doStepForumIndex($PID, $arSitemap, $sitemapFile)
	{
		$arForumList = array();
		if (!empty($arSitemap['SETTINGS']['FORUM_ACTIVE'])) {
			foreach ($arSitemap['SETTINGS']['FORUM_ACTIVE'] as $forumId => $active) {
				if ($active == "Y") {
					$arForumList[$forumId] = "Y";
				}
			}
		}
		if (count($arForumList) > 0 && Main\Loader::includeModule('forum')) {
			$arForums = array();
			$db_res = \CForumNew::GetListEx(
				array(),
				array(
					'@ID' => array_keys($arForumList),
					"ACTIVE" => "Y",
					"SITE_ID" => $arSitemap['SITE_ID'],
					"!TOPICS" => 0,
				)
			);
			while ($res = $db_res->Fetch()) {
				$arForums[$res['ID']] = $res;
			}
			$arForumList = array_intersect_key($arForums, $arForumList);

			foreach ($arForumList as $id => $forum) {
				SitemapRuntimeTable::add(array(
						'PID' => $PID,
						'PROCESSED' => SitemapRuntimeTable::UNPROCESSED,
						'ITEM_ID' => $id,
						'ITEM_TYPE' => SitemapRuntimeTable::ITEM_TYPE_FORUM)
				);

				$fileName = str_replace('#FORUM_ID#', $forumId, $arSitemap['SETTINGS']['FILENAME_FORUM']);
				$sitemapFile = new SitemapRuntime($PID, $fileName, self::$arSitemapSettings);
			}
		}

		self::$NS['FORUM_CURRENT_TOPIC'] = 0;
	}

	public static function doStepForum($PID, $arSitemap, $sitemapFile)
	{
		$bFinished = false;
		$bCheckFinished = false;

		$currentForum = false;
		$forumId = 0;

		$dbTopicResult = NULL;
		$arTopic = NULL;

		while (!$bFinished && \CModule::IncludeModule("forum")) {
			if (!$currentForum) {
				$arCurrentForum = false;
				$dbRes = SitemapRuntimeTable::getList(array(
					'order' => array('ID' => 'ASC'),
					'filter' => array(
						'PID' => $PID,
						'ITEM_TYPE' => SitemapRuntimeTable::ITEM_TYPE_FORUM,
						'PROCESSED' => SitemapRuntimeTable::UNPROCESSED,
					),
					'limit' => 1,
				));

				$currentForum = $dbRes->fetch();

				if ($currentForum) {
					$forumId = intval($currentForum['ITEM_ID']);

					$db_res = \CForumNew::GetListEx(
						array(),
						array(
							'ID' => $forumId,
							"ACTIVE" => "Y",
							"SITE_ID" => $arSitemap['SITE_ID'],
							"!TOPICS" => 0,
						)
					);
					$arCurrentForum = $db_res->Fetch();
					if (!$arCurrentForum) {
						SitemapRuntimeTable::update($currentForum['ID'], array(
							'PROCESSED' => SitemapRuntimeTable::PROCESSED,
						));
					} else {
						$fileName = str_replace('#FORUM_ID#', $forumId, $arSitemap['SETTINGS']['FILENAME_FORUM']);
						$sitemapFile = new SitemapRuntime($PID, $fileName, self::$arSitemapSettings);
					}
				}
			}

			if (!$currentForum) {
				$bFinished = true;
			} elseif (is_array($arCurrentForum)) {
				$bActive = (array_key_exists($forumId, $arSitemap['SETTINGS']['FORUM_TOPIC']) && $arSitemap['SETTINGS']['FORUM_TOPIC'][$forumId] == "Y");
				if ($bActive) {
					if ($dbTopicResult == NULL) {
						$dbTopicResult = \CForumTopic::GetList(
							array("LAST_POST_DATE" => "DESC"),
							array_merge(
								array(
									"FORUM_ID" => $forumId,
									"APPROVED" => "Y"),
								(self::$NS['FORUM_CURRENT_TOPIC'] > 0 ? array(
									">ID" => self::$NS["FORUM_CURRENT_TOPIC"],
								) : array()
								)
							),
							false,
							0,
							array('nTopCount' => 100)
						);
					}
					if (($arTopic = $dbTopicResult->fetch()) && $arTopic) {
						self::$NS["FORUM_CURRENT_TOPIC"] = $arTopic["ID"];
						$url = \CForumNew::PreparePath2Message(
							$arCurrentForum["PATH2FORUM_MESSAGE"],
							array(
								"FORUM_ID" => $arCurrentForum["ID"],
								"TOPIC_ID" => $arTopic["ID"],
								"TITLE_SEO" => $arTopic["TITLE_SEO"],
								"MESSAGE_ID" => "s",
								"SOCNET_GROUP_ID" => $arTopic["SOCNET_GROUP_ID"],
								"OWNER_ID" => $arTopic["OWNER_ID"],
								"PARAM1" => $arTopic["PARAM1"],
								"PARAM2" => $arTopic["PARAM2"],
							)
						);
						$sitemapFile->addIBlockEntry($url, MakeTimeStamp($arTopic['LAST_POST_DATE']));
					}
				} else {
					$url = \CForumNew::PreparePath2Message(
						$arCurrentForum["PATH2FORUM_MESSAGE"],
						array(
							"FORUM_ID" => $arCurrentForum["ID"],
							"TOPIC_ID" => $arCurrentForum["TID"],
							"TITLE_SEO" => $arCurrentForum["TITLE_SEO"],
							"MESSAGE_ID" => "s",
							"SOCNET_GROUP_ID" => $arCurrentForum["SOCNET_GROUP_ID"],
							"OWNER_ID" => $arCurrentForum["OWNER_ID"],
							"PARAM1" => $arCurrentForum["PARAM1"],
							"PARAM2" => $arCurrentForum["PARAM2"],
						)
					);
					$sitemapFile->addIBlockEntry($url, MakeTimeStamp($arCurrentForum['LAST_POST_DATE']));
				}
				if (empty($arTopic)) {
					$bCheckFinished = false;
					// we have finished current forum

					SitemapRuntimeTable::update($currentForum['ID'], array(
						'PROCESSED' => SitemapRuntimeTable::PROCESSED,
					));

					if ($sitemapFile->isNotEmpty()) {
						if ($sitemapFile->isCurrentPartNotEmpty()) {
							$sitemapFile->finish();
						} else {
							$sitemapFile->delete();
						}

						if (!is_array(self::$NS['XML_FILES']))
							self::$NS['XML_FILES'] = array();

						$xmlFiles = $sitemapFile->getNameList();
						$directory = $sitemapFile->getPathDirectory();
						foreach ($xmlFiles as &$xmlFile)
							$xmlFile = $directory . $xmlFile;
						self::$NS['XML_FILES'] = array_unique(array_merge(self::$NS['XML_FILES'], $xmlFiles));
					} else {
						$sitemapFile->delete();
					}

					$currentForum = false;
					$dbTopicResult = NULL;
					self::$NS['FORUM_CURRENT_TOPIC'] = 0;
				}
			}
		}
	}

	public static function doStepGenerate($PID, $arSitemap, $sitemapFile)
	{
		SitemapRuntimeTable::clearByPid($PID);

		$arFiles = array();

		$sitemapFile = new SitemapIndex($arSitemap['SETTINGS']['FILENAME_INDEX'], self::$arSitemapSettings);

		if (count(self::$NS['XML_FILES']) > 0) {
			foreach (self::$NS['XML_FILES'] as $xmlFile) {
				$arFiles[] = new IO\File(IO\Path::combine(
					$sitemapFile->getSiteRoot(),
					$xmlFile
				), $arSitemap['SITE_ID']);
			}
		}

		$sitemapFile->createIndex($arFiles);

		$arExistedSitemaps = array();

		if ($arSitemap['SETTINGS']['ROBOTS'] == 'Y') {
			$sitemapUrl = $sitemapFile->getUrl();

			$robotsFile = new RobotsFile($arSitemap['SITE_ID']);
			$robotsFile->addRule(
				array(RobotsFile::SITEMAP_RULE, $sitemapUrl)
			);

			$arSitemapLinks = $robotsFile->getRules(RobotsFile::SITEMAP_RULE);
			if (count($arSitemapLinks) > 1) // 1 - just added rule
			{
				foreach ($arSitemapLinks as $rule) {
					if ($rule[1] != $sitemapUrl) {
						$arExistedSitemaps[] = $rule[1];
					}
				}
			}
		}
	}

	public static function seoSitemapGetFilesData($PID, $arSitemap, $arCurrentDir, $sitemapFile)
	{
		$arDirList = array();

		if ($arCurrentDir['ACTIVE'] == SitemapRuntimeTable::ACTIVE) {
			$list = self::getDirStructure(
				$arSitemap['SETTINGS']['logical'] == 'Y',
				$arSitemap['SITE_ID'],
				$arCurrentDir['ITEM_PATH']
			);

			foreach ($list as $dir) {
				$dirKey = "/" . ltrim($dir['DATA']['ABS_PATH'], "/");

				if ($dir['TYPE'] == 'F') {
					if (!isset($arSitemap['SETTINGS']['FILE'][$dirKey])
						|| $arSitemap['SETTINGS']['FILE'][$dirKey] == 'Y') {
						if (preg_match($arSitemap['SETTINGS']['FILE_MASK_REGEXP'], $dir['FILE'])) {
							$f = new IO\File($dir['DATA']['PATH'], $arSitemap['SITE_ID']);
							$sitemapFile->addFileEntry($f);
						}
					}
				} else {
					if (!isset($arSitemap['SETTINGS']['DIR'][$dirKey])
						|| $arSitemap['SETTINGS']['DIR'][$dirKey] == 'Y') {
						$arDirList[] = $dirKey;
					}
				}
			}
		} else {
			$len = amreg_strlen($arCurrentDir['ITEM_PATH']);
			if (!empty($arSitemap['SETTINGS']['DIR'])) {
				foreach ($arSitemap['SETTINGS']['DIR'] as $dirKey => $checked) {
					if ($checked == 'Y') {
						if (strncmp($arCurrentDir['ITEM_PATH'], $dirKey, $len) === 0) {
							$arDirList[] = $dirKey;
						}
					}
				}
			}

			if (!empty($arSitemap['SETTINGS']['FILE'])) {
				foreach ($arSitemap['SETTINGS']['FILE'] as $dirKey => $checked) {
					if ($checked == 'Y') {
						if (strncmp($arCurrentDir['ITEM_PATH'], $dirKey, $len) === 0) {
							$fileName = IO\Path::combine(
								SiteTable::getDocumentRoot($arSitemap['SITE_ID']),
								$dirKey
							);

							if (!is_dir($fileName)) {
								$f = new IO\File($fileName, $arSitemap['SITE_ID']);
								if ($f->isExists()
									&& !$f->isSystem()
									&& preg_match($arSitemap['SETTINGS']['FILE_MASK_REGEXP'], $f->getName())
								) {
									$sitemapFile->addFileEntry($f);
								}
							}
						}
					}
				}
			}
		}

		if (count($arDirList) > 0) {
			foreach ($arDirList as $dirKey) {
				$arRuntimeData = array(
					'PID' => $PID,
					'ITEM_PATH' => $dirKey,
					'PROCESSED' => SitemapRuntimeTable::UNPROCESSED,
					'ACTIVE' => SitemapRuntimeTable::ACTIVE,
					'ITEM_TYPE' => SitemapRuntimeTable::ITEM_TYPE_DIR,
				);
				SitemapRuntimeTable::add($arRuntimeData);
			}
		}

		SitemapRuntimeTable::update($arCurrentDir['ID'], array(
			'PROCESSED' => SitemapRuntimeTable::PROCESSED,
		));
	}

	public static function getDirStructure($bLogical, $site, $path)
	{

		$arDirContent = array();
		\Bitrix\Main\Loader::includeModule('fileman');

		$arDirs = array();
		$arFiles = array();

		self::GetDirListExt(array($site, $path), $arDirs, $arFiles, array(), array("NAME" => "asc"), "DF", $bLogical, true);

		$arDirContent_t = array_merge($arDirs, $arFiles);
		for ($i = 0, $l = count($arDirContent_t); $i < $l; $i++) {
			$file = $arDirContent_t[$i];
			$arPath = array($site, $file['ABS_PATH']);
			if ($file["TYPE"] == "F" && $file["NAME"] == ".section.php") {
				continue;
			}

			$f = $file['TYPE'] == 'F'
				? new \Bitrix\Main\IO\File($file['PATH'], $site)
				: new \Bitrix\Main\IO\Directory($file['PATH'], $site);

			$p = $f->getName();

			if ($f->isSystem()
				|| $file['TYPE'] == 'F' && in_array($p, array("urlrewrite.php"))
				|| $file['TYPE'] == 'D' && preg_match("/\/(bitrix|" . \COption::getOptionString("main", "upload_dir", "upload") . ")\//", "/" . $p . "/")
			) {
				continue;
			}

			$arFileData = array(
				'NAME' => $bLogical ? $file['LOGIC_NAME'] : $p,
				'FILE' => $p,
				'TYPE' => $file['TYPE'],
				'DATA' => $file,
			);

			if (amreg_strlen($arFileData['NAME']) <= 0)
				$arFileData['NAME'] = GetMessage('SEO_DIR_LOGICAL_NO_NAME');

			$arDirContent[] = $arFileData;
		}
		unset($arDirContent_t);

		return $arDirContent;
	}

	public static function GetDirListExt($path, &$arDirs, &$arFiles, $arFilter = Array(), $sort = Array(), $type = "DF", $bLogical = false, $task_mode = false)
	{
		return self::GetDirList($path, $arDirs, $arFiles, $arFilter, $sort, $type, $bLogical, $task_mode);
	}

	function GetDirList($path, &$arDirs, &$arFiles, $arFilter = array(), $sort = array(), $type = "DF", $bLogical = false, $task_mode = false)
	{
		global $APPLICATION;

		\CMain::InitPathVars($site, $path);
		$DOC_ROOT = \CSite::GetSiteDocRoot($site);

		$arDirs = array();
		$arFiles = array();

		$exts = amreg_strtolower($arFilter["EXTENSIONS"]);
		$arexts = explode(",", $exts);
		if (isset($arFilter["TYPE"]))
			$type = amreg_strtoupper($arFilter["TYPE"]);

		$io = \CBXVirtualIo::GetInstance();
		$path = $io->CombinePath("/", $path);
		$abs_path = $io->CombinePath($DOC_ROOT, $path);

		if (!$io->DirectoryExists($abs_path))
			return false;

		$date_format = \CDatabase::DateFormatToPHP(\CLang::GetDateFormat("FULL"));
		$tzOffset = \CTimeZone::GetOffset();

		$dir = $io->GetDirectory($abs_path);
		$arChildren = $dir->GetChildren();
		$arExtension = array("php" => 1, "html" => 1, "php3" => 1, "php4" => 1, "php5" => 1, "php6" => 1, "phtml" => 1, "htm" => 1);
		foreach ($arChildren as $child) {
			$arFile = array();

			if (($type == "F" || $type == "") && $child->IsDirectory())
				continue;
			if (($type == "D" || $type == "") && !$child->IsDirectory())
				continue;

			$file = $child->GetName();

			if ($bLogical) {
				if ($child->IsDirectory()) {
					$sSectionName = "";
					$fsn = $io->CombinePath($abs_path, $file, ".section.php");
					if (!$io->FileExists($fsn))
						continue;

					include($io->GetPhysicalName($fsn));
					$arFile["LOGIC_NAME"] = $sSectionName;
				} else {
					$ext = \CFileMan::GetFileTypeEx($file);
					if (!isset($arExtension[$ext]))
						continue;

					if ($file == '.section.php')
						continue;

					if (!preg_match('/^\.(.*)?\.menu\.(php|html|php3|php4|php5|php6|phtml)$/', $file, $regs)) {
						$f = $io->GetFile($abs_path . "/" . $file);
						$filesrc = $f->GetContents();

						$title = \PHPParser::getPageTitle($filesrc);
						if ($title === false)
							continue;
						$arFile["LOGIC_NAME"] = $title;
					}
				}
			}

			$arFile["PATH"] = $abs_path . "/" . $file;
			$arFile["ABS_PATH"] = $path . "/" . $file;
			$arFile["NAME"] = $file;

			$arPerm = $APPLICATION->GetFileAccessPermission(array($site, $path . "/" . $file), array(2), $task_mode);
			if ($task_mode) {
				$arFile["PERMISSION"] = $arPerm[0];
				if (!empty($arPerm[1]))
					$arFile["PERMISSION_EX"] = $arPerm[1];
			} else
				$arFile["PERMISSION"] = $arPerm;

			$arFile["TIMESTAMP"] = $child->GetModificationTime() + $tzOffset;
			$arFile["DATE"] = date($date_format, $arFile["TIMESTAMP"]);

			if (isset($arFilter["TIMESTAMP_1"]) && strtotime($arFile["DATE"]) < strtotime($arFilter["TIMESTAMP_1"]))
				continue;
			if (isset($arFilter["TIMESTAMP_2"]) && strtotime($arFile["DATE"]) > strtotime($arFilter["TIMESTAMP_2"]))
				continue;

			if (is_set($arFilter, "MIN_PERMISSION") && $arFile["PERMISSION"] < $arFilter["MIN_PERMISSION"] && !$task_mode)
				continue;

			if (!$child->IsDirectory() && $arFile["PERMISSION"] <= "R" && !$task_mode)
				continue;

			if ($bLogical) {
				if (amreg_strlen($arFilter["NAME"]) > 0 && amreg_strpos($arFile["LOGIC_NAME"], $arFilter["NAME"]) === false)
					continue;
			} else {
				if (amreg_strlen($arFilter["NAME"]) > 0 && amreg_strpos($arFile["NAME"], $arFilter["NAME"]) === false)
					continue;
			}

			//if(amreg_strlen($arFilter["NAME"])>0 && amreg_strpos($arFile["NAME"], $arFilter["NAME"])===false)
			//	continue;

			if (amreg_substr($arFile["ABS_PATH"], 0, amreg_strlen(BX_ROOT . "/modules")) == BX_ROOT . "/modules" && !$task_mode)
				continue;

			if ($arFile["PERMISSION"] == "U" && !$task_mode) {
				$ftype = GetFileType($arFile["NAME"]);
				if ($ftype != "SOURCE" && $ftype != "IMAGE" && $ftype != "UNKNOWN") continue;
				if (amreg_substr($arFile["NAME"], 0, 1) == ".") continue;
			}

			if ($child->IsDirectory()) {
				$arFile["SIZE"] = 0;
				$arFile["TYPE"] = "D";
				$arDirs[] = $arFile;
			} else {
				if ($exts != "")
					if (!in_array(amreg_strtolower(amreg_substr($file, bxstrrpos($file, ".") + 1)), $arexts))
						continue;

				$arFile["TYPE"] = "F";
				$arFile["SIZE"] = $child->GetFileSize();
				$arFiles[] = $arFile;
			}
		}

		if (is_array($sort) && count($sort) > 0) {
			$by = key($sort);
			$order = amreg_strtolower($sort[$by]);
			$by = amreg_strtolower($by);
			if ($order != "desc")
				$order = "asc";
			if ($by != "size" && $by != "timestamp" && $by != "name_nat")
				$by = "name";

			usort($arDirs, array("_FilesCmp", "cmp_" . $by . "_" . $order));
			usort($arFiles, array("_FilesCmp", "cmp_" . $by . "_" . $order));
		}

		return NULL;
	}

	public static function fillPropDomain()
	{
		if (self::$arPropDomainAvailable == false) {
			self::$arPropDomainAvailable = \COption::GetOptionString("kit.multiregions", "sitemapagent_prop_domain_available", "SYS_DOMAIN_AVAILABLE");
			self::$arPropDomainAvailable = str_replace(",", "\n", self::$arPropDomainAvailable);
			self::$arPropDomainAvailable = explode("\n", self::$arPropDomainAvailable);
			foreach (self::$arPropDomainAvailable as $k => $v) {
				self::$arPropDomainAvailable[$k] = trim($v);
				if (amreg_strlen(self::$arPropDomainAvailable[$k]) <= 0) {
					unset(self::$arPropDomainAvailable[$k]);
				}
			}
			self::$arPropDomainAvailable = array_values(self::$arPropDomainAvailable);
		}

		if (self::$arPropDomainShow == false) {
			self::$arPropDomainShow = \COption::GetOptionString("kit.multiregions", "sitemapagent_prop_show_domain", "SYS_SHOW_DOMAIN");
			self::$arPropDomainShow = str_replace(",", "\n", self::$arPropDomainShow);
			self::$arPropDomainShow = explode("\n", self::$arPropDomainShow);
			foreach (self::$arPropDomainShow as $k => $v) {
				self::$arPropDomainShow[$k] = trim($v);
				if (amreg_strlen(self::$arPropDomainShow[$k]) <= 0) {
					unset(self::$arPropDomainShow[$k]);
				}
			}
			self::$arPropDomainShow = array_values(self::$arPropDomainShow);
		}

		if (self::$arPropDomainHide == false) {
			self::$arPropDomainHide = \COption::GetOptionString("kit.multiregions", "sitemapagent_prop_hide_domain", "SYS_HIDE_DOMAIN");
			self::$arPropDomainHide = str_replace(",", "\n", self::$arPropDomainHide);
			self::$arPropDomainHide = explode("\n", self::$arPropDomainHide);
			foreach (self::$arPropDomainHide as $k => $v) {
				self::$arPropDomainHide[$k] = trim($v);
				if (amreg_strlen(self::$arPropDomainHide[$k]) <= 0) {
					unset(self::$arPropDomainHide[$k]);
				}
			}
			self::$arPropDomainHide = array_values(self::$arPropDomainHide);
		}
		if (self::$arAllPropDomain == false) {
			self::$arAllPropDomain = array_merge(self::$arPropDomainAvailable, self::$arPropDomainShow, self::$arPropDomainHide);
		}
	}

	public static function doCheckElementAllowInDomain($IBLOCK_ID, $ELEMENT_ID, $DOMAIN_ID)
	{
		self::fillPropDomain();
		if (empty(self::$arAllPropDomain)) {
			return true;
		}
		$bAllow = true;
		$b = "SORT";
		$o = "ASC";
		$arProps = array();
		foreach (self::$arAllPropDomain as $prop) {
			$rProp = \CIBlockElement::GetProperty($IBLOCK_ID, $ELEMENT_ID, $b, $o, array("CODE" => $prop));
			while ($arProp = $rProp->Fetch()) {
				if (is_array($arProp['VALUE'])) {
					$arProps[$prop] = array_merge($arProps[$prop], $arProp['VALUE']);
				} elseif (amreg_strlen($arProp['VALUE']) > 0) {
					$arProps[$prop][] = $arProp['VALUE'];
				}
			}
		}
		if (!empty(self::$arPropDomainAvailable)) {
			foreach (self::$arPropDomainAvailable as $prop) {
				if (isset($arProps[$prop]) && !in_array($DOMAIN_ID, $arProps[$prop])) {
					$bAllow = false;
				}
			}
		}
		if ($bAllow) {
			if (!empty(self::$arPropDomainShow)) {
				foreach (self::$arPropDomainShow as $prop) {
					if (isset($arProps[$prop]) && !empty($arProps[$prop]) && !in_array($DOMAIN_ID, $arProps[$prop])) {
						$bAllow = false;
					}
				}
			}
		}
		if ($bAllow) {
			if (!empty(self::$arPropDomainHide)) {
				foreach (self::$arPropDomainHide as $prop) {
					if (isset($arProps[$prop]) && !empty($arProps[$prop]) && in_array($DOMAIN_ID, $arProps[$prop])) {
						$bAllow = false;
					}
				}
			}
		}
		return $bAllow;
	}
}
