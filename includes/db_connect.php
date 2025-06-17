<?php
$mysqli = mysqli_connect("127.0.0.1", "root", "", "eduvault_db");

if (mysqli_connect_errno()) {
    die("Failed to connect to MySQL: " . mysqli_connect_error());
}

define('BASE_URL', '/eduvault/');
define('SITE_NAME', 'EduVault');
?>