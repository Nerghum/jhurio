<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Media;
use Illuminate\Support\Facades\Storage;

Storage::disk('public')->makeDirectory('media');

$mediaItems = Media::all();

foreach ($mediaItems as $index => $item) {
    $width = 800;
    $height = 800;
    $img = imagecreatetruecolor($width, $height);
    
    // Generate a random pleasant color
    $r = rand(100, 255);
    $g = rand(100, 255);
    $b = rand(100, 255);
    $bg = imagecolorallocate($img, $r, $g, $b);
    imagefilledrectangle($img, 0, 0, $width, $height, $bg);
    
    // Draw some text
    $textColor = imagecolorallocate($img, 20, 20, 20);
    $text = "Product Image " . ($index + 1);
    // Rough center
    imagestring($img, 5, 300, 380, $text, $textColor);
    
    $filename = "test_image_{$index}.png";
    $path = storage_path("app/public/media/{$filename}");
    
    imagepng($img, $path);
    imagedestroy($img);
    
    $item->file_path = "media/{$filename}";
    $item->file_type = "image/png";
    $item->save();
}
echo "Generated local test images.\n";
