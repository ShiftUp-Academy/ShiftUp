<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$log = "";
foreach ([
    'quoted_pascal' => 'SELECT 1 FROM "ProgrammeFormation" LIMIT 1',
    'quoted_lower' => 'SELECT 1 FROM "programmeformation" LIMIT 1',
    'unquoted_pascal' => 'SELECT 1 FROM ProgrammeFormation LIMIT 1',
    'unquoted_lower' => 'SELECT 1 FROM programmeformation LIMIT 1'
] as $k => $sql) {
    try {
        DB::select($sql);
        $log .= "$k: SUCCESS\n";
    } catch (\Exception $e) {
        $log .= "$k: FAILED (" . $e->getMessage() . ")\n";
    }
}
file_put_contents('test_case_results.txt', $log);
echo "Done.";
