// Create settings menu
var settingsMenu = document.createElement("div");
settingsMenu.style.position = "absolute";
settingsMenu.style.top = "1em";
settingsMenu.style.right = "1em";
settingsMenu.innerHTML = '<div style="text-align: right;">Settings</div><div style="display: none;">'
    + '<label><input type="checkbox" name="videoexpand">Expand videos inline</label><br>'
    + '<label><input type="checkbox" name="videohover">Play videos on hover</label><br>'
    + '<label><input type="checkbox" name="videomuted">Start videos muted</label><br>'
    + '</div>';

function refreshSettings() {
    var settingsItems = settingsMenu.getElementsByTagName("input");
    for (var i = 0; i < settingsItems.length; i++) {
        var box = settingsItems[i];
        box.checked = setting(box.name);
    }
}

function setupCheckbox(box) {
    box.onchange = function(e) {
        localStorage[box.name] = JSON.stringify(box.checked);
    };
}

refreshSettings();
var settingsItems = settingsMenu.getElementsByTagName("input");
for (var i = 0; i < settingsItems.length; i++) {
    setupCheckbox(settingsItems[i]);
}

settingsMenu.onmouseover = function(e) {
    refreshSettings();
    var settingsSections = settingsMenu.getElementsByTagName("div");
    settingsSections[0].style.fontWeight = "bold";
    settingsSections[1].style.display = "block";
};
settingsMenu.onmouseout = function(e) {
    var settingsSections = settingsMenu.getElementsByTagName("div");
    settingsSections[0].style.fontWeight = "normal";
    settingsSections[1].style.display = "none";
};

if (window.addEventListener) window.addEventListener("load", function(e) {
    document.body.insertBefore(settingsMenu, document.body.firstChild);
}, false);
