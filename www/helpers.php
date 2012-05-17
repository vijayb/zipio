<?php

function rand_string($length) {
    $chars = "abcdefghijklmnopqrstuvwxyz";
    return substr(str_shuffle($chars),0,$length);
}

function generate_usercode($length = 8, $include_numbers = false) {     
    $vowels = array("a", "e", "i", "o", "u"); 
    $cons = array("b", "c", "d", "g", "h", "j", "k", "l", "m", "n", "p", "r",
                    "s", "t", "u", "v", "w", "tr", "cr", "br", "fr", "th", "dr",
                          "ch", "ph", "wr", "st", "sp", "sw", "pr", "sl", "cl"); 
     
    $num_vowels = count($vowels); 
    $num_cons = count($cons); 
    
    $pre = $post = $password = '';

    if ($include_numbers) {
        while ((($length/(strlen($pre) + strlen($post)+1)) > 2)) {        
            if (rand(0,1) === 0) {
                $pre .= chr(rand(48,57));
            } else {
                $post .= chr(rand(48,57));
            }
        }
    }
    
    $string_length = $length - (strlen($pre) + strlen($post));

    while (strlen($password) < $string_length) { 
        $password .= $cons[rand(0, $num_cons - 1)] . $vowels[rand(0, $num_vowels - 1)]; 
    } 
    return $pre.substr($password, 0, $string_length ) . $post;  
}



function debug($string, $color = "black") {
    print("<span style='color:$color;'>$string</span>" . "\n<br>");
}
?>
