<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

check_request_for_login($_GET);


if (!isset($_GET["username"])) {
    exit();
} else {
    $user_id = get_user_id_from_username($_GET["username"]);
    $username = get_username_from_user_id($user_id);
    print("<!-- user_id: $user_id -->\n");
    print("<!-- username: $username -->\n");
}


// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //

$page_title = <<<HTML
    My Friends
HTML;

$page_subtitle = "See all your friends or use the remove button to unfriend"

?>



<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_top.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->

<?php

$friends_array = get_friends_info($user_id);

?>
<div class="row">
        <div class="span12">
                    <?php
                    if (count($friends_array) == 0) {
                        $html = <<<HTML
                        You have not added any friends on Zipio
HTML;

print($html);
                    } else {
                        $html = <<< HTML
                        <table class="table table-striped table-condensed" id="friends-table">
                                <thead>
                                  <tr>
                                    <th>#</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                  </tr>
                                </thead>
                                <tbody>
HTML;
print($html);
                            for ($i = 0; $i < count($friends_array); $i++) {
                                $serial_no = $i+1;
                                $html = <<<HTML
                                <tr id = "friend-listing-{friends_array[$i]["id"]}">
                                <td>{$serial_no}</td>
                                <td>{$friends_array[$i]["username"]}</td>
                                <td>{$friends_array[$i]["email"]}</td>
                                <td><button class = "btn btn-mini" id = "unfriend-{$friends_array[$i]["id"]}" onclick="unfriend({$user_id}, {$friends_array[$i]['id']})" data-loading-text="Unfriending...">Unfriend</button></td>
                                </tr>
HTML;

print($html);
                            }
                    }
                    ?>
                    </tbody>
                </table>
        </div>
</div>






<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_scripts.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->




<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_bottom.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
