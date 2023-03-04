<?php
declare(strict_types=1);

namespace deidee\heticoon;

//require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use Imagick;
use ImagickDraw;
use ImagickPixel;
use deidee\Dedate;

class Deicon
{
    private $im;
    private $cols = 16;
    private $rows = 16;
    private $x = 0;
    private $y = 0;
    private $height = 384;
    private $width = 384;
    private $size = 24;
    private $type = 'png';
    private $offset = 0;
    private $padding = 0;
    private $blocks = 0;
    private $dataSet = [];

    private $palette = [];

    private $data = [
        [0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0],
        [0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0],
        [0,0,0,0,1,1,1,0,1,1,1,0,0,0,0,0],
        [0,0,0,0,1,0,1,0,1,0,1,0,0,0,0,0],
        [0,0,0,0,1,0,1,0,1,1,1,0,0,0,0,0],
        [0,0,0,0,1,0,1,0,1,0,0,0,0,0,0,0],
        [0,0,0,0,1,1,1,0,1,1,1,0,0,0,0,0],
        [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
        [1,1,1,0,0,0,1,0,0,0,0,0,0,0,0,0],
        [0,1,0,0,0,0,1,0,0,0,0,0,0,0,0,0],
        [0,1,0,0,1,1,1,0,1,1,1,0,1,1,1,0],
        [0,1,0,0,1,0,1,0,1,0,1,0,1,0,1,0],
        [0,1,0,0,1,0,1,0,1,1,1,0,1,1,1,0],
        [0,1,0,0,1,0,1,0,1,0,0,0,1,0,0,0],
        [1,1,1,0,1,1,1,0,1,1,1,0,1,1,1,0],
        [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]
    ];

    public function __construct($settings = []) {

        if(!empty($settings)):
            foreach($settings as $key => $value):
                if(!empty($key)) $this->$key = $value;
            endforeach;
        endif;

        if(empty($this->height)) $this->height = $this->rows * $this->size;
        if(empty($this->width)) $this->width = $this->cols * $this->size;
        $this->blocks = $this->rows * $this->cols;
        $this->dataSet = $this->data;

        // Fill the palette with colors.
        $this->populate();
    }

    public function __set($name, $value)
    {
        $method = 'set' . ucfirst($name);

        if(method_exists($this, $method)) {
            $this->$method($name);
        } else {
            $this->$name = $value;
        }
    }

    public function __toString() {
        $this->draw();

        header('Content-Type: ' . $this->im->getImageMimeType());

        return $this->im->getImageBlob();
    }

    public function deJade(): string
    {
        $r = mt_rand(0, 127);
        $g = mt_rand(127, 255);
        $b = mt_rand(0, 191);

        return "rgb($r, $g, $b)";
    }

    public function xmas(): string
    {
        if(mt_rand(1, 10) > 2)
        {
            $r = mt_rand(0, 63);
            $g = mt_rand(63, 127);
            $b = mt_rand(0, 63);
        }
        else
        {
            $r = mt_rand(127, 255);
            $g = mt_rand(0, 63);
            $b = mt_rand(0, 0);
        }

        return "rgb($r, $g, $b)";
    }

    public function draw() {
        // This is where the magick happens.
        $this->im = new Imagick();
        $this->im->newImage($this->width, $this->height, new ImagickPixel('#ffffff'));
        $this->im->setImageFormat($this->type);

        $draw = new ImagickDraw();
        $draw->setViewbox(0, 0, $this->width, $this->height);
        $draw->setStrokeWidth(0);
        $i = 0;

        for($row = 0; $row < $this->rows; $row++) {
            for($col = 0; $col < $this->cols; $col++) {
                $x1 = (($col + $this->offset + $this->padding) * $this->size) + $this->x;
                $x2 = $x1 + $this->size + mt_rand(-1, 1);
                //if($this->size > 3) $x2 += mt_rand(-1, 1);
                $y1 = (($row + $this->offset + $this->padding) * $this->size) + $this->y;
                $y2 = $y1 + $this->size + mt_rand(-1, 1);
                //if($this->size > 3) $y2 += mt_rand(-1, 1);
                $color = $this->palette[$i];

                if(!empty($this->data[$row][$col])) {
                    $draw->setFillColor(new ImagickPixel($color));
                    $draw->setFillOpacity(.5);
                    $draw->rectangle($x1, $y1, $x2, $y2);
                }

                $i++;
            }
        }

        $this->im->drawImage($draw);
    }

    public function getBlockCount() {
        return $this->blocks;
    }

    public function getDataURI() {
        $this->draw();

        return 'data:' . $this->im->getImageMimeType() . ';base64,' . base64_encode($this->im->getImageBlob());
    }

    public function populate() {
        $dedate = new Dedate\Dedate;

        if($dedate->isChristmas()) {
            for($i = 0; $i < $this->blocks; $i ++) {
                $this->palette[] = $this->xmas();
            }
        } else {
            for($i = 0; $i < $this->blocks; $i ++) {
                $this->palette[] = $this->deJade();
            }
        }

        // Pink for October.
        if(idate('m') === 10) {
            $this->palette[0] = 'rgb(255, 68, 136)';

            shuffle($this->palette);
        }
    }

    public function save() {
        $this->draw();

        $dir = '../dist/images/';
        $filename = 'image-' . time() . '.' . strtolower($this->im->getImageFormat());
        $target = $dir . $filename;

        file_put_contents($target, $this->im->getImageBlob());
    }

    public function setOffset($offset = 1) {
        $this->offset = $offset;
        $this->width += $this->offset * $this->size;
        $this->height += $this->offset * $this->size;
    }

    public function setPadding($padding = 1) {
        $this->padding = $padding;
        $this->width += $this->padding * 2 * $this->size;
        $this->height += $this->padding * 2 * $this->size;
    }
}
