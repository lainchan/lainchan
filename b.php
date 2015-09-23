<?php
//renaming to b.php
$dir = "banners/";
$files = scandir($dir);
$images = array_diff($files, array('.', '..'));
$name = $images[array_rand($images)];

// snags the extension
$img_extension = pathinfo($name, PATHINFO_EXTENSION);

// send the right headers
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1
header('Pragma: no-cache'); // HTTP 1.0
header('Expires: 0'); // Proxies
header("Content-type: image/" . $img_extension);
header("Content-Disposition: inline; filename=" . $name);

// readfile displays the image, passthru seems to spits stream.
readfile($dir.$name);
exit;
?>
