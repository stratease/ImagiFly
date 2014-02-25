<?php
namespace stratease\imagebuilder;
use Intervention\Image\Image;
class Builder
{
    /**
     * @var null
     */
    public $baseWidth = null;
    /**
     * @var null
     */
    public $baseHeight = null;
    /**
     * @var null
     */
    protected $baseImage = null;
    /**
     * @var null
     */
    protected $filePath = null;
    /**
     * @var array
     */
    public $baseDirectories = [];
    /**
     * @var bool
     */
    public $cache = true;
    /**
     * @var array
     */
    public $filters = [];
    /**
     * @var null|\stdClass
     */
    public $meta = null;
    /**
     * @var string
     */
    public $cacheDir = '../.cache/';
    /**
     * @var int Seconds to live
     */
    public $cacheDuration = 3600;
    /**
     * @param $baseDirs
     */
    public function __construct($baseDirs)
    {
        $this->cacheDir = __DIR__.'/../.cache/';
        if(is_string($baseDirs)) {
            $this->baseDirectories = [$baseDirs];
        } else {
            $this->baseDirectories = $baseDirs;
        }
        $this->meta = new \stdClass();
    }

    /**
     * @param $img
     * @return $this
     * @throws Exception
     */
    public function baseImage($img)
    {
        $this->baseImage = $img;
        $this->filePath = null; // clear it out..
        foreach($this->baseDirectories as $dir) {
            if(is_file($dir.'/'.$this->baseImage)) {
                $this->filePath = realpath($dir.'/'.$this->baseImage);
                break;
            }
        }
        // find our file?
        if($this->filePath === null) {
            throw new Exception('Could not locate base image! ('.$this->baseImage.')');
        }
        // Array ( [0] => 183 [1] => 313 [2] => 3 [3] => width="183" height="313" [bits] => 8 [mime] => image/png )
        $data = getimagesize($this->filePath);
        $this->meta->height = $data[1];
        $this->meta->width = $data[0];
        $this->meta->mimetype = $data['mime'];
        return $this;
    }

    /**
     * @param $width
     * @param $height
     * @return $this
     */
    public function baseSize($width, $height)
    {
        $this->baseHeight = $height;
        $this->baseWidth = $width;
        return $this;
    }

    /**
     * @param bool $bool
     * @return $this
     */
    public function cache($bool = true)
    {
        $this->cache = (bool)$bool;
        return $this;
    }

    /**
     * @param $func
     * @param $args
     * @return $this
     */
    public function __call($func, $args)
    {
        $this->filters[] = ['filter' => $func,
            'args' => $args];
        return $this;
    }

    /**
     * @param $image
     * @param $percent
     * @return mixed
     */
    protected function percResize($image, $percent)
    {
        $percent = $percent * .01;
        // find the percent of the orig values..
        $width = (imagesx($image->resource) * $percent);
        $height = (imagesy($image->resource) * $percent);

        return $image->resize($width, $height, true);
    }

    /**
     * @param $canvas
     * @param $baseImage
     * @param $filterName
     * @param $args
     * @return mixed
     */
    public function applyFilter($canvas, $baseImage, $filterName, $args)
    {
        $args = array_merge([$canvas, $baseImage], $args);
        return call_user_func_array([$this, $filterName.'Filter'], $args);
    }

    /**
     * @param $canvas
     * @param $baseImage
     * @param $cnt
     * @param string $style
     * @return mixed
     */
    public function overlayFilter($canvas, $baseImage, $cnt, $style = 'triangle')
    {
        $height = imagesy($canvas->resource);
        $width = imagesx($canvas->resource);
        if($style == 'left') {
            if($cnt == 2) {
                $img = $this->percResize(clone $baseImage,48);
                $y = (int)(($height / 2) - (imagesy($img->resource) / 2));
                $canvas->insert($img, 0, $y);
                $img = $this->percResize(clone $baseImage, 56);
                $x = (int)(($width / 2) - (imagesx($img->resource) / 2));
                $canvas->insert($img, $x, $y);
            }
        }
        else if($style == 'right') {
            if($cnt == 2) {
                $img = $this->percResize(clone $baseImage,48);
                $y = (int)(($height / 2) - (imagesy($img->resource) / 2));
                $x = ($width - imagesx($img->resource));
                $canvas->insert($img, $x, $y);
                $img = $this->percResize(clone $baseImage, 56);
                $x = (int)(($width / 2) - (imagesx($img->resource) / 2));
                $canvas->insert($img, $x, $y);
            }
        }
        else if($style == 'triangle') {
            switch($cnt) {
                case 3:
                    $img = $this->percResize(clone $baseImage,48);
                    $y = (int)(($height / 2) - (imagesy($img->resource) / 2));
                    $canvas->insert($img, 0, $y);
                    $x = ($width - imagesx($img->resource));
                    $canvas->insert($img, $x, $y);
                    $img = $this->percResize(clone $baseImage, 56);
                    $x = (int)(($width / 2) - (imagesx($img->resource) / 2));
                    $canvas->insert($img, $x, $y);
                    break;
                case 2:
                    $img = $this->percResize(clone $baseImage,48);
                    $y = (int)(($height / 2) - (imagesy($img->resource) / 2));
                    $canvas->insert($img, 0, $y);
                    $x = ($width - imagesx($img->resource));
                    $canvas->insert($img, $x, $y);
                    break;
                case 1:
                default:
                    return $baseImage;
            }
        }
        // todo Ditch the hardcoded stuff... started algorithm below
        /*
        // find out how big/ how many rows..
        $remainder = 0;
        $rows = [];
        $left = $cnt;
        for($i = 1; $i < $cnt; $i++) {
            $left -= $i;

            $rows[] = $i;
            if($left < ($i + 1)) {
                $remainder = $left;
                break;
            }

        }
        // .. distribute remainder up the rows
        if($remainder > 0) {
            $n = (count($rows) - 1);
            while($remainder-- > 0) {
                $rows[$n--]++;
            }
        }
        // some algorithm params for the overlay sizing/staggering...
        // iterate on rows, start in back e.g. [1, 2, 3 ] ...
        for($i = (count($rows) - 1); $i >= 0; $i--) {
            $img = $this->percResize(clone $baseImage, $percent);

            // how many images for this row?
            for($n = 0; $n < $rows[$i]; $n++) {
                // @todo - this is wrong, fix the row image inserts...
                // $canvas->insert($img, $n * 10, $n * 10);
            }
        }*/

        return $canvas;
    }

    /**
     * @param $baseImage
     * @param $filters
     * @return Image|mixed
     */
    public function applyFilters($baseImage, $filters)
    {
        // start with our canvas
        if($this->baseHeight !== null) { // specified height?
            $height = $this->baseHeight;
            $width = $this->baseWidth;
        } else { // assume 100% of base image
            $width = $this->meta->width;
            $height = $this->meta->height;
        }
        $canvas = Image::canvas($width, $height);
        // resize base
        $baseImage->resize($width, $height, true, true);
        if(count($filters)) {
            foreach($filters as $filter) {
                // write filter to our current canvas
                $canvas = $this->applyFilter($canvas, $baseImage, $filter['filter'], $filter['args']);
            }
        } else {
            // no filters, just apply image
            $canvas->insert($baseImage);
        }
        return $canvas;
    }

    /**
     * @return int
     */
    public function output()
    {
        // build it's name, used for cache and non-cache use
        // file extension..
        $ext = substr($this->filePath, strrpos($this->filePath, ".") + 1);
        $cacheId = sha1($this->filePath.json_encode($this->filters));
        $genFile = realpath($this->cacheDir).'/'.$cacheId.'.'.$ext;
        // are we using cache?
        if(($this->cache === false
            || file_exists($genFile) === false)
        || ((filemtime($genFile) + $this->cacheDuration) < time())) {
            // get our main image resource...
            $image = Image::make($this->filePath);
            // apply the filters
            $image = $this->applyFilters($image, $this->filters);
            $image->save($genFile);
        }
        header('Content-Type:'.$this->meta->mimetype);
        header('Content-Length: ' . filesize($genFile));
        return readfile($genFile);
    }
}
