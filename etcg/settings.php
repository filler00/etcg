<?php include 'header.php'; 

$database = new Database;

$settings = array(username,email,url,emailmessage,hiatustrading,inactivetrading,etcgurl,dateformat,dateheaderformat);

if ( isset($_POST['update']) ) {
	
	$sanitize = new Sanitize;
	
	$username = $sanitize->for_db($_POST['username']);
	$username = $sanitize->for_db($_POST['username']);
	$password = $_POST['password'];
	$password2 = $_POST['password2'];
	$email = $sanitize->for_db($_POST['email']);
	$url = $sanitize->for_db($_POST['url']);
	$etcgurl = $sanitize->for_db($_POST['etcgurl']);
	$dateformat = $sanitize->for_db($_POST['dateformat']);
	$dateheaderformat = $sanitize->for_db($_POST['dateheaderformat']);
	$emailmessage = $sanitize->for_db($_POST['emailmessage']);
	$hiatustrading = intval($_POST['hiatustrading']);
	$inactivetrading = intval($_POST['inactivetrading']);
	
	if ( substr($etcgurl, -1) != '/' ) { $etcgurl = "$etcgurl/"; }

	if ( $username === '' ) { $error[] = "Your username can't be left blank."; }
	if ( $password !== '' && $password !== $password2 ) { $error[] = "The passwords did not match."; }
	if ( $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) ) { $error[] = "Invalid email address."; }
	if ( $url === '' || !filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) ) { $error[] = "Invalid TCG post URL."; }
	if ( $etcgurl === '' || !filter_var($etcgurl, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) ) { $error[] = "Invalid eTCG URL."; }
	if ( $dateformat === '' ) { $error[] = "The date format can't be left blank."; }
	if ( $dateheaderformat === '' || strpos($dateheaderformat,'[DATE]') === false ) { $error[] = "Invalid date header format."; }
	if ( $hiatustrading != 1 && $hiatustrading != 0 ) { $error[] = "Invalid value for hiatus trading."; }
	if ( $inactivetrading != 1 && $inactivetrading != 0 ) { $error[] = "Invalid value for inactive trading."; }
	if ( !isset($error) ) {
	
		if ( $password !== '' ) { $password = sha1("$password".Config::DB_SALT.""); 
		
			$result = $database->query("UPDATE `settings` SET `value`='$password' WHERE `setting`='password' LIMIT 1");
			if ( !$result ) { $error[] = "Could not update password. ".mysqli_error().""; }
			else { $success[] = "Your password has been changed."; $_SESSION['password'] = $password; }
		
		}
		
		foreach ( $settings as $setting ) {
		
			$result = $database->query("UPDATE `settings` SET `value`='".$$setting."' WHERE `setting`='$setting' LIMIT 1");
			if ( !$result ) { $error[] = "Could not update $setting. ".mysqli_error().""; }
		
		}
		
		if ( $username !== $_SESSION['username'] ) { $_SESSION['username'] = $username; $success[] = "Your username has been changed."; }
		
		if ( !isset($error) ) { $success[] = "Settings have been updated."; }
	
	}

}

foreach ($settings as $setting) {

	$settingsinfo = $database->get_assoc("SELECT `value` FROM `settings` WHERE `setting`='$setting'");
	$$setting = $settingsinfo['value'];

}

?>

<h1>Edit Settings</h1>
<p>These are the settings for your easyTCG installation. To manage the settings for your TCGs, select a TCG from the dropdown menu on the left.</p>

<?php if ( isset($error) ) { foreach ( $error as $msg ) {  ?><div class="errors"><strong>ERROR!</strong> <?php echo $msg; ?></div><?php } } ?>
<?php if ( isset($success) ) { foreach ( $success as $msg ) { ?><div class="success"><strong>SUCCESS!</strong> <?php echo $msg; ?></div><?php } } ?>

<form action="" method="post">
<table class="style1" width="100%" align="center" cellpadding="5" cellspacing="5">
	<tr>
    	<td class="top" colspan="2">Settings</td>
    </tr><tr class="light">
    	<td width="200"><strong>Your Name</strong></td>
   	  <td>This is your name and username. It will be included in any outgoing emails.<br /><input name="username" type="text" id="username" value="<?php echo $username; ?>" size="50" /></td>
    </tr><tr class="xlight">
    	<td width="200"><strong>Password</strong></td><td><em>Only fill out if changing.</em><br /><input name="password" type="password" id="password" value="" /> <input name="password2" type="password" id="password2" value="" /></td>
    </tr><tr class="light">
    	<td width="200"><strong>Your Email</strong></td>
   	  <td>All outgoing emails will be sent from this address.<br /><input name="email" type="text" id="email" value="<?php echo $email; ?>" size="50" /></td>
    </tr><tr class="xlight">
    	<td width="200"><strong>Trade Post URL</strong></td>
   	  <td>The URL to your tradepost. This will be included at the bottom of any outgoing emails.<br /><input name="url" type="text" id="url" value="<?php echo $url; ?>" size="50" /></td>
    </tr><tr class="light">
    	<td width="200"><strong>eTCG URL</strong></td>
   	  <td>The URL to your easyTCG admin panel.<br /><input name="etcgurl" type="text" id="etcgurl" value="<?php echo $etcgurl; ?>" size="50" /></td>
    </tr><tr class="xlight">
    	<td width="200"><strong>Log Date Format</strong></td>
    	<td>The date format for your log entries. Ex. <em>F d, Y</em> = <?php echo date("F d, Y"); ?>, <em>m/d/y</em> = <?php echo date("m/d/y"); ?>. For more date formatting options, <a href="http://php.net/manual/en/function.date.php" target="_blank">click here</a>.<br />
        <input name="dateformat" type="text" id="dateformat" value="<?php echo $dateformat; ?>" size="50" /></td>
    </tr><tr class="light">
    	<td width="200"><strong>Date Header Format</strong></td>
    	<td>This is the format for date headers in your logs. easyTCG will look for and use this pattern when attempting to automatically insert log entries. Use <em>[DATE]</em> to indicate where the date will be inserted in the pattern.<br />
        <input name="dateheaderformat" type="text" id="dateheaderformat" value="<?php echo $dateheaderformat; ?>" size="50" /></td>
    </tr><tr class="xlight">
    	<td width="200"><strong>Hiatus Trading</strong></td>
    	<td>Allow trading for TCGs with the status <em>hiatus</em>.<br /><label><input name="hiatustrading" type="radio" value="1" <?php if ( $hiatustrading == 1 ) { echo 'checked="checked"'; } ?> /> Enable</label> 
    	<label><input name="hiatustrading" type="radio" value="0" <?php if ( $hiatustrading == 0 ) { echo 'checked="checked"'; } ?> /> Disable</label></td>
    </tr><tr class="light">
    	<td width="200"><strong>Inactive Trading</strong></td><td>Allow trading for TCGs with the status <em>inactive</em>.<br /><label><input name="inactivetrading" type="radio" value="1" <?php if ( $inactivetrading == 1 ) { echo 'checked="checked"'; } ?> /> Enable</label> 
    	<label><input name="inactivetrading" type="radio" value="0" <?php if ( $inactivetrading == 0 ) { echo 'checked="checked"'; } ?> /> Disable</label></td>
    </tr><tr class="xlight">
    	<td width="200"><strong>Email Message</strong></td>
   	  <td><em>Optional</em>. Enter a short message to include at the top of trade-acceptance emails. A trade overview and the cards that you traded away will be included below this message.<br />
      <textarea name="emailmessage" cols="50" id="emailmessage"><?php echo $emailmessage; ?></textarea></td>
    </tr><tr>
   	  <td colspan="2" align="right" class="xdark"><input name="update" type="submit" id="update" value="Update" /> <input name="Reset" type="reset" id="submit" value="Reset Fields" /></td>
    </tr>
</table>
</form>

<?php include 'footer.php'; ?>