import urllib.request
import json
import time

products = [
    # 1. Smart Watch
    {
        "name": "১০ ইন ১ ডাব্লিউ১০ স্মার্ট ওয়াচ কম্বো (১০টি আকর্ষণীয় স্ট্র্যাপ ও ওয়্যারলেস চার্জার সহ)",
        "slug": "10-in-1-w10-smart-watch-with-10-straps",
        "price": 1299,
        "compare_at_price": 1499,
        "purchase_price": 650,
        "stock_quantity": 50,
        "status": "published",
        "categories": ["Smart Watches", "Electronics & Gadgets", "Fashion Accessories"],
        "tags": ["স্মার্ট ওয়াচ", "W10 Smart Watch", "10 in 1 Watch", "Smartwatch Straps", "Apple Watch Ultra Style"],
        "description": "একটি ওয়াচ, দশটি ভিন্ন লুক! প্রতিদিনের ফ্যাশন ও লাইফস্টাইলকে আকর্ষণীয় করতে নিয়ে এলো W10 স্মার্ট ওয়াচ কম্বো। এতে পাচ্ছেন একটি প্রিমিয়াম মেটালিক স্মার্টওয়াচ, ১০টি বৈচিত্র্যময় ডিজাইনার স্ট্র্যাপ (সিলিকন, নাইলন লুপ, ওশান ব্যান্ড) এবং ফাস্ট ম্যাগনেটিক ওয়্যারলেস চার্জার।",
        "content": """<h3>W10 ১০ ইন ১ স্মার্ট ওয়াচ কালেকশন – এক ঘড়িতেই ১০টি ভিন্ন স্টাইল</h3>
<p>প্রতিদিন একই ঘড়ি পরে বিরক্ত? আপনার অফিস আউটফিট, ক্যাজুয়াল আড্ডা, স্পোর্টস বা পার্টির সাজের সাথে মিলিয়ে স্মার্টওয়াচটি বদলে নিন মুহূর্তেই! <strong>10 in 1 W10 Smart Watch with 10 Straps</strong> কম্বোতে পাচ্ছেন একটি প্রিমিয়াম মেটাল বডি স্মার্টওয়াচ এবং সাথে ১০টি ভিন্ন রঙ ও ম্যাটেরিয়ালের হাই-কোয়ালিটি স্ট্র্যাপ।</p>

<h4>প্রধান বৈশিষ্ট্য ও সুবিধাসমূহ:</h4>
<ul>
<li><strong>১০টি প্রিমিয়াম স্ট্র্যাপের বিশাল কম্বো:</strong> হাইপোঅ্যালার্জেনিক সিলিকন ব্যান্ড, ওশান ব্যান্ড, ব্রেথেবল নাইলন লুপ, আল্পাইন লুপ ও ব্রেইডেড ট্রেইল লুপ সহ ১০টি ফ্যাশনেবল স্ট্র্যাপ যা অনায়াসে পোশাকের সাথে ম্যাচিং করা যায়।</li>
<li><strong>লার্জ এইচডি ফুল-টাচ স্ক্রিন:</strong> আল্ট্রা-শার্প উজ্জ্বল ডিসপ্লে, যেখানে সময়, ফিটনেস ডেটা এবং নোটিফিকেশন অত্যন্ত নিখুঁতভাবে প্রদর্শিত হয়।</li>
<li><strong>ম্যাগনেটিক কুইক-রিলিজ সিস্টেম:</strong> স্ট্র্যাপ পরিবর্তন করা খুবই সহজ ও দ্রুত; কোনো টুল ছাড়াই কয়েক সেকেন্ডে নতুন স্ট্র্যাপ লাগানো যায়।</li>
<li><strong>স্মার্ট হেলথ ও স্পোর্টস ট্র্যাকিং:</strong> স্টেপ কাউন্ট, ক্যালরি বার্ন, হার্ট রেট ও মাল্টি-স্পোর্টস অ্যাক্টিভিটি মোড সাপোর্ট করে।</li>
<li><strong>ওয়্যারলেস ম্যাগনেটিক চার্জিং:</strong> বক্সে দেওয়া ম্যাগনেটিক চার্জিং পাকের মাধ্যমে দ্রুত ও ঝামেলাহীন চার্জ করা সম্ভব।</li>
</ul>

<h4>টেকনিক্যাল স্পেসিফিকেশন:</h4>
<table>
<tr><td><strong>মডেল</strong></td><td>W10 Smart Watch (Ultra Style)</td></tr>
<tr><td><strong>ডিসপ্লে</strong></td><td>লার্জ কার্ভড ফুল টাচ এইচডি ডিসপ্লে</td></tr>
<tr><td><strong>কেসিং ম্যাটেরিয়াল</strong></td><td>হাই-কোয়ালিটি টাইটানিয়াম-অ্যালয় মেটাল বডি</td></tr>
<tr><td><strong>কন্ট্রোল</strong></td><td>ফাংশনাল ডিজিটাল ক্রাউন ও সাইড বাটন</td></tr>
<tr><td><strong>কানেক্টিভিটি</strong></td><td>ব্লুটুথ স্মার্টফোন সিঙ্ক</td></tr>
<tr><td><strong>চার্জিং মেথড</strong></td><td>ম্যাগনেটিক ওয়্যারলেস ফাস্ট চার্জার</td></tr>
<tr><td><strong>প্যাকেজে যা যা থাকছে</strong></td><td>১x W10 স্মার্ট ওয়াচ, ১০x মাল্টিকালার ডিজাইনার স্ট্র্যাপ, ১x ম্যাগনেটিক চার্জার ক্যাবল</td></tr>
</table>

<h4>ব্যবহারের সুবিধা:</h4>
<p>অফিস, জিম, হাইকিং কিংবা বিয়ে বা উৎসবের অনুষ্ঠান—যেকোনো পরিবেশে আপনার ব্যক্তিত্বকে ফুটিয়ে তুলতে ঘড়ির স্ট্র্যাপ পরিবর্তন করে নিন নিমিষেই। তাছাড়া প্রিয়জনকে উপহার দেওয়ার জন্য এটি একটি সেরা আকর্ষণীয় গ্যাজেট গিফট কম্বো।</p>""",
        "image_urls": [
            "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/10-in-1-w10-smart-watch-with-10-straps/w10-watch-main.jpg",
            "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/10-in-1-w10-smart-watch-with-10-straps/w10-watch-features.jpg",
            "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/10-in-1-w10-smart-watch-with-10-straps/w10-watch-lifestyle.jpg"
        ],
        "image_text_sections": [
            {
                "title": "১০টি ভিন্ন ডিজাইনার স্ট্র্যাপ – এক ওয়াচেই প্রতিদিন নতুন লুক",
                "subtitle": "আল্ট্রা-স্টাইলিশ ও বহুমুখী ফ্যাশন কম্বো",
                "image_url": "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/10-in-1-w10-smart-watch-with-10-straps/w10-watch-straps.jpg",
                "content": "ক্যাজুয়াল লুক, জিমের স্পোর্টি ভাইব কিংবা ফর্মাল মিটিং—প্রতিটি মুহূর্তের সাথে ম্যাচ করে ঘড়িটির লুক পরিবর্তন করুন অনায়াসে। বক্সে থাকা ১০টি ভিন্ন ডিজাইনার স্ট্র্যাপ আপনাকে দেয় সীমাহীন স্টাইলিংয়ের স্বাধীনতা।",
                "image_position": "right",
                "display_position": "after_description",
                "button_text": "অর্ডার করতে চাই",
                "button_url": "#order-form"
            },
            {
                "title": "হাই-ডেফিনিশন ডিসপ্লে ও রিয়েল-টাইম ফিটনেস ট্র্যাকিং",
                "subtitle": "স্মার্ট লাইফস্টাইল ও প্রিমিয়াম মেটাল কেস",
                "image_url": "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/10-in-1-w10-smart-watch-with-10-straps/w10-watch-lifestyle.jpg",
                "content": "রোদেও পরিষ্কার দেখা যায় এমন ব্রাইট ডিসপ্লে এবং প্রিসিশন অ্যালয় কেসিং ঘড়িটিকে করে তোলে দীর্ঘস্থায়ী ও চোখ ধাঁধানো। দৈনন্দিন পদক্ষেপ, অ্যাক্টিভিটি ট্র্যাকিং এবং সময়ের নিখুঁত সঙ্গী এই আধুনিক স্মার্টওয়াচ।",
                "image_position": "left",
                "display_position": "after_description",
                "button_text": "এখনই কিনুন",
                "button_url": "#order-form"
            }
        ]
    },

    # 2. Milk Frother
    {
        "name": "ওয়ারলেস রিচার্জেবল ইলেকট্রিক মিল্ক ফ্রোথার ও এগ বিটার (৭ ওয়াট পাওয়ারফুল মোটর, টাইপ-সি)",
        "slug": "wireless-milk-frother-7w-rechargeable-type-c-electric-foam-maker",
        "price": 799,
        "compare_at_price": 1699,
        "purchase_price": 380,
        "stock_quantity": 50,
        "status": "published",
        "categories": ["Kitchen Appliances", "Coffee & Tea Accessories", "Kitchen Accessories"],
        "tags": ["মিল্ক ফ্রোথার", "কফি মেকার", "এগ বিটার", "Milk Frother", "Hand Mixer", "Electric Whisk", "Type-C Rechargeable"],
        "description": "ক্যাফে স্টাইলের ক্রিমি ক্যাপুচিনো বা লাতে তৈরি করুন ঘরে বসেই মাত্র কয়েক সেকেন্ডে! ৭ ওয়াটের হাই-স্পিড কপার মোটর এবং রিচার্জেবল টাইপ-সি ব্যাটারি সমৃদ্ধ এই ওয়্যারলেস মিল্ক ফ্রোথার দিয়ে দুধের পারফেক্ট ফোম, ডিম ফেটানো এবং বেবি ফুড অনায়াসে তৈরি করুন।",
        "content": """<h3>ওয়ারলেস রিচার্জেবল ইলেকট্রিক মিল্ক ফ্রোথার – ক্যাফে কোয়ালিটি ফোম এখন নিজের ঘরেই</h3>
<p>ঘরে বসেই নামীদামী কফি শপের মতো ঘন, মসৃণ ও ফোমি ক্যাপুচিনো, লাতে বা হট চকলেট বানাতে চান? <strong>Wireless 7W Rechargeable Electric Milk Frother</strong> আপনার রান্নাঘরের কাজকে করবে সহজ ও আনন্দদায়ক। এটি এমন এক কমপ্যাক্ট পোর্টেবল মিক্সার যা কোনো ব্যাটারি পরিবর্তনের ঝামেলা ছাড়াই টাইপ-সি চার্জিংয়ে চলে দীর্ঘক্ষণ।</p>

<h4>প্রধান বৈশিষ্ট্য ও সুবিধাসমূহ:</h4>
<ul>
<li><strong>৭ ওয়াট হাই-স্পিড পিওর কপার মোটর:</strong> শক্তিশালী মোটরের তীব্র ঘূর্ণনে মাত্র ১৫ থেকে ২০ সেকেন্ডের মধ্যে তৈরি হয় সুপার ক্রিমি ও ঘন দুধের ফেনা (Micro-Foam)।</li>
<li><strong>রিচার্জেবল টাইপ-সি পোর্ট:</strong> বারবার ব্যাটারি কেনার খরচ নেই! আধুনিক টাইপ-সি কেবলের সাহায্যে মোবাইল চার্জার বা পাওয়ার ব্যাংক দিয়ে সহজেই চার্জ করে নিতে পারবেন।</li>
<li><strong>ডুয়াল ফুড-গ্রেড স্টেইনলেস স্টিল হুইস্ক:</strong> প্যাকেজে থাকছে দুটি আলাদা অ্যাটাচমেন্ট—কফি ও দুধের ফোমের জন্য স্প্রিং ফ্রোদার হেড এবং ডিম ফেটানো বা প্রোটিন শেক বানানোর জন্য বেলুন হুইস্ক।</li>
<li><strong>সহজ অপারেশন ও এক-ক্লিক সুইচ:</strong> এর এর্গোনোমিক গ্রিপ হাতে ধরতে আরামদায়ক এবং সিঙ্গেল পুশ বাটনেই গতি নিয়ন্ত্রণ করা যায়।</li>
<li><strong>সহজ পরিষ্কারযোগ্য:</strong> স্টেইনলেস স্টিল হুইস্কটি সহজেই আলাদা করে পানির নিচে ধুয়ে পরিষ্কার করা যায়।</li>
</ul>

<h4>টেকনিক্যাল স্পেসিফিকেশন:</h4>
<table>
<tr><td><strong>মডেল</strong></td><td>Prestige MCID-130 Rechargeable Frother</td></tr>
<tr><td><strong>মোটর পাওয়ার</strong></td><td>7 Watt High Torque Motor</td></tr>
<tr><td><strong>ব্যাটারি টাইপ</strong></td><td>ইন-বিল্ট রিচার্জেবল লিথিয়াম ব্যাটারি</td></tr>
<tr><td><strong>চার্জিং পোর্ট</strong></td><td>USB Type-C ফাস্ট চার্জিং</td></tr>
<tr><td><strong>ম্যাটেরিয়াল</strong></td><td>প্রিমিয়াম ব্রাশড স্টেইনলেস স্টিল + ফুড-গ্রেড সিলিকন</td></tr>
<tr><td><strong>ব্যবহারের ক্ষেত্র</strong></td><td>কফি, দুধের ফোম, ডিম বিটিং, বেবি সিরিয়াল, লাচ্ছি, মাচা টি</td></tr>
<tr><td><strong>প্যাকেজে যা যা থাকছে</strong></td><td>১x ফ্রোথার হ্যান্ডেল, ১x স্প্রিং ফ্রোদার হেড, ১x বেলুন এগ হুইস্ক, ১x টাইপ-সি কেবল</td></tr>
</table>

<h4>ব্যবহারের নিয়ম:</h4>
<p>হালকা গরম দুধ কাপে নিয়ে ফ্রোথারের স্প্রিং হেডটি তরলের ভেতরে প্রবেশ করান। পাওয়ার বাটন চেপে ধরে আলতো করে ঘোরালেই নিমিষে তৈরি হয়ে যাবে পারফেক্ট ক্যাফে-স্টাইল ক্রিমি ফোম।</p>""",
        "image_urls": [
            "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/wireless-milk-frother-7w-rechargeable-type-c-electric-foam-maker/frother-main.jpg",
            "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/wireless-milk-frother-7w-rechargeable-type-c-electric-foam-maker/frother-features.jpg",
            "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/wireless-milk-frother-7w-rechargeable-type-c-electric-foam-maker/frother-lifestyle.jpg"
        ],
        "image_text_sections": [
            {
                "title": "মাত্র ২০ সেকেন্ডে ক্যাফে-স্টাইলের ঘন সিল্কি কফি ফোম",
                "subtitle": "৭ ওয়াট হাই-স্পিড পিওর কপার মোটর",
                "image_url": "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/wireless-milk-frother-7w-rechargeable-type-c-electric-foam-maker/frother-lifestyle.jpg",
                "content": "সকালের কফিকে করুন বিলাসবহুল ক্যাফে কোয়ালিটির! ৭ ওয়াটের শক্তিশালী মোটর চোখের পলকে দুধকে রূপান্তর করে পারফেক্ট ভেলভেটি ফোমে। ক্যাপুচিনো, লাতে বা হট চকলেটের স্বাদ বাড়বে দ্বিগুণ।",
                "image_position": "right",
                "display_position": "after_description",
                "button_text": "অর্ডার করতে চাই",
                "button_url": "#order-form"
            },
            {
                "title": "ডুয়াল স্টেইনলেস স্টিল হুইস্ক ও সুবিধাজনক টাইপ-সি চার্জিং",
                "subtitle": "মাল্টি-পারপাস হুইস্কিং ও বেবি ফুড মেকার",
                "image_url": "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/wireless-milk-frother-7w-rechargeable-type-c-electric-foam-maker/frother-features.jpg",
                "content": "শুধু কফি নয়, ডিম ফেটানো, পুষ্টিকর বেবি ফুড তৈরি কিংবা প্রোটিন শেক ব্লেন্ড করার জন্য এটি একটি অল-ইন-ওয়ান হ্যান্ড মিক্সার। টাইপ-সি ফাস্ট চার্জিং সুবিধা থাকায় ব্যবহার করা যায় দীর্ঘ সময়।",
                "image_position": "left",
                "display_position": "after_description",
                "button_text": "এখনই কিনুন",
                "button_url": "#order-form"
            }
        ]
    },

    # 3. Blood Pressure Monitor
    {
        "name": "ডিজিটাল অটোমেটিক আপার আর্ম ব্লাড প্রেশার মনিটর ও বিপি মেশিন (এলসিডি ডিসপ্লে ও ডব্লিউএইচও ইন্ডিকেটর)",
        "slug": "fully-automatic-digital-blood-pressure-monitor-arm-style-bp-machine",
        "price": 890,
        "compare_at_price": 1999,
        "purchase_price": 450,
        "stock_quantity": 50,
        "status": "published",
        "categories": ["Health & Medical", "Personal Care", "Medical Devices"],
        "tags": ["ব্লাড প্রেশার মেশিন", "বিপি মনিটর", "Digital BP Monitor", "Blood Pressure Machine", "Arm Style BP", "Health Device"],
        "description": "ঘরে বসেই নির্ভুল ও সহজে রক্তচাপ পরিমাপের জন্য আধুনিক ডিজিটাল আর্ম-স্টাইল বিপি মেশিন! এক ক্লিকে স্বয়ংক্রিয় রিডিং, বড় স্পষ্ট এলসিডি ডিসপ্লে, ডব্লিউএইচও কালার কোডেড প্রেসার লেভেল ইন্ডিকেটর এবং কমফোর্টেবল অ্যাডজাস্টেবল আর্ম কাফ।",
        "content": """<h3>ডিজিটাল অটোমেটিক আপার আর্ম ব্লাড প্রেশার মনিটর – সুস্থ পরিবারের বিশ্বস্ত স্বাস্থ্য সহকারী</h3>
<p>পরিবারের সদস্যদের নিয়মিত রক্তচাপ ও পালস রেট পরিমাপের জন্য হাসপাতালে যাওয়ার ঝামেলা আর নয়। <strong>Fully Automatic Digital Arm-Style BP Monitor</strong> দিয়ে ঘরে বসেই যেকোনো সময় মাত্র এক ক্লিকে সঠিক রক্তচাপ পরিমাপ করুন। বয়োজ্যেষ্ঠদের জন্যও এটি পরিচালনা করা অত্যন্ত সহজ ও নিরাপদ।</p>

<h4>প্রধান বৈশিষ্ট্য ও সুবিধাসমূহ:</h4>
<ul>
<li><strong>সম্পূর্ণ স্বয়ংক্রিয় ও নির্ভুল পরিমাপ:</strong> মাইক্রোকম্পিউটার ইন্টেলিজেন্ট চিপ প্রযুক্তি থাকায় স্বয়ংক্রিয়ভাবে আর্ম কাফ ফুলিয়ে রক্তচাপ (সিসটোলিক ও ডায়াস্টোলিক) এবং পালস রেট নির্ভুলভাবে নির্ণয় করে।</li>
<li><strong>বড় স্পষ্ট এলসিডি ডিসপ্লে:</strong> চশমা ছাড়াও বয়স্ক ব্যক্তিরা সহজে রিডিং পড়তে পারবেন এমন বড় ফন্টের পরিষ্কার স্ক্রিন।</li>
<li><strong>WHO কালার-কোডেড ইন্ডিকেটর:</strong> স্ক্রিনের পাশে রয়েছে বিশ্ব স্বাস্থ্য সংস্থার (WHO) স্ট্যান্ডার্ড কালার বার (সবুজ = স্বাভাবিক, হলুদ = মধ্যম, লাল = উচ্চ রক্তচাপ), যা দেখে সাধারণ মানুষও সহজে প্রেশারের অবস্থা বুঝতে পারেন।</li>
<li><strong>অ্যাডজাস্টেবল আর্ম কাফ (২২-৩২ সেমি):</strong> নরম ও ত্বকের জন্য আরামদায়ক কাফ, যা যেকোনো সাধারণ বা মাঝারি বাহুর সাথে পারফেক্টলি ফিট হয়।</li>
<li><strong>ডুয়াল পাওয়ার অপশন:</strong> সাধারণ ব্যাটারির পাশাপাশি ইউএসবি ক্যাবলের মাধ্যমে পাওয়ার কানেক্ট করেও চালানো যায়।</li>
<li><strong>মেমোরি স্টোরেজ:</strong> পূর্ববর্তী রিডিং সংরক্ষণ করে রাখে, ফলে ডাক্তারের সাথে পরামর্শের সময় পূর্বের প্রেশার চার্ট দেখানো সহজ হয়।</li>
</ul>

<h4>টেকনিক্যাল স্পেসিফিকেশন:</h4>
<table>
<tr><td><strong>প্রোডাক্টের ধরন</strong></td><td>Upper Arm Electronic Blood Pressure Monitor</td></tr>
<tr><td><strong>ডিসপ্লে টাইপ</strong></td><td>লার্জ ডিজিটাল এলসিডি ডিসপ্লে</td></tr>
<tr><td><strong>পরিমাপ পদ্ধতি</strong></td><td>অসিলোমেট্রিক স্টেপ ডিটেকশন (Oscillometric)</td></tr>
<tr><td><strong>প্রেসার রেঞ্জ</strong></td><td>0 - 299 mmHg (±3 mmHg নিখুঁততা)</td></tr>
<tr><td><strong>পালস রেঞ্জ</strong></td><td>40 - 199 বিট/মিনিট (±5% নির্ভুলতা)</td></tr>
<tr><td><strong>কাফ সাইজ</strong></td><td>২২ থেকে ৩২ সেমি অ্যাডজাস্টেবল কাফ</td></tr>
<tr><td><strong>পাওয়ার সোর্স</strong></td><td>USB DC 5V / 4x AAA ব্যাটারি</td></tr>
<tr><td><strong>প্যাকেজে যা যা থাকছে</strong></td><td>১x বিপি মনিটর মেশিন, ১x আরামদায়ক আর্ম কাফ, ১x ইউএসবি ক্যাবল, ১x ইউজার গাইড</td></tr>
</table>

<h4>ব্যবহারের সঠিক নিয়মাবলী:</h4>
<p>১. পরিমাপের আগে ৫ মিনিট শান্ত হয়ে চেয়ারে সোজা হয়ে বসুন।<br>
২. কাফটি বাম বাহুতে কনুইয়ের ১-২ সেমি ওপরে এমনভাবে বাঁধুন যেন তা হৃদপিণ্ডের সমান উচ্চতায় থাকে।<br>
৩. মেশিনের ‘START/STOP’ বাটন চাপুন এবং পরিমাপ চলাকালীন কথা বলা বা নড়াচড়া করা থেকে বিরত থাকুন।<br>
৪. কয়েক সেকেন্ডেই স্ক্রিনে নির্ভুল সিসটোলিক, ডায়াস্টোলিক ও নাড়ির স্পন্দন ভেসে উঠবে।</p>""",
        "image_urls": [
            "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/fully-automatic-digital-blood-pressure-monitor-arm-style-bp-machine/bp-main.jpg",
            "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/fully-automatic-digital-blood-pressure-monitor-arm-style-bp-machine/bp-features.jpg",
            "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/fully-automatic-digital-blood-pressure-monitor-arm-style-bp-machine/bp-lifestyle.jpg"
        ],
        "image_text_sections": [
            {
                "title": "পরিবারের বয়োজ্যেষ্ঠদের জন্য সহজে এক-ক্লিকে রক্তচাপ পরিমাপ",
                "subtitle": "মাইক্রোকম্পিউটার ইন্টেলিজেন্ট প্রিসিশন সেন্সর",
                "image_url": "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/fully-automatic-digital-blood-pressure-monitor-arm-style-bp-machine/bp-lifestyle.jpg",
                "content": "ক্লিনিক বা ফার্মেসিতে দৌড়াদৌড়ির ঝামেলা ছাড়া ঘরে বসেই বাবা-মা কিংবা নিজের স্বাস্থ্য পরীক্ষা করুন নিয়মিত। এক ক্লিকে স্বয়ংক্রিয় পরিমাপ প্রযুক্তি বয়স্কদের জন্যও এটি ব্যবহার করা অত্যন্ত সহজ করে তুলেছে।",
                "image_position": "right",
                "display_position": "after_description",
                "button_text": "অর্ডার করতে চাই",
                "button_url": "#order-form"
            },
            {
                "title": "বড় স্পষ্ট এলসিডি স্ক্রিন ও কালার-কোডেড ডব্লিউএইচও ইন্ডিকেটর",
                "subtitle": "২২-৩২ সেমি অ্যাডজাস্টেবল আর্ম কাফ ও ডুয়াল পাওয়ার",
                "image_url": "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/fully-automatic-digital-blood-pressure-monitor-arm-style-bp-machine/bp-features.jpg",
                "content": "উচ্চ রক্তচাপ বা লো প্রেশার বুঝতে আর বিশেষজ্ঞ হতে হবে না। স্ক্রিনের বিশ্ব স্বাস্থ্য সংস্থা নির্দেশিত কালার বার দেখেই রক্তচাপের অবস্থা বুঝে নেওয়া যায় সহজে। সাথে রয়েছে আরামদায়ক নরম কাফ ও ইউএসবি পাওয়ার সাপোর্ট।",
                "image_position": "left",
                "display_position": "after_description",
                "button_text": "এখনই কিনুন",
                "button_url": "#order-form"
            }
        ]
    },

    # 4. Heating Fan / Space Heater
    {
        "name": "পোর্টেবল ইলেকট্রিক রুম হিটিং ফ্যান – অ্যাডজাস্টেবল এয়ারফ্লো সিরামিক স্পেস হিটার",
        "slug": "portable-electric-heating-fan-adjustable-airflow-space-heater",
        "price": 1290,
        "compare_at_price": 2190,
        "purchase_price": 650,
        "stock_quantity": 50,
        "status": "published",
        "categories": ["Home Appliances", "Heaters", "Winter Essentials"],
        "tags": ["রুম হিটার", "হিটিং ফ্যান", "স্পেস হিটার", "Electric Room Heater", "Heating Fan", "PTC Space Heater", "Winter Heater"],
        "description": "শীতের তীব্রতায় ঘর, অফিস ডেস্ক বা পড়ার টেবিল রাখুন আরামদায়ক ও উষ্ণ! দ্রুত গরম করার পিটিসি সিরামিক হিটিং এলিমেন্ট, অ্যাডজাস্টেবল থার্মোস্ট্যাট নব ও ফ্যান স্পিড কন্ট্রোল এবং ওভারহিট সেফটি প্রোটেকশন সমৃদ্ধ কমপ্যাক্ট স্পেস হিটার।",
        "content": """<h3>পোর্টেবল ইলেকট্রিক রুম হিটিং ফ্যান – কনকনে শীতে ব্যক্তিগত উষ্ণতার নিশ্চয়তা</h3>
<p>শীতকালে পড়ার টেবিলে বসে পড়ালেখা করা, অফিসে কাজ করা বা ঘুমানোর সময় ঠান্ডা বাতাস কাবু করে ফেলে? আপনার ব্যক্তিগত রুম, বেডরুম কিংবা ওয়ার্কস্টেশনকে দ্রুত আরামদায়ক উষ্ণতায় ভরিয়ে দিতে নিয়ে এলো <strong>Portable Desktop Electric Heating Fan</strong>। কম বিদ্যুৎ খরচে সর্বোচ্চ হিটিং পারফরম্যান্সের এক অসাধারণ হিটার।</p>

<h4>প্রধান বৈশিষ্ট্য ও সুবিধাসমূহ:</h4>
<ul>
<li><strong>ইনস্ট্যান্ট র‌্যাপিড হিটিং:</strong> সুইচ অন করার মাত্র ৩ সেকেন্ডের মধ্যে শক্তিশালী সিরামিক হিটিং এলিমেন্ট গরম বাতাস ছড়ানো শুরু করে।</li>
<li><strong>অ্যাডজাস্টেবল এয়ারফ্লো ও থার্মোস্ট্যাট নব:</strong> ডুয়াল রোটারি নব দিয়ে পছন্দমতো তাপমাত্রা ও বাতাসের স্পিড (লো/হাই/কুল ফ্যান) নিয়ন্ত্রণ করা যায়।</li>
<li><strong>কমপ্যাক্ট ও বহনযোগ্য হ্যান্ডি সাইজ:</strong> যেকোনো ডেস্ক, সাইড টেবিল বা মেঝেতে খুব কম জায়গা দখল করে। লাইটওয়েট হওয়ায় সহজে এক রুম থেকে অন্য রুমে নিয়ে যাওয়া যায়।</li>
<li><strong>মাল্টি-লেভেল সেফটি প্রোটেকশন:</strong> বিল্ট-ইন অটোমেটিক ওভারহিট প্রোটেকশন রয়েছে—অতিরিক্ত গরম হলে নিজে থেকেই বন্ধ হয়ে যায়, ফলে শর্ট সার্কিট বা অগ্নিকাণ্ডের কোনো ভয় থাকে না।</li>
<li><strong>নিরাপদ ফ্রন্ট মেশ গ্রিল:</strong> শিশুদের নিরাপত্তা নিশ্চিত করতে সামনের অংশে রয়েছে হিট-রেজিস্ট্যান্ট সেফটি নেট, যা দুর্ঘটনাজনিত স্পর্শ প্রতিরোধ করে।</li>
<li><strong>শব্দহীন নিরিবিলি অপারেশন:</strong> অত্যন্ত মৃদু শব্দে চলে, যা আপনার ঘুম বা মনোযোগে বিন্দুমাত্র বিঘ্ন ঘটায় না।</li>
</ul>

<h4>টেকনিক্যাল স্পেসিফিকেশন:</h4>
<table>
<tr><td><strong>মডেল</strong></td><td>Portable Desktop Heating Fan</td></tr>
<tr><td><strong>হিটিং প্রযুক্তি</strong></td><td>PTC Fast Ceramic Heating Element</td></tr>
<tr><td><strong>কন্ট্রোল মোড</strong></td><td>ডুয়াল রোটারি নব (থার্মোস্ট্যাট + ফ্যান স্পিড)</td></tr>
<tr><td><strong>অপারেটিং ভোল্টেজ</strong></td><td>220V AC, 50Hz</td></tr>
<tr><td><strong>নিরাপত্তা ফিচারস</strong></td><td>অটোমেটিক ওভারহিট শাট-অফ, ফায়ারপ্রুফ বডি</td></tr>
<tr><td><strong>উপযুক্ত স্থান</strong></td><td>বেডরুম, পড়ার টেবিল, অফিস কেবিন, পার্সোনাল ওয়ার্কস্পেস</td></tr>
<tr><td><strong>প্যাকেজে যা যা থাকছে</strong></td><td>১x ইলেকট্রিক হিটিং ফ্যান, ১x পাওয়ার কেবল, ১x ইউজার গাইড</td></tr>
</table>

<h4>ব্যবহার বিধি:</h4>
<p>১. হিটারটি একটি শক্ত সমতল টেবিল বা মেঝেতে রাখুন।<br>
২. পাওয়ার প্লাগ যুক্ত করে টেম্পারেচার নব আপনার সুবিধাজনক মাত্রায় ঘুরিয়ে দিন।<br>
৩. কিছুক্ষণের মধ্যেই পুরো রুমে ছড়িয়ে পড়বে মিষ্টি আরামদায়ক উষ্ণতা।</p>""",
        "image_urls": [
            "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/portable-electric-heating-fan-adjustable-airflow-space-heater/heater-main.jpg",
            "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/portable-electric-heating-fan-adjustable-airflow-space-heater/heater-features.jpg",
            "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/portable-electric-heating-fan-adjustable-airflow-space-heater/heater-lifestyle.jpg"
        ],
        "image_text_sections": [
            {
                "title": "মাত্র ৩ সেকেন্ডে দ্রুত ঘরজুড়ে ছড়ায় প্রশান্তিময় উষ্ণতা",
                "subtitle": "অ্যাডভান্সড পিটিসি সিরামিক হিটিং প্রযুক্তি",
                "image_url": "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/portable-electric-heating-fan-adjustable-airflow-space-heater/heater-lifestyle.jpg",
                "content": "কনকনে শীতের দিনে ঠান্ডা হাত-পা ও ঠান্ডাজনিত অস্বস্তি দূর করুন অনায়াসে। শক্তিশালী হিটিং ফ্যানের মাধ্যমে মৃদু উষ্ণ হাওয়া ছড়িয়ে পড়ে দ্রুত, যা কাজের টেবিলে দেয় পরম তৃপ্তি ও স্বস্তি।",
                "image_position": "right",
                "display_position": "after_description",
                "button_text": "অর্ডার করতে চাই",
                "button_url": "#order-form"
            },
            {
                "title": "থার্মোস্ট্যাট টেম্পারেচার কন্ট্রোল ও মাল্টি-লেভেল সেফটি প্রোটেকশন",
                "subtitle": "নিরাপদ মেটালিক হানিকম্ব গ্রিল ও কমপ্যাক্ট ডিজাইন",
                "image_url": "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/portable-electric-heating-fan-adjustable-airflow-space-heater/heater-features.jpg",
                "content": "স্বাচ্ছন্দ্যে ব্যবহার নিশ্চিত করতে রয়েছে অ্যাডজাস্টেবল নব ও অটোমেটিক ওভারহিট শাট-অফ সুবিধা। সামনের সুরক্ষামূলক জালির কারণে এটি শিশু বা বয়স্কদের ঘরে ব্যবহারের জন্যও শতভাগ নিরাপদ।",
                "image_position": "left",
                "display_position": "after_description",
                "button_text": "এখনই কিনুন",
                "button_url": "#order-form"
            }
        ]
    }
]

for idx, p in enumerate(products, 1):
    slug_name = p["slug"]
    print(f"[{idx}/4] Uploading {slug_name}...")
    req = urllib.request.Request(
        "https://jhurio.com/api/products",
        data=json.dumps(p).encode("utf-8"),
        headers={
            "Authorization": "Bearer prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c",
            "Content-Type": "application/json",
            "Accept": "application/json",
            "User-Agent": "Mozilla/5.0"
        },
        method="POST"
    )
    try:
        with urllib.request.urlopen(req) as resp:
            print(f"  Status: {resp.status} - SUCCESS")
            res = json.loads(resp.read().decode("utf-8"))
            print(f"  Created Product ID: {res.get('product', {}).get('id')}")
    except urllib.error.HTTPError as e:
        print(f"  HTTPError {e.code}: {e.read().decode('utf-8')[:300]}")
    except Exception as e:
        print(f"  Error: {e}")
    time.sleep(1)

print("Batch upload finished.")
