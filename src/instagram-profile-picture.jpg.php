<?php
declare(strict_types=1);

namespace deidee;

require_once 'class.deicon.php';

$im = new Deicon(['size' => 64, 'type' => 'jpg']);
$im->setOffset(1);
if(isset($_GET['save'])) $im->save();
echo $im;
