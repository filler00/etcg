<?php include 'header.php'; if ( $_GET['action'] === 'deletetcg' && isset($_GET['id']) ) {
	
	$tcgid = intval($_GET['id']);
	if ( $database->num_rows("SELECT * FROM `tcgs` WHERE `id`='$tcgid'") == 0 ) { $error = "The TCG no longer exists."; }
	else {
	
		$result = $database->query("DELETE FROM `tcgs` WHERE `id` = '$tcgid' LIMIT 1");
		if ( !$result ) { $error[] = "Could not remove the TCG entry in tcgs table."; }
		else {
			
			$tables = array(additional, cards, collecting, trades);
			
			foreach ( $tables as $table ) {
				$result = $database->query("DELETE FROM `$table` WHERE `tcg` = '$tcgid'");
				if ( !$result ) { $error[] = "Could not remove the TCG's entry in tcgs table."; }
			}
		
		}
		
		if ( !isset($error) ) {
				if ( $tcgid == $_SESSION['currTCG'] ) { $_SESSION['currTCG'] = ""; }
				$success[] = "The TCG and all related items have been removed."; 
		}
	
	}

}

?>
           
<h1>Dashboard</h1>
<p>Welcome to <em>EasyTCG FM</em>!</p>

<?php if ( isset($error) ) { foreach ( $error as $msg ) {  ?><div class="errors"><strong>ERROR!</strong> <?php echo $msg; ?></div><?php } } ?>
<?php if ( isset($success) ) { foreach ( $success as $msg ) { ?><div class="success"><strong>SUCCESS!</strong> <?php echo $msg; ?></div><?php } } ?>

<br />
<table width="100%" cellpadding="5" cellspacing="3" class="style1" align="center">
    <tr>
        <td class="top" colspan="4">Your Joined TCGs</td>
    </tr>
    <tr class="xdark">
        <td width="20%" align="center">TCG Name</td><td width="40%" align="center">URL</td><td width="15%" align="center">Status</td><td width="25%" align="center">Last Update</td>
    </tr>
    <?php
        $database = new Database;
        $result = $database->query("SELECT * FROM `tcgs` ORDER BY `name`");
        $count = mysql_num_rows($result);
		
		if ( $count > 0 ) {
			$i = 0;
			while ( $row = mysql_fetch_assoc($result) ) {
				$i++;
				if ( $i % 2 == 0 ) { $class = 'xlight'; } else { $class = 'light'; }
				$tradecountt = $database->num_rows("SELECT `id` FROM `trades` WHERE `tcg`='".$row['id']."'");
				echo '<tr class="'.$class.'" id="linked" onclick="window.location.href = \'cards.php?id='.$row['id'].'\'">' .
				'<td><strong>'.$row['name'].'</strong></td>' .
				'<td><a href="'.$row['url'].'" target="_blank">'.$row['url'].'</a></td>' .
				'<td align="center"><em>'.$row['status'].'</em></td>' .
				'<td align="center">'.date('F j, Y',strtotime($row['lastupdated'])).'</td>';
			}
		}
		else {
			echo '<tr class="xlight"><td align="center" colspan="4">No active TCGs found.</td>';
		}
    ?>
    </tr> 
</table>
            
<?php include 'footer.php'; ?>