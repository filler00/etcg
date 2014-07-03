<?php if ( $enablelogin ) { ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>easyTCG FM - Login</title>
<link href="style.css" rel="stylesheet" type="text/css" />
</head>

<body>

<div align="center"><img src="images/top.png" alt="" /></div>

<br />

<table cellspacing="5" cellpadding="5" align="center" class="style1" width="500">
	<tr>
    	<td>
          <?php if ( isset($login) && !$login ) { echo '<div align="center"><strong>Login attempt failed.</strong></div>'; }?>
          <form id="login" name="login" method="post" action="">
          <table cellspacing="5" cellpadding="5" align="center">
            <tr>
              <td align="right"><label>username:</label></td>
              <td><input name="username" type="text" class="style1" id="username" size="40" /></td>
            </tr>
            <tr>
              <td align="right"><label>password:</label></td>
              <td><input name="password" type="password" class="style1" id="password" size="40" /></td>
            </tr>
            <tr>
              <td align="right" colspan="2"><label><input name="remember" type="checkbox" id="remember" value="1" /> remember me </label> <input type="submit" name="login" id="login" value="login" class="button1" /></td>
            </tr>
          </table>
          </form>
  		</td>
	</tr>
</table>

<br /><br />

<table cellspacing="5" cellpadding="5" align="center" class="style1" width="500">
	<tr>
    	<td align="center">
        	easyTCG FM copyright © 2009 Michelle Lewis (Bloo).<br />
			Part of and distributed by <a href="http://ka-blooey.net" target="_blank">ka-blooey.NET</a> &amp; <a href="http://tooblue.org" target="_blank">tooblue.ORG</a>
        </td>
    </tr>
</table>

</body>
</html>
<?php } ?>