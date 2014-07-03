<?php include 'header.php'; if ( !isset($_GET['id']) || $_GET['id'] == '' ) { ?>
           
<h1>Activity Logs</h1>
<br />
<?php
	$database = new Database;
	$result = $database->query("SELECT * FROM `tcgs` ORDER BY `name`");
	$count = mysql_num_rows($result);
	
	if ( $count > 0 ) {
		while ( $row = mysql_fetch_assoc($result) ) {
			echo '&raquo; <a href="logs.php?id='.$row['id'].'">'.$row['name'].'</a><br />';
		}
	}
	else {
		echo 'You haven\'t set up any TCGs yet.';
	}
?>

<?php } else if ( $_GET['id'] != '' ) { 
	
	$id = intval($_GET['id']);
	$database = new Database;
	
	if ( $database->num_rows("SELECT * FROM `tcgs` WHERE `id`='$id'") == 0 ) { echo "This TCG does not exist."; }
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
				
				if ( !$result ) { $error[] = "Could not update the log. ".mysql_error().""; }
				else { $success[] = "The log has been updated successfully."; }
			
			}
			
		}
		
		if ( isset($_POST['archive']) ) {
			
			$sanitize = new Sanitize;
			
			$logtype = $sanitize->for_db($_POST['logtype']);
			$log = $sanitize->for_db($_POST['log']);
			
			if ( $logtype == 'activity' || $logtype == 'trade' ) {
				
				$result = $database->query("UPDATE `tcgs` SET `".$logtype."log`='' WHERE `id`='$id'");
				if ( !$result ) { $error[] = "Could not truncate the log. ".mysql_error().""; }
				else {
					$curarch = $database->get_assoc("SELECT `".$logtype."logarch` FROM `tcgs` WHERE `id`='$id' LIMIT 1");
					$curarch = $curarch["$logtypelogarch"];
					$newlog = "$log\n\n$curarch";
					
					$result = $database->query("UPDATE `tcgs` SET `".$logtype."logarch`='$newlog' WHERE `id`='$id'");
					if ( !$result ) { $error[] = "Could not update the log archives. ".mysql_error().""; }
					else { $success[] = "The log has been updated successfully."; }
					
				}
			
			}
			
		}
		
		$tcginfo = $database->get_assoc("SELECT * FROM `tcgs` WHERE `id`='$id' LIMIT 1");
		$altname = strtolower(str_replace(' ','',$tcginfo['name']));
		
	?>
	
	<h1>Logs: <?php echo $tcginfo['name']; ?></h1>
	
	<?php if ( isset($error) ) { foreach ( $error as $msg ) {  ?><div class="errors"><strong>ERROR!</strong> <?php echo $msg; ?></div><?php } } ?>
	<?php if ( isset($success) ) { foreach ( $success as $msg ) { ?><div class="success"><strong>SUCCESS!</strong> <?php echo $msg; ?></div><?php } } ?>
	
	<br />
	<form action="" method="post">
	<input name="logtype" type="hidden" value="<?php if ( $_GET['view'] == 'archives' ) { echo 'activityarch'; } else { echo 'activity'; } ?>">
	<table class="style1" width="100%" align="center" cellpadding="5" cellspacing="5">
		<tr>
			<td class="top">Activity Log <?php if ( $_GET['view'] != 'archives' ) { echo '(<a href="logs.php?id='.$id.'&view=archives">view archived logs</a>)'; } else { echo 'Archives (<a href="logs.php?id='.$id.'">view current logs</a>)'; } ?></td>
		</tr><tr class="xlight">
			<td><textarea name="log" cols="130" rows="15" id="log" style="font-size: 12px; font-family: 'Trebuchet MS', Arial, Helvetica, sans-serif;"><?php if ( $_GET['view'] == 'archives' ) { echo $tcginfo['activitylogarch']; } else { echo $tcginfo['activitylog']; } ?></textarea></td>
		</tr>
		<tr>
			<td align="right" class="xdark"><input name="update" type="submit" id="update" value="Update Log"> <?php if ( $_GET['view'] != 'archives' ) { echo '<input name="archive" type="submit" id="archive" value="Archive">'; } ?> <input name="reset" type="reset" id="reset" value="Reset"></td>
		</tr>
	</table>
	</form>
	
	<br />
	<form action="" method="post">
	<input name="logtype" type="hidden" value="<?php if ( $_GET['view'] == 'archives' ) { echo 'tradearch'; } else { echo 'trade'; } ?>">
	<table class="style1" width="100%" align="center" cellpadding="5" cellspacing="5">
		<tr>
			<td class="top">Trade Log <?php if ( $_GET['view'] != 'archives' ) { echo '(<a href="logs.php?id='.$id.'&view=archives">view archived logs</a>)'; } else { echo 'Archives (<a href="logs.php?id='.$id.'">view current logs</a>)'; } ?></td>
		</tr><tr class="xlight">
			<td><textarea name="log" cols="130" rows="15" id="log" style="font-size: 12px; font-family: 'Trebuchet MS', Arial, Helvetica, sans-serif;"><?php if ( $_GET['view'] == 'archives' ) { echo $tcginfo['tradelogarch']; } else { echo $tcginfo['tradelog']; } ?></textarea></td>
		</tr>
		<tr>
			<td align="right" class="xdark"><input name="update" type="submit" id="update" value="Update Log"> <?php if ( $_GET['view'] != 'archives' ) { echo '<input name="archive" type="submit" id="archive" value="Archive">'; } ?> <input name="reset" type="reset" id="reset" value="Reset"></td>
		</tr>
	</table>
	</form>

<?php } } include 'footer.php'; ?>