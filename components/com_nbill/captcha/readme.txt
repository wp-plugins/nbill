Han-Kwang Nienhuys' PHP captcha
Copyright June 2007
Home page: http://www.lagom.nl/linux/hkcaptcha/

This file may be distributed, modified, and used freely as long as
this copyright message is preserved.

Yet another captcha implementation in PHP.  This one is written with
the current state of captcha-defeating research in mind. Apart from a
letter distortion that is more advanced than just rotating the
letters, the clutter is designed to make segmentation of the image
into separate letter glyphs hard to do automatically.

REQUIREMENTS:

PHP GD and truetype library (are normally installed on
webservers. Otherwise look for the package php-gd)

HOW TO USE:

The PHP script 'captcha-image.php' will generate a PNG with a
distorted five-letter code. The code is stored into the PHP session
variable $_SESSION['captcha_string']; see the examples example.html
and verify.php.

You may want to change the settings at the beginning of
'captcha-image.php'.

Included with this package is the Bitstream Vera Serif font, which can
be freely distributed according to the copyright described on
http://www.gnome.org/fonts/#Final_Bitstream_Vera_Fonts .
You can replace it by an other Truetype font to your liking.

CHANGELOG

20060611 - first public version
20061228 - with distorted circles instead of lines. Script not
  published.
20070620 - new distortion algorith, smoother character shapes.