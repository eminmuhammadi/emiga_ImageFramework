<?php
/**
 *
 * Using ImageFramework by EmiGa you can serve images as 
 * php-file which you can set manual width,height,quality,watermark and such another things. 
 * Easily setting for any website to load fast (lazyload)
 *
 *
 * LICENSE: MIT License
 *
 * Copyright (c) 2018 Emin Mühəmmədi (EmiGa)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @package    emiga_ImageFramework
 * @author     Emin Muhammadi
 * @copyright  2018 Emin Muhammadi (EmiGa)
 * @license    https://github.com/eminmuhammadi/emiga_ImageFramework/blob/master/LICENSE  MIT License
 * @version    1.0.0
 * @link       https://github.com/eminmuhammadi/emiga_ImageFramework
 */

class emigaImageFramework  {

/**
 * RGB color
 * 
 * @example rgb(255, 255, 255)
 * @var array
 * @access private
 */
private $color = array(255, 255, 255);
/**
 * Font path
 *
 * @var string
 * @access private
 */
private $font;
/**
 * Font size
 *
 * @example 16px
 * @var int
 * @access private
 */
private $fontSize;
/**
 * Image resource identifier
 *
 * @var resource
 * @access private
 */
private $image;
/**
 * One of the IMAGETYPE_* constants indicating the type of the image.
 *
 * @example image  {{{ .jpg //.gif//.png }}}
 * @var int
 * @access private
 */
private $imageType;
/**
 * Image width
 * 
 * @example 400px
 * @var int
 * @access private
 */
private $width;
/**
 * Image height
 *
 * @example 400px
 * @var int
 * @access private
 */
private $height;


private $file;

/**
 * Constructor - automatically called when you create a new instance of a class with new
 *
 * @access public
 * @return self
 */
public function __construct()
{
  if (!extension_loaded('gd') || !function_exists('gd_info'))
  {
    $this->error = "GD extension is not loaded (or not installed)";
    $this->errorCode = 200;
  }
}//end GD extension

/**
 * Get color
 *
 * @access public
 * @return array
 */
public function getColor(){
return $this->color;
}//end getColor
/**
 * Get font
 * 
 * @access public
 * @return string
 */
public function getFont(){
return $this->font;
}//end getFont
/**
 * Get font size
 * 
 * @access public
 * @return number
 */
public function getFontSize(){
return $this->fontSize;
}//endFontSize

/**
 * Get image height
 *
 * @access public
 * @return int|false Return the height of the image or FALSE on errors.
 */
public function getHeight(){
return imagesy($this->getImage());
}//end getHeight
/**
 * Get image resource
 *
 * @access public
 * @return resource
 */
public function getImage(){
return $this->image;
}//end getImage
/**
 * Get the size of an image
 *
 * @access public
 * @return Returns an array with 7 elements.
 */
public function getImageSize(){
return getimagesize($this->file['tmp_name']);
}//end getImageSize

/**
 * Get the type of image resource
 * 
 * @access public
 * @return number
 */
public function getImageType(){
return $this->imageType;
}//end getImageType
/**
 * Get image width
 *
 * @access public
 * @return int|false Return the width of the image or FALSE on errors.
 */
public function getWidth(){
return imagesx($this->getImage());
}//end getWidth



/**
 * Check if system memory is enough for image processing
 *
 * @access public
 * @return array
 */
public function isConvertPossible()
{
  $status = true;
  if (function_exists('memory_get_usage') && ini_get('memory_limit'))
  {
    $info = $this->getImageSize();
    $MB = 1024 * 1024;
    $K64 = 64 * 1024;
    $tweak_factor = 1.6;
    $channels = isset($info['channels']) ? $info['channels'] : 3;
    $memory_needed = round(($info[0] * $info[1] * $info['bits'] * $channels / 8 + $K64) * $tweak_factor);
    $memory_needed = memory_get_usage() + $memory_needed;
    $memory_limit = ini_get('memory_limit');
    if ($memory_limit != '')
    {
      $memory_limit = substr($memory_limit, 0, -1) * $MB;
    }
    if ($memory_needed > $memory_limit)
    {
      $status = false;
    }
  }
  return compact('status', 'memory_needed', 'memory_limit');
}//end isConvertPossible




/**
 * Load locale image file for later processing
 *
 * @param string $path The path to image
 * @access public
 * @return self
 */
public function loadImage($path=NULL)
{
  if (!is_null($path))
  {
    $this->file = array(
      'tmp_name' => $path,
      'name' => basename($path)
    );
  }
  $info = $this->getImageSize();

  $this->width = $info[0];
  $this->height = $info[1];
  $this->setImageType($info[2]);
  $file = $path;

  switch ($this->imageType)
  {
    case IMAGETYPE_JPEG:
      $this->setImage(@imagecreatefromjpeg($file));
      break;
    case IMAGETYPE_GIF:
      $this->setImage(@imagecreatefromgif($file));
      break;
    case IMAGETYPE_PNG:
      $this->setImage(@imagecreatefrompng($file));
      break;
  }
  return $this;
}//end loadImage



/**
 * Write text to the image
 *
 * @param string $text The text string in UTF-8 encoding.
 * @param string $position Accept: 'tl', 'tr', 'tc', 'bl', 'br', 'bc', 'cl', 'cr', 'cc'. <b>t</b> stands for Top, <b>b</b> stands for Bottom, <b>l</b> stands for Left, <b>r</b> stands for Right, <b>c</b> stands for Center.
 * @access public
 * @return self
 */
public function setWatermark($text, $position)
{
  $rgb = $this->getColor();

  $color = imagecolorallocate($this->getImage(), $rgb[0], $rgb[1], $rgb[2]);

  $tb = imagettfbbox($this->getFontSize(), 0, $this->getFont(), $text);

  switch ($position)
  {
    case 'tl':
      $x = $tb[0];
      $y = $this->getFontSize();
      break;
    case 'tr':
      $x = floor($this->getWidth() - $tb[2]);
      $y = $this->getFontSize();
      break;
    case 'tc':
      $x = ceil(($this->getWidth() - $tb[2]) / 2);
      $y = $this->getFontSize();
      break;
    case 'bl':
      $x = $tb[0];
      $y = floor($this->getHeight() - $this->getFontSize());
      break;
    case 'br':
      $x = floor($this->getWidth() - $tb[2]);
      $y = floor($this->getHeight() - $this->getFontSize());
      break;
    case 'bc':
      $x = ceil(($this->getWidth() - $tb[2]) / 2);
      $y = floor($this->getHeight() - $this->getFontSize());
      break;
    case 'cl':
      $x = $tb[0];
      $y = ceil($this->getHeight() / 2);
      break;
    case 'cr':
      $x = floor($this->getWidth() - $tb[2]);
      $y = ceil($this->getHeight() / 2);
      break;
    case 'cc':
    default:
      $x = ceil(($this->getWidth() - $tb[2]) / 2);
      $y = ceil($this->getHeight() / 2);
      break;
  }

  imagettftext($this->getImage(), $this->getFontSize(), 0, $x, $y, $color, $this->getFont(), $text);
  return $this;
}//end Watermark



/**
 * Set font path
 *
 * @param string $path The path to font file
 * @access public
 * @return self
 */
public function setFont($path){
  $this->font = $path;
  return $this;
}//end setFont

/**
 * Set font size
 *
 * @param int $size
 * @access public
 * @return self
 */
public function setFontSize($size){
  $this->fontSize = $size;
  return $this;
}//end setFontSize

/**
 * Set RGB color
 *
 * @param array $color Expect numeric array, eg. array(255, 255, 255)
 * @access public
 * @return self
 */
public function setColor($color){
  if (is_array($color) && count($color) === 3){
  $this->color = $color;}
  return $this;
}//end set color

/**
 * Set image resource
 * 
 * @param resource $resource
 * @access public
 * @return self
 */
public function setImage($resource){
  if (is_resource($resource)){
  $this->image = $resource;}
  return $this;
}//end setImage

/**
 * Set the type of image resource
 *  
 * @param int $value
 * @return self
 */
public function setImageType($value){
  if (is_int($value)){
  $this->imageType = $value;}
  return $this;
}//end setImageType



/**
 * Outputs image without saving
 * 
 * @param string $image_type
 * @param number $compression
 */ 
public function output($compression=100)
{
  switch ($this->imageType)
  {
    case IMAGETYPE_JPEG:
      header("Content-Type: image/jpeg");
      imageinterlace($this->getImage(), true);
      imagejpeg($this->getImage(), NULL, $compression);
      imagedestroy($this->getImage());
      break;
    case IMAGETYPE_GIF:
      header("Content-Type: image/gif");
      imagegif($this->getImage());
      imagedestroy($this->getImage());
      break;
    case IMAGETYPE_PNG:
      header("Content-Type: image/png");
      imagepng($this->getImage());
      imagedestroy($this->getImage());
      break;
  }

  exit;
}//end output (this -> ImageType)



/**
* Image resize to fixed size 
*
* @param int $width
* @param int $height
* @access public
* @return self
*/
public function resize($width, $height)
{
  $new_image = imagecreatetruecolor($width, $height);
  switch ($this->imageType)
  {
    case IMAGETYPE_PNG:
      imagealphablending($new_image, false);
      imagesavealpha($new_image, true);
      $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
      imagefilledrectangle($new_image, 0, 0, $width, $height, $transparent);
      break;
    case IMAGETYPE_JPEG:
    case IMAGETYPE_GIF:
      $transparent_index = imagecolortransparent($this->getImage());
      if ($transparent_index >= 0)
      {
        $transparent_color = imagecolorsforindex($this->getImage(), $transparent_index);
        $transparent_index = imagecolorallocate($new_image, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
        imagefill($new_image, 0, 0, $transparent_index);
        imagecolortransparent($new_image, $transparent_index);
      }
      break;
  }
  imagecopyresampled($new_image, $this->getImage(), 0, 0, 0, 0, $width, $height, $this->width, $this->height);
  $this->width = $width;
  $this->height = $height;
  $this->setImage($new_image);

  return $this;
}//end resize



public function crop($src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $dst_x = 0, $dst_y = 0)
{
  $new_image = imagecreatetruecolor($dst_w, $dst_h);

  switch ($this->imageType)
  {
    case IMAGETYPE_PNG:
      imagealphablending($new_image, false);
      imagesavealpha($new_image, true);
      $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
      imagefilledrectangle($new_image, 0, 0, $dst_w, $dst_h, $transparent);
      break;
    case IMAGETYPE_JPEG:
    case IMAGETYPE_GIF:
      $transparent_index = imagecolortransparent($this->getImage());
      if ($transparent_index >= 0)
      {
        $transparent_color = imagecolorsforindex($this->getImage(), $transparent_index);
        $transparent_index = imagecolorallocate($new_image, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
        imagefill($new_image, 0, 0, $transparent_index);
        imagecolortransparent($new_image, $transparent_index);
      }
      break;
  }


imagecopyresampled($new_image, $this->getImage(), $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

  $this->width = $dst_w;
  $this->height = $dst_h;
  $this->setImage($new_image);
  return $this;
}//end crop



}//end class
?>