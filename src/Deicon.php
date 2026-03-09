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
    private $blob = '';
    private $mimeType = 'image/png';

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
            $this->$method($value);
        } else {
            $this->$name = $value;
            $this->resolveDimensions();
        }
    }

    public function __toString()
    {
        try {
            $this->draw();

            if (!headers_sent()) {
                header('Content-Type: ' . $this->mimeType);
            }

            return (string) $this->blob;
        } catch (\Throwable $e) {
            return '';
        }
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

    private function normalizeType($type): string
    {
        $type = strtolower(trim((string) $type));
        $type = ltrim($type, '.');

        $map = [
            'jpg' => 'jpeg',
            'image/jpeg' => 'jpeg',
            'image/jpg' => 'jpeg',
            'png' => 'png',
            'image/png' => 'png',
            'svg' => 'svg',
            'image/svg+xml' => 'svg',
        ];

        return $map[$type] ?? $type;
    }

    private function isSvgType(): bool
    {
        return $this->type === 'svg';
    }

    private function getMimeTypeForType(): string
    {
        switch ($this->type) {
            case 'jpeg':
                return 'image/jpeg';
            case 'svg':
                return 'image/svg+xml';
            case 'png':
            default:
                return 'image/png';
        }
    }

    private function getFileExtension(): string
    {
        switch ($this->type) {
            case 'jpeg':
                return 'jpg';
            case 'svg':
                return 'svg';
            case 'png':
            default:
                return 'png';
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
     * Build a flat list of drawable rectangles.
     * This lets us render either raster or SVG from the same geometry.
     */
    private function buildRectangles(): array
    {
        $rectangles = [];
        $i = 0;

        for ($row = 0; $row < $this->rows; $row++) {
            for ($col = 0; $col < $this->cols; $col++) {
                $x = (($col + $this->offset + $this->padding) * $this->size) + $this->x;
                $y = (($row + $this->offset + $this->padding) * $this->size) + $this->y;
                $w = max(1, $this->size + mt_rand(-1, 1));
                $h = max(1, $this->size + mt_rand(-1, 1));
                $color = !empty($this->palette[$i]) ? $this->palette[$i] : self::COLOR_WHITE;

                // Works for both dense and sparse arrays.
                if (!empty($this->data[$row][$col])) {
                    $rectangles[] = [
                        'x' => $x,
                        'y' => $y,
                        'width' => $w,
                        'height' => $h,
                        'color' => $color,
                        'opacity' => 0.5,
                    ];
                }

                $i++;
            }
        }

        return $rectangles;
    }

    private function renderRaster(array $rectangles): void
    {
        $this->im = new Imagick();
        $this->im->newImage($this->width, $this->height, new ImagickPixel(self::COLOR_WHITE));
        $this->im->setImageFormat($this->type);

        $draw = new ImagickDraw();
        $draw->setViewbox(0, 0, $this->width, $this->height);
        $draw->setStrokeWidth(0);

        foreach ($rectangles as $rect) {
            $draw->setFillColor(new ImagickPixel($rect['color']));
            $draw->setFillOpacity($rect['opacity']);
            $draw->rectangle(
                $rect['x'],
                $rect['y'],
                $rect['x'] + $rect['width'],
                $rect['y'] + $rect['height']
            );
        }

        $this->im->drawImage($draw);
        $this->mimeType = $this->im->getImageMimeType();
        $this->blob = $this->im->getImageBlob();
    }

    private function renderSvg(array $rectangles): void
    {
        $svg = [];
        $svg[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $svg[] = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="' . (int)$this->width . '" height="' . (int)$this->height . '" viewBox="0 0 ' . (int)$this->width . ' ' . (int)$this->height . '">';
        $svg[] = '  <rect x="0" y="0" width="' . (int)$this->width . '" height="' . (int)$this->height . '" fill="' . htmlspecialchars(self::COLOR_WHITE, ENT_QUOTES, 'UTF-8') . '" />';

        foreach ($rectangles as $rect) {
            $svg[] = '  <rect'
                . ' x="' . (int)$rect['x'] . '"'
                . ' y="' . (int)$rect['y'] . '"'
                . ' width="' . (int)$rect['width'] . '"'
                . ' height="' . (int)$rect['height'] . '"'
                . ' fill="' . htmlspecialchars((string)$rect['color'], ENT_QUOTES, 'UTF-8') . '"'
                . ' fill-opacity="' . rtrim(rtrim(number_format((float)$rect['opacity'], 3, '.', ''), '0'), '.') . '"'
                . ' />';
        }

        $svg[] = '</svg>';

        $this->im = null;
        $this->mimeType = 'image/svg+xml';
        $this->blob = implode("\n", $svg);
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
        $this->blob = '';
        $this->mimeType = $this->getMimeTypeForType();

        // Fill the palette with colors.
        $this->populate();

        $rectangles = $this->buildRectangles();

        if ($this->isSvgType()) {
            $this->renderSvg($rectangles);
            return;
        }

        $this->renderRaster($rectangles);
    }

    public function getBlockCount()
    {
        return $this->blocks;
    }

    public function getDataURI()
    {
        $this->draw();

        return 'data:' . $this->mimeType . ';base64,' . base64_encode($this->blob);
    }

    public function getSvg(): string
    {
        $previousType = $this->type;
        $this->type = 'svg';
        $this->draw();
        $svg = (string) $this->blob;
        $this->type = $previousType;

        return $svg;
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
        $filename = 'image-' . time() . '.' . $this->getFileExtension();
        $target = $dir . $filename;

        file_put_contents($target, $this->blob);
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
        $this->type = $this->normalizeType($type);
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
