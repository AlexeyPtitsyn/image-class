<?php
/**
 * Image resize example using 302 redirect.
 *
 * @author Alexey Ptitsyn <numidium.ru@gmail.com>
 * @copyright Alexey Ptitsyn <numidium.ru@gmail.com>
 *
 * Example usage:
 *   1. Put this file in any directory, accessible through url.
 *   2. Create there `.htaccess` file with similar content:
 *
 *      RewriteEngine On
 *
 *      RewriteCond %{REQUEST_FILENAME} !-f
 *      RewriteCond %{REQUEST_FILENAME} !-d
 *
 *      RewriteBase /imagelib.git/get-image/
 *      RewriteRule ^(.*)$ get-image.php?image=$1 [R=302,QSA,L]
 *
 *   3. Fix the `RewriteBase` with the correct path from root url of your site.
 *   4. Set the right paths in this file.
 *   
 *   Now, while requesting your directory with filename, your image thumbnail
 *   will be returned to browser as 302 redirect.
 */

$minWidth = 100;
$minHeight = 100;

$maxWidth = 500;
$maxHeight = 500;

$imageName = null;
$imageWidth = null;
$imageHeight = null;

/** Check request parameters */
if(!isset($_REQUEST['image']) || gettype($_REQUEST['image']) !== 'string') {
  http_response_code(404);
  die();
}

$imageName = $_REQUEST['image'];

if(
  $imageName[0] == '/' ||
  strpos($imageName, '..') !== false
) {
  http_response_code(404);
  die();
}

if(isset($_REQUEST['w'])) {
  $imageWidth = (int)$_REQUEST['w'];
  if ($imageWidth < $minWidth) {
    $imageWidth = $minWidth;
  }
  if($imageWidth > $maxHeight ) {
    $imageWidth = $maxHeight;
  }
}

if(isset($_REQUEST['h'])) {
  $imageHeight = (int)$_REQUEST['h'];

  if($imageHeight < $minHeight) {
    $imageHeight = $minHeight;
  }
  if($imageHeight > $maxHeight) {
    $imageHeight = $maxHeight;
  }
}

// Resize an image via library
require_once __DIR__ . '/../inc/image.php';

$img = new Image($imageName, __DIR__ . '/..');
$src = '../../' . $img->resize($imageWidth, $imageHeight)->getSrc();

header("Location: $src", true, 302);
