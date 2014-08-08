<?php require_once('Connections/test.php'); ?>
<?php
// *** Validate request to login to this site.
if (!isset($_SESSION)) {
  session_start();
}

$loginFormAction = $_SERVER['PHP_SELF'];
if (isset($_GET['accesscheck'])) {
  $_SESSION['PrevUrl'] = $_GET['accesscheck'];
}

if (isset($_POST['usuario'])) {
  $loginUsername=$_POST['usuario'];
  $password=$_POST['password'];
  $MM_fldUserAuthorization = "idNivel";
  $MM_redirectLoginSuccess = "myarea.php";
  $MM_redirectLoginFailed = "error.php";
  $MM_redirecttoReferrer = false;
  mysql_select_db($database_test, $test);
  	
  $LoginRS__query=sprintf("SELECT idUsuario, Usuario, idNivel FROM Usuario WHERE Usuario='%s' AND Password='%s'",
  get_magic_quotes_gpc() ? $loginUsername : addslashes($loginUsername), get_magic_quotes_gpc() ? $password : addslashes($password)); 
   
  //echo $LoginRS__query;
   
  $LoginRS = mysql_query($LoginRS__query, $test) or die(mysql_error());
  $loginFoundUser = mysql_num_rows($LoginRS);
  if ($loginFoundUser) {
    //echo $LoginRS__query;
    $loginStrGroup  = mysql_result($LoginRS,0,'idNivel');
    
    //declare two session variables and assign them
    $_SESSION['MM_Username'] = $loginUsername;
    $_SESSION['MM_UserGroup'] = $loginStrGroup;	      

    if (isset($_SESSION['PrevUrl']) && false) {
      $MM_redirectLoginSuccess = $_SESSION['PrevUrl'];	
    }
    header("Location: " . $MM_redirectLoginSuccess );
  }
  else {
    header("Location: ". $MM_redirectLoginFailed );
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
<LINK href="css/interactiva.css" rel="stylesheet" type="text/css">
<title>login</title>
</head>

<body>
<div id="vbod">
<div id="page_margins">
<form action="<?php echo $loginFormAction; ?>" method="POST" name="form1">
<!--Filter-->
<table border="0" align="center">
  <tr>
  	<td colspan="2" align="center"><a href="http://<?php  echo $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);?>"/><img src="images/mylogo.png" alt="mylogo" height="100px"/></td>
  </tr>
</table>
<br/>
<!--form-->
<table border="0" align="center">
  <tr>
    <td width="45%" align="right">Usuario:</td>
    <td><input type="text" name="usuario" /></td>
  </tr>
  <tr>
    <td width="45%" align="right">Contrase&ntilde;a:</td>
    <td><input type="password" name="password" /></td>
  </tr> 
  <tr>
    <td colspan="2" align="center"><a href="usuario.php">Quiero registrarme</a></td>
  </tr>    
  <tr>
    <td colspan="2" align="center"><input name="submit" type="submit" value="Aceptar" /></td>
  </tr>  
</table>
</form>
</div>
</div>
<div id="border_bottom"></div>
</body>
</html>
