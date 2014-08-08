<?php require_once('Connections/test.php'); ?>
<?php require_once('php/engine.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
<LINK href="css/interactiva.css" rel="stylesheet" type="text/css">
<title>informes</title>
</head>

<body>

<?php
//Prepare links columns
$lc1=new LinkColumn();
$lc1->name="idInforme";
$lc1->url_dest="informe2.php";

$links=Array();
$links[$lc1->name]=$lc1;

BuildTable('Informe',$database_test,$test,$links);
?>
</body>
</html>
