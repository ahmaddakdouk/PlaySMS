<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};
if(!valid()){forcenoaccess();};

$dst_p_num = urlencode($_REQUEST['dst_p_num']);
$dst_gp_code = urlencode($_REQUEST['dst_gp_code']);

switch ($op)
{
	case "sendsmstopv":
		$message = $_REQUEST['message'];
		$rows = phonebook_getdatabyuid($uid, "p_desc");
		foreach ($rows as $key => $db_row) {
			$list_of_number .= "<option value=\"".$db_row['p_num']."\" $selected>".$db_row['p_desc']." ".$db_row['p_num']."</option>";
		}
		// add numbers from public phonebook
		$rows = phonebook_getsharedgroup($uid);
		foreach ($rows as $key => $db_row)
		{
			$c_gpid = $db_row['gpid'];
			$c_uid = $db_row['uid'];
			$c_username = uid2username($c_uid);
			$i = 0;
			$rows = phonebook_getdatabyid($c_gpid);
			foreach ($rows as $key => $db_row1) {
				$list_of_number .= "<option value=\"".$db_row1['p_num']."\" $selected>".$db_row1['p_desc']." ".$db_row1['p_num']." ("._('shared by')." ".$c_username.")</option>";
			}
		}
		$sms_from = sendsms_get_sender($username);
		$max_length = $core_config['smsmaxlength'];
		if ($sms_footer = username2footer($username))
		{
                        // fixme anton - minus 1 for a space as sms_footer prefix
			$max_length = $max_length - strlen($sms_footer) - 1;
		}
		else
		{
			$sms_footer = "<i>"._('not set')."</i>";
		}
		for ($i=0;$i<=23;$i++)
		{
			$c_i = sprintf("%02d",$i);
			$option_hour .= "<option value=\"$c_i\">$c_i</option>";
		}
		for ($i=0;$i<=59;$i++)
		{
			$c_i = sprintf("%02d",$i);
			$option_minute .= "<option value=\"$c_i\">$c_i</option>";
		}
		// WWW
		$db_query2 = "SELECT * FROM "._DB_PREF_."_tblSMSTemplate WHERE uid='$uid'";
		$db_result2 = dba_query($db_query2);
		$j = 0;
		$option_values = "<option value=\"\" default>--"._('Please select')."--</option>";
		while ($db_row = dba_fetch_array($db_result2))
		{
			$j++;
			$option_values .= "<option value=\"".$db_row['t_text']."\">".$db_row['t_title']."</option>";
			$input_values .= "<input type=\"hidden\" name=\"content_$j\" value=\"".$db_row['t_text']."\">";
		}

		// document.fm_sendsms.message.value = document.fm_smstemplate.content_num.value;
		// New function introduce for long sms count and another field (SMS character) added to send sms broadcast
		if ($errid) {
			$err = logger_get_error_string($errid);
		}
		if ($err) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
	    <form name=\"fm_smstemplate\">
	    $input_values
	    </form>

	    <h2>"._('Send SMS')."</h2>
	    <p>
	    <form name=\"fm_sendsms\" id=\"fm_sendsms\" action=\"index.php?app=menu&inc=send_sms&op=sendsmstopv_yes\" method=\"POST\">
	    <p>"._('SMS Sender ID').": $sms_from
	    <p>"._('SMS footer').": $sms_footer
	    <p>
	    <table cellpadding=1 cellspacing=0 border=0>
	    <tr>
		<td nowrap>
		    "._('Phone number(s)').":<br>
		    <select name=\"p_num_dump[]\" size=\"10\" multiple=\"multiple\" onDblClick=\"moveSelectedOptions(this.form['p_num_dump[]'],this.form['p_num[]'])\">$list_of_number</select>
		</td>
		<td width=10>&nbsp;</td>
		<td align=center valign=middle>
		<input type=\"button\" class=\"button\" value=\"&gt;&gt;\" onclick=\"moveSelectedOptions(this.form['p_num_dump[]'],this.form['p_num[]'])\"><br><br>
		<input type=\"button\" class=\"button\" value=\""._('All')." &gt;&gt;\" onclick=\"moveAllOptions(this.form['p_num_dump[]'],this.form['p_num[]'])\"><br><br>
		<input type=\"button\" class=\"button\" value=\"&lt;&lt;\" onclick=\"moveSelectedOptions(this.form['p_num[]'],this.form['p_num_dump[]'])\"><br><br>
		<input type=\"button\" class=\"button\" value=\""._('All')." &lt;&lt;\" onclick=\"moveAllOptions(this.form['p_num[]'],this.form['p_num_dump[]'])\">
		</td>		
		<td width=10>&nbsp;</td>
		<td nowrap>
		    "._('Send to').":<br>
		    <select name=\"p_num[]\" size=\"10\" multiple=\"multiple\" onDblClick=\"moveSelectedOptions(this.form['p_num[]'],this.form['p_num_dump[]'])\"></select>
		</td>
	    </tr>
	    </table>
	    <p>"._('Or').": <input type=text size=20 maxlength=20 name=p_num_text value=\"$dst_p_num\"> ("._('International format').")
	    <p>"._('Message template').": <select name=\"smstemplate\">$option_values</select>
	    <p><input type=\"button\" onClick=\"SetSmsTemplate();SmsSetCounter();\" name=\"nb\" value=\""._('Use template')."\" class=\"button\">
	    <p>"._('Your message').":
	    <br><textarea cols=\"39\" rows=\"5\" onClick=\"SmsSetCounter();\" onkeypress=\"SmsSetCounter();\" onblur=\"SmsSetCounter();\" onKeyUp=\"SmsSetCounter();\" onKeyUp=\"SmsCountKeyUp($max_length);\" onKeyDown=\"SmsCountKeyDown($max_length);\" name=\"message\" id=\"ta_sms_content\">$message</textarea>
	    <br>"._('SMS character').": <input type=\"text\"  style=\"font-weight:bold;\" name=\"txtcount\" value=\"0 char : 0 SMS\" size=\"17\" onFocus=\"document.frmSendSms.message.focus();\" readonly>
            <input type=\"hidden\" value=\"".$core_config['smsmaxlength']."\" name=\"hiddcount\"> 
            <input type=\"hidden\" value=\"".$core_config['smsmaxlength_unicode']."\" name=\"hiddcount_unicode\"> 
		<p><input type=checkbox name=msg_flash> "._('Send as flash message')."
	    <p><input type=checkbox name=msg_unicode onClick=\"SmsSetCounter();\" onkeypress=\"SmsSetCounter();\" onblur=\"SmsSetCounter();\"> "._('Send as unicode message (http://www.unicode.org)')."
	    <p><input type=submit class=button value='"._('Send')."' onClick=\"selectAllOptions(this.form['p_num[]'])\"> 
	    </form>
	";
	    // fixme anton - if no magic_quote_gpc then the pl_addslashes in init.php will add \ in web ($message)
	    // echo $content;
	    echo stripslashes($content);
	    break;
	case "sendsmstopv_yes":
		$p_num = $_POST['p_num'];
		if (!$p_num[0]) {
			$p_num = $_POST['p_num_text'];
		}
		$sms_to = $p_num;
		$msg_flash = $_POST['msg_flash'];
		$msg_unicode = $_POST['msg_unicode'];
		$message = $_POST['message'];
		if (($p_num || $sms_to) && $message) {
			$sms_type = "text";
			if ($msg_flash == "on") {
				$sms_type = "flash";
			}
			$unicode = "0";
			if ($msg_unicode == "on") {
				$unicode = "1";
			}
			list($ok,$to,$smslog_id) = sendsms_pv($username,$sms_to,$message,$sms_type,$unicode);

			if (count($ok) <= 5) {
				for ($i=0;$i<count($ok);$i++) {
					if ($ok[$i]) {
						$error_string .= _('Your SMS has been delivered to queue')." ("._('to').": ".$to[$i].")<br>";
					} else {
						$error_string .= _('Fail to sent SMS')." ("._('to').": `".$to[$i]."`)<br>";
					}
				}
			} else {
				// minimize delivery reports on web, actual status can be seen from outgoing SMS menu (emmanuel)
				$sms_sent = 0;
				$sms_failed = 0;
				for ($i=0;$i<count($ok);$i++) {
					if ($ok[$i]) {
						// $error_string .= _('Your SMS has been delivered to queue')." ("._('to').": `".$to[$i]."`)<br>";
						$sms_sent++;
					} else {
						// $error_string .= _('Fail to sent SMS')." ("._('to').": `".$to[$i]."`)<br>";
						$sms_failed++;
					}
				}
				// fixme anton - we dont need to add new lang entry, just use available phrase
				$error_string = _('Your SMS has been delivered to queue')." ("._('sent').": ".$sms_sent.", "._('failed').": ".$sms_failed.")";
			}

			$errid = logger_set_error_string($error_string);
			header("Location: index.php?app=menu&inc=send_sms&op=sendsmstopv&message=".urlencode($message)."&errid=".$errid);
		} else {
			header("Location: index.php?app=menu&inc=send_sms&op=sendsmstopv&message=".urlencode($message)."&err=".urlencode(_('You must select receiver and your message should not be empty')));
		}
		break;
	case "sendsmstogr":
		$message = $_REQUEST['message'];
		$rows = phonebook_getgroupbyuid($uid, "gp_name");
		foreach ($rows as $key => $db_row)
		{
			$c_count = phonebook_getmembercountbyid($db_row['gpid']);
			$list_of_group .= "<option value=\"".$db_row['gpid']."\" $selected>".$db_row['gp_name']." (".$db_row['gp_code'].")(".$c_count.")</option>";
		}

		// add shared group
		$rows = phonebook_getsharedgroup($uid);
		foreach ($rows as $key => $db_row)
		{
			$c_uid = $db_row['uid'];
			$c_username = uid2username($c_uid);
			$c_count = phonebook_getmembercountbyid($db_row['gpid']);
			$list_of_group .= "<option value=\"".$db_row['gpid']."\" $selected>".$db_row['gp_name']." (".$db_row['gp_code'].")(".$c_count.") - "._('shared by')." ".$c_username."</option>";
		}
		$sms_from = sendsms_get_sender($username);
		$max_length = $core_config['smsmaxlength'];
		if ($sms_footer = username2footer($username))
		{
                        // fixme anton - minus 1 for a space as sms_footer prefix
			$max_length = $max_length - strlen($sms_footer) - 1;
		}
		else
		{
			$sms_footer = "<i>"._('not set')."</i>";
		}

		// WWW
		$db_query2 = "SELECT * FROM "._DB_PREF_."_tblSMSTemplate WHERE uid='$uid'";
		$db_result2 = dba_query($db_query2);
		$j = 0;
		$option_values = "<option value=\"\" default>--"._('Please select')."--</option>";
		while ($db_row = dba_fetch_array($db_result2))
		{
			$j++;
			$option_values .= "<option value=\"".$db_row['t_text']."\">".$db_row['t_title']."</option>";
			$input_values .= "<input type=\"hidden\" name=\"content_$j\" value=\"".$db_row['t_text']."\">";
		}

		// document.fm_sendsms.message.value = document.fm_smstemplate.content_num.value;
		// New function introduce for long sms count and another field (SMS character) added to send sms broadcast
		if ($errid) {
			$err = logger_get_error_string($errid);
		}
		if ($err) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
	    <form name=\"fm_smstemplate\">
	    $input_values
	    </form>

	    <h2>"._('Send broadcast SMS')."</h2>
	    <p>
	    <form name=\"fm_sendsms\" id=\"fm_sendsms\" action=\"index.php?app=menu&inc=send_sms&op=sendsmstogr_yes\" method=\"POST\">
	    <p>"._('SMS Sender ID').": $sms_from
	    <p>"._('SMS footer').": $sms_footer 
	    <p>
	    <table cellpadding=1 cellspacing=0 border=0>
	    <tr>
		<td nowrap>
		    "._('Group List').":<br>
		    <select name=\"gpid_dump[]\" size=\"10\" multiple=\"multiple\" onDblClick=\"moveSelectedOptions(this.form['gpid_dump[]'],this.form['gpid[]'])\">$list_of_group</select>
		</td>
		<td width=10>&nbsp;</td>
		<td align=center valign=middle>
		<input type=\"button\" class=\"button\" value=\"&gt;&gt;\" onclick=\"moveSelectedOptions(this.form['gpid_dump[]'],this.form['gpid[]'])\"><br><br>
		<input type=\"button\" class=\"button\" value=\""._('All')." &gt;&gt;\" onclick=\"moveAllOptions(this.form['gpid_dump[]'],this.form['gpid[]'])\"><br><br>
		<input type=\"button\" class=\"button\" value=\"&lt;&lt;\" onclick=\"moveSelectedOptions(this.form['gpid[]'],this.form['gpid_dump[]'])\"><br><br>
		<input type=\"button\" class=\"button\" value=\""._('All')." &lt;&lt;\" onclick=\"moveAllOptions(this.form['gpid[]'],this.form['gpid_dump[]'])\">
		</td>		
		<td width=10>&nbsp;</td>
		<td nowrap>
		    "._('Send to').":<br>
		    <select name=\"gpid[]\" size=\"10\" multiple=\"multiple\" onDblClick=\"moveSelectedOptions(this.form['gpid[]'],this.form['gpid_dump[]'])\"></select>
		</td>
	    </tr>
	    </table>
	    <p>"._('Message template').": <select name=\"smstemplate\">$option_values</select>
	    <p><input type=\"button\" onClick=\"SetSmsTemplate();SmsSetCounter();\" name=\"nb\" value=\""._('Use template')."\" class=\"button\">
	    <p>"._('Your message').":
	    <br><textarea cols=\"39\" rows=\"5\" onClick=\"SmsSetCounter();\" onkeypress=\"SmsSetCounter();\" onblur=\"SmsSetCounter();\" onKeyUp=\"SmsSetCounter();\" onKeyUp=\"SmsCountKeyUp($max_length);\" onKeyDown=\"SmsCountKeyDown($max_length);\" name=\"message\" id=\"ta_sms_content\">$message</textarea>
	    <br>"._('SMS character').": <input type=\"text\"  style=\"font-weight:bold;\" name=\"txtcount\" value=\"0 char : 0 SMS\" size=\"17\" onFocus=\"document.frmSendSms.message.focus();\" readonly>
            <input type=\"hidden\" value=\"".$core_config['smsmaxlength']."\" name=\"hiddcount\"> 
            <input type=\"hidden\" value=\"".$core_config['smsmaxlength_unicode']."\" name=\"hiddcount_unicode\"> 
	    <p><input type=checkbox name=msg_flash> "._('Send as flash message')."
	    <p><input type=checkbox name=msg_unicode onClick=\"SmsSetCounter();\" onkeypress=\"SmsSetCounter();\" onblur=\"SmsSetCounter();\"> "._('Send as unicode message (http://www.unicode.org)')."
	    <p><input type=submit class=button value='"._('Send')."' onClick=\"selectAllOptions(this.form['gpid[]'])\"> 
	    </form>
	";
	    echo $content;
	    break;
	case "sendsmstogr_yes":
		$gpid = $_POST['gpid'];
		/*
		 if (!$gpid[0]) {
		 $gpid = $_POST['gpid_text'];
		 }
		 */
		$msg_flash = $_POST['msg_flash'];
		$msg_unicode = $_POST['msg_unicode'];
		$message = $_POST['message'];
		if ($gpid && $message) {
			$sms_type = "text";
			if ($msg_flash == "on") {
				$sms_type = "flash";
			}
			$unicode = "0";
			if ($msg_unicode == "on") {
				$unicode = "1";
			}
			list($ok,$to,$smslog_id) = sendsms_bc($username,$gpid,$message,$sms_type,$unicode);

			// minimize delivery reports on web, actual status can be seen from outgoing SMS menu (emmanuel)
			$sms_sent = 0;
			$sms_failed = 0;
			for ($i=0;$i<count($ok);$i++) {
				if ($ok[$i]) {
					// $error_string .= _('Your SMS has been delivered to queue')." ("._('to').": `".$to[$i]."`)<br>";
					$sms_sent++;
				} else {
					// $error_string .= _('Fail to sent SMS')." ("._('to').": `".$to[$i]."`)<br>";
					$sms_failed++;
				}
			}
			// fixme anton - we dont need to add new lang entry, just use available phrase
			$error_string = _('Your SMS has been delivered to queue')." ("._('sent').": ".$sms_sent.", "._('failed').": ".$sms_failed.")";

			$errid = logger_set_error_string($error_string);
			header("Location: index.php?app=menu&inc=send_sms&op=sendsmstogr&message=".urlencode($message)."&errid=".$errid);
		} else {
			header("Location: index.php?app=menu&inc=send_sms&op=sendsmstogr&message=".urlencode($message)."&err=".urlencode(_('You must select receiver group and your message should not be empty')));
		}
		break;
}

?>
