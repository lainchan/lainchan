$(function(){
  var enabled_file = true;
  var enabled_url = $("#upload_url").length > 0;
  var enabled_embed = $("#upload_embed").length > 0;
  var enabled_oekaki = $("#oekaki").length > 0;

  var disable_all = function() {
    $("#upload").hide();
    $("#upload_file").hide();
    $("#upload_url").hide();
    $("#upload_embed").hide();
    $("#oekaki").hide();

    if (enabled_oekaki) {
      if ($("#confirm_oekaki").is(':checked')) {
        $("#confirm_oekaki").click();
      }
    }
  };

  enable_file = function() {
    disable_all();
    $("#upload").show();
    $("#upload_file").show();
  };

  enable_url = function() {
    disable_all();
    $("#upload").show();
    $("#upload_url").show();

    $('label[for="file_url"]').html(_("URL"));
  };

  enable_embed = function() {
    disable_all();
    $("#upload_embed").show();
  };

  enable_oekaki = function() {
    disable_all();
    $("#oekaki").show();

    if (!$("#confirm_oekaki").is(':checked')) {
      $("#confirm_oekaki").click();
    }
  };

  if (enabled_url || enabled_embed || enabled_oekaki) {
    $("<tr><th>"+_("Select")+"</th><td id='upload_selection'></td></tr>").insertBefore("#upload");
    var my_html = "<a href='javascript:void(0)' onclick='enable_file(); return false;'>"+_("File")+"</a>";
    if (enabled_url) {
      my_html += " / <a href='javascript:void(0)' onclick='enable_url(); return false;'>"+_("Remote")+"</a>";
    }
    if (enabled_embed) {
      my_html += " / <a href='javascript:void(0)' onclick='enable_embed(); return false;'>"+_("Embed")+"</a>";
    }
    if (enabled_oekaki) {
      my_html += " / <a href='javascript:void(0)' onclick='enable_oekaki(); return false;'>"+_("Oekaki")+"</a>";

      $("#confirm_oekaki_label").hide();
    }
    $("#upload_selection").html(my_html);

    enable_file();
  }
});
