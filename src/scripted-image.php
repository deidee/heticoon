<?php

require_once 'class.deicon.php';

$accepted_vars = ['height' => 300, 'width' => 300, 'size' => 24, 'type' => 'jpg'];
$settings = array_intersect_key($_GET, $accepted_vars);

$im = new Deicon($settings);
if(isset($_GET['save'])) $im->save();
echo $im;
