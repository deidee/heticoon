<?php
declare(strict_types=1);

namespace deidee;

require_once '../src/class.deicon.php';

$accepted_vars = ['cols' => 16, 'rows' => 16, 'data' => []];
$settings = array_intersect_key($_GET, $accepted_vars);

$im = new Deicon($settings);
if(isset($_GET['save'])) $im->save();
echo $im;
