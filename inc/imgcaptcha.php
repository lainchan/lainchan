<?php
 // Wiem, ze ten kod to czysta ohyda. Coz.
 require_once("inc/functions.php");
 require_once("inc/ic-encrypt.php");

 global $config;

 function getImages() {
  global $config;
  $lines = explode("\n",file_get_contents($config["imgcaptcha_list"]));
  for($i=0;$i<count($lines);$i++) { $lines[$i] = explode(",",$lines[$i]);  }
  return $lines;
 }
 function getIPath($img) {
  global $config;
  return $config["imgcaptcha_images"] . "/" . $img;
 }
 function pickImage($lines) {
  $src = FALSE;
  while($src == FALSE) {
   $pick = rand(0,count($lines)-1);
   if($lines[$pick][0] != "") $src = imagecreatefrompng(getIPath($lines[$pick][0]));
  }
  imagedestroy($src);
  return $pick;
 }
 function ncfix($a) {
  if($a>255) { return 255; }
  if($a<0) { return 0; }
  return $a;
 }
 function randString($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+=')
 {
  $str = '';
  $count = strlen($charset);
  while ($length--) {
   $str .= $charset[rand(0, $count-1)];
  }
  return $str;
 }
 function generateCaptchaHash() {
  global $config;
  $lines = getImages();
  $pick = pickImage($lines);
  $enctext = $pick . ",," . time() . ",," . $_SERVER["REMOTE_ADDR"] . ",," . randString(12);
  $converter = new Encryption;
  return $converter->encode($config["imgcaptcha_key"],$enctext);
 }
 function ic_verifyHash($enctext, $output) {
  global $config;
  //print "VERIFY: " . $enctext . " " . $output . "<br>";
  $converter = new Encryption;
  $dectext = explode(",,",$converter->decode($config["imgcaptcha_key"],$enctext));
  if(count($dectext)<4) return true;
  $lines = getImages();
  $pick = $dectext[0];
  $time = time()-$dectext[1];
  if($time>$config["imgcaptcha_time_limit"]) return true;
  $lp = $lines[$pick];
  for($i=1;$i<count($lp);$i++) {
   if(strcasecmp($lp[$i],$output)==0) return false;
  }
  return true;
 }
 function getPick($enctext)
 {
  global $config;
  $converter = new Encryption;
  $dectext = explode(",,",$converter->decode($config["imgcaptcha_key"],$enctext));
  if(count($dectext)<=1) return; //SC
  $lines = getImages();
  return $dectext[0];
 }
 function generateImage($enctext)
 {
  global $config;
  $lines = getImages();
  $pick = getPick($enctext);
  if(!isset($lines[$pick])) return; //SC
  $src = imagecreatefrompng(getIPath($lines[$pick][0]));
  if($src == FALSE) return; //SC
  $maxc = 8;
  $icw = $config["imgcaptcha_width"];
  $ich = $config["imgcaptcha_height"];
  $dst = imagecreatetruecolor($icw,$ich);
  $srcxm = imagesx($src)-$icw;
  $srcym = imagesy($src)-$ich;
  $srcx = rand(0,$srcxm-1);
  $srcy = rand(0,$srcym-1);
  imagecopy($dst,$src,0,0,$srcx,$srcy,$icw,$ich);

  // Obfuscation step 1
  imagecopymergegray($dst,$dst,0,0,0,0,$icw,$ich,rand(20,45));
  // Obfuscation step 1.5
  for($i=0;$i<8;$i++) {
   $w = rand(5,10); $h = rand(5,10);
   $x = rand(0,$icw-1-$w); $y = rand(0,$ich-1-$h);
   $x2 = rand(0,$icw-1); $y2 = rand(0,$ich-1);
   imagefilledrectangle($dst,$x,$y,$x+$w,$y+$h,imagecolorat($dst,$x2,$y2));
  }
  for($i=0;$i<5;$i++) {
   $w = rand(20,40); $h = rand(20,40);
   $x = rand(0,$icw-1-$w); $y = rand(0,$ich-1-$h);
   imagecopymergegray($dst,$dst,$x,$y,$x,$y,$w,$h,0);
  }
  // Obfuscation step 2
  for($i=0;$i<$icw*$ich;$i++) {
   $x = $i%$icw; $y = $i/$icw;
   $c = imagecolorat($dst,$x,$y);
   if(rand(0,4) == 2) { $nc = $c ^ rand(0,16777215); }
   else { $nc = imagecolorat($dst,rand(0,$icw-1),rand(0,$ich-1)); }
   if(rand(18,24)!=21 and $c != 0 and $c != 0xFF00FF)
   {
    $nc = ncfix(($c&0xFF) + rand(-16,16)) | ncfix((($c>>8)&0xFF) + rand(-8,8))<<8 | ncfix((($c>>16)&0xFF) + rand(-32,32))<<16;
    $nc1 = $nc&0xFF ^ ($nc>>8)&0xFF ^ ($nc>>16)&0xFF;
   } else {
    $nc1 = $nc&0xFF;
    if($nc1>($maxc*25)) $nc1 = $nc % ($maxc*25);
   }
   $nc2 = $nc1 | $nc1<<8 | $nc1<<16;
   if(rand(0,1)==0) $nc2=$nc;
   imagesetpixel($dst,$x,$y,$nc2);
  }
  // Obfuscation step 3
  for($i=0;$i<rand(10,30);$i++) {
   $x1 = rand(0,$icw-1); $x2 = rand(0,$icw-1); $y1 = rand(0,$ich-1); $y2 = rand(0,$ich-1);
   $color = imagecolorallocate($dst, rand(0,$maxc)*25, rand(0,$maxc)*25, rand(0,$maxc)*25);
   imageline($dst,$x1,$y1,$x2,$y2,$color);
  }

  imagepng($dst);
 }
 //header('Content-Type: image/png');
 //$t = generateCaptchaHash();
 //generateImage($t);
?>
