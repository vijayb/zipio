<?

$path = "attachments";
$dir_handle = @opendir($path) or die("Unable to open $path"); 

while ($file = readdir($dir_handle)) {
    if ($file == "." || $file == ".." || $file == "index.php") continue;
    echo "<img src=\"/attachments/$file\">";
}

closedir($dir_handle); 

?>
