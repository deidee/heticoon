<?php

require_once 'class.deicon.php';

$im = new Deicon(['height' => 32, 'width' => 32, 'size' => 2, 'type' => 'ico']);
if(isset($_GET['save'])) $im->save();
echo $im;
