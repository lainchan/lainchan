$(document).ready(function(){
 $("#attention_bar").click(function(eO){ $("#attention_bar").css("display","none");
	 $("#attention_bar_form").css("display","block"); });
 $.get(configRoot + "attentionbar.txt", function(data) {
  $("#attention_bar").html(data);
  $("#attention_bar_input").val($("#attention_bar").text());
 });
});
