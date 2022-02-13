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
	if ( $url === '' || !filter_var($url, FILTER_VALIDATE_URL) ) { $error[] = "Invalid TCG post URL."; }
	if ( $etcgurl === '' || !filter_var($etcgurl, FILTER_VALIDATE_URL) ) { $error[] = "Invalid eTCG URL."; }
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
<div class="content col-12 col-sm-12 col-lg-12">
	<h1>EasyTCG Settings</h1>
	<p>These are the settings for your EasyTCG installation. To manage the settings for your TCGs, select a TCG from the dropdown menu on the right.</p>

	<?php if ( isset($error) ) { foreach ( $error as $msg ) {  ?>
		<div class="alert alert-danger alert-dismissible" role="alert">
			<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
			<strong>Error!</strong> <?php echo $msg; ?>
		</div>
	<?php } } ?>
	<?php if ( isset($success) ) { foreach ( $success as $msg ) { ?>
		<div class="alert alert-success alert-dismissible" role="alert">
			<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
			<strong>Success!</strong> <?php echo $msg; ?>
		</div>
	<?php } } ?>

	<form action="" method="post" role="form">
		<div class="form-group">
			<label for="username">Your Name</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="This is your name and username. It will be included in any outgoing emails."></i>
			<input type="text" class="form-control" name="username" id="username" value="<?php echo $username; ?>">
		</div>
		<div class="form-group">
			<label for="password">Password</label> <em>Only fill out if changing. Type twice.</em>
			<div class="row">
				<div class="col-xs-6">
					<input type="password" class="form-control" name="password" id="password">
				</div>
				<div class="col-xs-6">
					<input type="password" class="form-control" name="password2" id="password2">
				</div>
			</div>
		</div>
		<div class="form-group">
			<label for="email">Your Email</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="All outgoing emails will be sent from this address."></i>
			<input type="email" class="form-control" name="email" id="email" value="<?php echo $email; ?>">
		</div>
		<div class="form-group">
			<label for="url">Trade Post URL</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="The URL to your tradepost. This will be included at the bottom of any outgoing emails."></i>
			<input type="url" class="form-control" name="url" id="url" value="<?php echo $url; ?>">
		</div>
		<div class="form-group">
			<label for="etcgurl">EasyTCG Admin URL</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="The URL to your EasyTCG admin panel."></i>
			<input type="url" class="form-control" name="etcgurl" id="etcgurl" value="<?php echo $etcgurl; ?>">
		</div>
		<div class="form-group">
			<label for="dateformat">Log Date Format</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="The date format for your log entries. Ex. 'F d, Y' = <?php echo date("F d, Y"); ?>, 'm/d/y' = <?php echo date("m/d/y"); ?>."></i>
			<input type="text" class="form-control" name="dateformat" id="dateformat" value="<?php echo $dateformat; ?>">
		</div>
		<div class="form-group">
			<label for="dateheaderformat">Date Header Format</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="This is the format for date headers in your logs. EasyTCG will look for and use this pattern when attempting to automatically insert log entries. Use [DATE] to indicate where the date will be inserted in the pattern."></i>
			<input type="text" class="form-control" name="dateheaderformat" id="dateheaderformat" value="<?php echo $dateheaderformat; ?>">
		</div>
		<div class="form-group">
			<label for="hiatustrading">Hiatus Trading</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="Enable to allow trading for TCGs with the status 'hiatus'."></i>
			<label class="radio-inline">
				<input type="radio" name="hiatustrading" id="hiatustrading" value="1" <?php if ( $hiatustrading == 1 ) { echo 'checked="checked"'; } ?>> Enable
			</label>
			<label class="radio-inline">
				<input type="radio" name="hiatustrading" id="hiatustrading" value="0" <?php if ( $hiatustrading == 0 ) { echo 'checked="checked"'; } ?>> Disable
			</label>
		</div>
		<div class="form-group">
			<label for="inactivetrading">Inactive Trading</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="Enable to allow trading for TCGs with the status 'inactive'."></i>
			<label class="radio-inline">
				<input type="radio" name="inactivetrading" id="inactivetrading" value="1" <?php if ( $inactivetrading == 1 ) { echo 'checked="checked"'; } ?>> Enable
			</label>
			<label class="radio-inline">
				<input type="radio" name="inactivetrading" id="inactivetrading" value="0" <?php if ( $inactivetrading == 0 ) { echo 'checked="checked"'; } ?>> Disable
			</label>
		</div>
		<div class="form-group">
			<label for="emailmessage">Email Message</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="Enter a short message to include at the top of trade-acceptance emails. A trade overview and the cards that you traded away will be included below this message."></i> <em>Optional</em>
			<textarea class="form-control" rows="3" name="emailmessage" id="emailmessage"><?php echo $emailmessage; ?></textarea>
		</div>
		<button name="update" type="submit" id="update" class="btn btn-primary btn-block">Update</button>
	</form>
</div>

<?php include 'footer.php'; ?>