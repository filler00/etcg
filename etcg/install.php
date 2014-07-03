<?php define('VALID_INC', TRUE); include 'class_lib.php';

if ( isset($_POST['install']) ) {
	
	$database = new Database;
	$database->connect();
	$sanitize = new Sanitize;
	
	$username = $sanitize->for_db($_POST['username']);
	$password = $sanitize->for_db($_POST['password']);
	$password2 = $sanitize->for_db($_POST['password2']);
	$email = $sanitize->for_db($_POST['email']);
	$url = $sanitize->for_db($_POST['url']);
	$emailmessage = $sanitize->for_db($_POST['emailmessage']);
	$hiatustrading = intval($_POST['hiatustrading']);
	$inactivetrading = intval($_POST['inactivetrading']);
	$etcgurl = $sanitize->for_db($_POST['etcgurl']);
	$dateformat = $sanitize->for_db($_POST['dateformat']);
	$dateheaderformat = $sanitize->for_db($_POST['dateheaderformat']);
	
	if ( substr($etcgurl, -1) != '/' ) { $etcgurl = "$etcgurl/"; }

	if ( $username === '' ) { $error[] = "Your username can't be left blank."; }
	if ( !preg_match('/^[a-zA-Z0-9]{3,15}$/i', $username) ) { $error[] = "Your username must consist of 3-15 alphanumeric characters."; }
	if ( $password === '' ) { $error[] = "You must select a password."; }
	if ( $password !== '' && $password !== $password2 ) { $error[] = "The passwords did not match."; }
	if ( $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) ) { $error[] = "Invalid email address."; }
	if ( $url === '' || !filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) ) { $error[] = "Invalid TCG post URL."; }
	if ( $etcgurl === '' || !filter_var($etcgurl, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) ) { $error[] = "Invalid eTCG URL."; }
	if ( $dateformat === '' ) { $error[] = "The date format can't be left blank."; }
	if ( $dateheaderformat === '' || strpos($dateheaderformat,'[DATE]') === false ) { $error[] = "Invalid date header format."; }
	if ( $hiatustrading != 1 && $hiatustrading != 0 ) { $error[] = "Invalid value for hiatus trading."; }
	if ( $inactivetrading != 1 && $inactivetrading != 0 ) { $error[] = "Invalid value for inactive trading."; }
	if ( !isset($error) ) {
		
		$password = sha1("$password".Config::DB_SALT."");
		
		// Create Additional Table 
		$query = "CREATE TABLE IF NOT EXISTS `additional` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(50) NOT NULL,
		  `tcg` int(11) NOT NULL,
		  `value` text NOT NULL,
		  `easyupdate` int(11) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		)";
		
		$result = mysql_query($query);
		if ( !$result ) { $error[] = "Could not create table <em>additional</em>. ".mysql_error().""; }
		
		// Create Cards Table
		$query = "CREATE TABLE IF NOT EXISTS `cards` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `tcg` int(11) NOT NULL,
		  `category` varchar(50) NOT NULL,
		  `cards` longtext NOT NULL,
		  `worth` int(11) NOT NULL,
		  `auto` int(11) NOT NULL DEFAULT '0',
		  `autourl` varchar(255) NOT NULL DEFAULT 'default',
		  `priority` int(11) NOT NULL DEFAULT '1',
		  PRIMARY KEY (`id`)
		)";
		
		$result = mysql_query($query);
		if ( !$result ) { $error[] = "Could not create table <em>cards</em>. ".mysql_error().""; }
		
		// Create Collecting Table
		$query = "CREATE TABLE IF NOT EXISTS `collecting` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `tcg` int(11) NOT NULL,
		  `deck` varchar(150) NOT NULL,
		  `sort` int(11) NOT NULL DEFAULT '0',
		  `cards` text NOT NULL,
		  `worth` int(11) NOT NULL,
		  `count` int(11) NOT NULL,
		  `break` int(11) NOT NULL,
		  `filler` varchar(150) NOT NULL,
		  `pending` varchar(150) NOT NULL,
		  `puzzle` int(11) NOT NULL DEFAULT '0',
		  `group` int(11) NOT NULL DEFAULT '0',
		  `auto` int(11) NOT NULL DEFAULT '0',
		  `uploadurl` varchar(255) NOT NULL DEFAULT 'default',
		  `mastered` int(11) NOT NULL DEFAULT '0',
		  `mastereddate` date NOT NULL,
		  `badge` varchar(50) NOT NULL,
		  PRIMARY KEY (`id`)
		)";
		
		$result = mysql_query($query);
		if ( !$result ) { $error[] = "Could not create table <em>collecting</em>. ".mysql_error().""; }
		
		// Create Settings Table
		$query = "CREATE TABLE IF NOT EXISTS `settings` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `setting` varchar(50) NOT NULL,
		  `value` varchar(255) NOT NULL,
		  PRIMARY KEY (`id`)
		)";
		
		$result = mysql_query($query);
		if ( !$result ) { $error[] = "Could not create table <em>settings</em>. ".mysql_error().""; }
		
		// Create TCGs Table
		$query = "CREATE TABLE IF NOT EXISTS `tcgs` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(30) NOT NULL,
		  `url` varchar(255) NOT NULL,
		  `cardsurl` varchar(255) NOT NULL,
		  `cardspath` varchar(255) NOT NULL,
		  `status` varchar(8) NOT NULL,
		  `format` varchar(4) NOT NULL DEFAULT 'gif',
		  `defaultauto` varchar(255) NOT NULL,
		  `autoupload` int(11) NOT NULL DEFAULT '0',
		  `lastupdated` date NOT NULL,
		  `activitylog` longtext NOT NULL,
		  `activitylogarch` longtext NOT NULL,
		  `tradelog` longtext NOT NULL,
		  `tradelogarch` longtext NOT NULL,
		  PRIMARY KEY (`id`)
		)";
		
		$result = mysql_query($query);
		if ( !$result ) { $error[] = "Could not create table <em>tcgs</em>. ".mysql_error().""; }
		
		// Create Trades Table
		$query = "CREATE TABLE IF NOT EXISTS `trades` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `tcg` int(11) NOT NULL,
		  `trader` varchar(50) NOT NULL,
		  `email` varchar(255) NOT NULL,
		  `giving` text NOT NULL,
		  `givingcat` text NOT NULL,
		  `receiving` text NOT NULL,
		  `receivingcat` text NOT NULL,
		  `emailcards` int(11) NOT NULL DEFAULT '0',
		  `type` varchar(10) NOT NULL DEFAULT 'outgoing',
		  `date` date NOT NULL,
		  PRIMARY KEY (`id`)
		)";
		
		$result = mysql_query($query);
		if ( !$result ) { $error[] = "Could not create table <em>trades</em>. ".mysql_error().""; }
		
		// Add Settings
		$query = "INSERT INTO `settings` (`setting`, `value`) VALUES
			('username', '$username'),
			('password', '$password'),
			('email', '$email'),
			('url', '$url'),
			('emailmessage', '$emailmessage'),
			('hiatustrading', '$hiatustrading'),
			('inactivetrading', '$inactivetrading'),
			('etcgurl', '$etcgurl'),
			('dateformat', '$dateformat'),
			('dateheaderformat', '$dateheaderformat')";
		
		$result = mysql_query($query);
		if ( !$result ) { $error[] = "Could not populate table <em>settings</em>. ".mysql_error().""; }
		
		if ( !isset($error) ) { $success[] = "Installation has completed. <em>Please delete this file</em>, then feel free to <a href=\"index.php\">log in</a>!"; }
		
	}

}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>easyTCG FM</title>
<link href="style.css" rel="stylesheet" type="text/css" />
</head>

<body>

<div style="background-color: #FFF; width: 800px; margin: 100px auto; padding: 25px;">

	<h1>Easy TCG FM Installation</h1>
    <p>Fill out the form completely and click <em>Install</em> ONCE. It may take a moment to populate the database, so wait for it. <strong>DELETE this file (install.php) once installation is complete.</strong></p>
	<p><strong>**</strong> If you have not inserted your database settings into your class_lib.php file, you must do so FIRST.</p>
    
    <?php if ( isset($error) ) { foreach ( $error as $msg ) {  ?><br /><div align="center" class="errors"><strong>ERROR!</strong> <?php echo $msg; ?></div><br /><?php } } ?>
	<?php if ( isset($success) ) { foreach ( $success as $msg ) { ?><br /><div align="center" class="xlight"><strong>SUCCESS!</strong> <?php echo $msg; ?></div><br /><?php } } ?>
    
    <form action="" method="post">
    <table class="style1" align="center" cellpadding="5" cellspacing="5">
        <tr>
            <td class="top" colspan="2">Settings</td>
        </tr><tr class="light">
            <td width="200"><strong>Your Name</strong></td>
          <td>This is your username to log in to the admin panel, as well as your name, which will be included in any outgoing emails.<br />
            <input name="username" type="text" id="username" value="<?php if ( isset($_POST['install']) ) { echo $_POST['username']; } ?>" size="50" /></td>
        </tr><tr class="xlight">
            <td width="200"><strong>Password</strong></td><td>Your password to log in to the eTCG admin panel. Type twice.<br /><input name="password" type="password" id="password" value="" /> <input name="password2" type="password" id="password2" value="" /></td>
        </tr><tr class="light">
            <td width="200"><strong>Your Email</strong></td>
          <td>All outgoing emails will be sent from this address.<br /><input name="email" type="text" id="email" value="<?php if ( isset($_POST['install']) ) { echo $_POST['email']; } ?>" size="50" /></td>
        </tr><tr class="xlight">
            <td width="200"><strong>Trade Post URL</strong></td>
          <td>The URL to your tradepost. This will be included at the bottom of any outgoing emails.<br /><input name="url" type="text" id="url" value="<?php if ( isset($_POST['install']) ) { echo $_POST['url']; } else { echo 'http://'; } ?>" size="50" /></td>
        </tr><tr class="light">
            <td width="200"><strong>eTCG URL</strong></td>
          <td>The URL to your easyTCG admin panel.<br /><input name="etcgurl" type="text" id="etcgurl" value="<?php if ( isset($_POST['install']) ) { echo $_POST['etcgurl']; } else { echo 'http://'; } ?>" size="50" /></td>
        </tr><tr class="xlight">
            <td width="200"><strong>Log Date Format</strong></td>
          <td>The date format for your log entries. Ex. <em>F d, Y</em> = <?php echo date("F d, Y"); ?>, <em>m/d/y</em> = <?php echo date("m/d/y"); ?>. For more date formatting options, <a href="http://php.net/manual/en/function.date.php" target="_blank">click here</a>.<br />
            <input name="dateformat" type="text" id="dateformat" value="<?php if ( isset($_POST['install']) ) { echo $_POST['dateformat']; } else { echo 'F d, Y'; } ?>" size="50" /></td>
        </tr><tr class="light">
            <td width="200"><strong>Date Header Format</strong></td>
          <td>This is the format for date headers in your logs. easyTCG will look for and use this pattern when attempting to automatically insert log entries. Use <em>[DATE]</em> to indicate where the date will be inserted in the pattern.<br />
            <input name="dateheaderformat" type="text" id="dateheaderformat" value="<?php if ( isset($_POST['install']) ) { echo $_POST['dateheaderformat']; } else { echo '[DATE] ------------'; } ?>" size="50" /></td>
        </tr><tr class="xlight">
            <td width="200"><strong>Hiatus Trading</strong></td>
            <td>Enable or disable trading for TCGs with the status <em>hiatus</em>.<br /><label><input name="hiatustrading" type="radio" value="1" checked="checked" /> Enable</label> 
            <label><input name="hiatustrading" type="radio" value="0" /> Disable</label></td>
        </tr><tr class="light">
            <td width="200"><strong>Inactive Trading</strong></td><td>Enable or disable trading for TCGs with the status <em>inactive</em>.<br /><label><input name="inactivetrading" type="radio" value="1" /> Enable</label> 
            <label><input name="inactivetrading" type="radio" value="0" checked="checked" /> Disable</label></td>
        </tr><tr class="xlight">
            <td width="200"><strong>Email Message</strong></td>
          <td><em>Optional</em>. Enter a short message to include at the top of trade-acceptance emails. A trade overview and the cards that you traded away will be included below this message.<br />
          <textarea name="emailmessage" cols="50" id="emailmessage"><?php if ( isset($_POST['install']) ) { echo $_POST['emailmessage']; } ?></textarea></td>
        </tr><tr>
          <td colspan="2" align="right" class="xdark"><input name="install" type="submit" id="install" value="Install" /> <input name="Reset" type="reset" id="submit" value="Reset Fields" /></td>
        </tr>
    </table>
    </form>

</div>

</body>