<?php

function pluginmanager_get_status($plugin_category, $name) {
	global $core_config;
	if ($plugin_category == "gateway") {
		if ($core_config['module']['gateway'] == $name) {
			$ret = TRUE;
		} else {
			$ret = FALSE;
		}
	} else	if ($plugin_category == "themes") {
		if ($core_config['module']['themes'] == $name) {
			$ret = TRUE;
		} else {
			$ret = FALSE;
		}
	} else	if ($plugin_category == "language") {
		if ($core_config['module']['language'] == $name) {
			$ret = TRUE;
		} else {
			$ret = FALSE;
		}
	} else {
		$ret = TRUE;
	}
	return $ret;
}

function pluginmanager_list($plugin_category) {
	global $core_config;
	$upload_path = $core_config['apps_path']['plug'] . "/" . $plugin_category . "/";
	$dir = opendir($upload_path);
	$z = 0;
	while ($fn = readdir($dir)) {
		$template = preg_match('/^_/', $fn, $match);
		if (is_dir($upload_path . $fn) && $f != "." && $f != ".." && $template != true && $fn != 'common') {
			$subdir_tab[$z]['name'] .= $fn;
			$subdir_tab[$z]['version'] .= trim(file_get_contents($apps_path['plug'] . "/" . $plugin_category . "/" . $f . "/docs/VERSION"));
			$subdir_tab[$z]['date'] .= date($core_config['datetime']['format'], filemtime($upload_path . $f));
			if (pluginmanager_get_status($plugin_category, $fn)) {
				$subdir_tab[$z][status] .= '<span class=status_enabled />';
			} else {
				$subdir_tab[$z][status] .= '<span class=status_disabled />';
			}
			$z++;
		}
	}
	return $subdir_tab;
}

function pluginmanager_display($plugin_category) {
	global $core_config;
	$table = "
		<table id='m' width=100% class=sortable>
			<thead><tr>
				<th width=10%>" . _('Name') . "</th>
				<th width=30%>" . _('Description') . "</th>
				<th width=10%>" . _('Version') . "</th>
				<th width=20%>" . _('Author') . "</th>
				<th width=20%>" . _('Date') . "</th>
				<th width=10%>" . _('Status') . "</th>
			</tr></thead>
			<tbody>";
	$subdir_tab = pluginmanager_list($plugin_category);
	for ($l = 0; $l < sizeof($subdir_tab); $l++) {
		unset($plugin_info);
		$xml_file = $core_config['apps_path']['plug'] . "/". $plugin_category . "/" . $subdir_tab[$l]['name'] . "/docs/info.xml";
		if ($fc = file_get_contents($xml_file)) {
			$plugin_info = core_xml_to_array($fc);
			$plugin_info['status'] = $subdir_tab[$l]['status'];
		} else {
			logger_print("XML info file not present:" . $error, 2, "plugin");
		}

		if ($plugin_info['name']) {
			$tr_class = ($l % 2) ? "row_odd" : "row_even";
			$table .= "
				<tr class=$tr_class>
					<td align=center>" . $plugin_info['name'] . "</td>
					<td>" . $plugin_info['description'] . "</td>
					<td align=center>" . $plugin_info['release'] . "</td>
					<td align=center>" . $plugin_info['author'] . "</td>
					<td align=center>" . $plugin_info['date'] . "</td>
					<td align=center>" . $plugin_info['status'] . "</td>
				</tr>";
		}
	}
	$table .= "</tbody></table>";
	return $table;
}

?>