<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
$list = array_column($tables, 'table_name');
asort($list);
echo var_export(array_values($list), true);
