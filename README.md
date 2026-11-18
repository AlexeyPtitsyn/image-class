# Image class

A class for resizing GIF, transparent PNG and JPG images. Using GD.

The class `/inc/image.php` allows you to resize image to a thumbnail and add watermark to it.

Generally, the usage of this class is following:
```php
<?php
require_once __DIR__ . '/inc/image.php';

$path = 'originals/test.jpg'; // A path from root folder to file.
$rootPath = __DIR__; // Root path. Path, where thumbnail directory lies in.
$thumbnailsDir = 'thumbnails'; // Thumbnails directory;
$watermark = __DIR__ . '/images/watermark.png'; // Watermark image with full path.

$img = new Image($path, $rootPath, $thumbnailsDir, $watermark);
?>

<!-- Image class will generate an relative src to 200x150 thumbnail -->
<img src="<?=$img->resize(200,150)->getSrc()?>" alt="...">

<!-- The shorter way -->
<img src="<?=$img->thumbnail(200,150)?>" alt="...">
```

Of course, you can create a separate function to set thumbnails directory and other parameters:
```php
function img($path) {
  $img = new Image($path, __DIR__);
  $img->watermark = __DIR__ . '/images/watermark.png';

  return $img->resize(200)->getSrc();
}

<img src="<?=img('path/to/my-image.jpg')?>" alt="...">
```

In the `get-image` directory lies example of receiving resized images through URL and 302 redirect. E.g. for receiving resized images via AJAX or direct link.

## Constructor

```php
$img = new Image($path, $rootPath, $thumbnailsDirectory, $watermark);
```

- `$path` - Relative path to original image. E.g. path to original image from webpage that user is browsing. With image name and extension. Without leading `/`.
- `$rootPath` - Absolute path to directory, where locates folders of original images and thumbnails. Without ending `/`.
- `$thumbnailsDirectory` - Relative path from `$rootPath` to directory for thumbnails. Optional. Default `thumbnails`.
- `$watermark` - Absolute path to watermark. Watermark should be a semi-transparent PNG image. Optional.

While adding, watermark will be resized proportionally by image resize.

## Methods

### Get original image `->original()`

Returns original image object **with original relative path**.

### Resize image `->resize($width, $height)`

Returns resized image object. Both parameters is optional.

If one of the parameters is missing, the image will be resized by aspect ratio. According to its known value.

If both parameters are missing - thumbnail will be created with the original image size.

*Remember to use both parameters as much as you can. If at least one parameter is missing, the image size is undefined. So image class have to open original file to get its width/height to calculate aspect ratio.*

### Get image src `->getSrc($useTimestamp)`

Returns image src string. Boolean parameter `$useTimestamp` is optional. Default `true`. This parameter will add original file last modification time to QSA after image name. This is useful to reset browser cache if the new image thumbnail for the same image is generated.

### Thumbnail `->thumbnail($width, $height, $useTimestamp)`

A shortcut for `$image->resize($w,$h)->getSrc(true)`.
