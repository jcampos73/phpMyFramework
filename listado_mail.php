<?php require_once('Connections/test.php'); ?>
<?php require_once('php/engine.php'); ?>
<?php require_once('php/heading.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "admin";
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
<LINK href="css/interactiva.css" rel="stylesheet" type="text/css">
<title>listado</title>
</head>

<body>
<div id="vbod">
<div id="page_margins">
<?php echo $heading_begin; ?>
<?php echo $heading_end; ?>
<br/>

<?php
//Prepare filters
$superUser=false;
$where_clause="";
$MM_superUsers="admin";
if (in_array($MM_superUsers, Explode(",", $_SESSION['MM_UserGroup'])))
{ 
	//echo "<a href=\"productos.php\">" . "Veh&iacute;culos registrados". "</a>";
	$superUser=true;
	$where_clause = "WHERE FechaUpdate<'" . date("Y-m-d",strtotime("-1 week")) . "'";
}
else
{
	//echo "<a href=\"productos.php\">" . "Mis veh&iacute;culos" . "</a>";
	
	$where_clause = "WHERE FechaUpdate<'" . date("Y-m-d",strtotime("-1 week")) . "'";
}

//Prepare links columns

$links=Array();

$lc1=new LinkColumn();
$lc1->name="idProducto";
$lc1->url_dest="producto.php";
$lc1->label="ID";
$links[$lc1->name]=$lc1;

$links["Producto"]=new LinkColumn();
$links["Producto"]->label="T&iacute;tulo";

$links["DescCorta"]=new LinkColumn();
$links["DescCorta"]->label="Descripci&oacute;n corta";

$links["Descripcion"]=new LinkColumn();
$links["Descripcion"]->label="Descripci&oacute;n";

$links["Url"]=new LinkColumn();
$links["Url"]->label="Imagen";
$links["Url"]->url_base_img="server/php/files/thumbnail/";

if(!$superUser){
	$links["idUsuario"]=new LinkColumn();
	$links["idUsuario"]->skip=1;
	
	$links["FechaLog"]=new LinkColumn();
	$links["FechaLog"]->skip=1;
	
	$links["FechaUpdate"]=new LinkColumn();
	$links["FechaUpdate"]->skip=1;		
}

BuildTable('vw_producto',$database_test,$test,$links,$where_clause);

?>
<table border="0" align="center"><tr><td align="center">
<button class="delete ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" type="button" role="button" aria-disabled="false"><span class="ui-button-icon-primary ui-icon ui-icon-trash"></span><span class="ui-button-text">Enviar mails</span></button>
</td></tr>
<table>
</div>
</div>
<div id="border_bottom"></div>
</body>
</html>
