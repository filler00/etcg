<?php include 'header.php'; 

if ( !isset($_GET['id']) || $_GET['id'] == '' ) {
           
	echo '<div class="content col-12 col-sm-12 col-lg-12"><h1>Easy Updater</h1> <br />';
	
	$database = new Database;
	$result = $database->query("SELECT * FROM `tcgs` ORDER BY `name`");
	$count = mysqli_num_rows($result);
	
	if ( $count > 0 ) {
		while ( $row = mysqli_fetch_assoc($result) ) {
			echo '&raquo; <a href="update.php?id='.$row['id'].'">'.$row['name'].'</a><br />';
		}
	}
	else {
		echo 'You haven\'t set up any TCGs yet.';
	}
	echo '</div>';


} else if ( $_GET['id'] != '' ) {

	$database = new Database;
	$upload = new Upload;
	
	function trim_value(&$value) { $value = trim($value); }
	
	$id = intval($_GET['id']);
	
	if ( $database->num_rows("SELECT * FROM `tcgs` WHERE `id`='$id'") == 0 ) { echo "This TCG does not exist."; }
	else {
		
		if ( isset($_POST['update']) ) {
			
			$tcginfo = $database->get_assoc("SELECT * FROM `tcgs` WHERE `id`='$id' LIMIT 1");
			
			$sanitize = new Sanitize;
			function sanitize(&$value) { $sanitize = new Sanitize; $value = trim($value); $value = $sanitize->for_db($value); }
			
			$logentry = $sanitize->for_db($_POST['logentry']);
			$logtype = $sanitize->for_db($_POST['logtype']);
			if ( $logtype !== 'activity' && $logtype !== 'trade' ) { $logtype = 'activity'; }
			$newcards = $_POST['newcards'];
			$cardscat = $_POST['cardscat'];
			$activitylog = $sanitize->for_db($_POST['activitylog']);
			
			array_walk($newcards,'sanitize');
			array_walk($cardscat,'sanitize');
			
			$result = $database->query("SELECT * FROM `additional` WHERE `tcg`='$id'");
			while ( $row = mysqli_fetch_assoc($result) ) {
				$varname = ''.$row['name'].'_add';
				$$varname = $sanitize->for_db($_POST[$varname]);
			}
			
			// Update activity log first, if changed
			if ( $tcginfo['activitylog'] != $activitylog ) {
				$result = $database->query("UPDATE `tcgs` SET `activitylog`='$activitylog' WHERE `id`='$id' LIMIT 1");
				if ( !$result ) { $error[] = "Could not update the activity log. ".mysqli_error().""; }
			}
			
			// Insert the new log entry, if supplied
			if ( $logentry !== '' ) {
				$log = "- $logentry";
				$today = date("Y-m-d");
			
				$dateformat = $database->get_assoc("SELECT `value` FROM `settings` WHERE `setting`='dateformat'");
				$dateformat = $dateformat['value'];
				$dateheaderformat = $database->get_assoc("SELECT `value` FROM `settings` WHERE `setting`='dateheaderformat'");
				$dateheaderformat = $dateheaderformat['value'];
				
				$date = date("$dateformat");
				$logdate = $sanitize->for_db(str_replace('[DATE]',$date,$dateheaderformat));
				
				if ( $logtype == 'activity' ) { $logtype = 'activitylog'; } else { $logtype = 'tradelog'; }
				
				$oldlog = $sanitize->for_db($tcginfo[$logtype]);
				
				if ( strpos($oldlog,$logdate) !== false ) { $tradelog = str_replace($logdate,"$logdate\n$log",$oldlog); }
				else { $tradelog = "$logdate\n$log\n\n".$oldlog.""; }
				
				$result = $database->query("UPDATE `tcgs` SET `$logtype`='$tradelog', `lastupdated`='$today' WHERE `id`='$id' LIMIT 1");
				if ( !$result ) { $error[] = "Failed to insert new entry into the $logtype log. ".mysqli_error().""; } else { $success[] = "Inserted new log entry."; }
			}
			
			// Insert new cards, if any
			if ( implode(',',array_filter($newcards)) !== '' ) {
				
				$i = 0;
				foreach ( $newcards as $cardgroup ) {
					
					if ( !isset($error) && trim($cardgroup) !== '' ) {
						
						if ( $cardscat[$i] == 'collecting' ) {
							
							$cardgroup = explode(',',$cardgroup);
							array_walk($cardgroup, 'trim_value');
							
							foreach ( $cardgroup as $card ) {
								
								preg_match('/^([a-z0-9-_]+)([0-9]{2})$/i', $card, $matches);
								$deck = $matches[1];
								
								$exists = $database->num_rows("SELECT `id` FROM `collecting` WHERE `deck`='$deck' AND `tcg`='".$tcginfo['id']."' AND `mastered`='0' LIMIT 1");
								if ( $exists == 0 ) { 
									$result = $database->query("INSERT INTO `collecting` (`tcg`,`deck`,`cards`,`worth`,`count`,`break`,`filler`,`pending`,`puzzle`,`auto`,`uploadurl`) VALUE ('".$tcginfo['id']."','$deck','$card','1','15','5','filler','pending','0','0','default')");
									if ( !$result ) { $error[] = "Failed to add $card to a collecting deck."; }
									else { $success[] = "<em>No existing collecting deck was found matching $card</em>. Created a new collecting deck for $card titled $deck."; }
								}
								else {
									$collectinginfo = $database->get_assoc("SELECT * FROM `collecting` WHERE `deck`='$deck' AND `tcg`='".$tcginfo['id']."' AND `mastered`='0' LIMIT 1");
									$collectingid = $collectinginfo['id'];
									$collectingcards = $collectinginfo['cards'];
									$collectingauto = $collectinginfo['auto'];
									$autourl = $collectinginfo['uploadurl'];
									$format = $collectinginfo['format'];
									if ( $collectingcards === '' ) { $collectingcards = $card; } else { $collectingcards = "$collectingcards, $card"; }
									$result = $database->query("UPDATE `collecting` SET `cards`='$collectingcards' WHERE `id`='$collectingid' LIMIT 1");
									if ( !$result ) { $error[] = "Failed to add $card to a collecting deck."; }
									else { $success[] = "Added $card to the $deck collecting deck."; }
									
									if ( $tcginfo['autoupload'] == 1 && $collectingauto == 1 ) {
										
										if ( $autourl == 'default' ) { $defaultauto = $tcginfo['defaultauto']; }
										else { $defaultauto = $autourl; }
										
										if ( $format == 'default' ) { $formatval = $tcginfo['format']; }
										else { $formatval = $format; }
											
										$upsuccess = $upload->card($tcginfo,$collectinginfo,'collecting',$card);
									
										if ( $upsuccess === false ) { $error[] = "Failed to upload $card.$formatval from $defaultauto"; }
										else if ( $upsuccess === true ) { $success[] = "Uploaded $card.$formatval."; }
												
									}
									
								}
								
							}
						
						}
						
						else {
						
							$catinfo = $database->get_assoc("SELECT * FROM `cards` WHERE `tcg`='$id' AND `category`='".$cardscat[$i]."'");
							$catcards = $catinfo['cards'];
							$catauto = $catinfo['auto'];
							$autourl = $catinfo['autourl'];
							$format = $catinfo['format'];
							
							if ( $catcards === '' ) { $catcards = $cardgroup; } else { $catcards = ''.$catcards.', '.$cardgroup.''; }
							
							$catcards = explode(',',$catcards);
							array_walk($catcards, 'trim_value');
							
							if ( $tcginfo['autoupload'] == 1 && $catauto == 1 ) {
								foreach ( $catcards as $card ) {
									if ( !isset($error) ) {
										
										if ( $autourl == 'default' ) { $defaultauto = $tcginfo['defaultauto']; }
										else { $defaultauto = $autourl; }
										
										if ( $format == 'default' ) { $formatval = $tcginfo['format']; }
										else { $formatval = $format; }
										
										$upsuccess = $upload->card($tcginfo,$catinfo,'cards',$card);
									
										if ( $upsuccess === false ) { $error[] = "Failed to upload $card.$formatval from $defaultauto"; }
										else if ( $upsuccess === true ) { $success[] = "Uploaded $card.$formatval."; }
									
									}
								}
							}
							
							sort($catcards);
							$catcards = implode(', ',$catcards);
							
							$result = $database->query("UPDATE `cards` SET `cards`='$catcards' WHERE `tcg`='$id' AND `category`='".$cardscat[$i]."'");
							if ( !$result ) { $error[] = "Could not add cards to category '".$cardscat[$i]."'. ".mysqli_error().""; }
						
						}
						
					}
					
					$i++;
					
				}
				
			}
			
			// Update Additional Fields
			$resultt = $database->query("SELECT * FROM `additional` WHERE `tcg`='$id' AND `easyupdate`='1'");
			while ( $row = mysqli_fetch_assoc($resultt) ) {
				
				if ( !isset($error) ) {
					
					$varname = ''.$row['name'].'_add';
					$addname = $$varname;
					
					$fieldname = $row['name'];
					$result = $database->query("UPDATE `additional` SET `value`='$addname' WHERE `tcg`='$id' AND `name`='$fieldname' LIMIT 1");
					if ( !$result ) { $error[] = "Failed to update the aditional field '$fieldname'. ".mysqli_error().""; }
				
				}
				
			}
			
			if ( !isset($error) ) { $success[] = "All changes have been made successfully."; }
		
		}
	
		$tcginfo = $database->get_assoc("SELECT * FROM `tcgs` WHERE `id`='$id' LIMIT 1");
		
		$categories[] = 'collecting';
		$result = $database->query("SELECT `category` FROM `cards` WHERE `tcg`='$id'"); 
		while ( $row = mysqli_fetch_assoc($result) ) {
			$categories[] = $row['category'];
		}
		sort($categories);
		
		?>
		
		<div class="content col-12 col-sm-12 col-lg-12">
		
			<h1>Easy Updater</h1>
			
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
					<label for="tcgname">TCG</label>
					<input name="tcgname" id="tcgname" class="form-control" type="text" placeholder="<?php echo $tcginfo['name']; ?>" disabled>
				</div>
				<div class="form-group">
					<div class="row">
						<div class="col-xs-9">
							<label for="logentry">New Log Entry</label>
							<input name="logentry" id="logentry" type="text" class="form-control" placeholder="Paste a new log entry here!">
						</div>
						<div class="col-xs-3">
							<label for="logtype">Log Type</label>
							<select name="logtype" id="logtype" class="form-control">
								<option value="activity" selected>Activity</option>
								<option value="trade">Trade</option>
							</select>
						</div>
					</div>
				</div>
				
				<hr>
				
				<div class="form-group">	
					<div class="clearfix">
						<label>New Cards</label> <button type="button" class="btn btn-primary btn-xs pull-right btn-new-cards" data-toggle="tooltip" data-placement="top" title="" data-original-title="Add another card category"><i class="fa fa-plus"></i></button>
					</div>
					<div class="row eu-new-cards">
						<div class="col-xs-8">
							<input name="newcards[]" id="newcards[]" type="text" class="form-control" placeholder="card01, card02, card03...">
						</div>
						<div class="col-xs-4">
							<select name="cardscat[]" id="cardscat[]" class="form-control">
								<?php foreach ( $categories as $category ) { ?><option value="<?php echo trim($category); ?>" <?php if ( trim($categ[$i]) == trim($category) ) { echo 'selected'; } ?>><?php echo trim($category); ?></option><?php } ?>
							</select>
						</div>
					</div>
				</div>
				
				<?php if ( $database->num_rows("SELECT `name`, `value` FROM `additional` WHERE `tcg`='$id' AND `easyupdate`='1' ORDER BY `id`") > 0 ) { ?>
				<hr>
				<?php $result = $database->query("SELECT `name`, `value` FROM `additional` WHERE `tcg`='$id' AND `easyupdate`='1' ORDER BY `id`"); while ( $row = mysqli_fetch_assoc($result) ) { ?>
				<div class="form-group">
					<label for="<?php echo $row['name'] ?>_add"><?php echo $row['name'] ?></label>
					<input name="<?php echo $row['name'] ?>_add" id="<?php echo $row['name'] ?>_add" type="text" class="form-control" value="<?php echo $row['value']; ?>">
				</div>
				<?php } } ?>
				
				<hr>
				
				<div class="form-group">
					<label for="activitylog">Activity Log</label>
					<textarea name="activitylog" id="activitylog" rows="8" class="form-control"><?php echo $tcginfo['activitylog']; ?></textarea>
				</div>
				
				<button name="update" id="update" type="submit" class="btn btn-primary btn-block">Update</button>
				
			</form>
			
		</div>

<?php } } include 'footer.php'; ?>