import urllib.request
import json
import time

products = [
    # 1. Hot Plate
    {
        "name": "স্টার শেফ জেএক্স-১০১০এ ইলেকট্রিক হট প্লেট চুলা (১০০০ ওয়াট, ৫-ধাপ থার্মোস্ট্যাট কন্ট্রোল)",
        "slug": "star-chef-jx-1010a-electric-hot-plate-1000w",
        "price": 1890,
        "compare_at_price": 2590,
        "purchase_price": 950,
        "stock_quantity": 50,
        "status": "published",
        "categories": ["Kitchen Appliances", "Cooktops & Burners", "Kitchen Accessories"],
        "tags": ["ইলেকট্রিক চুলা", "হট প্লেট", "Star Chef", "Electric Hot Plate", "Cooking Stove", "1000W Stove"],
        "description": "গ্যাস সিলিন্ডারের ঝামেলা ছাড়াই যেকোনো স্থানে দ্রুত ও নিরাপদে রান্না করুন! স্টার শেফ ১০০০ ওয়াটের পাওয়ারফুল কাস্ট-আয়রন হট প্লেট চুলায় রয়েছে ১-৫ ধাপের অ্যাডজাস্টেবল থার্মোস্ট্যাট নব, ওভারহিট সেফটি প্রোটেকশন এবং নন-স্লিপ গ্রিপ।",
        "content": """<h3>স্টার শেফ জেএক্স-১০১০এ ইলেকট্রিক হট প্লেট – আধুনিক ও ঝামেলামুক্ত রান্নার সঙ্গী</h3>
<p>গ্যাসের স্বল্পতা, সিলিন্ডার শেষ হয়ে যাওয়া কিংবা ব্যাচেলর বা ট্রাভেলিংয়ের সময় রান্নার ঝামেলায় আর পড়তে হবে না। <strong>Star Chef JX-1010A 1000W Electric Hot Plate</strong> দিয়ে বিদ্যুৎ সংযোগেই তৈরি করে ফেলুন ভাত, তরকারি, চা, কফি বা গরম খাবার। ফ্ল্যাট তলাযুক্ত যেকোনো স্টিল, অ্যালুমিনিয়াম, সিরামিক বা কাঁচের পাত্রেই এতে রান্না করা যায়।</p>

<h4>প্রধান বৈশিষ্ট্য ও সুবিধাসমূহ:</h4>
<ul>
<li><strong>১০০০ ওয়াটের শক্তিশালী হিটিং প্লেট:</strong> ভারী ও মজবুত সলিড কাস্ট আয়রন বার্নার যা দ্রুত গরম হয় এবং পাত্রের চারপাশে সমানভাবে তাপ সঞ্চালন করে।</li>
<li><strong>১ থেকে ৫ ধাপের অ্যাডজাস্টেবল থার্মোস্ট্যাট:</strong> রোটারি নবের মাধ্যমে বিভিন্ন রান্নার জন্য প্রয়োজনীয় তাপমাত্রা (হালকা গরম রাখা থেকে শুরু করে ফুটন্ত রান্না) সহজেই নিয়ন্ত্রণ করা যায়।</li>
<li><strong>বিল্ট-ইন ওভারহিট প্রোটেকশন:</strong> অটোমেটিক থার্মাল শাট-অফ সুবিধা রয়েছে, যা অতিরিক্ত উত্তপ্ত হওয়া প্রতিরোধ করে বিদ্যুৎ সাশ্রয় ও সম্পূর্ণ নিরাপত্তা নিশ্চিত করে।</li>
<li><strong>নন-স্টিক মেটালিক ফিনিশ ও সহজ পরিষ্কার:</strong> মসৃণ বডি সহজে কাপড় দিয়ে মুছে পরিষ্কার রাখা যায় এবং জং বা মরিচা ধরে না।</li>
<li><strong>নন-স্লিপ রাবার ফিট:</strong> নিচের শক্ত রাবার গ্রিপ টেবিল বা কিচেন স্ল্যাবে চুলাটিকে পিছলে যাওয়া থেকে রক্ষা করে।</li>
</ul>

<h4>টেকনিক্যাল স্পেসিফিকেশন:</h4>
<table>
<tr><td><strong>ব্র্যান্ড ও মডেল</strong></td><td>Star Chef JX-1010A</td></tr>
<tr><td><strong>পাওয়ার রেটিং</strong></td><td>1000 Watt High Power</td></tr>
<tr><td><strong>ভোল্টেজ</strong></td><td>220V - 240V AC, 50/60Hz</td></tr>
<tr><td><strong>বার্নার টাইপ</strong></td><td>সলিড কাস্ট আয়রন হিটিং ডিস্ক</td></tr>
<tr><td><strong>হিট কন্ট্রোল</strong></td><td>১-৫ লেভেল অ্যাডজাস্টেবল থার্মোস্ট্যাট</td></tr>
<tr><td><strong>ইন্ডিকেটর</strong></td><td>পাওয়ার অন/অফ রেড ইন্ডিকেটর লাইট</td></tr>
<tr><td><strong>উপযুক্ত পাত্র</strong></td><td>স্টিল, অ্যালুমিনিয়াম, সিরামিক, কপার ও গ্লাস পট</td></tr>
<tr><td><strong>প্যাকেজে থাকছে</strong></td><td>১x স্টার শেফ হট প্লেট, ৪x এক্সট্রা রাবার ফিট, ১x ইউজার গাইড</td></tr>
</table>

<h4>ব্যবহারের নিয়মাবলী:</h4>
<p>১. চুলাটি সমতল জায়গায় রেখে পাওয়ার সকেটে প্লাগ ইন করুন।<br>
২. নবের সাহায্যে ১ থেকে ৫ এর মধ্যে প্রয়োজনমতো তাপমাত্রা নির্বাচন করুন।<br>
৩. ফ্ল্যাট তলাযুক্ত পাত্র বার্নারের ওপর বসিয়ে নিরাপদ ও স্বাচ্ছন্দ্যময় রান্না উপভোগ করুন।</p>""",
        "image_urls": [
            "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/star-chef-jx-1010a-electric-hot-plate-1000w/hotplate-main.jpg",
            "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/star-chef-jx-1010a-electric-hot-plate-1000w/hotplate-features.jpg",
            "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/star-chef-jx-1010a-electric-hot-plate-1000w/hotplate-lifestyle.jpg"
        ],
        "image_text_sections": [
            {
                "title": "গ্যাস ছাড়াই দ্রুত রান্নার ১০০০ ওয়াট শক্তিশালী কাস্ট আয়রন বার্নার",
                "subtitle": "ইউনিফর্ম হিট ডিস্ট্রিবিউশন প্রযুক্তি",
                "image_url": "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/star-chef-jx-1010a-electric-hot-plate-1000w/hotplate-lifestyle.jpg",
                "content": "গ্যাসের সমস্যাকে চিরতরে বিদায় দিন! ১০০০ ওয়াটের সলিড কাস্ট আয়রন প্লেট দ্রুততম সময়ে খাবার রান্না ও গরম করে। সাধারণ হাঁড়ি-পাতিল থেকেই শুরু করে যেকোনো ফ্ল্যাট সারফেস পাত্রে অনায়াসে রান্না করা যায়।",
                "image_position": "right",
                "display_position": "after_description",
                "button_text": "অর্ডার করতে চাই",
                "button_url": "#order-form"
            },
            {
                "title": "১ থেকে ৫ ধাপের টেম্পারেচার থার্মোস্ট্যাট ও ডুয়াল সেফটি প্রোটেকশন",
                "subtitle": "টেকসই মেটালিক বডি ও অ্যান্টি-স্লিপ রাবার ফিট",
                "image_url": "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/star-chef-jx-1010a-electric-hot-plate-1000w/hotplate-features.jpg",
                "content": "রান্নার প্রয়োজন অনুযায়ী হালকা আঁচে ডাল সেদ্ধ করা থেকে শুরু করে ফুটন্ত চা-কফি তৈরি—সবকিছুই নিয়ন্ত্রণ করুন নবের মাধ্যমে। অটোমেটিক ওভারহিট শাট-অফ ফিচার চুলাটিকে রাখে সম্পূর্ণ নিরাপদ ও দীর্ঘস্থায়ী।",
                "image_position": "left",
                "display_position": "after_description",
                "button_text": "এখনই কিনুন",
                "button_url": "#order-form"
            }
        ]
    },

    # 2. Coffee Grinder
    {
        "name": "ন্যাশনাল প্রো ১৮০০ ওয়াট ইলেকট্রিক কফি ও মসলা ব্লেন্ডার গ্রাইন্ডার (স্টেইনলেস স্টিল ব্লেড)",
        "slug": "national-pro-electric-coffee-grinder",
        "price": 999,
        "compare_at_price": 1550,
        "purchase_price": 480,
        "stock_quantity": 50,
        "status": "published",
        "categories": ["Kitchen Appliances", "Blenders & Grinders", "Coffee & Tea Accessories"],
        "tags": ["কফি গ্রাইন্ডার", "মসলা গ্রাইন্ডার", "Electric Grinder", "Spice Grinder", "Coffee Grinder", "National Pro"],
        "description": "কফি বিন, শুকনা মরিচ, হলুদ, জিরা, গোলমরিচ কিংবা বাদাম মাত্র কয়েক সেকেন্ডে নিখুঁত গুঁড়ো করুন! ন্যাশনাল প্রো ১৮০০ ওয়াটের শক্তিশালী কপার মোটর ও আল্ট্রা-শার্প স্টেইনলেস স্টিল ব্লেড দেয় দ্রুত ও মিহি ব্লেন্ডিংয়ের নিশ্চয়তা।",
        "content": """<h3>ন্যাশনাল প্রো ১৮০০ ওয়াট ইলেকট্রিক গ্রাইন্ডার – ঘরে তৈরি খাঁটি ও তাজা মসলার জাদুকরী সমাধান</h3>
<p>বাজারের ভেজাল গুঁড়া মসলা পরিহার করে চোখের সামনে তাজা ও সুগন্ধি মসলা গুঁড়ো করতে চান? <strong>National PRO 1800W Electric Coffee & Spice Grinder</strong> দিয়ে ঘরেই চোখের পলকে ব্লেন্ড করে ফেলুন কফি বিন, এলাচ, দারুচিনি, শুকনা মরিচ, জিরা, সরিষা, চালের গুঁড়া ও শক্ত বাদাম।</p>

<h4>প্রধান বৈশিষ্ট্য ও সুবিধাসমূহ:</h4>
<ul>
<li><strong>১৮০০ ওয়াটের হাই-টর্ক শক্তিশালী মোটর:</strong> উচ্চ ক্ষমতাসম্পন্ন মোটর যা শক্ত থেকে শক্ত উপাদানকেও মাত্র ৫-১০ সেকেন্ডের মধ্যে মিহি পাউডারে পরিণত করে।</li>
<li><strong>ফুড-গ্রেড স্টেইনলেস স্টিল বাটি ও ব্লেড:</strong> মরচে-রোধী প্রিমিয়াম স্টিলের তৈরি দুটি ধারালো ব্লেড ও বাটি, যা খাবারের পুষ্টি ও খাঁটি স্বাদ অক্ষুণ্ণ রাখে।</li>
<li><strong>ট্রান্সপারেন্ট পুশ-টপ সেফটি ঢাকনা:</strong> স্বচ্ছ ঢাকনা থাকায় বাইরে থেকেই ভেতরের গুঁড়ো হওয়ার মাত্রা দেখা যায়। চাপ দিয়ে ধরে রাখলে চালু হয় এবং হাত সরালে বন্ধ হয়ে যায়—১০০% নিরাপদ!</li>
<li><strong>৮৫% পর্যন্ত বিদ্যুৎ সাশ্রয়ী:</strong> দ্রুততম সময়ে গ্রাইন্ডিং সম্পন্ন করে বলে বিদ্যুৎ খরচ হয় নামমাত্র।</li>
<li><strong>সহজে বহনযোগ্য ও হ্যান্ডি সাইজ:</strong> রান্নাঘরের স্ল্যাবে খুব সামান্য জায়গা নেয় এবং সহজে পরিষ্কার করা যায়।</li>
</ul>

<h4>টেকনিক্যাল স্পেসিফিকেশন:</h4>
<table>
<tr><td><strong>ব্র্যান্ড</strong></td><td>National PRO</td></tr>
<tr><td><strong>মডেল</strong></td><td>Electric Coffee & Dry Spice Grinder</td></tr>
<tr><td><strong>মোটর পাওয়ার</strong></td><td>1800W High Speed Copper Motor</td></tr>
<tr><td><strong>ভোল্টেজ</strong></td><td>220-240V AC, 50Hz</td></tr>
<tr><td><strong>ক্যাপাসিটি</strong></td><td>Up to 5 Cups / 100g dry ingredients</td></tr>
<tr><td><strong>ব্লেড টাইপ</strong></td><td>ডাবল-উইং আল্ট্রা-শার্প স্টেইনলেস স্টিল ব্লেড</td></tr>
<tr><td><strong>উপযুক্ত উপাদান</strong></td><td>কফি বিন, হলুদ, শুকনা মরিচ, এলাচ, গোলমরিচ, বাদাম, চাল</td></tr>
</table>

<h4>ব্যবহারের নিয়ম:</h4>
<p>শুকনো মসলা বা কফি বিন বাটিতে রাখুন, স্বচ্ছ ঢাকনাটি লাগিয়ে হাত দিয়ে চেপে ধরুন। মাত্র কয়েক সেকেন্ড চালু রাখলেই পেয়ে যাবেন ফ্রেশ ও মিহি গুঁড়ো মসলা।</p>""",
        "image_urls": [
            "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/national-pro-electric-coffee-grinder/grinder-main.jpg",
            "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/national-pro-electric-coffee-grinder/grinder-features.jpg",
            "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/national-pro-electric-coffee-grinder/grinder-lifestyle.jpg"
        ],
        "image_text_sections": [
            {
                "title": "শুকনো মসলা ও কফি বিন নিমেষেই করুন পারফেক্ট ব্লেন্ড",
                "subtitle": "১৮০০ ওয়াট আল্ট্রা-পাওয়ারফুল হাই-টর্ক মোটর",
                "image_url": "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/national-pro-electric-coffee-grinder/grinder-lifestyle.jpg",
                "content": "বাজারের প্যাকেটজাত মসলার কৃত্রিম গন্ধ আর নয়! ঘরে বসেই জিরা, ধনিয়া, গরম মসলা বা কফি বিন চোখের পলকে গুঁড়ো করুন। পরিবারের সুস্বাস্থ্যের জন্য তাজা মসলার কোনো বিকল্প নেই।",
                "image_position": "right",
                "display_position": "after_description",
                "button_text": "অর্ডার করতে চাই",
                "button_url": "#order-form"
            },
            {
                "title": "ফুড-গ্রেড স্টেইনলেস স্টিল বাটি ও আল্ট্রা-শার্প ডুয়াল ব্লেড",
                "subtitle": "৮৫% এনার্জি সেভিং ও ট্রান্সপারেন্ট পুশ-টপ সেফটি ক্যাপ",
                "image_url": "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/national-pro-electric-coffee-grinder/grinder-features.jpg",
                "content": "উচ্চমানের ফুড-গ্রেড স্টিল চেম্বার ও স্বচ্ছ ঢাকনা ব্যবহারের নিরাপত্তা নিশ্চিত করে। ঢাকনায় আলতো চাপ দিলেই মোটর চালু হয় এবং প্রয়োজনমতো মিহি হয়ে গেলে ছেড়ে দিলেই স্বয়ংক্রিয়ভাবে বন্ধ হয়।",
                "image_position": "left",
                "display_position": "after_description",
                "button_text": "এখনই কিনুন",
                "button_url": "#order-form"
            }
        ]
    },

    # 3. Roti Maker
    {
        "name": "অ্যাফিক্স স্টেইনলেস স্টিল পুরি ও রুটি মেকার – জাম্বো সাইজ (হেভি ডিউটি প্রেসার হ্যান্ডেল)",
        "slug": "affix-s-s-puri-roti-maker-jumbo-size",
        "price": 1499,
        "compare_at_price": 1799,
        "purchase_price": 750,
        "stock_quantity": 50,
        "status": "published",
        "categories": ["Kitchen Appliances", "Kitchen Tools", "Roti & Puri Makers"],
        "tags": ["রুটি মেকার", "পুরি মেকার", "Roti Maker", "Puri Press", "Stainless Steel Roti Maker", "Affix Jumbo"],
        "description": "বেলন-পিরি দিয়ে রুটি বেলার কষ্ট আর নয়! অ্যাফিক্স প্রিমিয়াম স্টেইনলেস স্টিল জাম্বো রুটি ও পুরি মেকারের সাহায্যে মাত্র এক চাপে তৈরি করুন পারফেক্ট গোল রুটি, পুরি বা পাঁপড়। মজবুত বডি ও অ্যান্টি-স্লিপ গ্রিপ।",
        "content": """<h3>অ্যাফিক্স স্টেইনলেস স্টিল পুরি ও রুটি মেকার (জাম্বো সাইজ) – সকালের নাস্তা তৈরি হোক ঝড়ের গতিতে</h3>
<p>প্রতিদিন সকালে পরিবারের জন্য অনেকগুলো রুটি বা পুরি বেলন-পিরি দিয়ে বেলা অত্যন্ত পরিশ্রমের ও সময়সাপেক্ষ কাজ। <strong>Affix S.S. Puri & Roti Maker (Jumbo Size)</strong> আপনার রান্নাঘরের সেই কষ্ট দূর করে দেবে পুরোপুরি! কোনো বিদ্যুৎ খরচ ছাড়াই ম্যানুয়াল এক হালকা প্রেসেই পেয়ে যাবেন পারফেক্ট গোল সাইজের রুটি ও পুরি।</p>

<h4>প্রধান বৈশিষ্ট্য ও সুবিধাসমূহ:</h4>
<ul>
<li><strong>প্রিমিয়াম স্টেইনলেস স্টিল কনস্ট্রাকশন:</strong> শতভাগ মরচে-রোধী হেভি ডিউটি স্টেইনলেস স্টিল বডি যা অত্যন্ত দীর্ঘস্থায়ী ও টেকসই।</li>
<li><strong>জাম্বো সাইজ প্রেসিং প্লেট:</strong> বড় সাইজের প্লেট থাকায় সাধারণ ছোট পুরি ছাড়াও বড় সাইজের রুটি, পরোটা বা পাঁপড় অনায়াসে তৈরি করা যায়।</li>
<li><strong>সহজ প্রেসার হ্যান্ডেল ও নরম রাবার গ্রিপ:</strong> বৈজ্ঞানিক লিভারেজ ডিজাইনের হ্যান্ডেল থাকায় সামান্য চাপেই রুটি পাতলা ও গোল হয়ে ছড়িয়ে পড়ে, হাতে কোনো অতিরিক্ত বল প্রয়োগ করতে হয় না।</li>
<li><strong>অ্যান্টি-স্লিপ রাবার লেগস:</strong> নিচে শক্ত রাবার গ্রিপ থাকায় চাপ দেওয়ার সময় মেঝের উপর পিছলে যায় না।</li>
<li><strong>বিদ্যুৎ ও ব্যাটারি ফ্রি:</strong> কোনো বিদ্যুৎ বা গ্যাস সংযোগের প্রয়োজন নেই, সম্পূর্ণ পরিবেশবান্ধব ও নিরাপদ।</li>
</ul>

<h4>টেকনিক্যাল স্পেসিফিকেশন:</h4>
<table>
<tr><td><strong>ব্র্যান্ড</strong></td><td>Affix (Heart of Kitchen)</td></tr>
<tr><td><strong>মডেল</strong></td><td>S.S. Puri & Roti Maker (Jumbo Size)</td></tr>
<tr><td><strong>উপাদান</strong></td><td>ফুড-গ্রেড হেভি ডিউটি স্টেইনলেস স্টিল</td></tr>
<tr><td><strong>হ্যান্ডেল গ্রিপ</strong></td><td>এর্গোনোমিক অ্যান্টি-স্লিপ সফট রাবার গ্রিপ</td></tr>
<tr><td><strong>ব্যবহারের ক্ষেত্র</strong></td><td>রুটি, লুচি, পুরি, পাঁপড়, মোমো শিট, ফুচকা</td></tr>
<tr><td><strong>অপারেশন</strong></td><td>ম্যানুয়াল সফট হ্যান্ড প্রেস</td></tr>
</table>

<h4>ব্যবহারের সহজ পদ্ধতি:</h4>
<p>১. আটার ছোট লেচির ওপর সামান্য তেল লাগিয়ে দুই প্লেটের মাঝে রাখুন।<br>
২. হ্যান্ডেলটি ধরে একবার আলতো চাপ দিন।<br>
৩. ঢাকনা তুললেই পেয়ে যাবেন নিখুঁত গোল ও পাতলা রুটি বা পুরি, যা সরাসরি তাওয়ায় সেঁকে বা তেলে ভেজে পরিবেশন করা যায়।</p>""",
        "image_urls": [
            "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/affix-s-s-puri-roti-maker-jumbo-size/roti-main.jpg",
            "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/affix-s-s-puri-roti-maker-jumbo-size/roti-features.jpg",
            "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/affix-s-s-puri-roti-maker-jumbo-size/roti-lifestyle.jpg"
        ],
        "image_text_sections": [
            {
                "title": "বেলন-পিরির ঝামেলা ছাড়া এক চাপেই তৈরি হবে গোল ফুলকো রুটি ও পুরি",
                "subtitle": "স্মার্ট ও দ্রুত রান্নার হোমমেড সমাধান",
                "image_url": "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/affix-s-s-puri-roti-maker-jumbo-size/roti-lifestyle.jpg",
                "content": "ঘণ্টার পর ঘণ্টা দাঁড়িয়ে বেলন দিয়ে রুটি বেলার দিন শেষ! আটার লেচি রেখে হালকা চাপ দিলেই নিমিষে তৈরি হয়ে যায় গোল রুটি, পুরি বা লুচি। পরিবার বা মেহমানদারির নাস্তা তৈরিতে সময় বাঁচায় বহুগুণ।",
                "image_position": "right",
                "display_position": "after_description",
                "button_text": "অর্ডার করতে চাই",
                "button_url": "#order-form"
            },
            {
                "title": "১০০% প্রিমিয়াম অ্যান্টি-রাস্ট স্টেইনলেস স্টিল ও জাম্বো প্রেসিং সাইজ",
                "subtitle": "এর্গোনোমিক নন-স্লিপ রাবার গ্রিপ হ্যান্ডেল",
                "image_url": "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/affix-s-s-puri-roti-maker-jumbo-size/roti-features.jpg",
                "content": "মরিচাহীন ফুড-গ্রেড স্টিল এবং বড় জাম্বো ডায়ামিটার প্লেট নিশ্চিত করে দীর্ঘস্থায়ী ব্যবহার। নরম রাবার হ্যান্ডেলের কারণে সামান্য শক্তিতেই নিখুঁত পাতলা ও সমান রুটি তৈরি করা যায়।",
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
    print(f"[{idx}/3] Uploading {slug_name}...")
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

print("Batch 2 upload finished.")
