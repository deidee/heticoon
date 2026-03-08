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
    const COLOR_WHITE = '#ffffff';

    private $im;
    private $cols = 16;
    private $rows = 16;
    private $x = 0;
    private $y = 0;
    private $height; // Resolved/final canvas height.
    private $width;  // Resolved/final canvas width.
    private $size = 24;
    private $type = 'png';
    private $offset = 0;
    private $padding = 0;
    private $blocks = 0;
    private $dataSet = [];

    private $palette = [];

    /**
     * If true, width/height are leading and size is calculated automatically.
     */
    private $autoSize = false;

    /**
     * Track whether width/height were explicitly set by the caller.
     * If not, they are derived from grid settings and size.
     */
    private $widthExplicit = false;
    private $heightExplicit = false;

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

    public function __construct($settings = [])
    {
        if (!empty($settings)) {
            foreach ($settings as $key => $value) {
                if ($key === '' || $key === null) {
                    continue;
                }

                $method = 'set' . ucfirst($key);

                if (method_exists($this, $method)) {
                    $this->$method($value);
                } else {
                    $this->$key = $value;
                }
            }
        }

        $this->resolveDimensions();
    }

    public function __set($name, $value)
    {
        $method = 'set' . ucfirst($name);

        if (method_exists($this, $method)) {
            $this->$method($value); // fixed: pass $value, not $name
        } else {
            $this->$name = $value;
            $this->resolveDimensions();
        }
    }

    public function __toString()
    {
        $this->draw();

        header('Content-Type: ' . $this->im->getImageMimeType());

        return $this->im->getImageBlob();
    }

    /**
     * Total grid units needed horizontally.
     *
     * Original drawing logic effectively uses:
     * - left margin: offset + padding
     * - content: cols
     * - right margin: padding
     *
     * So total units = cols + offset + 2*padding
     */
    private function getTotalWidthUnits()
    {
        return $this->cols + $this->offset + ($this->padding * 2);
    }

    /**
     * Total grid units needed vertically.
     */
    private function getTotalHeightUnits()
    {
        return $this->rows + $this->offset + ($this->padding * 2);
    }

    /**
     * Resolve actual size/width/height from the current settings.
     *
     * Behaviour:
     * - autoSize = false:
     *   size leads, width/height are derived unless explicitly set
     * - autoSize = true:
     *   explicit width and/or height lead, size is calculated to fit
     */
    private function resolveDimensions()
    {
        $totalWidthUnits = $this->getTotalWidthUnits();
        $totalHeightUnits = $this->getTotalHeightUnits();

        if ($this->autoSize) {
            $candidateSizes = [];

            if ($this->widthExplicit && !empty($this->width) && $totalWidthUnits > 0) {
                $candidateSizes[] = (int) floor($this->width / $totalWidthUnits);
            }

            if ($this->heightExplicit && !empty($this->height) && $totalHeightUnits > 0) {
                $candidateSizes[] = (int) floor($this->height / $totalHeightUnits);
            }

            if (!empty($candidateSizes)) {
                $this->size = max(1, min($candidateSizes));
            }
        }

        if (!$this->widthExplicit || empty($this->width)) {
            $this->width = $totalWidthUnits * $this->size;
        }

        if (!$this->heightExplicit || empty($this->height)) {
            $this->height = $totalHeightUnits * $this->size;
        }
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
        if (mt_rand(1, 10) > 2) {
            $r = mt_rand(0, 63);
            $g = mt_rand(63, 127);
            $b = mt_rand(0, 63);
        } else {
            $r = mt_rand(127, 255);
            $g = mt_rand(0, 63);
            $b = 0;
        }

        return "rgb($r, $g, $b)";
    }

    /**
     * @throws \ImagickException
     * @throws \ImagickDrawException
     * @throws \ImagickPixelException
     */
    public function draw()
    {
        $this->resolveDimensions();

        $this->blocks = $this->rows * $this->cols;
        $this->dataSet = $this->data;
        $this->palette = [];

        // Fill the palette with colors.
        $this->populate();

        // This is where the magick happens.
        $this->im = new Imagick();
        $this->im->newImage($this->width, $this->height, new ImagickPixel(self::COLOR_WHITE));
        $this->im->setImageFormat($this->type);

        $draw = new ImagickDraw();
        $draw->setViewbox(0, 0, $this->width, $this->height);
        $draw->setStrokeWidth(0);
        $i = 0;

        for ($row = 0; $row < $this->rows; $row++) {
            for ($col = 0; $col < $this->cols; $col++) {
                $x1 = (($col + $this->offset + $this->padding) * $this->size) + $this->x;
                $x2 = $x1 + $this->size + mt_rand(-1, 1);
                $y1 = (($row + $this->offset + $this->padding) * $this->size) + $this->y;
                $y2 = $y1 + $this->size + mt_rand(-1, 1);
                $color = !empty($this->palette[$i]) ? $this->palette[$i] : self::COLOR_WHITE;

                // Important:
                // this works both for dense 16x16 arrays and sparse GET arrays.
                if (!empty($this->data[$row][$col])) {
                    $draw->setFillColor(new ImagickPixel($color));
                    $draw->setFillOpacity(.5);
                    $draw->rectangle($x1, $y1, $x2, $y2);
                }

                $i++;
            }
        }

        $this->im->drawImage($draw);
    }

    public function getBlockCount()
    {
        return $this->blocks;
    }

    public function getDataURI()
    {
        $this->draw();

        return 'data:' . $this->im->getImageMimeType() . ';base64,' . base64_encode($this->im->getImageBlob());
    }

    public function populate()
    {
        $dedate = new Dedate\Dedate;

        if ($dedate->isChristmas()) {
            for ($i = 0; $i < $this->blocks; $i++) {
                $this->palette[] = $this->xmas();
            }
        } else {
            for ($i = 0; $i < $this->blocks; $i++) {
                $this->palette[] = $this->deJade();
            }
        }

        // Pink for October.
        if (idate('m') === 10) {
            $this->palette[0] = 'rgb(255, 68, 136)';
            shuffle($this->palette);
        }
    }

    public function save()
    {
        $this->draw();

        $dir = '../dist/images/';
        $filename = 'image-' . time() . '.' . strtolower($this->im->getImageFormat());
        $target = $dir . $filename;

        file_put_contents($target, $this->im->getImageBlob());
    }

    public function setColumnCount($cols = 16)
    {
        $this->cols = (int) $cols;
        $this->resolveDimensions();
    }

    public function setOffset($offset = 1)
    {
        $this->offset = (int) $offset;
        $this->resolveDimensions();
    }

    public function setPadding($padding = 1)
    {
        $this->padding = (int) $padding;
        $this->resolveDimensions();
    }

    public function setRowCount($rows = 16)
    {
        $this->rows = (int) $rows;
        $this->resolveDimensions();
    }

    public function setX($x = 0)
    {
        $this->x = (int) $x;
        $this->resolveDimensions();
    }

    public function setY($y = 0)
    {
        $this->y = (int) $y;
        $this->resolveDimensions();
    }

    public function setWidth($width = null)
    {
        if ($width === null || $width === '' || (int)$width <= 0) {
            $this->width = null;
            $this->widthExplicit = false;
        } else {
            $this->width = (int) $width;
            $this->widthExplicit = true;
        }

        $this->resolveDimensions();
    }

    public function setHeight($height = null)
    {
        if ($height === null || $height === '' || (int)$height <= 0) {
            $this->height = null;
            $this->heightExplicit = false;
        } else {
            $this->height = (int) $height;
            $this->heightExplicit = true;
        }

        $this->resolveDimensions();
    }

    public function setSize($size = 24)
    {
        $this->size = max(1, (int) $size);
        $this->resolveDimensions();
    }

    public function setType($type = 'png')
    {
        $this->type = (string) $type;
    }

    public function setAutoSize($autoSize = true)
    {
        $this->autoSize = (bool) $autoSize;
        $this->resolveDimensions();
    }

    public function setData($data = [])
    {
        // Important: keep sparse GET arrays as-is.
        // Do NOT infer row/column count from them.
        $this->data = is_array($data) ? $data : [];
    }
}