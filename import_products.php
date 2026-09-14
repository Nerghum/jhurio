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

$products = [
    [
        'title' => '300M WiFi Repeater – Wireless Range Extender',
        'price' => 1000,
        'category' => 'Electronics & Gadgets',
        'tags' => ['WiFi', 'Networking', 'Repeater'],
        'short_desc' => '300M ওয়াইফাই রিপিটার আপনার বর্তমান ওয়াইফাই নেটওয়ার্কের রেঞ্জ বাড়াতে সাহায্য করে।',
        'full_desc' => '<p>300M ওয়াইফাই রিপিটার একটি চমৎকার ডিভাইস যা আপনার বাসা বা অফিসের ডেড জোনগুলোতে ওয়াইফাই সিগন্যাল পৌঁছে দেয়। এটি ব্যবহার করা খুবই সহজ এবং এর মাধ্যমে আপনি নিরবচ্ছিন্ন ইন্টারনেট উপভোগ করতে পারবেন।</p>',
        'images' => [
            "https://back-office.htbazar.com/productImages/Mu3FM1788187808-light-575x1024.jpeg",
            "https://back-office.htbazar.com/productImages/h7UDh1788868403-light-641x1024.jpeg",
        ]
    ],
    [
        'title' => 'Automatic Curling Bar PD-2118 – 42W Automatic Hair Curler',
        'price' => 1200,
        'category' => 'Health & Beauty',
        'tags' => ['Hair Care', 'Curler', 'Beauty'],
        'short_desc' => 'PD-2118 অটোমেটিক কার্লিং বার দিয়ে খুব সহজেই ঘরে বসে সুন্দর কার্ল তৈরি করুন।',
        'full_desc' => '<p>42W ক্ষমতাসম্পন্ন এই অটোমেটিক হেয়ার কার্লারটি খুব দ্রুত গরম হয়। এর মাধ্যমে কোন রকম ঝামেলা ছাড়াই নিরাপদভাবে চুলের স্টাইলিং করা যায়। এতে অ্যান্টি-স্ক্যাল্ড প্রযুক্তি রয়েছে যা চুলকে পুড়ে যাওয়ার হাত থেকে রক্ষা করে।</p>',
        'images' => [
            "https://back-office.htbazar.com/productImages/shopify-2AqQ8ef2G5RA.jpg",
            "https://back-office.htbazar.com/productImages/Q2qI51786960331-light-584x1024.jpeg",
        ]
    ],
    [
        'title' => 'HOCO EQ24 Wireless Bluetooth Headset – 13mm Speaker',
        'price' => 1500,
        'category' => 'Electronics & Gadgets',
        'tags' => ['Headset', 'Bluetooth', 'Audio'],
        'short_desc' => 'HOCO EQ24 ওয়্যারলেস ব্লুটুথ হেডসেট - চমৎকার সাউন্ড এবং ২৮ ঘণ্টার ব্যাটারি ব্যাকআপ।',
        'full_desc' => '<p>HOCO EQ24 একটি প্রিমিয়াম ব্লুটুথ হেডসেট। এতে রয়েছে ১৩ মিমি স্পিকার যা দেয় স্পষ্ট এবং শক্তিশালী সাউন্ড। ২৮ ঘন্টা পর্যন্ত দীর্ঘস্থায়ী ব্যাটারি লাইফের কারণে এটি সারাদিন ব্যবহারের জন্য দারুণ উপযোগী।</p>',
        'images' => [
            "https://back-office.htbazar.com/productImages/shopify-NfSNQfs7qxA3.jpg",
            "https://back-office.htbazar.com/productImages/shopify-OqOACf2Lub9k.jpg",
        ]
    ],
    [
        'title' => 'Folding Washing Machine 12L – Portable Mini Washing Machine',
        'price' => 3500,
        'category' => 'Home Appliances',
        'tags' => ['Washing Machine', 'Portable', 'Home'],
        'short_desc' => '১২ লিটার পোর্টেবল ফোল্ডিং ওয়াশিং মেশিন - টাচ স্ক্রিন কন্ট্রোল সহ।',
        'full_desc' => '<p>যারা ব্যাচেলর বা ট্রাভেল করতে ভালোবাসেন তাদের জন্য এই পোর্টেবল ওয়াশিং মেশিনটি দারুণ কাজের। এটি ১২ লিটার ধারণক্ষমতা সম্পন্ন এবং সহজেই ভাঁজ করে রাখা যায়। টাচ স্ক্রিনের মাধ্যমে সহজেই এটি নিয়ন্ত্রণ করা সম্ভব।</p>',
        'images' => [
            "https://back-office.htbazar.com/productImages/3RWFd1788863160-light-550x1024.jpeg",
            "https://back-office.htbazar.com/productImages/shopify-fUCFuud1vo82.jpg",
        ]
    ],
    [
        'title' => 'Wireless Bluetooth Speaker Y-900 – 8W Powerful Sound',
        'price' => 800,
        'category' => 'Electronics & Gadgets',
        'tags' => ['Speaker', 'Bluetooth', 'Audio'],
        'short_desc' => 'ওয়্যারলেস ব্লুটুথ স্পিকার Y-900 - ৮ ওয়াটের শক্তিশালী সাউন্ড।',
        'full_desc' => '<p>Y-900 ব্লুটুথ স্পিকারটি তার ৮ ওয়াট আউটপুটের মাধ্যমে দারুণ মিউজিক এক্সপেরিয়েন্স প্রদান করে। এর চমৎকার ডিজাইন এবং পোর্টেবিলিটি এটিকে যে কোন আড্ডায় বা ভ্রমণে নিয়ে যাওয়ার জন্য উপযুক্ত করে তোলে।</p>',
        'images' => [
            "https://back-office.htbazar.com/productImages/shopify-j9wzviDkTrj5.png",
            "https://back-office.htbazar.com/productImages/rukCy1788343706-light-575x1024.jpeg",
        ]
    ]
];

foreach ($products as $prod) {
    // 1. Create or get Category
    $category = Category::firstOrCreate(
        ['slug' => Str::slug($prod['category'])],
        ['name' => $prod['category']]
    );

    // 2. Create or get Tags
    $tagIds = [];
    foreach ($prod['tags'] as $tagName) {
        $tag = Tag::firstOrCreate(
            ['slug' => Str::slug($tagName)],
            ['name' => $tagName]
        );
        $tagIds[] = $tag->id;
    }

    // 3. Process Images
    $mediaIds = [];
    foreach ($prod['images'] as $index => $imageUrl) {
        $filename = basename($imageUrl);
        
        try {
            $contents = file_get_contents($imageUrl);
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
        ['slug' => Str::slug($prod['title'])],
        [
            'name' => $prod['title'],
            'description' => $prod['short_desc'],
            'content' => $prod['full_desc'],
            'price' => $prod['price'],
            'status' => 'published',
            'stock_quantity' => 10,
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
}

echo "All products imported successfully.\n";
