<?php
$out = "";
foreach (glob('app/Models/*.php') as $filename) {
    $content = file_get_contents($filename);
    if (preg_match('/protected \$table = [\'"](.+?)[\'"];/', $content, $matches)) {
        $out .= basename($filename) . ": " . $matches[1] . "\n";
    } else {
         $out .= basename($filename) . ": [default]\n";
    }
}
file_put_contents('models_debug.txt', $out);
echo "Done.";
