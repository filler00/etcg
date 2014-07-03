<?php include 'header.php';

$database = new Database;
function trim_value(&$value) { $value = trim($value); }

if ( isset($_POST['newtrade']) ) {
	
	$sanitize = new Sanitize;
	
	$tcgid = intval($_POST['tcg']);
	$trader = $sanitize->for_db($_POST['trader']);
	$email = $sanitize->for_db($_POST['email']);
	$giving = $sanitize->for_db($_POST['giving']);
	$receiving = $sanitize->for_db($_POST['receiving']);
	$type = $sanitize->for_db($_POST['type']);
	$grab = intval($_POST['grab']);
	
	$exists = $database->num_rows("SELECT `id` FROM `tcgs` WHERE `id`='$tcgid'");
	
	if ( $exists != 1 ) { $error[] = "The TCG does not exist."; }
	if ( $trader === '' ) { $error[] = "The trader field can't be left blank."; }
	if ( $email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL) ) { $error[] = "Invalid email address."; }
	if ( $type != 'outgoing' && $type != 'incoming' ) { $error[] = "Invalid trade type."; }
	if ( !isset($error) ) {
	
		if ( $grab == 1 ) {
			
			$giving = explode(',',$giving);
			array_walk($giving, 'trim_value');
			
			foreach ( $giving as $givingcard ) {
				
				unset($cardfound);
				
				$result = $database->query("SELECT * FROM `cards` WHERE `tcg`='$tcgid' AND `priority`!='3' ORDER BY `priority`");
				$x = 0;
				while ( $row = mysqli_fetch_array($result) ) {
					
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
			
			if ( isset($removedcards) ) {
			$checkremoved = implode(',',$removedcards);
			$checkremoved = explode(',',$checkremoved);
			array_walk($checkremoved, 'trim_value');
			}
			
			foreach ( $giving as $givingcard ) {
				
				if ( !isset($checkremoved) || $checkremoved === '' || !in_array($givingcard,$checkremoved) ) { $error[] = "Could not grab $givingcard."; }
				
			}
			
			if ( isset($removedcards) ) { $giving = implode('; ',$removedcards); $givingcats = implode(', ',$removedcats); }
			else { $giving = ''; $givingcats = ''; }
				
		} // end if grab
		
		$today = date("Y-m-d");
		$result = $database->query("INSERT INTO `trades` (`tcg`,`trader`,`email`,`giving`,`givingcat`,`receiving`,`receivingcat`,`type`,`date`) VALUE ('$tcgid','$trader','$email','$giving','$givingcats','$receiving','','$type','$today')");
		if ( !result ) { $error[] = "Could not add the new trade. ".mysqli_error().""; }
		else { $success[]= "The trade has been added."; }
		
	}

}

?>

<h1>New Pending Trade</h1>
<p>Separate card names with commas. Select the <em>grab from categories</em> option to automatically remove trading cards from your collection. Low priority categories are searched first.</p>
<?php if ( isset($_GET['id']) ) { ?><p>&laquo; <a href="trades.php?id=<?php echo intval($_GET['id']); ?>">Return to Trades</a></p><?php }?>

<?php if ( isset($error) ) { foreach ( $error as $msg ) {  ?><div class="errors"><strong>ERROR!</strong> <?php echo $msg; ?></div><?php } } ?>
<?php if ( isset($success) ) { foreach ( $success as $msg ) { ?><div class="success"><strong>SUCCESS!</strong> <?php echo $msg; ?></div><?php } } ?>

<form action="" method="post">
<table align="center" width="500" cellpadding="5" cellspacing="5">
	<tr>
    	<td align="right">TCG: </td>
        <td>
            <select name="tcg" id="tcg">
            	<?php $result = $database->query("SELECT * FROM `tcgs` ORDER BY `name`");
				while ( $row = mysqli_fetch_assoc($result) ) { ?>
                <option value="<?php echo $row['id']; ?>" <?php if ( isset($_GET['id']) && intval($_GET['id']) == $row['id'] ) { echo 'selected="selected"'; } ?>><?php echo $row['name']; ?></option>
                <?php } ?>
            </select>
      </td>
    </tr>
    <tr>
    	<td align="right">Trader: </td>
        <td><input name="trader" type="text" id="trader" /></td>
    </tr>
    <tr>
    	<td align="right">Email: </td>
        <td><input name="email" type="text" id="email" /></td>
  	</tr>
    <tr>
    	<td align="right">Trading Cards: </td>
      <td><input name="giving" type="text" id="giving" size="50" /></td>
  	</tr>
    <tr>
    	<td align="right">Receiving Cards: </td>
      <td><input name="receiving" type="text" id="receiving" size="50" /> </td>
    </tr>
    <tr>
    	<td align="right">Type: </td>
      <td>
          <select name="type" id="type">
            <option value="outgoing" selected>Outgoing</option>
            <option value="incoming">Incoming</option>
          </select> 
      </td>
  	</tr>
    <tr>
    	<td colspan="2" align="right"><label>grab from categories: <input name="grab" type="checkbox" id="grab" value="1" checked></label> <input name="newtrade" type="submit" id="newtrade" value="Submit"></td>
    </tr>
</table>
</form>

<?php include 'footer.php'; ?>