<?php

$size = !empty($_GET['size']) ? intval($_GET['size']) : 24;

?>
<!doctype html>
<html dir="ltr" lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="none">
    <title>Input</title>
    <link rel="stylesheet" href="https://grid.sexy/css/grid.min.css">
    <link rel="stylesheet" href="https://hetcdn.nl/deidee/css/destijl.min.css">
    <style>

        * {
            border: 0;
            margin: 0;
            padding: 0;
        }

        input[type=checkbox]::after {
            content: '';
            display: inline-block;
            height: 1.5rem;
            width: 1.5rem;
        }

        input[type=checkbox]:checked::after {
            background: lime;
        }

        label {
            background: black;
            display: inline-block;
            height: 1.5rem;
            position: relative;
            width: 1.5rem;
        }

        table {
            border-collapse: collapse;
        }

    </style>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col">
            <form action="input.php" method="get">
                <table>
                    <tbody>
                    </tbody>
                </table>
                <div><input min="1" max="64" step="1" name="size" type="number" value="<?= $size ?>"></div>
                <div>
                    <select name="type" id="type">
                        <option value="jpg">image/jpeg</option>
                        <option value="png">image/png</option>
                    </select>
                </div>
                <div>
                    <button type="submit">Doe</button>
                </div>
            </form>
        </div>
        <div class="col" hidden>
            <pre></pre>
        </div>
        <div class="col">
            <?php

            $accepted_vars = ['height' => 300, 'width' => 300, 'size' => 24, 'type' => 'jpg'];
            $query_vars = array_intersect_key($_GET, $accepted_vars);
            $query_string = http_build_query($query_vars);
            $src = 'src/scripted-image.php?' . $query_string;

            print_r($query_vars);

            ?>
            <img src="<?= $src ?>" alt="">
        </div>
    </div>
</div>
<script>
    "use strict";

    let tbody = document.querySelector('tbody');
    let form = document.querySelector('form');

    let cols = 16;
    let rows = 16;
    let i = 0;

    for(let row = 0; row < rows; row++) {
        let tr = document.createElement('tr');

        for(let col = 0; col < cols; col++) {
            let td = document.createElement('td');
            let label = document.createElement('label');
            let input = document.createElement('input');
            input.setAttribute('name', 'data[' + row + '][' + col + ']');
            input.setAttribute('type', 'checkbox');
            input.setAttribute('value', i);
            label.appendChild(input);
            td.appendChild(label);
            tr.appendChild(td);

            ++i;
        }

        tbody.appendChild(tr);
    }

    form.onsubmit = function(e) {
        //e.preventDefault();
        let formData = new FormData(form);
        let pre = document.querySelector('pre');
        pre.innerText = JSON.stringify(Array.from(formData));
    }


</script>
</body>
</html>