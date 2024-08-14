<?php
/////////////////////////
///////ZOTEC FRAMEWORK
//////admin@zotecsoft.com
////////////////////////
unset($_SESSION["user_data"]);
session_destroy();
header("Location: /login");

?>