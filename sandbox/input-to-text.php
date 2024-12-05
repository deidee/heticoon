<?php
declare(strict_types=1);

namespace deidee;

define('DEFAULT_COLS', 16);
define('DEFAULT_ROWS', 16);
define('DEFAULT_SIZE', 24);
define('DEFAULT_DATA', [
    [0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0],
    [0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0],
    [0,0,0,0,1,1,1,0,1,1,1,0,0,0,0,0],
    [0,0,0,0,1,0,1,0,1,0,1,0,0,0,0,0],
    [0,0,0,0,1,0,1,0,1,1,1,0,0,0,0,0],
    [0,0,0,0,1,0,1,0,1,0,0,0,0,0,0,0],
    [0,0,0,0,1,1,1,0,1,1,1,0,0,0,0,0],
    [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
    [1,1,1,0,0,0,1,0,0,0,0,0,0,0,0,0],
    [0,1,0,0,0,0,1,0,0,0,0,0,0,0,0,0],
    [0,1,0,0,1,1,1,0,1,1,1,0,1,1,1,0],
    [0,1,0,0,1,0,1,0,1,0,1,0,1,0,1,0],
    [0,1,0,0,1,0,1,0,1,1,1,0,1,1,1,0],
    [0,1,0,0,1,0,1,0,1,0,0,0,1,0,0,0],
    [1,1,1,0,1,1,1,0,1,1,1,0,1,1,1,0],
    [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]
]);

$cols = !empty($_GET['cols']) ? intval($_GET['cols']) : DEFAULT_COLS;
$rows = !empty($_GET['rows']) ? intval($_GET['rows']) : DEFAULT_ROWS;
$size = !empty($_GET['size']) ? intval($_GET['size']) : DEFAULT_SIZE;

$empty_row = array_fill(0, $cols, '0');
$empty_set = array_fill(0, $rows, $empty_row);

$data = !empty($_GET['data']) && is_array($_GET['data']) ? $_GET['data'] : DEFAULT_DATA;

$matrix = array_replace_recursive($empty_set,
    array_intersect_key(
        $data, $empty_set
    )
);

// Just a counter.
$cell = 0;

?>
<!doctype html>
<html dir="ltr" lang="nl">
<head>
    <meta charset="utf-8">
    <title>HetIcoon</title>
    <link rel="stylesheet" href="https://grid.sexy/css/grid.min.css">
</head>
<body id="top">
<div class="container">
    <div class="row">
        <div class="col">
            <form action="">
                <label>Rijen: <input name="rows" type="number" min="1" max="256" value="<?= $rows ?>" placeholder="Rijen"></label>
                <label>Kolommen: <input name="cols" type="number" min="1" max="256" value="<?= $cols ?>" placeholder="Kolommen"></label>
                <label>Grootte: <input name="size" type="number" min="1" max="256" value="<?= $size ?>" placeholder="Grootte"></label>
                <table>
                    <tbody>
                    <?php for($r = 0; $r < $rows; $r++): ?>
                        <tr>
                            <?php for($c = 0; $c < $cols; $c++): $cell++; ?>
                                <td><label><input<?= (!empty($matrix[$r][$c]) ? ' checked' : '') ?> id="cell-<?= $cell ?>" name="data[<?= $r ?>][<?= $c ?>]" type="checkbox" value="<?= $cell ?>"></label></td>
                            <?php endfor; ?>
                        </tr>
                    <?php endfor; ?>
                    </tbody>
                </table>
                <button type="submit">Doe</button>
            </form>
        </div>
        <div class="col">
            <?php

            $accepted_vars = ['rows' => 16, 'cols' => 16, 'height' => 300, 'width' => 300, 'size' => 24, 'type' => 'jpg', 'save' => false, 'data' => []];
            $query_vars = array_intersect_key($_GET, $accepted_vars);
            $query_string = http_build_query($query_vars);
            $src = 'scripted-image.php?' . $query_string;

            ?>
            <img src="<?= $src ?>" alt="">
        </div>
        <div class="col">
            <pre>
<?= htmlspecialchars(print_r($matrix, true)) ?>
</pre>
        </div>
    </div>
</div>
</body>
</html>