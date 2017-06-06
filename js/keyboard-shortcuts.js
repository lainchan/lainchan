// author: joakimoa
// keyboard navigation option
// v1.2

$(document).on("ready", function() {

// adding checkbox for turning on/off
if (window.Options && Options.get_tab('general')) {
	Options.extend_tab("general",
	"<fieldset><legend> Keyboard Navigation </legend>" +
	 ("<label class='keyboardnav' id='keyboardnav' style='padding:0px;'><input type='checkbox' /> Enable Keyboard Navigation</label>") +
	 "<table><tr><td>Action</td><td>Key (a-z)</td></tr>" +
     "<tr><td>Next Reply</td><td><input class='field' name='next-reply' spellcheck='false'></td></tr>" +
     "<tr><td>Previous Reply</td><td><input class='field' name='previous-reply' spellcheck='false'></td></tr>" +
     "<tr><td>Expand File</td><td><input class='field' name='expando' spellcheck='false'></td></tr>" +
     "</table></fieldset>");
}

$('.keyboardnav').on('change', function(){
	var setting = $(this).attr('id');
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

var nextReplyKeycode = 74;     // j
var previousReplyKeycode = 75; // k
var expandoKeycode = 69;       // e

if (getSetting('keyboardnav')) $('#keyboardnav>input').prop('checked', 'checked');
if (isKeySet('next.reply.key')) {
    nextReplyKeycode = localStorage["next.reply.key"];
    nextReplyInput.value = nextReplyKeycode;
}
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
    nextReplyInput.value = "";
    if (e.keyCode >= 65 && e.keyCode <= 90) {
        nextReplyInput.value = String.fromCharCode(e.keyCode);
        localStorage["next.reply.key"] = e.keyCode;
    }
}

function changePreviousReplyKey(e) {
    previousReplyInput.value = "";
    if (e.keyCode >= 65 && e.keyCode <= 90) {
        previousReplyInput.value = String.fromCharCode(e.keyCode);
        localStorage["previous.reply.key"] = e.keyCode;
    }
}

function changeExpandoKey(e) {
    expandoInput.value = "";
    if (e.keyCode >= 65 && e.keyCode <= 90) {
        expandoInput.value = String.fromCharCode(e.keyCode);
        localStorage["expando.key"] = e.keyCode;
    }
}

// loads the main function
function loadKeyboardNav() {
    var replies = document.getElementsByClassName("post reply");
    var current_file = null;
    var highlight_color = "#555";
    var default_color = "black";
    if (replies.length > 0) default_color = window.getComputedStyle(replies[0], null).getPropertyValue("background-color");

    var reply_indexx = 0;
    var image_indexx = -1;
    function focusNextReply() {
        if (reply_indexx < replies.length-1) {
            reply_indexx++;
            image_indexx = -1;
            var images = replies[reply_indexx].getElementsByClassName("file");
            if (images.length !== 0) {
                focusNextImage();
            } else {
                scrollTo(replies[reply_indexx]);
            }
        }
    }

    function focusNextImage() {
        var images = replies[reply_indexx].getElementsByClassName("file");
        if (images.length === 0) {
            focusNextReply();
        } else {
            image_indexx++;
            if (image_indexx > images.length-1) {
                image_indexx = 0;
                focusNextReply();
            } else {
                scrollTo(images[image_indexx]);
            }
        }
    }

    function focusPreviousReply() {
        if (reply_indexx > 0) {
            reply_indexx--;
            var images = replies[reply_indexx].getElementsByClassName("file");
            image_indexx = images.length;
            if (images.length !== 0) {
                focusPreviousImage();
            } else {
                image_indexx = -1;
                scrollTo(replies[reply_indexx]);
            }
        }
    }

    function focusPreviousImage() {
        var images = replies[reply_indexx].getElementsByClassName("file");
        if (images.length === 0) {
            focusPreviousReply();
        } else {
            image_indexx--;
            if (image_indexx < 0) {
                image_indexx = 0;
                focusPreviousReply();
            } else {
                scrollTo(images[image_indexx]);
            }
        }
    }

    function scrollTo(e) {
        if (current_file !== null) {
            current_file.style.backgroundColor = default_color;
        }
        current_file = e;
        e.scrollIntoView();
        e.style.backgroundColor = highlight_color;
    }

    function expandFile() {
        var imgg = replies[reply_indexx].getElementsByClassName("post-image");
        if (imgg.length > 0 && image_indexx > -1) {
            imgg[image_indexx].click();
        }
    }

    // input
    window.addEventListener("keydown", checkKeyPressed, false);

    function checkKeyPressed(e) {
        if (e.keyCode == nextReplyKeycode) {
            focusNextImage();
        } else if (e.keyCode == previousReplyKeycode) {
            focusPreviousImage();
        } else if (e.keyCode == expandoKeycode) {
            expandFile();
        }
    }
}

// loads main function if checkbox toggled and in a thread with replies
if (getSetting('keyboardnav') && document.getElementsByClassName("thread").length === 1 && document.getElementsByClassName("post reply").length > 0) {
    loadKeyboardNav();
}

});
