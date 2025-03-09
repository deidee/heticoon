<?php
declare(strict_types=1);

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use deidee\heticoon\Deicon;

$settings = ['size' => 48/*, 'type' => 'jpg'*/];
if(!empty($_GET['data'])) $settings['data'] = $_GET['data'];

$im = new Deicon($settings);
$im->setOffset(1);
$im->setPadding(2);
//$im->setColumnCount(17);
//$im->setX(-48);
if(isset($_GET['save'])) $im->save();
echo $im;
