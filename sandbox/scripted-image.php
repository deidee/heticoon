<?php
declare(strict_types=1);

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use deidee\heticoon\Deicon;

$accepted_vars = ['cols' => 16, 'rows' => 16, 'size' => 24, 'type' => 'jpg'];

if(!empty($_GET['data'])) $accepted_vars['data'] = $_GET['data'];

$settings = array_intersect_key($_GET, $accepted_vars);

$im = new Deicon($settings);
if(isset($_GET['save'])) $im->save();
echo $im;
