<?php
session_start();

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        redirect("/eduvault/login.php");
        exit();
    }
}
?>