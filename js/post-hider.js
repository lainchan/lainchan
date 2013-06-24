function phGetCookieName(board, id) {
  return "ph_hide_" + board + "_" + id;
}
function phPostHidden(board, id) {
  return (localStorage.getItem(phGetCookieName(board, id)) != null);
}
function phPostToggle(board, id) {
 if(phPostHidden(board, id)) { localStorage.removeItem(phGetCookieName(board, id)); }
 else { localStorage.setItem(phGetCookieName(board, id),"yes"); }
}
function phGetInnerText(board, id) {
 if(phPostHidden(board, id)) { return "[+]"; }
 else { return "[–]"; }
}
function phGetOpID(element) {
 return Number(element.children("div.post.op").children("p.intro").children("a.post_no:eq(1)").text());
}
function phGetOpBoard(element) {
 return element.data("board");
}
function phPostHandle(element) {
 var id = phGetOpID(element);
 var board = phGetOpBoard(element);
 var preplies = element.children("div.post.reply");
 var pbody = element.children("div.post.op").children("div.body");
 var pimage = element.children("a:first").children("img");
 var pbutton = element.children("div.post.op").children("p.intro").children("a.posthider");
 var pomitted = element.children("div.post.op").children("span.omitted");
 if(phPostHidden(board, id)) { element.addClass("thread-hidden"); pomitted.hide(); preplies.hide(); pbody.hide(); pimage.hide(); pbutton.text("[+]"); }
 else { element.removeClass("thread-hidden"); pomitted.show(); preplies.show(); pbody.show(); pimage.show(); pbutton.text("[–]"); }
}

$(document).ready(function(){
  $('form[name="postcontrols"] > div[id^="thread"]').each(function(index, element){
    // Get thread ID.
    var pin = $(this).children("div.post.op").children("p.intro");
    var tid = phGetOpID($(this));
    if(tid != NaN) {
      $("<a href='javascript:;' class='posthider'>[?]</a>").insertAfter(pin.children('a:last')).click(function(e) {
	var eO = $(e.target);
        var par = eO.parent().parent().parent();
        phPostToggle(phGetOpBoard(par), phGetOpID(par));
        phPostHandle(par);
        return false;
      });
      phPostHandle($(this));
    }
  });
});
