<?php
require dirname(__DIR__) . '/includes/bootstrap.php';
$_SESSION=[];
session_regenerate_id(true);
header('Location: login.php');
exit;
