$(document).ready(function(){
  $(".desktop-style .sub .sub").on("mouseover", function() {
    $(this).addClass("hover");
  }).on("mouseout", function() {
    $(this).removeClass("hover");
  });
});
