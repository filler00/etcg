<?php

	if ( isset($_POST['tradesubmit']) ) {
		
		$database = new Database;
		$sanitize = new Sanitize;
		
		$name = $sanitize->for_db($_POST['name']);
		$email = $sanitize->for_db($_POST['email']);
		$website = $sanitize->for_db($_POST['website']);
		$tcg = intval($_POST['tcg']);
		$wants = $sanitize->for_db($_POST['wants']);
		$offer = $sanitize->for_db($_POST['offer']);
		$comments = $sanitize->for_db($_POST['comments']);
		
		$validcards = true;
		
		$wants = explode(',',$wants);
		foreach ( $wants as $card ) {
			if ( !preg_match("/[a-z0-9_-]+[0-9]{2,4}/i", $card) ) { $validcards = false; }
		}
		
		$offer = explode(',',$offer);
		foreach ( $offer as $card ) {
			if ( !preg_match("/[a-z0-9_-]+[0-9]{2,4}/i", $card) ) { $validcards = false; }
		}
		
		array_walk($wants, 'trim_value');
		array_walk($offer, 'trim_value');
		
		if ( $validcards === true ) {
		
			foreach ( $wants as $givingcard ) {
		
				unset($cardfound);
					
				$result = $database->query("SELECT * FROM `cards` WHERE `tcg`='$tcg' AND `priority`!='3' ORDER BY `priority`");
				while ( $row = mysql_fetch_array($result) ) {
					
					if ( !isset($cardfound) || $cardfound != true ) {
						
						$cards = explode(',',$row['cards']);
						array_walk($cards, 'trim_value');
						
						$i = 0;
						foreach ( $cards as $card ) {
							if ( preg_match("/$givingcard/i", $card) && !isset($cardfound) ) { 
								$foundcards[] = $card;
								$cardfound = true;
							}
							$i++;
						}
					
					}
					
				}
				
			}
			
			
			if ( isset($foundcards) ) {
				array_walk($foundcards, 'trim_value');
				$foundcards = implode(',',$foundcards);
			}
			
			$wants = implode(',',$wants);
			
			if ( $foundcards === $wants ) { $cardsfound = true; } else { $cardsfound = false; }
		
		}
		
		if ( $name == '' || $email == ''|| $website == '' || $website == 'http://' || $tcg == ''|| $wants == ''|| $offer == ''  ) { $error[] = 'Please fill out the form completely. All fields are mandatory except for the comments field.'; }
		else if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) { $error[] = 'The supplied email was invalid.'; }
		else if ( !filter_var($website, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) ) { $error[] = 'The supplied URL was invalid. Make sure you include <em>http://</em>.'; }
		else if ( $validcards === false ) { $error[] = "One or more cards is invalid. Check that you're separating card names with commas, and that cardnames are spelled out and formatted correctly (cardname00)."; }
		else if ( $cardsfound !== true ) { $error[] = "One or more cards that you requested were not found in my collection. Please check your spelling, and make sure you're requesting cards from categories marked as <em>trading</em>. If you're unsure, leave a note about it in the comments field."; }
		else {
			
			$wants = explode(',',$wants);
			
			foreach ( $wants as $givingcard ) {
				
				unset($cardfound);
				
				$result = $database->query("SELECT * FROM `cards` WHERE `tcg`='$tcg' AND `priority`!='3' ORDER BY `priority`");
				$x = 0;
				while ( $row = mysql_fetch_array($result) ) {
					
					if ( !isset($cardfound) || $cardfound != true ) {
						
						$cards = explode(',',$row['cards']);
						array_walk($cards, 'trim_value');
						
						$i = 0;
						foreach ( $cards as $card ) {
							if ( preg_match('/^'.$givingcard.'$/i', $card) && !isset($cardfound) ) { 
								if ( $removedcards[$x] == '' ) { $removedcards[$x] = $card; } else { $removedcards[$x] = ''.$removedcards[$x].', '.$card.''; }
								$removedcats[$x] = $row['category'];
								$cards[$i] = '';
								$cardfound = true;
							}
							$i++;
						}
						
						$cards = array_filter($cards);
						sort($cards);
						$cards = implode(', ',$cards);
						
						$categid = $row['id'];
						$resultt = $database->query("UPDATE `cards` SET `cards`='$cards' WHERE `id`='$categid'");
						if ( !$resultt ) { $error[] = "Error updating cards from category ".$row['category'].""; }
					
					}
					
					$x++;
					
				}
			
			}
			
			$offer = implode(', ',$offer);
			$wants = implode(', ',$wants);
			$giving = implode('; ',$removedcards);
			$givingcats = implode(', ',$removedcats);
			
			$today = date("Y-m-d");
			$result = $database->query("INSERT INTO `trades` (`tcg`,`trader`,`email`,`giving`,`givingcat`,`receiving`,`receivingcat`,`type`,`date`) VALUE ('$tcg','$name','$email','$giving','$givingcats','$offer','','incoming','$today')");
			if ( !result ) { $error[] = "Could not add the new trade. ".mysql_error().""; }
			
			$youremail = $database->get_assoc("SELECT `value` FROM `settings` WHERE `setting` = 'email'");
			$youremail = $youremail['value'];
			$etcgurl = $database->get_assoc("SELECT `value` FROM `settings` WHERE `setting` = 'etcgurl'");
			$etcgurl = $etcgurl['value'];
			$tcgname = $database->get_assoc("SELECT `name` FROM `tcgs` WHERE `id`='$tcg'");
			$tcgname = $tcgname['name'];
			$headers = "From: $name <$email> \r\n";
			$headers.= "Reply-To: $email";
			$message = "New Incoming Trade Request for $tcgname: \n\nName: $name \nEmail: $email \nWebsite: $website \nTCG: $tcgname \nCards Wanted: $wants \nCards Offered: $offer \nComments: $comments \n\nManage $tcgname Trades: ".$etcgurl."trades.php?id=$tcg";
			
			if ( mail($youremail,"Trade Request: $tcgname",$message,$headers) ) { $success = true; } else { $error[] = "There was an error sending your form. Please try again, or send an email directly to <em>$youremail</em>."; }
		
		}
	}

?>

<?php 

if ( $success ) { echo "<h1>Trade Me</h1><p><strong>Your form was submitted successfully!</strong> Any cards that you requested should have been moved to my pending cards section. I will get back to you as soon as possible, but please allow at least a week for a response to your trade request before resubmitting/withdrawing your offer. <em>Thank you!</em></p><p>&raquo; <a href=\"index.php\">Return to Index</a><br />&raquo; <a href=\"trade.php\">Return to Trade Form</a></p>"; }

else { 

$database = new Database;
$youremail = $database->get_assoc("SELECT `value` FROM `settings` WHERE `setting` = 'email'");
$youremail = $youremail['value'];

?> 

<h1>Trade Me</h1>
<div align="center"><ul>
	<li>Please allow at least <em>7 days</em> for a response to your trade request.</li>
	<li>If the form doesn't work, feel free to email me: <strong><?php echo str_replace('@','[at]',$youremail); ?></strong></li>
	<li><strong>Please spell out card names COMPLETELY.</strong> (ie. do NOT type cardname01/02; DO type cardname01, cardname02)</li>
</ul></div>
   
<br />

<?php include 'tradeform.php'; } ?>