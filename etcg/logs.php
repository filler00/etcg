<?php include 'header.php'; if ( !isset($_GET['id']) || $_GET['id'] == '' ) { ?>

<div class="content col-12 col-sm-12 col-lg-12">
	<h1>Activity Logs</h1>

<?php
	$database = new Database;
	$result = $database->query("SELECT * FROM `tcgs` ORDER BY `name`");
	$count = mysqli_num_rows($result);
	
	if ( $count > 0 ) {
		while ( $row = mysqli_fetch_assoc($result) ) {
			echo '<p>&raquo; <a href="logs.php?id='.$row['id'].'">'.$row['name'].'</a></p>';
		}
	}
	else {
		echo '<p class="text-info">You haven\'t set up any TCGs yet.</p>';
	}
?>

</div>

<?php } else if ( $_GET['id'] != '' ) { 
	
	$id = intval($_GET['id']);
	$database = new Database;
	
	if ( $database->num_rows("SELECT * FROM `tcgs` WHERE `id`='$id'") == 0 ) { echo '<div class="content col-12 col-sm-12 col-lg-12">This TCG does not exist.</div>'; }
	else {

		$tcginfo = $database->get_assoc("SELECT * FROM `tcgs` WHERE `id`='$id' LIMIT 1");
		$altname = strtolower(str_replace(' ','',$tcginfo['name']));
		
		if ( isset($_POST['update']) ) {
			
			$sanitize = new Sanitize;
			
			$logtype = $sanitize->for_db($_POST['logtype']);
			$log = $sanitize->for_db($_POST['log']);
			
			if ( $logtype == 'activity' || $logtype == 'trade' || $logtype == 'activityarch' || $logtype == 'tradearch' ) {
				
				if ( $logtype == 'activity' ) { $result = $database->query("UPDATE `tcgs` SET `activitylog`='$log' WHERE `id`='$id'"); }
				if ( $logtype == 'trade' ) { $result = $database->query("UPDATE `tcgs` SET `tradelog`='$log' WHERE `id`='$id'"); }
				if ( $logtype == 'activityarch' ) { $result = $database->query("UPDATE `tcgs` SET `activitylogarch`='$log' WHERE `id`='$id'"); }
				if ( $logtype == 'tradearch' ) { $result = $database->query("UPDATE `tcgs` SET `tradelogarch`='$log' WHERE `id`='$id'"); }
				
				if ( !$result ) { $error[] = "Could not update the log. ".mysqli_error().""; }
				else { $success[] = "The log has been updated successfully."; }
			
			}
			
		}
		
		if ( isset($_POST['archive']) ) {
			
			$sanitize = new Sanitize;
			
			$logtype = $sanitize->for_db($_POST['logtype']);
			$log = $sanitize->for_db($_POST['log']);
			
			if ( $logtype == 'activity' || $logtype == 'trade' ) {
				
				$result = $database->query("UPDATE `tcgs` SET `".$logtype."log`='' WHERE `id`='$id'");
				if ( !$result ) { $error[] = "Could not truncate the log. ".mysqli_error().""; }
				else {
					$curarch = $database->get_assoc("SELECT `".$logtype."logarch` FROM `tcgs` WHERE `id`='$id' LIMIT 1");
					$curarch = $curarch["$logtype"."logarch"];
					$newlog = "$log\n\n$curarch";
					
					$result = $database->query("UPDATE `tcgs` SET `".$logtype."logarch`='$newlog' WHERE `id`='$id'");
					if ( !$result ) { $error[] = "Could not update the log archives. ".mysqli_error().""; }
					else { $success[] = "The log has been updated successfully."; }
					
				}
			
			}
			
		}
		
		$tcginfo = $database->get_assoc("SELECT * FROM `tcgs` WHERE `id`='$id' LIMIT 1");
		$altname = strtolower(str_replace(' ','',$tcginfo['name']));
		
	?>
	
	<div class="content col-12 col-sm-12 col-lg-12">
	
		<h1>Logs <small><?php echo $tcginfo['name']; ?></small></h1>
		
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
			<input name="logtype" type="hidden" value="<?php if ( $_GET['view'] == 'archives' ) { echo 'activityarch'; } else { echo 'activity'; } ?>">
			<div class="form-group">
				<label for="exampleInputEmail1">Activity Log
				<?php if ( $_GET['view'] != 'archives' ) { ?>
					(<a href="logs.php?id=<?php echo $id; ?>&view=archives">view archived logs</a>)
				<?php } else { ?>
					Archives (<a href="logs.php?id=<?php echo $id; ?>">view current logs</a>)
				<?php } ?>
				</label>
				<textarea name="log" id="log" class="form-control" rows="20"><?php if ( $_GET['view'] == 'archives' ) { echo $tcginfo['activitylogarch']; } else { echo $tcginfo['activitylog']; } ?></textarea>
			</div>
			<div class="btn-group btn-group-justified">
				<div class="btn-group">
					<button name="update" id="update" type="submit" class="btn btn-primary">Update Log</button>
				</div>
				<?php if ( $_GET['view'] != 'archives' ) { ?>
					<div class="btn-group">
						<button name="archive" id="archive" type="submit" class="btn btn-info">Archive</button>
					</div>
				<?php } ?>
			</div>
		</form>
		
		<hr>

		<form action="" method="post" role="form">
			<input name="logtype" type="hidden" value="<?php if ( $_GET['view'] == 'archives' ) { echo 'tradearch'; } else { echo 'trade'; } ?>">
			<div class="form-group">
				<label for="exampleInputEmail1">Trade Log
				<?php if ( $_GET['view'] != 'archives' ) { ?>
					(<a href="logs.php?id=<?php echo $id; ?>&view=archives">view archived logs</a>)
				<?php } else { ?>
					Archives (<a href="logs.php?id=<?php echo $id; ?>">view current logs</a>)
				<?php } ?>
				</label>
				<textarea name="log" id="log" class="form-control" rows="20"><?php if ( $_GET['view'] == 'archives' ) { echo $tcginfo['tradelogarch']; } else { echo $tcginfo['tradelog']; } ?></textarea>
			</div>
			<div class="btn-group btn-group-justified">
				<div class="btn-group">
					<button name="update" id="update" type="submit" class="btn btn-primary">Update Log</button>
				</div>
				<?php if ( $_GET['view'] != 'archives' ) { ?>
					<div class="btn-group">
						<button name="archive" id="archive" type="submit" class="btn btn-info">Archive</button>
					</div>
				<?php } ?>
			</div>
		</form>

	</div>

<?php } } include 'footer.php'; ?>