function setupVideo(thumb, url) {
    var video = null;
    var videoContainer, videoHide;
    var expanded = false;
    var hovering = false;
    var loop = true;
    var loopControls = [document.createElement("span"), document.createElement("span")];
    var fileInfo = thumb.parentNode.querySelector(".fileinfo");
    var mouseDown = false;

    function unexpand() {
        if (expanded) {
            expanded = false;
            if (video.pause) video.pause();
            videoContainer.style.display = "none";
            thumb.style.display = "inline";
            video.style.maxWidth = "inherit";
            video.style.maxHeight = "inherit";
        }
    }

    function unhover() {
        if (hovering) {
            hovering = false;
            if (video.pause) video.pause();
            videoContainer.style.display = "none";
            video.style.maxWidth = "inherit";
            video.style.maxHeight = "inherit";
        }
    }

    function getVideo() {
        if (video == null) {
            video = document.createElement("video");
            video.src = url;
            video.loop = loop;
            video.innerText = "Your browser does not support HTML5 video.";

            videoHide = document.createElement("img");
            videoHide.src = configRoot + "cc/collapse.gif";
            videoHide.alt = "[ - ]";
            videoHide.title = "Collapse video";
            videoHide.style.marginLeft = "-15px";
            videoHide.style.cssFloat = "left";
            videoHide.addEventListener("click", unexpand, false);

            videoContainer = document.createElement("div");
            videoContainer.style.paddingLeft = "15px";
            videoContainer.style.display = "none";
            videoContainer.appendChild(videoHide);
            videoContainer.appendChild(video);
            thumb.parentNode.insertBefore(videoContainer, thumb.nextSibling);

            // Dragging to the left collapses the video
            video.addEventListener("mousedown", function(e) {
                if (e.button == 0) mouseDown = true;
            }, false);
            video.addEventListener("mouseup", function(e) {
                if (e.button == 0) mouseDown = false;
            }, false);
            video.addEventListener("mouseenter", function(e) {
                mouseDown = false;
            }, false);
            video.addEventListener("mouseout", function(e) {
                if (mouseDown && e.clientX - video.getBoundingClientRect().left <= 0) {
                    unexpand();
                }
                mouseDown = false;
            }, false);
        }
    }

    thumb.addEventListener("click", function(e) {
        if (setting("videoexpand") && !e.shiftKey && !e.ctrlKey && !e.altKey && !e.metaKey) {
            getVideo();
            expanded = true;
            hovering = false;

            video.style.position = "static";
            video.style.pointerEvents = "inherit";
            video.style.display = "inline";
            videoHide.style.display = "inline";
            videoContainer.style.display = "block";
            videoContainer.style.position = "static";
            thumb.style.display = "none";

            video.muted = setting("videomuted");
            video.controls = true;
            if (video.readyState == 0) {
                video.addEventListener("loadedmetadata", expand2, false);
            } else {
                setTimeout(expand2, 0);
            }
            video.play();
            e.preventDefault();
        }
    }, false);

    function expand2() {
        video.style.maxWidth = "100%";
        video.style.maxHeight = window.innerHeight + "px";
        var bottom = video.getBoundingClientRect().bottom;
        if (bottom > window.innerHeight) {
            window.scrollBy(0, bottom - window.innerHeight);
        }
    }

    thumb.addEventListener("mouseover", function(e) {
        if (setting("videohover")) {
            getVideo();
            expanded = false;
            hovering = true;

            var docRight = document.documentElement.getBoundingClientRect().right;
            var thumbRight = thumb.querySelector("img, video").getBoundingClientRect().right;
            var maxWidth = docRight - thumbRight - 20;
            if (maxWidth < 250) maxWidth = 250;

            video.style.position = "fixed";
            video.style.right = "0px";
            video.style.top = "0px";
            var docRight = document.documentElement.getBoundingClientRect().right;
            var thumbRight = thumb.querySelector("img, video").getBoundingClientRect().right;
            video.style.maxWidth = maxWidth + "px";
            video.style.maxHeight = "100%";
            video.style.pointerEvents = "none";

            video.style.display = "inline";
            videoHide.style.display = "none";
            videoContainer.style.display = "inline";
            videoContainer.style.position = "fixed";

            video.muted = setting("videomuted");
            video.controls = false;
            video.play();
        }
    }, false);

    thumb.addEventListener("mouseout", unhover, false);

    function setupLoopControl(i) {
        loopControls[i].addEventListener("click", function(e) {
            loop = (i != 0);
            thumb.href = thumb.href.replace(/([\?&])loop=\d+/, "$1loop=" + i);
            if (video != null) {
                video.loop = loop;
                if (loop && video.currentTime >= video.duration) {
                    video.currentTime = 0;
                }
            }
            loopControls[i].style.fontWeight = "bold";
            loopControls[1-i].style.fontWeight = "inherit";
        }, false);
    }

    loopControls[0].textContent = "[play once]";
    loopControls[1].textContent = "[loop]";
    loopControls[1].style.fontWeight = "bold";
    for (var i = 0; i < 2; i++) {
        setupLoopControl(i);
        loopControls[i].style.whiteSpace = "nowrap";
        fileInfo.appendChild(document.createTextNode(" "));
        fileInfo.appendChild(loopControls[i]);
    }
}

if (window.addEventListener) window.addEventListener("load", function(e) {
    document.body.insertBefore(settingsMenu, document.body.firstChild);

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
}, false);
