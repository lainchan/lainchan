<html>
<head>
<title>Lainchan Banners</title>
</head>
<body>
<?php
if ($handle = opendir('banners')) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            echo "<a href=\"banners/$entry\"><img src=\"banners/$entry\" alt=\"$entry\" style=\"width:348px;height:128px\"></a> ";
        }
    }
    closedir($handle);
}
?>
</body>
</html>
