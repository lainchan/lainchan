if (window.addEventListener) window.addEventListener("load", function(e) {
    document.getElementById("playerheader").appendChild(settingsMenu);

    var video = document.getElementsByTagName("video")[0];

    var loopLinks = [document.getElementById("loop0"), document.getElementById("loop1")];
    function setupLoopLink(i) {
        loopLinks[i].addEventListener("click", function(e) {
            video.loop = (i != 0);
            if (i != 0 && video.currentTime >= video.duration) {
                video.currentTime = 0;
            }
            loopLinks[i].style.fontWeight = "bold";
            loopLinks[1-i].style.fontWeight = "inherit";
            e.preventDefault();
        }, false);
    }
    for (var i = 0; i < 2; i++) {
        setupLoopLink(i);
    }

    video.muted = setting("videomuted");
    video.play();
}, false);
