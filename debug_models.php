<?php
foreach (glob('app/Models/*.php') as $filename) {
    $content = file_get_contents($filename);
    if (preg_match('/protected \$table = [\'"](.+?)[\'"];/', $content, $matches)) {
        echo basename($filename) . ": " . $matches[1] . "\n";
    } else {
         echo basename($filename) . ": [default]\n";
    }
}
