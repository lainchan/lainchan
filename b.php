<?php
// This script assumes there is at least one normal (non-priority)
// banner!

// Get the files in a directory, returns null if the directory does
// not exist.
function getFilesInDirectory($dir) {
    if (! is_dir($dir)) {
        return null;
    }

    return array_diff(scandir($dir), ['.', '..']);
}

// Serve a random banner and exit.
function serveRandomBanner($dir, $files): never {
    $name = $files[array_rand($files)];

    // snags the extension
    $ext = pathinfo((string) $name, PATHINFO_EXTENSION);

    // send the right headers
    header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1
    header('Pragma: no-cache'); // HTTP 1.0
    header('Expires: 0'); // Proxies
    header("Content-type: image/" . $ext);
    header("Content-Disposition: inline; filename=" . $name);

    // readfile displays the image, passthru seems to spits stream.
    readfile($dir.$name);
    exit;
}

// Get all the banners
$bannerDir = "banners/";
$priorityDir = "banners_priority/";

$banners = getFilesInDirectory($bannerDir);
$priority = getFilesInDirectory($priorityDir);

// If there are priority banners, serve 1/3rd of the time.
if($priority !== null && (is_countable($priority) ? count($priority) : 0) !== 0 && random_int(0,2) === 0) {
    serveRandomBanner($priorityDir, $priority);
}

serveRandomBanner($bannerDir, $banners);
?>
