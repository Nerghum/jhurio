<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$shortDescriptions = [
    '300m-wifi-repeater-wireless-range-extender' => '৩০০এম ওয়াইফাই রিপিটার আপনার বর্তমান ওয়াইফাই নেটওয়ার্কের রেঞ্জ বাড়াতে সাহায্য করে।
<ul>
  <li>স্পিড: ৩০০ এমবিপিএস</li>
  <li>সুবিধা: সহজ প্লাগ অ্যান্ড প্লে</li>
  <li>ব্যবহার: হোম এবং অফিস</li>
</ul>',

    'automatic-curling-bar-pd-2118-42w-automatic-hair-curler' => 'PD-2118 অটোমেটিক কার্লিং বার দিয়ে খুব সহজেই ঘরে বসে সুন্দর কার্ল তৈরি করুন।
<ul>
  <li>পাওয়ার: ৪২ ওয়াট</li>
  <li>রোটেশন: ৩৬০ ডিগ্রি অটোমেটিক</li>
  <li>সুবিধা: অ্যান্টি-স্ক্যাল্ড প্রযুক্তি</li>
</ul>',

    'hoco-eq24-wireless-bluetooth-headset-13mm-speaker' => 'HOCO EQ24 ওয়্যারলেস ব্লুটুথ হেডসেট - চমৎকার সাউন্ড এবং দীর্ঘ ব্যাটারি লাইফ।
<ul>
  <li>স্পিকার: ১৩ মিমি ডাইনামিক</li>
  <li>ব্যাটারি: ২৮ ঘণ্টা প্লেব্যাক</li>
  <li>কানেক্টিভিটি: ব্লুটুথ ৫.৩</li>
</ul>',

    'folding-washing-machine-12l-portable-mini-washing-machine' => '১২ লিটার পোর্টেবল ফোল্ডিং ওয়াশিং মেশিন - ছোট পরিবার বা ভ্রমণের জন্য সেরা।
<ul>
  <li>ক্যাপাসিটি: ১২ লিটার</li>
  <li>ডিজাইন: ফোল্ডিং এবং পোর্টেবল</li>
  <li>কন্ট্রোল: স্মার্ট টাচ স্ক্রিন</li>
</ul>',

    'wireless-bluetooth-speaker-y-900-8w-powerful-sound' => 'ওয়্যারলেস ব্লুটুথ স্পিকার Y-900 - ৮ ওয়াটের শক্তিশালী সাউন্ড।
<ul>
  <li>আউটপুট: ৮ ওয়াট পাওয়ার</li>
  <li>ফিচার: ব্লুটুথ, এফএম এবং ইউএসবি</li>
  <li>ডিজাইন: পোর্টেবল এবং রিচার্জেবল</li>
</ul>'
];

foreach ($shortDescriptions as $slug => $desc) {
    $product = Product::where('slug', $slug)->first();
    if ($product) {
        $product->update(['description' => $desc]);
        echo "Updated short description for: {$product->name}\n";
    } else {
        echo "Product not found for slug: {$slug}\n";
    }
}

echo "All short descriptions updated successfully.\n";
