/**
 * Author: Amanda Benade
 * Company: Digitread Media
 * E-Mail: info@digitread.co.za
 * Last Edited: 23 March 2019
 * Licence: MIT
 */

jQuery(document).ready(function() {
    /**Add editor button to the preview screen**/
    $("div[data-control=\"media-preview-container\"]").on('DOMSubtreeModified', ".sidebar-image-placeholder[data-control=\"sidebar-thumbnail\"]", function() {
        var template = $('#editor-buttons').html();
        var newDiv = $('#image-editor').length;
        if(newDiv < 1) {
            $('.sidebar-image-placeholder-container').append(template);
        }
    });  
});

/*Show editing modal forms*/
function getForm($form) {
    if($form == 'resize') {
        $('#resizeForm').modal('show');
    }
    if($form == 'rotate') {
        $('#rotateForm').modal('show');
    }     
    if($form == 'flip') {
        $('#flipForm').modal('show');
    }    
    if($form == 'crop') {
        $('#cropForm').modal('show');
    }    
    if($form == 'grayscale') {
        $('#grayscaleForm').modal('show');
    }    
    if($form == 'watermark') {
        $('#watermarkForm').modal('show');
    }    
}   

/*Check for transparent animated GIFs*/
function validateChecked() {
    let isAnimated = $('#animated').is(':checked');
    let isTransparent = $('#transparent').is(':checked');
    if((isAnimated === true)&&(isTransparent === true)) {
        alert('Transparent animated GIFs are not supported at this time.');
        $('#animated').prop( "checked", false );
        $('#transparent').prop( "checked", false );
    }
}

/**Get the attributes for the selected element row and add input values**/
function getRowAttributes($row,$form) {
    /*Initiate Hash Table*/
    let data = {};
    let inputval = {};
    let isAnimated = false;
    let isTransparent = false;
    let keepOriginal = true;
    let canOverwrite = false;
    
    /*Iterate through the attributes and push the key/value pairs to the hash table*/
    $.each($row[0].attributes, function (key, val) {
        data[val.name] = val.value;
    });

    /*Get image editing data*/
    switch($form) {
        case '#resizeForm':
            isAnimated = $('#resizeForm #animated').is(':checked');
            isTransparent = $('#resizeForm #transparent').is(':checked');
            keepOriginal = $('#resizeForm #original').is(':checked');
            canOverwrite = $('#resizeForm #overwrite').is(':checked');
            inputval = resizeData(data);
            break;
        case '#rotateForm':
            isAnimated = $('#rotateForm #animated').is(':checked');
            isTransparent = $('#rotateForm #transparent').is(':checked');
            keepOriginal = $('#rotateForm #original').is(':checked');
            canOverwrite = $('#rotateForm #overwrite').is(':checked');
            inputval = rotateData(data,isTransparent);
            break;
        case '#flipForm':
            isAnimated = $('#flipForm #animated').is(':checked');
            isTransparent = $('#flipForm #transparent').is(':checked');
            keepOriginal = $('#flipForm #original').is(':checked');
            canOverwrite = $('#flipForm #overwrite').is(':checked');
            inputval = flipData(data);
            break;  
        case '#cropForm':
            keepOriginal = $('#cropForm #original').is(':checked');
            canOverwrite = $('#cropForm #overwrite').is(':checked');
            inputval = cropData(data);
            break;   
        case '#grayscaleForm':
            keepOriginal = $('#grayscaleForm #original').is(':checked');
            canOverwrite = $('#grayscaleForm #overwrite').is(':checked');
            inputval = grayscaleData(data);
            break;             
        default:
            break;
    }
    
    if((isAnimated === true)&&(isTransparent === true)) {
        keepOriginal = 1;
    }     

    inputval['overwrite'] = canOverwrite;
    inputval['original'] = keepOriginal;
    inputval['animated'] = isAnimated;
    inputval['transparent'] = isTransparent;
    
    /*Return the arry for further use*/
    return inputval;
}    

/*Resize image data*/
function resizeData(data) {
    data['process'] = 'resize';
    data['w'] = $('#resizeForm #w').val(); //width
    data['h'] = $('#resizeForm #h').val(); //height
    data['quality'] = $('#resizeForm #quality').val(); //jpg quality
    data['constraint'] = $('#resizeForm #constraint').val(); //resize by width/height/exact
    $('#resizeForm').hide();
    
    /*Return the array for further use*/
    return data;    
}

/*Crop image data*/
function cropData(data) {
    data['process'] = 'crop';
    data['w'] = $('#cropForm #w').val(); //width
    data['h'] = $('#cropForm #h').val(); //height
    $('#cropForm').hide();
    
    /*Return the array for further use*/
    return data;    
}

/*Rotate image data*/
function rotateData(data,isTransparent=false) {
    data['process'] = 'rotate';
    data['angle'] = $('#rotateForm #angle').val(); //rotation angle
    //background
    if(isTransparent !== true) {
        data['bg'] = $('#rotateForm #bg').val();
    }
    else {
        data['bg'] = null;
    }
    $('#rotateForm').hide();
    
    /*Return the array for further use*/
    return data;    
}

/*Flip image data*/
function flipData(data) {
    data['h'] = '';
    data['v'] = '';
    
    let isH = $('#flipForm #h').is(':checked'); //horizontal
    let isV = $('#flipForm #v').is(':checked'); //vertical
    data['process'] = 'flip';
    
    if(isH === true) {
        data['h'] = "h";
    }
    if(isV === true) {
        data['v'] = "v";
    }    

    $('#flipForm').hide();
    
    /*Return the array for further use*/
    return data;    
}

/*Grayscale image data*/
function grayscaleData(data) {
    data['process'] = 'grayscale';

    $('#grayscaleForm').hide();
    
    /*Return the array for further use*/
    return data;    
}

/*Reload medialibrary after editing*/
function imageComplete() {
    location.reload(true);
}    