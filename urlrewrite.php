<?
error_reporting(7);
@include_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/ammina.regions/mbfunc.php");
if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/urlrewrite.regions.php")) {
	$arRegionsUrl = @include($_SERVER['DOCUMENT_ROOT'] . "/urlrewrite.regions.php");
	$_SERVER['REQUIRED_AMMINA_REGION_CODE'] = '-';
	$_SERVER['REQUIRED_AMMINA_DOMAIN_ID'] = $arRegionsUrl['-'][0]['ID'];
	foreach ($arRegionsUrl as $regionUrlPath => $arRegionalUrl) {
		foreach ($arRegionalUrl as $k => $v) {
			if ($v["REGIONAL"] == $v["ORIGINAL"]) {
				continue;
			}
			if (amreg_strpos($_SERVER['REQUEST_URI'], $v['REGIONAL']) === 0) {
				$_SERVER['REQUIRED_AMMINA_REGION_CODE'] = $regionUrlPath;
				$_SERVER['REQUIRED_AMMINA_DOMAIN_ID'] = $v['ID'];
				$checkUrl = $_SERVER['REDIRECT_URL'];
				if ($_SERVER['REDIRECT_URL'] == "/bitrix/urlrewrite.php") {
					$checkUrl = $_SERVER['REQUEST_URI'];
				}
				if (amreg_strpos($checkUrl, $v["REGIONAL"]) !== false) {
					$bAllow = true;
					$strNewUrl = $_SERVER['REQUEST_URI'];
					if ($v['LEVEL'] > 0) {
						$arAfter = explode("/", amreg_substr($_SERVER['REQUEST_URI'], amreg_strpos($_SERVER['REQUEST_URI'], $v['REGIONAL']) + amreg_strlen($v['REGIONAL'])));
						if (count($arAfter) > $v['LEVEL']) {
							$bAllow = false;
							$strNewUrl = $v['ORIGINAL'] . implode("/", $arAfter);
						}
					}
					if ($bAllow) {
						$_SERVER['REQUEST_URI'] = str_replace($v["REGIONAL"], $v['ORIGINAL'], $_SERVER['REQUEST_URI']);
						$_SERVER['REDIRECT_URL'] = str_replace($v["REGIONAL"], $v['ORIGINAL'], $_SERVER['REDIRECT_URL']);
						if (file_exists($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REDIRECT_URL'])) {
							if (is_dir($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REDIRECT_URL']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REDIRECT_URL'] . "/index.php")) {
								include($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REDIRECT_URL'] . "/index.php");
							} elseif (is_file($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REDIRECT_URL'])) {
								include($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REDIRECT_URL']);
							}
							die();
						}
					} else {
						$cgiMode = (mb_stristr(php_sapi_name(), "cgi") !== false);
						if ($cgiMode) {
							header("Status: 301 Moved Permanently");
						} else {
							header($_SERVER['SERVER_PROTOCOL'] . " 301 Moved Permanently");
						}
						header("Location: " . $strNewUrl);
						die();
					}
				}
				break(2);
			}
		}
	}
}
