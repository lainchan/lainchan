function styleCode() {
    $('pre').each(function() {
        if (!$(this).hasClass('prettyprint')) {
            $(this).addClass('prettyprint');
        }
    });

    prettyPrint();
}

$(function() {styleCode();});

