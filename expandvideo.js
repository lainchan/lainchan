function setupVideo(thumb, url) {
    var video = null;
    var videoContainer, videoHide;
    var expanded = false;
    var hovering = false;

    function unexpand() {
        if (expanded) {
            expanded = false;
            if (video.pause) video.pause();
            videoContainer.style.display = "none";
            thumb.style.display = "inline";
        }
    }

    function unhover() {
        if (hovering) {
            hovering = false;
            if (video.pause) video.pause();
            video.style.display = "none";
        }
    }

    function getVideo() {
        if (video == null) {
            video = document.createElement("video");
            video.src = url;
            video.loop = true;
            video.innerText = "Your browser does not support HTML5 video.";
            video.onclick = function(e) {
                if (e.shiftKey) {
                    unexpand();
                    e.preventDefault();
                }
            };

            videoHide = document.createElement("img");
            videoHide.src = configRoot + "cc/collapse.gif";
            videoHide.alt = "[ - ]";
            videoHide.title = "Collapse to thumbnail";
            videoHide.style.verticalAlign = "top";
            videoHide.style.marginRight = "2px";
            videoHide.onclick = unexpand;

            videoContainer = document.createElement("div");
            videoContainer.style.whiteSpace = "nowrap";
            videoContainer.appendChild(videoHide);
            videoContainer.appendChild(video);
            thumb.parentNode.insertBefore(videoContainer, thumb.nextSibling);
        }
    }

    thumb.onclick = function(e) {
        if (setting("videoexpand") && !e.shiftKey && !e.ctrlKey && !e.altKey && !e.metaKey) {
            getVideo();
            expanded = true;
            hovering = false;

            video.style.position = "static";
            video.style.maxWidth = "";
            video.style.maxHeight = "";

            video.style.display = "inline";
            videoHide.style.display = "inline";
            videoContainer.style.display = "block";
            thumb.style.display = "none";

            video.muted = setting("videomuted");
            video.controls = true;
            video.play();
            return false;
        }
    };

    thumb.onmouseover = function(e) {
        if (setting("videohover")) {
            getVideo();
            expanded = false;
            hovering = true;

            video.style.position = "fixed";
            video.style.right = "0px";
            video.style.top = "0px";
            video.style.maxWidth = (document.body.parentNode.getBoundingClientRect().right - thumb.getBoundingClientRect().right) + "px";
            video.style.maxHeight = "100%";

            video.style.display = "inline";
            videoHide.style.display = "none";
            videoContainer.style.display = "inline";

            video.muted = setting("videomuted");
            video.controls = false;
            video.play();
        }
    };

    thumb.onmouseout = unhover;
}

window.onload = function() {
    settingsPanel.style.position = "absolute";
    settingsPanel.style.top = "1em";
    settingsPanel.style.right = "1em";
    document.body.insertBefore(settingsPanel, document.body.firstChild);

    var thumbs = document.querySelectorAll("a.file");
    for (var i = 0; i < thumbs.length; i++) {
        if (/\.webm$/.test(thumbs[i].pathname)) {
            setupVideo(thumbs[i], thumbs[i].href);
        } else {
            var m = thumbs[i].search.match(/\bv=([^&]*)/);
            if (m != null) {
                var url = decodeURIComponent(m[1]);
                if (/\.webm$/.test(url)) setupVideo(thumbs[i], url);
            }
        }
    }
};
