<?php require_once('Connections/test.php'); ?>
<?php require_once('php/engine.php'); ?>
<?php require_once('php/heading.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

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
//Prepare links columns
//$lc1=new LinkColumn();
//$lc1->name="EstadoCivil";
//$lc1->table="EstadoCivil";
//$lc1->idField="idEstadoCivil";
//$lc1->nameField="EstadoCivil";

$links=Array();
$links[$lc1->name]=$lc1;

$links["Usuario"]=new LinkColumn();
$links["Usuario"]->validatorMsg="Debe introducir su nombre.";

$MM_superUsers="admin";
if (in_array($MM_superUsers, Explode(",", $_SESSION['MM_UserGroup'])))
{ 
	//Do nothing
}
else
{
	$links["idNivel"]=new LinkColumn();
	$links["idNivel"]->skip=1;	
} 
	
$result = BuildForm('Usuario',$database_test,$test,$links,"usuarios.php","usuarios.php");
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
<?php echo $heading_begin; ?>
<?php echo $heading_end; ?>
<br/>

<?php
echo $result;
?>
</div>
</div>
</body>
</html>
