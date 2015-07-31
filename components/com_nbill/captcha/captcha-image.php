<?php
//Ensure this file has been reached throug ha valid entry point
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

/***************************************

 Han-Kwang Nienhuys' PHP captcha
 Copyright June 2007
 Home page: http://www.lagom.nl/linux/hkcaptcha/

 This file may be distributed, modified, and used freely as long as
 the above attribution and this copyright message is preserved.

 Yet another captcha implementation in PHP.  This one is written with
 the current state of captcha-defeating research in mind. Apart from a
 letter distortion that is more advanced than just rotating the
 letters, the clutter is designed to make segmentation of the image
 into separate letter glyphs hard to do automatically.

 The 5-letter code is stored into the PHP session variable
 $_SESSION['captcha_string']; see the examples example.html and verify.php.

***************************************/

// CONFIG
global $font;
global $signature;
global $perturbation;
global $imgwid;
global $imghgt;
global $numcirc;
global $numlines;

$font = "include/VeraSeBd.ttf";
$domain = str_replace("http://", "", nbf_cms::$interop->live_site);
$domain = str_replace("https://", "", $domain);
$domain = str_replace("www.", "", $domain);
$signature = $domain;
$perturbation = 0.5; // bigger numbers give more distortion; 1 is standard
$imgwid = 150; // image width, pixels
$imghgt = 50; // image height, pixels
$numcirc = 0; // number of wobbly circles
$numlines = 2; // number of lines
// END CONFIG

// global vars
$ncols = 20; // foreground or background cols
// end global varsfunction frand()
{
  return 0.0001*rand(0,9999);
}

// wiggly random line centered at specified coordinates
function randomline($img, $col, $x, $y)
{
  $theta = (frand()-0.5)*M_PI*0.7;
  global $imgwid;
  $len = rand($imgwid*0.4,$imgwid*0.7);
  $lwid = rand(0,1);

  $k = frand()*0.6+0.2; $k = $k*$k*0.5;
  $phi = frand()*6.28;
  $step = 0.5;
  $dx = $step*cos($theta);
  $dy = $step*sin($theta);
  $n = $len/$step;
  $amp = 1.5*frand()/($k+5.0/$len);
  $x0 = $x - 0.5*$len*cos($theta);
  $y0 = $y - 0.5*$len*sin($theta);

  $ldx = round(-$dy*$lwid);
  $ldy = round($dx*$lwid);
  for ($i = 0; $i < $n; ++$i) {
    $x = $x0+$i*$dx + $amp*$dy*sin($k*$i*$step+$phi);
    $y = $y0+$i*$dy - $amp*$dx*sin($k*$i*$step+$phi);
    imagefilledrectangle($img, $x, $y, $x+$lwid, $y+$lwid, $col);
  }
}

// amp = amplitude (<1), num=numwobb (<1)
function imagewobblecircle($img, $xc, $yc, $r, $wid, $amp, $num, $col)
{
  $dphi = 1;
  if ($r > 0)
    $dphi = 1/(6.28*$r);
  $woffs = rand(0,100)*0.06283;
  for ($phi = 0; $phi < 6.3; $phi += $dphi) {
    $r1 = $r * (1-$amp*(0.5+0.5*sin($phi*$num+$woffs)));
    $x = $xc + $r1*cos($phi);
    $y = $yc + $r1*sin($phi);
    imagefilledrectangle($img, $x, $y, $x+$wid, $y+$wid, $col);
  }
}

// make a distorted copy from $tmpimg to $img. $wid,$height apply to $img,
// $tmpimg is a factor $iscale bigger.
function distorted_copy($tmpimg, $img, $width, $height, $iscale)
{
  $numpoles = 3;

  // make an array of poles AKA attractor points
  global $perturbation;
  for ($i = 0; $i < $numpoles; ++$i) {
    do {
      $px[$i] = rand(0, $width);
    } while ($px[$i] >= $width*0.3 && $px[$i] <= $width*0.7);
    do {
      $py[$i] = rand(0, $height);
    } while ($py[$i] >= $height*0.3 && $py[$i] <= $height*0.7);
    $rad[$i] = rand($width*0.4, $width*0.8);
    $tmp = -frand()*0.15-0.15;
    $amp[$i] = $perturbation * $tmp;
  }

  // get img properties bgcolor
  $bgcol = imagecolorat($tmpimg, 1, 1);
  $width2 = $iscale*$width;
  $height2 = $iscale*$height;

  // loop over $img pixels, take pixels from $tmpimg with distortion field
  for ($ix = 0; $ix < $width; ++$ix)
    for ($iy = 0; $iy < $height; ++$iy) {
      $x = $ix;
      $y = $iy;
      for ($i = 0; $i < $numpoles; ++$i) {
	$dx = $ix - $px[$i];
	$dy = $iy - $py[$i];
	if ($dx == 0 && $dy == 0)
	  continue;
	$r = sqrt($dx*$dx + $dy*$dy);
	if ($r > $rad[$i])
	  continue;
	$rscale = $amp[$i] * sin(3.14*$r/$rad[$i]);
	$x += $dx*$rscale;
	$y += $dy*$rscale;
      }
      $c = $bgcol;
      $x *= $iscale;
      $y *= $iscale;
      if ($x >= 0 && $x < $width2 && $y >= 0 && $y < $height2)
	$c = imagecolorat($tmpimg, $x, $y);
      imagesetpixel($img, $ix, $iy, $c);
    }
}

// add grid for debugging purposes
function addgrid($tmpimg, $width2, $height2, $iscale, $color) {
  $lwid = floor($iscale*3/2);
  imagesetthickness($tmpimg, $lwid);
  for ($x = 4; $x < $width2-$lwid; $x+=$lwid*2)
    imageline($tmpimg, $x, 0, $x, $height2-1, $color);
  for ($y = 4; $y < $height2-$lwid; $y+=$lwid*2)
    imageline($tmpimg, 0, $y, $width2-1, $y, $color);
}function warped_text_image($width, $height, $string)
{
  // internal variablesinternal scale factor for antialias
  $iscale = 3;

  // initialize temporary image
  $width2 = $iscale*$width;
  $height2 = $iscale*$height;
  $tmpimg = imagecreate($width2, $height2);
  $bgColor = imagecolorallocatealpha ($tmpimg, 192, 192, 192, 100);
  $col = imagecolorallocate($tmpimg, 0, 0, 0);

  // init final image
  $img = imagecreate($width, $height);
  imagepalettecopy($img, $tmpimg);
  imagecopy($img, $tmpimg, 0,0 ,0,0, $width, $height);

  // put straight text into $tmpimage
  global $font;
  $fsize = $height2*0.35;
  $bb = imageftbbox($fsize, 0, nbf_cms::$interop->nbill_fe_base_path . "/captcha/$font", $string, array());
  $tx = $bb[4]-$bb[0];
  $ty = $bb[5]-$bb[1];
  $x = floor($width2/2 - $tx/2 - $bb[0]);
  $y = round($height2/2 - $ty/2 - $bb[1]);
  imagettftext($tmpimg, $fsize, 0, $x, $y - 20, -$col, nbf_cms::$interop->nbill_fe_base_path . "/captcha/$font", $string);

  // addgrid($tmpimg, $width2, $height2, $iscale, $col); // debug

  // warp text from $tmpimg into $img
  distorted_copy($tmpimg, $img, $width, $height, $iscale);

  // add wobbly circles (spaced)
  global $numcirc;
  for ($i = 0; $i < $numcirc; ++$i) {
    $x = $width * (1+$i) / ($numcirc+1);
    $x += (0.5-frand())*$width/$numcirc;
    $y = rand($height*0.1, $height*0.9);
    $r = frand();
    $r = ($r*$r+0.2)*$height*0.2;
    $lwid = rand(0,1);
    $wobnum = rand(1,4);
    $wobamp = frand()*$height*0.01/($wobnum+1);
    imagewobblecircle($img, $x, $y, $r, $lwid, $wobamp, $wobnum, $col);
  }

  // add wiggly lines
  global $numlines;
  for ($i = 0; $i < $numlines; ++$i) {
    $x = $width * (1+$i) / ($numlines+1);
    $x += (0.5-frand())*$width/$numlines;
    $y = rand($height*0.1, $height*0.9);
    randomline($img, $col, $x, $y);
  }

  return $img;
}

function add_text($img, $string)
{
  $cmtcol = imagecolorallocatealpha ($img, 128, 0, 0, 64);
  imagestring($img, 5, 5, imagesy($img)-20, $string, $cmtcol);
}

ob_start(); //For some reason, calling session_start prevents further headers from being sent, although I cannot detect any output. Weird. Anyway, ob_start here and ob_end_clean at the end fixes this.
@ini_set('session.save_handler', 'files');
@session_start();

// generate  5 letter random string
$rand = "";
// some easy-to-confuse letters taken out C/G I/l Q/O h/b
$letters = "ABDEFHKLMNPRSTUVWXZabdefghikmnpqrstuvwxyz";
for ($i = 0; $i < 5; ++$i) {
  $rand .= substr($letters, rand(0,nbf_common::nb_strlen($letters)-1), 1);
}

//Load the cron token and use that as a hash key (to prevent tampering)
$nb_database = nbf_cms::$interop->database;
$sql = "SELECT cron_auth_token FROM #__nbill_configuration WHERE id = 1";
$nb_database->setQuery($sql);
$token = $nb_database->loadResult();

// create the hash for the random number
$hash = md5($token . nbf_common::nb_strtoupper($rand));

$_SESSION['captcha_string'] = $hash;

// start main program
$image = warped_text_image($imgwid, $imghgt, $rand);
add_text($image, $signature);

// send several headers to make sure the image is not cached
// taken directly from the PHP Manual

// Date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-type: image/png');

ob_end_clean();

// send the image to the browser
imagepng($image);

// destroy the image to free up the memory
imagedestroy($image);