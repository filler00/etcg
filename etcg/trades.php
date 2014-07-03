<?php include 'header.php';

$database = new Database;
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
				if ( !$result ) { $error[] = "Could not replace cards ($cardgroup) in category '".$givingcat[$i]."'. ".mysql_error().""; }
				else { $success[] = "Replaced $cardgroup in category ".$givingcat[$i]."."; }
				
				$i++;
				
			}
		}
		
		$result = $database->query("DELETE FROM `trades` WHERE `id` = '$tradeid' LIMIT 1");
		if ( !$result ) { $error[] = "Could not remove the trade entry from the database. ".mysql_error().""; }
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
						
							$deck = preg_replace('/[^a-z_-]{2,3}$/i', '', $card);
							$exists = $database->num_rows("SELECT `id` FROM `collecting` WHERE `deck` LIKE '%$deck%' AND `tcg`='".$tcginfo['id']."' AND `mastered`='0' LIMIT 1");
							if ( $exists == 0 ) { 
								$result = $database->query("INSERT INTO `collecting` (`tcg`,`deck`,`cards`,`worth`,`count`,`break`,`filler`,`pending`,`puzzle`,`auto`,`uploadurl`) VALUE ('".$tcginfo['id']."','$deck','$card','1','15','5','filler','pending','0','0','default')");
								if ( !$result ) { $error[] = "Failed to add $card to a collecting deck."; }
								else { $success[] = "No existing collecting deck was found matching $card. Created a new collecting deck for $card titled $deck."; }
							}
							else {
								$collectinginfo = $database->get_assoc("SELECT * FROM `collecting` WHERE `deck` LIKE '%$deck%' AND `tcg`='".$tcginfo['id']."' AND `mastered`='0' LIMIT 1");
								$collectingid = $collectinginfo['id'];
								$collectingcards = $collectinginfo['cards'];
								$collectingauto = $collectinginfo['auto'];
								$autourl = $collectinginfo['uploadurl'];
								if ( $collectingcards === '' ) { $collectingcards = $card; } else { $collectingcards = "$collectingcards, $card"; }
								$result = $database->query("UPDATE `collecting` SET `cards`='$collectingcards' WHERE `id`='$collectingid' LIMIT 1");
								if ( !$result ) { $error[] = "Failed to add $card to a collecting deck."; }
								else { $success[] = "Added $card to the $deck collecting deck."; }
								
								if ( $tcginfo['autoupload'] == 1 && $collectingauto == 1 ) {

									$filename = ''.$tcginfo['cardspath'].''.$card.'.'.$tcginfo['format'].'';
								
									if ( !file_exists($filename) ) {
										
										if ( $autourl == 'default' ) { $defaultauto = $tcginfo['defaultauto']; }
										else { $defaultauto = $autourl; }
										$imgurl = ''.$defaultauto.''.$card.'.'.$tcginfo['format'].'';
										
										if ( !$img = file_get_contents($imgurl) ) { $error[] = "Couldn't find the file named $card.$format at $defaultauto"; }
										else {
											if ( !file_put_contents($filename,$img) ) { $error[] = "Failed to upload $filename"; }	
											else { $success[] = "Uploaded $card.".$tcginfo['format']."."; }
										}
										
									}
											
								}
								
							}
							
						}
					
					}
					
					else {
					
						$catinfo = $database->get_assoc("SELECT `cards`, `auto`, `autourl` FROM `cards` WHERE `tcg`='$tcgid' AND `category`='".$receivingcat[$i]."'");
						$catcards = $catinfo['cards'];
						$catauto = $catinfo['auto'];
						$autourl = $catinfo['autourl'];
						
						if ( $catcards === '' ) { $catcards = $cardgroup; } else { $catcards = ''.$catcards.', '.$cardgroup.''; }
						
						$catcards = explode(',',$catcards);
						array_walk($catcards, 'trim_value');
						
						if ( $tcginfo['autoupload'] == 1 && $catauto == 1 ) {
							foreach ( $catcards as $card ) {
								if ( !isset($error) ) {
									$filename = ''.$tcginfo['cardspath'].''.$card.'.'.$tcginfo['format'].'';
								
									if ( !file_exists($filename) ) {
										
										if ( $autourl == 'default' ) { $defaultauto = $tcginfo['defaultauto']; }
										else { $defaultauto = $autourl; }
										$imgurl = ''.$defaultauto.''.$card.'.'.$tcginfo['format'].'';
										
										if ( !$img = file_get_contents($imgurl) ) { $error[] = "Couldn't find the file named $card.$format at $defaultauto"; }
										else {
											if ( !file_put_contents($filename,$img) ) { $error[] = "Failed to upload $filename."; }	
											else { $success[] = "Uploaded $card.".$tcginfo['format']."."; }
										}
										
									}
								
								}
							}
						}
						
						sort($catcards);
						$catcards = implode(', ',$catcards);
						
						$result = $database->query("UPDATE `cards` SET `cards`='$catcards' WHERE `tcg`='$tcgid' AND `category`='".$receivingcat[$i]."'");
						if ( !$result ) { $error[] = "Could not add cards in category '".$receivingcat[$i]."'. ".mysql_error().""; }
					
					}
					
				}
				
				$i++;
				
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
			$logdate = str_replace('[DATE]',$date,$dateheaderformat);
			
			$oldlog = mysql_real_escape_string($tcginfo['tradelog']);
				
			if ( strpos($oldlog,$logdate) !== false ) { $tradelog = str_replace($logdate,"$logdate\n$log",$oldlog); }
			else { $tradelog = "$logdate\n$log\n\n".$oldlog.""; }
			
			$result = $database->query("UPDATE `tcgs` SET `tradelog`='$tradelog', `lastupdated`='$today' WHERE `id`='$tcgid' LIMIT 1");
			if ( !$result ) { $error[] = "Failed to update the trade log. ".mysql_error().""; }
			else {
				
				$success[] = "Trade log updated.";
				
				if ( $emailcards == 1 ) {
					$giving = explode(', ',$giving);
					foreach ( $giving as $card ) {
						$givinglinks .= "\n".$tcginfo['cardsurl'].''.$card.'.'.$tcginfo['format']."";
					}
					$giving = implode(', ',$giving);
					
					$username = $database->get_assoc("SELECT `value` FROM `settings` WHERE `setting` = 'username'");
					$username = $username['value'];
					$useremail = $database->get_assoc("SELECT `value` FROM `settings` WHERE `setting` = 'email'");
					$useremail = $useremail['value'];
					$usermessage = $database->get_assoc("SELECT `value` FROM `settings` WHERE `setting` = 'emailmessage'");
					$usermessage = $usermessage['value'];
					$tpost = $database->get_assoc("SELECT `value` FROM `settings` WHERE `setting` = 'url'");
					$tpost = $tpost['value'];
					
					$message = "$usermessage \n\nTrade Overview: Your $receiving for my $giving \n\nYour Cards: $givinglinks \n\n- ".$username." \n$tpost";
					$headers = "From: ".$username." <$useremail> \r\n";
					$headers.= "Reply-To: $useremail";
					if ( !mail($email,''.$tcginfo['name'].' Trade',$message,$headers) ) { $error[] = "Could not email the trader"; } else { $success[] = "Notification email sent to $trader with cards included.";  }
				}
				
				if ( !isset($error) ) { 
					
					$result = $database->query("DELETE FROM `trades` WHERE `id` = '$tradeid' LIMIT 1");
					if ( !$result ) { $error[] = "Could not remove the trade from the database. ".mysql_error().""; }
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
			if ( !$result ) { $error[] = "Failed to update trade settings. ".mysql_error().""; }
			else { $success[] = "The trade has been updated."; }
			
		}
		
	}

} 

$newcounter = 0; // initialize variable to count new field containers
$counttrades = 0;
?>

<script type="text/javascript">

function addElement(newCounter,tradeType,menuOptions) {
  var ni = document.getElementById('myDiv'+newCounter);
  //var numi = document.getElementById('theValue');
  //var num = (document.getElementById('theValue').value -1)+ 2;
  //numi.value = num;
  var newdiv = document.createElement('div');
  var divIdName = 'myNewDiv' + newCounter;
  newdiv.setAttribute('id',divIdName);
  if ( tradeType == 'trading' ) {
  	newdiv.innerHTML = '<input name="newtrading[]" type="text" id="newtrading[]" value="new field" size="40" style="font-size:10px;" onFocus="if (this.value==\'new field\') this.value=\'\';" onBlur="if (this.value==\'\') this.value=\'new field\';" /> » <select name="newtradingcat[]" id="newtradingcat[]">' +menuOptions+ '</select>';
  } else {
  	newdiv.innerHTML = '<input name="newreceiving[]" type="text" id="newreceiving[]" value="new field" size="40" style="font-size:10px;" onFocus="if (this.value==\'new field\') this.value=\'\';" onBlur="if (this.value==\'\') this.value=\'new field\';" /> » <select name="newreceivingcat[]" id="newreceivingcat[]">' +menuOptions+ '</select>';
  }
  ni.appendChild(newdiv);
  console.log(menuOptions);
}

var menuOptions = new Array();
</script>

	<?php if ( isset($error) ) { foreach ( $error as $msg ) {  ?><div class="errors"><strong>ERROR!</strong> <?php echo $msg; ?></div><?php } } ?>
	<?php if ( isset($success) ) { foreach ( $success as $msg ) { ?><div class="success"><strong>SUCCESS!</strong> <?php echo $msg; ?></div><?php } } ?>

<?php if ( isset($_GET['id']) && $database->num_rows("SELECT * FROM `tcgs` WHERE `id`='".intval($_GET['id'])."'") != 0 ) { $result = $database->query("SELECT * FROM `tcgs` WHERE `id`='".intval($_GET['id'])."'"); }
else { $result = $database->query("SELECT * FROM `tcgs` WHERE `status` = 'active' ORDER BY `id`"); }
while ( $tcginfo = mysql_fetch_assoc($result) ) {
	
	unset($categories);
	$cats = $database->query("SELECT `category` FROM `cards` WHERE `tcg`='".$tcginfo['id']."'"); 
	$i = 0;
	while ( $row = mysql_fetch_assoc($cats) ) {
		$categories[] = $row['category'];
		$i++;
	}
	
	sort($categories);
	$gcategories = $categories;
	array_unshift($categories,'collecting');
	$rcategories = $categories;
	
	$tradecount = $database->num_rows("SELECT * FROM `trades` WHERE `tcg` = '".$tcginfo['id']."'");
	if ( $tradecount > 0 ) {
	
		echo '<h1>'.$tcginfo['name'].'</h1>';
		echo '<br /><div id="tradesNav"><div id="'.$tcginfo['id'].'incomingTab" onclick="navTrades(\''.$tcginfo['id'].'incoming\',\''.$tcginfo['id'].'outgoing\')" class="tradesNavSel">Inbox ('.$database->num_rows("SELECT * FROM `trades` WHERE `tcg` = '".$tcginfo['id']."' AND `type` = 'incoming'").')</div><div id="'.$tcginfo['id'].'outgoingTab" onclick="navTrades(\''.$tcginfo['id'].'outgoing\',\''.$tcginfo['id'].'incoming\')" class="tradesNavDesel">Outbox ('.$database->num_rows("SELECT * FROM `trades` WHERE `tcg` = '".$tcginfo['id']."' AND `type` = 'outgoing'").')</div><a class="tradesNavDesel" href="newtrade.php?id='.$tcginfo['id'].'">New Trade</a></div>';
		echo '<div id="tradesContainer">';
		
		$tradingcateg = array(outgoing, incoming);
		foreach ( $tradingcateg as $tcateg ) {
			
			if ( $tcateg == 'incoming' ) { $displayNone = ''; }
			else { $displayNone = 'style="display:none;"'; }
			
			echo '<div id="'.$tcginfo['id'].''.$tcateg.'" '.$displayNone.'>';
			
			$resultt = $database->query("SELECT * FROM `trades` WHERE `tcg`='".$tcginfo['id']."' AND `type` = '".$tcateg."' ORDER BY `date` DESC");
			while ( $row = mysql_fetch_assoc($resultt) ) { ?>
				
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
				<table align="center" width="100%" cellpadding="5" cellspacing="0" class="style1" id="trade<?php echo $counttrades; ?>">
					<tr class="linked" id="collapseTrigger<?php echo $counttrades; ?>" onclick="collapseTrades('trade<?php echo $counttrades; ?>','collapseTrigger<?php echo $counttrades; ?>');">
						<td class="top" colspan="2"><em><?php echo date('F d, Y', strtotime($row['date'])); ?></em> | Sent <?php if ( $tcateg == 'outgoing' ) { echo 'to'; } else { echo 'from'; } ?> <strong><?php echo $row['trader']; ?></strong> <?php if ( isset($row['email']) && $row['email'] !== '' ) { echo '(<a href="mailto:'.$row['email'].'">'.$row['email'].'</a>)'; } ?></td>
					</tr>
					<tr class="collapse">
						<script type="text/javascript">menuOptions[<?php echo $counttrades; ?>] = "<?php echo $menuoptions; ?>";</script>
						<?php $newcounter++; ?>
						<td class="xdark">Trading Cards <img src="images/newfield.png" class="newfieldBtn" onclick="addElement(<?php echo $newcounter; ?>,'trading',menuOptions[<?php echo $counttrades; ?>]);"> </td>
						<?php $newcounter++; ?>
						<td class="xdark">Receiving Cards <img src="images/newfield.png" class="newfieldBtn" onclick="addElement(<?php echo $newcounter; ?>,'receiving',menuOptions[<?php echo $counttrades; ?>]);"> </td>
					</tr>
					<tr class="collapse">
						<td valign="top">
							<?php $tradingcards = explode(';',$row['giving']); $categ = explode(',',$row['givingcat']); $i = 0;
							foreach ($tradingcards as $cardgroup) { ?>
							<input name="tradingcards[]" type="text" id="tradingcards[]" value="<?php echo trim($cardgroup); ?>" size="40" style="font-size:10px;" /> » 
						  	<select name="tradingcat[]" id="tradingcat[]">
								<?php foreach ( $gcategories as $category ) { ?><option value="<?php echo trim($category); ?>" <?php if ( trim($categ[$i]) == trim($category) ) { echo 'selected="selected"'; } ?>><?php echo trim($category); ?></option><?php } ?>
							</select>
							<?php echo '<br /><br />'; $i++; } ?>
							<div id="myDiv<?php echo $newcounter-1; ?>"> </div>
						</td>
						<td valign="top">
							<?php $receivingcards = explode(';',$row['receiving']); $categ = explode(',',$row['receivingcat']); $i = 0;
							foreach ($receivingcards as $cardgroup) { ?>
							<input name="receivingcards[]" type="text" id="receivingcards[]" value="<?php echo trim($cardgroup); ?>" size="40" style="font-size:10px;" /> » 
						  <select name="receivingcat[]" id="receivingcat[]">
                          		<option value="">--</option>
								<?php foreach ( $rcategories as $category ) { ?><option value="<?php echo trim($category); ?>" <?php if ( trim($categ[$i]) == trim($category) ) { echo 'selected="selected"'; } ?>><?php echo trim($category); ?></option><?php } ?>
							</select>
							<?php echo '<br /><br />'; $i++; } ?>
							<div id="myDiv<?php echo $newcounter; ?>"> </div>
						</td>
					</tr>
					<tr class="collapse">
						<td colspan="2" class="light" align="right"><?php if ( $row['email'] !== '' ) { ?><label>email cards: <input name="emailcards" type="checkbox" id="emailcards" value="1" <?php if ( $row['emailcards'] === '1' ) { echo 'checked="checked"'; } ?> /></label> <?php } ?>
						<input name="update" type="submit" id="update" value="Update" /> <input name="complete" type="submit" id="complete" value="Complete Trade" /> <input name="remove" type="submit" id="remove" value="Remove Trade" /></td>
					</tr>
				</table>
				</form>
				
	<?php
			$counttrades++;
			}

			echo '</div>';
		
		}

		echo '</div><br /><br />';
		
	}
	
	else { echo '<h1>'.$tcginfo['name'].'</h1>'; echo '<p>&raquo; <a href="newtrade.php?id='.$tcginfo['id'].'">New Pending Trade</a></p>'; echo '<p>There are currently no pending trades for this TCG.</p>';  }

}

?>

<?php include 'footer.php'; ?>