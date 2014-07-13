<?php include 'header.php'; if ( !isset($_GET['id']) || $_GET['id'] == '' ) { ?>

<div class="content col-12 col-sm-12 col-lg-12">
	<h1>Manage Cards</h1>
</div>

<?php } else if ( $_GET['id'] != '' ) { 
	
	$id = intval($_GET['id']);
	$database = new Database;
	$upload = new Upload;

	$tcginfo = $database->get_assoc("SELECT * FROM `tcgs` WHERE `id`='$id' LIMIT 1");
	$altname = strtolower(str_replace(' ','',$tcginfo['name']));
	
	if ( isset($_POST['update']) ) {
		
		$sanitize = new Sanitize;
		
		$catid = intval($_POST['id']);
		$category = $sanitize->for_db($_POST['category']);
		$cards = $sanitize->for_db($_POST['cards']);
		$worth = intval($_POST['worth']);
		$auto = intval($_POST['auto']);
		$priority = intval($_POST['priority']);
		$autourl = $sanitize->for_db($_POST['autourl']);
		$format = $sanitize->for_db($_POST['format']);
		
		if ( $autourl != 'default' && $autourl != '' && substr($autourl, -1) != '/' ) { $autourl = "$autourl/"; }
		if ( $autourl == '' ) { $autourl = 'default'; }
		if ( $format == '' ) { $format = 'default'; }
		if ( $worth === '' ) { $worth = 1; }
		
		if ( $category == '' ) { $error[] = "The category name must be defined."; }
		else if ( $auto != 1 && $auto != 0 ) { $error[] = "Invalid auto value."; }
		else if ( $priority != 1 && $priority != 2 && $priority != 3 ) { $error[] = "Invalid priority value."; }
		else if ( $autourl != 'default' && !filter_var($autourl, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) ) { $error[] = "Invalid Auto URL."; }
		else if ( $database->num_rows("SELECT * FROM `cards` WHERE `id`=".$catid."") != 1 ) { $error[] = "Invalid category ID."; }
		else {
			
			if ( $cards != '' ) {
				$cards = explode(',',$cards);
				
				function trim_value(&$value) { 
					$value = trim($value); 
				}
				
				array_walk($cards, 'trim_value');
				
				if ( $tcginfo['autoupload'] == 1 && $auto == 1 ) {
					foreach ( $cards as $card ) {
						if ( !isset($error) ) {
						
							if ( $autourl == 'default' ) { $defaultauto = $tcginfo['defaultauto']; }
							else { $defaultauto = $autourl; }
							
							if ( $format == 'default' ) { $formatval = $tcginfo['format']; }
							else { $formatval = $format; }

							$upsuccess = $upload->card($tcginfo,'','cards',$card,$defaultauto,$formatval);
									
							if ( $upsuccess === false ) { $error[] = "Failed to upload $card.$formatval from $defaultauto"; }
							else if ( $upsuccess === true ) { 
								if ( !isset($success) || (isset($success) && !in_array("All missing cards have been uploaded",$success)) ) {
									$success[] = "All missing cards have been uploaded";
								}
							}
						
						}
					}
				}
				
				sort($cards);
				$cards = implode(', ',$cards);
			}
		
			$result = $database->query("UPDATE `cards` SET `category`='$category', `cards`='$cards', `worth`='$worth', `auto`='$auto', `autourl`='$autourl', `format`='$format', `priority`='$priority' WHERE `id`='$catid' LIMIT 1");
			if ( !$result ) { $error[] = "Could not update the category. ".mysqli_error().""; }
			else { $success[] = "Category <em>$category</em> was updated successfully!"; }
		
		}
		
	}
	
	if ( isset($_POST['newcat']) ) {
		
		$sanitize = new Sanitize;
		
		$category = $sanitize->for_db($_POST['category']);
		$worth = intval($_POST['worth']);
		$auto = intval($_POST['auto']);
		$autourl = $sanitize->for_db($_POST['autourl']);
		
		if ( $autourl != 'default' && $autourl != '' && substr($autourl, -1) != '/' ) { $autourl = "$autourl/"; }
		if ( $autourl == '' ) { $autourl = 'default'; }
		if ( $worth === '' ) { $worth = 1; }
		
		$exists = $database->num_rows("SELECT `category` FROM `cards` WHERE `tcg`='$id' AND `category`='$category'");
		
		if ( $category == '' || $category == 'category name' ) { $error[] = "The category name must be defined."; }
		else if ( $exists != 0 ) { $error[] = "A category witht that name already exists."; }
		else if ( $auto != 1 && $auto != 0 ) { $error[] = "Invalid auto value."; }
		else if ( $autourl != 'default' && !filter_var($autourl, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) ) { $error[] = "Invalid Auto URL."; }
		else {
		
			$result = $database->query("INSERT INTO `cards` (`tcg`,`category`,`worth`,`auto`,`autourl`) VALUE ('$id','$category','$worth','$auto','$autourl')");
			if ( !$result ) { $error[] = "Could not update the category. ".mysqli_error().""; }
			else { $success[] = "Category <em>$category</em> was created successfully."; }
		
		}
		
	}
	
	if ( $_GET['action'] == 'delete' && isset($_GET['cat']) ) {
	
		$catid = intval($_GET['cat']);	
		$exists = $database->num_rows("SELECT * FROM `cards` WHERE `id`='$catid'");
		
		if ( $exists === 1 ) {
		
			$result  = $database->query("DELETE FROM `cards` WHERE `id` = '$catid' LIMIT 1");
			if ( !$result ) { $error[] = "There was an error while attempting to remove the category. ".mysqli_error().""; }
			else { $success[] = "The category has been removed."; }
		
		}
		
		else { $error[] = "The category no longer exists."; }
	
	}
	
	?>
    
	<div class="content col-12 col-sm-12 col-lg-12">
	
		<h1>Manage Cards <small><?php echo $tcginfo['name']; ?></small></h1>
		<p class="clearfix">
			&raquo; <a href="collecting.php?id=<?php echo $id; ?>">View Collecting</a> &nbsp; 
			&raquo; <a href="mastered.php?id=<?php echo $id; ?>">View Mastered</a>
			<button class="btn btn-primary btn-xs pull-right" data-toggle="modal" data-target="#new-category-modal"><i class="fa fa-plus"></i> &nbsp; New Category</button>
		</p>
		
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
		
		<div class="modal fade" id="new-category-modal" tabindex="-1" role="dialog" aria-labelledby="new-category-label" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
						<h2 class="modal-title" id="new-category-label">New Category</h2>
					</div>
					<div class="modal-body">
						<form action="cards.php?id=<?php echo $id; ?>" method="post" role="form">
						<div class="form-group">
							<label for="category">Category Name</label>
							<input name="category" id="category" type="text" class="form-control" placeholder="ie. 'keeping'">
						</div>
						<div class="form-group">
							<label for="worth">Card Worth</label>
							<input name="worth" id="worth" type="number" class="form-control" placeholder="ie. 1">
						</div>
						<div class="form-group">
							<label for="autourl">Auto-Upload URL</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="Leave as 'default' to use the Auto-Upload URL defined in the TCG Settings"></i>
							<input name="autourl" id="autourl" type="text" class="form-control" value="default">
						</div>
						<div class="form-group">
							<div class="checkbox">
								<label>
									<input name="auto" id="auto" type="checkbox" value="1">
									Enable Auto-Upload for this category
								</label>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						<button name="newcat" id="newcat" type="submit" class="btn btn-primary">Create Category</button>
						</form>
					</div>
				</div>
			</div>
		</div>
		
		<div class="panel-group" id="cards-panel">	
		
		<?php $result = $database->query("SELECT * FROM `cards` WHERE `tcg`='$id' ORDER BY `category`");
		while ( $row = mysqli_fetch_assoc($result) ) { ?>

			<div class="panel panel-default">
				<div class="panel-heading clearfix" data-toggle="collapse" data-target="#cat<?php echo $row['id']; ?>">
					<i class="fa fa-th-large"></i> &nbsp; <?php echo $row['category']; ?>
					<span class="pull-right"><i class="fa fa-chevron-down"></i></span>
				</div>
				<div id="cat<?php echo $row['id']; ?>" class="panel-collapse collapse">
					<div class="panel-body">
						<form action="cards.php?id=<?php echo $id; ?>" method="post" role="form">
							<input name="id" type="hidden" id="id" value="<?php echo $row['id']; ?>">
							
							<div class="row">
								<div class="col-md-4">
									<div class="form-group">
										<label for="category">Category Name</label>
										<input type="text" class="form-control" id="category" name="category" placeholder="Category Name" value="<?php echo $row['category']; ?>">
									</div>
									<div class="form-group">
										<div class="row">
											<div class="col-xs-4">
												<label for="worth">Worth</label>
										<input type="number" class="form-control" id="worth" name="worth" value="<?php echo $row['worth']; ?>">
											</div>
											<div class="col-xs-4">
												<label for="format">Format</label>
												<input type="text" class="form-control" id="format" name="format" value="<?php echo $row['format']; ?>">
											</div>
											<div class="col-xs-4">
												<label for="worth">Priority</label>
												<select name="priority" id="priority" class="form-control">
													<option value="1" <?php if ( $row['priority'] == 1 ) { echo 'selected'; } ?>>Low</option>
													<option value="2" <?php if ( $row['priority'] == 2 ) { echo 'selected'; } ?>>Medium</option>
													<option value="3" <?php if ( $row['priority'] == 3 ) { echo 'selected'; } ?>>High</option>
												</select>
											</div>
										</div>
									</div>
									<div class="form-group">
										<div class="row">
											<div class="col-xs-10">
												<label for="autourl">Auto-Upload URL</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="Leave as 'default' to use the Auto-Upload URL defined in the TCG Settings"></i>
												<input type="text" class="form-control" id="autourl" name="autourl" value="<?php echo $row['autourl']; ?>">
											</div>
											<div class="col-xs-2">
												<label for="auto" class="sr-only">Enable</label>
												<input name="auto" type="checkbox" id="auto" value="1" <?php if ( $row['auto'] == 1 ) { echo 'checked'; } ?> data-toggle="tooltip" data-placement="top" title="Enable Auto-Upload" style="margin-top: 35px;">
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-8">
									<div class="form-group">
										<label for="cards">Cards</label>
										<textarea name="cards" class="form-control" rows="9" id="cards"><?php echo $row['cards']; ?></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="panel-footer clearfix">
							<div class="btn-group pull-right">
									<button name="update" id="update" type="submit" class="btn btn-sm btn-primary">Update Category</button>
									<a class="btn btn-danger btn-sm" href="cards.php?id=<?php echo $id; ?>&action=delete&cat=<?php echo $row['id']; ?>" onclick="go=confirm('Are you sure that you want to permanently delete this category? The contents will be lost completely.'); return go;" data-toggle="tooltip" data-placement="top" title="Delete This Category"><i class="fa fa-times-circle"></i></a>
							</div>
						</form>
					</div>
				</div>
			</div>
        
		<?php } ?>
		
		</div>
		
	</div>

<?php } include 'footer.php'; ?>