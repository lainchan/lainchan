if (window.addEventListener) window.addEventListener("load", function(e) {
    var video = document.getElementsByTagName("video")[0];
    video.muted = setting("videomuted");
    video.play();
}, false);
