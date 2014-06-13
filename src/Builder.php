<?php
namespace stratease\ImageBuilder;
use Intervention\Image\ImageManagerStatic;
use Intervention\Image\Image;
use stratease\ImageBuilder\Util\ConfigurableObject;
use stratease\ImageBuilder\Filter\Resize;
use stratease\ImageBuilder\Util\File;
use stratease\ImageBuilder\Filter\FilterInterface;
use Symfony\Component\Finder\Tests\Expression\RegexTest;

class Builder extends ConfigurableObject
{
    /**
     * @var null
     */
    protected $baseWidth = null;
    /**
     * @var RequestParserInterface
     */
    protected $requestParser = null;
    /**
     * @var null
     */
    protected $baseHeight = null;
    /**
     * @var null
     */
    protected $baseImage = null;

    /**
     * @var array
     */
    protected $baseDirectory = [];
    /**
     * @var bool
     */
    protected $cache = true;
    /**
     * @var array
     */
    protected $filters = [];
    /**
     * @var null|\stdClass
     */
    protected $imageFile = null;
    /**
     * @var string sys temp dir
     */
    protected $cacheDirectory = null;
    /**
     * @var int Seconds to live
     */
    protected $cacheDuration = 3600;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        // default to systems tmp dir
        $this->cacheDirectory = sys_get_temp_dir();
        parent::__construct($options);
    }

    /**
     * @param string|array $dir
     */
    public function setBaseDirectory($dir)
    {
        // should be array
        if(is_string($dir)) {
            $dir = [$dir];
        }
        $this->baseDirectory = $dir;
    }

    /**
     * @param RequestParserInterface $parser
     * @return $this
     */
    public function setRequestParser(RequestParserInterface $parser)
    {
        $this->requestParser = $parser;

        return $this;
    }
    /**
     * @param $img
     * @return $this
     * @throws \Exception
     */
    public function setBaseImage($img)
    {

        $this->baseImage = $img;

        return $this;
    }

    /**
     * @param bool $bool
     * @param null $dir
     * @return $this
     */
    public function setCache($bool = true, $dir = null)
    {
        $this->cache = (bool)$bool;
        if($dir !== null) {
            $this->setCacheDirectory($dir);
        }
        return $this;
    }

    /**
     * @param $dir
     * @return $this
     */
    public function setCacheDirectory($dir)
    {
        $this->cacheDirectory = $dir;

        return $this;
    }

    /**
     * Queues filters to be run on output
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
    protected function percentResize($image, $percent)
    {
        $percent = $percent * .01;
        // find the percent of the orig values..
        $width = (imagesx($image->resource) * $percent);
        $height = (imagesy($image->resource) * $percent);

        return $image->resize($width, $height, true);
    }

    /**
     * @param Image $canvas  The modifiable canvas
     * @param Image $baseImage The original image object
     * @param FilterInterface $filter the filter
     * @param array $args arguments, passed down to the filters filter method
     * @return Image canvas after filter has been applied
     * @throws \Exception
     */
    public function applyFilter(Image $canvas, Image $baseImage, FilterInterface $filter, array $args = [])
    {

        $filter = clone $filter;
        // setup filter object...
        $filter->setCanvas($canvas);
        $filter->setBaseImage($baseImage);

        $callable = [$filter, 'filter'];
        // run filter
        if(is_callable($callable)) {

            return call_user_func_array($callable, $args);
        } else {

            throw new \Exception("Filter '".get_class($filter)."' must define a 'filter' method to accept filter arguments.");
        }
    }

    /**
     * @param string $filterName
     * @param FilterInterface $object
     * @return $this
     */
    public function addFilterExtension($filterName, FilterInterface $object)
    {
        $this->filterExtensions[$filterName] = $object;

        return $this;
    }

    /**
     * @param $filterName
     * @return FilterInterface
     */
    public function getFilterExtension($filterName)
    {
        return isset($this->filterExtensions[$filterName]) ? $this->filterExtensions[$filterName] : null;
    }

    /**
     * @param $filterName
     * @return FilterInterface
     * @throws \ErrorException
     */
    public function getFilter($filterName)
    {
        // special size setter filter
        // @todo clean Size filter up... should hook into main filter loader.
        // @todo ..It has a unique filter mask, so we need to update the mask system to build appropriately
        if(preg_match(Resize::getFilterMask(), $filterName, $matches)) {

            return new Resize();
        }
        // extension ?
        if($filterObj = $this->getFilterExtension($filterName)) {

            return $filterObj;
        } else {
            $class = __NAMESPACE__.'\Filter\\'.ucwords($filterName);

            if(class_exists($class)) {

                return new $class();
            }
            else {

                throw new \ErrorException("Unable to locate the filter '".$filterName."'", E_USER_ERROR);
            }

        }
    }

    /**
     * @param Image $canvas
     * @param Image $baseImage
     * @param $filters array
     * @return Image
     */
    public function applyFilters(Image $canvas, Image $baseImage, $filters)
    {
        $baseImageWritten = false;
        foreach($filters as $filter) {
            // build filter object...
            if($filterObj = $this->getFilter($filter['filter'])) {
                // build args off parsed filter
                $fArgs = $filterObj->buildArgs($filter);
                // write filter to our current canvas
                $canvas = $this->applyFilter($canvas, $baseImage, $filterObj, $fArgs);
                // check filter flags
                if(!$baseImageWritten) {
                    $baseImageWritten = $filterObj->writesBaseImageToCanvas();
                }
            }
        }

        // if no filters wrote the base image on the canvas, we presume that should always be done.. do it now.
        if($baseImageWritten === false) {
            $canvas->insert($baseImage, 0, 0); // should we center?
        }
        return $canvas;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getBaseFile()
    {
        $filePath = null;
        if(!($baseImage = $this->baseImage)) {
            if($this->requestParser == null) {
                throw new \Exception("No base image defined!", E_USER_ERROR);
            } else {
                $baseImage = $this->requestParser->getRequestedImage();
            }
        }
        foreach($this->baseDirectory as $dir) {
            if(is_file($dir.'/'.$baseImage)) {
                $filePath = realpath($dir.'/'.$baseImage);
                break;
            }
        }

        // find our file?
        if($filePath === null) {
            throw new \Exception('Could not locate image \''.$baseImage.'\'. Looked in '.implode(", ", $this->baseDirectory), E_USER_ERROR);
        }

        return $filePath;
    }

    /**
     * @return $this
     */
    public function compile()
    {
        $filePath = $this->getBaseFile();
        // get file meta data...
        $imageFile = new File($filePath);

        // get our main image resource...
        $image = ImageManagerStatic::make($imageFile->getPath());
        // start with base image size
        $canvas = ImageManagerStatic::canvas($imageFile->getWidth(), $imageFile->getHeight());

        // apply the filters
        $image = $this->applyFilters($canvas, $image, $this->getFilters());

        // now save the image
        $genFile = $this->generateFileName();
        $image->save($genFile);

        return $this;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        if(count($this->filters)) {
            $filters = $this->filters;
        } else {
            $filters = [];
        }

        if($this->requestParser != null) {
            // request parser? merge filters...
            if($rFilters = $this->requestParser->getRequestedFilters()) {

                return array_merge($filters, $rFilters);
            }
        }

        return $filters;
    }

    /**
     * @return string
     */
    protected function generateFileName()
    {
        $filePath = $this->getBaseFile();
        // file extension..
        $ext = substr($filePath, strrpos($filePath, ".") + 1);
        // should be unique id for this file + filters
        $imgId = sha1($filePath.json_encode($this->getFilters()));
        // build generated file
        $genFile = realpath($this->cacheDirectory).'/'.$imgId.'.'.$ext;

        return $genFile;
    }

    /**
     * @return $this
     */
    public function output()
    {
        $genFile = $this->generateFileName();

        // are we using cache?
        if($this->cache === false // not cacheing ?
            || file_exists($genFile) === false // file doesn't exist ?
            // or we are cacheing, but need to check expiration...
        || ($this->cache === true
                && ((filemtime($genFile) + $this->cacheDuration) < time()))) {
            $this->compile();
        }

        $filePath = $this->getBaseFile();
        // get file meta data...
        $imageFile = new File($filePath);

        // output stuff..
        header('Content-Type:'.$imageFile->getMimeType());
        header('Content-Length: ' . filesize($genFile));
        readfile($genFile);

        return $this;
    }
}
