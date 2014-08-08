<?php require_once('Connections/test.php'); ?>
<?php require_once('php/engine.php'); ?>
<?php require_once('php/heading.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "admin,";
$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && false) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "login.php";
//echo $_SESSION['MM_UserGroup'];
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($QUERY_STRING) && strlen($QUERY_STRING) > 0) 
  $MM_referrer .= "?" . $QUERY_STRING;
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
<LINK href="css/interactiva.css" rel="stylesheet" type="text/css">
<title>indice</title>
</head>

<body>
<div id="vbod">
<div id="page_margins">
<?php echo $heading_begin; ?>
<?php echo $heading_end; ?>
<br/>
<!--Filter-->
<!--<table border="0" align="center">
  <tr>
  	<td colspan="2" align="center"><img src="images/mylogo.png" alt="mylogo" height="100px"/></td>
  </tr>
</table>-->
<!--Grid-->
<table border="0" align="center">
  <tr>
    <td align="center">
	<?php
	$MM_superUsers="admin";
    if (in_array($MM_superUsers, Explode(",", $_SESSION['MM_UserGroup'])))
	{ 
      echo "<a href=\"productos.php\">" . "Veh&iacute;culos registrados". "</a>";
    }
	else
	{
		echo "<a href=\"productos.php\">" . "Mis veh&iacute;culos" . "</a>";
	} 	
	?>	
	</td>
	<?php
	$MM_superUsers="admin";
    if (in_array($MM_superUsers, Explode(",", $_SESSION['MM_UserGroup'])))
	{ 	
	?>
	<td rowspan="3" align="left">
	<?php }else{ ?>
	<td rowspan="2" align="left">
	<?php } ?>
<?php
	mysql_select_db($database_test, $test);

	$query_rsInforme = "select idUsuario,Usuario,Email,Provincia,Telefono from Usuario" . " " . "WHERE Usuario='" . $_SESSION['MM_Username'] . "'";;
		
	$rsInforme = mysql_query($query_rsInforme, $test) or die(mysql_error());
	$row_rsInforme = mysql_fetch_assoc($rsInforme);
	$totalRows_rsInforme = mysql_num_rows($rsInforme);	

	echo "<b>Tus datos de contacto:</b>" . "<br/>";
	echo $row_rsInforme['Usuario'] . "<br/>";
	echo $row_rsInforme['Email'] . "<br/>";
	echo $row_rsInforme['Telefono'] . "<br/>";
?>	
	</td>
  </tr>
   <tr>
    <td align="center">
	<?php
	$MM_superUsers="admin";
    if (in_array($MM_superUsers, Explode(",", $_SESSION['MM_UserGroup'])))
	{ 
      echo "<a href=\"usuarios.php\">" . "Usuarios registrados". "</a>";
    }
	else
	{
		echo "<a href=\"usuario.php?idUsuario=" . $row_rsInforme['idUsuario'] . "\">" . "Mis datos" . "</a>";
	} 	
	?>
	</td>
  </tr>  
	<?php
	$MM_superUsers="admin";
    if (in_array($MM_superUsers, Explode(",", $_SESSION['MM_UserGroup'])))
	{ 
    	echo "<tr><td align=\"center\"><a href=\"listado_mail.php\">" . "Listado control mails". "</a></td></tr>";
    }
	else
	{
		//Do nothing
	} 	
	?>    
</table>
</div>
</div>
<div id="border_bottom"></div>
</body>
</html>
