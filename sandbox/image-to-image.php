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
    <div><button type="submit">Doe</button></div>
</form>
<?php

if(strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {

    if(is_uploaded_file($_FILES['source']['tmp_name'])) {
        echo '<div>' . htmlspecialchars($_FILES['source']['name']) . ':</div>';

        $img = new Imagick();
        $file = fopen($_FILES['source']['tmp_name'], 'rb');
        $img->readImageFile($file);
        $ita = $img->getPixelIterator();
        $data = [];
        $y = 0;

        foreach ($ita as $row => $pixels) { /* Loop through pixel rows */
            $data[$y] = [];

            foreach ($pixels as $column => $pixel) { /* Loop through the pixels in the row (columns) */
                /** @var $pixel \ImagickPixel */
                $c = $pixel->getColor();
                // Check if white and/or transparent.
                if(empty($c['a']) OR ($c['r'] === 255 && $c['g'] === 255) && $c['b'] === 255) {
                    $data[$y][] = 0;
                } else {
                    $data[$y][] = 1;
                }
            }
            $y++;
            $ita->syncIterator(); /* Sync the iterator, this is important to do on each iteration */
        }

        echo '<pre>';
        echo var_dump($data);
        echo '</pre>';
    } else {
        echo '<p>Geen bronbestand gevonden.</p>';
    }
}


?>
</body>
</html>