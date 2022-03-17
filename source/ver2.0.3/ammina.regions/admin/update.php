<?

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

define("NO_AGENT_CHECK", true);
define("NO_KEEP_STATISTIC", true);

$initialTime = time();
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/ammina.regions/prolog.php");

Bitrix\Main\Loader::includeModule('ammina.regions');

Loc::loadMessages(__FILE__);

$modulePermissions = $APPLICATION->GetGroupRight("ammina.regions");
if ($modulePermissions < "W") {
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

if (CAmminaRegions::getTestPeriodInfo() == \Bitrix\Main\Loader::MODULE_DEMO_EXPIRED) {
	require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
	CAdminMessage::ShowMessage(array("MESSAGE" => Loc::getMessage("AMMINA_REGIONS_SYS_MODULE_IS_DEMO_EXPIRED"), "HTML" => true));
	require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
	die();
}

if (!isset($INTERVAL)) {
	$INTERVAL = 30;
} else {
	$INTERVAL = intval($INTERVAL);
}
if (!isset($PAUSE)) {
	$PAUSE = 2;
} else {
	$PAUSE = intval($PAUSE);
}

@set_time_limit(0);

$start_time = time();

$arErrors = array();
$arMessages = array();

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_REQUEST["Import"] == "Y") {
	require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_js.php");

	if (array_key_exists("NS", $_POST) && is_array($_POST["NS"])) {
		$NS = $_POST["NS"];
	} else {
		$NS = array(
			"STEP" => isset($_REQUEST['startstep']) ? $_REQUEST['startstep'] : 0,
			"DELETE_CITY" => ($_REQUEST["DELETE_CITY"] == "Y" ? "Y" : "N"),
			"LOCATION_CITY" => ($_REQUEST["LOCATION_CITY"] == "Y" || $_REQUEST["LOAD_LOCATION"] == "Y" ? "Y" : "N"),
			"LOAD_LOCATION" => ($_REQUEST["LOAD_LOCATION"] == "Y" ? "Y" : "N"),
			"LOAD_LOCATION_VILLAGE" => ($_REQUEST["LOAD_LOCATION_VILLAGE"] == "Y" ? "Y" : "N"),
			"INTERVAL" => $_REQUEST['INTERVAL'],
			"PAUSE" => $_REQUEST['PAUSE'],
			"START_TIME" => time(),
		);
		$_SESSION['AMMINA_REGIONS_IMPORT_DATA'] = array();
	}

	$obImport = new \Ammina\Regions\Import($NS, $start_time);
	if (!check_bitrix_sessid()) {
		$arErrors[] = GetMessage("AMMINA_REGIONS_IMPORT_ACCESS_DENIED");
	} else {
		$obImport->doImportProcess($_SESSION['AMMINA_REGIONS_IMPORT_DATA']);
		$NS = $obImport->getNSData();
		$_SESSION['AMMINA_REGIONS_IMPORT_DATA'] = $obImport->getImportData();
		$arErrors = array_merge($arErrors, $obImport->getErrors());
	}

	?>
	<script>
		CloseWaitWindow();
	</script>
	<?

	foreach ($arErrors as $strError) {
		CAdminMessage::ShowMessage($strError);
	}
	foreach ($arMessages as $strMessage) {
		CAdminMessage::ShowMessage(array("MESSAGE" => $strMessage, "TYPE" => "OK"));
	}

	if (count($arErrors) == 0) {
		$totalTime = time() - $NS['START_TIME'];
		$h = intval($totalTime / 3600);
		$totalTime2 = $totalTime - $h * 3600;
		$m = intval($totalTime2 / 60);
		$s = $totalTime2 - $m * 60;
		$sTime = sprintf("%d:%02d:%02d", $h, $m, $s);
		if ($NS['STEP'] < 12) {
			$progressItems = array(
				Loc::getMessage("ammina.regions_STATUS_STEP_0"),
				Loc::getMessage("ammina.regions_STATUS_TIME", array("#TIME#" => $sTime)),
			);
			$progressTotal = 0;
			$progressValue = 0;
			if ($NS['STEP'] == 1) {
				$progressItems = array(
					Loc::getMessage("ammina.regions_STATUS_STEP_1"),
					Loc::getMessage("ammina.regions_STATUS_TIME", array("#TIME#" => $sTime)),
				);
			} elseif ($NS['STEP'] == 2) {
				$progressItems = array(
					Loc::getMessage("ammina.regions_STATUS_STEP_2"),
					Loc::getMessage("ammina.regions_STATUS_TIME", array("#TIME#" => $sTime)),
				);
			} elseif ($NS['STEP'] == 3) {
				$progressItems = array(
					Loc::getMessage("ammina.regions_STATUS_STEP_3", array("#BLOCK#" => (amreg_strlen($_SESSION['AMMINA_REGIONS_IMPORT_DATA']['CURRENT_IP']) > 0 ? $_SESSION['AMMINA_REGIONS_IMPORT_DATA']['CURRENT_IP'] : "0.0.0.0"))),
					Loc::getMessage("ammina.regions_STATUS_TIME", array("#TIME#" => $sTime)),
				);
				$progressValue = $_SESSION['AMMINA_REGIONS_IMPORT_DATA']['STEP3_CNT'];
				$progressTotal = $_SESSION['AMMINA_REGIONS_IMPORT_DATA']['STEP3_TOTAL'];
			} elseif ($NS['STEP'] == 4) {
				$progressItems = array(
					Loc::getMessage("ammina.regions_STATUS_STEP_4"),
					Loc::getMessage("ammina.regions_STATUS_TIME", array("#TIME#" => $sTime)),
				);
				$progressValue = $_SESSION['AMMINA_REGIONS_IMPORT_DATA']['STEP4_CNT'];
				$progressTotal = $_SESSION['AMMINA_REGIONS_IMPORT_DATA']['STEP4_TOTAL'];
			} elseif ($NS['STEP'] == 5) {
				$progressItems = array(
					Loc::getMessage("ammina.regions_STATUS_STEP_5"),
					Loc::getMessage("ammina.regions_STATUS_TIME", array("#TIME#" => $sTime)),
				);
				$progressValue = $_SESSION['AMMINA_REGIONS_IMPORT_DATA']['STEP5_CNT'];
				$progressTotal = $_SESSION['AMMINA_REGIONS_IMPORT_DATA']['STEP5_TOTAL'];
			} elseif ($NS['STEP'] == 6) {
				$progressItems = array(
					Loc::getMessage("ammina.regions_STATUS_STEP_6"),
					Loc::getMessage("ammina.regions_STATUS_TIME", array("#TIME#" => $sTime)),
				);
				$progressValue = $_SESSION['AMMINA_REGIONS_IMPORT_DATA']['STEP6_CNT'];
				$progressTotal = $_SESSION['AMMINA_REGIONS_IMPORT_DATA']['STEP6_TOTAL'];
			} elseif ($NS['STEP'] == 7) {
				$progressItems = array(
					Loc::getMessage("ammina.regions_STATUS_STEP_7"),
					Loc::getMessage("ammina.regions_STATUS_TIME", array("#TIME#" => $sTime)),
				);
			} elseif ($NS['STEP'] == 8) {
				$progressItems = array(
					Loc::getMessage("ammina.regions_STATUS_STEP_8"),
					Loc::getMessage("ammina.regions_STATUS_TIME", array("#TIME#" => $sTime)),
				);
				$progressValue = $_SESSION['AMMINA_REGIONS_IMPORT_DATA']['STEP8_CNT'];
				$progressTotal = $_SESSION['AMMINA_REGIONS_IMPORT_DATA']['STEP8_TOTAL'];
			} elseif ($NS['STEP'] == 9) {
				$progressItems = array(
					Loc::getMessage("ammina.regions_STATUS_STEP_9"),
					Loc::getMessage("ammina.regions_STATUS_TIME", array("#TIME#" => $sTime)),
				);
				$progressValue = $_SESSION['AMMINA_REGIONS_IMPORT_DATA']['STEP9_CNT'];
				$progressTotal = $_SESSION['AMMINA_REGIONS_IMPORT_DATA']['STEP9_TOTAL'];
			} elseif ($NS['STEP'] == 10) {
				$progressItems = array(
					Loc::getMessage("ammina.regions_STATUS_STEP_10"),
					Loc::getMessage("ammina.regions_STATUS_TIME", array("#TIME#" => $sTime)),
				);
				$progressValue = $_SESSION['AMMINA_REGIONS_IMPORT_DATA']['STEP10_CNT'];
				$progressTotal = $_SESSION['AMMINA_REGIONS_IMPORT_DATA']['STEP10_TOTAL'];
			} elseif ($NS['STEP'] == 11) {
				$progressItems = array(
					Loc::getMessage("ammina.regions_STATUS_STEP_11"),
					Loc::getMessage("ammina.regions_STATUS_TIME", array("#TIME#" => $sTime)),
				);
				$progressValue = $_SESSION['AMMINA_REGIONS_IMPORT_DATA']['STEP11_CNT'];
				$progressTotal = $_SESSION['AMMINA_REGIONS_IMPORT_DATA']['STEP11_TOTAL'];
			}
			CAdminMessage::ShowMessage(
				array(
					"DETAILS" => "<p>" . implode("</p><p>", $progressItems) . "</p>",
					"HTML" => true,
					"TYPE" => "PROGRESS",
					"PROGRESS_TOTAL" => $progressTotal,
					"PROGRESS_VALUE" => $progressValue,
				)
			);

			if ($NS["STEP"] >= 0) {
				?>
				<script type="text/javascript">
					window.setTimeout(function () {
						DoNext(<?=CUtil::PhpToJSObject(array("NS" => $NS)) ?>);
					}, <?=(intval($_REQUEST['PAUSE']) * 1000)?>);
				</script>
				<?
			}
		} else {
			CAdminMessage::ShowMessage(
				array(
					"MESSAGE" => Loc::getMessage("AMMINA_REGIONS_IMPORT_COMPLETED"),
					"DETAILS" => "<p>" . Loc::getMessage("AMMINA_REGIONS_IMPORT_COMPLETED") . "</p><p>" . Loc::getMessage("ammina.regions_STATUS_TIME", array("#TIME#" => $sTime)) . "</p>",
					"HTML" => true,
					"TYPE" => "PROGRESS",
				)
			);
			?>
			<script type="text/javascript">
				EndImport();
			</script>
			<?
		}
	} else {
		?>
		<script type="text/javascript">
			EndImport();
		</script>
		<?
	}

	require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin_js.php");
}

$APPLICATION->SetTitle(Loc::getMessage('AMMINA_REGIONS_PAGE_TITLE'));

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

if (CAmminaRegions::getTestPeriodInfo() == \Bitrix\Main\Loader::MODULE_DEMO) {
	CAdminMessage::ShowMessage(array("MESSAGE" => Loc::getMessage("AMMINA_REGIONS_SYS_MODULE_IS_DEMO"), "HTML" => true));
} elseif (CAmminaRegions::getTestPeriodInfo() == \Bitrix\Main\Loader::MODULE_DEMO_EXPIRED) {
	CAdminMessage::ShowMessage(array("MESSAGE" => Loc::getMessage("AMMINA_REGIONS_SYS_MODULE_IS_DEMO_EXPIRED"), "HTML" => true));
}

?>
	<div id="tbl_ammina_regions_import_result_div"></div>
<?
$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => GetMessage("AMMINA_REGIONS_IMPORT_TAB"),
		"ICON" => "main_user_edit",
		"TITLE" => GetMessage("AMMINA_REGIONS_IMPORT_TAB_TITLE"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);
?>
	<script type="text/javascript">
		var running = false;
		var oldNS = '';

		function DoNext(NS) {
			var interval = parseInt(document.getElementById('INTERVAL').value);
			var pause = parseInt(document.getElementById('PAUSE').value);
			var queryString = 'Import=Y'
				+ '&lang=<?echo LANG?>'
				+ '&<?echo bitrix_sessid_get()?>'
				+ '&INTERVAL=' + interval + '&PAUSE=' + pause<?=(isset($_REQUEST['startstep']) ? "+'&startstep=" . htmlspecialcharsbx($_REQUEST['startstep']) . "'" : '')?>;
			;

			if (!NS) {
				//Make URL
				if (document.getElementById('DELETE_CITY') && document.getElementById('DELETE_CITY').checked)
					queryString += '&DELETE_CITY=' + document.getElementById('DELETE_CITY').value;
				if (document.getElementById('LOCATION_CITY') && document.getElementById('LOCATION_CITY').checked)
					queryString += '&LOCATION_CITY=' + document.getElementById('LOCATION_CITY').value;
				if (document.getElementById('LOAD_LOCATION') && document.getElementById('LOAD_LOCATION').checked)
					queryString += '&LOAD_LOCATION=' + document.getElementById('LOAD_LOCATION').value;
				if (document.getElementById('LOAD_LOCATION_VILLAGE') && document.getElementById('LOAD_LOCATION_VILLAGE').checked)
					queryString += '&LOAD_LOCATION_VILLAGE=' + document.getElementById('LOAD_LOCATION_VILLAGE').value;
			}

			if (running) {
				ShowWaitWindow();
				BX.ajax({
					url: 'ammina.regions.update.php?' + queryString,
					method: 'POST',
					data: NS,
					dataType: 'html',
					cache: false,
					onsuccess: function(result){
						document.getElementById('tbl_ammina_regions_import_result_div').innerHTML = result;
					},
					onfailure: function(){
						window.setTimeout(function () {
							DoNext(NS);
						}, 5000);
					}
				});
				/*
				BX.ajax.post(
					'ammina.regions.update.php?' + queryString,
					NS,
					function (result) {
						document.getElementById('tbl_ammina_regions_import_result_div').innerHTML = result;
					}
				);*/
			}
		}

		function StartImport() {
			running = document.getElementById('start_button').disabled = true;
			DoNext();
		}

		function EndImport() {
			running = document.getElementById('start_button').disabled = false;
		}
	</script>
	<form method="POST" action="<? echo $APPLICATION->GetCurPage() ?>?lang=<? echo htmlspecialcharsbx(LANG) ?>" name="form1" id="form1">
		<?
		$tabControl->Begin();
		$tabControl->BeginNextTab();
		?>
		<tr>
			<td width="40%"><? echo GetMessage("AMMINA_REGIONS_IMPORT_INTERVAL") ?>:</td>
			<td width="60%">
				<input type="text" id="INTERVAL" name="INTERVAL" size="5" value="<?= intval($INTERVAL) ?>"/>
			</td>
		</tr>
		<tr>
			<td width="40%"><? echo GetMessage("AMMINA_REGIONS_IMPORT_PAUSE") ?>:</td>
			<td width="60%">
				<input type="text" id="PAUSE" name="PAUSE" size="5" value="<?= intval($PAUSE) ?>"/>
			</td>
		</tr>
		<tr>
			<td><? echo GetMessage("AMMINA_REGIONS_IMPORT_DELETE_CITY") ?>:</td>
			<td>
				<input type="checkbox" id="DELETE_CITY" name="DELETE_CITY" value="Y"/>
			</td>
		</tr>
		<tr>
			<td><? echo GetMessage("AMMINA_REGIONS_IMPORT_LINK_LOCATION_CITY") ?>:</td>
			<td>
				<input type="checkbox" id="LOCATION_CITY" name="LOCATION_CITY" value="Y"/>
			</td>
		</tr>
		<?
		if (CModule::IncludeModule("sale")) {
			?>
			<tr>
				<td><? echo GetMessage("AMMINA_REGIONS_IMPORT_LOAD_LOCATION") ?>:</td>
				<td>
					<input type="checkbox" id="LOAD_LOCATION" name="LOAD_LOCATION" value="Y" checked="checked"/>
				</td>
			</tr>
			<tr>
				<td><? echo GetMessage("AMMINA_REGIONS_IMPORT_LOAD_LOCATION_VILLAGE") ?>:</td>
				<td>
					<input type="checkbox" id="LOAD_LOCATION_VILLAGE" name="LOAD_LOCATION_VILLAGE" value="Y" />
				</td>
			</tr>
		<? } ?>
		<? $tabControl->Buttons(); ?>
		<input type="button" id="start_button" value="<? echo GetMessage("AMMINA_REGIONS_IMPORT_START_IMPORT") ?>" onclick="StartImport();" class="adm-btn-save">
		<input type="button" id="stop_button" value="<? echo GetMessage("AMMINA_REGIONS_IMPORT_STOP_IMPORT") ?>" onclick="EndImport();">
		<? $tabControl->End(); ?>
	</form>

<?
if (CAmminaRegions::getTestPeriodInfo() == \Bitrix\Main\Loader::MODULE_DEMO) {
	CAdminMessage::ShowMessage(array("MESSAGE" => Loc::getMessage("AMMINA_REGIONS_SYS_MODULE_IS_DEMO"), "HTML" => true));
} elseif (CAmminaRegions::getTestPeriodInfo() == \Bitrix\Main\Loader::MODULE_DEMO_EXPIRED) {
	CAdminMessage::ShowMessage(array("MESSAGE" => Loc::getMessage("AMMINA_REGIONS_SYS_MODULE_IS_DEMO_EXPIRED"), "HTML" => true));
}
CAmminaRegions::showSupportForm();
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>