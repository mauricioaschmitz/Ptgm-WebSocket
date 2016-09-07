<?php
$hostname_conn = "localhost";
$database_conn = "protegemed";
$username_conn = "root";
$password_conn = "senha.123";
$conn = mysql_connect($hostname_conn, $username_conn, $password_conn) or trigger_error(mysql_error(),E_USER_ERROR); 
?>
