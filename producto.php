<?php require_once('Connections/test.php'); ?>
<?php require_once('php/engine.php'); ?>
<?php require_once('php/heading.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "admin,";
$MM_donotCheckaccess = false;

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
if((!($MM_donotCheckaccess))){
	if (!( (isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
	  $MM_qsChar = "?";
	  $MM_referrer = $_SERVER['PHP_SELF'];
	  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
	  if (isset($QUERY_STRING) && strlen($QUERY_STRING) > 0) 
	  $MM_referrer .= "?" . $QUERY_STRING;
	  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
	  header("Location: ". $MM_restrictGoTo); 
	  exit;
	}
}
?>
<?php
//Prepare filters
$sidUsuario="";
$MM_superUsers="admin";
if (in_array($MM_superUsers, Explode(",", $_SESSION['MM_UserGroup'])))
{ 
	//echo "<a href=\"productos.php\">" . "Veh&iacute;culos registrados". "</a>";
}
else
{
	//echo "<a href=\"productos.php\">" . "Mis veh&iacute;culos" . "</a>";
	mysql_select_db($database_test, $test);
	
	$query_rsInforme = "SELECT * FROM ". "Usuario" . " " . "WHERE Usuario='" . $_SESSION['MM_Username'] . "'";
	$rsInforme = mysql_query($query_rsInforme, $test) or die(mysql_error());
	$row_rsInforme = mysql_fetch_assoc($rsInforme);
		
	$sidUsuario = $row_rsInforme["idUsuario"];
	
	mysql_free_result($rsInforme);
}

$links=Array();

$links["Producto"]=new LinkColumn();
$links["Producto"]->validatorMsg="Debe introducir un nombre.";
$links["Producto"]->label="T&iacute;tulo";

$lc1=new LinkColumn();
$lc1->name="Marca";
$lc1->table="Marca";
$lc1->idField="idMarca";
$lc1->nameField="Marca";
$lc1->validatorMsg="Debe seleccionar la marca";
$links[$lc1->name]=$lc1;

$links["idUsuario"]=new LinkColumn();
$links["idUsuario"]->default_value=$sidUsuario;
$links["idUsuario"]->hidden=1;

$links["Ano"]=new LinkColumn();
$links["Ano"]->date_only_year=1;
$links["Ano"]->label="A&ntilde;o";
$links["Ano"]->validatorMsg="Debe seleccionar el a&ntilde;o.";

$lc1=new LinkColumn();
$lc1->name="Combustible";
$lc1->table="Combustible";
$lc1->idField="idCombustible";
$lc1->nameField="Combustible";
$lc1->validatorMsg="Debe seleccionar el combustible";
$links[$lc1->name]=$lc1;

$links["FechaLog"]=new LinkColumn();
$links["FechaLog"]->skip=1;

$links["FechaUpdate"]=new LinkColumn();
$links["FechaUpdate"]->skip=1;
	
$result = BuildForm('Producto',$database_test,$test,$links,"upload.php","upload.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
<LINK href="css/interactiva.css" rel="stylesheet" type="text/css">
<title>informes</title>
</head>

<body>
<div id="vbod">
<div id="page_margins">
<!--Filter-->
<!--<table border="0" align="center">
  <tr>
  	<td colspan="2" align="center"><img src="images/mylogo.jpg" alt="mylogo" height="100px"/></td>
  </tr>-->
<?php echo $heading_begin; ?>
<?php echo $heading_end; ?>
<br/>

<?php
echo $result;
?>
</div>
</div>
<div id="border_bottom"></div>
</body>
</html>
