<?php

use App\Models\Lecon;
use Illuminate\Support\Facades\Storage;

$l = Lecon::where('Contenu', 'LIKE', '%.pdf')->latest()->first();

if ($l) {
    echo 'ID: ' . $l->IdLecon . PHP_EOL;
    echo 'Contenu: ' . $l->Contenu . PHP_EOL;
    
    $path = str_replace('/storage/', '', $l->Contenu);
    echo 'Relative Path: ' . $path . PHP_EOL;
    
    $exists = Storage::disk('public')->exists($path);
    echo 'Exists public: ' . ($exists ? 'YES' : 'NO') . PHP_EOL;
    
    if ($exists) {
        echo 'Full Path: ' . Storage::disk('public')->path($path) . PHP_EOL;
        echo 'Mime Type: ' . Storage::disk('public')->mimeType($path) . PHP_EOL;
    }
} else {
    echo 'No PDF lesson found.' . PHP_EOL;
}
