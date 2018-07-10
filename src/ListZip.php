<?php
namespace morrisdj\ListZip;

use Aws\S3\S3Client;

class ListZip {

    private $s3client;

    /**
     * @param callable $provider
     * @return ListZip
     */
    public static function create(callable $provider) {
        return new self($provider);
    }

    /**
     * ListZip constructor.
     *
     * @param callable $provider
     */
    private function __construct(callable $provider) {
	    $this->s3client = new S3Client([
            'version' => 'latest',
            'region' => 'us-east-1',
            'credentials' => $provider
        ]);
    }

    /**
     * @param string $zipFile
     * @param string $bucket
     * @return array of FileItem
     */
    public function getFiles($zipFile, $bucket) {

		// this will be the return array of file info
        $ret = array();

        // get last 18 bytes of the file which point to the "end of central directory" record
        $res = $this->s3client->getObject(['Bucket' => $bucket, 'Key' => $zipFile, 'Range' => 'bytes=-18']);
        // unpack the central directory into an array
        $ecd = unpack('vdisk/vstart/vtotal/ventries/Vsize/Voffset', $res['Body']);

        // 'offset' points to the "central directory" of the files
        // 'size' is the length of the central directory
        $range2 = 'bytes=' . $ecd['offset'] . '-' . ($ecd['offset'] + $ecd['size']);
        $res = $this->s3client->getObject(['Bucket' => $bucket, 'Key' => $zipFile, 'Range' => $range2]);
        $files = $res['Body'];
        
        // unpack the central directory data into an array
        while (strlen($files) > 1) {
        	// unpack the first 46 bytes of the string
            $file = unpack('a2pk/vcode/Cversion/Chostos/Cminver/Ctargetos/vgpbit/vmethod/Vdate/Vcrc/Vcsize/Vusize/vnamelength/vextralength/vcommentlength/vdiskno/vattrs/Vexattrs/Vheaderoffset', $files);
            $ret[] = FileItem::create(
                substr($files, 46, $file['namelength']),
            	$file['usize'],
                $this->dosToUnixTime($file['date'])
            );
            $files = substr($files, 46 + $file['namelength'] + $file['extralength'] + $file['commentlength']);
        }
        return $ret;
    }

    /**
     * @param int $dosTime
     * @return \DateTime
     */
    private function dosToUnixTime($dosTime) {
        $date = new \DateTime();
        $date->setDate(
            (($dosTime >> 25) & 0x7f) + 1980,
            (($dosTime >> 21) & 0x0f) - 1,
            ($dosTime >> 16) & 0x1f
        );
        $date->setTime(
            ($dosTime >> 11) & 0x1f,
            ($dosTime >> 5) & 0x3f,
            ($dosTime << 1) & 0x3e
        );
        return $date;
    }
	
}
