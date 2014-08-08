<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_stcmotores = "31.24.40.180";
$database_stcmotores = "stcmotor-es";
$username_stcmotores = "__syncro";
$password_stcmotores = "~9eJ6xe1";
$stcmotores = mysql_pconnect($hostname_stcmotores, $username_stcmotores, $password_stcmotores) or trigger_error(mysql_error(),E_USER_ERROR); 
?>