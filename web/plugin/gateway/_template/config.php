<?php
defined('_SECURE_') or die('Forbidden');

$template_param['ready'] = FALSE;

$db_query = "SELECT * FROM "._DB_PREF_."_gatewayTemplate_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$template_param['name'] = $db_row['cfg_name'];
	$template_param['path'] = $db_row['cfg_path'];
	$template_param['global_sender'] = $db_row['cfg_global_sender'];
	$template_param['ready'] = $db_row['ready'];
}

//$gateway_number = $template_param['global_sender'];

// insert to left menu array
if (isadmin()) {
	$menutab_gateway = $core_config['menutab']['gateway'];
	$arr_menu[$menutab_gateway][] = array("index.php?app=menu&inc=gateway_template&op=manage", _('Manage template'));
}
?>
