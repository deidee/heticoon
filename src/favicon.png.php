<?php
declare(strict_types=1);

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use deidee\heticoon\Deicon;

$im = new Deicon(['height' => 16, 'width' => 16, 'size' => 1, 'type' => 'png']);
if(isset($_GET['save'])) $im->save();
echo $im;
