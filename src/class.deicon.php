<?php
declare(strict_types=1);

namespace deidee;

use Imagick;
use ImagickDraw;
use ImagickPixel;

class Deicon
{
    private $im;
    private $cols = 16;
    private $rows = 16;
    private $height = 384;
    private $width = 384;
    private $size = 24;
    private $type = 'png';
    private $offset = 0;
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

        $this->height = $this->rows * $this->size;
        $this->width = $this->cols * $this->size;
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

    public function deJade() {
        $r = mt_rand(0, 127);
        $g = mt_rand(127, 255);
        $b = mt_rand(0, 191);

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
                $x1 = ($col + $this->offset) * $this->size;
                $x2 = $x1 + $this->size - 1;
                //if($this->size > 3) $x2 += mt_rand(-1, 1);
                $y1 = ($row + $this->offset) * $this->size;
                $y2 = $y1 + $this->size - 1;
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

    public function populate() {
        for($i = 0; $i < $this->blocks; $i ++) {
            $this->palette[] = $this->deJade();
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
}
