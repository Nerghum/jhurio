<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Find Media created in the last 10 minutes
$cutoff = Carbon::now()->subMinutes(10);
$medias = Media::where('created_at', '>=', $cutoff)->get();

$count = 0;
foreach ($medias as $media) {
    // Delete physical file
    if (!str_starts_with($media->file_path, 'http')) {
        Storage::disk('public')->delete($media->file_path);
    }
    
    // Detach from product_media pivot if linked
    DB::table('product_media')->where('media_id', $media->id)->delete();
    
    // Delete the record
    $media->delete();
    
    $count++;
}

echo "Successfully deleted {$count} media items added in the last 10 minutes.\n";
