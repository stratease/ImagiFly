<?php
namespace stratease\ImagiFly\Filter;
use stratease\ImagiFly\Filter\FilterInterface;
use Intervention\Image\Image;
abstract class BaseFilter implements FilterInterface {
    /**
     * {@inheritdoc}
     */
    public function writesBaseImageToCanvas()
    {
        return true;
    }
    /**
     * {@inheritdoc}
     */
    public function setBaseImage(Image $baseImage)
    {
        $this->baseImage = $baseImage;
    }
    /**
     * {@inheritdoc}
     */
    public function setCanvas(Image $canvas)
    {
        $this->canvas = $canvas;
    }


    /**
     * {@inheritdoc}
     */
    public function buildArgs(array $parsedArgs)
    {
        return $parsedArgs['args'];
    }
} 