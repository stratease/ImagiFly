<?php

namespace stratease\ImagiFly\Util;


class File {

    protected $height;
    protected $width;
    protected $mimeType;
    protected $path;
    public function __construct($filePath)
    {
        $this->setFile($filePath);
    }

    public function setFile($filePath)
    {
        $this->path = $filePath;
        $data = getimagesize($filePath);
        $this->height = $data[1];
        $this->width = $data[0];
        $this->mimeType = $data['mime'];
    }

    public function getPath()
    {
        return $this->path;
    }
    public function getHeight()
    {
        return $this->height;
    }
    public function getWidth()
    {
        return $this->width;
    }
    public function getMimeType()
    {
        return $this->mimeType;
    }
} 