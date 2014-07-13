<?php include 'header.php';
	
if ( isset($_POST['submit']) ) {
	
	$sanitize = new Sanitize;
	$database = new Database;
	
	$name = $sanitize->for_db($_POST['name']);
	$url = $sanitize->for_db($_POST['url']);
	$cardsurl = $sanitize->for_db($_POST['cardsurl']);
	$cardspath = $sanitize->for_db($_POST['cardspath']);
	$defaultauto = $sanitize->for_db($_POST['defaultauto']);
	$autoupload = intval($sanitize->for_db($_POST['autoupload']));
	$status = $sanitize->for_db($_POST['status']);
	$format = $sanitize->for_db($_POST['format']);
	$additional = $sanitize->for_db($_POST['additional']);
	$exists = $database->num_rows("SELECT * FROM `tcgs` WHERE `name`='$name'");
	
	if ( substr($cardsurl, -1) != '/' ) { $cardsurl = "$cardsurl/"; }
	if ( substr($cardspath, -1) != '/' ) { $cardspath = "$cardspath/"; }
	if ( substr($defaultauto, -1) != '/' ) { $defaultauto = "$defaultauto/"; }
	
	if ( $name == '' ) { $error[] = "The name field can't be left blank."; }
	else if ( $exists != 0 ) { $error[] = "A TCG already exists with this name."; }
	else if ( $url == '' ) { $error[] = "The TCG URL field can't be left blank."; }
	else if ( $cardsurl == '' ) { $error[] = "The Cards Directory URL field can't be left blank."; }
	else if ( $cardspath == '' ) { $error[] = "The Cards Path field can't be left blank."; }
	else if ( $autoupload === '' ) { $error[] = "The Auto Upload field can't be left blank."; }
	else if ( $status == '' ) { $error[] = "The Status field can't be left blank."; }
	else if ( $format == '' ) { $error[] = "The Format field can't be left blank."; }
	else if ( !filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) || !filter_var($cardsurl, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) ) { $error[] = "Invalid URL. Please include <em>http://</em>."; }
	else if ( $defaultauto != '' && $defaultauto != '/' && $defaultauto != 'http://' && !filter_var($defaultauto, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) ) { $error[] = "Invalid URL. Please include <em>http://</em>."; }
	else if ( $autoupload != '0' && $autoupload != '1'  ) { $error[] = "Invalid value for the Auto Upload field."; }
	else {
		
		$today = date("Y-m-d");
		
		$result = $database->query("INSERT INTO `tcgs` (`name`,`url`,`cardsurl`,`cardspath`,`defaultauto`,`autoupload`,`lastupdated`,`status`,`format`) VALUE ('$name','$url','$cardsurl','$cardspath','$defaultauto','$autoupload','$today','$status','$format')");
		
		if ( !$result ) { $error[] = "Error inserting row. ".mysqli_error().""; }
		else {
			
			$tcginfo = $database->get_assoc("SELECT * FROM `tcgs` WHERE `name`='$name' LIMIT 1");
			$tcgid = $tcginfo['id'];
			
			if ( $additional != "" ) {
				
				$additional = explode(',',$additional);
			
				foreach ( $additional as $field ) {
					if ( !isset($error) ) {
						$field = str_replace(' ','',trim($field));
						$numrows = $database->num_rows("SELECT * FROM `additional` WHERE `name`='$field' AND `tcg`='$tcgid'");
						if ( $numrows == 0 ) {
							$result = $database->query("INSERT INTO `additional` (`name`,`tcg`) VALUE ('$field','$tcgid')");
							if ( !$result ) { $error[] = "Error inserting row. ".mysqli_error().""; }
						}
						else { $error[] = "Error adding additional fields. Fields can't share the same name."; }
					}
				}
			
			}
			
			if ( !isset($error) ) {
			
				$success = 'true';
			
			}
		
		}
		
	}
	
}

?>

<div class="content col-12 col-sm-12 col-lg-12">
	<h1>New TCG</h1>

	<?php if ( !isset($success) ) { ?>

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
			<label for="name">TCG Name</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="The name of the new TCG. Feel free to use special characters."></i>
			<input name="name" id="name" type="text" class="form-control" value="<?php if ( isset($_POST['submit']) ) { echo $_POST['name']; } ?>">
		</div>
		<div class="form-group">
			<label for="url">TCG URL</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="The URL to the TCG's website (NOT the URL to your tradepost)."></i>
			<input name="url" id="url" type="url" class="form-control" placeholder="http://" value="<?php if ( isset($_POST['submit']) ) { echo $_POST['url']; } ?>">
		</div>
		<div class="form-group">
			<label for="cardsurl">Cards Directory URL</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="The URL to the directory where you will be uploading your cards."></i>
			<input name="cardsurl" id="cardsurl" type="url" class="form-control" placeholder="http://" value="<?php if ( isset($_POST['submit']) ) { echo $_POST['cardsurl']; } ?>">
		</div>
		<div class="form-group">
			<label for="cardspath">Cards Path</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="The direct PATH to the directory where you will be uploading your cards. Don't forget trailing slashes."></i>
			<input name="cardspath" id="cardspath" type="text" class="form-control" placeholder="/home/user/tcg/cards/" value="<?php if ( isset($_POST['submit']) ) { echo $_POST['cardspath']; } ?>">
		</div>
		<div class="form-group">
			<label for="format">Image Format</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="The image format for your cards. (gif, png, jpg, jpeg)"></i>
			<input name="format" id="format" type="text" class="form-control" placeholder="ie. 'png'" value="<?php if ( isset($_POST['submit']) ) { echo $_POST['format']; } ?>">
		</div>
		<div class="form-group">
			<label for="defaultauto">Default Upload URL</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="The default URL to the directory where the TCG owner has uploaded their cards. This can be changed later for individual card categories. Leave this blank if you will not be using the auto upload feature."></i>
			<input name="defaultauto" id="defaultauto" type="url" class="form-control" placeholder="http://" value="<?php if ( isset($_POST['submit']) ) { echo $_POST['defaultauto']; } ?>">
		</div>
		<div class="form-group">
			<label for="autoupload">Auto Upload</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="Select YES to enable the auto upload feature. This feature will attempt to automatically upload your cards directly from the TCG's site."></i>			
			<label class="radio-inline">
				<input type="radio" name="autoupload" id="autoupload1" value="1"> Yes
			</label>
			<label class="radio-inline">
				<input type="radio" name="autoupload" id="autoupload2" value="0" checked> No
			</label>
		</div>
		<div class="form-group">
			<label for="status">Status</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="Your status in the TCG."></i>
			<select name="status" id="status" class="form-control">
				<option value="active" selected>Active</option>
				<option value="hiatus">Hiatus</option>
				<option value="inactive">Inactive</option>
			</select>
		</div>
		<div class="form-group">
			<label for="additional">Additional Fields</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="You can use additional fields to keep track of currency, items, coupons, etc. Separate the names of additional fields with commas. Leave it blank if you don't want to use this feature."></i>
			<input name="additional" id="additional" type="text" class="form-control" value="<?php if ( isset($_POST['submit']) ) { echo $_POST['additional']; } ?>">
		</div>
		<button name="submit" type="submit" id="submit" class="btn btn-primary btn-block">Add This TCG</button>
	</form>
</div>

<?php } else { ?>

<p><strong>The new TCG with the name <em><?php echo $name; ?></em> has been added successfully!</strong></p>
<p>&raquo; <a href="index.php">Return to Dashboard</a><br />&raquo; <a href="newtcg.php">Add Another TCG</a></p>

</div>
            
<?php } include 'footer.php'; ?>