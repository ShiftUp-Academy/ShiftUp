<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$modelsPath = __DIR__ . '/app/Models';
$files = scandir($modelsPath);

$results = [];

foreach ($files as $file) {
    if ($file === '.' || $file === '..' || !str_ends_with($file, '.php')) continue;

    $className = 'App\\Models\\' . str_replace('.php', '', $file);
    try {
        if (!class_exists($className)) continue;
        
        $reflection = new ReflectionClass($className);
        $traits = $reflection->getTraitNames();
        
        if (in_array('Illuminate\\Database\\Eloquent\\SoftDeletes', $traits)) {
            $model = new $className;
            $table = $model->getTable();
            
            $hasColumn = Schema::hasColumn($table, 'deleted_at');
            
            $results[] = [
                'model' => $className,
                'table' => $table,
                'has_deleted_at' => $hasColumn
            ];
        }
    } catch (\Throwable $e) {
    }
}

file_put_contents('soft_deletes_check.json', json_encode($results, JSON_PRETTY_PRINT));
echo "Done! Results saved to soft_deletes_check.json\n";
