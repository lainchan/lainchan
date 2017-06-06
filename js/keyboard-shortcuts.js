// author: joakimoa
// keyboard navigation option
// v1.1

// todo change document.getElementsByClassName("file multifile"); to "post-image"
// todo change to navigation replies > post-image e.g.
// for reply in replies:
//     for post-image in reply:
//          scrollTo(post-image)


// adding checkbox for turning on/off
if (window.Options && Options.get_tab('general')) {
	Options.extend_tab("general",
	"<fieldset><legend> Keyboard Navigation </legend>" +
	 "<label class='keyboardnav' id='keyboardnav' style='padding:0px;'><input type='checkbox' /> Enable Keyboard Navigation</label>" +
	 "<table><tr><td>Action</td><td>Key</td></tr>" +
     "<tr><td>Next Reply</td><td><input class='field' name='next-reply' spellcheck='false'></td></tr>" +
     "<tr><td>Previous Reply</td><td><input class='field' name='previous-reply' spellcheck='false'></td></tr>" +
     "<tr><td>Expand File</td><td><input class='field' name='expando' spellcheck='false'></td></tr>" +
     "</table></fieldset>");
}

$('.keyboardnav').on('change', function(){
	var setting = $(this).attr('id');
    console.log("changed keyboardnav");

	localStorage[setting] = $(this).children('input').is(':checked');
});

if (!localStorage.keyboardnav) {
	localStorage.keyboardnav = 'false';
}
if (!localStorage["next.reply.key"]) {
    localStorage["next.reply.key"] = 74;
}
if (!localStorage["previous.reply.key"]) {
    localStorage["previous.reply.key"] = 75;
}
if (!localStorage["expando.key"]) {
    localStorage["expando.key"] = 69;
}

// getting locally stored setting
function getSetting(key) {
	return (localStorage[key] == 'true');
}

function isKeySet(key) {
    return (localStorage[key] !== false);
}

var nextReplyInput = document.getElementsByName("next-reply")[0];
var previousReplyInput = document.getElementsByName("previous-reply")[0];
var expandoInput = document.getElementsByName("expando")[0];

var nextReplyKeycode = 74;
var previousReplyKeycode = 75;
var expandoKeycode = 69;

if (getSetting('keyboardnav')) $('#keyboardnav>input').prop('checked', 'checked');
if (isKeySet('next.reply.key')) {
    nextReplyKeycode = localStorage["next.reply.key"];
    nextReplyInput.value = nextReplyKeycode;
}  // need to add so it loads the settings if there are any, to both the vars and to the text fields
if (isKeySet('previous.reply.key')) {
    previousReplyKeycode = localStorage["previous.reply.key"];
    previousReplyInput.value = previousReplyKeycode;
}
if (isKeySet('expando.key')) {
    expandoKeycode = localStorage["expando.key"];
    expandoInput.value = expandoKeycode;
}

document.getElementsByName("next-reply")[0].value = String.fromCharCode(nextReplyKeycode);
document.getElementsByName("previous-reply")[0].value = String.fromCharCode(previousReplyKeycode);
document.getElementsByName("expando")[0].value = String.fromCharCode(expandoKeycode);

nextReplyInput.addEventListener("keyup", changeNextReplyKey, false);
previousReplyInput.addEventListener("keyup", changePreviousReplyKey, false);
expandoInput.addEventListener("keyup", changeExpandoKey, false);

function changeNextReplyKey(e) {
    //console.log(String.fromCharCode(e.keyCode));
    nextReplyInput.value = "";
    if (e.keyCode >= 65 && e.keyCode <= 90) {
        nextReplyInput.value = String.fromCharCode(e.keyCode);
        localStorage["next.reply.key"] = e.keyCode;
    }
}

function changePreviousReplyKey(e) {
    //console.log(String.fromCharCode(e.keyCode));
    previousReplyInput.value = "";
    if (e.keyCode >= 65 && e.keyCode <= 90) {
        previousReplyInput.value = String.fromCharCode(e.keyCode);
        localStorage["previous.reply.key"] = e.keyCode;
    }
}

function changeExpandoKey(e) {
    //console.log(String.fromCharCode(e.keyCode));
    expandoInput.value = "";
    if (e.keyCode >= 65 && e.keyCode <= 90) {
        expandoInput.value = String.fromCharCode(e.keyCode);
        localStorage["expando.key"] = e.keyCode;
    }
}

// loads the main function
function loadKeyboardNav() {
    // get arr and nav
    var files = document.getElementsByClassName("file multifile");
    var current_file = null;
    var default_color = "black";
    default_color = window.getComputedStyle(files[0], null).getPropertyValue("background-color");

    var k = -1;
    function scrollDown() {
        if (k < files.length) {
            k++;
            scrollTo(files[k]);
        }
    }

    function scrollUp() {
        if (k > 0) {
            k--;
            scrollTo(files[k]);
        }
    }

    function scrollTo(e) {
        if (current_file !== null) {
            current_file.style.backgroundColor = default_color;
        }
        current_file = e;
        e.scrollIntoView();
        e.style.backgroundColor = "#1D1D21";
    }

    function expandFile() {
        files[k].getElementsByClassName("post-image")[0].click();
    }

    // input
    window.addEventListener("keydown", checkKeyPressed, false);

    function checkKeyPressed(e) {
        if (e.keyCode == nextReplyKeycode) {
            scrollDown();
        } else if (e.keyCode == previousReplyKeycode) {
            scrollUp();
        } else if (e.keyCode == expandoKeycode && k > -1) {
            expandFile();
        }
    }
}

// loads main function if checkbox toggled and in a thread
if (getSetting('keyboardnav') && document.getElementsByClassName("thread").length === 1) {
    loadKeyboardNav();
}
