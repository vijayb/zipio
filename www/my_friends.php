<?php
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
require("static_supertop.php");
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||


if (!isset($_GET["username"])) {
    exit();
} else {
    $user_id = get_user_id_from_username($_GET["username"]);
    $username = get_username_from_user_id($user_id);
    $friends_array = get_friends_info($user_id);
    if ($_SESSION["user_id"] != $user_id) {
        goto_homepage();
    }
    print("<!-- user_id: $user_id -->\n");
    print("<!-- username: $username -->\n");
}


// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //

$page_title = "My Friends";
$page_subtitle = "";

$table_rows = "";

for ($i = 0; $i < count($friends_array); $i++) {

    $table_rows .= <<<HTML

        <tr id="friend-listing-{friends_array[$i]["id"]}">
            <td><a href="/{$friends_array[$i]["username"]}">{$friends_array[$i]["username"]}</a></td>
            <td>{$friends_array[$i]["email"]}</td>
            <td style="text-align:right">
                <button class="btn btn-mini"
                        id="unfriend-{$friends_array[$i]["id"]}"
                        onclick="unfriend({$user_id}, {$friends_array[$i]['id']})"
                        data-loading-text="Unfriending...">
                    Unfriend
                </button>
            </td>
        </tr>
HTML;

}

?>



<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_top.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->



<div class="row">
    <div class="span12">
        <table class="table table-striped table-condensed" id="friends-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php print($table_rows); ?>
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
