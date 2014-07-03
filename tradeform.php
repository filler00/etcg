<?php if ( isset($error) ) { foreach ( $error as $msg ) {  ?><br /><div align="center" class="xlight"><strong>ERROR!</strong> <?php echo $msg; ?></div><br /><?php } } ?>

<form action="trade.php" method="post" name="trades" id="trades">
<table class="stats" align="center" width="400" cellpadding="5" cellspacing="5">
	<tr>
    	<td align="right">Name</td>
    	<td><input type="text" name="name" id="name" value="<?php if ( isset($_POST['tradesubmit']) ) { echo $_POST['name']; } ?>"></td>
    </tr>
	<tr>
	  <td align="right">Email</td>
	  <td><input type="text" name="email" id="email" value="<?php if ( isset($_POST['tradesubmit']) ) { echo $_POST['email']; } ?>"></td>
  </tr>
	<tr>
	  <td align="right">Website</td>
	  <td><input name="website" type="text" id="website" value="<?php if ( isset($_POST['tradesubmit']) ) { echo $_POST['website']; } else { echo 'http://'; } ?>"></td>
  </tr>
	<tr>
	  <td align="right">TCG</td>
	  <td><select name="tcg" id="tcg">
      	<?php $database = new Database;
		$hiatustrading = $database->get_assoc("SELECT `value` FROM `settings` WHERE `setting`='hiatustrading' LIMIT 1");
		$hiatustrading = $hiatustrading['value'];
		$inactivetrading = $database->get_assoc("SELECT `value` FROM `settings` WHERE `setting`='inactivetrading' LIMIT 1");
		$inactivetrading = $inactivetrading['value'];
		
		if ( $hiatustrading == 0 && $inactivetrading == 0 ) { $result = $database->query("SELECT `id`,`name` FROM `tcgs` WHERE `status`='active' ORDER BY `name`"); }
		else if ( $hiatustrading == 1 && $inactivetrading == 1 ) { $result = $database->query("SELECT `id`,`name` FROM `tcgs` ORDER BY `name`"); }
		else if ( $hiatustrading == 1 ) { $result = $database->query("SELECT `id`,`name` FROM `tcgs` WHERE `status`='active' OR `status`='hiatus' ORDER BY `name`"); }
		else if ( $inactivetrading == 1 ) { $result = $database->query("SELECT `id`,`name` FROM `tcgs` WHERE `status`='active' OR `status`='inactive' ORDER BY `name`"); }
		
		while ( $row = mysql_fetch_assoc($result) ) {
	    	echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
        } ?>
      </select></td>
  </tr>
	<tr>
	  <td align="right">Cards You Want</td>
	  <td><input type="text" name="wants" id="wants" value="<?php if ( isset($_POST['tradesubmit']) ) { echo $_POST['wants']; } ?>"></td>
  </tr>
	<tr>
	  <td align="right">Cards You'll Give Me</td>
	  <td><input type="text" name="offer" id="offer" value="<?php if ( isset($_POST['tradesubmit']) ) { echo $_POST['offer']; } ?>"></td>
  </tr>
	<tr>
	  <td align="right">Comments?<br /> Member Cards?</td>
	  <td><textarea name="comments" id="comments" cols="20" rows="3"><?php if ( isset($_POST['tradesubmit']) ) { echo $_POST['comments']; } ?></textarea></td>
  </tr>
  <tr>
	  <td colspan="2" align="right"><input type="submit" name="tradesubmit" id="tradesubmit" value="Submit"></td>
  </tr>
</table>
</form>