<?
if (IsModuleInstalled('ammina.regions')) {
	if (is_dir(dirname(__FILE__) . '/install/js'))
		$updater->CopyFiles("install/js", "js/");

}
