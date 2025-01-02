<?php

declare(strict_types=1);

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use deidee\heticoon\Deicon;

$settings = [/*'height' => 960, 'width' => 960, */'size' => 48];
if(!empty($_GET['data'])) $settings['data'] = $_GET['data'];

$im = new Deicon($settings);
$im->setOffset(1);
$im->setPadding(0);

if(isset($_GET['save'])) $im->save();
echo $im;
