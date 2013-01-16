<?php
defined('_SECURE_') or die('Forbidden');

function user_getall($fields='', $extras='') {
	$ret = array();
	if (is_array($fields)) {
		foreach ($fields as $key => $val) {
			$q_condition .= "AND ".$key."='".$val."' ";
		}
		if ($q_condition) {
			$q_condition = "WHERE ".substr($q_condition, 3);
		}
	}
	if (is_array($extras)) {
		foreach ($extras as $key => $val) {
			$q_extra .= $key." ".$val." ";
		}
	}
	$db_query = "SELECT * FROM "._DB_PREF_."_tblUser ".$q_condition." ".$q_extra;
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}
	return $ret;
}

function user_count($fields='', $extras='') {
	$ret = 0;
	if (is_array($fields)) {
		foreach ($fields as $key => $val) {
			$q_condition .= "AND ".$key."='".$val."' ";
		}
		if ($q_condition) {
			$q_condition = "WHERE ".substr($q_condition, 3);
		}
	}
	if (is_array($extras)) {
		foreach ($extras as $key => $val) {
			$q_extra .= $key." ".$val." ";
		}
	}
	$db_query = "SELECT count(*) AS count FROM "._DB_PREF_."_tblUser ".$q_condition." ".$q_extra;
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = $db_row['count'];
	}
	return $ret;
}

function user_add($item) {
	$ret = false;
	if (is_array($item)) {
		foreach ($item as $key => $val) {
			$sets .= $key.",";
			$vals .= "'".$val."',";
		}
		if ($sets && $vals) {
			$sets = substr($sets, 0, -1);
			$vals = substr($vals, 0, -1);
			$db_query = "INSERT INTO "._DB_PREF_."_tblUser (".$sets.") VALUES (".$vals.")";
			if ($c_id = dba_insert_id($db_query)) {
				$ret = $c_id;
			}
		}
	}
	return $ret;
}

function user_update($item, $condition='') {
	$ret = false;
	global $core_config;
	if (is_array($item)) {
		foreach ($item as $key => $val) {
			if (($key != "sender") || ($core_config['denycustomsender']!=true) || isadmin($condition['username'])) {
				$sets .= $key."='".$val."',";
			}
		}
		if ($sets) {
			$sets = substr($sets, 0, -1);
			if (is_array($condition)) {
				foreach ($condition as $key => $val){ 
					$q_condition .= " AND ".$key."='".$val."'";
				}
				if ($q_condition) {
					$q_condition = " WHERE 1=1 ".$q_condition;
				}
			}
			$db_query = "UPDATE "._DB_PREF_."_tblUser SET ".$sets." ".$q_condition;
			if ($c_rows = dba_affected_rows($db_query)) {
				$ret = $c_rows;
			}
		}
	}
	return $ret;
}

function user_remove($condition='') {
	$ret = false;
	if (is_array($condition)) {
		foreach ($condition as $key => $val){ 
			$q_condition .= "AND ".$key."='".$val."' ";
		}
		if ($q_condition) {
			$q_condition = "WHERE ".substr($q_condition, 3);
		}
		$db_query = "DELETE FROM "._DB_PREF_."_tblUser ".$q_condition;
		if ($c_rows = dba_affected_rows($db_query)) {
			$ret = $c_rows;
		}
	}
	return $ret;
}

function user_isavail($fields='') {
	$ret = false;
	if (is_array($fields)) {
		foreach ($fields as $key => $val) {
			$q_condition .= "OR ".$key."='".$val."' ";
		}
		if ($q_condition) {
			$q_condition = "WHERE ".substr($q_condition, 2);
		}
	}
	$db_query = "SELECT * FROM "._DB_PREF_."_tblUser ".$q_condition." LIMIT 1";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = 0;
	} else {
		$ret = 1;
	}
	return $ret;
}

function user_getallwithstatus($status) {
	$ret = array();
	$db_query = "SELECT * FROM "._DB_PREF_."_tblUser WHERE status='$status'";
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}
	return $ret;
}

function user_getdatabyuid($uid) {
	global $core_config;
	$ret = array();
	if ($uid) {
		$db_query = "SELECT * FROM "._DB_PREF_."_tblUser WHERE uid='$uid'";
		$db_result = dba_query($db_query);
		if ($db_row = dba_fetch_array($db_result)) {
			$ret = $db_row;
			$ret['opt']['sms_footer_length'] = ( strlen($ret['footer']) > 0 ? strlen($ret['footer']) + 1 : 0 );
			$ret['opt']['per_sms_length'] = $core_config['main']['per_sms_length'] - $ret['opt']['sms_footer_length'];
			$ret['opt']['per_sms_length_unicode'] = $core_config['main']['per_sms_length_unicode'] - $ret['opt']['sms_footer_length'];
			$ret['opt']['max_sms_length'] = $core_config['main']['max_sms_length'] - $ret['opt']['sms_footer_length'];
			$ret['opt']['max_sms_length_unicode'] = $core_config['main']['max_sms_length_unicode'] - $ret['opt']['sms_footer_length'];
		}
	}
	return $ret;
}

function user_getdatabyusername($username) {
	$uid = username2uid($username);
	return user_getdatabyuid($uid);
}

function user_getfieldbyuid($uid, $field) {
	$field = q_sanitize($field);
	if ($uid && $field) {
		$db_query = "SELECT $field FROM "._DB_PREF_."_tblUser WHERE uid='$uid'";
		$db_result = dba_query($db_query);
		if ($db_row = dba_fetch_array($db_result)) {
			$ret = $db_row[$field];
		}
	}
	return $ret;
}

function user_getfieldbyusername($username, $field) {
	$uid = username2uid($username);
	return user_getfieldbyuid($uid, $field);
}

function user_add_validate($item) {
	$ret['status'] = true;
	if (is_array($item)) {
		if ($item['password'] && (strlen($item['password']) < 4)) {
			$ret['error_string'] = _('Password should be at least 4 characters');
			$ret['status'] = false;
		}
		if ($item['username'] && (strlen($item['username']) < 3)) {
			$ret['error_string'] = _('Username should be at least 3 characters')." (".$item['username'].")";
			$ret['status'] = false;
		}
		if ($item['username'] && (! preg_match('/([A-Za-z0-9\.\-])/', $item['username']))) {
			$ret['error_string'] = _('Valid characters for username are alphabets, numbers, dot or dash')." (".$item['username'].")";
			$ret['status'] = false;
		}
		if ($item['email']) {
			if (! preg_match('/^(.+)@(.+)\.(.+)$/', $item['email'])) {
				$ret['error_string'] = _('Your email format is invalid')." (".$item['email'].")";
				$ret['status'] = false;
			}
			$c_user = user_getall(array('email' => $item['email']));
			if ($c_user[0]['username'] && ($c_user[0]['username'] != $item['username'])) {
				$ret['error_string'] = _('Email is already in use by other username') . " (" . _('email') . ": ".$item['email'].", " . _('username') . ": " . $c_user[0]['username'] . ") ";
				$ret['status'] = false;
			}
		}
		if ($item['mobile']) {
			if (! preg_match('/([0-9\+\- ])/', $item['mobile'])) {
				$ret['error_string'] = _('Your mobile format is invalid')." (".$item['mobile'].")";
				$ret['status'] = false;
			}
			$c_uid = mobile2uid($item['mobile']);
			$c_user = user_getall(array('uid' => $c_uid));
			if ($c_user[0]['username'] && ($c_user[0]['username'] != $item['username'])) {
				$ret['error_string'] = _('Mobile is already in use by other username') . " (" . _('mobile') . ": ".$item['mobile'].", " . _('username') . ": " . $c_user[0]['username'] . ") ";
				$ret['status'] = false;
			}
		}
	}
	return $ret;
}

function uid2username($uid) {
	if ($uid) {
		$db_query = "SELECT username FROM "._DB_PREF_."_tblUser WHERE uid='$uid'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$username = $db_row['username'];
	}
	return $username;
}

function username2uid($username) {
	if ($username) {
		$db_query = "SELECT uid FROM "._DB_PREF_."_tblUser WHERE username='$username'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$uid = $db_row['uid'];
	}
	return $uid;
}

function mobile2uid($mobile) {
	if ($mobile) {
		// remove +
		$mobile = str_replace('+','',$mobile);
		// remove first 3 digits if phone number length more than 7
		if (strlen($mobile) > 7) { $mobile = substr($mobile,3); }
		$db_query = "SELECT uid FROM "._DB_PREF_."_tblUser WHERE mobile LIKE '%$mobile'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$uid = $db_row['uid'];
	}
	return $uid;
}

?>
