<?php include 'header.php'; if ( $_GET['action'] == 'deletetcg' || !isset($_GET['id']) || $_GET['id'] == '' ) { 

?>
           
<h1>Manage TCGs</h1>
<p>Please select a TCG to manage from the left dropdown menu, or add a new TCG by clicking on the "+" icon. Your TCGs can also be managed from the <a href="index.php">dashboard</a>.</p>

<?php if ( isset($error) ) { foreach ( $error as $msg ) {  ?><div class="errors"><strong>ERROR!</strong> <?php echo $msg; ?></div><?php } } ?>
<?php if ( isset($success) ) { foreach ( $success as $msg ) { ?><div class="success"><strong>SUCCESS!</strong> <?php echo $msg; ?></div><?php } } ?>


<?php } else if ( $_GET['id'] != '' && $_GET['action'] !== 'deletetcg' ) { 
	
	$id = intval($_GET['id']);
	$database = new Database;
	
	if ( $database->num_rows("SELECT * FROM `tcgs` WHERE `id`='$id'") == 0 ) { echo "This TCG does not exist."; }
	else {
	
		if ( isset($_POST['submit']) ) {
			
			$sanitize = new Sanitize;
			
			$name = $sanitize->for_db($_POST['name']);
			$url = $sanitize->for_db($_POST['url']);
			$cardsurl = $sanitize->for_db($_POST['cardsurl']);
			$cardspath = $sanitize->for_db($_POST['cardspath']);
			$defaultauto = $sanitize->for_db($_POST['defaultauto']);
			$autoupload = intval($sanitize->for_db($_POST['autoupload']));
			$status = $sanitize->for_db($_POST['status']);
			$format = $sanitize->for_db($_POST['format']);
			$lastupdated = $sanitize->for_db($_POST['lastupdated']);
			$exists = $database->num_rows("SELECT * FROM `tcgs` WHERE `name`='$name' AND `id`!='$id'");
			
			if ( substr($cardsurl, -1) != '/' ) { $cardsurl = "$cardsurl/"; }
			if ( substr($cardspath, -1) != '/' ) { $cardspath = "$cardspath/"; }
			if ( substr($defaultauto, -1) != '/' ) { $defaultauto = "$defaultauto/"; }
			
			$newfield = str_replace(' ','',$sanitize->for_db($_POST['newfield']));
			$newvalue = $sanitize->for_db($_POST['newvalue']);
			$neweu = $sanitize->for_db($_POST['neweu']);
			$exists2 = $database->num_rows("SELECT * FROM `additional` WHERE `tcg`='$id' AND `name`='$newfield'");
			
			$result = $database->query("SELECT * FROM `additional` WHERE `tcg`='$id'");
			while ( $row = mysqli_fetch_assoc($result) ) {
				$varname = ''.$row['name'].'_add';
				$$varname = $sanitize->for_db($_POST[$varname]);
				
				$varname = ''.$row['name'].'_eu';
				$$varname = $sanitize->for_db(intval($_POST[$varname]));
			}
			
			if ( $exists != 0 ) { $error[] = 'The TCG name is already taken by another entry.'; }
			else if ( $name == '' ) { $error[] = "The name field can't be left blank."; }
			else if ( $url == '' ) { $error[] = "The TCG URL field can't be left blank."; }
			else if ( $cardsurl == '' ) { $error[] = "The Cards Directory URL field can't be left blank."; }
			else if ( $cardspath == '' ) { $error[] = "The Cards Path field can't be left blank."; }
			else if ( $autoupload === '' ) { $error[] = "The Auto Upload field can't be left blank."; }
			else if ( $status == '' ) { $error[] = "Invalid Status field value."; }
			else if ( $format == '' ) { $error[] = "Invalid Format field value."; }
			else if ( $lastupdated == '' ) { $error[] = "Invalid Last Updated field value."; }
			else if ( $status != 'active' && $status != 'hiatus' && $status != 'inactive' ) { $error[] = "Invalid Status field value."; }
			else if ( !filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) || !filter_var($cardsurl, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) ) { $error[] = "Invalid URL. Please include <em>http://</em>."; }
			else if ( $defaultauto != '' && $defaultauto != '/' && $defaultauto != 'http://' && !filter_var($defaultauto, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) ) { $error[] = "Invalid URL. Please include <em>http://</em>."; }
			else if ( $autoupload != '0' && $autoupload != '1'  ) { $error[] = "Invalid value for the Auto Upload field."; }
			else if ( $newfield != '' && $newfield != 'new field' && $exists2 != 0 ) { $error[] = "An additional field with that name already exists."; }
			else {
				
				$tcginfo = $database->get_assoc("SELECT * FROM `tcgs` WHERE `id`='$id' LIMIT 1");
				
				if ( !isset($error) || $error == '' ) {
					
					$result = $database->query("UPDATE `tcgs` SET `name`='$name',`url`='$url',`cardsurl`='$cardsurl',`cardspath`='$cardspath',`defaultauto`='$defaultauto',`autoupload`='$autoupload',`status`='$status',`format`='$format',`lastupdated`='$lastupdated' WHERE `id`='$id'");
					if ( !$result ) { $error[] = 'Failed to update the table. '.mysqli_error().''; }
					else {
					
						$resultt = $database->query("SELECT * FROM `additional` WHERE `tcg`='$id'");
						while ( $row = mysqli_fetch_array($resultt) ) {
							
							if ( !isset($error) ) {
								
								$varname = ''.$row['name'].'_add';
								$addname = $$varname;
								
								$varname = ''.$row['name'].'_eu';
								$addeu = $$varname;
								
								$fieldname = $row['name'];
								$result = $database->query("UPDATE `additional` SET `value`='$addname', `easyupdate`='$addeu' WHERE `tcg`='$id' AND `name`='$fieldname'");
								if ( !$result ) { $error[] = "Failed to update the aditional field '$fieldname'. ".mysqli_error().""; }
							
							}
							
						}
						
						if ( !isset($error) ) {
							
							if ( $newfield != '' && $newfield != 'new field' && $newfield != 'newfield' && $neweu < 2 ) {
										
								$result = $database->query("INSERT INTO `additional` (`name`,`tcg`,`value`,`easyupdate`) VALUE ('$newfield','$id','$newvalue','$neweu')");
								if ( !$result ) { $error[] = "Failed to insert the new additonal field. ".mysqli_error().""; }
								else { $success[] = "Your changes and/or additions have been made successfully."; }
							
							}
							
							else {
								
								$success[] = "Your changes and/or additions have been made successfully.";
										
							}
						
						}
					
					}
					
				}
			
			}
		
		}
		
		if ( $_GET['action'] == 'delete' && isset($_GET['field']) ) {
		
			$fieldid = intval($_GET['field']);	
			$exists = $database->num_rows("SELECT * FROM `additional` WHERE `id`='$fieldid'");
			
			if ( $exists === 1 ) {
			
				$result  = $database->query("DELETE FROM `additional` WHERE `id` = '$fieldid' LIMIT 1");
				if ( !$result ) { $error[] = "There was an error while attempting to remove the field. ".mysqli_error().""; }
				else { $success[] = "The field has been removed."; }
			
			}
			
			else { $error[] = "The field no longer exists."; }
		
		}
		
		$tcginfo = $database->get_assoc("SELECT * FROM `tcgs` WHERE `id`='$id' LIMIT 1");
		$tcgname = $tcginfo['name'];
		$result = $database->query("SELECT * FROM `additional` WHERE `tcg`='$id'");
		$numrows = $database->num_rows("SELECT * FROM `additional` WHERE `tcg`='$id'");
	
	?>
		
		<h1>TCG Settings: <?php echo $tcgname; ?></h1>
		
		<br />
		
		<?php if ( isset($error) ) { foreach ( $error as $msg ) {  ?><div class="errors"><strong>ERROR!</strong> <?php echo $msg; ?></div><?php } } ?>
		<?php if ( isset($success) ) { foreach ( $success as $msg ) { ?><div class="success"><strong>SUCCESS!</strong> <?php echo $msg; ?></div><?php } } ?>
	
		<form action="manage.php?id=<?php echo $tcginfo['id']; ?>" method="post">
		<table class="style1" width="100%" align="center" cellpadding="5" cellspacing="5">
			<tr>
				<td class="top" colspan="2">Settings</td>
			</tr><tr class="light">
				<td width="200"><strong>TCG Name</strong></td>
				<td><input name="name" type="text" id="name" value="<?php echo $tcginfo['name']; ?>" size="50" /></td>
			</tr><tr class="xlight">
				<td width="200"><strong>TCG URL</strong></td>
				<td><input name="url" type="text" id="url" value="<?php echo $tcginfo['url']; ?>" size="50" /></td>
			</tr><tr class="light">
				<td width="200"><strong>Cards Directory URL</strong></td>
				<td><input name="cardsurl" type="text" id="cardsurl" value="<?php echo $tcginfo['cardsurl']; ?>" size="50" /></td>
			</tr><tr class="xlight">
				<td width="200"><strong>Cards Path</strong></td>
				<td><input name="cardspath" type="text" id="cardspath" value="<?php echo $tcginfo['cardspath']; ?>" size="50" /></td>
			</tr><tr class="light">
				<td width="200"><strong>Image Format</strong></td>
				<td><input name="format" type="text" id="format" value="<?php echo $tcginfo['format']; ?>" size="10" /></td>
			</tr><tr class="xlight">
				<td width="200"><strong>Default Upload URL</strong></td>
				<td><input name="defaultauto" type="text" id="defaultauto" value="<?php echo $tcginfo['defaultauto']; ?>" size="50" /></td>
			</tr><tr class="light">
				<td width="200"><strong>Auto Upload</strong></td>
				<td><label><input name="autoupload" type="radio" value="1" <?php if ( $tcginfo['autoupload'] == 1 ) {  echo 'checked="checked"'; } ?> />Enable</label> <label><input name="autoupload" type="radio" value="0" <?php if ( $tcginfo['autoupload'] == 0 ) {  echo 'checked="checked"'; } ?> /> Disable</label></td>
			</tr><tr class="xlight">
				<td width="200"><strong>Status</strong></td>
				<td><select name="status" id="status">
				  <option value="active" <?php if ( $tcginfo['status'] == 'active' ) {  echo 'selected="selected"'; } ?>>Active</option>
				  <option value="hiatus" <?php if ( $tcginfo['status'] == 'hiatus' ) {  echo 'selected="selected"'; } ?>>Hiatus</option>
				  <option value="inactive" <?php if ( $tcginfo['status'] == 'inactive' ) {  echo 'selected="selected"'; } ?>>Inactive</option>
				</select></td>
			</tr><tr class="light">
				<td width="200"><strong>Last Updated</strong></td>
				<td><input name="lastupdated" type="text" id="lastupdated" value="<?php echo $tcginfo['lastupdated']; ?>" size="50" /></td>
			</tr><tr>
				<td class="top" colspan="2">Additional Fields</td>
			</tr>
			<?php $i = 0; while ( $row = mysqli_fetch_array($result) ) { $i++; if ( $i % 2 == 0 ) { $class = 'xlight'; } else { $class = 'light'; } ?>
			<tr class="<?php echo $class; ?>">
				<td width="200"><strong><?php echo $row['name']; ?></strong></td>
			  <td><input name="<?php echo $row['name']; ?>_add" type="text" id="<?php echo $row['name']; ?>_add" value="<?php echo $row['value']; ?>" size="50" /> <input name="<?php echo $row['name']; ?>_eu" type="checkbox" id="<?php echo $row['name']; ?>_eu" value="1" <?php if ( $row['easyupdate'] == 1 ) { echo 'checked'; } ?>> <a href="manage.php?id=<?php echo $id; ?>&action=delete&field=<?php echo $row['id']; ?>" onclick="go=confirm('Are you sure that you want to permanently delete this field? The contents will be lost completely.'); return go;"><img src="images/delete.gif" alt="delete" /></a></td>
			</tr>
			<?php } ?>
			
			<?php $i++; if ( $i % 2 == 0 ) { $class = 'xlight'; } else { $class = 'light'; } ?>
			<tr class="<?php echo $class; ?>">
				<td width="200">+ <input name="newfield" type="text" id="newfield" value="new field" onfocus="if (this.value=='new field') this.value='';" onblur="if (this.value=='') this.value='new field';"></td>
			  <td><input name="newvalue" type="text" id="newvalue" value="value" size="50" onfocus="if (this.value=='value') this.value='';" onblur="if (this.value=='') this.value='value';" /> <input name="neweu" type="checkbox" id="neweu" value="1"></td>
			</tr>
			<tr>
				<td colspan="2" align="right" class="xdark"><input name="submit" type="submit" id="submit" value="Update This TCG" /> <input name="Reset" type="reset" id="submit" value="Reset Fields" /></td>
			</tr>
		</table>
		</form>
		
		<p><a href="index.php?id=<?php echo $id; ?>&action=deletetcg" onclick="go=confirm('Are you sure that you want to permanently delete this TCG? All data related to the TCG will be lost.'); return go;">&raquo; Remove This TCG</a></p>
			
<?php } } include 'footer.php'; ?>
