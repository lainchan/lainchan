<?php 
/* This file is dedicated to the public domain; you may do as you wish with it. */
$params = '?v=' . urlencode($_GET['v']) . '&amp;t=' . urlencode($_GET['t']);
$loop = ($_GET['loop'] != "0");
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($_GET['t']); ?></title>
    <link rel="stylesheet" href="playerstyle.css">
    <script src="settings.js"></script>
    <script src="playersettings.js"></script>
</head>
<body>
    <div id="playerheader">
        <a id="loop0" href="<?php echo $params; ?>&amp;loop=0"<?php if (!$loop) echo ' style="font-weight: bold"'; ?>>[play once]</a>
        <a id="loop1" href="<?php echo $params; ?>&amp;loop=1"<?php if ($loop) echo ' style="font-weight: bold"'; ?>>[loop]</a>
    </div>
    <div id="playercontent">
        <video controls<?php if ($loop) echo ' loop'; ?> src="<?php echo htmlspecialchars($_GET['v']); ?>">
            Your browser does not support HTML5 video. <a href="<?php echo htmlspecialchars($_GET['v']); ?>">[Download]</a>
        </video>
    </div>
</body>
</html>
