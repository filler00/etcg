<?php if ( ! defined('VALID_INC') ) exit('No direct script access allowed');

require_once('config.php');

class Sanitize {
	
	function clean ($data) {
			
		if ( get_magic_quotes_gpc() ) { $data = stripslashes($data); }
		
		$data = trim(htmlentities(strip_tags($data)));
		
		return $data;
	
	}
	
	function for_db ($data) {
		
		$database = new Database;
		$link = $database->connect();
		
		$data = $this->clean($data);
		
		$data = mysqli_real_escape_string($link, $data);
		
		return $data;
			
	}
	
}

class Database {
	
	function connect () {
	
		$link = @mysqli_connect( Config::DB_SERVER , Config::DB_USER , Config::DB_PASSWORD, Config::DB_DATABASE )
		or die( "Couldn't connect to MYSQL: ".mysqli_error() );
		
		return $link;
		
	}
	
	function query ($query) {
		
		$link = $this->connect();
		
		$result = mysqli_query($link, $query);
		
		return $result;
		
	}
	
	function get_assoc ($query) {
		
		$link = $this->connect();
		
		$result = mysqli_query($link, $query);
		
		if ( !$result ) { die ( "Couldn't process query: ".mysqli_error() ); }
		
		$assoc = mysqli_fetch_assoc($result);
		
		return $assoc;
		
	}
	
	function get_array ($query) {
		
		$link = $this->connect();
		
		$result = mysqli_query($link, $query);
		
		if ( !$result ) { die ( "Couldn't process query: ".mysqli_error() ); }
		
		$array = mysqli_fetch_array($result);
		
		return $array;
		
	}
	
	function num_rows ($query) {
	
		$link = $this->connect();
		
		$result = mysqli_query($link, $query);
		
		if ( !$result ) { die ( "Couldn't process query: ".mysqli_error() ); }
		
		$num_rows = mysqli_num_rows($result);
		
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

class Upload {

	function card( $tcginfo, $catinfo = '', $table, $card, $defaultauto = '', $format = '' ) {
			
		if ( $catinfo != '' ) {
			if ( $catinfo['format'] == 'default' ) { $format = $tcginfo['format']; }
			else { $format = $catinfo['format']; }
	
			if ( $table == 'collecting' ) {
				if ( $catinfo['uploadurl'] == 'default' ) { $defaultauto = $tcginfo['defaultauto']; }
				else { $defaultauto = $catinfo['uploadurl']; }
			} else {
				if ( $catinfo['autourl'] == 'default' ) { $defaultauto = $tcginfo['defaultauto']; }
				else { $defaultauto = $catinfo['autourl']; }
			}
		}
		
		$filename = ''.$tcginfo['cardspath'].''.$card.'.'.$format.'';
		$imgurl = ''.$defaultauto.''.$card.'.'.$format.'';
			
		if ( !file_exists($filename) ) {
			
			if ( !$img = file_get_contents($imgurl) ) { return false; }
			else {
				if ( !file_put_contents($filename,$img) ) { return false; }	
				else { return true; }
			}
			
		} else { return 0; };

	}

}

?>