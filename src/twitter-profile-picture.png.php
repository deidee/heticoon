<?php

require_once 'class.deicon.php';

$im = new Deicon([/*'height' => 960, 'width' => 960, */'type' => 'png']);
if(isset($_GET['save'])) $im->save();
echo $im;
