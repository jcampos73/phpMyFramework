<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_test = "31.24.40.180";
$database_test = "necesitocredito";
$username_test = "necesitocredito";
$password_test = "Wju75c!3";
$test = mysql_pconnect($hostname_test, $username_test, $password_test) or trigger_error(mysql_error(),E_USER_ERROR); 
?>