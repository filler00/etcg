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
           
<h1>New TCG</h1>

<?php if ( !isset($success) ) { ?>

<p>Add a new TCG to your easyTCG card manager!</p>

<?php if ( isset($error) ) { foreach ( $error as $msg ) {  ?><div class="errors"><strong>ERROR!</strong> <?php echo $msg; ?></div><?php } } ?>

<br />

<form action="" method="post">
<table class="style1" width="100%" align="center" cellpadding="5" cellspacing="5">
	<tr>
    	<td class="top" colspan="2">Settings</td>
    </tr><tr class="light">
    	<td width="200"><strong>TCG Name</strong></td>
    	<td>The name of the new TCG. Feel free to use special characters.<br /><input name="name" type="text" id="name" value="<?php if ( isset($_POST['submit']) ) { echo $_POST['name']; } ?>" size="50" /></td>
    </tr><tr class="xlight">
    	<td width="200"><strong>TCG URL</strong></td><td>The URL to the TCG's website (NOT the URL to your card collection).<br /><input name="url" type="text" id="url" value="<?php if ( isset($_POST['submit']) ) { echo $_POST['url']; } else { echo 'http://'; } ?>" size="50" /></td>
    </tr><tr class="light">
    	<td width="200"><strong>Cards Directory URL</strong></td>
    	<td>The URL to the directory where you will be uploading your cards.<br /><input name="cardsurl" type="text" id="cardsurl" value="<?php if ( isset($_POST['submit']) ) { echo $_POST['cardsurl']; } else { echo 'http://'; } ?>" size="50" /></td>
    </tr><tr class="xlight">
    	<td width="200"><strong>Cards Path</strong></td>
    	<td>The direct PATH to the directory where you will be uploading your cards. Don't forget <em>trailing slashes</em>.<br /><input name="cardspath" type="text" id="cardspath" value="<?php if ( isset($_POST['submit']) ) { echo $_POST['cardspath']; } else { echo '/'; } ?>" size="50" /></td>
    </tr><tr class="light">
    	<td width="200"><strong>Image Format</strong></td>
    	<td>The image format for your cards. (gif, png, jpg, jpeg)<br /><input name="format" type="text" id="format" value="<?php if ( isset($_POST['submit']) ) { echo $_POST['format']; } else { echo 'gif'; } ?>" size="10" /></td>
    </tr><tr class="xlight">
    	<td width="200"><strong>Default Upload URL</strong></td>
    	<td>The default URL to the directory where the TCG owner has uploaded their cards. This can be changed later for individual card categories. <em>Leave this blank if you will not be using the auto upload feature.</em><br /><input name="defaultauto" type="text" id="defaultauto" value="<?php if ( isset($_POST['submit']) ) { echo $_POST['defaultauto']; } else { echo 'http://'; } ?>" size="50" /></td>
    </tr><tr class="light">
    	<td width="200"><strong>Auto Upload</strong></td><td>Select YES to enable the auto upload feature. This feature will attempt to automatically upload your cards directly from the TCG's site.<br /><label><input name="autoupload" type="radio" value="1" />Yes</label> <label><input name="autoupload" type="radio" value="0" checked="checked" /> No</label></td>
    </tr><tr class="xlight">
    	<td width="200"><strong>Status</strong></td><td>Your status in the TCG.<br /><select name="status" id="status">
    	  <option value="active" selected="selected">Active</option>
    	  <option value="hiatus">Hiatus</option>
          <option value="inactive">Inactive</option>
    	</select></td>
    </tr><tr>
    	<td class="top" colspan="2">Additional Fields</td>
    </tr><tr class="xlight">
    	<td width="200"><strong>Additonal Fields</strong></td>
    	<td>You can use additional fields to keep track of currency, items, coupons, etc. Separate the names of additonal fields with commas. Leave it blank if you don't want to use this feature.<br /><input name="additional" type="text" id="additional" value="<?php if ( isset($_POST['submit']) ) { echo $_POST['additional']; } ?>" size="50" /></td>
    </tr><tr>
    	<td colspan="2" align="right" class="xdark"><input name="submit" type="submit" id="submit" value="Add This TCG" /> <input name="Reset" type="reset" id="submit" value="Reset Fields" /></td>
    </tr>
</table>
</form>

<?php } else { ?>

<p><strong>The new TCG with the name <em><?php echo $name; ?></em> has been added successfully!</strong></p>
<p>&raquo; <a href="index.php">Return to Dashboard</a><br />&raquo; <a href="newtcg.php">Add Another TCG</a></p>
            
<?php } include 'footer.php'; ?>
