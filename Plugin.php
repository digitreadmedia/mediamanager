<?php namespace DigitreadMedia\MediaManager;

use System\Classes\PluginBase;
use \DigitreadMedia\MediaManager\Classes\MediaImageManager;
use System\Classes\MediaLibrary;
use App;
use Event;
use Input;
use Config;
use Redirect;
use ValidationException;
use Backend;

/**
 * Image Manipulation / Editing
 * Author: Amanda Benade
 * Company: Digitread Media
 * E-Mail: info@digitread.co.za
 * Last Edited: 23 March 2019
 * Licence: MIT
 */
class Plugin extends PluginBase
{
    public function boot()
    {
        App::error(function($e) {
          if(preg_match('/Transparent animated GIFs are not supported/',$e->getMessage())) {
    				return 'Transparent animated GIFs are not supported at this time. Please refresh the page.';
    			}
          if(preg_match('/This functionality requires the \$force parameter to be set to true/',$e->getMessage())) {
    				return 'Incorrect image library selected. Please refresh the page.';
    			}
    	  if(preg_match('/nimated GIFs are not supported at this time/',$e->getMessage())) {
    				return 'Animated GIFs are not supported at this time. Please refresh the page.';
    			}
    	  if(preg_match('/accepts only numeric values/',$e->getMessage())) {
    				return 'Please enter a numeric value.';
    			}    			
        });
    
        \Backend\Widgets\MediaManager::extend(function ($widget) {
            $widget->addViewPath(plugins_path().'/digitreadmedia/mediamanager/backend/widgets/mediamanager/partials/');
            $widget->addJs('/plugins/digitreadmedia/mediamanager/assets/js/get_row_attributes.js');
            $widget->addCss('/plugins/digitreadmedia/mediamanager/assets/css/custom.css');
            $widget->addDynamicMethod('onCustomImage', function() use ($widget) {
                               
                //Set the media path
                $base = Backend::url('backend/media');
                $path = base_path() . Config::get('cms.storage.media.path');

                //Get the images & editing values
                $checked = post('checked');
                
                /*tracelog($checked);*/
                if(!is_array($checked)) {
                   throw new \Exception("The form contained no data."); 
                }
                
                //Sanitize the posted values
                $data = filter_var_array($checked,FILTER_SANITIZE_STRING); 
                
                //Set the folders
                $folder = $data['data-folder'];
                $parent = $path . $folder;

                //Default values
                $image = '';
                $force = false; //Force Grafik Library
                $driver = 'gd'; //Default image driver
                $keeporiginal = true;
                $transparent = false;
                $overwrite = false;
                $animated = false;
                $resizeby = "width";
                $width = 1600;
                $height = 1200;
                $quality = null;
                $mime = null;
                $angle = null;
                $background = 'null';
                $mode = [];


                /*tracelog($data);*/
                //Process the posted array
                if(($data['data-item-type'] == 'file')&&($data['data-document-type'] == 'image')) {
                    $image = $path . $data['data-path'];
                    $mime = mime_content_type($image);
                    $name = $data['data-title'];
                    $keeporiginal = filter_var($data['original'], FILTER_VALIDATE_BOOLEAN);
                    $overwrite = filter_var($data['overwrite'], FILTER_VALIDATE_BOOLEAN);
                    $animated = filter_var($data['animated'], FILTER_VALIDATE_BOOLEAN);
                    $transparent = filter_var($data['transparent'], FILTER_VALIDATE_BOOLEAN);
                    
                    if(isset($data['process']) && $data['process'] == 'resize') {
                        $resizeby = filter_var($data['constraint'], FILTER_SANITIZE_STRING);
                        $width = preg_replace("/[^0-9.]/", "", $data['w']);
                        $height = preg_replace("/[^0-9.]/", "", $data['h']);
                        $quality = filter_var($data['quality'], FILTER_SANITIZE_NUMBER_INT);
                        if($quality < 1) {$quality = null;}
                    }
                    elseif(isset($data['process']) && $data['process'] == 'rotate') {
                        $background = filter_var($data['bg'], FILTER_SANITIZE_STRING);
                        $angle = filter_var($data['angle'], FILTER_SANITIZE_NUMBER_INT);
                    }  
                    elseif(isset($data['process']) && $data['process'] == 'flip') {
                        $mode = [
                            'h' => filter_var($data['h'],FILTER_SANITIZE_STRING), 
                            'v' => filter_var($data['v'],FILTER_SANITIZE_STRING),
                        ];
                    }    
                    elseif(isset($data['process']) && $data['process'] == 'crop') {
                        $force = true;
                        $w = preg_replace("/[^0-9.]/", "", $data['w']);
                        $h = preg_replace("/[^0-9.]/", "", $data['h']);
                        if($w > 0) {$width = $w;}
                        if($h > 0) {$height = $h;}
                    } 
                    elseif(isset($data['process']) && $data['process'] == 'grayscale') {
                        $force = true;
                    }                
                    
                    if((!$width)&&(!$height)) {
                        list($width, $height) = getimagesize($image);
                    }

                }

                try {
                    $action = new MediaImageManager($image, $parent, $path, $driver, $keeporiginal, $overwrite, $animated,$transparent,$force);
                    
                    switch($data['process']) {
                        case 'resize':
                            $action->onResize($width,$height,$resizeby);
                            break;
                        case 'rotate':
                            $action->onRotate($angle,$background);
                            break;
                        case 'flip':
                            $action->onFlip($mode);
                            break;
                        case 'crop':
                            $action->onCrop($width,$height,$mime);
                            break;   
                        case 'grayscale':
                            $action->onGrayscale();
                            break;                            
                        default:
                            break;
                    }
                    
                    $new_image = $action->onSave($name,$quality);
                    $newimg = str_replace($path,'',$new_image);
                    MediaLibrary::instance()->resetCache();
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            });    
        });
        
        
    }
}
