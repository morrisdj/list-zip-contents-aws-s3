<?php
if (!class_exists('CFRuntime'))
    require 'awslib/sdk.class.php';

class listzip {

    const BUCKET = 'YOUR-DEFAULT-BUCKET';
    const S3USER_ACCESS_KEY = 'YOUR-ACCESS-KEY';
    const S3USER_SECRET_KEY = 'YOUR-SECRET-KEY';
    private $S3;
    
    function __construct() {
	   $this->S3 = new AmazonS3(self::S3USER_ACCESS_KEY, self::S3USER_SECRET_KEY);
       //$this->S3->set_region(AmazonS3::REGION_EU_W1);
    }

    function files($zipfile, $bucket) {

		// this will be the return array of file info
        $ret = array();

		// get the headers for the zip file from S3
		// this will give us the size of the zip file (content-length)
        $res = $this->S3->get_object_headers($bucket, $filename);
        $headers = $res->header; 
        // if the header is not present for any reason, return an empty result
        if (!isset($headers['content-length'])) return $ret;
        
        // get last 18 bytes of the file which point to the "end of central directory" record
        $length = $headers['content-length'];
        $range1 = ($length - 18) . '-' . $length;
        $res = $this->S3->get_object($bucket, $zipfile, array('range' => $range1));
        $data = ((substr($res->status, 0, 2) == '20') ? $res->body : '');
        // unpack the central directory into an array
        $ecd = unpack('vdisk/vstart/vtotal/ventries/Vsize/Voffset', $data);
        
        // offset points to the "central directory" of the files
        // size is the length of the central directory
        // get the central directory data
        $range2 = $ecd['offset'] . '-' . ($ecd['offset'] + $ecd['size']);
        $res = $this->S3->get_object($bucket, $zipfile, array('range' => $range2));
        $files = ((substr($res->status, 0, 2) == '20') ? $res->body : '');
        
        // unpack the central directory data into an array
        while (strlen($files) > 1) {
            $file = unpack('a2pk/vcode/Cversion/Chostos/Cminver/Ctargetos/vgpbit/vmethod/Vdate/Vcrc/Vcsize/Vusize/vnamelength/vextralength/vcommentlength/vdiskno/vattrs/Vexattrs/Vheaderoffset', $files);
            $file += array('filename' => substr($files, 46, $file['namelength']),
                                   'extra' => substr($files, 46 + $file['namelength'], $file['extralength']),
                                   'comment' => substr($files, 46 + $file['namelength'] + $file['extralength'], $file['commentlength']),
                                   'timestamp' => $this->dosToUnixTime($file['date']) // convert dos time to unix timestamp (see below)
                          );
            $ret[] = $file;
            $files = substr($files, 46 + $file['namelength'] + $file['extralength'] + $file['commentlength']);
        }
        return $ret;
    }

    
    /** Converts DOS time to Unix time */
    function dosToUnixTime($dosTime) {
        $date = mktime(
            (($dosTime >> 11) & 0x1f), // hours
            (($dosTime >> 5) & 0x3f),  // minutes
            (($dosTime << 1) & 0x3e),  // seconds
            ((($dosTime >> 21) & 0x0f) - 1),   // month
            (($dosTime >> 16) & 0x1f),         // day
            ((($dosTime >> 25) & 0x7f) + 1980) // year
        );
        return $date;
    }
	
}
