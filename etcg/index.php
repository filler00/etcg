<?php include 'header.php'; if ( $_GET['action'] === 'deletetcg' && isset($_GET['id']) ) {
	
	$tcgid = intval($_GET['id']);
	if ( $database->num_rows("SELECT * FROM `tcgs` WHERE `id`='$tcgid'") == 0 ) { $error[] = "The TCG no longer exists."; }
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

// GitHub API
$options = array('http' => array('user_agent'=> $_SERVER['HTTP_USER_AGENT']));
$context = stream_context_create($options);
$response = file_get_contents('https://api.github.com/repos/tooblue/etcg/releases', false, $context);
$releases = json_decode($response, true);

?>

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

<div class="content col-12 col-sm-12 col-lg-12">
	<h1>Dashboard</h2>
	
	<div class="row row-notices clearfix">
		<div class="col-md-7 panel panel-primary">
			<div class="panel-body">
				<h2><i class="fa fa-exclamation-triangle"></i> Latest eTCG Releases</h2>
				<?php $i = 0; foreach( $releases as $release ) { if ( $i > 4 ) { break; } ?>
					<p><strong><a href="<?php echo $release['html_url']; ?>" alt=""><?php echo $release['tag_name']; ?>: <?php echo $release['name']; ?></a></strong> @ <?php echo date("F j, Y, g:i a", strtotime($release['published_at'])); ?></p>
				<?php $i++; } ?>
			</div>
		</div>
		
		<div class="col-md-4 panel panel-default">
			<div class="panel-body">
				<h2><i class="fa fa-question-circle"></i> Need Help?</h2>
				<ul>
					<li><a href="https://github.com/tooblue/etcg/wiki" alt="">Documentation</a></li>
					<li><a href="https://github.com/tooblue/etcg/issues" alt="">Report Issues</a></li>
					<li><a href="http://filler00.com/forum/viewforum.php?f=13" alt="">Support Forums</a></li>
					<li><a href="http://filler00.com/forum/viewforum.php?f=16">Script Mods</a></li>
				</ul>
			</div>
		</div>
	</div>
	
	<table class="table table-striped">
		<thead>
			<tr>
				<th>TCG</th>
				<th>URL</th>
				<th>Status</th>
				<th>Updated</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$database = new Database;
				$result = $database->query("SELECT * FROM `tcgs` ORDER BY `name`");
				$count = mysqli_num_rows($result);
				
				if ( $count > 0 ) {
					$i = 0;
					while ( $row = mysqli_fetch_assoc($result) ) { $i++; ?>
						<tr <?php if ( $row['status'] == 'inactive' ) { echo 'class="danger"'; } else if ( $row['status'] == 'hiatus' ) { echo 'class="warning"'; } ?>>
							<td><strong><a href="cards.php?id=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></strong></td>
							<td><a href="'.$row['url'].'" target="_blank"><?php echo $row['url']; ?></a></td>
							<td><em><?php echo $row['status']; ?></em></td>
							<td><?php echo date('F j, Y', strtotime($row['lastupdated'])); ?></td>
						</tr>
					<?php }
				}
				else {
					echo '<tr><td align="center" colspan="4">No active TCGs found.</td></tr>';
				}
			?>
		</tbody>
	</table>
</div><!--/span-->

<?php include 'footer.php'; ?>