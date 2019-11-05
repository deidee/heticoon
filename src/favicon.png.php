<?php

require_once 'class.deicon.php';

$im = new Deicon(['height' => 16, 'width' => 16, 'size' => 1, 'type' => 'png']);
if(isset($_GET['save'])) $im->save();
echo $im;
