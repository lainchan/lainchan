<html>
<head>
<title>Lainchan Banners</title>
</head>
<body>
<?php
function listBannersInDir($dir) {
    if ($handle = opendir($dir)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                echo "<a href=\"$dir/$entry\"><img src=\"$dir/$entry\" alt=\"$entry\" style=\"width:348px;height:128px\"></a> ";
            }
        }
        closedir($handle);
    }
}

listBannersInDir("banners_priority");
listBannersInDir("banners");
?>
</body>
</html>
