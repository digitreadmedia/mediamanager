<?php namespace DigitreadMedia\MediaManager\Classes;

use Grafika\Grafika;
use Intervention\Image\ImageManager;

/**
 * Image Manipulation / Editing
 * Author: Amanda Benade
 * Company: Digitread Media
 * E-Mail: info@digitread.co.za
 * Last Edited: 23 March 2019
 * Licence: MIT
 */
class MediaImageManager 
{
    protected $editor;
    protected $image;
    protected $animated;
    protected $library;
    protected $directory;
    protected $overwrite;
    protected $keeporiginal;
    protected $resized;
    protected $originals;
    protected $original;
    protected $size;
    protected $forcelibrary;
    protected $permissions = 'X00';

    /**
     * $image: full absolute image path with image name
     * $parentDir: image residing (absolute) directory 
     * $base: images base/parent directory
     * $driver: default driver for ImageManager: gd or imagick
     * $keeporiginal: boolean true/false Should we keep the original image? Default true
     * $overwrite: boolean true/false Should we overwrite the original image? Default false
     * $animated: boolean true/false Is the image an animated gif?
     * $transparent: boolean true/false Is the image transparent?
     * $forcelibrary: Force the use of the Grafika library
     */
    public function __construct($image, $parentDir, $base, $driver = 'gd', bool $keeporiginal = true, bool $overwrite = false, bool $animated = false, bool $transparent = false, bool $forcelibrary = false)
    {

        /*Check support for system PHP*/
        $this->checkPhpSupport();    
        
        //Verify directory
        if (!is_null($parentDir) && !is_dir($parentDir)) {
            throw new \Exception("$parentDir is not a valid directory.");
        }

        //Force use of Grafika in some instances
        if($forcelibrary == true) {$this->forcelibrary = 'Grafika';}

        $this->directory = $parentDir;
        $this->originals = $base . DIRECTORY_SEPARATOR . "originals";
        $this->resized = $base . DIRECTORY_SEPARATOR . "edited";
        $this->overwrite = $overwrite;
        
        //Override image retention for transparent animated gifs (not supported by libraries)
        if(($animated)&&($transparent)) {
            throw new \Exception('Transparent animated GIFs are not supported at this time. Please refresh the page.');
            $this->keeporiginal = true;
        }
        else {
            $this->keeporiginal = $keeporiginal;
        }
        $this->permissions = 'X00';
        
        //Set writing permissios
        if(($keeporiginal == true) && ($overwrite == true)) {$this->permissions = 'X11'; } //create original directory + set target directory to parent
        if(($keeporiginal == true) && ($overwrite == false)) {$this->permissions = 'X10'; } //create original directory & set target directory to resize + if parent = resize : keep/continue
        if(($keeporiginal == false) && ($overwrite == true)) {$this->permissions = 'X01'; } //set target directory to parent
        if(($keeporiginal == false) && ($overwrite == false)) {$this->permissions = 'X00'; } //set target directory to resize + if parent = resize : keep/continue

        //Set directories
        switch($this->permissions) 
        {
            case 'X11':
                $this->createDir($this->originals);
                $this->targetDir = $parentDir;
                break;
            case 'X10':
                $this->createDir($this->resized);
                $this->targetDir = $this->resized;
                break;
            case 'X01':
                $this->targetDir = $parentDir;
                break;
            case 'X00':
                $this->createDir($this->resized);
                $this->targetDir = $this->resized;
                break;
        }

        $this->size = getimagesize($image);

        //Setup editors
        if((($animated)&&(!$transparent))||($this->forcelibrary == 'Grafika')) {
            $this->library = 'Grafika';
            $this->editor = Grafika::createEditor(array('Gd', 'Imagick'));
            $this->editor->open($this->image, $image);
        }
        else {
            $this->library = 'ImageManager';
            $this->editor = new ImageManager(array('driver' => $driver));
            $this->image = $this->editor->make($image);
        }

    }
    
    /**
     * Check if certain features are supported by the server PHP version
     */
    protected function checkPhpSupport() 
    {
        // Check if PHP version is valid
        if (PHP_VERSION_ID < 50300) {
            throw new \Exception ('This functionality requires PHP5.3+');
        } 
        elseif (PHP_VERSION_ID < 50400) {
            $this->forcelibrary = 'Grafika';
        }
        
    }
    
    protected function createDir($dir) 
    {
        //Create directory if not exist
        if (!is_null($dir) && !is_dir($dir)) {
            try {
                mkdir($dir);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }        
    }
    
    /**
     * Resize Image
     * int $width in px
     * int $height in px
     * string $resizeby width/height/exact
     */
    public function onResize($width,$height,$resizeby) 
    {
        
        if($this->library == 'ImageManager') {
            if($resizeby == 'width') 
            {
                //Exact Width
                $this->image->resize($width, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            elseif($resizeby == 'height') {
                //Exact Height
                $this->image->resize(null, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });            
            }
            elseif($resizeby == 'exact') {
                //Exact Height
                $this->image->fit($width, $height, function ($constraint) {
                    $constraint->upsize();
                });
            }
        }
        else {
            if(($resizeby == 'width')&&($this->size[0] > $width)) 
            {
                //Exact Width
                $this->editor->resizeExactWidth($this->image, $width);
            }
            elseif(($resizeby == 'height')&&($this->size[1] > $height)) {
                //Exact Height
                $this->editor->resizeExactHeight($this->image, $height);
            }
            elseif(($resizeby == 'exact')&&(($this->size[0] > $width) || ($this->size[1] > $height))) {
                //Exact Height
                $this->editor->resizeExact($this->image, $width, $height);
            }
        }

    }
    
    /**
     * Rotate image
     * int $angle -/+
     * string $background #000000 (hexadecimal)/null
     */
    public function onRotate($angle,$background=null) 
    {
        
        if($this->library == 'ImageManager') {
            if($background) {
                $this->image->rotate($angle,$background);
            } else {
                $this->image->rotate($angle);
            }
        }
        else {
            $this->editor->rotate($this->image,$angle,$background);
        }

    }
    
    /**
     * Grayscale image filter
     * $force = true
     * Force use of Grafika
     */
    public function onGrayscale() 
    {
        
        if($this->library == 'Grafika') {
            $filter = Grafika::createFilter('Grayscale'); 
            $this->editor->apply($this->image, $filter ); 
        }
        else {
            /*Incorrect image library selected*/
            throw new \Exception ('This functionality requires the $force parameter to be set to true');
        }

    }    
    
    /**
     * Flip image
     * array $modes (array('v'=>'v','h'=>'h'))
     */
    public function onFlip($modes=[]) 
    {
        $h = $modes['h'];
        $v = $modes['v'];

        if($this->library == 'ImageManager') {
            if($h == 'h') {$this->image->flip($h);}
            if($v == 'v') {$this->image->flip($v);}
        }
        else {
            if($h == 'h') {$this->editor->flip($this->image,$h);}
            if($v == 'v') {$this->editor->flip($this->image,$v);}                
        }

    }  
    
    /**
     * Crop images
     * int $width
     * int $height
     * string $mime
     * $force = true
     * Force use of Grafika
     */
    public function onCrop($width,$height,$mime) 
    {
        if($width > $this->size[0]) {$width = $this->size[0];}
        if($height > $this->size[1]) {$height = $this->size[1];}
        
        if($this->library == 'Grafika') {
            $this->editor->crop($this->image,$width,$height,'smart');
        }
        else {
            /*Incorrect image library selected*/
            throw new \Exception ('This functionality requires the $force parameter to be set to true');
        }  

    }     
    
    /**
     * Save edited image
     * string $name 
     * int $quality
     */
    public function onSave($name,$quality=null) 
    {
        if (!is_dir($this->targetDir)) {
            if(!mkdir($this->targetDir)) {
                throw new \Exception($this->targetDir.': The destination must be an existing directory.');
            }
        }
        if (!is_writable($this->targetDir)) {
            throw new \Exception('The destination must be a writable directory.');
        }
        
        //Move the original file
        if($this->permissions == 'X11') 
        {
            try {
                $current = $this->directory . DIRECTORY_SEPARATOR . $name;
                $backup = $this->originals . DIRECTORY_SEPARATOR . $name;
                if(file_exists($backup)) {
                    $backup = $this->originals . DIRECTORY_SEPARATOR . date('Ymdhis') . '_' .$name;
                }
                $fileBackup = rename($current, $backup);
            } catch (Exception $e) {
                echo $e->getMessage();
            }            
        }
        
        //Set new image name & directory
        $new_img = $this->targetDir . DIRECTORY_SEPARATOR . $name;

        //Rename file if exists with overwriting disabled
        if((!$this->overwrite)&&(file_exists($new_img))) {
            $new_img = $this->targetDir . DIRECTORY_SEPARATOR . date('Ymdhis') . '_' .$name;
        }

        try {
            //Save new image
            $new_img = str_replace('//','/',$new_img); //Remove any sneaky double slashes
            if($this->library == 'ImageManager') {
                $this->image->save($new_img,$quality);
            }
            else {
                $this->editor->save($this->image, $new_img);
            }

            return $new_img;
        } catch (Exception $e) {
                echo $e->getMessage();
        }
        
    }
}