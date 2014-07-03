<?php  if ( ! defined('VALID_INC') ) exit('No direct script access allowed');

//****************** EASYTCG FM CLASS LIBRARY ******************//

// CHANGE SETTINGS BELOW TO MATCH YOUR EASYTCG FM DATABASE SETTINGS
class Config {

	const DB_SERVER = 'localhost', // In most cases, you can leave this as 'localhost'. If you're unsure, check with your host.
		  DB_USER = 'dbuser', // Database user
		  DB_PASSWORD = 'dbpassword', // Database user password
		  DB_DATABASE = 'dbdatabase', // Database name
		  DB_SALT = 'aEF#TGgs-!dgaw3324_WQ+'; // This is your password salt. Feel free to keep the default value, but it is advised that you change it. Treat it like a high security password.

}
// DO NOT EDIT PAST THIS LINE UNLESS YOU KNOW WHAT YOU'RE DOING

class Sanitize {
	
	function clean ($data) {
			
		if ( get_magic_quotes_gpc() ) { $data = stripslashes($data); }
		
		$data = trim(htmlentities(strip_tags($data)));
		
		return $data;
	
	}
	
	function for_db ($data) {
		
		$data = $this->clean($data);
		
		$data = mysql_real_escape_string($data);
		
		return $data;
			
	}
	
}

class Database {
	
	function connect () {
	
		@mysql_connect( Config::DB_SERVER , Config::DB_USER , Config::DB_PASSWORD )
		or die( "Couldn't connect to MYSQL: ".mysql_error() );
		@mysql_select_db( Config::DB_DATABASE )
		or die ( "Couldn't open $db_datase: ".mysql_error() );
		
		return true;
		
	}
	
	function query ($query) {
		
		@mysql_connect( Config::DB_SERVER , Config::DB_USER , Config::DB_PASSWORD )
		or die( "Couldn't connect to MYSQL: ".mysql_error() );
		@mysql_select_db( Config::DB_DATABASE )
		or die ( "Couldn't open $db_datase: ".mysql_error() );
		
		$result = mysql_query($query);
		
		return $result;
		
	}
	
	function get_assoc ($query) {
		
		@mysql_connect( Config::DB_SERVER , Config::DB_USER , Config::DB_PASSWORD )
		or die( "Couldn't connect to MYSQL: ".mysql_error() );
		@mysql_select_db( Config::DB_DATABASE )
		or die ( "Couldn't open $db_datase: ".mysql_error() );
		
		$result = mysql_query($query);
		
		if ( !$result ) { die ( "Couldn't process query: ".mysql_error() ); }
		
		$assoc = mysql_fetch_assoc($result);
		
		return $assoc;
		
	}
	
	function get_array ($query) {
		
		@mysql_connect( Config::DB_SERVER , Config::DB_USER , Config::DB_PASSWORD )
		or die( "Couldn't connect to MYSQL: ".mysql_error() );
		@mysql_select_db( Config::DB_DATABASE )
		or die ( "Couldn't open $db_datase: ".mysql_error() );
		
		$result = mysql_query($query);
		
		if ( !$result ) { die ( "Couldn't process query: ".mysql_error() ); }
		
		$array = mysql_fetch_array($result);
		
		return $array;
		
	}
	
	function num_rows ($query) {
	
		@mysql_connect( Config::DB_SERVER , Config::DB_USER , Config::DB_PASSWORD )
		or die( "Couldn't connect to MYSQL: ".mysql_error() );
		@mysql_select_db( Config::DB_DATABASE )
		or die ( "Couldn't open $db_datase: ".mysql_error() );
		
		$result = mysql_query($query);
		
		if ( !$result ) { die ( "Couldn't process query: ".mysql_error() ); }
		
		$num_rows = mysql_num_rows($result);
		
		return $num_rows;
	
	}
	
}

class Session {

	function start ($username, $password, $remember) {
			
			$database = new Database;
			
			$database->connect();
			
			$sanitize = new Sanitize;
			
			$remember = filter_var($remember, FILTER_SANITIZE_NUMBER_INT);
			$username = $sanitize->for_db($username);
			
			if ( $remember != 3 ) {
				$password = sha1("$password".Config::DB_SALT."");
			}
			else {
				$password = $sanitize->for_db($password);	
			}
			
			$unexists = $database->num_rows("SELECT `value` FROM `settings` WHERE `setting` = 'username' AND `value` = '$username'");
			$pwexists = $database->num_rows("SELECT `value` FROM `settings` WHERE `setting` = 'password' AND `value` = '$password'");
			
			if ( !preg_match('/^[a-zA-Z0-9]{3,15}$/i', $username) || $unexists != 1 || $pwexists != 1 ) { return false; }
			else {
				
				$_SESSION['logged_in'] = true;
				$_SESSION['username'] = $username;
				$_SESSION['password'] = $password;
				$_SESSION['currTCG'] = "";
				
				if ( $remember == 1 || $remember == 3 ) {
					
					setcookie("easyTCGFM_un", $username, time()+60*60*24*30); 
					setcookie("easyTCGFM_pw", $password, time()+60*60*24*30); 
					
				}
			
			}
		
	}
	
	function validate () {
		
		$sanitize = new Sanitize;
		$database = new Database;
		$database->connect();
		
		if ( $_SESSION['logged_in'] != true && isset($_COOKIE['easyTCGFM_un']) && isset($_COOKIE['easyTCGFM_pw']) ) {
			
			$username = $sanitize->for_db($_COOKIE['easyTCGFM_un']);
			$password = $sanitize->for_db($_COOKIE['easyTCGFM_pw']);
			$database = new Database;
			$unexists = $database->num_rows("SELECT `value` FROM `settings` WHERE `setting` = 'username' AND `value` = '$username'");
			$pwexists = $database->num_rows("SELECT `value` FROM `settings` WHERE `setting` = 'password' AND `value` = '$password'");
			if ( $unexists == 1 && $pwexists == 1 ) { $this->start ($username, $password, 3); return true; }
			else { return false; }
		
		}
		
		else if ( $_SESSION['logged_in'] == true ) {
			
			$username = $_SESSION['username'];
			$password = $_SESSION['password'];
			$database = new Database;
			$unexists = $database->num_rows("SELECT `value` FROM `settings` WHERE `setting` = 'username' AND `value` = '$username'");
			$pwexists = $database->num_rows("SELECT `value` FROM `settings` WHERE `setting` = 'password' AND `value` = '$password'");
			if ( $unexists == 1 && $pwexists == 1  ) { return true; }
			else { $this->close(); return false; }
		
		}
		
		else {
			return false;	
		}
		
	}
	
	function close () {
		
		$time = time();
		setcookie("easyTCGFM_un", "", $time - 3600);
		setcookie("easyTCGFM_pw", "", $time - 3600);
		
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		session_unset();
		session_destroy();
		
	}

}

class Email {

	function auto( $memid, $subject, $message, $type, $check = 1 ) {
		
		$database = new Database;
		$tcgname = $database->get_assoc("SELECT * FROM `settings` WHERE `setting` = 'tcg_name'");
		$tcgname = $tcgname['value'];
		$tcgemail = $database->get_assoc("SELECT * FROM `settings` WHERE `setting` = 'tcg_email'");
		$tcgemail = $tcgemail['value'];
		$tcgurl = $database->get_assoc("SELECT * FROM `settings` WHERE `setting` = 'tcg_url'");
		$tcgurl = $tcgurl['value'];
		$meminfo = $database->get_assoc("SELECT `$type`, `email`, `username` FROM `members` WHERE `id`='$memid'");
		
		$message = str_replace('{mem_name}',$meminfo['username'],$message);
		$message = str_replace('{tcg_name}',$tcgname,$message);
		$message = str_replace('{tcg_url}',$tcgurl,$message);
		
		if ( $meminfo[$type] == 1 && $check == 1 || $check == 0 ) {
			$headers = "From: $tcgname <$tcgemail> \r\n";
			$headers.= "Reply-To: $tcgemail";
			if ( mail($meminfo['email'],"$tcgname: $subject",$message,$headers) ) { return true; } else { return false; }
		}
		
		else { return true; }

	}

}

?>