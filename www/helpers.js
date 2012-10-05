var imageFiltered = {};
var map;





////////////////////////////////////////////////////////////////////////////////
// SHOW MODALS
////////////////////////////////////////////////////////////////////////////////

function showMapModal(lat, lng) {
    $(".modal").modal('hide');

    $('#map-modal').modal('show');
    var width = $('#map-modal').width();
    var height = 300;
    $("#map-canvas").attr("src", "http://maps.googleapis.com/maps/api/staticmap?center="+lat+","+lng+
        "&zoom=14&size="+width+"x"+height+"&maptype=roadmap&markers=color:red%7Clabel:S%7C"+lat+","+lng+"&sensor=false");
}

function showSignupModal() {
    $('#signup-modal').modal('show');
    $('#signup-username').focus();
}

function showLoginModal() {
    $("#login-error").hide();
    $('#login-modal').modal('show');
    $("#login-submit").button("reset");
    $("#login-submit").prop("disabled", true);
    $("#login-email-check").empty();
    $('#login-password').val('');
    $('#login-email').val('').focus();
}

function showForgotPasswordModal() {
    $(".modal").modal('hide');
    $("#password-email-check").html("");
    $("#password-modal").modal('show');
    $("#password-email").val($("#login-email").val());
    $("#password-email").focus();
    checkEmailIsOkay("password");
    setPasswordSubmitButton();
}

function showInviteModal() {
    $(".modal").modal('hide');
    $("#invite-submit").button("reset");
    $("#invite-modal").modal('show');
    $("#invite-emails").val("").focus();
}

function showFacebookModal(albumOwnerUsername, albumHandle, albumphotoID) {

    if (gFB["status"] == 1) {

        imageURL = $("#image-" + albumphotoID).attr("src");

        $("#facebook-image").data("album-owner-username", albumOwnerUsername);
        $("#facebook-image").data("album-handle", albumHandle);
        $("#facebook-image").data("albumphoto-id", albumphotoID);

        $("#facebook-image").data("caption", $("#albumphoto-caption-" + albumphotoID).html());

        $(".modal").modal('hide');
        $("#facebook-comment").empty().focus();
        $("#facebook-image").attr("src", imageURL);
        $("#facebook-modal").modal('show');
        return;

    } else {

        FB.login(function (response) {
            if (response.authResponse) {
                showFacebookModal(albumphotoID);
            } else {
                console.log('User cancelled login or did not fully authorize.');
            }
        }, {scope: 'email, publish_stream'});
    }

}


function showCaptionModal(albumphotoID, albumphotoOwnerID, albumphotoS3) {
    $(".modal").modal('hide');
    $('#caption-input').val($("#albumphoto-caption-"+ albumphotoID).html());

    $('#caption-modal').attr("albumphoto-id", albumphotoID);
    $('#caption-modal').attr("caption-modifier-id", gUser["id"]);
    $('#caption-modal').attr("caption-modifier-username", gUser["username"]);
    $('#caption-modal').attr("album-handle", gAlbum["handle"]);
    $('#caption-modal').attr("albumphoto-owner-id", albumphotoOwnerID);
    $('#caption-modal').attr("albumphoto-s3", albumphotoS3);


    $('#caption-modal').modal('show');
    $('#caption-input').focus();
}


function showCommentsModal(albumphotoID, s3, imageURL) {

    $("#comment-input").val("");
    $("#comments").html("Loading comments...");

    if (!isLoggedIn()) {
        showLoginModal();
        $("#login-error-message").html("Log in to comment on this photo.");
        $("#login-error").hide().delay(250).slideDown();
        return;
    }

    $(".modal").modal('hide');

    $('#comment-modal').attr("albumphoto-id", albumphotoID);
    $('#comment-modal').attr("albumphoto-s3", s3);
    $("#comment-modal").modal('show');

    reloadComments(albumphotoID);
    $("#comment-input").focus();
}

function showLikersModal(albumphotoID) {
    if (!isLoggedIn()) {
        showLoginModal();
        $("#login-error-message").html("Log in to see who liked the photo.");
        $("#login-error").hide().delay(250).slideDown();
        return;
    }

    $(".modal").modal('hide');
    $("#likes-modal").modal('show');

    loadLikers(albumphotoID);
}


function showNotificationPanel() {

    debug("Start of showNotificationPanel()");

    jQuery.ajax({
        type: "GET",
        url: "/update_last_notified_and_get_html.php?&user_id=" + gUser["id"] + "&token=" + gUser["token"],
        success: function(data) {
            $("#notification-counter").fadeOut();
            $("#notification-items").html(data);

            if (data == "") {
                $("#notification-items").html("<li style='padding:5px 15px'>No new notifications</li>");
            }


        },
        async: true
    });




}


function pollNotifications() {

    debug("Start of pollNotifications()");


    if (!isLoggedIn()) {
        return;
    }


    jQuery.ajax({
        type: "GET",
        url: "/poll_notifications_json.php?user_id=" + gUser["id"] + "&token=" + gUser["token"] + "&num_notifications=" + $('#notification-counter').html(),
        success: function(data) {
            var notificationsArray = JSON.parse(data);
            var numNotifications = notificationsArray.length;

            if (numNotifications > 0) {
                $("#notification-counter").html(numNotifications).show();
            } else {
                $("#notification-counter").html(numNotifications).fadeOut();
            }

            pollNotifications();
        },
        async: true
    });

}

function getCommentsHTML(commentsArray, albumphotoID) {

    var html = "";

    for (i = 0; i < commentsArray.length; i++) {
        html += "\
            <div style='margin-bottom:8px'>\
            <b>" + commentsArray[i]["username"] + ":</b>\
            <span id='comment-text-" + commentsArray[i]["id"] + "'>" + commentsArray[i]["comment"] + "</span>\
            <span style='font-size:12px; color:#999999'>" + commentsArray[i]["created"] + "</span>";

        if (parseInt(commentsArray[i]["commenter_id"]) == parseInt(gUser["id"]) ||
            (typeof gAlbum !== "undefined" && parseInt(gAlbum["user_id"]) == parseInt(gUser["id"]))) {
                html += "&nbsp;&nbsp;<a href='javascript:void(0);' onclick='$(this).parent().slideUp(); deleteComment(" + commentsArray[i]["id"] + ", " + $('#comment-modal').attr('albumphoto-id') + ");' class='no-underline'><i class='icon-remove'></i></a>";
    }

    var heart = "gray";
    var likedComment = 0;
    if (parseInt(commentsArray[i]["liked"]) == 1) {
        heart = "red";
        likedComment = 1;
    }

    var display_like_count = "";
    if (parseInt(commentsArray[i]["like_count"]) == 0) {
        display_like_count = "style='display:none;'";
    }



    html += "&nbsp;&nbsp;<a href='javascript:void(0);' onclick='toggleLikeComment(" + commentsArray[i]["id"] + "," + commentsArray[i]["commenter_id"] + "," + albumphotoID + ");'><i id='comment-like-" + commentsArray[i]["id"] + "' liked=" + likedComment + " class='icon-heart heart-" + heart + " no-underline'></i></a>&nbsp;<a href='javascript:void(0);' onclick='loadCommentLikers(" + commentsArray[i]["id"] + ")'><span id=comment-like-count-" + commentsArray[i]["id"] + " " + display_like_count + ">" + commentsArray[i]["like_count"] + "</span></a>";

    html += "\
        <span style='color:#999; font-size: 12px' id='likers-list-" + commentsArray[i]["id"] + "'></span>\
        </div>";

    }

    if (html == "") html = "<span style='color:#999999'>No comments yet. Be the first!</span>";

    return html;

}




function getLikersHTML(likersArray) {
    var html = "";

    html = "Liked by "

    for (i = 0; i < likersArray.length; i++) {
        var username = likersArray[i]["username"];
        if (parseInt(likersArray[i]["liker_id"]) == parseInt(gUser["id"])) {
            username = "me";
        }

        html += username;

        html += ", ";
    }

    html = html.replace(/(,\s*$)/g, "");

    return html;
}




function showFBBar() {
    $("#fb-bar").slideDown();
    $("body").css("padding-top", "120px");
}

function hideFBBar() {
    $("#fb-bar").slideUp();
    $("body").css("padding-top", "60px");
}

////////////////////////////////////////////////////////////////////////////////
// SUBMIT MODALS
////////////////////////////////////////////////////////////////////////////////

// addComment

function submitComment() {

    $("#comment-submit").button("loading");

    var albumphotoID = parseInt($('#comment-modal').attr("albumphoto-id"));
    var albumphotoS3 = $('#comment-modal').attr("albumphoto-s3");
    var comment = $("#comment-input").val();

    jQuery.ajax({
        type: 'POST',
        url: "/add_comment.php",
        data: {
            "albumphoto_id": albumphotoID,
            "comment": comment,
            "token": gUser["token"],
            "commenter_id": gUser["id"],
            "album_id": gAlbum["id"],
            "album_owner_id": gAlbum["user_id"],
            "album_owner_username": gAlbum["username"],
            "album_handle": gAlbum["handle"],
            "commenter_username": gUser["username"],
            "albumphoto_s3": albumphotoS3
        },
        success: function(data) {
            if (parseInt(data) == 1) {
                // need to reload comment stream

                reloadComments(albumphotoID);
                $("#comment-input").val("");
                $("#comment-submit").button("reset");

            } else {
                alert(data);
            }
        },
        async: true
    });
}


function submitCommentOneUp() {

    $("#one-up-comment-submit").button("loading");

    var albumphotoID = parseInt($('#one-up-photo').attr("albumphoto-id"));
    var albumphotoS3 = $('#one-up-photo').attr("albumphoto-s3");
    var comment = $("#one-up-comment-input").val();

    jQuery.ajax({
        type: 'POST',
        url: "/add_comment.php",
        data: {
            "albumphoto_id": albumphotoID,
            "comment": comment,
            "token": gUser["token"],
            "commenter_id": gUser["id"],
            "album_id": gAlbum["id"],
            "album_owner_id": gAlbum["user_id"],
            "album_owner_username": gAlbum["username"],
            "album_handle": gAlbum["handle"],
            "commenter_username": gUser["username"],
            "albumphoto_s3": albumphotoS3
        },
        success: function(data) {
            if (parseInt(data) == 1) {
                // need to reload comment stream

                reloadCommentsOneUp(albumphotoID);
                $("#one-up-comment-input").val("");
                $("#one-up-comment-submit").button("reset");

            } else {
                alert(data);
            }
        },
        async: true
    });
}







function reloadComments(albumphotoID, userID) {
    var urlString = "/get_comments_json.php?albumphoto_id=" + albumphotoID + "&user_id=" + gUser["id"] + "&token=" + gUser["token"];

    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            var commentsArray = JSON.parse(data);
            var html = getCommentsHTML(commentsArray, albumphotoID);
            $("#comments").html(html);
            $("#comment-modal-body").scrollTop($("#comment-modal-body")[0].scrollHeight);
            $("#comment-count-" + albumphotoID).html(commentsArray.length);

            if (commentsArray.length > 0) {
                $("#comment-count-" + albumphotoID).show();
            } else {
                $("#comment-count-" + albumphotoID).hide();
            }

        },
        async: true
    });
}


function reloadCommentsOneUp(albumphotoID, userID) {
    var urlString = "/get_comments_json.php?albumphoto_id=" + albumphotoID + "&user_id=" + gUser["id"] + "&token=" + gUser["token"];

    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            var commentsArray = JSON.parse(data);
            var html = getCommentsHTML(commentsArray, albumphotoID);
            $("#one-up-comments").html(html);

        },
        async: true
    });
}


function toggleLikeComment(commentID, commenterID, albumphotoID) {

    jQuery.ajax({
        type: 'POST',
        url: "/toggle_like_comment.php",
        data: {
            "comment_id": commentID,
            "comment": $("#comment-text-" + commentID).html(),
            "albumphoto_id": albumphotoID,
            "album_id": gAlbum["id"],
            "album_owner_id": gAlbum["user_id"],
            "commenter_id": commenterID,
            "liker_id": gUser["id"],
            "liker_username": gUser["username"],
            "token": gUser["token"],
            "old_like_value": $("#comment-like-" + commentID).attr("liked"),
            "albumphoto_owner_id": $("#image-" + albumphotoID).attr("owner-id"),
            "album_handle": gAlbum["handle"],
            "albumphoto_s3": $("#image-" + albumphotoID).attr("albumphoto-s3")
        },
        success: function(data) {
            if (parseInt(data) == 1) {
                var likeCount = parseInt($("#comment-like-count-" + commentID).html());
                if (parseInt($("#comment-like-"+commentID).attr("liked")) == 1) {
                    $("#comment-like-" + commentID).attr("class", "icon-heart heart-gray no-underline");
                    $("#comment-like-" + commentID).attr("liked", 0);
                    $("#comment-like-count-" + commentID).html(likeCount - 1);
                    if (likeCount - 1 == 0) {
                        $("#comment-like-count-" + commentID).hide();
                    }
                } else {
                    $("#comment-like-"+commentID).attr("class", "icon-heart heart-red no-underline");
                    $("#comment-like-"+commentID).attr("liked", 1);
                    $("#comment-like-count-" + commentID).html(likeCount + 1);
                    $("#comment-like-count-" + commentID).show();
                }
            } else {
                alert(data);
            }
        },
        async: true
    });
}



function deleteComment(commentID, albumphotoID) {
    var urlString = "/delete_comment.php?comment_id=" + commentID +
                                        "&token=" + gUser["token"] +
                                        "&commenter_id=" + gUser["id"];
    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            if (data == 1) {
                reloadComments(albumphotoID);
            } else {
                // bad token or wrong arguments
            }
        },
        async: true
    });
}


function likeAlbumphoto(albumphotoID, likerID, albumphotoOwnerID, albumphotoS3) {
    if (!isLoggedIn()) {
        showLoginModal();
        $("#login-error-message").html("Log in to like this photo.");
        $("#login-error").hide().delay(250).slideDown();
        return;
    }

    jQuery.ajax({
        type: 'POST',
        url: "/like_albumphoto.php",
        data: {
            "albumphoto_id": albumphotoID,
            "token": gUser["token"],
            "album_id": gAlbum["id"],
            "album_owner_id": gAlbum["user_id"],
            "liker_id": likerID,
            "old_like_value": $("#albumphoto-like-"+albumphotoID).attr("liked"),
            "albumphoto_owner_id": albumphotoOwnerID,
            "album_handle": gAlbum["handle"],
            "liker_username": gUser["username"],
            "albumphoto_s3": albumphotoS3
        },
        success: function(data) {
            if (parseInt(data) == 1) {
            var likeCount = parseInt($("#albumphoto-like-count-" + albumphotoID).html());

            if (parseInt($("#albumphoto-like-"+albumphotoID).attr("liked")) == 0) {
                $("#albumphoto-like-"+albumphotoID).attr("liked", 1)
                $("#albumphoto-like-count-" + albumphotoID).html(likeCount + 1);
                likeCount++;
                $("#albumphoto-like-heart-"+albumphotoID).addClass("heart-red").removeClass("heart-gray");
            } else {
                $("#albumphoto-like-"+albumphotoID).attr("liked", 0)
                $("#albumphoto-like-count-" + albumphotoID).html(likeCount - 1);
                likeCount--;
                $("#albumphoto-like-heart-"+albumphotoID).addClass("heart-gray").removeClass("heart-red");
            }

            if (likeCount == 0) {
                $("#albumphoto-like-count-link-" + albumphotoID).hide();
            } else {
                $("#albumphoto-like-count-link-" + albumphotoID).show();
            }

            } else {
                alert(data);
            }
        },
        async: true
    });
}


function loadLikers(albumphotoID) {
    jQuery.ajax({
        type: "GET",
        url: "/get_albumphoto_likers_json.php?albumphoto_id=" + albumphotoID,
        success: function(data) {
            var likersArray = JSON.parse(data);
            var html = getLikersHTML(likersArray);
            $("#likes").html(html);
            $("#albumphoto-like-count-" + albumphotoID).html(likersArray.length);
        },
        async: true
    });
}


function loadCommentLikers(commentID) {
    jQuery.ajax({
        type: "GET",
        url: "/get_comment_likers_json.php?comment_id=" + commentID,
        success: function(data) {
            var likersArray = JSON.parse(data);
            var html = getLikersHTML(likersArray);
            $("#likers-list-" + commentID).html(html);
            $("#comment-like-count-" + commentID).html(likersArray.length);
        },
        async: true
    });
}



function submitLogin() {
    $("#login-submit").button("loading");
    var passwordHash = sha1($("#login-password").val());
    var email = $("#login-email").val();
    var urlString = "/attempt_login.php?email=" + email + "&password_hash=" + passwordHash;

    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            if (parseInt(data) != 0) {
                window.location.replace(window.location.href.split('#')[0]);
            } else {
                $("#login-error").show();
                $("#login-error-message").html("<strong>Woops.</strong> That email & password combo is invalid.");
                $("#login-password").select();
                $("#login-submit").button("reset");
                $("#login-submit").prop("disabled", true);
            }
        },
        async: true
    });
}

function submitInvite() {
    $("#invite-submit").button("loading");
    var emails = encodeURIComponent($("#invite-emails").val());
    var urlString = "/add_collaborators_and_send_emails.php?emails=" + emails +
                                                          "&inviter_id=" + gUser["id"] +
                                                          "&album_id=" + gAlbum["id"] +
                                                          "&inviter_token=" + gUser["token"] +
                                                          "&album_token=" + gAlbum["token"];

    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            console.log("hello");
            window.location.replace(window.location.href.split('#')[0] + "#alert=6");
            window.location.reload(true);
        },
        async: true
    });
}


function submitForgotPassword() {
    $("#password-submit").button("loading");
    var email = $("#password-email").val();
    var urlString = "/send_password_email.php?email=" + email;

    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            $("#password-modal").modal('hide');
            $("#header-alert-title").html("Check your email for a password reset link");
            $("#header-alert-text").html("");
            $("#header-alert").addClass("alert-success");
            $("#header-alert").fadeIn();
        },
        async: true
    });
}


function submitCaption() {

    var albumphotoID = $('#caption-modal').attr("albumphoto-id");
    var caption = $("#caption-input").val();

    jQuery.ajax({
        type: 'POST',
        url: "/update_caption.php",
        data: {
            "albumphoto_id": albumphotoID,
            "caption": caption,
            "token": gAlbum["token"],
            "album_id": gAlbum["id"],
            "caption_modifier_id": $('#caption-modal').attr("caption-modifier-id"),
            "caption_modifier_username":$('#caption-modal').attr("caption-modifier-username"),
            "album_handle": $('#caption-modal').attr("album-handle"),
            "albumphoto_owner_id": $('#caption-modal').attr("albumphoto-owner-id"),
            "albumphoto_s3": $('#caption-modal').attr("albumphoto-s3")
        },
        success: function(data) {
            if (parseInt(data) == 1) {
                $("#caption-modal").modal('hide');
                $("#albumphoto-caption-" + albumphotoID).html(caption);

                if (caption != "") {
                    $("#albumphoto-caption-" + albumphotoID).parent().addClass("albumphoto-caption-always-visible");
                    $("#albumphoto-caption-" + albumphotoID).parent().removeClass("albumphoto-caption");
                    $("#add-caption-" + albumphotoID).html('&nbsp; <i class="icon-pencil"></i> Edit');
                } else {
                    $("#albumphoto-caption-" + albumphotoID).parent().addClass("albumphoto-caption");
                    $("#albumphoto-caption-" + albumphotoID).parent().removeClass("albumphoto-caption-always-visible");
                    $("#add-caption-" + albumphotoID).html('<i class="icon-pencil"></i> Add a caption');
                }
            } else {
        alert(data);
                // bad token
            }
        },
        async: true
    });

}


function showNewsFeedAlbums(friendID) {
    $("#albums-" + friendID).toggle();

    $("#albums-caret-" + friendID).toggleClass("icon-caret-right");
    $("#albums-caret-" + friendID).toggleClass("icon-caret-down");
}


function toggleAlbumFollower(albumID, albumOwnerID) {

    // Did the user just check or uncheck the box?
    var checked = $('#album-checkbox-' + albumID).is(':checked');
    var action = "";

    if (checked) {
        action = "add";
    } else {
        action = "delete"
    }

    var urlString = "/toggle_albumfollower.php?album_id=" + albumID
                                           + "&action=" + action
                                           + "&token=" + gUser["token"]
                                           + "&user_id=" + gUser["id"]
                                           + "&album_owner_id=" + albumOwnerID;

    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            if (parseInt(data) == 1) {
                $("#saved-" + albumID).show().delay(700).fadeOut();
            } else {
                // bad token
            }
        },
        async: true
    });
}




function submitUsernamePassword() {
    $("#register-submit").button("loading");
    var username = gUser["username"];
    var passwordHash = sha1($("#register-password").val());

    if ($("#register-username").is(":visible")) {
        username = $("#register-username").val();
    }

    var urlString = "/save_username_password.php?token=" + gUser["token"] +
                                               "&username=" + username +
                                               "&password_hash=" + passwordHash;

    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            if (parseInt(data) == 1) {
                var oldURL = window.location.href;
                forwardURL = oldURL.replace("/" + gUser["username"], "/" + username);
                window.location.replace(forwardURL.split('#')[0]);
            }
        },
        async: true
    });

}

function changeAlbumPrivacy() {
    $("#album-settings-submit").button("loading");
    var newSetting = parseInt($('input[name=album-privacy]:checked').val());
    var urlString = "/change_album_read_permissions.php?album_id=" + gAlbum['id'] + "&read_permissions=" + newSetting + "&token=" + gAlbum['token'];

    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            if (parseInt(data) == 1) {
                $("#album-privacy-saved-" + newSetting).show().delay(700).fadeOut();
            } else {
                // bad token
            }
        },
        async: true
    });
}


function changeAlbumWritePermissions() {
    $("#album-settings-submit").button("loading");

    if ($("#write-permissions-checkbox").attr("checked") == "checked") {
        newSetting = 2;
    } else {
        newSetting = 1;
    }

    var urlString = "/change_album_write_permissions.php?album_id=" + gAlbum['id'] + "&write_permissions=" + newSetting + "&token=" + gAlbum['token'];

    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            if (parseInt(data) == 1) {
                $("#write-permissions-saved").show().delay(700).fadeOut();
            } else {
                // bad token
            }
        },
        async: true
    });
}



function submitSignup() {
    $("#signup-submit").button("loading");
    var username = $("#signup-username").val();
    var passwordHash = sha1($("#signup-password").val());
    var email = $("#signup-email").val();

    var urlString = "/sign_up_user.php?username=" + username +
                                     "&password_hash=" + passwordHash +
                                     "&email=" + email;
    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            window.location.replace(window.location.href.split('#')[0]);
        },
        async: true
    });
}

////////////////////////////////////////////////////////////////////////////////
// SET MODAL BUTTONS
////////////////////////////////////////////////////////////////////////////////

function setSignupSubmitButton() {
    debug("setSignupSubmitButton called");
    $("#signup-submit").attr("disabled", true);
    if ($("#signup-email-check").data("correct") == 1 &&
        $("#signup-username-check").data("correct") == 1 &&
        $("#signup-password").val() != "") {
        $("#signup-submit").removeAttr("disabled");
    } else {
        $("#signup-submit").attr("disabled", true);
    }
}

function setRegisterSubmitButton() {
    debug("setRegisterSubmitButton()");
    $("#register-submit").attr("disabled", true);
    if (($("#register-username-check").data("correct") == 1 || !$("#register-username").is(":visible")) &&
        $("#register-password").val() != "") {
        $("#register-submit").removeAttr("disabled");
    } else {
        $("#register-submit").attr("disabled", true);
    }
}

function setLoginSubmitButton() {
    $("#login-submit").attr("disabled", true);
    if ($("#login-email-check").data("correct") == 1  &&
        $("#login-password").val() != "") {
        $("#login-submit").removeAttr("disabled");
    } else {
        $("#login-submit").attr("disabled", true);
    }
}

function setPasswordSubmitButton() {
    $("#password-submit").attr("disabled", true);
    if ($("#password-email-check").data("correct")) {
        $("#password-submit").removeAttr("disabled");
    } else {
        $("#password-submit").attr("disabled", true);
    }
}



function deleteFriend(friendID, userID, userToken) {
    var urlString = "/delete_friend.php?friend_id=" + friendID +
                                      "&user_id=" + userID +
                                      "&user_token=" + userToken;

    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            if (parseInt(data) == 1) {
                $("#friend-" + friendID).remove();
            } else {
                // bad token
            }
        },
        async: true
    });

}







function deleteCollaborator(collaboratorID, albumID, albumToken) {
    var urlString = "/delete_collaborator.php?collaborator_id=" + collaboratorID +
                                            "&album_id=" + albumID +
                                            "&album_token=" + albumToken;

    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            if (parseInt(data) == 1) {
                $("#collaborator-" + collaboratorID).remove();
            } else {
                // bad token
            }
        },
        async: true
    });

}



function flipChangeUsername() {
    if ($("#register-username-panel").is(":visible")) {
        $('#register-username-panel').hide();
        $('#register-read-only-username-panel').show();
    } else {
        $('#register-username-panel').show();
        $('#register-read-only-username-panel').hide();
        $("#register-password-submit").addClass("disabled");
        $("#register-username-check").html("Type in the username you'd like");
        $('#register-username').val("");
        $('#register-username').focus();
    }
}

function checkEmailIsOkay(prefix) {

    $("#" + prefix + "-email-check").data("correct", 0);

    var emailEntered = $("#" + prefix + "-email").val();
    if (emailEntered == "") {
        $("#" + prefix + "-email-check").html("Type in your email");
        return;
    }

    if (!validateEmail(emailEntered)) {
        $("#" + prefix + "-email-check").html("<i class='icon-remove'></i> That doesn't appear to be a valid address");
        return;
    }

    var urlString = "/check_email_is_unique.php?email=" + emailEntered;

    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            data = parseInt(data);
            if (data == 0) {
                // 0 means the email does NOT exist in the database
                if (prefix == "password" || prefix == "login") {
                    $("#" + prefix + "-email-check").html("<i class='icon-remove'></i> That email address isn't registered");
                } else {
                    $("#" + prefix + "-email-check").html("<i class='icon-ok'></i> Looks good!");
                    $("#" + prefix + "-email-check").data("correct", 1);
                }
            } else if (data == 1) {
                // 1 means the email DOES exist in the database
                if (prefix == "password") {
                    $("#" + prefix + "-email-check").html("<i class='icon-ok'></i> Click the blue button below and we'll email you a reset password link.");
                    $("#" + prefix + "-email-check").data("correct", 1);
                } else if (prefix == "login") {
                    $("#" + prefix + "-email-check").html("<i class='icon-ok'></i> Welcome back!");
                    $("#" + prefix + "-email-check").data("correct", 1);
                } else {
                    $("#" + prefix + "-email-check").html("<i class='icon-remove'></i> <b>" + emailEntered + "</b> has already signed up");
                }
            }
        },
        async: false
    });
}





function checkUsernameIsUnique(prefix) {

    $("#" + prefix + "-username-check").data("correct", 0);
    var usernameEntered = $("#" + prefix + "-username").val();

    if (usernameEntered == "") {
        $("#" + prefix + "-username-check").html("Type in the username you'd like");
        return;
    }

    if (/[^A-Za-z0-9]/.test(usernameEntered) ) {
        $("#" + prefix + "-username-check").html("<i class='icon-remove'></i> Only letters and numbers, please");
        return;
    }

    var urlString = "/check_username_is_unique.php?username=" + usernameEntered;
    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            data = parseInt(data);
            if (data == 0) {
                $("#" + prefix + "-username-check").html("<i class='icon-ok'></i> <b>" + usernameEntered + "</b> is available!");
                $("#" + prefix + "-username-check").data("correct", 1);
            } else if (isLoggedIn() && data == gUser["id"]) {
                $("#" + prefix + "-username-check").html("<i class='icon-remove'></i> Ummm...that's already your username");
            } else {
                $("#" + prefix + "-username-check").html("<i class='icon-remove'></i> <b>" + usernameEntered + "</b> is already taken (sorry, try something else)");
            }
        },
        async: false
    });
}








function rotatePhoto(clockwise, albumphotoID, albumID, albumphotoToken, s3Handle) {
    $("#cover-message-" + albumphotoID).html("Rotating...");
    $("#cover-" + albumphotoID).fadeIn();

    jQuery.ajax({
        type: "GET",
        url: "/rotate_photo.php?clockwise=" + clockwise + "&albumphoto_id=" + albumphotoID + "&album_id=" + albumID + "&albumphoto_token=" + albumphotoToken + "&s3_handle=" + s3Handle,
        success: function(data) {
            if (parseInt(data) == 1) {
                $("#cover-" + albumphotoID).fadeOut();
                $("#image-" + albumphotoID).attr("src", $("#image-"+albumphotoID).attr("src") + "?" + new Date().getTime());
            } else {
                alert("Error rotating image");
            }
        },
        async: true
    });
}


function resetPhotoToOriginal(albumphotoID, croppedOriginalSrc, bigOriginalSrc) {
    $("#save-" + albumphotoID).show();
    $("#albumphoto-" + albumphotoID).find("canvas").remove();
    $("#image-" + albumphotoID).attr("src", croppedOriginalSrc);
    imageFiltered[albumphotoID.toString()] = 0;
}

function applyFilter(albumphotoID, imageProxySrc, filter) {
    $("#filter-" + albumphotoID).button("loading");
    RUN_EFFECT['e' + filter](albumphotoID, imageProxySrc);
    imageFiltered[albumphotoID.toString()] = filter;
    // Button is reset and save button is shown in filter_and_cover_img
}

function saveFiltered(albumphotoID, croppedImageProxySrc, bigImageProxySrc, bigOriginalSrc) {
    if (imageFiltered.hasOwnProperty(albumphotoID)) {

        $("#cover-message-" + albumphotoID).html("Saving...");
        $("#cover-" + albumphotoID).fadeIn();

        if (imageFiltered[albumphotoID] == 0) {
            $.ajax({
                type: 'GET',
                url: '/save_filtered.php?albumphoto_id=' + albumphotoID + '&reset_to_original',
                sync: false,
                success: function(data) {
                    $("#save-" + albumphotoID).hide();
                    $("#cover-" + albumphotoID).fadeOut();
                }
            });

        } else {
            SAVE_EFFECT['e' + imageFiltered[albumphotoID]](albumphotoID, croppedImageProxySrc, bigImageProxySrc);
        }
        delete imageFiltered[albumphotoID.toString()];
        // Save button and cover are hidden in filter_and_save
    } else {
        alert("No filter selected");
    }
}




function fbLogin() {
    FB.login(function (response) {
        if (response.authResponse) {
            console.log('Welcome!  Fetching your information.... ');
        } else {
            console.log('User cancelled login or did not fully authorize.');
        }
    }, {scope: 'email, publish_stream'});
}


function createLikeButton(element, url) {

    if (fbIsValid()) {
        var likeButton = $(document.createElement("fb:like"));
        likeButton.attr("href", url);
        likeButton.attr("send", "true");
        likeButton.attr("layout", "button_count");
        likeButton.attr("show_faces", "true");
        $(element).empty().append(likeButton);
        FB.XFBML.parse($(element).get(0));
    }
}




function postToFacebook() {
    $("#facebook-submit").button("loading");

    var imageURL = $("#facebook-image").attr("src");

    var albumOwnerUsername = $("#facebook-image").data("album-owner-username");
    var albumHandle = $("#facebook-image").data("album-handle");
    var albumphotoID = $("#facebook-image").data("albumphoto-id");

    var oneUpURL = "http://zipio.com/" + albumOwnerUsername + "/" + albumHandle + "/" + albumphotoID;

    var albumphotoCaption = $("#facebook-image").data("caption");

    FB.api('/me/feed',
           'post',
           {
                link: oneUpURL,
                message: $("#facebook-comment").val(),
                picture: imageURL,
                name: "See photo at Zipio",
                caption: albumphotoCaption

           },
           function(response) {
                if (!response || response.error) {
                    console.log(response);
                    alert('Error occured');
                } else {
                    $("#facebook-submit").button("reset");
                    $(".modal").modal('hide');
                    $("#header-alert-title").html("Posted to Facebook");
                    $("#header-alert-text").html("");
                    $("#header-alert").addClass("alert-success");
                    $("#header-alert").delay(1000).fadeIn();
                }
    });
}






function isLoggedIn() {
    if (typeof gUser != 'undefined') {
        return true;
    }
    return false;
}

function debug(string) {
    if (this.console && typeof console.log != "undefined") {
        console.log(string);
    }
}

function preload(arrayOfImages) {
    $(arrayOfImages).each(function(){
        $('<img/>')[0].src = this;
    });
}

function deletePhotoFromAlbum(albumPhotoID, token, s3) {
    $("#albumphoto-" + albumPhotoID).fadeOut(500);
    $("#image-" + albumPhotoID).fadeOut(500);
    var urlString = "/delete_photo_from_album.php?albumphoto_id=" + albumPhotoID +
                                                "&token=" + token +
                                                "&s3=" + s3;
    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            if (parseInt(data) == 1) {
                $("#albumphoto-" + albumPhotoID).remove();
            } else if (parseInt(data) == 0) {
                debug("Could not delete photo because the token's wrong.");
                $("#albumphoto-" + albumPhotoID).show();
                return;
            } else {
                debug("Could not delete photo (unknown error).");
                $("#albumphoto-" + albumPhotoID).show();
            }
        },
        async: true
    });
}



function setAsAlbumCover(albumPhotoID, albumID, token) {
    var urlString = "/set_as_album_cover.php?albumphoto_id=" + albumPhotoID +
                                           "&album_id=" + albumID +
                                           "&token=" + token;
    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            if (parseInt(data) == 1) {
                $("#header-alert-title").html("Album cover changed");
                $("#header-alert-text").html("");
                $("#header-alert").addClass("alert-success");
                $("#header-alert").fadeIn();
            } else if (parseInt(data) == 0) {
                debug("Could not delete photo because the token's wrong.");
            } else {
                debug("Could not delete photo (unknown error).");
            }
        },
        async: true
    });
}




function deleteAlbum(albumID, token) {
    $("#album-" + albumID).fadeOut(100);
    var urlString = "/delete_album.php?album_id=" + albumID +
                                     "&token=" + token;
    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            if (parseInt(data) == 1) {
                $("#album-" + albumPhotoID).remove();
            } else if (parseInt(data) == 0) {
                debug("Could not delete album because the token's wrong.");
                $("#album-" + albumPhotoID).show();
                return;
            } else {
                debug("Could not delete album (unknown error).");
                $("#album-" + albumPhotoID).show();
            }
        },
        async: true
    });

}


function validateEmail(email) {
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}





function getAlert(alert) {
    var returnArr = new Array();

    if (alert == 1) {
        // legacy
    } else if (alert == 2) {
        // legacy
    } else if (alert == 3) {
        returnArr["title"] = hashParams["email"] + " can now add photos to this album.";
        returnArr["text"]  = "We'll email " + hashParams["email"] + " when this album is updated.";
        returnArr["class"] = "alert-success";
    } else if (alert == 4) {
        // legacy
    } else if (alert == 5) {
        returnArr["title"] = "The user '" + hashParams["username"] + "' doesn't exist.";
        returnArr["text"]  = "";
        returnArr["class"] = "alert-error";
    } else if (alert == 6) {
        returnArr["title"] = "Emails sent and collaborators added.";
        returnArr["text"]  = "The people you just invited can now add photos to this album (they're now listed in the Album Collaborators list).";
        returnArr["class"] = "alert-success";
    } else if (alert == 7) {
        returnArr["title"] = hashParams["username"] + " wants you to add photos to this album!";
        returnArr["text"]  = "Adding photos is easy. Just email them as attachments to the email address below. Or, <a href=''>download the Zipio camera</a> to add photos as you take them.";
        returnArr["class"] = "alert-success";
    } else if (alert == 8) {
        returnArr["title"] = "Welcome to Zipio! Here's your first album.";
        returnArr["text"]  = "Now, invite people to add their photos! Photos can be added by email, so there's no sign-up required. Click the blue \"Invite\" button below to add collaborators (we'll send them invites).";
        returnArr["class"] = "alert-success";
    } else if (alert == 9) {
        returnArr["title"] = "That photo was deleted.";
        returnArr["text"]  = "";
        returnArr["class"] = "alert-success";
    }

    return returnArr;
}





















// Functions that have been copied from various sources on the Internet

function getURLParameter(name) {
    return decodeURIComponent(
        (location.search.match(RegExp("[?|&]"+name+'=(.+?)(&|$)'))||[,null])[1]
    );
}

function getURLHashParameter(name) {
    return decodeURIComponent(
        (location.hash.match(RegExp("[#|&]"+name+'=(.+?)(&|$)'))||[,null])[1]
    );
}

function sha1 (str) {

    // Calculate the sha1 hash of a string
    //
    // version: 1109.2015
    // discuss at: http://phpjs.org/functions/sha1
    // +   original by: Webtoolkit.info (http://www.webtoolkit.info/)
    // + namespaced by: Michael White (http://getsprink.com)
    // +      input by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // -    depends on: utf8_encode
    // *     example 1: sha1('Kevin van Zonneveld');
    // *     returns 1: '54916d2e62f65b3afa6e192e6a601cdbe5cb5897'
    var rotate_left = function (n, s) {
        var t4 = (n << s) | (n >>> (32 - s));
        return t4;
    };

    var cvt_hex = function (val) {
        var str = "";
        var i;
        var v;

        for (i = 7; i >= 0; i--) {
            v = (val >>> (i * 4)) & 0x0f;
            str += v.toString(16);
        }
        return str;
    };

    var blockstart;
    var i, j;
    var W = new Array(80);
    var H0 = 0x67452301;
    var H1 = 0xEFCDAB89;
    var H2 = 0x98BADCFE;
    var H3 = 0x10325476;
    var H4 = 0xC3D2E1F0;
    var A, B, C, D, E;
    var temp;

    str = this.utf8_encode(str);
    var str_len = str.length;

    var word_array = [];
    for (i = 0; i < str_len - 3; i += 4) {
        j = str.charCodeAt(i) << 24 | str.charCodeAt(i + 1) << 16 | str.charCodeAt(i + 2) << 8 | str.charCodeAt(i + 3);
        word_array.push(j);
    }

    switch (str_len % 4) {
    case 0:
        i = 0x080000000;
        break;
    case 1:
        i = str.charCodeAt(str_len - 1) << 24 | 0x0800000;
        break;
    case 2:
        i = str.charCodeAt(str_len - 2) << 24 | str.charCodeAt(str_len - 1) << 16 | 0x08000;
        break;
    case 3:
        i = str.charCodeAt(str_len - 3) << 24 | str.charCodeAt(str_len - 2) << 16 | str.charCodeAt(str_len - 1) << 8 | 0x80;
        break;
    }

    word_array.push(i);

    while ((word_array.length % 16) != 14) {
        word_array.push(0);
    }

    word_array.push(str_len >>> 29);
    word_array.push((str_len << 3) & 0x0ffffffff);

    for (blockstart = 0; blockstart < word_array.length; blockstart += 16) {
        for (i = 0; i < 16; i++) {
            W[i] = word_array[blockstart + i];
        }
        for (i = 16; i <= 79; i++) {
            W[i] = rotate_left(W[i - 3] ^ W[i - 8] ^ W[i - 14] ^ W[i - 16], 1);
        }


        A = H0;
        B = H1;
        C = H2;
        D = H3;
        E = H4;

        for (i = 0; i <= 19; i++) {
            temp = (rotate_left(A, 5) + ((B & C) | (~B & D)) + E + W[i] + 0x5A827999) & 0x0ffffffff;
            E = D;
            D = C;
            C = rotate_left(B, 30);
            B = A;
            A = temp;
        }

        for (i = 20; i <= 39; i++) {
            temp = (rotate_left(A, 5) + (B ^ C ^ D) + E + W[i] + 0x6ED9EBA1) & 0x0ffffffff;
            E = D;
            D = C;
            C = rotate_left(B, 30);
            B = A;
            A = temp;
        }

        for (i = 40; i <= 59; i++) {
            temp = (rotate_left(A, 5) + ((B & C) | (B & D) | (C & D)) + E + W[i] + 0x8F1BBCDC) & 0x0ffffffff;
            E = D;
            D = C;
            C = rotate_left(B, 30);
            B = A;
            A = temp;
        }

        for (i = 60; i <= 79; i++) {
            temp = (rotate_left(A, 5) + (B ^ C ^ D) + E + W[i] + 0xCA62C1D6) & 0x0ffffffff;
            E = D;
            D = C;
            C = rotate_left(B, 30);
            B = A;
            A = temp;
        }

        H0 = (H0 + A) & 0x0ffffffff;
        H1 = (H1 + B) & 0x0ffffffff;
        H2 = (H2 + C) & 0x0ffffffff;
        H3 = (H3 + D) & 0x0ffffffff;
        H4 = (H4 + E) & 0x0ffffffff;
    }

    temp = cvt_hex(H0) + cvt_hex(H1) + cvt_hex(H2) + cvt_hex(H3) + cvt_hex(H4);
    return temp.toLowerCase();
}

function utf8_encode (argString) {
    // Encodes an ISO-8859-1 string to UTF-8
    //
    // version: 1109.2015
    // discuss at: http://phpjs.org/functions/utf8_encode
    // +   original by: Webtoolkit.info (http://www.webtoolkit.info/)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: sowberry
    // +    tweaked by: Jack
    // +   bugfixed by: Onno Marsman
    // +   improved by: Yves Sucaet
    // +   bugfixed by: Onno Marsman
    // +   bugfixed by: Ulrich
    // +   bugfixed by: Rafal Kukawski
    // *     example 1: utf8_encode('Kevin van Zonneveld');
    // *     returns 1: 'Kevin van Zonneveld'
    if (argString === null || typeof argString === "undefined") {
        return "";
    }

    var string = (argString + ''); // .replace(/\r\n/g, "\n").replace(/\r/g, "\n");
    var utftext = "",
        start, end, stringl = 0;

    start = end = 0;
    stringl = string.length;
    for (var n = 0; n < stringl; n++) {
        var c1 = string.charCodeAt(n);
        var enc = null;

        if (c1 < 128) {
            end++;
        } else if (c1 > 127 && c1 < 2048) {
            enc = String.fromCharCode((c1 >> 6) | 192) + String.fromCharCode((c1 & 63) | 128);
        } else {
            enc = String.fromCharCode((c1 >> 12) | 224) + String.fromCharCode(((c1 >> 6) & 63) | 128) + String.fromCharCode((c1 & 63) | 128);
        }
        if (enc !== null) {
            if (end > start) {
                utftext += string.slice(start, end);
            }
            utftext += enc;
            start = end = n + 1;
        }
    }

    if (end > start) {
        utftext += string.slice(start, stringl);
    }

    return utftext;
}


function removeHash () {
    var scrollV, scrollH, loc = window.location;
    if ("pushState" in history)
        history.pushState("", document.title, loc.pathname + loc.search);
    else {
        // Prevent scrolling by storing the page's current scroll offset
        scrollV = document.body.scrollTop;
        scrollH = document.body.scrollLeft;

        loc.hash = "";

        // Restore the scroll offset, should be flicker free
        document.body.scrollTop = scrollV;
        document.body.scrollLeft = scrollH;
    }
}
