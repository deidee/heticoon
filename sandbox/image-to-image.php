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
        echo '<pre>';
        echo base64_encode($_FILES['source']['tmp_name']);
        echo '</pre>';
    } else {
        echo '<p>Geen bronbestand gevonden.</p>';
    }
}


?>
</body>
</html>