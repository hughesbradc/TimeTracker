<?php
//Set the content-type
header('Content-Type: image/png');

//Where's the font file?
putenv('GDFONTPATH=/usr/share/fonts/paratype-pt-sans');

//Create the image
$image = imagecreatetruecolor(500,25);

//Create colors
$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);
$gray = imagecolorallocate($image, 128, 128, 128);
imagefilledrectangle($image, 0, 0, 499, 24, $white);

//The text to draw - will be the inputted text from the text box
$text = 'The name from the text box will generate to a PNG here.';
//Fonts
$font = 'PTS76F.ttf';

//Add someshadow to the text
//imagettftext($image, 20, 0, 11, 21, $gray, $font, $text);

//Add the text
imagettftext($image, 12, 0, 10, 20, $black, $font, $text);

//Using imagepng() results in clearer text compared with imagejpeg()
imagepng($image);
imagedestroy($image);
?>
