/*
 * rules-popup.js
 * https://github.com/mkwia/lainchan/js/rules-popup.js
 *
 * Forces user to accept rules from /templates/rules.html on first welcome
 *
 * For the purposes of this script the captcha is called "goutweed" due to
 *     kalyx's request for something "less triggering".
 *
 * 2016 mkwia <github.com/mkwia>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/rules-popup.js';
 *
 */

$(function() {
    if (typeof localStorage.rulesAccepted === "undefined") {

        // generate a 7-character long random string
        goutweed = Math.random().toString(36).substring(2, 9)

        $("body")
            .prepend("<div id='rules-popup'>")
            .prepend("<div id='mobile-rules-popup'>");

        // non-mobile popup
        $("#rules-popup")
            .append("<div class='rules-popup-top'>lainchan rule agreement</div>")
            .append("<div class='rules-popup-content-wrapper'></div>")
            .append("<div class='rules-popup-bottom'></div>");

        $(".rules-popup-content-wrapper")
            .append("<div id='rules-popup-content'></div>");
        $("#rules-popup-content")
            .load("/templates/rules.html", function() {
                $(this).children().removeClass();
            });

        $(".rules-popup-bottom")
            .append("<div class='rules-popup-bottom-instructions'>If you accept the rules, solve the goutweed and press ACCEPT.</div>")
            .append("<div class='rules-popup-goutweed-wrapper'></div>");
        $(".rules-popup-goutweed-wrapper")
            .append("<div class='rules-popup-goutweed'>" + goutweed + "</div>")
            .append("<form class='rules-popup-form' onsubmit=\"if ($('#goutweed').val() == '" + goutweed + "') { localStorage.rulesAccepted = 1; $('#rules-popup').remove(); } return false;\"></form>");
        $(".rules-popup-form")
            .append("<input class='rules-popup-form-input' type='text' id='goutweed' />")
            .append("<input type='submit' value='ACCEPT' />");


        // mobile popup
        $("#mobile-rules-popup")
            .append("<h1 class='mobile-rules-popup-top'>lainchan rule agreement</h1>")
            .append("<div id='mobile-rules-popup-content'></div>")
            .append("<form class='mobile-rules-popup-form' onsubmit=\"if ($('#mobile-goutweed').val() == '" + goutweed + "') { localStorage.rulesAccepted = 1; $('#mobile-rules-popup').remove(); } return false;\"></form>")
        
        $("#mobile-rules-popup-content")
            .load("/templates/rules.html", function() {
                $(this).children().removeClass();
            });
        
        $(".mobile-rules-popup-form")
            .append("<p class='mobile-rules-popup-instructions'>If you accept the rules, solve the goutweed and press ACCEPT.</p>")
            .append("<div class='mobile-rules-popup-goutweed'>" + goutweed + "</div>")
            .append("<input class='mobile-rules-popup-form-input' type='text' id='mobile-goutweed' />")
            .append("<input type='submit' value='ACCEPT' />");
    }
});
