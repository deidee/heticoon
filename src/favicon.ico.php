<?php
declare(strict_types=1);

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use deidee\heticoon\Deicon;

$im = new Deicon(['height' => 32, 'width' => 32, 'size' => 2, 'type' => 'ico']);
if(isset($_GET['save'])) $im->save();
echo $im;
