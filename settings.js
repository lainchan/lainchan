var settingsPanel = document.createElement("div");
settingsPanel.innerHTML = '<div style="text-align: right;">Settings</div><div style="display: none;">'
    + '<label><input type="checkbox" name="videoexpand" checked>Expand videos inline</label><br>'
    + '<label><input type="checkbox" name="videohover" checked>Play videos on hover</label><br>'
    + '<label><input type="checkbox" name="videomuted">Start videos muted</label><br>'
    + '</div>';

function refreshSettings() {
    var settingsItems = settingsPanel.getElementsByTagName("input");
    for (var i = 0; i < settingsItems.length; i++) {
        var box = settingsItems[i];
        if (box.name in localStorage) {
            box.checked = JSON.parse(localStorage[box.name]);
        } else {
            localStorage[box.name] = JSON.stringify(box.checked);
        }
    }
}

function setupCheckbox(box) {
    box.onchange = function(e) {
        localStorage[box.name] = JSON.stringify(box.checked);
    };
}

refreshSettings();
var settingsItems = settingsPanel.getElementsByTagName("input");
for (var i = 0; i < settingsItems.length; i++) {
    setupCheckbox(settingsItems[i]);
}

settingsPanel.onmouseover = function(e) {
    refreshSettings();
    var settingsSections = settingsPanel.getElementsByTagName("div");
    settingsSections[0].style.fontWeight = "bold";
    settingsSections[1].style.display = "block";
};
settingsPanel.onmouseout = function(e) {
    var settingsSections = settingsPanel.getElementsByTagName("div");
    settingsSections[0].style.fontWeight = "normal";
    settingsSections[1].style.display = "none";
};

function setting(name) {
    return JSON.parse(localStorage[name]);
}
