$(document).ready(function(){
 $("#pasek_atencji").click(function(eO){ $("#pasek_atencji").css("display","none");
	 $("#pasek_atencji_forma").css("display","block"); });
 $.get("/atencja.php", function(data) {
  $("#pasek_atencji").text(data);
  $("#pasek_atencji_input").val(data);
 });
});
