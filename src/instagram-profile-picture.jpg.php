<?php
declare(strict_types=1);

namespace deidee;

require_once 'class.deicon.php';

$im = new Deicon([/*'height' => 960, 'width' => 960, */'type' => 'jpg']);
$im->setOffset(1);
if(isset($_GET['save'])) $im->save();
echo $im;
