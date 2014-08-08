<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_test = "h2286937.stratoserver.net";//"62.193.217.214";
$database_test = "stcmotor";//"interactiva";
$username_test = "sqlstc";//"sqlinter";
$password_test = "jf2014";//"inter2013";
$test = mysql_pconnect($hostname_test, $username_test, $password_test) or trigger_error(mysql_error(),E_USER_ERROR); 
?>