window.onload = function() {
    settingsPanel.style.cssFloat = "right";
    document.body.insertBefore(settingsPanel, document.body.firstChild);
    var video = document.getElementsByTagName("video")[0];
    video.muted = setting("videomuted");
    video.play();
};
