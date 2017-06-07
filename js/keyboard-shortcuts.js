// author: joakimoa
// keyboard navigation
// v1.4

$(document).on("ready", function() {

// adding keyboard navigation to options menu
if (window.Options && Options.get_tab('general')) {
    Options.extend_tab("general",
    "<fieldset><legend> Keyboard Navigation </legend>" +
     ("<label class='keyboardnav' id='keyboardnav' style='padding:0px;'><input type='checkbox' /> Enable Keyboard Navigation</label>") +
     "<table><tr><td>Action</td><td>Key (a-z)</td></tr>" +
     "<tr><td>Next Reply</td><td><input class='field' name='next-reply-input' spellcheck='false'></td></tr>" +
     "<tr><td>Previous Reply</td><td><input class='field' name='previous-reply-input' spellcheck='false'></td></tr>" +
     "<tr><td>Expand File</td><td><input class='field' name='expando-input' spellcheck='false'></td></tr>" +
     "<tr><td>Refresh Thread</td><td><input class='field' name='refresh-thread-input' spellcheck='false'></td></tr>" +
     "</table></fieldset>");
}

$('.keyboardnav').on('change', function(){
    var setting = $(this).attr('id');
    localStorage[setting] = $(this).children('input').is(':checked');
});

var nextReplyKeycode = 74;     // j
var previousReplyKeycode = 75; // k
var expandoKeycode = 69;       // e
var refreshThreadKeycode = 82; // r

if (!localStorage.keyboardnav) {
    localStorage.keyboardnav = 'false';
}
if (!localStorage["next.reply.key"]) {
    localStorage["next.reply.key"] = nextReplyKeycode;
}
if (!localStorage["previous.reply.key"]) {
    localStorage["previous.reply.key"] = previousReplyKeycode;
}
if (!localStorage["expando.key"]) {
    localStorage["expando.key"] = expandoKeycode;
}
if (!localStorage["refresh.thread.key"]) {
    localStorage["refresh.thread.key"] = refreshThreadKeycode;
}

// getting locally stored setting
function getSetting(key) {
    return (localStorage[key] == 'true');
}

function isKeySet(key) {
    return (localStorage[key] !== false);
}

var nextReplyInput = document.getElementsByName("next-reply-input")[0];
var previousReplyInput = document.getElementsByName("previous-reply-input")[0];
var expandoInput = document.getElementsByName("expando-input")[0];
var refreshThreadInput = document.getElementsByName("refresh-thread-input")[0];

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
if (isKeySet('refresh.thread.key')) {
    refreshThreadKeycode = localStorage["refresh.thread.key"];
    refreshThreadInput.value = refreshThreadKeycode;
}

nextReplyInput.value = String.fromCharCode(nextReplyKeycode);
previousReplyInput.value = String.fromCharCode(previousReplyKeycode);
expandoInput.value = String.fromCharCode(expandoKeycode);
refreshThreadInput.value = String.fromCharCode(refreshThreadKeycode);

nextReplyInput.addEventListener("keyup", changeNextReplyKey, false);
previousReplyInput.addEventListener("keyup", changePreviousReplyKey, false);
expandoInput.addEventListener("keyup", changeExpandoKey, false);
refreshThreadInput.addEventListener("keyup", changeRefreshThreadKey, false);

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

function changeRefreshThreadKey(e) {
    refreshThreadInput.value = "";
    if (e.keyCode >= 65 && e.keyCode <= 90) {
        refreshThreadInput.value = String.fromCharCode(e.keyCode);
        localStorage["refresh.thread.key"] = e.keyCode;
    }
}

// loads the main function
function loadKeyboardNav() {
    var replies = document.getElementsByClassName("post reply");
    var current_file = null;
    var default_color = "#333";
    var highlight_color = "#555";

    // grabs base and highlight colors
    if (replies.length > 0) {
        if (replies[0].classList.contains("highlighted")) {
            replies[0].classList.remove('highlighted');
            default_color = window.getComputedStyle(replies[0], null).getPropertyValue("background-color");
            replies[0].classList.add('highlighted');
            highlight_color = window.getComputedStyle(replies[0], null).getPropertyValue("background-color");
        } else {
            default_color = window.getComputedStyle(replies[0], null).getPropertyValue("background-color");
            replies[0].classList.add('highlighted');
            highlight_color = window.getComputedStyle(replies[0], null).getPropertyValue("background-color");
            replies[0].classList.remove('highlighted');
        }
    }

    // check if user is in textareas where hotkeys needs to be disabled
    var text_input_focused = false;
    function checkFocused () {
        var el = document.activeElement;
        if (el && (el.tagName.toLowerCase() == 'input' && el.type == 'text' ||
            el.tagName.toLowerCase() == 'textarea')) {
            text_input_focused = true;
        } else {
            text_input_focused = false;
        }
    }

    document.addEventListener('focus',function(e){
        checkFocused();
    }, true);

    document.addEventListener('blur',function(e){ 
        text_input_focused = false; 
    }, true);

    // strips out <a href="" class="file"> tags
    function getFileList(e) {
        var arr = [];
        var e = e.getElementsByClassName("file");
        if (e.length > 0) {
            for (i = 0; i < e.length; i++) {
                if (e[i].tagName === "DIV") {
                    arr.push(e[i]);
                }
            }
        } 
        return arr;
    }

    var reply_indexx = -1; // might change back to 0
    var image_indexx = -1;
    function focusNextReply() {
        if (reply_indexx < replies.length-1) {
            reply_indexx++;
            image_indexx = -1;
            var images = getFileList(replies[reply_indexx]);
            if (images.length !== 0) {
                focusNextImage();
            } else {
                scrollTo(replies[reply_indexx], true);
            }
        }
    }

    function focusNextImage() {
        var images = getFileList(replies[reply_indexx]);

        if (images.length === 0) {
            focusNextReply();
        } else {
            image_indexx++;
            if (image_indexx > images.length-1) {
                image_indexx = 0;
                focusNextReply();
            } else {
                var im = images[image_indexx].getElementsByClassName("full-image");
                if (im.length === 0) {
                    im = images[image_indexx].getElementsByClassName("post-image");
                }
                scrollTo(im[0], true);
            }
        }
    }

    function focusPreviousReply() {
        if (reply_indexx > 0) {
            reply_indexx--;
            var images = getFileList(replies[reply_indexx]);
            image_indexx = images.length;
            if (images.length !== 0) {
                focusPreviousImage();
            } else {
                image_indexx = -1;
                scrollTo(replies[reply_indexx], false);
            }
        }
    }

    function focusPreviousImage() {
        var images = getFileList(replies[reply_indexx]);
        if (images.length === 0) {
            focusPreviousReply();
        } else {
            image_indexx--;
            if (image_indexx < 0) {
                image_indexx = 0;
                focusPreviousReply();
            } else {
                var im = images[image_indexx].getElementsByClassName("full-image");
                if (im.length === 0) {
                    im = images[image_indexx].getElementsByClassName("post-image")
                }
                scrollTo(im[0], false);
            }
        }
    }

    // from https://gist.github.com/jjmu15/8646226
    function isInViewport(element) {
        var rect = element.getBoundingClientRect();
        var html = document.documentElement;
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || html.clientHeight) &&
            rect.right <= (window.innerWidth || html.clientWidth)
        );
    }

    function scrollTo(e, direction_down) {
        if (current_file !== null && !current_file.classList.contains("highlighted")) {
            current_file.style.backgroundColor = default_color;
        }
        current_file = e;
        if (!isInViewport(e)) {
            if (direction_down) {
                e.scrollIntoView(false);
                window.scrollBy(0, 30);
            } else {
                e.scrollIntoView();
                window.scrollBy(0, -30);
            }
        }

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
        if (!text_input_focused) {
            replies = document.getElementsByClassName("post reply"); // if new ones via AJAX
            if (e.keyCode == nextReplyKeycode) {
                if (reply_indexx === -1) { // needed for initial condition
                    focusNextReply();
                } else {
                    focusNextImage();
                }
            } else if (e.keyCode == previousReplyKeycode) {
                focusPreviousImage();
            } else if (e.keyCode == expandoKeycode) {
                expandFile();
            } else if (e.keyCode == refreshThreadKeycode) {
                document.getElementById("update_thread").click();
            }
        }
    }
}

// loads main function if checkbox toggled and in a thread with replies
if (getSetting('keyboardnav') && document.getElementsByClassName("thread").length === 1 && document.getElementsByClassName("post reply").length > 0) {
    loadKeyboardNav();
}

});