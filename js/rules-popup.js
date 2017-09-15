/*
 * rules-popup.js
 * https://github.com/mkwia/lainchan/js/rules-popup.js
 *
 * Forces user to accept rules from /templates/rules.html on first welcome
 *
 * 2016 mkwia <github.com/mkwia>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/rules-popup.js';
 *
 */

$(window).ready(function() {
  if (typeof localStorage.rulesAccepted === "undefined") {

    // generate a 7-character long random string
    captcha = Math.random().toString(36).substring(2, 9)

    $("body")
        .prepend("<div id='rules-popup'>");

    $("#rules-popup")
        .append("<div class='rules-popup-top'>lainchan rule agreement</div>")
        .append("<div class='rules-popup-content-wrapper'></div>")
        .append("<div class='rules-popup-bottom'></div>");

    $(".rules-popup-content-wrapper")
        .append("<div id='rules-popup-content'></div>");
    $("#rules-popup-content")
        .load("/templates/rules.html");

    $(".rules-popup-bottom")
        .append("<div class='rules-popup-bottom-instructions'>If you accept the rules, retype the captcha and press ACCEPT.</div>")
        .append("<div class='rules-popup-captcha-wrapper'></div>");
    $(".rules-popup-captcha-wrapper")
        .append("<div class='rules-popup-captcha'>" + captcha + "</div>")
        .append("<form class='rules-popup-form' onsubmit=\"if ($('#captcha').val() == '" + captcha + "') { localStorage.rulesAccepted = 1; $('#rules-popup').remove(); } return false;\"></form>");
    $(".rules-popup-form")
        .append("<input class='rules-popup-form-input' type='text' id='captcha' />")
        .append("<input type='submit' value='ACCEPT' />");
  }
})
