function phGetCookieName(id) {
  return "ph_hide_" + id;
}
function phPostHidden(id) {
  return ($.cookie(phGetCookieName(id)) != null);
}
function phPostToggle(id) {
 if(phPostHidden(id)) { $.cookie(phGetCookieName(id),null); }
 else { $.cookie(phGetCookieName(id),"yes"); }
}
function phGetInnerText(id) {
 if(phPostHidden(id)) { return "[+]"; }
 else { return "[-]"; }
}
function phGetOpID(element) {
 return Number(element.children("div.post.op").children("p.intro").children("a.post_no:eq(1)").text());
}
function phPostHandle(element) {
 var id = phGetOpID(element);
 var preplies = element.children("div.post.reply");
 var pbody = element.children("div.post.op").children("div.body");
 var pimage = element.children("a:first").children("img");
 var pbutton = element.children("div.post.op").children("p.intro").children("a.posthider");
 if(phPostHidden(id)) { element.addClass("post-hidden"); preplies.hide(); pbody.hide(); pimage.hide(); pbutton.text("[+]"); }
 else { element.removeClass("post-hidden"); preplies.show(); pbody.show(); pimage.show(); pbutton.text("[-]"); }
}

$(document).ready(function(){
  $('div[id^="thread"]').each(function(index, element){
    // Get thread ID.
    var pin = $(this).children("div.post.op").children("p.intro");
    var tid = phGetOpID($(this));
    if(tid != NaN) {
      $("<a class='posthider'>[?]</a>").insertAfter(pin.children('a:last')).click(function(e) {
	var eO = $(e.target);
        var par = eO.parent().parent().parent();
        phPostToggle(phGetOpID(par));
        phPostHandle(par);
      });
      phPostHandle($(this));
    }
  });
});
