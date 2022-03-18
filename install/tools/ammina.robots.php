<?
define('NOT_CHECK_PERMISSIONS', true);
define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$strDomain = amreg_strtolower($_SERVER[COption::GetOptionString("ammina.regions","host_var_name","HTTP_HOST")]);
if (amreg_strpos($strDomain, "www.") === 0) {
	$strDomain = amreg_substr($strDomain, 4);
}
$strFile = false;
if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/seofiles/robots/" . $strDomain . ".robots.txt")) {
	$strFile = $_SERVER['DOCUMENT_ROOT'] . "/seofiles/robots/" . $strDomain . ".robots.txt";
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . "/seofiles/robots/www." . $strDomain . ".robots.txt")) {
	$strFile = $_SERVER['DOCUMENT_ROOT'] . "/seofiles/robots/www." . $strDomain . ".robots.txt";
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . "/robots.txt")) {
	$strFile = $_SERVER['DOCUMENT_ROOT'] . "/robots.txt";
}
if ($strFile !== false) {
	echo file_get_contents($strFile);
}

$APPLICATION->FinalActions();
