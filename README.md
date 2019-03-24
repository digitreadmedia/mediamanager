# OC Media Manager Extension Plugin

This plugin extends the default October CMS backend media manager and allows end users to edit / manipulate images inside the October CMS media library interface. This is helpful when you have clients that need to resize large images, without having to use an external image editor.  The plugin extends the Media Library core files, but does not change them.

## Minimum Requirements
- PHP >= 5.4 minimum, PHP >= 7 recommended
- GD library >= 2.0
- Imagick library (highly recommended but not required) >= 3.3.0 and ImageMagick >= 6.5.3

## Libraries
- Grafika (https://kosinix.github.io/grafika/) - Licence: MIT
- Intervention (http://image.intervention.io) - Licence: MIT

## Usage
Select an image in the media library and click on an editing button in the right-hand sidebar.  Provide the necessary settings in the modal window.

## Supported Operations
Resize, Rotate, Flip, Smart Crop, Grayscale
Options: Overwrite or Keep Original Images.  Creates directories: originals, edited

## Supported Formats
Tested with JPG, PNG, GIF

## Some Restrictions
Animated GIFs are currently only supported in resize operations.  In other operations the gif will be flattened.

Currently **transparent animated GIFs** are not supported.  Setting the image as transparent only will flatten the gif and preserve transparency.

Currently only available in the actual media library (../backend/media)

Some operations may decrease or increase filesize.

## Settings
- **Width:** image width in px, provided as positive integer
- **Height:** image height in px, provided as positive integer
- **Quality:** jpg quality in %, provided as positive integer
- **Angle:** rotation angle provided as positive or negative integer
- **Background:** provided in hexadecimal format (#000000)
