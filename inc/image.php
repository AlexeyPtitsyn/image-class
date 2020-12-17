<?php
/**
 * Image class.
 *
 * @author Alexey Ptitsyn <numidium.ru@gmail.com>
 * @copyright Alexey Ptitsyn <numidium.ru@gmail.com>
 *
 * Example usage:
 * <?php
 *   $image = new Image('originals/image.jpg', __DIR__);
 * ?>
 * <img src="<?=$image->resize(100)->getSrc()?>"
 *   alt="Proportial miniature with 100px wide">
 *
 * <img src="<?=$image->resize(null, 200)->getSrc()?>"
 *   alt="Proportial miniature with 200px high">
 *
 * <img src="<?=$image->resize(100, 50)->getSrc()?>"
 *   alt="Miniature with custom width and height">
 *
 * <img src="<?=$image->original()->getSrc()?>"
 *   alt="Image original in its dir">
 *
 * Watermark example:
 * <?php
 *   $img = new Image('original/image.jpg', __DIR__, 'original/watermark.png');
 * ?>
 * <img src="<?=$img?>" alt="Image with watermark">
 */

class Image
{
  protected $original; // Full path and filename to original image.

  protected $rootPath; // Root path, where root path of browser should be.
  protected $imagePath; // Path to image and its filename after root path.
  protected $originalImagePath; // Same as above, but for original image.

  protected $filename;
  protected $extension;

  protected $src; // Internal src name.

  protected $thumbnailsDir; // Thumbnails directory or path.

  public $watermark; // Full path to watermark image

  /**
   * @constructor
   *
   * @param {string} $imagePath - Image path from some root path.
   *                              Without leading slash.
   * @param {string} $rootPath - Image root path. Without
   *                             trailing slash. Default __DIR__ - this class
   *                             directory.
   * @param {string} $thumbnailsDir - Image thumbnails path. Without leading
   *                                  and trailing slash. Default `thumbnails`.
   * @param {string} $watermark - Full path to watermark image. Image
   *                              should be a PNG image.
   *
   * @return {object}
   */
  function __construct($imagePath, $rootPath = __DIR__, $thumbnailsDir = 'thumbnails', $watermark = null) {
    $this->rootPath = $rootPath;
    $this->imagePath = $imagePath;
    $this->originalImagePath = $imagePath;

    $this->original = $this->rootPath . '/' .$this->imagePath;

    $this->filename = pathinfo($this->original, PATHINFO_FILENAME);
    $this->extension = pathinfo($this->original, PATHINFO_EXTENSION);

    $this->src = $this->imagePath;

    $this->thumbnailsDir = $thumbnailsDir;

    $this->watermark = $watermark;
  } // constructor();

  /**
   * Return object with original src.
   *
   * @return {object}
   */
  public function original() {
    $this->src = $this->originalImagePath;
    return $this;
  } // original();

  /**
   * Returns constructed file name.
   *
   * @param {int} $w - thumbnail image width.
   * @param {int} $h - thumbnail image height.
   *
   * @return {string}
   */
  protected function constructFileName($w, $h) {
    $constructedFileName = $this->thumbnailsDir . '/' .
      $this->filename . "-{$w}x{$h}." . $this->extension;

    if($this->watermark) {
      $constructedFileName = $this->thumbnailsDir . '/' .
        $this->filename . "-{$w}x{$h}-wm." . $this->extension;
    }

    return $constructedFileName;
  } // constructFileName();

  /**
   * Checks wheter thumbnail exists and is latest.
   *
   * @param {string} $fileName - relative path to thumbnail.
   *
   * @return {boolean}
   */
  protected function isFileFresh($fileName) {
    return file_exists("{$this->rootPath}/{$fileName}") &&
        filemtime("{$this->rootPath}/{$fileName}") >=
        filemtime($this->original);
  } // isFileFresh();

  /**
   * A shortcut for getting resized thumbnail. Returns image src.
   * 
   * @param {int} $w - Image width. Optional.
   * @param {int} $h - Image height. Optional.
   * @param {boolean} $timestamp - Add timestamp at the end
   *                               of the URI. Optional. Default true.
   *
   * @return {string}
   */
  public function thumbnail($w = null, $h = null, $timestamp = true) {
    return $this->resize($w, $h)->getSrc($timestamp);
  }

  /**
   * Resize image with a given width or height. If both parameters is optional,
   * original image will be given. Returns $this.
   *
   * @param {integer} $w - New image width. Optional.
   * @param {integer} $h - New image height. Optional.
   *
   * @return {object}
   */
  public function resize($w = null, $h = null) {
    if($w !== null && $h !== null) {
      $constructedFileName = $this->constructFileName($w, $h);

      if($this->isFileFresh($constructedFileName)) {
        $this->src = $constructedFileName;

        return $this;
      }
    }

    $imageInfo = getimagesize($this->original);
    $originalWidth = $imageInfo[0];
    $originalHeight = $imageInfo[1];
    $originalType = $imageInfo['mime'];

    $ratio = $originalWidth / $originalHeight;
    if($w == null) {
      $w = intval($h * $ratio);
    }

    if($h == null) {
      $h = intval($w / $ratio);
    }

    if($w == null && $h == null) {
      $w = $originalWidth;
      $h = $originalHeight;
    }

    $constructedFileName = $this->constructFileName($w, $h);

    if($this->isFileFresh($constructedFileName)) {
      $this->src = $constructedFileName;
      return $this;
    }

    $dst = imagecreatetruecolor($w, $h);
    switch ($originalType) {
      case 'image/gif':
        $src = imagecreatefromgif($this->original);
        break;
      
      case 'image/jpeg':
        $src = imagecreatefromjpeg($this->original);
        break;

      case 'image/png':
        $src = imagecreatefrompng($this->original);

        // Save transparency:
        imagealphablending($dst, false);
        imagesavealpha($dst,true);
        $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
        imagefilledrectangle($dst, 0, 0, $w, $h, $transparent);
        break;

      default:
        return $this;
        break;
    }

    imagecopyresampled(
      $dst, $src, 0, 0, 0, 0,
      $w, $h,
      $originalWidth, $originalHeight
    );

    // Add watermark if it is set
    if($this->watermark) {
      $wm = imagecreatefrompng($this->watermark);

      $percentW = ($w * 100 / $originalWidth)/100;
      $percentH = ($h * 100 / $originalHeight)/100;

      $wmW = imagesx($wm);
      $wmH = imagesy($wm);

      $wmX = $w - $wmW*$percentW;
      $wmY = $h - $wmH*$percentH;

      imagecopyresampled(
        $dst, $wm,
        $wmX, $wmY,
        0, 0,
        intval($wmW*$percentW), intval($wmH*$percentH),
        $wmW, $wmH
      );
    }

    $newFileName = "{$this->rootPath}/{$constructedFileName}";

    $jpgQuality = 71;
    $pngQuality = 4;

    switch ($originalType) {
      case 'image/gif':
        $result = imagegif($dst, $newFileName);
        break;
      
      case 'image/jpeg':
        $result = imagejpeg($dst, $newFileName, $jpgQuality);
        break;

      case 'image/png':
        $result = imagepng($dst, $newFileName, $pngQuality);
        break;

      default:
        return $this;
        break;
    }

    imagedestroy($dst);

    $this->src = $constructedFileName;

    return $this;
  } // resize();

  /**
   * Get image src path (for browser).
   *
   * @param {boolean} $useTimestamp - Set image last modification time
   *                                  at the end of URI string.
   *                                  Default `true`.
   *
   * @return {string}
   */
  public function getSrc($useTimestamp = true) {
    return $this->src . '?t=' . filemtime($this->rootPath . '/' . $this->imagePath);
  } // getSrc();
} // class Image
