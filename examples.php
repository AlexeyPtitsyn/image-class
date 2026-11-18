<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title></title>
</head>
<body style="background: #eee;">

<?php
// Debug mode
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php
// Include image class
require_once __DIR__ . "/inc/image.php";

// Just for convenience
function img($path) {
  $img = new Image($path, __DIR__);

  $img->watermark = __DIR__ . '/originals/watermark.png';

  return $img;
}
?>

<img src="<?=img('originals/png-image.png')->thumbnail(100, 100)?>">
<img src="<?=img('originals/png-image.png')->thumbnail(null, 300)?>">
<img src="<?=img('originals/png-image.png')->thumbnail()?>">

<!-- Test of resizing image by url -->
<img src="get-image/originals/png-image.png?h=200">

</body>
</html>
