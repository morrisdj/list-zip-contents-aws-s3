<?php
namespace morrisdj\ListZip;

class FileItem {

    private $filename;
    private $size;
    private $timestamp;

    /**
     * @param string $filename
     * @param int $size
     * @param \DateTime $timestamp
     * @return FileItem
     */
    public static function create($filename, $size, \DateTime $timestamp) {
        return new self($filename, $size, $timestamp);
    }

    /**
     * FileItem constructor.
     *
     * @param string $filename
     * @param int $size
     * @param \DateTime $timestamp
     */
    private function __construct($filename, $size, \DateTime $timestamp) {
        if (!is_string($filename)) {
            throw new \InvalidArgumentException('Filename must be a string');
        }
        if (!is_int($size)) {
            throw new \InvalidArgumentException('Size must be an integer');
        }
        $this->filename = $filename;
        $this->size = $size;
        $this->timestamp = $timestamp;
    }

    /**
     * @return string
     */
    public function getFilename() {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->filename;
    }

    /**
     * @return int
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp() {
        return $this->timestamp;
    }

}
