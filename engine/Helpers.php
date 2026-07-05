<?php

if (!function_exists('dd')) {

    function dd(...$vars): never
    {
        http_response_code(500);

        echo <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Dump</title>

<style>
body {
    margin:0;
    background:#18171B;
    color:#F8F8F2;
    font-family:Consolas, Monaco, monospace;
}

.dump-container {
    padding:20px;
}

.dump-block {
    background:#272822;
    border-left:5px solid #A6E22E;
    padding:15px;
    margin-bottom:10px;
    overflow:auto;
    line-height:1.5;
    font-size:14px;
}
</style>
</head>
<body>
<div class="dump-container">
HTML;


        foreach ($vars as $var) {

            echo '<div class="dump-block"><pre>';

            highlight_string(
                "<?php\n" . var_export($var, true)
            );

            echo '</pre></div>';
        }

        echo <<<HTML
</div>
</body>
</html>
HTML;

        exit;
    }
}