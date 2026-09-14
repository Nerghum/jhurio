<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Media;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

$productData = [
    'title' => 'Automatic Roti Maker',
    'price' => 2500,
    'category' => 'Home Appliances',
    'tags' => ['Kitchen', 'Roti Maker', 'Appliance'],
    'short_desc' => 'অটোমেটিক রুটি মেকার - ঘরে বসেই খুব সহজে এবং কম সময়ে গোল ও নরম রুটি তৈরি করুন।
<ul>
  <li>সময় সাশ্রয়ী: কয়েক সেকেন্ডেই রুটি তৈরি</li>
  <li>নন-স্টিক প্লেট: রুটি লেগে থাকে না</li>
  <li>ব্যবহার: অত্যন্ত সহজ ও নিরাপদ</li>
</ul>',
    'full_desc' => '
        <h2>অটোমেটিক রুটি মেকার (Automatic Roti Maker)</h2>
        <p>প্রতিদিন রুটি বানানোর ঝামেলা থেকে মুক্তি পেতে অটোমেটিক রুটি মেকার হতে পারে আপনার রান্নাঘরের সেরা সঙ্গী। এর সাহায্যে কোনো রকম কষ্ট ছাড়াই খুব দ্রুত গোল এবং নরম রুটি তৈরি করা সম্ভব।</p>
        <h3>মূল বৈশিষ্ট্যসমূহ:</h3>
        <ul>
            <li><strong>সময় সাশ্রয়ী:</strong> খুব দ্রুত গরম হয় এবং কয়েক সেকেন্ডের মধ্যেই রুটি তৈরি করে ফেলে।</li>
            <li><strong>নন-স্টিক কোটিং:</strong> রুটি মেকারের প্লেটে নন-স্টিক কোটিং থাকায় রুটি লেগে যায় না এবং পরিষ্কার করাও খুব সহজ।</li>
            <li><strong>সহজ ব্যবহার:</strong> শুধু খামির দিয়ে চাপ দিন এবং আপনার পারফেক্ট গোল রুটি প্রস্তুত।</li>
            <li><strong>তাপমাত্রা নিয়ন্ত্রক:</strong> প্রয়োজন অনুযায়ী তাপমাত্রা কমানো বা বাড়ানোর স্বয়ংক্রিয় ব্যবস্থা রয়েছে।</li>
            <li><strong>টেকসই ডিজাইন:</strong> উন্নত মানের ম্যাটেরিয়াল দিয়ে তৈরি হওয়ায় এটি অনেক দিন লাস্টিং করে।</li>
        </ul>
        <p>যাঁরা ব্যস্ত জীবনযাপন করেন বা রুটি বানাতে স্বাচ্ছন্দ্যবোধ করেন না, তাঁদের জন্য এটি একটি অপরিহার্য পণ্য।</p>
    ',
    'images' => [
        "https://back-office.htbazar.com/productImages/VHi9o1788012505-light-865x1024.jpeg",
        "https://back-office.htbazar.com/productImages/shopify-pZa0hxzlmPoJ.jpg",
        "https://back-office.htbazar.com/productImages/shopify-npJNpzJyhSWL.png",
        "https://back-office.htbazar.com/productImages/shopify-p3PcwzCTsKmX.jpg",
        "https://back-office.htbazar.com/productImages/EGnTY1787671727-light-1086x1448.jpeg"
    ]
];

// 1. Create or get Category
$category = Category::firstOrCreate(
    ['slug' => Str::slug($productData['category'])],
    ['name' => $productData['category']]
);

// 2. Create or get Tags
$tagIds = [];
foreach ($productData['tags'] as $tagName) {
    $tag = Tag::firstOrCreate(
        ['slug' => Str::slug($tagName)],
        ['name' => $tagName]
    );
    $tagIds[] = $tag->id;
}

// 3. Process Images
$mediaIds = [];
foreach ($productData['images'] as $index => $imageUrl) {
    // Strip trailing slash if present
    $imageUrl = rtrim($imageUrl, "\\");
    $filename = basename($imageUrl);
    
    try {
        $contents = @file_get_contents($imageUrl);
        if ($contents) {
            $path = 'products/' . uniqid() . '_' . $filename;
            Storage::disk('public')->put($path, $contents);
            
            $media = Media::create([
                'filename' => $filename,
                'file_path' => $path,
                'file_type' => 'image/jpeg',
                'file_size' => strlen($contents)
            ]);
            $mediaIds[] = $media->id;
            echo "Downloaded: $filename\n";
        }
    } catch (\Exception $e) {
        echo "Failed to download image: $imageUrl. Storing external url.\n";
        $media = Media::create([
            'filename' => $filename,
            'file_path' => $imageUrl,
            'file_type' => 'image/jpeg',
            'file_size' => 0
        ]);
        $mediaIds[] = $media->id;
    }
}

// 4. Create Product
$thumbnailId = count($mediaIds) > 0 ? $mediaIds[0] : null;

$product = Product::updateOrCreate(
    ['slug' => Str::slug($productData['title'])],
    [
        'name' => $productData['title'],
        'description' => $productData['short_desc'],
        'content' => $productData['full_desc'],
        'price' => $productData['price'],
        'status' => 'published',
        'stock_quantity' => 15,
        'thumbnail_media_id' => $thumbnailId,
        'is_featured' => true,
    ]
);

// Attach Category
$product->categories()->syncWithoutDetaching([$category->id]);

// Attach Tags
$product->tags()->syncWithoutDetaching($tagIds);

// Attach Gallery Media
if (count($mediaIds) > 0) {
    $galleryData = [];
    foreach ($mediaIds as $index => $id) {
        $galleryData[$id] = ['sort_order' => $index];
    }
    $product->gallery()->sync($galleryData);
}

echo "Imported: " . $product->name . "\n";
