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
