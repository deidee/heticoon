<?php
declare(strict_types=1);

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use deidee\heticoon\Deicon;

$im = new Deicon(['size' => 48/*, 'type' => 'jpg'*/]);
$im->setOffset(1);
$im->setPadding(2);
if(isset($_GET['save'])) $im->save();
echo $im;
