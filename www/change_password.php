<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");



?>



<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_top.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->









<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_scripts.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->




<script>

</script>




<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_bottom.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->












<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

if (!isset($_GET["request"])) {
    exit();
}

$request = decrypt_json($_GET["request"]);
print("<!--" . print_r($request, true) . "-->");


?>

<html>

<head>
<script src="lib/jquery-1.7.2.min.js"></script>
<script src="helpers.js"></script>

<script>


function hashPassword() {
    $("#password_hash").val(sha1($("#password_hash").val()));
    return true;
}

</script>

</head>

<form id="change_username" action="handle_request.php?request=<?php print(urlencode($_GET["request"])); ?>" method="post" onsubmit="return hashPassword();">
<table>

<tr>
    <td>New username</td>
    <td><input id="username" name="username" type="text"/></td>
</tr>

<tr>
    <td>First name</td>
    <td><input id="first_name" name="first_name" type="text"/></td>
</tr>

<tr>
    <td>Last name</td>
    <td><input id="last_name" name="last_name" type="text"/></td>
</tr>

<tr>
    <td>Password</td>
    <td><input id="password_hash" name="password_hash" type="password"/></td>
</tr>

<tr>
    <td></td>
    <td><input type="submit"></td>
</tr>

</table>
</form>


</html>