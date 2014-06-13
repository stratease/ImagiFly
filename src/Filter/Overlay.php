<?php
namespace stratease\ImagiFly\Filter;
use Intervention\Image\Image;
use stratease\ImagiFly\Filter\BaseFilter;
class Overlay extends BaseFilter
{
    /**
     * @param $cnt
     * @param string $style
     * @return Image - The modified canvas object;
     */
    public function filter( $cnt, $style = 'triangle')
    {

        $height = $this->canvas->height();
        $width = $this->canvas->width();
        $percBackground = .0;
        if($style == 'left'
            || $style == 'right') {
            if($cnt == 2) {
                $img = clone $this->baseImage;
                $foreGroundImg = clone $this->baseImage;
                // find out our box size aspect ratio...
                if($width < $height) {
                    $h = (int)($height / 2);
                    $xBox1 = (int) ($width - ($width * $percBackground));
                    $yBox1 = (int) ($h - ($h * $percBackground));
                    $xBox2 = (int) $width;
                    $yBox2 = (int) $h;
                } else {
                    $w = (int)($width / 2);
                    $xBox1 = (int) ($w - ($w * $percBackground));
                    $yBox1 = (int) ($height - ($height * $percBackground));
                    $xBox2 = (int) $w;
                    $yBox2 = (int) $height;
                }
                // resize
                $img->resize($xBox1, $yBox1, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $foreGroundImg->resize($xBox2, $yBox2,function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                // shift for centering
                $x2 = (int)(($width / 2) - ($foreGroundImg->width() / 2));
                $y2 = (int)(($height - $foreGroundImg->height()) / 2);
                $halfW = (int)($width / 2);
                $fourthW = (int)($width / 4);
                $x = (int)($fourthW - ($img->width() / 2));
                $y = (int)(($height - $img->height()) / 2);
                // place background images
                if($style == 'left') {
                    $this->canvas->insert($img, null, $x, $y);
                } else {
                    $this->canvas->insert($img, $x + $halfW, $y);
                }
                // place foreground
                $this->canvas->insert($foreGroundImg, $x2, $y2);
            }
        } else if($style == 'triangle') {
            switch($cnt) {
                case 3:
                    $img = clone $this->baseImage;
                    $foreGroundImg = clone $this->baseImage;
                    // find out our box size aspect ratio...
                    if($width < $height) {
                        $h = (int)($height / 2);
                        $xBox1 = (int) ($width - ($width * $percBackground));
                        $yBox1 = (int) ($h - ($h * $percBackground));
                    } else {
                        $w = (int)($width / 2);
                        $xBox1 = (int) ($w - ($w * $percBackground));
                        $yBox1 = (int) ($height - ($height * $percBackground));
                    }

                    // resize
                    $img->resize($xBox1, $yBox1, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });

                    // shift for centering
                    $x2 = (int)(($width / 2) - ($foreGroundImg->width() / 2));
                    $y2 = (int)(($height - $foreGroundImg->height()) / 2);
                    $halfW = (int)($width / 2);
                    $fourthW = (int)($width / 4);
                    $x = (int)($fourthW - ($img->width() / 2));
                    $y = (int)(($height - $img->height()) / 2);
                    // place background images
                    $this->canvas->insert($img, null, $x, $y);
                    $this->canvas->insert($img, null, $x + $halfW, $y);
                    // place foreground
                    $this->canvas->insert($foreGroundImg, null, $x2, $y2);
                    break;
                case 2:
                    $img = clone $this->baseImage;

                    // find out our box size aspect ratio...
                    if($width < $height) {
                        $h = (int)($height / 2);
                        $xBox1 = (int) ($width - ($width * $percBackground));
                        $yBox1 = (int) ($h - ($h * $percBackground));
                    } else {
                        $w = (int)($width / 2);
                        $xBox1 = (int) ($w - ($w * $percBackground));
                        $yBox1 = (int) ($height - ($height * $percBackground));
                    }
                    // resize
                    $img->resize($xBox1, $yBox1, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    // shift for centering
                    $halfW = (int)($width / 2);
                    $fourthW = (int)($width / 4);
                    $x = (int)($fourthW - ($img->width() / 2));
                    $y = (int)(($height - $img->height()) / 2);
                    // place background images
                    $this->canvas->insert($img, null, $x, $y);
                    $this->canvas->insert($img, null, $x + $halfW, $y);
                    break;
                case 1:
                default:
                    return $this->baseImage;
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
                // $this->canvas->insert($img, $n * 10, $n * 10);
            }
        }*/

        return $this->canvas;
    }

    public static function getFilterMask()
    {
        return 'overlay';
    }
}
