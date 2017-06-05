// author: joakimoa
// keyboard navigation option
// v1.0

// adding checkbox for turning on/off
if (window.Options && Options.get_tab('general')) {
	Options.extend_tab("general",
	"<fieldset><legend> Keyboard Navigation </legend>" +
	 ("<label class='keyboardnav' id='keyboardnav' style='padding:0px;'><input type='checkbox' /> Enable Keyboard Navigation (jk: up/down, e: expand) </label>") +
	 "</fieldset>");
}

$('.keyboardnav').on('change', function(){
	var setting = $(this).attr('id');
    console.log("changed keyboardnav");

	localStorage[setting] = $(this).children('input').is(':checked');
});

if (!localStorage.keyboardnav) {
	localStorage.keyboardnav = 'false';
}

// getting locally stored setting
function getSetting(key) {
	return (localStorage[key] == 'true');
}

if (getSetting('keyboardnav')) $('#keyboardnav>input').prop('checked', 'checked');

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
        if (e.keyCode == "74") {
            scrollDown();
        } else if (e.keyCode == "75") {
            scrollUp();
        } else if (e.keyCode == "69") {
            expandFile();
        }
    }
}

// loads main function if checkbox toggled and in a thread
if (getSetting('keyboardnav') && document.getElementsByClassName("thread").length === 1) {
    console.log("test");
    loadKeyboardNav();
}
