<?php include 'header.php'; if ( $_GET['action'] == 'deletetcg' || !isset($_GET['id']) || $_GET['id'] == '' ) { ?>

<div class="content col-12 col-sm-12 col-lg-12">

	<h1>Manage TCGs</h1>
	<p>Please select a TCG to manage from the left dropdown menu, or add a new TCG by clicking on the "+" icon. Your TCGs can also be managed from the <a href="index.php">dashboard</a>.</p>

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

</div>

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
	
	<div class="content col-12 col-sm-12 col-lg-12">
		
		<h1>TCG Settings <small><?php echo $tcgname; ?></small></h1>
		
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
	
		<form action="manage.php?id=<?php echo $tcginfo['id']; ?>" method="post" role="form">
			<div class="row">
				<div class="col-md-6">
					<h2>TCG Settings</h2>
					<div class="form-group">
						<label for="name">TCG Name</label>
						<input name="name" type="text" id="name" class="form-control" value="<?php echo $tcginfo['name']; ?>">
					</div>
					<div class="form-group">
						<label for="url">TCG URL</label>
						<input name="url" type="url" id="url" class="form-control" value="<?php echo $tcginfo['url']; ?>">
					</div>
					<div class="form-group">
						<label for="cardsurl">Cards Directory URL</label>
						<input name="cardsurl" type="url" id="cardsurl" class="form-control" value="<?php echo $tcginfo['cardsurl']; ?>">
					</div>
					<div class="form-group">
						<label for="cardspath">Cards Path</label>
						<input name="cardspath" type="text" id="cardspath" class="form-control" value="<?php echo $tcginfo['cardspath']; ?>">
					</div>
					<div class="form-group">
						<label for="format">Image Format</label>
						<input name="format" type="text" id="format" class="form-control" value="<?php echo $tcginfo['format']; ?>">
					</div>
					<div class="form-group">
						<label for="defaultauto">Default Upload URL</label>
						<input name="defaultauto" type="text" id="defaultauto" class="form-control" value="<?php echo $tcginfo['defaultauto']; ?>">
					</div>
					<div class="form-group">
						<label for="autoupload">Auto Upload</label>
						<label class="radio-inline">
							<input type="radio" name="autoupload" id="autoupload1" value="1" <?php if ( $tcginfo['autoupload'] == 1 ) {  echo 'checked="checked"'; } ?>> Enable
						</label>
						<label class="radio-inline">
							<input type="radio" name="autoupload" id="autoupload2" value="0" <?php if ( $tcginfo['autoupload'] == 0 ) {  echo 'checked="checked"'; } ?>> Disable
						</label>
					</div>
					<div class="form-group">
						<label for="status">Status</label>
						<select name="status" id="status" class="form-control">
							<option value="active" <?php if ( $tcginfo['status'] == 'active' ) {  echo 'selected'; } ?>>Active</option>
							<option value="hiatus" <?php if ( $tcginfo['status'] == 'hiatus' ) {  echo 'selected'; } ?>>Hiatus</option>
							<option value="inactive" <?php if ( $tcginfo['status'] == 'inactive' ) {  echo 'selected'; } ?>>Inactive</option>
						</select>
					</div>
					<div class="form-group">
						<label for="lastupdated">Last Updated</label>
						<input name="lastupdated" type="date" id="lastupdated" class="form-control" value="<?php echo $tcginfo['lastupdated']; ?>">
					</div>
					<p><a href="index.php?id=<?php echo $id; ?>&action=deletetcg" onclick="go=confirm('Are you sure that you want to permanently delete this TCG? All data related to the TCG will be lost.'); return go;" class="text-danger"><i class="fa fa-times"></i> &nbsp; Delete This TCG</a></p>
				</div>
				<div class="col-md-6">
					<h2>Additional Fields</h2>
					<?php if ( $numrows === 0 ) { ?>
						<p class="text-info">You haven't created any additional fields!</p>
					<?php } else { while ( $row = mysqli_fetch_array($result) ) { ?>
					<div class="form-group">
						<input name="<?php echo $row['name']; ?>_eu" type="checkbox" id="<?php echo $row['name']; ?>_eu" value="1" <?php if ( $row['easyupdate'] == 1 ) { echo 'checked'; } ?> data-toggle="tooltip" data-placement="top" title="Show this field in the Easy Updater">
						&nbsp;
						<label for="<?php echo $row['name']; ?>_add"><?php echo $row['name']; ?></label>
						<a href="manage.php?id=<?php echo $id; ?>&action=delete&field=<?php echo $row['id']; ?>" onclick="go=confirm('Are you sure that you want to permanently delete this field? The contents will be lost completely.'); return go;" data-toggle="tooltip" data-placement="top" title="Delete this field"><i class="fa fa-times-circle text-danger"></i></a>
						<textarea name="<?php echo $row['name']; ?>_add" id="<?php echo $row['name']; ?>_add" class="form-control" rows="1"><?php echo $row['value']; ?></textarea>
					</div>
					<hr>
					<?php } } ?>
					<h2>Add a New Field</h2>
					<div class="form-group">
						<input name="newfield" type="text" id="newfield" class="form-control" placeholder="New Field Name">
					</div>
					<div class="form-group">
						<textarea name="newvalue" id="newvalue" class="form-control" rows="1" placeholder="New Field Value"></textarea>
					</div>
					<div class="checkbox">
					  <label>
						<input name="neweu" type="checkbox" id="neweu" value="1">
						Show this field in the Easy Updater
					  </label>
					</div>
				</div>
			</div>
			<button name="submit" type="submit" id="submit" class="btn btn-primary btn-block">Update This TCG</button>
		</form>
		
</div>
<?php } } include 'footer.php'; ?>