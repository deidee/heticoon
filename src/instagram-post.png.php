<?php
declare(strict_types=1);

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use deidee\heticoon\Deicon;

$settings = ['size' => 56/*, 'type' => 'jpg'*/];
if(!empty($_GET['data'])) $settings['data'] = $_GET['data'];

$im = new Deicon($settings);
$im->setOffset(1);
$im->setPadding(1);
$im->setColumnCount(18);
$im->setRowCount(18);
//$im->setX(-48);
$im->setAutoSize(true);
$im->setHeight(1080);
$im->setWidth(1080);
if(isset($_GET['save'])) $im->save();
echo $im;
