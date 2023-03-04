<?php

declare(strict_types=1);

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$scaler = !empty($_POST['size']) ? intval($_POST['size']) : 8;
$offset = !empty($_POST['offset']) ? intval($_POST['offset']) : 1;
$x = !empty($_POST['x']) ? intval($_POST['x']) : 0;
$y = !empty($_POST['y']) ? intval($_POST['y']) : 0;
$w = !empty($_POST['width']) ? intval($_POST['width']) : '';
$h = !empty($_POST['height']) ? intval($_POST['height']) : '';

use deidee\heticoon\Deicon;

?>
<!doctype html>
<html dir="ltr" lang="nl">
<head>
    <meta charset="utf-8">
    <title>Image to image</title>
    <link rel="stylesheet" href="https://default.style/">
    <link rel="stylesheet" href="https://hetcdn.nl/deidee/css/deflex.min.css">
</head>
<body id="top">
<form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" enctype="multipart/form-data">
    <div><input name="source" type="file"></div>
    <div><label>Size: <input name="size" type="number" min="1" max="128" value="<?= $scaler ?>"></label></div>
    <div><label>X: <input name="x" type="number" min="0" max="128" value="<?= $x ?>"></label></div>
    <div><label>Y: <input name="y" type="number" min="0" max="128" value="<?= $y ?>"></label></div>
    <div><label>Width: <input name="width" type="number" min="0" max="4096" value="<?= $w ?>"></label></div>
    <div><label>Height: <input name="height" type="number" min="0" max="4096" value="<?= $h ?>"></label></div>
    <div><button type="submit">Doe</button></div>
</form>
<?php

if(strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {

    if(is_uploaded_file($_FILES['source']['tmp_name'])) {
        echo '<div>' . htmlspecialchars($_FILES['source']['name']) . ':</div>';

        $img = new Imagick();
        $file = fopen($_FILES['source']['tmp_name'], 'rb');
        $img->readImageFile($file);
        $height = $img->getImageHeight();
        $width = $img->getImageWidth();
        $ita = $img->getPixelIterator();
        $data = [];
        $l = 0;

        foreach ($ita as $row => $pixels) { /* Loop through pixel rows */
            $data[$l] = [];

            foreach ($pixels as $column => $pixel) { /* Loop through the pixels in the row (columns) */
                /** @var $pixel \ImagickPixel */
                $c = $pixel->getColor();
                // Check if white and/or transparent.
                if(empty($c['a']) OR ($c['r'] === 255 && $c['g'] === 255) && $c['b'] === 255) {
                    $data[$l][] = 0;
                } else {
                    $data[$l][] = 1;
                }
            }
            $l++;
            $ita->syncIterator(); /* Sync the iterator, this is important to do on each iteration */
        }

        $ico = new Deicon([
            'height' => $h ?? $height * $scaler, 'width' => $w ?? $width * $scaler,
            'x' => $x, 'y' => $y,
            'size' => $scaler, 'type' => 'png', 'data' => $data]);

        $src = $ico->getDataURI();

        echo '<img alt="" src="' . $src . '">';

        //echo '<pre>';
        //echo var_dump($data);
        //echo '</pre>';
    } else {
        echo '<p>Geen bronbestand gevonden.</p>';
    }
}


?>
</body>
</html>