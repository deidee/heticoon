<?php
declare(strict_types = 1);

namespace deidee;

define('DEFAULT_COLS', 16);
define('DEFAULT_ROWS', 16);
define('DEFAULT_DATA', []);

$cols = !empty($_GET['cols']) ? intval($_GET['cols']) : DEFAULT_COLS;
$rows = !empty($_GET['rows']) ? intval($_GET['rows']) : DEFAULT_ROWS;

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
</head>
<body id="top">
<form action="">
    <label><input name="rows" type="number" min="1" max="256" value="<?= $rows ?>" placeholder="Rijen"></label>
    <label><input name="cols" type="number" min="1" max="256" value="<?= $cols ?>" placeholder="Kolommen"></label>
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
<pre>
<?= htmlspecialchars(print_r($matrix, true)) ?>
</pre>
</body>
</html>