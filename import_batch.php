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

$productsData = [
    [
        'title' => 'Silicone Sink Water Splash Guard Guard & Water Baffle',
        'price' => 300,
        'category' => 'Kitchen Accessories',
        'tags' => ['Sink', 'Splash Guard', 'Kitchen'],
        'short_desc' => 'সিলিকন সিঙ্ক ওয়াটার স্প্ল্যাশ গার্ড - কিচেন পরিষ্কার রাখুন সহজে।
<ul>
  <li>উপাদান: উচ্চ মানের সিলিকন</li>
  <li>ব্যবহার: পানি ছিটকানো রোধ করে</li>
  <li>সুবিধা: সহজে পরিষ্কারযোগ্য ও টেকসই</li>
</ul>',
        'full_desc' => '
            <h2>সিলিকন সিঙ্ক ওয়াটার স্প্ল্যাশ গার্ড</h2>
            <p>থালাবাসন ধোয়ার সময় সিঙ্কের চারপাশে পানি ছিটকে পড়ার বিরক্তিকর সমস্যা থেকে মুক্তি পেতে সিলিকন সিঙ্ক ওয়াটার স্প্ল্যাশ গার্ড ব্যবহার করুন। এটি আপনার রান্নাঘরকে শুকনো এবং পরিষ্কার রাখতে সাহায্য করবে।</p>
            <h3>মূল বৈশিষ্ট্যসমূহ:</h3>
            <ul>
                <li><strong>উচ্চ মানের সিলিকন ম্যাটেরিয়াল:</strong> এটি সম্পূর্ণ নিরাপদ, নরম এবং অত্যন্ত টেকসই।</li>
                <li><strong>অ্যান্টি-স্প্ল্যাশ ডিজাইন:</strong> পানি ছিটকানো রোধ করে এবং আপনার জামাকাপড় শুকনো রাখে।</li>
                <li><strong>সহজ ইন্সটলেশন:</strong> সিঙ্কের চারপাশে সহজেই সেট করা যায় এবং স্থান পরিবর্তন করা যায়।</li>
                <li><strong>সহজ পরিচ্ছন্নতা:</strong> পানির সাহায্যে খুব দ্রুত ধুয়ে ফেলা যায়।</li>
                <li><strong>মাল্টিপারপাস ব্যবহার:</strong> রান্নাঘরের সিঙ্ক বা বাথরুমের বেসিনে ব্যবহারের জন্য উপযুক্ত।</li>
            </ul>
            <p>আপনার রান্নাঘরের প্রতিদিনের কাজকে আরও সহজ এবং গোছানো করতে আজই এটি সংগ্রহ করুন।</p>
        ',
        'images' => [
            "https://back-office.htbazar.com/productImages/h7UDh1788868403-light-641x1024.jpeg",
            "https://back-office.htbazar.com/productImages/ZxP0y1787072508-light-1024x572.jpeg",
            "https://back-office.htbazar.com/productImages/ohOM81788791842-light-529x1024.jpeg",
            "https://back-office.htbazar.com/productImages/Lw4lL1788870947-light-1254x1254.jpeg",
            "https://back-office.htbazar.com/productImages/shopify-37XMoGlxFvHE.jpg"
        ]
    ],
    [
        'title' => 'MEISHI DA Multifunctional Stainless Steel Sink Organizer Basket',
        'price' => 500,
        'category' => 'Kitchen Accessories',
        'tags' => ['Sink Organizer', 'Stainless Steel', 'Basket'],
        'short_desc' => 'মাল্টিফাংশনাল স্টেইনলেস স্টিল সিঙ্ক অর্গানাইজার বাস্কেট।
<ul>
  <li>উপাদান: মরিচারোধী স্টেইনলেস স্টিল</li>
  <li>ডিজাইন: স্পঞ্জ ও স্ক্রাবার রাখার জন্য পারফেক্ট</li>
  <li>সুবিধা: অতিরিক্ত পানি ঝরে যায়</li>
</ul>',
        'full_desc' => '
            <h2>মাল্টিফাংশনাল স্টেইনলেস স্টিল সিঙ্ক অর্গানাইজার</h2>
            <p>আপনার কিচেন সিঙ্ক এলাকা পরিষ্কার ও গুছিয়ে রাখতে MEISHI DA সিঙ্ক অর্গানাইজার বাস্কেটটি একটি চমৎকার সমাধান। এতে স্পঞ্জ, ব্রাশ, ডিশ সোপ ইত্যাদি খুব সহজেই রাখা যায়।</p>
            <h3>মূল বৈশিষ্ট্যসমূহ:</h3>
            <ul>
                <li><strong>প্রিমিয়াম ম্যাটেরিয়াল:</strong> মরিচারোধী টেকসই স্টেইনলেস স্টিল দিয়ে তৈরি, যা দীর্ঘদিন ব্যবহার করা যায়।</li>
                <li><strong>ভেন্টিলেশন ডিজাইন:</strong> অতিরিক্ত পানি দ্রুত ঝরে যায়, ফলে স্পঞ্জ ব্যাকটেরিয়া মুক্ত থাকে।</li>
                <li><strong>সহজ ইনস্টলেশন:</strong> সিঙ্কের সাথে আটকে রাখা যায় খুব সহজেই।</li>
                <li><strong>বহুমুখী ব্যবহার:</strong> রান্নাঘর এবং বাথরুম উভয় স্থানেই এটি ব্যবহার উপযোগী।</li>
                <li><strong>কম্প্যাক্ট সাইজ:</strong> বেশি জায়গা দখল করে না কিন্তু অনেক কিছু রাখা যায়।</li>
            </ul>
            <p>সিঙ্ক পরিষ্কার রাখতে এবং প্রয়োজনীয় জিনিস হাতের নাগালে পেতে এটি একটি অত্যন্ত দরকারী পণ্য।</p>
        ',
        'images' => [
            "https://back-office.htbazar.com/productImages/shopify-YWf3i7jatjii.png",
            "https://back-office.htbazar.com/productImages/shopify-odHspczWXbVu.png",
            "https://back-office.htbazar.com/productImages/shopify-xoyysqU46FSL.png",
            "https://back-office.htbazar.com/productImages/shopify-j5kqxu49gNlU.png",
            "https://back-office.htbazar.com/productImages/HiBkK1788011722-light-596x1024.jpeg"
        ]
    ],
    [
        'title' => 'Portable Travel Bottles Set – 6 Pcs',
        'price' => 400,
        'category' => 'Travel Accessories',
        'tags' => ['Travel', 'Bottles', 'Toiletry'],
        'short_desc' => 'পোর্টেবল ট্রাভেল বোতল সেট (৬ পিস) - ভ্রমণের জন্য সেরা।
<ul>
  <li>পরিমাণ: ৬ পিস সেট</li>
  <li>ডিজাইন: লিক-প্রুফ এবং হালকা</li>
  <li>ব্যবহার: শ্যাম্পু, লোশন এবং ক্রিম বহনের জন্য</li>
</ul>',
        'full_desc' => '
            <h2>পোর্টেবল ট্রাভেল বোতল সেট (৬ পিস)</h2>
            <p>ভ্রমণের সময় ভারী প্রসাধনীর বোতল বহন করা অনেক কষ্টের। এই ৬ পিসের পোর্টেবল ট্রাভেল বোতল সেটটি আপনার ট্রাভেল ব্যাগে সহজেই ফিট হয়ে যাবে এবং আপনার ভ্রমণকে করবে আরও সহজ।</p>
            <h3>মূল বৈশিষ্ট্যসমূহ:</h3>
            <ul>
                <li><strong>সম্পূর্ণ সেট:</strong> ৬টি ভিন্ন সাইজ ও আকারের বোতল, যা শ্যাম্পু, লোশন বা ক্রিমের জন্য উপযুক্ত।</li>
                <li><strong>লিক-প্রুফ ডিজাইন:</strong> বোতলগুলো থেকে কোন তরল চুঁইয়ে পড়ার ভয় নেই।</li>
                <li><strong>এয়ারলাইন অ্যাপ্রুভড:</strong> বিমানে বহনের জন্য বোতলগুলোর সাইজ সম্পূর্ণ উপযুক্ত।</li>
                <li><strong>পুনর্ব্যবহারযোগ্য:</strong> পরিষ্কার করা খুব সহজ এবং বারবার ব্যবহার করা যায়।</li>
                <li><strong>পরিবেশবান্ধব:</strong> হাই-কোয়ালিটি প্লাস্টিক এবং সিলিকন দিয়ে তৈরি।</li>
            </ul>
            <p>যারা প্রায়ই ভ্রমণ করেন তাদের জন্য এটি একটি অত্যন্ত প্রয়োজনীয় সামগ্রী।</p>
        ',
        'images' => [
            "https://back-office.htbazar.com/productImages/Ng9mJ1788028694-light-1600x1600.jpeg",
            "https://back-office.htbazar.com/productImages/Uvn7O1788187133-dark-1086x1448.jpeg",
            "https://back-office.htbazar.com/productImages/FuTv51786895652-light-657x1024.jpeg",
            "https://back-office.htbazar.com/productImages/shopify-wa2AOtI3vnyn.jpg",
            "https://back-office.htbazar.com/productImages/acrP31788188390-light-628x1024.jpeg"
        ]
    ],
    [
        'title' => '2-in-1 Fitness Combo Set: Hand Gripper with Counter & Spring Tummy Trimmer',
        'price' => 800,
        'category' => 'Fitness',
        'tags' => ['Fitness', 'Workout', 'Tummy Trimmer', 'Hand Gripper'],
        'short_desc' => 'টু-ইন-ওয়ান ফিটনেস কম্বো সেট - ঘরে বসেই ব্যায়াম করুন।
<ul>
  <li>প্যাকেজ: হ্যান্ড গ্রিপার এবং স্প্রিং টামি ট্রিমার</li>
  <li>ফিচার: অটো কাউন্টার যুক্ত হ্যান্ড গ্রিপার</li>
  <li>উপকারিতা: পেশী শক্তিশালী এবং মেদ কমাতে সহায়ক</li>
</ul>',
        'full_desc' => '
            <h2>টু-ইন-ওয়ান ফিটনেস কম্বো সেট</h2>
            <p>যারা ঘরে বসে নিজেদের ফিটনেস ধরে রাখতে চান, তাদের জন্য এই ২-ইন-১ ফিটনেস কম্বো সেটটি দারুণ একটি প্যাকেজ। এতে থাকছে একটি কাউন্টারযুক্ত হ্যান্ড গ্রিপার এবং স্প্রিং টামি ট্রিমার।</p>
            <h3>মূল বৈশিষ্ট্যসমূহ:</h3>
            <ul>
                <li><strong>হ্যান্ড গ্রিপার উইথ কাউন্টার:</strong> আপনার হাতের পেশীকে শক্তিশালী করতে সাহায্য করে। এতে থাকা অটোমেটিক কাউন্টার আপনার ওয়ার্কআউটের ট্র্যাক রাখে।</li>
                <li><strong>স্প্রিং টামি ট্রিমার:</strong> পেটের মেদ কমাতে এবং বডি টোন আপ করতে অত্যন্ত কার্যকরী।</li>
                <li><strong>টেকসই ম্যাটেরিয়াল:</strong> হেভি-ডিউটি স্প্রিং এবং মজবুত প্লাস্টিক হ্যান্ডেল দীর্ঘস্থায়ী ব্যবহারের নিশ্চয়তা দেয়।</li>
                <li><strong>কম্প্যাক্ট সাইজ:</strong> যেকোনো জায়গায় বহন করে ব্যায়াম করা যায়।</li>
                <li><strong>সকলের জন্য উপযুক্ত:</strong> পুরুষ বা নারী, নতুন বা অভিজ্ঞ যে কেউ এটি ব্যবহার করতে পারেন।</li>
            </ul>
            <p>নিয়মিত ব্যায়ামের মাধ্যমে সুস্থ ও সুন্দর জীবনের দিকে এক ধাপ এগিয়ে যান এই ফিটনেস কম্বোটির সাহায্যে।</p>
        ',
        'images' => [
            "https://back-office.htbazar.com/productImages/shopify-z8gixFGCKXOy.png",
            "https://back-office.htbazar.com/productImages/pKIcV1788010457-light-552x1024.jpeg",
            "https://back-office.htbazar.com/productImages/rnMln1788788833-light-1024x572.jpeg",
            "https://back-office.htbazar.com/productImages/MeMR61788001965-dark-577x1024.jpeg",
            "https://back-office.htbazar.com/productImages/UDE7H1788873023-light-635x1024.jpeg"
        ]
    ],
    [
        'title' => 'USB Rechargeable Milk Frother',
        'price' => 600,
        'category' => 'Kitchen Accessories',
        'tags' => ['Milk Frother', 'Coffee', 'Kitchen'],
        'short_desc' => 'ইউএসবি রিচার্জেবল মিল্ক ফ্রদার - পারফেক্ট কফি তৈরির জন্য।
<ul>
  <li>রিচার্জেবল: বারবার ব্যাটারি বদলানোর ঝামেলা নেই</li>
  <li>ডিজাইন: ওয়্যারলেস এবং সহজে বহনযোগ্য</li>
  <li>ব্যবহার: কফি, মিল্কশেক এবং ডিম ফেটানোর জন্য</li>
</ul>',
        'full_desc' => '
            <h2>ইউএসবি রিচার্জেবল মিল্ক ফ্রদার</h2>
            <p>আপনার কফি বা মিল্কশেকের উপরে রেস্টুরেন্টের মতো সুন্দর ফোম তৈরি করতে এই ইউএসবি রিচার্জেবল মিল্ক ফ্রদারটি অপরিহার্য। এটি ব্যবহার করা খুবই সহজ এবং দ্রুত কাজ করে।</p>
            <h3>মূল বৈশিষ্ট্যসমূহ:</h3>
            <ul>
                <li><strong>রিচার্জেবল ব্যাটারি:</strong> এতে রয়েছে বিল্ট-ইন রিচার্জেবল ব্যাটারি, তাই বারবার নতুন ব্যাটারি কেনার ঝামেলা নেই।</li>
                <li><strong>মাল্টি-স্পিড কন্ট্রোল:</strong> কাজের সুবিধা অনুযায়ী ভিন্ন ভিন্ন স্পিড সেট করা যায়।</li>
                <li><strong>দুটি ভিন্ন হুইস্ক হেড:</strong> ডিম ফেটানো এবং দুধ ফোম করার জন্য আলাদা হুইস্ক হেড দেওয়া আছে।</li>
                <li><strong>স্টেইনলেস স্টিল ডিজাইন:</strong> প্রিমিয়াম কোয়ালিটির স্টেইনলেস স্টিল দিয়ে তৈরি যা মরিচারোধী এবং নিরাপদ।</li>
                <li><strong>সহজ পরিচ্ছন্নতা:</strong> কাজ শেষে শুধুমাত্র পানি দিয়ে ধুয়ে ফেলা যায়।</li>
            </ul>
            <p>কফিপ্রেমী বা বেকিংয়ে আগ্রহীদের জন্য এটি একটি দুর্দান্ত উপহার হতে পারে।</p>
        ',
        'images' => [
            "https://back-office.htbazar.com/productImages/ZHvUM1788958245-light-825x1024.jpeg",
            "https://back-office.htbazar.com/productImages/oejYG1788871842-dark-1376x768.jpg",
            "https://back-office.htbazar.com/productImages/cZQCI1786891954-light-740x1024.jpeg",
            "https://back-office.htbazar.com/productImages/ndiZM1789047339-light-1254x1254.jpeg",
            "https://back-office.htbazar.com/productImages/RJmAX1788783059-light-585x1024.jpeg"
        ]
    ],
    [
        'title' => 'DIY Laptop Table Stand Leg Kit',
        'price' => 1500,
        'category' => 'Office Supplies',
        'tags' => ['Laptop Stand', 'DIY', 'Desk Accessory'],
        'short_desc' => 'ডিআইওয়াই ল্যাপটপ টেবিল স্ট্যান্ড লেগ কিট - নিজের মতো টেবিল তৈরি করুন।
<ul>
  <li>উপাদান: মজবুত মেটাল লেগস</li>
  <li>ডিজাইন: ডিআইওয়াই বা নিজের মতো কাস্টমাইজযোগ্য</li>
  <li>সুবিধা: ফোল্ডেবল এবং সহজে বহনযোগ্য</li>
</ul>',
        'full_desc' => '
            <h2>ডিআইওয়াই ল্যাপটপ টেবিল স্ট্যান্ড লেগ কিট</h2>
            <p>যারা নিজেদের পছন্দমতো ল্যাপটপ টেবিল বা ছোট পড়ার টেবিল তৈরি করতে চান, তাদের জন্য এই DIY ল্যাপটপ টেবিল স্ট্যান্ড লেগ কিটটি দারুণ কাজের। যেকোনো সমতল বোর্ডকে নিমিষেই টেবিলে রূপান্তর করুন।</p>
            <h3>মূল বৈশিষ্ট্যসমূহ:</h3>
            <ul>
                <li><strong>প্রিমিয়াম ম্যাটেরিয়াল:</strong> অত্যন্ত টেকসই এবং মজবুত মেটাল দিয়ে তৈরি, যা ভারী ওজন বহন করতে সক্ষম।</li>
                <li><strong>সহজ ইন্সটলেশন:</strong> স্ক্রু-এর মাধ্যমে সহজেই যেকোনো কাঠের বোর্ডের সাথে লাগানো যায়।</li>
                <li><strong>ফোল্ডেবল ডিজাইন:</strong> ব্যবহার শেষে পা গুলো ভাঁজ করে রাখা যায়, ফলে জায়গা বাঁচে।</li>
                <li><strong>অ্যান্টি-স্লিপ গ্রিপ:</strong> স্ট্যান্ডের নিচে রাবার গ্রিপ থাকায় এটি মেঝের সাথে শক্তভাবে লেগে থাকে এবং পিছলে যায় না।</li>
                <li><strong>বহুমুখী ব্যবহার:</strong> বেড ট্রে, ল্যাপটপ ডেস্ক, পড়ার টেবিল ইত্যাদি তৈরিতে পারফেক্ট।</li>
            </ul>
            <p>ক্রিয়েটিভ মানুষদের জন্য এটি একটি অত্যন্ত উপযোগী প্রোডাক্ট, যা দিয়ে আপনি নিজের হাতে একটি পারফেক্ট টেবিল তৈরি করতে পারবেন।</p>
        ',
        'images' => [
            "https://back-office.htbazar.com/productImages/wI5Fl1788869722-light-771x1024.jpeg",
            "https://back-office.htbazar.com/productImages/GaiXT1787755306-light-925x1024.jpeg",
            "https://back-office.htbazar.com/productImages/TDYS31788094329-light-839x1024.jpeg",
            "https://back-office.htbazar.com/productImages/1c5Q01788864768-light-812x1024.jpeg",
            "https://back-office.htbazar.com/productImages/RJmAX1788783059-light-585x1024.jpeg"
        ]
    ]
];

foreach ($productsData as $productData) {
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
}

echo "All products imported successfully.\n";
