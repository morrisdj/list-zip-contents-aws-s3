<?php
include '../vendor/autoload.php';
include  'testfile.php';

use Aws\Credentials\CredentialProvider;
use morrisdj\ListZip\FileItem;
use morrisdj\ListZip\ListZip;

class ListZipTest extends \PHPUnit\Framework\TestCase {

    public function testListZip() {

        $provider = CredentialProvider::ini('default', __DIR__ . '/awscredentials.ini');

        $listZip = ListZip::create($provider);

        $files = $listZip->getFiles(TESTFILE, TESTBUCKET);
        $this->assertInternalType('array', $files);

        /** @var FileItem $file */
        $file = $files[0];
        $this->assertInstanceOf(FileItem::class, $file);
        $this->assertInternalType('string', $file->getFilename());
        $this->assertInternalType('integer', $file->getSize());
        $this->assertInstanceOf(\DateTime::class, $file->getTimestamp());
    }

}
