// Functions that we've written ourselves

function attemptLogin() {

    // Put the "Go" button in a loading state
    $("#login-submit").button("loading");

    var passwordHash = sha1($("#login-password").val());
    var email = $("#login-email").val();

    var urlString = "/attempt_login.php?email=" + email + "&password_hash=" + passwordHash;

    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            if (parseInt(data) != 0) {
                window.location.replace(window.location.href);
            } else {
                $("#login-error").show();
            }
        },
        async: false
    });
}

function showFollowModal() {
    $("#follow-email").val("");
    $("#follow-email-check").empty();
    $("#follow-submit").button("reset");
    if (isLoggedIn()) {
        // If the user is logged in, simply create the follower relationship
        $("#follow-submit").button("loading");

    } else {
        // If the user is not logged in, we need to ask for his email address
        // to make sure the rightful owner of the email address actually wants
        // to follow the album
        $("#follow-modal").modal('show');
        $("#follow-email").focus();
    }
}

function unfollowAlbum(user_id, album_id, token) {
    $("#unfollow-submit").button("loading");

    var urlString = "/unfollow_album.php?user_id=" + user_id + "&album_id=" + album_id + "&token=" + token;

    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            if (parseInt(data) == 1) {
                window.location.replace(window.location.href.split('#')[0] + "#alert=2");
                window.location.reload(true);
            } else {
                // bad token
            }
        },
        async: false
    });

}


function submitEmailToFollow(album_id) {

    $("#follow-submit").button("loading");
    var email = $("#follow-email").val();
    var urlString = "/send_follow_email.php?email=" + email + "&album_id=" + album_id;

    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            $("#follow-modal").modal('hide');
            $("#header-alert-title").html("You're not quite done!");
            $("#header-alert-text").html("Click the link in the email we just sent you to confirm that you want to follow this album.");
            $("#header-alert").addClass("alert-info");
            $("#header-alert").fadeIn();
        },
        async: false
    });
}


function saveUsernamePassword() {

    // Put the "Go" button in a loading state
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
                window.location.replace(window.location.href);
            }
        },
        async: false
    });

}

function signupUser() {

    // Put the "Go" button in a loading state
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
            window.location.replace(window.location.href);
        },
        async: false
    });
}



function setFollowSubmitButton() {
     if ($("#follow-email-check").data("correct") == 1) {
        $("#follow-submit").removeClass("disabled");
     } else {
        $("#follow-submit").addClass("disabled");
     }
}

function setSignupSubmitButton() {
    $("#signup-submit").addClass("disabled");
    if ($("#signup-email-check").data("correct") == 1 &&
        $("#signup-username-check").data("correct") == 1 &&
        $("#signup-password").val() != "") {
        $("#signup-submit").removeClass("disabled");
    } else {
        $("#signup-submit").addClass("disabled");
    }
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






function checkEmailIsUnique(prefix) {

    $("#" + prefix + "-email-check").data("correct", 0);

    var emailEntered = $("#" + prefix + "-email").val();

    if (emailEntered == "") {
        $("#" + prefix + "-email-check").html("Type in your email");
        return;
    }

    if (!validateEmail(emailEntered)) {
        $("#" + prefix + "-email-check").html("That doesn't appear to be a valid address");
        return;
    }

    if (prefix == "follow") {
        $("#follow-email-check").html("We'll send you notifications to this email");
        $("#follow-email-check").data("correct", 1);
        return;
    }

    var urlString = "/check_email_is_unique.php?email=" + emailEntered;

    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            data = parseInt(data);
            if (data == 0) {
                $("#" + prefix + "-email-check").html("Looks good!");
                $("#" + prefix + "-email-check").data("correct", 1);
            } else {
                $("#" + prefix + "-email-check").html("<b>" + emailEntered + "</b> has already signed up");
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
        $("#" + prefix + "-username-check").html("Only letters and numbers, please");
        return;
    }

    var urlString = "/check_username_is_unique.php?username=" + usernameEntered;
    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            data = parseInt(data);
            if (data == 0) {
                $("#" + prefix + "-username-check").html("<b>" + usernameEntered + "</b> is available!");
                $("#" + prefix + "-username-check").data("correct", 1);
            } else if (isLoggedIn() && data == gUser["id"]) {
                $("#" + prefix + "-username-check").html("Ummm...that's already your username");
            } else {
                $("#" + prefix + "-username-check").html("<b>" + usernameEntered + "</b> is already taken (try something else)");
            }
        },
        async: false
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

function resizeWindow() {
    if (span3Width != $(".span3").width()) {
        setMasonry();
        $(".item").css("margin-bottom", $(".span3").css("margin-left"));
        span3Width = $(".span3").width();
    }
}

function setMasonry() {
    var container = $("#masonry-container");
    var mobileWidth = 480;
    var maxWidth = 768;

    $(container).imagesLoaded(function() {
        $(container).masonry({
            itemSelector : '.item',
            isAnimated: true,
            columnWidth: function() {
                // Get the width of each span3 + the margin on the left
                // because the Masonry column width includes the space
                // "between" columns.
                return $(".span3").width() + parseInt($(".span3").css("margin-left").match(/\d+/));
            }
        });
    });
}

function preload(arrayOfImages) {
    $(arrayOfImages).each(function(){
        $('<img/>')[0].src = this;
    });
}

function deletePhotoFromAlbum(photoID, albumID, albumPhotoID, coverPhotoID, token) {
    var urlString = "/delete_photo_from_album.php?photo_id=" + photoID +
                                                "&album_id=" + albumID +
                                                "&albumphoto_id=" + albumPhotoID +
                                                "&cover_photo_id=" + coverPhotoID +
                                                "&token=" + token;
    jQuery.ajax({
        type: "GET",
        url: urlString,
        success: function(data) {
            if (parseInt(data) == 1) {
                $("#photo-" + photoID).fadeOut(function() {
                    $("#photo-" + photoID).remove();
                    $("#masonry-container").masonry("reload");
                });
            } else if (parseInt(data) == 0) {
                debug("Could not delete photo because the token's wrong");
                return;
            } else {
                debug("Could not delete photo (unknown error)");
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
        returnArr["title"] = "You're now following this album!";
        returnArr["text"] = "This is a title";
        returnArr["class"] = "alert-info";
    } else if (alert == 2) {
        returnArr["title"] = "You're no longer following this album.";
        returnArr["text"] = "";
        returnArr["class"] = "alert-info";
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

//