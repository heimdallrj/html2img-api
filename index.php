<?php
/** 
 * @package: html2img-api
 * @author: @_thinkholic
 * @version: v0.1 02012015
 *
 * @credits: http://buffernow.com/html-to-image-php-script/
 *
 */
require_once('html2_pdf_lib/html2pdf.class.php');

# TimeZone Settings
//date_default_timezone_set("Asia/Colombo");
$timestamp = strtotime(date("Y-m-d H:i:s"));

# Status
$status = array(  
    100 => 'Continue',
    101 => 'Switching Protocols',
    200 => 'OK',
    201 => 'Created',
    202 => 'Accepted',
    203 => 'Non-Authoritative Information',
    204 => 'No Content',
    205 => 'Reset Content',
    206 => 'Partial Content',
    300 => 'Multiple Choices',
    301 => 'Moved Permanently',
    302 => 'Found',
    303 => 'See Other',
    304 => 'Not Modified',
    305 => 'Use Proxy',
    307 => 'Temporary Redirect',
    400 => 'Bad Request',
    401 => 'Unauthorized',
    402 => 'Payment Required',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    406 => 'Not Acceptable',
    407 => 'Proxy Authentication Required',
    408 => 'Request Time-out',
    409 => 'Conflict',
    410 => 'Gone',
    411 => 'Length Required',
    412 => 'Precondition Failed',
    413 => 'Request Entity Too Large',
    414 => 'Request-URI Too Large',
    415 => 'Unsupported Media Type',
    416 => 'Requested range not satisfiable',
    417 => 'Expectation Failed',
    500 => 'Internal Server Error',
    501 => 'Not Implemented',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Time-out',
    505 => 'HTTP Version not supported'
);

# Respond inits
$data['statusCode'] = 400;
$data['contentType'] = 'application/json';

# Headers
header("Access-Control-Allow-Orgin: *");
header("Access-Control-Allow-Methods: *");

# Respond inits
$data['validToken'] = FALSE;
$data['requestTimestamp'] = $timestamp;

if ( isset( $_REQUEST['api'] ) && ( $_REQUEST['api'] ) )
{
    $flag = FALSE;
    
    # validate the Request
    if ( (isset($_REQUEST['html'])) && (isset($_REQUEST['imgType'])) )
    {
        $flag = TRUE;
    }
    
    # Media Type Array for Images
    $mediaType = array(
        'image/gif' => array(
                            'ext' => 'gif',
                            'desc' => 'GIF image'
                        ),
        'image/jpeg' => array(
                            'ext' => 'jpg',
                            'desc' => 'JPEG JFIF image'
                        ),
        'image/pjpeg' => array(
                            'ext' => 'jpeg',
                            'desc' => 'JPEG JFIF'
                        ),
        'image/png' => array(
                            'ext' => 'png',
                            'desc' => 'Portable Network Graphics'
                        ),
        'image/svg+xml' => array(
                            'ext' => 'svg',
                            'desc' => 'SVG'
                        ),
        'image/tiff' => array(
                            'ext' => 'tiff',
                            'desc' => 'TIFF image'
                        ),
        'image/vnd.djvu' => array(
                            'ext' => 'djvu',
                            'desc' => 'DJVU image'
                        ),
    );
    
    if ( $flag )
    {
        # Read Request Param
        $html = $_REQUEST['html'];

        $imgNamePref = time();
        if ( isset($_REQUEST['imgNamePref']) )
        {
            $imgNamePref = $_REQUEST['imgNamePref'];
        }

        $imgType = $_REQUEST['imgType'];
        $imgExt = $mediaType[$imgType]['ext'];

        $w = 400;
        if ( isset($_REQUEST['w']) ) { $w = $_REQUEST['w']; }

        $h = 400;
        if ( isset($_REQUEST['h']) ) { $w = $_REQUEST['h']; }

        # Buffering
        ob_start();  
        print $html;
        $imgData = ob_get_clean();
        ob_clean ();

        try
        {
            # JSON Respond
            $data['action'] = "success";

            # Generate PDF
            $html2pdf = new HTML2PDF('P', 'A4', 'en');
            //$html2pdf->setModeDebug();
            $html2pdf->setDefaultFont('courier');
            $html2pdf->writeHTML($imgData);
            $file = $html2pdf->Output('temp.pdf','F');

            # Generate Image
            $im = new imagick('temp.pdf');
            $im->setImageFormat( $imgExt );
            $imgName = $imgNamePref.'.'.$imgExt;
            $im->setSize($w,$h);
            $im->writeImage( $imgName );
            $im->clear();
            $im->destroy(); 

            # Remove temp pdf
            unlink('temp.pdf');

            # JSON Respond
            $data['imgPath'] = $imgName;
            $data['statusCode'] = 200;

        }
        catch(HTML2PDF_exception $e)
        {
            # JSON Respond
            $data['action'] = "fail";
            $data['error'] = $e;
            //exit;
        }
    }
}

 # JSON Respond
$data['status'] = $status[$data['statusCode']];

# headers again
header('Content-Type: application/json');
header("HTTP/1.1 " . $data['statusCode'] . " " . $data['status']);

# return respond
print json_encode($data);

// EOF.