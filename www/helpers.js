var imageFiltered = {};

////////////////////////////////////////////////////////////////////////////////
// SHOW MODALS
////////////////////////////////////////////////////////////////////////////////


function showSignupModal() {
    $('#signup-modal').modal('show');
    $('#signup-username').focus();
}

function showLoginModal() {
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
                    $("#" + prefix + "-email-check").html("<i class='icon-ok'></i> We'll send a password reset link to <b>" + emailEntered + "</b>");
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






















function resetPhotoToOriginal(albumphotoID, croppedOriginalSrc, bigOriginalSrc) {
    $("#save-" + albumphotoID).show();
    $("#albumphoto-" + albumphotoID).find("canvas").remove();
    $("#image-" + albumphotoID).attr("src", croppedOriginalSrc);
    $("#fancybox-" + albumphotoID).attr("href", bigOriginalSrc);
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

        $("#cover-" + albumphotoID).fadeIn();

        if (imageFiltered[albumphotoID] == 0) {
            $.ajax({
                type: 'GET',
                url: '/save_filtered.php?albumphoto_id=' + albumphotoID + '&reset_to_original',
                sync: false,
                success: function(data) {
                    console.log(data);
                    $("#fancybox-" + albumphotoID).attr("href", bigOriginalSrc);
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
            FB.api('/me', function (response) {
                console.log('Good to see you, ' + response.name + '.');
            });
        } else {
            console.log('User cancelled login or did not fully authorize.');
        }
    }, {scope: 'email, publish_stream'});
}







function toggleLikePhoto(userID, albumphotoID, albumID) {

    var likedNew;

    if ($('#like-' + albumphotoID).hasClass("red-heart")) {
        $('#albumphoto-' + albumphotoID).attr('likes', parseInt($('#albumphoto-' + albumphotoID).attr('likes')) - 1);
        likedNew = 0;
    } else {
        $('#albumphoto-' + albumphotoID).attr('likes', parseInt($('#albumphoto-' + albumphotoID).attr('likes')) + 1);
        likedNew = 1;
    }

    $('#like-count-' + albumphotoID).html($('#albumphoto-' + albumphotoID).attr('likes'));

    if (likedNew) {
        $('#like-' + albumphotoID).addClass("red-heart").removeClass("gray-heart");
    } else {
        $('#like-' + albumphotoID).addClass("gray-heart").removeClass("red-heart");
    }

    jQuery.ajax({
        type: "GET",
        url: "/record_like_activity.php?like=" + likedNew + "&userID=" + userID + "&albumphotoID=" + albumphotoID + "&albumID=" + albumID,
        async: true,
        success: function (data) {
            console.log(data);
        }
    });

}

function unlikePhoto(userID, albumphotoID, albumID) {
    //alert('#like-'+albumphotoID);
    $('#like-' + albumphotoID).attr('src', '/grey_heart.png');
    $('#a-like-' + albumphotoID).attr('onclick', 'likePhoto(' + userID + ',' + albumphotoID + ',' + albumID + ')');

    jQuery.ajax({
        type: "GET",
        url: "/record_like_activity.php?like=0&userID=" + userID + "&albumphotoID=" + albumphotoID + "&albumID=" + albumID,
        async: true,
        success: function (data) {
            console.log(data);
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

function deletePhotoFromAlbum(albumPhotoID, token) {
    $("#albumphoto-" + albumPhotoID).fadeOut(100);
    var urlString = "/delete_photo_from_album.php?albumphoto_id=" + albumPhotoID +
                                                "&token=" + token;
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
