<?php
require 'include/config.php';
session_destroy();
header("Location: index.php");
exit();
?>