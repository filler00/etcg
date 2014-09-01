<?php include 'header.php';

$database = new Database;
$upload = new Upload;
function trim_value(&$value) { $value = trim($value); }

if ( isset($_POST['remove']) ) {

	$sanitize = new Sanitize;
	
	function sanitize(&$value) { $sanitize = new Sanitize; $value = $sanitize->for_db($value); }

	$tradeid = intval($_POST['id']);
	$tcgid = intval($_POST['tcgid']);
	$giving = $_POST['tradingcards'];
	$givingcat = $_POST['tradingcat'];
	
	array_walk($giving,'sanitize');
	array_walk($givingcat,'sanitize');
	
	$exists = $database->num_rows("SELECT `trader` FROM `trades` WHERE `id` = '$tradeid'");
	$exists2 = $database->num_rows("SELECT `name` FROM `tcgs` WHERE `id` = '$tcgid'");
	
	if ( $exists == 0 ) { $error[] = 'This trade no longer exists.'; }
	if ( $exists2 == 0 ) { $error[] = 'The TCG does not exist.'; }
	if ( !isset($error) ) {
		
		if ( trim(implode(',',array_filter($giving))) !== '' ) {
			$i = 0;
			foreach ( $giving as $cardgroup ) {
				
				$catinfo = $database->get_assoc("SELECT `cards` FROM `cards` WHERE `tcg`='$tcgid' AND `category`='".$givingcat[$i]."'");
				$catcards = $catinfo['cards'];
				
				if ( $catcards === '' ) { $catcards = $cardgroup; } else { $catcards = ''.$catcards.', '.$cardgroup.''; }
				
				$catcards = explode(',',$catcards);
				array_walk($catcards, 'trim_value');
				
				sort($catcards);
				$catcards = implode(', ',$catcards);
				
				$result = $database->query("UPDATE `cards` SET `cards`='$catcards' WHERE `tcg`='$tcgid' AND `category`='".$givingcat[$i]."'");
				if ( !$result ) { $error[] = "Could not replace cards ($cardgroup) in category '".$givingcat[$i]."'. ".mysqli_error().""; }
				else { $success[] = "Replaced $cardgroup in category ".$givingcat[$i]."."; }
				
				$i++;
				
			}
		}
		
		$result = $database->query("DELETE FROM `trades` WHERE `id` = '$tradeid' LIMIT 1");
		if ( !$result ) { $error[] = "Could not remove the trade entry from the database. ".mysqli_error().""; }
		else { $success[] = "The trade has been removed."; }
			
	}			

}

if ( isset($_POST['update']) || isset($_POST['complete']) ) {
	
	$sanitize = new Sanitize;
	
	function sanitize(&$value) { $sanitize = new Sanitize; $value = trim($value); $value = $sanitize->for_db($value); }

	$tradeid = intval($_POST['id']);
	$tcgid = intval($_POST['tcgid']);
	$trader = $sanitize->for_db($_POST['trader']);
	$email = $sanitize->for_db($_POST['email']);
	$giving = $_POST['tradingcards'];
	$givingcat = $_POST['tradingcat'];
	$receiving = $_POST['receivingcards'];
	$receivingcat = $_POST['receivingcat'];
	
	if ( !is_array($_POST['newtrading']) ) {
		$newgiving = array();
	} else { $newgiving = $_POST['newtrading']; }
	if ( !is_array($_POST['newtradingcat']) ) {
		$newgivingcat = array();
	} else { $newgivingcat = $_POST['newtradingcat']; }
	if ( !is_array($_POST['newreceiving']) ) {
		$newreceiving = array();
	} else { $newreceiving = $_POST['newreceiving']; }
	if ( !is_array($_POST['newreceivingcat']) ) {
		$newreceivingcat = array();
	} else { $newreceivingcat = $_POST['newreceivingcat']; }

	$emailcards = intval($_POST['emailcards']);
	if ( $emailcards === '' ) { $emailcards = '0'; }
	
	array_walk($giving,'sanitize');
	array_walk($givingcat,'sanitize');
	array_walk($receiving,'sanitize');
	array_walk($receivingcat,'sanitize');
	array_walk($newgiving,'sanitize');
	array_walk($newgivingcat,'sanitize');
	array_walk($newreceiving,'sanitize');
	array_walk($newreceivingcat,'sanitize');
	
	$exists = $database->num_rows("SELECT `trader` FROM `trades` WHERE `id` = '$tradeid'");
	$exists2 = $database->num_rows("SELECT `name` FROM `tcgs` WHERE `id` = '$tcgid'");
	
	if ( $emailcards != 1 && $emailcards != 0 ) { $error[] = 'Invalid value for email cards.'; }
	if ( $exists == 0 ) { $error[] = 'This trade no longer exists.'; }
	if ( $exists2 == 0 ) { $error[] = 'The TCG does not exist.'; }
	if ( isset($_POST['complete']) && count(array_filter($receivingcat)) < count(array_filter($receiving)) ) { $error[] = "Please define the category to send your cards to."; }
	if ( !isset($error) ) {
		
		$tcginfo = $database->get_assoc("SELECT * FROM `tcgs` WHERE `id`='$tcgid' LIMIT 1");
		
		if ( !empty($newgiving) ) {
			$i = 0;
			foreach ( $newgiving as $cardgroup ) {
				if ( $cardgroup !== '' && $cardgroup !== 'new field' ) {
				 $giving[] = $cardgroup; 
				 $givingcat[] = $newgivingcat[$i];
				}
				$i++;
			} 
		}
		if ( !empty($newreceiving) ) {
			$i = 0;
			foreach ( $newreceiving as $cardgroup ) {
				if ( $cardgroup !== '' && $cardgroup !== 'new field' ) {
				 $receiving[] = $cardgroup; 
				 $receivingcat[] = $newreceivingcat[$i];
				}
				$i++;
			} 
		}
	
		$i = 0;
		foreach ( $giving as $cardgroup ) {
			if ( $cardgroup === '' ) { unset($givingcat[$i]); }
			$i++;
		}
		
		$giving = array_filter($giving);
		
		$i = 0;
		foreach ( $receiving as $cardgroup ) {
			if ( $cardgroup === '' ) { unset($receivingcat[$i]); }
			$i++;
		}
		
		$receiving = array_filter($receiving);
		
		if ( isset($_POST['complete']) ) {
			
			$i = 0;
			foreach ( $receiving as $cardgroup ) {
				
				if ( !isset($error) ) {
					
					if ( $receivingcat[$i] == 'collecting' ) {
						
						$cardgroup = explode(',',$cardgroup);
						array_walk($cardgroup, 'trim_value');
						
						foreach ( $cardgroup as $card ) {
						
							preg_match('/^([a-z0-9-_]+)([0-9]{2})$/i', $card, $matches);
							$deck = $matches[1];
							
							$exists = $database->num_rows("SELECT `id` FROM `collecting` WHERE `deck`='$deck' AND `tcg`='".$tcginfo['id']."' AND `mastered`='0' LIMIT 1");
							if ( $exists == 0 ) { 
								$result = $database->query("INSERT INTO `collecting` (`tcg`,`deck`,`cards`,`worth`,`count`,`break`,`filler`,`pending`,`puzzle`,`auto`,`uploadurl`) VALUE ('".$tcginfo['id']."','$deck','$card','1','15','5','filler','pending','0','0','default')");
								if ( !$result ) { $error[] = "Failed to add $card to a collecting deck."; }
								else { $success[] = "No existing collecting deck was found matching $card. Created a new collecting deck for $card titled $deck."; }
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
					
						$catinfo = $database->get_assoc("SELECT `format`, `cards`, `auto`, `autourl` FROM `cards` WHERE `tcg`='$tcgid' AND `category`='".$receivingcat[$i]."'");
						$catcards = $catinfo['cards'];
						$catauto = $catinfo['auto'];
						$autourl = $catinfo['autourl'];
						if ( $catinfo['format'] == 'default' ) { $format = $tcginfo['format']; }
						else { $format = $catinfo['format']; }
						
						if ( $catcards === '' ) { $catcards = $cardgroup; } else { $catcards = ''.$catcards.', '.$cardgroup.''; }
						
						$catcards = explode(',',$catcards);
						array_walk($catcards, 'trim_value');
						
						if ( $tcginfo['autoupload'] == 1 && $catauto == 1 ) {
							
							foreach ( $catcards as $card ) {
							
								if ( $autourl == 'default' ) { $defaultauto = $tcginfo['defaultauto']; }
								else { $defaultauto = $autourl; }
									
								$upsuccess = $upload->card($tcginfo,$catinfo,'cards',$card);
									
								if ( $upsuccess === false ) { $error[] = "Failed to upload $card.$format from $defaultauto"; }
								else if ( $upsuccess === true ) { $success[] = "Uploaded $card.$format."; }
							}
						}
						
						sort($catcards);
						$catcards = implode(', ',$catcards);
						
						$result = $database->query("UPDATE `cards` SET `cards`='$catcards' WHERE `tcg`='$tcgid' AND `category`='".$receivingcat[$i]."'");
						if ( !$result ) { $error[] = "Could not add cards in category '".$receivingcat[$i]."'. ".mysqli_error().""; }
					
					}
					
				}
				
				$i++;
				
			}
			
			//build list of card image links for email
			if ( $emailcards == 1 ) {
				$i = 0;
				
				foreach ( $giving as $cardgroup ) {
					
					$cardgroup = explode(',',$cardgroup);
					array_walk($cardgroup, 'trim_value');
					
					foreach ( $cardgroup as $card ) {
						
						preg_match('/^([a-z0-9-_]+)([0-9]{2})$/i', $card, $matches);
						$deck = $matches[1];
						
						if ( $givingcat[$i] == 'collecting' ) {
							if ( $database->num_rows("SELECT `format` FROM `collecting` WHERE `deck`='$deck' AND `tcg`='".$tcginfo['id']."' AND `mastered`='0' LIMIT 1") == 0 ) {
								$catinfo = '';
							}
							else {
								$catinfo = $database->get_assoc("SELECT `format` FROM `collecting` WHERE `deck`='$deck' AND `tcg`='".$tcginfo['id']."' AND `mastered`='0' LIMIT 1");
							}
						} else {
							$catinfo = $database->get_assoc("SELECT `format` FROM `cards` WHERE `tcg`='$tcgid' AND `category`='".$givingcat[$i]."'");
						}
						
						if ( $catinfo == '' || $catinfo['format'] == 'default' ) { $format = $tcginfo['format']; }
						else { $format = $catinfo['format']; }
						
						$givinglinks .= "\n".$tcginfo['cardsurl'].''.$card.'.'.$format."";
						
					}
					
					$i++;
				}
			}
					
			$giving = implode(', ',$giving);
			$receiving = implode(', ',$receiving);
			$today = date("Y-m-d");
			
			$log = "- Traded $trader: my $giving for $receiving";
			
			$dateformat = $database->get_assoc("SELECT `value` FROM `settings` WHERE `setting`='dateformat'");
			$dateformat = $dateformat['value'];
			$dateheaderformat = $database->get_assoc("SELECT `value` FROM `settings` WHERE `setting`='dateheaderformat'");
			$dateheaderformat = $dateheaderformat['value'];
			
			$date = date("$dateformat");
			$logdate = $sanitize->for_db(str_replace('[DATE]',$date,$dateheaderformat));
			
			$oldlog = $sanitize->for_db($tcginfo['tradelog']);
				
			if ( strpos($oldlog,$logdate) !== false ) { $tradelog = str_replace($logdate,"$logdate\n$log",$oldlog); }
			else { $tradelog = "$logdate\n$log\n\n".$oldlog.""; }
			
			$result = $database->query("UPDATE `tcgs` SET `tradelog`='$tradelog', `lastupdated`='$today' WHERE `id`='$tcgid' LIMIT 1");
			if ( !$result ) { $error[] = "Failed to update the trade log. ".mysqli_error().""; }
			else {
				
				$success[] = "Trade log updated.";
				
				if ( $emailcards == 1 ) {

					$username = $database->get_assoc("SELECT `value` FROM `settings` WHERE `setting` = 'username'");
					$username = $username['value'];
					$useremail = $database->get_assoc("SELECT `value` FROM `settings` WHERE `setting` = 'email'");
					$useremail = $useremail['value'];
					$usermessage = $database->get_assoc("SELECT `value` FROM `settings` WHERE `setting` = 'emailmessage'");
					$usermessage = $usermessage['value'];
					$tpost = $database->get_assoc("SELECT `value` FROM `settings` WHERE `setting` = 'url'");
					$tpost = $tpost['value'];
					
					$message = "$usermessage \n\nTrade Overview: Your $receiving for my $giving \n\nYour Cards: $givinglinks \n\n- ".$username." \n$tpost";
					$headers = "From: $username \r\n";
					$headers.= "Reply-To: $useremail";
					if ( !mail($email,''.$tcginfo['name'].' Trade',$message,$headers) ) { $error[] = "Could not email the trader"; } else { $success[] = "Notification email sent to $trader with cards included.";  }
				}
				
				if ( !isset($error) ) { 
					
					$result = $database->query("DELETE FROM `trades` WHERE `id` = '$tradeid' LIMIT 1");
					if ( !$result ) { $error[] = "Could not remove the trade from the database. ".mysqli_error().""; }
					else { $success[] = "The trade has been completed and removed."; }
				
				}
			
			}
			
		}
		
		else if ( isset($_POST['update']) ) {
			
			$giving = implode('; ',$giving);
			$givingcat = implode(', ',$givingcat);
			$receiving = implode('; ',$receiving);
			$receivingcat = implode(', ',$receivingcat);

			$result = $database->query("UPDATE `trades` SET `giving`='$giving', `givingcat`='$givingcat', `receiving`='$receiving', `receivingcat`='$receivingcat', `emailcards`='$emailcards' WHERE `id`='$tradeid' LIMIT 1");
			if ( !$result ) { $error[] = "Failed to update trade settings. ".mysqli_error().""; }
			else { $success[] = "The trade has been updated."; }
			
		}
		
	}

} 

$counttrades = 0;
?>

<div class="content col-12 col-sm-12 col-lg-12">
	<div class="clearfix">
		<h1 class="pull-left">Trades</h1>
		
		<div class="row pull-right trades-nav-controls">
			<div class="col-xs-5">
				<select class="form-control" id="trades-type-sel">
					<option selected>All Trades</option>
					<option>Incoming</option>
					<option>Outgoing</option>
				</select>
			</div>
			<div class="col-xs-5">
				<select class="form-control" id="trades-tcg-sel">
					<option <?php if ( !isset($_GET['id']) ) { echo 'selected'; }  ?>>All TCGs</option>
					<?php $result = $database->query("SELECT * FROM `tcgs` ORDER BY `name`");
					while ( $row = mysqli_fetch_assoc($result) ) {?>
						<option data-tcg-id="<?php echo $row['id']; ?>" <?php if ( intval($_GET['id']) == $row['id'] ) { echo 'selected'; }  ?>><?php echo $row['name']; ?></option>
					<?php } ?>
				</select>
			</div>
			<div class="col-xs-1">
				<a href="newtrade.php" type="button" class="btn btn-primary new-trade-btn"><i data-toggle="tooltip" data-placement="top" title="New Trade" class="fa fa-plus"></i></a>
			</div>
		</div>
	</div>
	
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

	<div class="panel-group" id="trades-panel">

<?php $result = $database->query("SELECT * FROM `tcgs` ORDER BY `name`");
while ( $tcginfo = mysqli_fetch_assoc($result) ) {
	
	unset($categories);
	$cats = $database->query("SELECT `category` FROM `cards` WHERE `tcg`='".$tcginfo['id']."'"); 
	$i = 0;
	while ( $row = mysqli_fetch_assoc($cats) ) {
		$categories[] = $row['category'];
		$i++;
	}
	
	sort($categories);
	$gcategories = $categories;
	array_unshift($categories,'collecting');
	$rcategories = $categories; ?>
		
		<?php			
		$resultt = $database->query("SELECT * FROM `trades` WHERE `tcg`='".$tcginfo['id']."' ORDER BY `date` DESC");
		while ( $row = mysqli_fetch_assoc($resultt) ) { ?>
			
			<?php // Build menu options for new category select
						$menuoptions = "";
						foreach ( $gcategories as $category ) {
							$menuoptions .= '<option value="'.trim($category).'">'.trim($category).'</option>';
						}
						$menuoptions = str_replace('"','\"',$menuoptions);
			?>
			
				<form action="" method="post">
				<input name="id" type="hidden" id="id" value="<?php echo $row['id'] ?>" />
				<input name="tcgid" type="hidden" id="tcgid" value="<?php echo $row['tcg'] ?>" />
				<input name="trader" type="hidden" id="trader" value="<?php echo $row['trader'] ?>" />
				<input name="email" type="hidden" id="email" value="<?php echo $row['email'] ?>" />
				<div class="panel panel-default" data-trade-type="<?php echo $row['type'] ?>" data-trade-tcg="<?php echo $tcginfo['name'] ?>">
					<div class="panel-heading" data-toggle="collapse" data-parent="#trades-panel" data-target="#collapse<?php echo $counttrades; ?>"> <!-- Trade Head -->
						<span class="panel-title clearfix">
							 <i class="fa fa-envelope"></i> &nbsp; 
							 <span class="label label-primary"><?php echo $tcginfo['name'] ?></span> &nbsp; 
							 <strong><em><?php echo date('F d, Y', strtotime($row['date'])); ?></em></strong>
							 <span class="pull-right">
								Sent <?php if ( $row['type'] == 'outgoing' ) { echo 'to'; } else { echo 'from'; } ?> <a href="mailto:<?php echo $row['email']; ?>"><strong><?php echo $row['trader']; ?></strong></a> &nbsp; 
								<i class="fa fa-chevron-down"></i>
							</span>
						</span>
					</div>
					<div id="collapse<?php echo $counttrades; ?>" class="panel-collapse collapse"> <!-- Trade Body -->
						<div class="panel-body">
							<table class="table">
								<thead>
									<tr>
										<th class="clearfix">
											<i class="fa fa-arrow-circle-o-up"></i> Trading Cards
											<button type="button" class="btn btn-primary btn-xs pull-right btn-new-trading" data-toggle="tooltip" data-placement="top" title="Add another category"><i class="fa fa-plus"></i></button>
										</th>
										<th class="clearfix">
											<i class="fa fa-arrow-circle-o-down"></i> Receiving Cards
											<button type="button" class="btn btn-primary btn-xs pull-right btn-new-receiving" data-toggle="tooltip" data-placement="top" title="Add another category"><i class="fa fa-plus"></i></button>
										</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td class="td-trading-cats"> <!-- Trading Card Categories -->
											<?php $tradingcards = explode(';',$row['giving']); $categ = explode(',',$row['givingcat']); $i = 0;
											foreach ($tradingcards as $cardgroup) { ?>	
												<div class="clearfix">
													<div class="col-xs-8">
														<input name="tradingcards[]" id="tradingcards[]" type="text" class="form-control" placeholder="card01, card02, card03" value="<?php echo trim($cardgroup); ?>">
													</div>
													<div class="col-xs-4">
														<select class="form-control" name="tradingcat[]" id="tradingcat[]">
															<?php foreach ( $gcategories as $category ) { ?><option value="<?php echo trim($category); ?>" <?php if ( trim($categ[$i]) == trim($category) ) { echo 'selected="selected"'; } ?>><?php echo trim($category); ?></option><?php } ?>
														</select>
													</div>
												</div>
											<?php $i++; } ?>
										</td>
										<td class="td-receiving-cats"> <!-- Receiving Card Categories -->
											<?php $receivingcards = explode(';',$row['receiving']); $categ = explode(',',$row['receivingcat']); $i = 0;
											foreach ($receivingcards as $cardgroup) { ?>		
												<div class="clearfix">
													<div class="col-xs-8">
														<input name="receivingcards[]" id="receivingcards[]" type="text" class="form-control" placeholder="card01, card02, card03" value="<?php echo trim($cardgroup); ?>">
													</div>
													<div class="col-xs-4">
														<select class="form-control" name="receivingcat[]" id="receivingcat[]">
															<option value="">--</option>
															<?php foreach ( $rcategories as $category ) { ?><option value="<?php echo trim($category); ?>" <?php if ( trim($categ[$i]) == trim($category) ) { echo 'selected="selected"'; } ?>><?php echo trim($category); ?></option><?php } ?>
														</select>
													</div>
												</div>
											<?php $i++; } ?>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="panel-footer clearfix"> <!-- Trade Settings & Controls -->
							<?php if ( $row['email'] !== '' ) { ?>
								<div class="checkbox-inline" data-toggle="tooltip" data-placement="top" title="Send an email with a trade summary and cards when the trade is completed.">
									<label>
										<input type="checkbox" name="emailcards" id="emailcards" value="1" <?php if ( $row['emailcards'] === '1' ) { echo 'checked="checked"'; } ?>>
										<i class="fa fa-share"></i> Email Cards
									</label>
								</div>
							<?php } ?>
							<div class="btn-group pull-right">
								<!--<button type="button" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Edit Trade Settings"><i class="fa fa-cogs"></i></button>-->
								<button name="update" id="update" type="submit" class="btn btn-primary btn-sm">Update Trade</button>
								<button name="complete" id="complete" type="submit" class="btn btn-success btn-sm">Complete Trade</button>
								<button name="remove" id="remove" type="submit" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Remove"><i class="fa fa-times"></i></button>
							</div>
						</div>
					</div>
				</div>
			</form>
				
<?php	
			$counttrades++;
		
		}

}

?>
	</div>

</div>

<?php include 'footer.php'; ?>
