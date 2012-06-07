<?php

/*
 We want a token with:
    - expiration date
    - album id
    - user id
    - action: display_album
*/

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

$public_key = openssl_get_publickey(file_get_contents('/usr/local/zipio/public.key'));
$private_key = openssl_get_privatekey(file_get_contents('/usr/local/zipio/private.key'));

$token["expiration"] = time();
$token["album_id"] = 1;
$token["user_id"] = 2;
$token["action"] = 3;


/*
$json = json_encode($token);

debug($json);




$res = openssl_public_encrypt($json, $encrypted_text, $public_key);

debug(base64_encode($encrypted_text));

$res = openssl_private_decrypt($encrypted_text, $decrypted_text, $private_key);

debug($decrypted_text);

*/

$encrypted = encrypt_json($token);
debug($encrypted);

$decrypted = decrypt_json("rCA/sCVMF5/aAs/cmWMiFHfHX1L0w3Mhokh84pzHKpwDg+G17sGGwN3pRAIa6kzUAPjA9zmCZAw/y92QAZDOYZOT4Hlok77uXUxEFudBre3Y6vh7A4VuKbNrtegUGPzFdPTDuUSyGl19z12bMObj5ZEHuPYqkZiVnaVT9vD2J84=");
print_r($decrypted);

if (isset($decrypted)) {
    debug("1");
} else {
    debug("2");
}

?>