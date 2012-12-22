//function obecnyCzas() {
// return Math.round(new Date().getTime() / 1000);
//}

function imgcaptcha_odswiezKapcze() {
 $.get("/imgcaptcha_p.php", function(data) {
  $("#imgcaptcha_hash").val(data);
  $("#imgcaptcha_img").prop("src","/imgcaptcha_im.php?cr=" + data);
 });
}
//function resetujKapcze() {
// $("#imgcaptcha_img").prop("src","/zakrytek.png");
//}
//$(document).ready(function(){
// //resetujKapcze(); - to nie powinno byc na razie potrzebne
//});
