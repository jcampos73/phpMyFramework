<?php
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}

// ** Logout the current user. **
$logoutAction = $_SERVER['PHP_SELF']."?doLogout=true";
if ((isset($_SERVER['QUERY_STRING'])) && ($_SERVER['QUERY_STRING'] != "")){
  $logoutAction .="&". htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
  //to fully log out a visitor we need to clear the session varialbles
  $_SESSION['MM_Username'] = NULL;
  $_SESSION['MM_UserGroup'] = NULL;
  $_SESSION['PrevUrl'] = NULL;
  unset($_SESSION['MM_Username']);
  unset($_SESSION['MM_UserGroup']);
  unset($_SESSION['PrevUrl']);
	
  $logoutGoTo = "login.php";
  if ($logoutGoTo) {
    header("Location: $logoutGoTo");
    exit;
  }
}
?>
<?php
function prepareParam($filename)
{
	$array_param = array(
		"proyectos.php" => array("idCliente"),
		"trabajos.php" => array("idCliente","idProyecto"),
		"producto.php" => array("idProducto")
	);

	$paramStr="";

	if(isset($array_param[$filename])){

		for($i=0;$i<count($array_param[$filename]);$i++){	
			if(isset($_GET[ $array_param[$filename][$i] ])){
				if($paramStr==""){
					$paramStr.="?";
				}
				else{
					$paramStr.="&";
				}
				$paramStr.=$array_param[$filename][$i]."=".$_GET[$array_param[$filename][$i]];
			}
		}
	}
	return $paramStr;
}
?>
<?php
// carriage return type (we use a PHP end of line constant)
$eol = PHP_EOL;

$array_back = array(
    "informes.php" => "index.php",
    "clientes.php" => "index.php",
	"proyectos.php" => "clientes.php",
	"trabajos.php" => "proyectos.php",
	"usuarios.php" => "index.php",
	"productos.php" => "index.php",
	"producto.php" => "productos.php",
	"upload.php" => "producto.php",
	"listado_mail.php" => "index.php"
);

$array_new = array(
    "informes.php" => "informe.php",
    "clientes.php" => "cliente.php",
	"proyectos.php" => "proyecto.php",
	"trabajos.php" => "trabajo.php",
	"usuarios.php" => "usuario.php",
	"productos.php" => "producto.php",
	"listado_mail.php" => "producto.php"
);

$array_title = array(
	"index.php" => "indice",
    "informes.php" => "informes",
    "clientes.php" => "clientes",
	"proyectos.php" => "proyectos",
	"trabajos.php" => "trabajos",
	"usuarios.php" => "usuarios",
	"productos.php" => "mis veh&iacute;culos",
	"producto.php" => "alta-mod. veh&iacute;culo",
	"upload.php" => "upload",
	"listado_mail.php" => "listado mails"
);

$filename = $_SERVER['PHP_SELF'];
$arrStr = explode("/", $filename );
$arrStr = array_reverse($arrStr ); 
$filename = $arrStr[0];
//echo $filename . "<br/>";
$filename_back = $array_back[$filename];
$filename_new = $array_new[$filename];
//echo $filename_back . "<br/>";
//echo $filename_new . "<br/>";

//Prepare get parameter
//foreach($_GET as $query_string_variable => $value) {
   //echo "$query_string_variable  = $value <Br />";
//}

//Prepare path
$file_path = "<a href=\"" . $filename . prepareParam($filename)  . "\" style=\"font-size: 1.2em;\">" . "<b>" . $array_title[$filename] . "</b>" . "</a>";

$file_path_part = $filename_back;


while(isset($file_path_part) && $file_path_part != ""){
	$file_path = "<a href=\"" . $file_path_part . prepareParam($file_path_part) . "\">" . $array_title[$file_path_part] . "</a>" . "&nbsp;/&nbsp;" . $file_path ;
	$file_path_part = $array_back[$file_path_part];
}

$heading_begin="
<!--Filter-->
<table border=\"0\" align=\"center\" class=\"heading\">
  <tr>
  	<td colspan=\"3\" rowspan=\"2\" align=\"left\"><a href=\"http://" . $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]) . "\"/><img src=\"images/mylogo.png\" alt=\"mylogo\" height=\"100px\"/></a></td>
  </tr>
";

$sel_1=($filename=="index.php" ? "selected" : "");
$sel_2=($filename=="producto.php" ? "selected" : "");
$sel_3=($filename=="productos.php" ? "selected" : "");

$heading_end="
  <!--
  <tr>
    <td colspan=\"1\" align=\"center\"><a href=\"" . $filename_back . prepareParam($filename_back) . "\"><img src=\"images/back.gif\"/ width=\"48\"></a></td>
	<!--<td colspan=\"1\" align=\"center\"><a href=\"" . $filename_new  . prepareParam($filename) . "\"><img src=\"images/new_item.gif\" width=\"48\"/></a></td>
	<td colspan=\"1\" align=\"center\"><a href=\"" . $logoutAction . "\"><img src=\"images/log_out.gif\"/ width=\"48\"></a></td>
  </tr>
  -->
  <tr>	
  	<!--<td colspan=\"1\" align=\"center\"><a href=\"" . $filename_back . prepareParam($filename_back) . "\">Atr&aacute;s</a>
	</td>-->
	<!--<td colspan=\"1\" align=\"center\">
	<a href=\"" . $filename_new  . prepareParam($filename) . "\">Nuevo</a>
	</td>-->
	<td colspan=\"1\" align=\"center\">
	" . (isset($_SESSION['MM_Username']) ? "Bienvenido <b>" . $_SESSION['MM_Username'] . "</b>" : "") . "</td>	
	<td colspan=\"1\" align=\"center\">
	<a href=\"" . "normativa.php" . "\" target=\"_blank\">Normativa de publicaci&oacute;n</a></td>		
	<td colspan=\"1\" align=\"center\">
	<a href=\"" . "myarea.php" . "\">Mi &aacute;rea</a></td>	
	<td colspan=\"1\" align=\"center\"><!--&nbsp;|&nbsp;-->
	<a href=\"" . $logoutAction . "\">" . (isset($_SESSION['MM_Username']) ? "Log out" : "") . "</a></td>
  </tr> 
  <tr>
  	<td colspan=\"6\" align=\"center\">" . $file_path . "</td>
  </tr>
</table>

<!--<div id=\"container\">-->
<div id=\"tabs\">
<div id=\"nav\">
<ul>
	<li id=\"inicio\" class=\"ini\">
	<a class=\"" . $sel_1 . "\" href=\"index.php\">Inicio</a>
	</li>
	<li id=\"insertar\" class=\"\">
	<a class=\"" . $sel_2 . "\" href=\"producto.php\">Insertar veh&iacute;culo</a>
	</li>
	<li id=\"listado\" class=\"\">
	<a class=\"" . $sel_3 . "\" href=\"productos.php\">Listado de veh&iacute;culos</a>
	</li>
</ul>
</div>
</div>  
<div class=\"subnav_main\" id=\"_ctl0_Header_subnav_main\">  
</div>
<!--</div>--> 


";

/*echo "<tr>" . $eol .
	"<td colspan=\"2\" align=\"center\"><a href=\"clientes.php\">Atr&aacute;s</a>&nbsp;|&nbsp;<a href=\"proyecto.php?idCliente=" . $_GET['idCliente'] . "\">Nuevo</a>&nbsp;|&nbsp;<a href=\"" . $logoutAction . "\">Log out</a></td>" . $eol .
	"</tr>" . $eol;*/


?>