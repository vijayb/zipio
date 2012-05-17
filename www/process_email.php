<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require("db.php");
require("helpers.php");

print_r($_POST);
print("<br><br>");

$sender = $_POST["sender"];
$recipient = $_POST["recipient"];

// First, check if this user exists
$query = "SELECT id FROM Users WHERE email='$sender'";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query: ' . mysql_error());

if (mysql_num_rows($result) == 1) {
    // A user with this email already exists, so get the user's ID
    while ($row = mysql_fetch_assoc($result)) {
        $user_id = $row["id"];
    }
    debug("user_id: " . $user_id);
    debug("User with id $user_id already exists prior to this post.");
} else {
    // New user! Create a new row in the Users table and get the ID of the newly
    // created row.
    $usercode = generate_usercode(5);
    $query = "INSERT INTO Users (
                email,
                usercode,
                username
              ) VALUES (
                '$sender',
                '$usercode',
                '$usercode'
              ) ON DUPLICATE KEY UPDATE last_seen=UTC_TIMESTAMP()";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query: ' . $query . " - " . mysql_error());

    $user_id = mysql_insert_id();

    debug("New user added with id $user_id.");
}

$parts = explode('@', $recipient);
$target_album_handle = $parts[0];
$recipient_domain = $parts[1];

debug("$target_album_handle: " . $target_album_handle . "\n");






// Check if target user is specified explicitly (e.g., ...@alex.zipio.com)
if (preg_match("/(.+)\.zipiyo\.com/", $recipient_domain, $matches)) {
    $target_userstring = $matches[1];
    debug("target_userstring: " . $target_userstring . "\n");
    $target_user_id = get_user_id_from_userstring($target_userstring);
} else {
    $target_user_id = $user_id;
}

$target_album_id = album_exists($target_album_handle, $target_user_id);

// At this point, we have
//      $user_id = The ID of the user who submitted the photo
//      $target_album_id = The ID of the album to add the photo to IF IT EXISTS

if ($target_album_id > 0) {
    debug("Target user ($target_user_id) exists.", "green");
    debug("Target album ($target_album_handle) exists.", "green");
    debug("Target album has ID $target_album_id");
    
    if ($target_user_id == $user_id) {
        // User is adding to own album
        debug("User is adding to his own album.");
        add_photo($user_id, $target_album_id, 1);
    } else {
        // User is adding to another user's album, so check if friends
        $are_friends = are_friends($target_user_id, $user_id);
        if ($are_friends == 1) {
            debug("User is already a friend of target user.", "green");
            // Add the photo to the friend's album
            add_photo($user_id, $target_album_id, 1);
        } else if ($are_friends == 0) {
            debug("Sender of photo is not a friend of target user.", "red");
            // Add photo as invisible and send an email to the owner
            add_photo($user_id, $target_album_id, 0);
        }
    }

} else if ($target_album_id == 0) {
    debug("Target user ($target_user_id) exists.", "green");
    debug("Target album ($target_album_handle) does not exist.", "red");

    if ($target_user_id == $user_id) {
        // Create an album
        debug("User is attempting to create own album.", "green");
        $target_album_id = create_album($user_id, $target_album_handle);
        add_photo($user_id, $target_album_id, 1);

    } else {
        // A user cannot create an album for another user, so disallow
        debug("User is attempting to create album for another user.", "red");
    }
} else if ($target_album_id == -1) {
    debug("Target user ($target_user_id) does not exist.", "red");
}










function are_friends($user_id_1, $user_id_2) {

    global $con;

    $query = "SELECT * FROM Friends WHERE user_id='$user_id_1' AND friend_id=$user_id_2";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query: ' . mysql_error());

    if (mysql_num_rows($result) == 1) {
        return 1;
    }

    return 0;
}


function create_album($user_id, $handle) {
    
    global $con;
    
    $query = "INSERT INTO Albums (
                  user_id,
                  handle
              ) VALUES (
                  '$user_id',
                  '$handle'
              ) ON DUPLICATE KEY UPDATE id=id";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query: ' . $query . " - " . mysql_error());

    $album_id = mysql_insert_id();
    return $album_id;
}


function add_photo($owner_user_id, $target_album_id, $visible = 1) {

    // $owner_user_id: the user who sends the email with the photo attached
    // $target_album_id: the album this photo will be added to

    global $con;
    
    debug("Adding photo (owner $owner_user_id) to album $target_album_id");

    $query = "INSERT INTO Photos (
                user_id,
                visible,
                s3_url
              ) VALUES (
                '$owner_user_id',
                '$visible',
                's3_url_placeholder_" . rand_string(10) . "'
              ) ON DUPLICATE KEY UPDATE id=id";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query: ' . $query . " - " . mysql_error());

    $photo_id = mysql_insert_id();

    $query = "INSERT INTO AlbumPhotos (
                photo_id,
                album_id
              ) VALUES (
                '$photo_id',
                '$target_album_id'
              ) ON DUPLICATE KEY UPDATE id=id";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query: ' . $query . " - " . mysql_error());

    return $photo_id;
}




function album_exists($handle, $user_id) {

    global $con;

    // Check if the album exists for the given user
    $query = "SELECT * FROM Albums WHERE handle='$handle' AND user_id=$user_id";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query: ' . mysql_error());

    if (mysql_num_rows($result) == 1) {
        while ($row = mysql_fetch_assoc($result)) {
            $album_id = $row["id"];
            return $album_id;
        }
    }

    // Check if the user exists at all!
    $query = "SELECT * FROM Users WHERE id='$user_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query: ' . mysql_error());

    if (mysql_num_rows($result) == 1) {
        // The user exists
        return 0;
    } else {
        // The user doesn't exist
        return -1;
    }
}


function get_user_id_from_userstring($username) {

    global $con;

    $query = "SELECT id FROM Users WHERE username='$username'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query: ' . mysql_error());

    $user_id = -1;

    while ($row = mysql_fetch_assoc($result)) {
        $user_id = $row["id"];
    }
    return $user_id;
}




function get_album_id($user_id, $handle) {

    global $con;

    $query = "SELECT id FROM Albums WHERE user_id='$user_id' AND handle='$handle'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query: ' . mysql_error());
   
    $album_id = -1;

    while ($row = mysql_fetch_assoc($result)) {
        $album_id = $row["id"];
    }
    return $album_id;    
}






/*
$uploads_dir = 'attachments';
$name = $_FILES["attachment-1"]["name"];
$tmp_name = $_FILES["attachment-1"]["tmp_name"];

move_uploaded_file($tmp_name, "$uploads_dir/$name");
*/
// service request number: 856884051
// 800-624-9896










?>
