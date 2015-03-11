//Setting global variables
var favorites = JSON.parse(localStorage.favorites);
Options.add_tab('fav-tab','star',_("Favorites"));

//Creating functions
var generateList = function(){
	var favStor = [];
  	for(var i=1; i<favorites.length+1; i++){
  		favStor.push($("#sortable > div:nth-child("+i+")").html());
  	}
	return JSON.stringify(favStor);
} //This will generate a list of boards based off of the list on the screen
function removeBoard(boardNumber){
	favorites.splice(boardNumber, 1);
	localStorage.favorites = JSON.stringify(favorites);
	$("#sortable > div:nth-child("+(boardNumber+1)+")").remove();
	$("#minusList > div:nth-child("+(favorites.length+1)+")").remove();
} //This removes a board from favorites, localStorage.favorites and the page
function addBoard(){
	$("#sortable").append("<div>"+($("#plusBox").val())+"</div>");
	$("#minusList").append("<div onclick=\"removeBoard("+favorites.length+")\" style=\"cursor: pointer; margin-left: 5px\">-</div>");
	favorites.push($("#plusBox").val());
	localStorage.favorites = JSON.stringify(favorites);
	$("#space").remove();
	$("#plusBox").remove(); //Refreshing the last 3 elements to move the box down
	$("#plus").remove();
	$("#submitFavorites").remove();
	$("<br id=\"space\"></br>").appendTo(Options.get_tab('fav-tab').content);
	$("<input id=\"plusBox\" type=\"text\">").appendTo(Options.get_tab('fav-tab').content);
	$("#plusBox").keydown(function( event ) {
 		if(event.keyCode == 13){
 			$("#plus").click();
 		}
	}); //Adding enter to submit
	document.getElementById("plusBox").value = ""; //Removing text from textbox
	$("#plusBox").focus(); //Moving cursor into text box again after refresh
	$("<div id=\"plus\" onclick=\"addBoard()\">+</div>").css({
		cursor: "pointer",
		color: "#0000FF"
	}).appendTo(Options.get_tab('fav-tab').content); //Adding the plus to the tab
	$("<input id=\"submitFavorites\" onclick=\"localStorage.favorites=generateList();document.location.reload();\" type=\"button\" value=\""+_("Refresh")+"\">").css({
		height: 25, bottom: 5,
		width: "calc(100% - 10px)",
		left: 5, right: 5
	}).appendTo(Options.get_tab('fav-tab').content); //Adding button to the tab
} //This adds the text inside the textbox to favorites, localStorage.favorites and the page

//Making as many functions and variables non-global
$(document).ready(function(){

//Pregenerating list of boards 
var favList = ['<div id="sortable" style="cursor: pointer; float: left;display: inline-block">'];
for(var i=0; i<favorites.length; i++){
    favList += '<div>'+favorites[i]+'</div>';
} 
favList += '</div>';

//Creating list of minus symbols to remove unwanted boards
var minusList = ['<div id="minusList" style="color: #0000FF;display: inline-block">'];
for(var i=0; i<favorites.length; i++){
    minusList += '<div onclick="removeBoard('+i+')" style="cursor: pointer; margin-left: 5px">-</div>';
} 
minusList += "</div>"; 

//Help message so people understand how sorting boards works
$("<span>Drag the boards to sort them.</span><br></br>").appendTo(Options.get_tab('fav-tab').content);

//Adding list of boards and minus symbols to remove boards with
$(favList).appendTo(Options.get_tab('fav-tab').content);  //Adding the list of favorite boards to the tab
$(minusList).appendTo(Options.get_tab('fav-tab').content); //Adding the list of minus symbols to the tab

//Adding spacing and text box to right boards into
$("<br id=\"space\"></br>").appendTo(Options.get_tab('fav-tab').content);
$("<input id=\"plusBox\" type=\"text\">").appendTo(Options.get_tab('fav-tab').content);
$("#plusBox").keydown(function( event ) {
	if(event.keyCode == 13){
		$("#plus").click();
	}
});

//Adding plus symbol to use to add board
$("<div id=\"plus\" onclick=\"addBoard()\">+</div>").css({
	cursor: "pointer",
	color: "#0000FF"
}).appendTo(Options.get_tab('fav-tab').content); //Adding the plus button
$("<input id=\"submitFavorites\" onclick=\"localStorage.favorites=generateList();document.location.reload();\" type=\"button\" value=\""+_("Submit")+"\">").css({
	height: 25, bottom: 5,
	width: "calc(100% - 10px)",
	left: 5, right: 5
}).appendTo(Options.get_tab('fav-tab').content); //Adding submit button to the tab

$("#sortable").sortable(); //Making boards with sortable id use the sortable jquery function

});
