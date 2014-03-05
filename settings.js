// Scripts obtain settings by calling this function
function setting(name) {
    return JSON.parse(localStorage[name]);
}

// Settings should be changed with this function
function changeSetting(name, value) {
    localStorage[name] = JSON.stringify(value);
}

// Default settings
function setDefault(name, value) {
    if (!(name in localStorage)) {
        changeSetting(name, value);
    }
}
setDefault("videoexpand", true);
setDefault("videohover", false);
setDefault("videovolume", 1.0);

// Create settings menu
var settingsMenu = document.createElement("span");
settingsMenu.className = "settings";
settingsMenu.innerHTML = '<span>[Settings]</span>'
    + '<div style="display: none; text-align: left; position: absolute; right: 0px; margin-left: -999em; margin-top: -1px; padding-top: 1px;">'
    + '<label><input type="checkbox" name="videoexpand">Expand videos inline</label><br>'
    + '<label><input type="checkbox" name="videohover">Play videos on hover</label><br>'
    + '<label><input type="range" name="videovolume" min="0" max="1" step="0.01" style="width: 4em; height: 1ex; vertical-align: middle; margin: 0px;">Default volume</label><br>'
    + '</div>';

function refreshSettings() {
    var settingsItems = settingsMenu.getElementsByTagName("input");
    for (var i = 0; i < settingsItems.length; i++) {
        var control = settingsItems[i];
        if (control.type == "checkbox") {
            control.checked = setting(control.name);
        } else if (control.type == "range") {
            control.value = setting(control.name);
        }
    }
}

function setupControl(control) {
    if (control.addEventListener) control.addEventListener("change", function(e) {
        if (control.type == "checkbox") {
            changeSetting(control.name, control.checked);
        } else if (control.type == "range") {
            changeSetting(control.name, control.value);
        }
    }, false);
}

refreshSettings();
var settingsItems = settingsMenu.getElementsByTagName("input");
for (var i = 0; i < settingsItems.length; i++) {
    setupControl(settingsItems[i]);
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
