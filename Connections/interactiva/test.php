<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_test = "31.24.40.180";
$database_test = "smi-proyectos";
$username_test = "smi-proyectos";
$password_test = "3beJ8f*6";
$test = mysql_pconnect($hostname_test, $username_test, $password_test) or trigger_error(mysql_error(),E_USER_ERROR); 
?>