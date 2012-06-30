<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

$s3_bucket_name = "s3.zipio.com";
$s3_root = "http://s3.zipio.com/photos";
$www_root = "http://zipio.com";
$founders_email_address = "Zipio <founders@zipio.com>";

$debug = 0;

define('CACHE_PATH', 'opticrop-cache/');

$album_privacy_contants[1] = "<i class='icon-lock' style='color:red;'></i> Private";
$album_privacy_contants[2] = "<i class='icon-group' style='color:orange;'></i> Friends";
$album_privacy_contants[3] = "<i class='icon-globe' style='color:green;'></i> Public";

function goto_homepage($args) {
    if (!isset($args)) $args = "";
    header("Location: $www_root/index.php$args");
}

function login_user($user_id) {
    session_regenerate_id();
    $_SESSION["user_id"] = $user_id;
    $_SESSION["user_info"] = get_user_info($user_id);
    $_SESSION["user_info"]["token"] = calculate_token_from_id($user_id, "Users");
}

function is_logged_in() {
    if (isset($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    } else {
        return 0;
    }
}

function check_request_for_login($_GET) {
    if (isset($_GET["request"])) {
        $request = decrypt_json($_GET["request"]);
        if (isset($request["user_id"])) {
            login_user($request["user_id"]);
            $url = strtok($_SERVER['REQUEST_URI'], '?');
            header("Location: $url");
        }
    }
}

function rand_string($length) {
    $chars = "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz";
    return substr(str_shuffle($chars), 0, $length);
}

function generate_username($email) {
    global $con;
    $parts = explode('@', $email);
    $username = preg_replace("/[^A-Za-z0-9]/", "", $parts[0]);

    for ($i = 0; $i < 5; $i++) {
        $suggested_username = $username . rand_number_string($i);
        $query = "SELECT id FROM Users WHERE username_hash=UNHEX(SHA1('$suggested_username'))";
        $result = mysql_query($query, $con);
        if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . $query . " - " . mysql_error());
        if (mysql_num_rows($result) == 0) {
            return $suggested_username;
        }
    }
}

function rand_number_string($length) {
    if ($length <= 0) {
        return "";
    }

    return rand(pow(10, $length - 1), pow(10, $length) - 1);
}


function create_follower($album_owner_id, $follower_id, $album_id) {

    global $con;

    $query ="INSERT INTO Followers (
                follower_id,
                album_owner_id,
                album_id
              ) VALUES (
                '$follower_id',
                '$album_owner_id',
                '$album_id'
              )";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . $query . " - " . mysql_error());
    $id = mysql_insert_id();
    return $id;
}

function debug($string, $color = "black") {
    print("<span style='color:$color;'>$string</span>" . "\n<br>");
}

function send_email($to, $from, $subject, $html) {

    global $founders_email_address;

    if ($from == "") {
        $from = $founders_email_address;
    }

    $html = "<span style='color:black;' id='" . time() . "'>" . $html . "</span><br><br><span style='font-size:10px; color:#aaaaaa;'>Email confirmation number: " . time() . "</span>";

    $request = new HttpRequest('https://api.mailgun.net/v2/zipio.com/messages', HttpRequest::METH_POST);
    $auth = base64_encode('api:key-68imhgvpoa-6uw3cl8728kcs9brvlmr9');
    $request->setHeaders(array('Authorization' => 'Basic '.$auth));
    $request->setPostFields(array('from'=>$from, 'to'=>$to, 'subject'=>$subject, 'html'=>$html));
    $request->send();

    return $request;
}

function encrypt_json($arr) {
    $json = json_encode($arr);
    $encrypted_text = openssl_encrypt($json, "aes-128-cbc", "cheapass", true, "1234567812345678");
    return base64_encode($encrypted_text);
}

function decrypt_json($encrypted_json) {
    $decrypted_text = openssl_decrypt(base64_decode($encrypted_json), "aes-128-cbc", "cheapass", true, "1234567812345678");
    return json_decode($decrypted_text, true);
}

function calculate_token($id, $created) {
    return sha1($id . $created);
}

function calculate_token_from_id($id, $table) {
    global $con;

    $query = "SELECT created FROM " . $table . " WHERE id='$id' LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {
        $created = $row["created"];
        $token = calculate_token($id, $created);
    } else {
        return 0;
    }
    return $token;
}

function check_token($id, $token, $table) {
    $correct_token = calculate_token_from_id($id, $table);
    if ($token == $correct_token) {
        return 1;
    }
    return 0;
}

function is_friend($user_id, $potential_friend_id) {

    global $con;

    $query = "SELECT * FROM Friends WHERE user_id='$user_id' AND friend_id=$potential_friend_id";
    $result = mysql_query($query, $con);
    if (!$result)
        die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if (mysql_num_rows($result) == 1) {
        return 1;
    }

    return 0;
}

function create_user($username, $password_hash, $email) {

    global $con;

    $query = "INSERT INTO Users (
                email,
                username,
                password_hash
              ) VALUES (
                '$email',
                '$username',
                '$password_hash'
              ) ON DUPLICATE KEY UPDATE id=id";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . $query . " - " . mysql_error());

    $user_id = mysql_insert_id();

    return $user_id;
}


function create_album($user_id, $handle) {

    global $con;

    $handle = strtolower($handle);

    $query = "INSERT INTO Albums (
                  user_id,
                  handle,
                  permissions
              ) VALUES (
                  '$user_id',
                  '$handle',
                  3
              ) ON DUPLICATE KEY UPDATE id=id";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ .
                      ': ' . $query . " - " . mysql_error());

    $album_id = mysql_insert_id();
    return $album_id;
}

function filterImageAndWriteToS3($image, $image_path, $s3_name, $filter) {
    global $s3;
    global $s3_bucket_name;

    $tmp_image_path = $image_path . "_tmp";

    $image->writeImage($tmp_image_path);
    if ($filter == 1) { // tilt shift
        exec("/usr/bin/convert \( $tmp_image_path -gamma 0.75 -modulate 100,130 -contrast \) \( +clone -sparse-color Barycentric '0,0 black 0,%h white' -function polynomial 4,-4,1 -level 0,50% \) -compose blur -set option:compose:args 5 -composite $tmp_image_path");
    } else if ($filter == 2) { // gotham
        $cmd = "/usr/bin/convert $tmp_image_path -modulate 120,10,100 -fill '#222b6d' -colorize 20 -gamma 0.5 -contrast -contrast $tmp_image_path";
        exec($cmd);
        echo "[$cmd]<BR>\n";
        $cmd = "/usr/bin/convert $tmp_image_path -bordercolor 'black' -border {20}x{20} $tmp_image_path";
        echo "[$cmd]<BR>\n";
        exec($cmd);
    } else if ($filter == 3) { // kelvin
        $cmd = "/usr/bin/convert \( $tmp_image_path -auto-gamma -modulate 120,50,100 \) \( -size ".$image->getImageWidth()."x".$image->getImageHeight()." -fill rgba\(255,153,0,0.5\) -draw 'rectangle 0,0 ".$image->getImageWidth().",".$image->getImageHeight()."' \) -compose multiply $tmp_image_path";
        echo "[$cmd]<BR>\n";
        exec($cmd);
        // also need to add border for kelvin...
    } else if ($filter == 4) { // lomo-fi
        exec("/usr/bin/convert $tmp_image_path -channel R -level 33% -channel G -level 33% $tmp_image_path");
    } else if ($filter == 5) { // toaster
        exec("/usr/bin/convert $tmp_image_path -modulate 150,80,100 -gamma 1.2 -contrast -contrast $tmp_image_path");
    } else if ($filter == 6) { // nashville
        exec("/usr/bin/convert $tmp_image_path -contrast -modulate 100,150,100 -auto-gamma $tmp_image_path");
    } else { // No filter

    }

    echo $tmp_image_path." ***<BR>\n";
    echo $s3_name." ***<BR>\n";
    if (!$s3->putObjectFile($tmp_image_path, $s3_bucket_name,
                            "photos/" . $s3_name, S3::ACL_PUBLIC_READ)) {
        debug("Error in writing to S3");
        debug($tmp_image_path);
        return 1;
    }

    unlink($tmp_image_path);
    return 0;
}

//$cmd = "/usr/bin/convert \( /tmp/input.jpg -gamma 0.75 -modulate 100,130 -contrast \) \( +clone -sparse-color Barycentric '0,0 black 0,%h white' -function polynomial 4,-4,1 -level 0,50% \) -compose blur -set option:compose:args 5 -composite /tmp/output.jpg";

function add_albumphoto($owner_user_id, $target_album_id, $target_album_owner_id,
                        $visible = 1, $path_to_photo, &$s3_url_parameter) {

    // $owner_user_id: the user who sends the email with the photo attached
    // $target_album_id: the album this photo will be added to

    global $con;
    global $s3;

    $visible_string = "";
    if (!$visible) $visible_string = "<b>invisible</b>";

    $s3_url =
        $owner_user_id ."_". $target_album_id . "_" . sha1(rand_string(20));
    $s3_url_parameter = $s3_url;

    $sizes = array(1024);

    $failed = 0;

    $image = new imagick($path_to_photo);
    $image->setImageCompression(Imagick::COMPRESSION_JPEG);
    $image->setImageCompressionQuality(65);
    $image->stripImage();

    for ($ii = 0; $ii < count($sizes); $ii++) {
        // Scale the image, indeud
        $tmpimage = clone $image;
        $tmpimage->scaleImage($sizes[$ii], $sizes[$ii], true);

        for ($filter = 0; $filter <= 0; $filter++) {
            $s3_name = $s3_url . "_" . $sizes[$ii] . "_" . $filter;
            $failed = $failed || filterImageAndWriteToS3($tmpimage,
                                                         $path_to_photo,
                                                         $s3_name,
                                                         $filter);
        }
    }

    $cropped_image = clone $image;
    if ($image->getImageWidth() > $image->getImageHeight()) {
        $cropped_image->scaleImage(0, 300);
    } else {
        $cropped_image->scaleImage(300, 0);
    }
    $cropped_path = $path_to_photo."_cropped";
    opticrop($cropped_image, 300, 300, $cropped_path);

    $cropped_image = new imagick($cropped_path);

    for ($filter = 0; $filter <= 0; $filter++) {
        $s3_name = $s3_url."_cropped_" . $filter;
        $failed = $failed || filterImageAndWriteToS3($cropped_image,
                                                     $path_to_photo,
                                                     $s3_name,
                                                     $filter);
    }

    unlink($cropped_path);

    if (!$failed) {
        $query = "INSERT INTO Photos (
                    user_id,
                    s3_url
                  ) VALUES (
                    '$owner_user_id',
                    '$s3_url'
                  ) ON DUPLICATE KEY UPDATE id=id";
        $result = mysql_query($query, $con);
        if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' .
                          $query . " - " . mysql_error());
        $photo_id = mysql_insert_id();

        $query = "INSERT INTO AlbumPhotos (
                    photo_id,
                    photo_owner_id,
                    album_id,
                    album_owner_id,
                    visible
                  ) VALUES (
                    '$photo_id',
                    '$owner_user_id',
                    '$target_album_id',
                    '$target_album_owner_id',
                    '$visible'
                  ) ON DUPLICATE KEY UPDATE id=id";
        $result = mysql_query($query, $con);
        if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' .
                          $query . " - " . mysql_error());
        $albumphoto_id = mysql_insert_id();
        return $albumphoto_id;

    } else {
        echo "Failed to copy file.\n";
        return 0;
    }

}

function email_followers($album_info, $s3_urls) {

    global $con;
    global $www_root;
    global $s3_root;

    $query = "SELECT follower_id, email FROM Followers LEFT JOIN Users ON follower_id=Users.id WHERE album_id=" . $album_info["id"];
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . $query . " - " . mysql_error());

    $album_owner_info = get_user_info($album_info["user_id"]);

    $follower_email_body = <<<EMAIL
        Photos were just added to <b>{$album_owner_info["username"]}</b>'s <a href="{$www_root}/{$album_owner_info["username"]}/{$album_info["handle"]}"><b>{$album_info["handle"]}</b> album</a>!
        <br><br>
EMAIL;

    for ($i = 0; $i < count($s3_urls); $i++) {
        $follower_email_body .= "<img src='" . $s3_root . "/" . $s3_urls[$i] . "_cropped_0'><br><br>";
    }

    while ($row = mysql_fetch_assoc($result)) {
        send_email($row["email"], $founders_email_address, "New photos!", $follower_email_body);
    }

}


/** return 1 if user is following album
 *  else returns 0
 **/
function is_following($logged_in_user_id, $album_id) {

    global $con;

    $query = "SELECT id FROM Followers WHERE follower_id='$logged_in_user_id' AND album_id='$album_id'";
    $result = mysql_query($query);
    if(mysql_num_rows($result) == 1) { // user is following the album
        return 1;
    } else return 0;
}




function album_exists($handle, $user_id_or_username) {

    global $con;
    $handle = strtolower($handle);

    // Check if argument is a username
    $user_id = get_user_id_from_username($user_id_or_username);

    if ($user_id == 0) {
        // No, the argument is not a username; it's an ID
        $user_id = $user_id_or_username;
    }

    // Check if the album exists for the given user
    $query = "SELECT id FROM Albums WHERE handle_hash=UNHEX(SHA1('" . $handle . "')) AND user_id='$user_id' LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {
        $album_id = $row["id"];
        return $album_id;
    }

    // Check if the user exists at all!
    $query = "SELECT * FROM Users WHERE id='$user_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if (mysql_num_rows($result) == 1) {
        // The user exists
        return 0;
    } else {
        return -1;
    }
}


function get_user_id_from_username($username) {

    global $con;

    $query = "SELECT id FROM Users WHERE username='$username' LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    $user_id = 0;

    if ($row = mysql_fetch_assoc($result)) {
        $user_id = $row["id"];
    }

    return $user_id;
}


function get_username_from_user_id($user_id) {

    global $con;

    $query = "SELECT username FROM Users WHERE id='$user_id' LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    $username = "";

    if ($row = mysql_fetch_assoc($result)) {
        $username = $row["username"];
    }

    return $username;

}

function get_user_info($user_id) {

    global $con;

    $query = "SELECT * FROM Users WHERE id='$user_id' LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {

        $inner_query = "SELECT friend_id FROM Friends WHERE user_id='$user_id'";
        $inner_result = mysql_query($inner_query, $con);
        if (!$inner_result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());
        $row["friends"] = array();

        while ($inner_row = mysql_fetch_assoc($inner_result)) {
            array_push($row["friends"], $inner_row["friend_id"]);
        }

        $row["token"] = calculate_token($row["id"], $row["created"]);
        return $row;
    } else {
        return 0;
    }
}

function get_user_info_from_email($email) {

    global $con;

    $query = "SELECT * FROM Users WHERE email_hash=UNHEX(SHA1('$email'))";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {
        $row["token"] = calculate_token($row["id"], $row["created"]);
        return $row;
    } else {
        return 0;
    }
}


function get_album_info($album_id) {

    global $con;

    $query = "SELECT * FROM Albums WHERE id='$album_id' LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {
        $row["token"] = calculate_token($row["id"], $row["created"]);
        return $row;
    } else {
        return 0;
    }
}

function get_albums_info($user_id) {

    global $con;

    $query = "SELECT * FROM Albums WHERE user_id='$user_id'";

    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    $albums_array = array();
    while ($row = mysql_fetch_assoc($result)) {
        $album = get_album_info($row["id"]);
        array_push($albums_array, $album);
    }
    return $albums_array;

}

function get_following_albums_info($user_id) {

    global $con;

    $query = "SELECT *
              FROM Followers
              LEFT JOIN Albums
              ON Followers.album_id = Albums.id
              WHERE Followers.follower_id = '$user_id'";
    $result = mysql_query($query, $con);

    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    $albums_array = array();
    while ($row = mysql_fetch_assoc($result)) {
        $album = get_album_info($row["id"]);
        array_push($albums_array, $album);
    }

    return $albums_array;
}


function get_followers_user_info($album_id) {

    global $con;

    $query = "SELECT
                Followers.follower_id,
                Users.*
              FROM Followers
              LEFT JOIN Users
              ON Followers.follower_id=Users.id
              WHERE Followers.album_id='$album_id'";
    $result = mysql_query($query, $con);

    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    $users_array = array();
    while ($row = mysql_fetch_assoc($result)) {
        $row["token"] = calculate_token_from_id($row["id"], "Users");
        array_push($users_array, $row);
    }

    return $users_array;
}


function get_albumphotos_info($album_id) {
    global $con;

    $query = "SELECT id
              FROM AlbumPhotos
              WHERE album_id='$album_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    $albumphotos_array = array();
    while ($row = mysql_fetch_assoc($result)) {
        $albumphoto = get_albumphoto_info($row["id"]);
        array_push($albumphotos_array, $albumphoto);
    }
    return $albumphotos_array;
}

function get_albumphoto_info($albumphoto_id) {
    global $con;

    $query = "SELECT
                AlbumPhotos.id,
                photo_id,
                photo_owner_id,
                album_id,
                album_owner_id,
                visible,
                filter_code,
                s3_url,
                AlbumPhotos.created
              FROM AlbumPhotos
              LEFT JOIN Photos
              ON AlbumPhotos.photo_id=Photos.id
              WHERE AlbumPhotos.id='$albumphoto_id'
              LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {
        $row["token"] = calculate_token($row["id"], $row["created"]);
        return $row;
    } else {
        return 0;
    }
}

function get_friends_info($user_id) {

   global $con;

   $query = "SELECT friend_id
             FROM Friends
             WHERE user_id='$user_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    $friends_array = array();
    while ($row = mysql_fetch_assoc($result)) {
        $friend = get_user_info($row["friend_id"]);
        array_push($friends_array, $friend);
    }
    return $friends_array;

}

function update_data($table, $id, $key_values) {

    global $con;

    $update_string = "";

    foreach ($key_values as $key=>$value) {
        $update_string .= " " . $key . "='" . mysql_real_escape_string($value) . "',";
    }
    $update_string = rtrim($update_string, ",");


    $query = "UPDATE $table SET $update_string WHERE id=$id LIMIT 1";
    $result = mysql_query($query, $con);

    if (!$result) {
        return 0;
    } else {
        return 1;
    }
}



// Input is an imagemagick image. $w/$h are the dimensions of the desired
// cropped image, and $out is where the cropped image will be written.
// Note that the dimensions of $image must be greater than or equal to
// $w x $h in both dimensions.

function opticrop($img, $w, $h, $out) {

    // source dimensions
    $w0 = $img->getImageWidth();
    $h0 = $img->getImageHeight();
    if ($w > $w0 || $h > $h0)
        die("Target dimensions must be smaller or equal to source dimensions.");

    // parameters for the edge-maximizing crop algorithm
    $r = 1;         // radius of edge filter
    $nk = 9;        // scale count: number of crop sizes to try
    $gamma = 0.2;   // edge normalization parameter -- see documentation
    $ar = $w/$h;    // target aspect ratio (AR)
    $ar0 = $w0/$h0;    // target aspect ratio (AR)

    //    dprint("$image: $w0 x $h0 => $w x $h");
    $imgcp = clone $img;

    // compute center of edginess
    $img->edgeImage($r);
    $img->modulateImage(100,0,100); // grayscale
    $img->blackThresholdImage("#0f0f0f");
    $img->writeImage($out);
    // use gd for random pixel access
    $im = ImageCreateFromJpeg($out);
    $xcenter = 0;
    $ycenter = 0;
    $sum = 0;
    $n = 100000;
    for ($k=0; $k<$n; $k++) {
        $i = mt_rand(0,$w0-1);
        $j = mt_rand(0,$h0-1);
        $val = imagecolorat($im, $i, $j) & 0xFF;
        $sum += $val;
        $xcenter += ($i+1)*$val;
        $ycenter += ($j+1)*$val;
    }
    $xcenter /= $sum;
    $ycenter /= $sum;

    // crop source img to target AR
    if ($w0/$h0 > $ar) {
        // source AR wider than target
        // crop width to target AR
        $wcrop0 = round($ar*$h0);
        $hcrop0 = $h0;
    }
    else {
        // crop height to target AR
        $wcrop0 = $w0;
        $hcrop0 = round($w0/$ar);
    }

    // crop parameters for all scales and translations
    $params = array();

    // crop at different scales
    $hgap = $hcrop0 - $h;
    $hinc = ($nk == 1) ? 0 : $hgap / ($nk - 1);
    $wgap = $wcrop0 - $w;
    $winc = ($nk == 1) ? 0 : $wgap / ($nk - 1);

    // find window with highest normalized edginess
    $n = 10000;
    $maxbetanorm = 0;

    $maxparam = array('w'=>0, 'h'=>0, 'x'=>0, 'y'=>0);
    for ($k = 0; $k < $nk; $k++) {
        $hcrop = round($hcrop0 - $k*$hinc);
        $wcrop = round($wcrop0 - $k*$winc);
        $xcrop = $xcenter - $wcrop / 2;
        $ycrop = $ycenter - $hcrop / 2;
        //dprint("crop: $wcrop, $hcrop, $xcrop, $ycrop");

        if ($xcrop < 0) $xcrop = 0;
        if ($xcrop+$wcrop > $w0) $xcrop = $w0-$wcrop;
        if ($ycrop < 0) $ycrop = 0;
        if ($ycrop+$hcrop > $h0) $ycrop = $h0-$hcrop;

        $beta = 0;
        for ($c=0; $c<$n; $c++) {
            $i = mt_rand(0,$wcrop-1);
            $j = mt_rand(0,$hcrop-1);
            $beta += imagecolorat($im, $xcrop+$i, $ycrop+$j) & 0xFF;
        }
        $area = $wcrop * $hcrop;
        $betanorm = $beta / ($n*pow($area, $gamma-1));
        //dprint("beta: $beta; betan: $betanorm");

        //dprint("image$k.jpg:<br/>\n<img src=\"$currfile\"/>");
        // best image found, save it
        if ($betanorm > $maxbetanorm) {
            $maxbetanorm = $betanorm;
            $maxparam['w'] = $wcrop;
            $maxparam['h'] = $hcrop;
            $maxparam['x'] = $xcrop;
            $maxparam['y'] = $ycrop;
            //$maxfile = $currfile;
        }
    }

    // return image
    $imgcp->cropImage($maxparam['w'],$maxparam['h'],
                      $maxparam['x'],$maxparam['y']);
    $imgcp->scaleImage($w,$h);
    $imgcp->writeImage($out);

    chmod($out, 0777);
    $img->destroy();
    $imgcp->destroy();
    return 0;
}



?>
