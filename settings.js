// Scripts obtain settings by calling this function
function setting(name) {
    return JSON.parse(localStorage[name]);
}

// Default settings
function setDefault(name, value) {
    if (!(name in localStorage)) {
        localStorage[name] = JSON.stringify(value);
    }
}
setDefault("videoexpand", true);
setDefault("videohover", false);
setDefault("videomuted", false);

// Create settings menu
var settingsMenu = document.createElement("span");
settingsMenu.className = "settings";
settingsMenu.innerHTML = '<span>[Settings]</span>'
    + '<div style="display: none; text-align: left; position: absolute; right: 0px; margin-left: -999em; margin-top: -1px; padding-top: 1px;">'
    + '<label><input type="checkbox" name="videoexpand">Expand videos inline</label><br>'
    + '<label><input type="checkbox" name="videohover">Play videos on hover</label><br>'
    + '<label><input type="checkbox" name="videomuted">Open videos muted</label><br>'
    + '</div>';

function refreshSettings() {
    var settingsItems = settingsMenu.getElementsByTagName("input");
    for (var i = 0; i < settingsItems.length; i++) {
        var box = settingsItems[i];
        box.checked = setting(box.name);
    }
}

function setupCheckbox(box) {
    if (box.addEventListener) box.addEventListener("change", function(e) {
        localStorage[box.name] = JSON.stringify(box.checked);
    }, false);
}

refreshSettings();
var settingsItems = settingsMenu.getElementsByTagName("input");
for (var i = 0; i < settingsItems.length; i++) {
    setupCheckbox(settingsItems[i]);
}

if (settingsMenu.addEventListener) {
    settingsMenu.addEventListener("mouseover", function(e) {
        refreshSettings();
        settingsMenu.getElementsByTagName("span")[0].style.fontWeight = "bold";
        settingsMenu.getElementsByTagName("div")[0].style.display = "block";
    }, false);
    settingsMenu.addEventListener("mouseout", function(e) {
        settingsMenu.getElementsByTagName("span")[0].style.fontWeight = "normal";
        settingsMenu.getElementsByTagName("div")[0].style.display = "none";
    }, false);
}
