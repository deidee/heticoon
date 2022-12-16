<?php
declare(strict_types=1);

namespace deidee;

require_once 'class.deicon.php';

$im = new Deicon(['size' => 48, 'type' => 'jpg']);
$im->setOffset(1);
$im->setPadding(2);
if(isset($_GET['save'])) $im->save();
echo $im;
