<?php

class Deicon
{
    private $cols;
    private $rows;
    private $height = 384;
    private $width = 384;
    private $size = 24;
    private $type = 'png';

    private $palette = [];

    private $data = <<<STR
0000001000000000
0000001000000000
0000111011100000
0000101010100000
0000101011100000
0000101010000000
0000111011100000
0000000000000000
1110001000000000
0100001000000000
0100111011101110
0100101010101010
0100101011101110
0100101010001000
1110111011101110
0000000000000000
STR;

    public function __construct($settings = []) {

        if(!empty($settings)):
            foreach($settings as $key => $value):
                if(!empty($key)) $this->$key = $value;
            endforeach;
        endif;

        $this->array = preg_split('/\n|\r\n?/', $this->data);
        $this->rows = $this->cols = count($this->array);
        $this->width = $this->height = $this->cols * $this->size;
        $this->blocks = pow($this->rows, 2);
        $this->dataSet = [];

        foreach($this->array as $key => $value) {
            $this->dataSet[] = str_split($value);
        }

        // Fill the palette with colors.
        $this->populate();

        // This is where the magick happens.
        $this->im = new Imagick();
        $this->im->newImage($this->width, $this->height, new ImagickPixel('#ffffff'));
        $this->im->setImageFormat($this->type);

        $this->data = str_replace(["\r", "\n"], '', $this->data);

        $this->draw();
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
        header('Content-Type: ' . $this->im->getImageMimeType());

        return $this->im->getImageBlob();
    }

    public function deJade() {
        $r = mt_rand(0, 127);
        $g = mt_rand(127, 255);
        $b = mt_rand(0, 191);

        $color = "rgb($r, $g, $b)";

        return $color;
    }

    public function draw() {
        $draw = new ImagickDraw();
        $draw->setViewbox(0, 0, $this->width, $this->height);
        $draw->setStrokeWidth(0);
        $i = 0;

        for($row = 0; $row < $this->rows; $row++) {
            for($col = 0; $col < $this->cols; $col++) {
                $x1 = $col * $this->size;
                $x2 = $x1 + $this->size - 1;
                //if($this->size > 3) $x2 += mt_rand(-1, 1);
                $y1 = $row * $this->size;
                $y2 = $y1 + $this->size - 1;
                //if($this->size > 3) $y2 += mt_rand(-1, 1);
                $color = $this->palette[$i];

                if(!empty($this->data{$i}) && $this->data{$i} === '1') {
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
        $dir = '../dist/images/';
        $filename = 'image-' . time() . '.' . $this->im->getImageFormat();
        $target = $dir . $filename;

        file_put_contents($target, $this->im->getImageBlob());
    }

    public function setInput() {
        echo 'yes.';
    }
}
