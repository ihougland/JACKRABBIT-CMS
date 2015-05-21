<?php
require_once('srp_core.php');
//session stuffs
SRPCore()->sec_session_start();
require_once('class.captcha_x.php');
$server = &new captcha_x ();
$server->handle_request ();
?>

