<?php
/*
Class to manage link columns of tables
*/
class LinkColumn
{
	var $new=0;			//Set to one to make new column
	var $skip=0;		//Set to one to skip the field field
	var $hidden=0;		//Set to one to hide the field field
	var $name;			//Column name
	var $label;			//If set translate column label
	var $value;			//Column/field fixed value
	var $default_value;	//Column/field fixed value
	var $url_dest;		//Destination url
	var $url_dest_key;	//Key parameter for destination url
	var $url_base_img;	//Base url for umage ended in /
	var $date_only_year;//Set to 1 to only show year in date
	
	var $table;			//For obtaining the vaues from a form
	var $idField;		//Id field for the select
	var $nameField;		//Name field for the select
	
	var $validatorMsg;	//Message for fields that are required
	
	
}

function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  $theValue = (!get_magic_quotes_gpc()) ? addslashes($theValue) : $theValue;

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
	case "tinyint":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
	case "decimal":
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
    default:
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;  	  
  }
  return $theValue;
}

/*
Function to build forms
*/
function BuildForm($table_name,$database_test,$test,$links,$insertGoTo,$updateGoTo){	

	$eol = PHP_EOL;
	
	//Recordset navigatos
	$currentPage = $_SERVER["PHP_SELF"];
	
	$maxRows_rsInforme = 2;
	$pageNum_rsInforme = 0;
	if (isset($_GET['pageNum_rsInforme'])) {
	  $pageNum_rsInforme = $_GET['pageNum_rsInforme'];
	}
	$startRow_rsInforme = $pageNum_rsInforme * $maxRows_rsInforme;	

	//Prepare columns
	//--------------------------------------------------------------------------------------------------------------------------------

	mysql_select_db($database_test, $test);

	$query_rsInforme = "SELECT COLUMN_NAME,DATA_TYPE,COLUMN_KEY
	FROM INFORMATION_SCHEMA.COLUMNS 
	WHERE TABLE_SCHEMA='" . $database_test . "' 
		AND TABLE_NAME='". $table_name . "'";
		
	$rsInforme = mysql_query($query_rsInforme, $test) or die(mysql_error());
	$row_rsInforme = mysql_fetch_assoc($rsInforme);
	$totalRows_rsInforme = mysql_num_rows($rsInforme);		
	
	$arr = array();
	$arr_key = array();

	$asc_desc="ASC";
	$asc_desc_1="DESC";
	$sort_clause="";
	if (isset($_GET['DESC'])) {
	  $column_sort = $_GET['DESC'];
	  $asc_desc="DESC";
	  $asc_desc_1="ASC";
	}
	else if(isset($_GET['ASC'])) {
	  $column_sort = $_GET['ASC'];
	  $asc_desc="ASC";
	  $asc_desc_1="DESC";
	}
	
	do {
	
		if($sort_clause==""){
			if($column_sort==""){
				$sort_clause="ORDER BY " . $row_rsInforme['COLUMN_NAME'] . " " . $asc_desc;
				$column_sort=$row_rsInforme['COLUMN_NAME'];
			}
			else
			{
				$sort_clause="ORDER BY " . $column_sort . " " . $asc_desc;
			}
		}
		
		$arr[$row_rsInforme['COLUMN_NAME']]=$row_rsInforme['DATA_TYPE'];	
		
		//Store keys
		if(strlen($row_rsInforme['COLUMN_KEY'])>0){
			$arr_key[$row_rsInforme['COLUMN_NAME']]=$row_rsInforme['DATA_TYPE'];	
		}

	} while ($row_rsInforme = mysql_fetch_assoc($rsInforme));

	mysql_free_result($rsInforme);	
	
	//Insert/uodate data
	//--------------------------------------------------------------------------------------------------------------------------------	
	//Validations
	$validated=true;
	$showValidated=false;
	foreach ($links as $clave => $valor){
		if(isset($valor->validatorMsg)){
			if($_POST[$clave]=="")
			{
				$validated=false;
			}
		}
	}
	
	//echo "update" . $_POST["MM_update"] . "<br/>";
	if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {	
		$showValidated=true;
		if($validated){
			$query_Update = "UPDATE " . $table_name ." SET ";
			//Iterate data fields for building update query
			foreach ($arr as $clave => $valor){
				if(!array_key_exists ( $clave , $arr_key )){
					if($valor=="date"){
						$Fecha=$_POST[$clave . "Agno"]."-".$_POST[$clave . "Mes"]."-".$_POST[$clave . "Dia"];
						$query_Update .= sprintf($clave . "=%s,", GetSQLValueString($Fecha, $valor));
					}
					else{
						$query_Update .= sprintf($clave . "=%s,", GetSQLValueString($_POST[$clave], $valor));
					}
				}
			}
			$query_Update = substr($query_Update, 0, -1) . " WHERE ";	
			foreach ($arr_key as $clave => $valor){
				$query_Update .= sprintf($clave . "=%s AND ", GetSQLValueString($_POST[$clave], $valor));
			}
			$query_Update = substr($query_Update, 0, -5);
			//echo $query_Update . "<br/>";	
			
			mysql_select_db($database_test, $test);
			$Result1 = mysql_query($query_Update, $test) or die(mysql_error());
			
			if (isset($_SERVER['QUERY_STRING'])) {
				$updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
				$updateGoTo .= $_SERVER['QUERY_STRING'];
			}

			//Add last insert idd as parameter
			foreach ($arr_key as $clave => $valor){
				if (!isset($_SESSION)) {
				  session_start();
				}
				$_SESSION[$clave] = GetSQLValueString($_POST[$clave], $valor);
				break;
			}	
									
			header(sprintf("Location: %s", $updateGoTo));						
		}//end if validated
	}
	else if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
		$showValidated=true;
		if($validated){
			$query_Insert = "INSERT INTO " . $table_name ." (";
			$query_Insert2 = ") VALUES (";
			//Iterate data fields for building insert query
			foreach ($arr as $clave => $valor){
				if(!array_key_exists ( $clave , $arr_key )){
					$query_Insert .= $clave . ",";
					if($valor=="date"){
						$Fecha=$_POST[$clave . "Agno"]."-".$_POST[$clave . "Mes"]."-".$_POST[$clave . "Dia"];
						$query_Insert2 .= sprintf("%s,", GetSQLValueString($Fecha, $valor));
					}
					else{					
						$query_Insert2 .= sprintf("%s,", GetSQLValueString($_POST[$clave], $valor));
					}
				}
			}
			$query_Insert = substr($query_Insert, 0, -1) . substr($query_Insert2, 0, -1) . ");" . $eol;
			//$query_Insert .= "SELECT LAST_INSERT_ID() AS ID FROM ". $table_name . ";";	
			//echo $query_Insert;
			
			mysql_select_db($database_test, $test);
			$Result1 = mysql_query($query_Insert, $test) or die(mysql_error());
			//$row_Result1 = mysql_fetch_assoc($Result1);
			
			if (isset($_SERVER['QUERY_STRING'])) {
				$insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
				$insertGoTo .= $_SERVER['QUERY_STRING'];
			}
			//Add last insert idd as parameter
			foreach ($arr_key as $clave => $valor){
				$id = mysql_insert_id();
				$insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
				$insertGoTo .= $clave . "=" . $id;					
				break;
			}			

			//Redirect
			header(sprintf("Location: %s", $insertGoTo));					
		}//end if validated
	}	
	
	//Prepare data
	//--------------------------------------------------------------------------------------------------------------------------------
	
	$query_rsInforme = "SELECT * FROM " . $table_name ." WHERE ";
	$emptyFlag=true;
	foreach ($arr_key as $clave => $valor){
		if(isset($_GET[$clave])){
			$emptyFlag=false;
			$query_rsInforme .= sprintf($clave . "=%s AND ", (get_magic_quotes_gpc()) ? $_GET[$clave] : addslashes($_GET[$clave]));
		}
	}
	if($emptyFlag==false){
		$query_rsInforme = substr($query_rsInforme, 0, -5) . "  " . $sort_clause;
		$rsInforme = mysql_query($query_rsInforme, $test) or die(mysql_error());
		$row_rsInforme = mysql_fetch_assoc($rsInforme);		
	}
	else{
		$query_rsInforme = "SELECT * FROM " . $table_name . "  " . $sort_clause;
	}
	
	//echo $query_rsInforme;
	//$query_limit_rsInforme = sprintf("%s LIMIT %d, %d", $query_rsInforme, $startRow_rsInforme, $maxRows_rsInforme);
	
	//For populating selects	
	$client_Javascript = "<script language=\"javascript\">" . $eol;	
	//For selecting values
	$client_Javascript2 = "<script language=\"javascript\">" . $eol;	
	$client_Text = "";
	$client_Text .= "<form id=\"form1\" name=\"form1\" method=\"POST\">". $eol;
	$client_Text .= "<table align=\"center\">" . $eol;
	
	//do {
	
		foreach ($arr as $clave => $valor){
		
			//Check if row has to be collapsed
			if( (isset($links[$clave]->hidden) && $links[$clave]->hidden<>0) ||
				(isset($links[$clave]->skip) && $links[$clave]->skip<>0) ){
				$client_Text .=  "<tr style=\"display: none;\">" . $eol;
			}
			else if(!array_key_exists ( $clave , $arr_key )){
				$client_Text .=  "<tr>" . $eol;
			}
			else{
				$client_Text .=  "<tr style=\"display: none;\">" . $eol;
			}	
			
			//Label
			if( (isset($links[$clave]->hidden) && $links[$clave]->hidden<>0) ||
				(isset($links[$clave]->skip) && $links[$clave]->skip<>0) ){
				//Do nothing
			}			
			else if(!array_key_exists ( $clave , $arr_key )){
				$label = isset($links[$clave]->label) ? $links[$clave]->label : $clave;
				$client_Text .= "<td align=\"left\">" . $label . "</td>" . $eol;
			}
					
			//SELECT VALUES (select value data is in other table)
			if(isset($links[$clave]->skip) && $links[$clave]->skip<>0){
			
			}
			else if(isset($links[$clave]->new) && $links[$clave]->new<>0){
				//Render client html			
				if(isset($links[$clave]->hidden) && $links[$clave]->hidden<>0){
					$client_Text .= "<td><input type=\"hidden\" name=\"" . $clave . "\" value=\"" . (isset($_POST[$clave]) ? $_POST[$clave] : $links[$clave]->value) . "\" />" . 
					"</td>" . $eol;
				}
				else{
					$client_Text .= "<td><input type=\"text\" name=\"" . $clave . "\" value=\"" . (isset($_POST[$clave]) ? $_POST[$clave] : $links[$clave]->value) . "\" />" . 
					"</td>" . $eol;				
				}				
			}
			//Dropbox
			else if(isset($links[$clave]->table)){
				if(isset($links[$clave]->table) && strlen($links[$clave]->table)>0){
					//Prepare javascript code
					$client_Javascript .= "select = document.getElementById(\"" . $links[$clave]->table . "\");" . $eol;
					$client_Javascript .= "select.options[select.options.length] = new Option(\"" . "" . "\",\"" . "" . "\");" . $eol;
					$client_Javascript .= "if(\"" . "" . "\"==selected" . $links[$clave]->table . ") select.selectedIndex = select.options.length-1;" . $eol;
					$query_rsSelect = "SELECT " . $links[$clave]->idField . "," . $links[$clave]->nameField . " FROM " . $links[$clave]->table;
					$rsSelect = mysql_query($query_rsSelect, $test) or die(mysql_error());
					$row_rsSelect = mysql_fetch_assoc($rsSelect);		
					do {
						//Prepare javascript code
						$client_Javascript .= "select.options[select.options.length] = new Option(\"" . $row_rsSelect[$links[$clave]->nameField] . "\",\"" . $row_rsSelect[$links[$clave]->nameField] . "\");" . $eol;
						$client_Javascript .= "if(\"" . $row_rsSelect[$links[$clave]->nameField] . "\"==selected" . $links[$clave]->table . ") select.selectedIndex = select.options.length-1;" . $eol;
					} while ($row_rsSelect = mysql_fetch_assoc($rsSelect));	
					//Render client html
					$client_Text .= "<td align=\"left\"><select name=\"" . $links[$clave]->table . "\" id=\"" . $links[$clave]->table . "\"></select>" .
					"<span style=\"color: #FF0000\">" . ($showValidated==true && $validated==false && isset($links[$clave]->validatorMsg) ? $links[$clave]->validatorMsg : "") . "</span>" . "</td>" .
					$eol;	
					//Selected value
					$client_Javascript2 .= "var selected" . $links[$clave]->table . "=" . (isset($row_rsInforme[$clave]) ? "\"" . $row_rsInforme[$clave] . "\"" : ($_POST[$clave]!="" ? "\"" . $_POST[$clave] . "\"" : "\"\"")) . ";" . $eol;
				}
			}
			//Date
			else if($valor=="date"){
				//Prepare javascript code
				$client_Javascript .= "select = document.getElementById(\"" . $clave . "Dia" . "\");" . $eol;
				$client_Javascript .= "select.options[select.options.length] = new Option(\"" . "" . "\",\"" . "" . "\");" . $eol;
				for($i=1;$i<=31;$i++) {
					$client_Javascript .= "select.options[select.options.length] = new Option(\"" . $i . "\",\"" . $i . "\");" . $eol;
					$client_Javascript .= "if(" . $i . "==selected" . $clave . "Dia" . ") select.selectedIndex = select.options.length-1;" . $eol;	
				}
				$client_Javascript .= "select = document.getElementById(\"" . $clave . "Mes" . "\");" . $eol;
				$client_Javascript .= "select.options[select.options.length] = new Option(\"" . "" . "\",\"" . "" . "\");" . $eol;
				for($i=1;$i<=12;$i++) {
					$client_Javascript .= "select.options[select.options.length] = new Option(\"" . $i . "\",\"" . $i . "\");" . $eol;
					$client_Javascript .= "if(" . $i . "==selected" . $clave . "Mes" . ") select.selectedIndex = select.options.length-1;" . $eol;	
				}
				$client_Javascript .= "select = document.getElementById(\"" . $clave . "Agno" . "\");" . $eol;
				$client_Javascript .= "select.options[select.options.length] = new Option(\"" . "" . "\",\"" . "" . "\");" . $eol;				
				$year=date('Y');
				for($i=$year;$i>=$year-100;$i--) {
					$client_Javascript .= "select.options[select.options.length] = new Option(\"" . $i . "\",\"" . $i . "\");" . $eol;
					$client_Javascript .= "if(\"" . $i . "\"==selected" . $clave . "Agno" . ") select.selectedIndex = select.options.length-1;" . $eol;					
				}				
				
				//Selected value
				//echo "debug=".$row_rsInforme[$clave].$links[$clave]->date_only_year;
				$client_Javascript2 .= "var selected" . $clave . "Agno" . "=" . (isset($row_rsInforme[$clave]) && $row_rsInforme[$clave]!="0000-00-00" ? "\"" . substr($row_rsInforme[$clave],0,4) . "\"" : ($_POST[$clave. "Agno"]!="" ? "\"" . $_POST[$clave. "Agno"] . "\"" : "\"\"")) . ";" . $eol;
				$client_Javascript2 .= "var selected" . $clave . "Mes" . "=" . (isset($row_rsInforme[$clave]) && $row_rsInforme[$clave]!="0000-00-00" ? "\"" . substr($row_rsInforme[$clave],5,2) . "\"" : ($_POST[$clave. "Mes"]!="" ? "\"" . $_POST[$clave. "Mes"] . "\"" : (isset($links[$clave]->date_only_year) ? "\"1\"" : "\"\""))) . ";" . $eol;
				$client_Javascript2 .= "var selected" . $clave . "Dia" . "=" . (isset($row_rsInforme[$clave]) && $row_rsInforme[$clave]!="0000-00-00" ? "\"" . substr($row_rsInforme[$clave],8,2) . "\"" : ($_POST[$clave. "Dia"]!="" ? "\"" . $_POST[$clave. "Dia"] . "\"" : (isset($links[$clave]->date_only_year) ? "\"1\"" : "\"\""))) . ";" . $eol;								
																	
				$client_Text .= "<td align=\"left\">";
				if(isset($links[$clave]->date_only_year)){
					$client_Text .= "A&ntilde;o:
					<select name=\"" . $clave . "Agno\" id=\"" . $clave . "Agno\" ></select>";			
					$client_Text .= "
					" . (isset($links[$clave]->date_only_year) ? "" : "D&iacute;a:") . "
					<select name=\"" . $clave . "Dia\" id=\"" . $clave . "Dia\" " . (isset($links[$clave]->date_only_year) ? "style=\"visibility:hidden\"" : "") . "></select>
					" . (isset($links[$clave]->date_only_year) ? "" : "Mes:") . "				
					<select name=\"" . $clave . "Mes\" id=\"" . $clave . "Mes\" " . (isset($links[$clave]->date_only_year) ? "style=\"visibility:hidden\"" : "") . "></select>" . 
					"<span style=\"color: #FF0000\">" . ($showValidated==true && $validated==false && isset($links[$clave]->validatorMsg) ? $links[$clave]->validatorMsg : "") . "</span>" . "</td>" . $eol;						
				}
				else{
					$client_Text .= "
					" . (isset($links[$clave]->date_only_year) ? "" : "D&iacute;a:") . "
					<select name=\"" . $clave . "Dia\" id=\"" . $clave . "Dia\" " . (isset($links[$clave]->date_only_year) ? "style=\"visibility:hidden\"" : "") . "></select>
					" . (isset($links[$clave]->date_only_year) ? "" : "Mes:") . "				
					<select name=\"" . $clave . "Mes\" id=\"" . $clave . "Mes\" " . (isset($links[$clave]->date_only_year) ? "style=\"visibility:hidden\"" : "") . "></select>";
					$client_Text .= "A&ntilde;o:
					<select name=\"" . $clave . "Agno\" id=\"" . $clave . "Agno\" ></select>" . 
					"<span style=\"color: #FF0000\">" . ($showValidated==true && $validated==false && isset($links[$clave]->validatorMsg) ? $links[$clave]->validatorMsg : ""). "</span>" . "</td>" . $eol;
				}		
			
			}
			//Radio button YES/NO
			else if($valor=="tinyint"){
				//Render client html	
				$client_Text .= "<td align=\"left\">		
				<label>
				  <input type=\"radio\" name=\"" . $clave . "\" value=\"1\"" . ((isset($_POST[$clave]) ? $_POST[$clave] : $row_rsInforme[$clave])<>'0'  ? 'checked="checked"' : '') . "/>
				  SI</label>
				<label>	
				  <input type=\"radio\" name=\"" .  $clave . "\" value=\"0\"" . ((isset($_POST[$clave]) ? $_POST[$clave] : $row_rsInforme[$clave])=='0' || (isset($row_rsInforme[$clave])==false && isset($_POST[$clave])==false)? 'checked="checked"' : '') . "/>
				  NO</label>" . "</td>" . $eol;		
			}
			//Hidden key
			else if(array_key_exists ( $clave , $arr_key )){
				//Render client html			
				$client_Text .= "<td><input type=\"hidden\" name=\"" . $clave . "\" value=\"" . (isset($_POST[$clave]) ? $_POST[$clave] : $row_rsInforme[$clave]) . "\" />" . 
				"</td>" . $eol;			
			}
			//Password
			else if(isset($links[$clave]->password) && $links[$clave]->password<>0){
				$client_Text .= "<td align=\"left\"><input type=\"text\" name=\"" . $clave . "\" value=\"" . (isset($_POST[$clave]) ? $_POST[$clave] : (isset($links[$clave]->default_value) ? $links[$clave]->default_value : $row_rsInforme[$clave]) ) . "\" />" . 
				"<span style=\"color: #FF0000\">" . ($showValidated==true && $validated==false && isset($links[$clave]->validatorMsg) ? $links[$clave]->validatorMsg : ""). "</span>" . "</td>" . $eol;
				$client_Text .= "<td align=\"left\"><input type=\"text\" name=\"" . $clave . "2" . "\" value=\"" . (isset($_POST[$clave]) ? $_POST[$clave] : (isset($links[$clave]->default_value) ? $links[$clave]->default_value : $row_rsInforme[$clave]) ) . "\" />" . $eol;
			}
			//Normal and hidden text fields
			else{
				//Render client html			
				if(isset($links[$clave]->hidden) && $links[$clave]->hidden<>0){
					$client_Text .= "<td colspan=\"2\"><input type=\"hidden\" name=\"" . $clave . "\" value=\"" . (isset($_POST[$clave]) ? $_POST[$clave] : (isset($links[$clave]->default_value) ? $links[$clave]->default_value : $row_rsInforme[$clave]) ) . "\" />" . 
					"</td>" . $eol;
				}
				else{							
					$client_Text .= "<td align=\"left\"><input type=\"text\" name=\"" . $clave . "\" value=\"" . (isset($_POST[$clave]) ? $_POST[$clave] : (isset($links[$clave]->default_value) ? $links[$clave]->default_value : $row_rsInforme[$clave]) ) . "\" />" . 
					"<span style=\"color: #FF0000\">" . ($showValidated==true && $validated==false && isset($links[$clave]->validatorMsg) ? $links[$clave]->validatorMsg : ""). "</span>" . "</td>" . $eol;
				}
			}
			
			$client_Text .= "</tr>".$eol;
						
		}
	
	//} while ($row_rsInforme = mysql_fetch_assoc($rsInforme));
	
	//Finalize client html
  	$client_Text .= "<tr><td colspan=\"2\" align=\"center\">".$eol;
  	$client_Text .= "<input type=\"button\" name=\"Submit\" value=\"Enviar\" onClick=\"javascript:doSubmit();\" />".$eol;
  	$client_Text .= "</td></tr>".$eol;
	$client_Text .= "</table>".$eol;
	$client_Text .= "<br/>";
	$client_Text .= "<input type=\"hidden\" name=\"MM_insert\" id=\"MM_insert\" value=\"\">". $eol;
	$client_Text .= "<input type=\"hidden\" name=\"MM_update\" id=\"MM_update\" value=\"\">". $eol;
	$client_Text .= "<input type=\"hidden\" name=\"MM_cancel\" id=\"MM_cancel\" value=\"\">". $eol;
	$client_Text .= "</form>". $eol;	
	
	//Finalize javascript
	$client_Javascript .= "</script>" . $eol;	
	$client_Javascript2 .= "</script>" . $eol;	

	//Free sql result
	mysql_free_result($rsInforme);
	
	//Render
	foreach ($arr_key as $clave => $valor){
		break;
	}
		
	$client_Total=
	$client_Text		
	. $client_Javascript2
	. $client_Javascript
	. "<script language=\"javascript\">
	function doSubmit(){
		var input = document.getElementById(\"" . (isset($_GET[$clave]) ? "MM_update" : "MM_insert") . "\");
		input.value=\"form1\";
		form1.submit();
	}
	</script>
	<script language=\"javascript\">
	function doCancel(){
		var input = document.getElementById(\"" . "MM_cancel" . "\");
		input.value=\"form1\";
		form1.submit();
	}
	</script>
	";	
	
	return $client_Total;
}

/*
Function to build tables
Parameters:
table_name		database table name
database_test	database
test			connection
links			array of LinkColumn
*/
function BuildTable($table_name,$database_test,$test,$links,$where_clause){	

	$eol = PHP_EOL;
	
	//Recordset navigatos
	$currentPage = $_SERVER["PHP_SELF"];
	
	$maxRows_rsInforme = 6;
	$pageNum_rsInforme = 0;
	if (isset($_GET['pageNum_rsInforme'])) {
	  $pageNum_rsInforme = $_GET['pageNum_rsInforme'];
	}
	$startRow_rsInforme = $pageNum_rsInforme * $maxRows_rsInforme;

	//Filter from other page
	/*
	$colname_rsInforme = "-1";
	if (isset($_GET['idInforme'])) {
	  $colname_rsInforme = (get_magic_quotes_gpc()) ? $_GET['idInforme'] : addslashes($_GET['idInforme']);
	}
	*/
	//mysql_select_db($database_test, $test);
	
	//Prepare columns
	//--------------------------------------------------------------------------------------------------------------------------------

	mysql_select_db($database_test, $test);

	$query_rsInforme = "SELECT COLUMN_NAME,DATA_TYPE,COLUMN_KEY
	FROM INFORMATION_SCHEMA.COLUMNS 
	WHERE TABLE_SCHEMA='" . $database_test . "' 
		AND TABLE_NAME='". $table_name . "'";
		
	$rsInforme = mysql_query($query_rsInforme, $test) or die(mysql_error());
	$row_rsInforme = mysql_fetch_assoc($rsInforme);
	$totalRows_rsInforme = mysql_num_rows($rsInforme);	
		
	echo "<table align=\"center\" class=\"list\">"."<tr>".$eol;

	$arr = array();

	$asc_desc="ASC";
	$asc_desc_1="DESC";
	$sort_clause="";
	if (isset($_GET['DESC'])) {
	  $column_sort = $_GET['DESC'];
	  $asc_desc="DESC";
	  $asc_desc_1="ASC";
	}
	else if(isset($_GET['ASC'])) {
	  $column_sort = $_GET['ASC'];
	  $asc_desc="ASC";
	  $asc_desc_1="DESC";
	}
	
	do {

		//echo $row_rsInforme['COLUMN_NAME'].'-'.$row_rsInforme['DATA_TYPE'].'-'.$row_rsInforme['COLUMN_KEY'].'<br/>';
		//echo $row_rsInformes['CONSTRAINT_NAME'].'<br/>';
	
		if($sort_clause==""){
			if($column_sort==""){
				$sort_clause="ORDER BY " . $row_rsInforme['COLUMN_NAME'] . " " . $asc_desc;
				$column_sort=$row_rsInforme['COLUMN_NAME'];
			}
			else
			{
				$sort_clause="ORDER BY " . $column_sort . " " . $asc_desc;
			}
		}
		
		$clave = $row_rsInforme['COLUMN_NAME'];
		$label = isset($links[$clave]->label) ? $links[$clave]->label : $clave;
		
		if(isset($links[$clave]->skip) && $links[$clave]->skip<>0){
			//skip
		}	
		else if($row_rsInforme['COLUMN_NAME'] == $column_sort){
			echo "<td><a href=\"?" . $asc_desc_1 . "=" . $row_rsInforme['COLUMN_NAME'] . "\">" . $label . "</a></td>" . $eol;
		}	
		else
		{
			echo "<td><a href=\"?ASC=" . $row_rsInforme['COLUMN_NAME'] . "\">" . $label . "</a></td>" . $eol;
		}
		
		$arr[$row_rsInforme['COLUMN_NAME']]=$row_rsInforme['DATA_TYPE'];	

	} while ($row_rsInforme = mysql_fetch_assoc($rsInforme));

	echo "</tr>".$eol;

	mysql_free_result($rsInforme);
	
	//Prepare data
	//--------------------------------------------------------------------------------------------------------------------------------
	$query_rsInforme = "SELECT *
	FROM ". $table_name . " " . $where_clause . " " . $sort_clause;

	$query_rsInforme = sprintf("SELECT * FROM ". $table_name . " " . $where_clause . " " . $sort_clause, $colname_rsInforme);
	//echo $query_rsInforme;
	$query_limit_rsInforme = sprintf("%s LIMIT %d, %d", $query_rsInforme, $startRow_rsInforme, $maxRows_rsInforme);
	$rsInforme = mysql_query($query_limit_rsInforme, $test) or die(mysql_error());
	$row_rsInforme = mysql_fetch_assoc($rsInforme);
	
	//Do page number
	if (isset($_GET['totalRows_rsInforme'])) {
	  $totalRows_rsInforme = $_GET['totalRows_rsInforme'];
	} else {
	  $all_rsInforme = mysql_query($query_rsInforme);
	  $totalRows_rsInforme = mysql_num_rows($all_rsInforme);
	}
	$totalPages_rsInforme = ceil($totalRows_rsInforme/$maxRows_rsInforme)-1;

	$queryString_rsInforme = "";
	if (!empty($_SERVER['QUERY_STRING'])) {
	  $params = explode("&", $_SERVER['QUERY_STRING']);
	  $newParams = array();
	  foreach ($params as $param) {
		if (stristr($param, "pageNum_rsInforme") == false && 
			stristr($param, "totalRows_rsInforme") == false) {
		  array_push($newParams, $param);
		}
	  }
	  if (count($newParams) != 0) {
		$queryString_rsInforme = "&" . htmlentities(implode("&", $newParams));
	  }
	}
	$queryString_rsInforme = sprintf("&totalRows_rsInforme=%d%s", $totalRows_rsInforme, $queryString_rsInforme);
	
	//echo $query_rsInforme;
	
	//$rsInforme = mysql_query($query_rsInforme, $test) or die(mysql_error());
	//$row_rsInforme = mysql_fetch_assoc($rsInforme);
	//$totalRows_rsInforme = mysql_num_rows($rsInforme);
	
	do {
	
		echo "<tr>".$eol;
		foreach ($arr as $clave => $valor){
			if(isset($links[$clave]->skip) && $links[$clave]->skip<>0){
				//skip
			}
			//image
			else if(isset($links[$clave]->url_base_img)){
				if(isset($links[$clave]->url_dest_key)){
					echo "<td>" . "<a href=\"" . $links[$clave]->url_dest . "?" . $links[$clave]->url_dest_key . "=" . $row_rsInforme[$links[$clave]->url_dest_key] . "\">" . "<img src=\"" . $links[$clave]->url_base_img . $row_rsInforme[$clave] . "\"/>" . "</a></td>" . $eol;	
				}
				else
				{			
					echo "<td>" . "<img src=\"" . $links[$clave]->url_base_img . $row_rsInforme[$clave] . "\"/>" . "</td>" . $eol;
				}
			}			
			else if(isset($links[$clave]->url_dest)){
				if(isset($links[$clave]->url_dest_key)){
					echo "<td>" . "<a href=\"" . $links[$clave]->url_dest . "?" . $links[$clave]->url_dest_key . "=" . $row_rsInforme[$links[$clave]->url_dest_key] . "\">" . $row_rsInforme[$clave] . "</a></td>" . $eol;	
				}
				else
				{
					echo "<td>" . "<a href=\"" . $links[$clave]->url_dest . "?" . $clave . "=" . $row_rsInforme[$clave] . "\">" . $row_rsInforme[$clave] . "</a></td>" . $eol;	
				}		
			}			
			else{
				echo "<td>" . $row_rsInforme[$clave] . "</td>" . $eol;
			}
		}
	
	echo "</tr>".$eol;
	
	} while ($row_rsInforme = mysql_fetch_assoc($rsInforme));
	
	echo "</table>".$eol;
	
	echo "<br/>";
	
	//Recordset navigator
	//--------------------------------------------------------------------------------------------------------------------------------

	//echo $pageNum_rsInforme . $totalPages_rsInforme . $eol;
	
	echo "<table border=\"0\" width=\"50%\" align=\"center\" class=\"rec_nav\">" . $eol;	
	echo "<tr>" . $eol;
	echo "<td width=\"23%\" align=\"center\">" . $eol;
	if ($pageNum_rsInforme > 0) {
		printf("<a href=\"%s?pageNum_rsInforme=%d%s\">First</a>" . $eol, $currentPage, 0, $queryString_rsInforme);
	}
	echo "</td>" . $eol;
	echo "<td width=\"31%\" align=\"center\">" . $eol;
	if ($pageNum_rsInforme > 0) {
		printf("<a href=\"%s?pageNum_rsInforme=%d%s\">Previous</a>" . $eol, $currentPage, max(0, $pageNum_rsInforme - 1), $queryString_rsInforme);
	}	
	echo "</td>" . $eol;
	echo "<td width=\"23%\" align=\"center\">" . $eol;
	if ($pageNum_rsInforme < $totalPages_rsInforme) { 
		printf("<a href=\"%s?pageNum_rsInforme=%d%s\">Next</a>" . $eol, $currentPage, min($totalPages_rsInforme, $pageNum_rsInforme + 1),$queryString_rsInforme);
	}
	echo "</td>" . $eol;
	echo "<td width=\"31%\" align=\"center\">" . $eol;
	if ($pageNum_rsInforme < $totalPages_rsInforme) {
		printf("<a href=\"%s?pageNum_rsInforme=%d%s\">Last</a>" . $eol, $currentPage, $totalPages_rsInforme, $queryString_rsInforme);
	}
	echo "</td>" . $eol;	
	echo "</tr>" . $eol;
	echo "<tr><td align=\"center\" colspan=\"4\">" . $eol;
	echo "Records " . ($startRow_rsInforme + 1) . " to " . min($startRow_rsInforme + $maxRows_rsInforme, $totalRows_rsInforme) . " of " . $totalRows_rsInforme . $eol;
	echo "</td></tr>" . $eol;	
	echo "</table>" . $eol;
	
	mysql_free_result($rsInforme);
	
}
?>