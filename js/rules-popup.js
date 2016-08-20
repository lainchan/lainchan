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

$(function() {
    if (typeof localStorage.rulesAccepted === "undefined") + function(c) {
        var d = $("<div id='rules'>").prependTo("body").width("80%").height("80%")
            .css("z-index", 9999).css("position", "fixed")
            .css("top", "50%").css("bottom", 0).css("left", "50%").css("right", 0)
            .css("margin-top", "-40vh").css("margin-left", "-40%")
            .css("background", "black")
            .css("text-align", "center").css("font-family", "sans-serif")
            .css("font-size", "14px").css("color", "white")

        d.html("" +

            "<div style='font-size: 40px; line-height: 60px; position: absolute; top: 0px; height: 60px; width: 100%;'>lainchan rule agreement</div>" +

            "<div style='text-align: left; position: absolute; bottom: 80px; top: 60px;" +
            "width: 100%; background-color: #ddd; overflow: auto; font-family: serif; color: #444;'>" +
            "<div style='padding: 10px; font-size: 12px;' id='rules-actual'>" +
            "</div>" +
            "</div>" +

            "<div style='bottom: 0px; height: 80px; width: 100%; position: absolute;'>" +
            "<div style='line-height: 40px;'>If you accept the rules, retype the captcha and press ACCEPT.</div>" +
            "<div style='height: 40px;'><div style='display: inline-block; border: 1px solid white; font-family: serif; padding: 3px;'>" + c + "</div>" +
            "<form onsubmit=\"if ($('#captcha').val() == '" + c + "') { localStorage.rulesAccepted = 1; $('#rules').remove(); } return false;\"" +
            " style='display: inline-block;'>" +
            "<input type='text' id='captcha' style='width: 100px;' />" +
            "<input type='submit' value='ACCEPT' />" +
            "</form>" +
            "</div>" +
            "</div>" +

            "");

        $("#rules-actual").load("/templates/rules.html");

    }("faggotry1234");
});
