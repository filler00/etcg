					</div><!--/row-->
				</div><!--/span-->

				<div class="col-xs-6 col-sm-3 sidebar-offcanvas" id="sidebar" role="navigation">
					<div class="input-group">
						<form name="tcgselect">
							<select name="currTCG" id="currTCG" class="form-control" onchange="location.href=tcgselect.currTCG.options[selectedIndex].value">
								<option value="index.php?id=">-- Active --</option>
								<?php
								$result = $database->query("SELECT * FROM `tcgs` WHERE `status` = 'active' ORDER BY `name`");
								
								while ( $row = mysqli_fetch_assoc($result) ) {
								?>
								<option value="cards.php?id=<?php echo $row['id'] ?>" <?php if ( $_SESSION['currTCG'] == $row['id'] ) { echo 'selected="selected"'; } ?>><?php echo $row['name']; ?></option>
								<?php } ?>
								<option value="index.php?id="></option>
								<option value="index.php?id=">-- Hiatus --</option>
								<?php
								$result = $database->query("SELECT * FROM `tcgs` WHERE `status` = 'hiatus' ORDER BY `name`");
								
								while ( $row = mysqli_fetch_assoc($result) ) {
								?>
								<option value="cards.php?id=<?php echo $row['id'] ?>" <?php if ( $_SESSION['currTCG'] == $row['id'] ) { echo 'selected="selected"'; } ?>><?php echo $row['name']; ?></option>
								<?php } ?>
								<option value="index.php?id="></option>
								<option value="index.php?id=">-- Inactive --</option>
								<?php
								$result = $database->query("SELECT * FROM `tcgs` WHERE `status` = 'inactive' ORDER BY `name`");
								
								while ( $row = mysqli_fetch_assoc($result) ) {
								?>
								<option value="cards.php?id=<?php echo $row['id'] ?>" <?php if ( $_SESSION['currTCG'] == $row['id'] ) { echo 'selected="selected"'; } ?>><?php echo $row['name']; ?></option>
								<?php } ?>
							</select>
						</form>
						<span class="input-group-btn">
							<a href="newtcg.php" class="btn btn-primary"><i class="fa fa-plus"></i></a>
						</span>
					</div><!-- /input-group -->
					
					<?php if ( $_SESSION['currTCG'] != "" ) { ?>
					<div class="list-group">
						<a href="manage.php?id=<?php echo $_SESSION['currTCG']; ?>" class="list-group-item"><i class="fa fa-cog"></i> TCG Settings</a>
						<a href="cards.php?id=<?php echo $_SESSION['currTCG']; ?>" class="list-group-item"><i class="fa fa-th"></i> Collection</a>
						<a href="logs.php?id=<?php echo $_SESSION['currTCG']; ?>" class="list-group-item"><i class="fa fa-list"></i> Logs</a>
						<a href="trades.php?id=<?php echo $_SESSION['currTCG']; ?>" class="list-group-item"><i class="fa fa-envelope"></i> Trades <span class="badge"><?php echo $database->num_rows("SELECT * FROM `trades` WHERE `tcg` = '".$_SESSION['currTCG']."'"); ?></span></a>
						<a href="update.php?id=<?php echo $_SESSION['currTCG']; ?>" class="list-group-item"><i class="fa fa-star"></i> Easy Update</a>
					</div>
					<?php } ?>
				</div><!--/span-->
			</div><!--/row-->

			<footer>
				<small>
					EasyTCG by <a href="http://kablooey.net">Bloo</a> &hearts;
					<br>See <a href="http://filler00.com/">Filler00</a> &amp; <a href="https://github.com/tooblue/etcg">GitHub</a> for documentation and support.
				</small>
			</footer>

		</div> <!-- /container -->


		<!-- Bootstrap core JavaScript
		================================================== -->
		<!-- Placed at the end of the document so the pages load faster -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
		<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
		<script src="js/scripts.js"></script>
	</body>
</html>