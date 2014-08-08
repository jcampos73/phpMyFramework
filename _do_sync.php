<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once('Connections/test.php');
//require_once('Connections/stcmotores.php');
?>
<?php

function __DoSync($database_test, $test, $only_query=false, $arr_keys_org=array())
{	
	//Debug param
	$param_debug=1;//1, debug; 0,prod
	$param_only_onetable=true;
	$param_counter_table=5;
	$param_only_onerow=true;
	
	if($param_debug==0){
		require_once('Connections/stcmotores.php');
	}

	//Global variables
	//Prepare array of ids
	$arr_keys=array();
	//$arr_keys_org=array();
	$counter_table=0;
	
	mysql_select_db($database_test, $test);

	$query_rsMappingTable = "select distinct pos,sys_dest,table_org,table_dest from __MappingColumn order by pos asc";
		
	$rsMappingTable = mysql_query($query_rsMappingTable, $test) or die(mysql_error());
	$row_rsMappingTable = mysql_fetch_assoc($rsMappingTable);
	$totalRows_rsMappingTable = mysql_num_rows($rsMappingTable);
	

	//Loop through tables to synchronize
	do {
		//Local variables to loop 1
		$pos=$row_rsMappingTable['pos'];
		$table_org=$row_rsMappingTable['table_org'];
		$table_dest=$row_rsMappingTable['table_dest'];
		$sys_dest=$row_rsMappingTable['sys_dest'];
		
		echo "pos=" . $pos . "<br/>";
	
		//Query columns to synchronize
		$query_rsMappingColumn =
			sprintf("select do_proc,column_org,column_dest,key_table,default_val from __MappingColumn where sys_dest='%s' and table_org='%s' and table_dest='%s' order by pos_table",
			$sys_dest,
			$table_org,
			$table_dest);
			
		$rsMappingColumn = mysql_query($query_rsMappingColumn, $test) or die(mysql_error());			
		$row_rsMappingColumn = mysql_fetch_assoc($rsMappingColumn);
		$totalRows_rsMappingColumn = mysql_num_rows($rsMappingColumn);		
		
		//Variables for building queries
		$query_select="";
		$query_insertupdate1="";
		$query_insertupdate2="%s";
		$query_insertupdate_tot="insert into %s (%s) values (%s)";
		
		//Prepare array of columns
		$arr_columns=array();	
		$arr_values=array();
		$arr_dest_to_org=array();	
		$counter_column=0;		
		
		do{
			//Local variables to loop 2
			$do_proc=$row_rsMappingColumn['do_proc'];
			$column_org=$row_rsMappingColumn['column_org'];
			$column_dest=$row_rsMappingColumn['column_dest'];
			$key_table=$row_rsMappingColumn['key_table'];
			$default_val=$row_rsMappingColumn['default_val'];
			
			//Process one column
			if(isset($do_proc) && $do_proc!=0){

					
				//Check if it is a key column
				if(isset($key_table) && $key_table!=""){
					if($key_table==$table_dest){
						//Do nothing

				
					}
					else
					{
						if($query_insertupdate1 != ""){
							$query_insertupdate1 .= ",";
						}
						$query_insertupdate1.=$column_dest;
						//echo "key_table=" . $key_table . "<br/>";
						$arr_values[$column_dest]=$arr_keys[$key_table];
					}
				}
				else
				{
					//Prepare insert/update query
					if($query_insertupdate1 != ""){
						$query_insertupdate1 .= ",";
					}
					$query_insertupdate1.=$column_dest;		

					if(isset($column_org) && $column_org!=""){
						//Map column origin to destination
						$arr_dest_to_org[$column_dest]=$column_org;					
						$arr_columns[$counter_column]=$column_org;
						$arr_values[$column_dest]="";
					}
					else
					{
						//We have a fixed value
						$arr_values[$column_dest]=$default_val;
					}
					

				}
			}
		
			//Increment column counter
			$counter_column++;
		} while ($row_rsMappingColumn = mysql_fetch_assoc($rsMappingColumn));
		
		//Prepare query for insert/update
		$query_insertupdate_tot=sprintf($query_insertupdate_tot,
			$table_dest,
			$query_insertupdate1,
			$query_insertupdate2);
							
		//echo "query=" . $query_insertupdate_tot . "<br/>";
		
		//Ok we got the query go with the insert/update
		//--------------------------------------------------------------------------------------------
		//--------------------------------------------------------------------------------------------
		//Select source rows
		$query_rsSourceTable = "select %s from %s";
		$select_columns="";
		
		//Insert into key table only for pos=1 (first table of hierarchy)
		$source_key="";
		if($pos==1){
			//Secho "pos=" . $pos . "<br/>";
			$query_rsSourceTableKey = "SELECT COLUMN_NAME,DATA_TYPE,COLUMN_KEY
			FROM INFORMATION_SCHEMA.COLUMNS 
			WHERE TABLE_SCHEMA='" . $database_test . "' 
				AND TABLE_NAME='". $table_org . "' AND COLUMN_KEY = 'PRI'";	
			//echo "query=" . $query_rsSourceTableKey. "<br/>";
			$rsSourceTableKey = mysql_query($query_rsSourceTableKey, $test) or die(mysql_error());			
			$row_rsSourceTableKey = mysql_fetch_assoc($rsSourceTableKey);
			$totalRows_rsSourceTableKey = mysql_num_rows($rsSourceTableKey);	
			do{
				if($select_columns!=""){
					$select_columns.= ",";
				}
				else
				{
					$source_key=$row_rsSourceTableKey['COLUMN_NAME'];
				}
				$select_columns.= $row_rsSourceTableKey['COLUMN_NAME'];
			} while ($row_rsSourceTableKey = mysql_fetch_assoc($rsSourceTableKey));
			mysql_free_result($rsSourceTableKey);			
		}				
		
		//Build select query for source table
		if($select_columns=="" && empty($arr_columns))
		{
			$query_rsSourceTable = sprintf($query_rsSourceTable,"*",$table_org);
		}
		else
		{
			$arr_added_columns=array();
			foreach ($arr_columns as $clave => $valor)
			{
				//if(in_array($valor,$arr_added_columns)==false){
					if($select_columns!="") $select_columns.= ",";
					$select_columns.= $valor;
					$arr_added_columns[$clave]=$valor;
				//}
			}
			$query_rsSourceTable = sprintf($query_rsSourceTable,$select_columns,$table_org);	
		}
		
		//Now where
		$where="";
		foreach ($arr_keys_org as $clave => $valor)
		{
			if($where=="")
			{
				$where.=" where ";
			}
			else if($where!=" where ")
			{
				$where.=" and ";
			}
			$where.=sprintf("%s=%s",$clave,$valor);
		}			
		$query_rsSourceTable.=$where;
		
		//Debug
		echo "query=" . $query_rsSourceTable . "<br/>";
		
		if($only_query==true){
			return $query_rsSourceTable;
		}
		
		//Queery source table
		$rsSourceTable = mysql_query($query_rsSourceTable, $test);// or die(mysql_error());			
		$row_rsSourceTable = mysql_fetch_assoc($rsSourceTable);
		$totalRows_rsSourceTable = mysql_num_rows($rsSourceTable);			
		do
		{
			if($source_key!=""){
				$arr_keys_org[$source_key]=$row_rsSourceTable[$source_key];
			}
			//Check if we got that key processed, if so continue
			if($pos==1){
				$query_rsMappingIdTable=sprintf("select * from %s where sys_dest='%s' and table_org='%s' and table_dest='%s' and id_org=%s",
					"__MappingId",
					$sys_dest,
					$table_org,
					$table_dest,
					$row_rsSourceTable[$source_key]);
					
				$rsMappingIdTable = mysql_query($query_rsMappingIdTable, $test);
				$row_rsMappingIdTable = mysql_fetch_assoc($rsMappingIdTable);
				$totalRows_rsMappingIdTablee = mysql_num_rows($rsMappingIdTable);							
				mysql_free_result($rsMappingIdTable);
				echo "query=" . $query_rsMappingIdTable . "<br/>";						
				if($totalRows_rsMappingIdTablee>0){
					echo "continue" . "<br/>";						
					continue;
				}
			}
			
			$arr_columns = explode(',', $query_insertupdate1);
			if(empty($arr_columns))
			{
				//Do nothing use query as it is
			}
			else
			{
				$insert_values="";
				foreach ($arr_columns as $clave => $valor)
				{
					if($insert_values!="") $insert_values.= ",";
					//echo "columna=".$valor."<br/>";
					//Check for url column
					$prefix="";
					if(stripos($valor, "url")){
						echo "url=".$valor."<br/>";
						$prefix=$_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]) . "/";
						echo "prefix=".$prefix."<br/>";
					}
					$insert_values.= sprintf("'" . $prefix . "%s'", (array_key_exists($valor,$arr_dest_to_org) && array_key_exists($arr_dest_to_org[$valor],$row_rsSourceTable) ? $row_rsSourceTable[$arr_dest_to_org[$valor]] : $arr_values[$valor]) );				
				}
				$query_insertupdate_tot = sprintf($query_insertupdate_tot,$insert_values);
								
			}	

			echo "query=" . $query_insertupdate_tot . "<br/>";
			//Execute insert/update query
			//Uncomment for prod
			if($param_debug==0){
				$rsDestTable = mysql_query($query_rsSourceTable, eval("$".$sys_dest));
				$arr_keys[$table_dest]=mysql_insert_id(eval("$".$sys_dest));
				mysql_free_result($rsDestTable);
			}

			//Comment this line for prod
			if($param_debug==1){
				if($source_key!=""){
					$arr_keys[$table_dest]=$row_rsSourceTable[$source_key]*1000;//666;
				}
				else
				{
					$arr_keys[$table_dest]=666;
				}
			}			
			
			//Insert into key table only for pos=1 (first table of hierarchy)
			if($pos==1){
			
				$query_rsMappingIdTable=sprintf("insert into %s (sys_dest,table_org_table_dest,id_org,id_dest) values ('%s','%s','%s',%s,%s)",
					"__MappingId",
					$sys_dest,
					$table_org,
					$table_dest,
					$row_rsSourceTable[$source_key],
					$arr_keys[$table_dest]);
					
				echo "query=" . $query_rsMappingIdTable . "<br/>";
				
				if($param_debug==0){	
					$rsMappingIdTable = mysql_query($query_rsMappingIdTable, eval("$".$sys_dest));					
					mysql_free_result($rsMappingIdTable);
				}
				
			}//end $pos=1
		
		} while ($param_only_onerow=false && $row_rsSourceTable = mysql_fetch_assoc($rsSourceTable));
		//--------------------------------------------------------------------------------------------		
		//--------------------------------------------------------------------------------------------		
		
		mysql_free_result($rsSourceTable);
		mysql_free_result($rsMappingColumn);	

		$counter_table++;
	} while (($param_only_onetable==false || $counter_table<$param_counter_table) && $row_rsMappingTable = mysql_fetch_assoc($rsMappingTable));

	mysql_free_result($rsMappingTable);	
}


$query_rsSourceTable=__DoSync($database_test, $test,true);

//Queery source table
$rsSourceTable = mysql_query($query_rsSourceTable, $test);// or die(mysql_error());			
$row_rsSourceTable = mysql_fetch_assoc($rsSourceTable);
$totalRows_rsSourceTable = mysql_num_rows($rsSourceTable);

$arr_keys_org=array();
do{

	echo "---------------------------------------------------------------------" . "<br/>";
	echo "id=". $row_rsSourceTable["idProducto"] . "<br/>";
	echo "---------------------------------------------------------------------" . "<br/>";
	$arr_keys_org["idProducto"]=$row_rsSourceTable["idProducto"];
	$query_rsSourceTable=__DoSync($database_test, $test,false, $arr_keys_org);

} while ($row_rsSourceTable = mysql_fetch_assoc($rsSourceTable));

mysql_free_result($rsSourceTable);		

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
<title>synchro</title>
</head>

<body>
</body>
</html>