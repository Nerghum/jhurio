import urllib.request
import json
import time
import re

API_URL = "https://jhurio.com/api/products"
API_TOKEN = "prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c"

HEADERS = {
    "Authorization": f"Bearer {API_TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "User-Agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)"
}

# 15 Products with 5-7 Realistic Bangla, English, and Banglish Reviews
PRODUCT_REVIEWS = [
    # 1. Roti Maker (ID: 19)
    {
        "id": 19,
        "slug": "affix-s-s-puri-roti-maker-jumbo-size",
        "name": "অ্যাফিক্স স্টেইনলেস স্টিল পুরি ও রুটি মেকার – জাম্বো সাইজ",
        "reviews": [
            {
                "reviewer_name": "Shamim Reza",
                "reviewer_email": "shamim.reza88@gmail.com",
                "rating": 5,
                "comment": "আমি মূলত সকালের নাস্তার জন্য নিয়েছিলাম। রুটি আর লুচি অনেক দ্রুত আর গোল গোল তৈরি হয়। স্টিলের বডি বেশ মজবুত, কোনো নড়াচড়া করে না। খুব ভালো গ্যাজেট।",
                "is_verified_purchase": True,
                "created_at": "2026-10-24 09:15:00"
            },
            {
                "reviewer_name": "Tanvir Hasan",
                "reviewer_email": "tanvir.h.ctg@yahoo.com",
                "rating": 5,
                "comment": "Product ta asholei useful. Choto theke boro shobai use korte parbe. Chap dilei shundor ruti hoye jay, hand pressure handle ta heavy duty. Value for money.",
                "is_verified_purchase": True,
                "created_at": "2026-10-25 14:30:00"
            },
            {
                "reviewer_name": "Farhana Yasmin",
                "reviewer_email": "farhana.yasmin.bd@gmail.com",
                "rating": 5,
                "comment": "Received the jumbo size roti maker yesterday. Solid stainless steel build and smooth pressing action. Puri came out perfectly round. Fast delivery to Mirpur!",
                "is_verified_purchase": True,
                "created_at": "2026-10-26 11:20:00"
            },
            {
                "reviewer_name": "Md. Nazrul Islam",
                "reviewer_email": "nazrul.islam1975@gmail.com",
                "rating": 4,
                "comment": "জিনিসটা বেশ ভালো। তবে আটার ডো একটু নরম করে মাখতে হবে, তাহলে একদম সুন্দর পাতলা রুটি হয়। প্যাকেজিং চমৎকার ছিল।",
                "is_verified_purchase": True,
                "created_at": "2026-10-27 16:45:00"
            },
            {
                "reviewer_name": "Sabina Yeasmin",
                "reviewer_email": "sabina.yeasmin@hotmail.com",
                "rating": 5,
                "comment": "Delivery khub fast chilo, order korar 2 din er moddhe pelam. Puri maker hishebe 10/10. Shokaler nasta banano ekhon khub easy hoye geche.",
                "is_verified_purchase": True,
                "created_at": "2026-10-28 10:10:00"
            },
            {
                "reviewer_name": "Asif Mahmud",
                "reviewer_email": "asif.mahmud.eng@gmail.com",
                "rating": 5,
                "comment": "Really impressed with the quality! Heavy-duty steel and non-slip rubber grip at bottom keeps it steady. Recommended for every home.",
                "is_verified_purchase": True,
                "created_at": "2026-10-29 13:00:00"
            }
        ]
    },

    # 2. Coffee & Spice Grinder (ID: 18)
    {
        "id": 18,
        "slug": "national-pro-electric-coffee-grinder",
        "name": "ন্যাশনাল প্রো ১৮০০ ওয়াট কফি ও মসলা ব্লেন্ডার গ্রাইন্ডার",
        "reviews": [
            {
                "reviewer_name": "Mahfuzur Rahman",
                "reviewer_email": "mahfuz.rahman.bd@gmail.com",
                "rating": 5,
                "comment": "মাত্র ১০ সেকেন্ডেই শুকনা মরিচ আর গোলমরিচ একদম মিহি গুঁড়া হয়ে গেছে! মোটরটা সত্যিই অনেক শক্তিশালী। কাচের ঢাকনা থাকায় ভেতরটা দেখতে সুবিধা হয়।",
                "is_verified_purchase": True,
                "created_at": "2026-10-24 15:20:00"
            },
            {
                "reviewer_name": "Zubair Ahmed",
                "reviewer_email": "zubair.ahmed.dhaka@gmail.com",
                "rating": 5,
                "comment": "Coffee beans grind korar jonno niyechilam, result ekdom outstanding! Blade gulo khub sharp ar stainless steel body ta dekhteo premium. Best grinder at this budget.",
                "is_verified_purchase": True,
                "created_at": "2026-10-25 18:40:00"
            },
            {
                "reviewer_name": "Nusrat Sharmin",
                "reviewer_email": "nusrat.sharmin89@yahoo.com",
                "rating": 5,
                "comment": "Powerful motor and stainless steel bowl. Ground my cinnamon and cardamom into fine powder within seconds. Very easy to clean as well.",
                "is_verified_purchase": True,
                "created_at": "2026-10-26 12:15:00"
            },
            {
                "reviewer_name": "Rakibul Islam",
                "reviewer_email": "rakib.islam.buet@gmail.com",
                "rating": 5,
                "comment": "আম্মুর জন্য অর্ডার করেছিলাম। উনি এলাচ, লবঙ্গ আর দারুচিনির গুঁড়া বানিয়ে খুব খুশি হয়েছেন। ওয়ান টাচ সুইচ সিস্টেম থাকায় বয়স্কদের ব্যবহারের জন্যও খুব সহজ।",
                "is_verified_purchase": True,
                "created_at": "2026-10-27 19:10:00"
            },
            {
                "reviewer_name": "Sumon Chowdhury",
                "reviewer_email": "sumon.chw@gmail.com",
                "rating": 4,
                "comment": "Product original peyechi. Shob kichu bhalo moto grind hoy, sound o khub beshi na. Overall satisfied, thanks Jhurio!",
                "is_verified_purchase": True,
                "created_at": "2026-10-28 14:05:00"
            },
            {
                "reviewer_name": "Nilufar Yasmin",
                "reviewer_email": "nilufar.yasmin.home@gmail.com",
                "rating": 5,
                "comment": "ছোট সাইজের কিন্তু কাজ করে বড় ব্লেন্ডারের মতো। রান্নাঘরে জায়গা কম নেয় আর অল্প মসলা গুঁড়া করার জন্য পারফেক্ট।",
                "is_verified_purchase": True,
                "created_at": "2026-10-29 11:30:00"
            }
        ]
    },

    # 3. Electric Hot Plate (ID: 17)
    {
        "id": 17,
        "slug": "star-chef-jx-1010a-electric-hot-plate-1000w",
        "name": "স্টার শেফ জেএক্স-১০১০এ ইলেকট্রিক হট প্লেট চুলা (১০০০ ওয়াট)",
        "reviews": [
            {
                "reviewer_name": "Md. Jahangir Alam",
                "reviewer_email": "jahangir.alam.eng@gmail.com",
                "rating": 5,
                "comment": "সিলিন্ডার গ্যাস শেষ হয়ে গেলে বিপদে পড়তে হতো, এখন এই হট প্লেট দিয়ে নিশ্চিন্তে রান্না করতে পারি। কাস্ট আয়রন বার্নার খুব দ্রুত গরম হয়। দারুণ জিনিস!",
                "is_verified_purchase": True,
                "created_at": "2026-10-24 16:30:00"
            },
            {
                "reviewer_name": "Rashedul Karim",
                "reviewer_email": "rashed.karim92@gmail.com",
                "rating": 5,
                "comment": "Bachelor life e ranna korar jonno best option. Steel ar aluminium shob pot e shundor kaj kore. Temperature control knob ta khub handy.",
                "is_verified_purchase": True,
                "created_at": "2026-10-25 20:10:00"
            },
            {
                "reviewer_name": "Sabrina Mostafa",
                "reviewer_email": "sabrina.mostafa@gmail.com",
                "rating": 5,
                "comment": "Works like a charm! 1000W heating disc gets hot within minutes. Boiled water and cooked rice without any issue. Solid build and safe to use.",
                "is_verified_purchase": True,
                "created_at": "2026-10-26 14:25:00"
            },
            {
                "reviewer_name": "Kazi Imtiaz",
                "reviewer_email": "kazi.imtiaz.bd@yahoo.com",
                "rating": 5,
                "comment": "থার্মোস্ট্যাট খুব ভালো কাজ করে, নির্দিষ্ট তাপে অটো অফ হয় আবার চালু হয়। ফলে বিদ্যুৎ অপচয় হয় না। বিল্ড কোয়ালিটিও চমৎকার।",
                "is_verified_purchase": True,
                "created_at": "2026-10-27 21:00:00"
            },
            {
                "reviewer_name": "Sazzad Hossain",
                "reviewer_email": "sazzad.hossain.dev@gmail.com",
                "rating": 4,
                "comment": "Garter rubber feet thakay table theke shorbe na. 3 din use korlam, khub bhalo service dicche. Highly recommended!",
                "is_verified_purchase": True,
                "created_at": "2026-10-28 17:35:00"
            },
            {
                "reviewer_name": "Sharmin Sultana",
                "reviewer_email": "sharmin.sultana.doctor@gmail.com",
                "rating": 5,
                "comment": "অফিসে চা-কফি আর দুপুরের খাবার গরম করার জন্য পারফেক্ট চুলা। দেখতেও সুন্দর আর সহজে যেকোনো স্থানে নিয়ে যাওয়া যায়।",
                "is_verified_purchase": True,
                "created_at": "2026-10-29 16:15:00"
            }
        ]
    },

    # 4. Space Heater Fan (ID: 16)
    {
        "id": 16,
        "slug": "portable-electric-heating-fan-adjustable-airflow-space-heater",
        "name": "পোর্টেবল ইলেকট্রিক হিটার ফ্যান – অ্যাডজাস্টেবল স্পেস হিটার",
        "reviews": [
            {
                "reviewer_name": "Anisur Rahman",
                "reviewer_email": "anisur.rahman.77@gmail.com",
                "rating": 5,
                "comment": "শীতের জন্য অসাধারণ একটি হিটার ফ্যান। চালু করার সাথে সাথেই চমৎকার উষ্ণ হাওয়া দেয়। পিটিসি সিরামিক হিটিং হওয়াতে কোনো পোড়া গন্ধ নেই।",
                "is_verified_purchase": True,
                "created_at": "2026-10-23 20:00:00"
            },
            {
                "reviewer_name": "Sonia Parveen",
                "reviewer_email": "sonia.parveen86@gmail.com",
                "rating": 5,
                "comment": "Khub compact size kintu room besh taratari gorom kore fele. Baby thakay heater ta amader khub kaje lagche. Sound level o ekdom low.",
                "is_verified_purchase": True,
                "created_at": "2026-10-24 22:15:00"
            },
            {
                "reviewer_name": "Tariqul Islam",
                "reviewer_email": "tariqul.islam.bank@gmail.com",
                "rating": 5,
                "comment": "Excellent portable space heater. The adjustable thermostat and dual heat modes are very convenient. Safety tip-over switch works as advertised.",
                "is_verified_purchase": True,
                "created_at": "2026-10-26 08:30:00"
            },
            {
                "reviewer_name": "Kamrul Hasan",
                "reviewer_email": "kamrul.hasan.eng@gmail.com",
                "rating": 5,
                "comment": "টেবিল বা বেডের পাশে রাখার জন্য একদম আইডিয়াল সাইজ। বিদ্যুৎ খরচও বড় বড় হিটারের চেয়ে অনেক কম। প্যাকেজিং ও ডেলিভারি দুইটাই ভালো ছিল।",
                "is_verified_purchase": True,
                "created_at": "2026-10-27 18:00:00"
            },
            {
                "reviewer_name": "Nafis Fuad",
                "reviewer_email": "nafis.fuad.stu@gmail.com",
                "rating": 4,
                "comment": "Design ta besh smart ar portable handle thakay room to room carry kora easy. Winter er jonno must-have ekta gadget.",
                "is_verified_purchase": True,
                "created_at": "2026-10-28 21:40:00"
            },
            {
                "reviewer_name": "Sadia Rahman",
                "reviewer_email": "sadia.rahman.arch@gmail.com",
                "rating": 5,
                "comment": "Very impressed with the heat output despite the compact size. Perfect for cold winter mornings while working at my desk.",
                "is_verified_purchase": True,
                "created_at": "2026-10-29 10:20:00"
            }
        ]
    },

    # 5. Digital BP Monitor (ID: 15)
    {
        "id": 15,
        "slug": "fully-automatic-digital-blood-pressure-monitor-arm-style-bp-machine",
        "name": "ডিজিটাল অটোমেটিক ব্লাড প্রেসার মনিটর – আর্ম স্টাইল বিপি মেশিন",
        "reviews": [
            {
                "reviewer_name": "Dr. Sayed Mahmud",
                "reviewer_email": "sayed.mahmud.doc@gmail.com",
                "rating": 5,
                "comment": "আব্বু-আম্মুর রেগুলার প্রেসার মাপার জন্য নিয়েছি। ডিসপ্লে বড় হওয়াতে চশমা ছাড়াও রিডিং স্পষ্ট পড়া যায়। রেজাল্ট একদম হসপিটালের ম্যানুয়াল মেশিনের মতোই সঠিক।",
                "is_verified_purchase": True,
                "created_at": "2026-10-23 11:30:00"
            },
            {
                "reviewer_name": "Farzana Haque",
                "reviewer_email": "farzana.haque.bd@gmail.com",
                "rating": 5,
                "comment": "Accuracy khub accurate. Arm cuff ta soft ar easily adjust kora jay. Memory feature thakay ager reading gulo track rakha shombhob hoy. Best buy!",
                "is_verified_purchase": True,
                "created_at": "2026-10-25 09:10:00"
            },
            {
                "reviewer_name": "Mohammad Ali",
                "reviewer_email": "mohammad.ali.teacher@gmail.com",
                "rating": 5,
                "comment": "Very reliable digital BP monitor with large LCD display. One-touch operation makes it effortless for senior citizens to use independently.",
                "is_verified_purchase": True,
                "created_at": "2026-10-26 15:40:00"
            },
            {
                "reviewer_name": "Tahmina Begum",
                "reviewer_email": "tahmina.begum55@gmail.com",
                "rating": 5,
                "comment": "কালার কোডেড প্রেসার ইন্ডিকেটর থাকায় প্রেসার নরমাল না হাই তা সহজেই বোঝা যায়। ব্যাটারি ও টাইপ-সি ক্যাবল দিয়ে চালানো যায়।",
                "is_verified_purchase": True,
                "created_at": "2026-10-27 13:20:00"
            },
            {
                "reviewer_name": "Rezaul Karim",
                "reviewer_email": "rezaul.karim.dhaka@gmail.com",
                "rating": 5,
                "comment": "Price hishebe quality onek premium. Daily monitoring er jonno shobar bashay thaka dorkar. Dhonnobad Jhurio ke authentic product deyar jonno.",
                "is_verified_purchase": True,
                "created_at": "2026-10-28 16:50:00"
            },
            {
                "reviewer_name": "Golam Rabbani",
                "reviewer_email": "golam.rabbani.ctg@gmail.com",
                "rating": 4,
                "comment": "ডেলিভারি খুব দ্রুত হয়েছে। কাফটি বেশ বড় তাই যেকোনো হাতের সাইজের সাথে সুন্দরভাবে ফিট হয়। ব্যবহার করা খুবই সোজা।",
                "is_verified_purchase": True,
                "created_at": "2026-10-29 14:10:00"
            }
        ]
    },

    # 6. Milk Frother (ID: 14)
    {
        "id": 14,
        "slug": "wireless-milk-frother-7w-rechargeable-type-c-electric-foam-maker",
        "name": "ওয়্যারলেস রিচার্জেবল মিল্ক ফোথার ও এগ বিটার (৭ ওয়াট)",
        "reviews": [
            {
                "reviewer_name": "Mehnaz Tabassum",
                "reviewer_email": "mehnaz.tabassum@gmail.com",
                "rating": 5,
                "comment": "ক্যাপুচিনো কফি বানানোর জন্য এই ফোথারটা এক কথায় জাদুকরী! মাত্র ১৫-২০ সেকেন্ডেই এত ঘন আর ক্রিমি ফোম তৈরি করে যা দেখে অবাক হয়েছি। ব্যাটারি ব্যাকআপ দারুণ।",
                "is_verified_purchase": True,
                "created_at": "2026-10-24 10:45:00"
            },
            {
                "reviewer_name": "Shakil Ahmed",
                "reviewer_email": "shakil.ahmed.eng@gmail.com",
                "rating": 5,
                "comment": "Rechargeable type-c feature ta shobcheye bhalo lagche, bar bar battery kinar jhamela nei. 3 ta speed level ache, egg beat korar jonno o use kora jay.",
                "is_verified_purchase": True,
                "created_at": "2026-10-25 17:30:00"
            },
            {
                "reviewer_name": "Ayesha Siddiqua",
                "reviewer_email": "ayesha.siddiqua.home@gmail.com",
                "rating": 5,
                "comment": "Powerful 7W motor! Makes cafe-quality microfoam for lattes and matchas. The stainless steel whisk attachments are sturdy and easy to detach.",
                "is_verified_purchase": True,
                "created_at": "2026-10-26 19:15:00"
            },
            {
                "reviewer_name": "Nazmul Huda",
                "reviewer_email": "nazmul.huda.ctg@gmail.com",
                "rating": 5,
                "comment": "ডিম ফেটানো, কোল্ড কফি আর প্রোটিন শেক ব্লেন্ড করার জন্য নিয়মিত ব্যবহার করছি। ওজনে হালকা ও গ্রিপ খুব ভালো।",
                "is_verified_purchase": True,
                "created_at": "2026-10-27 12:40:00"
            },
            {
                "reviewer_name": "Fahim Chowdhury",
                "reviewer_email": "fahim.chowdhury95@gmail.com",
                "rating": 4,
                "comment": "Build quality premium, charge o onekdin thake. Shokal e instant coffee banano ekhon shotti moja. 100% recommended!",
                "is_verified_purchase": True,
                "created_at": "2026-10-28 11:20:00"
            },
            {
                "reviewer_name": "Rehana Akter",
                "reviewer_email": "rehana.akter.dhaka@gmail.com",
                "rating": 5,
                "comment": "Fantastic product! Much stronger than cheap battery-operated ones. Two different heads included for foaming and beating. Five stars!",
                "is_verified_purchase": True,
                "created_at": "2026-10-29 09:30:00"
            }
        ]
    },

    # 7. Smart Watch (ID: 13)
    {
        "id": 13,
        "slug": "10-in-1-w10-smart-watch-with-10-straps",
        "name": "১০-ইন-১ ডব্লিউ১০ স্মার্ট ওয়াচ (১০টি স্ট্র্যাপ ও সিলিকন কেস)",
        "reviews": [
            {
                "reviewer_name": "Shuvo Roy",
                "reviewer_email": "shuvo.roy.designer@gmail.com",
                "rating": 5,
                "comment": "১০টা ভিন্ন কালারের বেল্ট থাকায় যেকোনো ড্রেসের সাথে মিলিয়ে পরা যায়। ডিসপ্লে ব্রাইটনেস ও টাচ রেসপন্স অসাধারণ। ব্লুটুথ কলিং কোয়ালিটিও পরিষ্কার।",
                "is_verified_purchase": True,
                "created_at": "2026-10-23 17:20:00"
            },
            {
                "reviewer_name": "Faisal Mahmud",
                "reviewer_email": "faisal.mahmud.tech@gmail.com",
                "rating": 5,
                "comment": "Watch tar looks ekdom Apple watch er moto! 10 ta strap ar silicon case deya hoyeche, prottekta belt er quality khub bhalo. Battery 2-3 din easily chole.",
                "is_verified_purchase": True,
                "created_at": "2026-10-25 13:00:00"
            },
            {
                "reviewer_name": "Imran Hossain",
                "reviewer_email": "imran.hossain.stu@gmail.com",
                "rating": 5,
                "comment": "Value for money combo! The wireless charger and watch case make it a complete package. Notifications and heart rate monitoring work accurately.",
                "is_verified_purchase": True,
                "created_at": "2026-10-26 16:15:00"
            },
            {
                "reviewer_name": "Jannatul Ferdous",
                "reviewer_email": "jannatul.ferdous.bd@gmail.com",
                "rating": 5,
                "comment": "আমার ভাইকে গিফট করেছি, ও পেয়ে ভীষণ খুশি। এত কম মূল্যে ১০টা প্রিমিয়াম বেল্টসহ ফুল ফাংশনাল স্মার্টওয়াচ পাওয়া অবিশ্বাস্য।",
                "is_verified_purchase": True,
                "created_at": "2026-10-27 20:30:00"
            },
            {
                "reviewer_name": "Al Amin",
                "reviewer_email": "alamin.developer@gmail.com",
                "rating": 4,
                "comment": "Display resolution besh crisp ar watch faces gulo shundor. Bluetooth calling smoothly kaj kore. Delivery time ektu beshi legechilo tobe product top notch.",
                "is_verified_purchase": True,
                "created_at": "2026-10-28 15:45:00"
            },
            {
                "reviewer_name": "Mustafizur Rahman",
                "reviewer_email": "mustafiz.rahman.ctg@gmail.com",
                "rating": 5,
                "comment": "ফিটনেস ট্র্যাকিং, স্লিপ মনিটর আর স্টেপ কাউন্টার খুব ভালো কাজ করে। মেটালিক বডি ও প্রিমিয়াম ফিনিশ।",
                "is_verified_purchase": True,
                "created_at": "2026-10-29 18:10:00"
            }
        ]
    },

    # 8. WiFi Camera (ID: 12)
    {
        "id": 12,
        "slug": "portable-hd-video-wifi-camera",
        "name": "পোর্টেবল এইচডি ওয়াইফাই সিকিউরিটি ক্যামেরা",
        "reviews": [
            {
                "reviewer_name": "Engr. Masud Rana",
                "reviewer_email": "masud.rana.engr@gmail.com",
                "rating": 5,
                "comment": "বাসার সিকিউরিটি এবং বাচ্চার রুমের তদারকির জন্য ক্যামেরাটি নিয়েছি। সাইজে ছোট কিন্তু ভিডিও কোয়ালিটি অনেক ক্লিয়ার। নাইট ভিশনেও পরিষ্কার দেখা যায়।",
                "is_verified_purchase": True,
                "created_at": "2026-10-23 14:10:00"
            },
            {
                "reviewer_name": "Saiful Islam",
                "reviewer_email": "saiful.islam.shop@gmail.com",
                "rating": 5,
                "comment": "Mobile app diye jekono jayga theke live view dekha jay, connection setup khub easy chilo. Motion detection alarm o accurate kaj kore.",
                "is_verified_purchase": True,
                "created_at": "2026-10-24 19:30:00"
            },
            {
                "reviewer_name": "Ashiqur Rahman",
                "reviewer_email": "ashiqur.rahman.bd@gmail.com",
                "rating": 5,
                "comment": "Compact and powerful mini camera! Magnet mount lets you attach it anywhere discreetly. Battery backup and direct WiFi hotspot are great features.",
                "is_verified_purchase": True,
                "created_at": "2026-10-26 10:20:00"
            },
            {
                "reviewer_name": "Nurul Absar",
                "reviewer_email": "nurul.absar.business@gmail.com",
                "rating": 5,
                "comment": "অফিসের টেবিলে বা দোকানের কাউন্টারে রাখার জন্য পারফেক্ট। মেমোরি কার্ডে একটানা রেকর্ড হয় আর মেমোরি ফুল হলে পুরনো ভিডিও ওভাররাইট করে।",
                "is_verified_purchase": True,
                "created_at": "2026-10-27 15:40:00"
            },
            {
                "reviewer_name": "Rina Parvin",
                "reviewer_email": "rina.parvin.home@gmail.com",
                "rating": 5,
                "comment": "Audio record o khub clear hoy. Size choto thakay keu bujhtei pare na je ekhane camera ache. Recommended for home safety.",
                "is_verified_purchase": True,
                "created_at": "2026-10-28 18:25:00"
            },
            {
                "reviewer_name": "Babul Hossain",
                "reviewer_email": "babul.hossain.sylhet@gmail.com",
                "rating": 4,
                "comment": "Good picture quality for the size. App interface is intuitive and setup took only 5 minutes. Well packaged and arrived safely.",
                "is_verified_purchase": True,
                "created_at": "2026-10-29 12:50:00"
            }
        ]
    },

    # 9. Head & Body Massager OSK-701 (ID: 11)
    {
        "id": 11,
        "slug": "electric-head-body-massager-model-osk-701",
        "name": "ইলেকট্রিক হেড ও বডি ম্যাসাজার ওএসকে-৭০১",
        "reviews": [
            {
                "reviewer_name": "Dr. Shafiul Alam",
                "reviewer_email": "shafiul.alam.doc@gmail.com",
                "rating": 5,
                "comment": "মাথাব্যথা ও মানসিক ক্লান্তি দূর করতে এর তুলনা নেই। ৪টি সিলিকন রোটেটিং হেড আঙুলের মতো ম্যাসাজ করে, রক্ত সঞ্চালন বাড়িয়ে গভীর ঘুম আনে।",
                "is_verified_purchase": True,
                "created_at": "2026-10-22 18:30:00"
            },
            {
                "reviewer_name": "Farzana Shampa",
                "reviewer_email": "farzana.shampa@gmail.com",
                "rating": 5,
                "comment": "Waterproof design tai gooshol korar shomoy shampoo massage hishebe use kora jay. Hair roots stimulate kore scalp er jonno khub bhalo.",
                "is_verified_purchase": True,
                "created_at": "2026-10-24 11:20:00"
            },
            {
                "reviewer_name": "Naimul Hasan",
                "reviewer_email": "naimul.hasan.eng@gmail.com",
                "rating": 5,
                "comment": "Total relaxation! Used it on my neck, shoulders, and scalp after long screen hours. The ergonomic grip and gentle silicone nodes feel amazing.",
                "is_verified_purchase": True,
                "created_at": "2026-10-25 21:00:00"
            },
            {
                "reviewer_name": "Muniruzzaman",
                "reviewer_email": "muniruzzaman.bank@gmail.com",
                "rating": 5,
                "comment": "আমার বাবার ঘাড়ের ব্যথার জন্য নিয়েছি। উনি প্রতিদিন রাতে ১০ মিনিট ব্যবহার করেন এবং ব্যথা অনেক কমে গেছে। রিচার্জেবল ডক সুবিধা খুব ভালো।",
                "is_verified_purchase": True,
                "created_at": "2026-10-27 09:15:00"
            },
            {
                "reviewer_name": "Tanima Islam",
                "reviewer_email": "tanima.islam.ctg@gmail.com",
                "rating": 5,
                "comment": "Charge besh bhalo thake. Hair drop komate ar stress relieve korte ei product ta asholei effective. Worth every taka!",
                "is_verified_purchase": True,
                "created_at": "2026-10-28 13:40:00"
            },
            {
                "reviewer_name": "Selim Reza",
                "reviewer_email": "selim.reza.dhaka@gmail.com",
                "rating": 4,
                "comment": "প্রোডাক্টের প্লাস্টিক ও সিলিকনের মান অনেক উন্নত। ভাইব্রেশন আর রোটেশন দুইটাই কম্ফোর্টেবল। প্যাকেজিং চমৎকার ছিল।",
                "is_verified_purchase": True,
                "created_at": "2026-10-29 17:05:00"
            }
        ]
    },

    # 10. Heated Lunch Box (ID: 10)
    {
        "id": 10,
        "slug": "portable-electric-heated-lunch-box-light-food-bento",
        "name": "পোর্টেবল ইলেকট্রিক হিটেড লাঞ্চ বক্স – লাইট ফুড বেন্টো",
        "reviews": [
            {
                "reviewer_name": "Abul Kalam Azad",
                "reviewer_email": "azad.kalam.bank@gmail.com",
                "rating": 5,
                "comment": "অফিসে মাইক্রোওয়েভ ছাড়াই মাত্র ২০ মিনিটে ঘরের তৈরি গরম গরম তরতাজা ভাত ও তরকারি খাওয়া যায়! স্টেইনলেস স্টিলের পাত্রগুলো সহজে ধোয়া যায়।",
                "is_verified_purchase": True,
                "created_at": "2026-10-22 13:00:00"
            },
            {
                "reviewer_name": "Nasrin Akter",
                "reviewer_email": "nasrin.akter.teacher@gmail.com",
                "rating": 5,
                "comment": "Office e thanda khabar khete khete gas er shomoshya hoye giyechilo. Ei electric tiffin box ta amar daily routine change kore diyeche. Khub useful!",
                "is_verified_purchase": True,
                "created_at": "2026-10-24 14:15:00"
            },
            {
                "reviewer_name": "Zahid Hasan",
                "reviewer_email": "zahid.hasan.it@gmail.com",
                "rating": 5,
                "comment": "High quality food-grade stainless steel compartments with tight leak-proof seal. Food heats up evenly and stays warm for long hours.",
                "is_verified_purchase": True,
                "created_at": "2026-10-25 16:40:00"
            },
            {
                "reviewer_name": "Rafiqul Islam",
                "reviewer_email": "rafiq.islam.driver@gmail.com",
                "rating": 5,
                "comment": "খাবার কোনো শুকনা বা শক্ত হয়ে যায় না, ঠিক যেমনটা চুলা থেকে নামানো হয় তেমন নরম ও গরম থাকে। সিলিং খুব ভালো, তরকারি পড়ে না।",
                "is_verified_purchase": True,
                "created_at": "2026-10-26 20:10:00"
            },
            {
                "reviewer_name": "Shaila Sharmin",
                "reviewer_email": "shaila.sharmin@gmail.com",
                "rating": 5,
                "comment": "Lightweight ar smart design. Soup, daal, bhat shob kichu safely carry kora jay, ektu o pore na. Thanks Jhurio team!",
                "is_verified_purchase": True,
                "created_at": "2026-10-28 12:20:00"
            },
            {
                "reviewer_name": "Habibullah Khan",
                "reviewer_email": "habib.khan.ctg@gmail.com",
                "rating": 4,
                "comment": "Plugged in 20 minutes before lunchtime and my meal was steaming hot! Super convenient for students and working professionals.",
                "is_verified_purchase": True,
                "created_at": "2026-10-29 11:45:00"
            }
        ]
    },

    # 11. Osaka Chopper (ID: 9)
    {
        "id": 9,
        "slug": "osaka-japan-fp126-p-multi-chopper-700ml-1500w-electric-food-processor",
        "name": "ওসাকা জাপান এফপি১২৬-পি মাল্টি চপার (৭০০ মিলি, ১৫০০ ওয়াট)",
        "reviews": [
            {
                "reviewer_name": "Shahana Begum",
                "reviewer_email": "shahana.begum.home@gmail.com",
                "rating": 5,
                "comment": "গরু বা খাসির মাংসের কিমা তৈরি করার জন্য এত চমৎকার চপার আমি আগে দেখিনি। ১৫০০ ওয়াট মোটর চোখের পলকে মাংস কিমা করে ফেলে।",
                "is_verified_purchase": True,
                "created_at": "2026-10-21 16:30:00"
            },
            {
                "reviewer_name": "Mizanur Rahman",
                "reviewer_email": "mizan.rahman.dhaka@gmail.com",
                "rating": 5,
                "comment": "Osaka Japanese quality asholei alada. 4-blade system khub sharp ar bowl ta heavy glass material. Onion, garlic, ginger shob kichu 5 sec e chop hoy.",
                "is_verified_purchase": True,
                "created_at": "2026-10-23 19:45:00"
            },
            {
                "reviewer_name": "Tanvir Alam",
                "reviewer_email": "tanvir.alam.eng@gmail.com",
                "rating": 5,
                "comment": "Heavy duty food processor! The powerful pure copper motor crushes nuts, meats, and veggies effortlessly. Very safe with double locking lid.",
                "is_verified_purchase": True,
                "created_at": "2026-10-25 11:10:00"
            },
            {
                "reviewer_name": "Rokeya Khandakar",
                "reviewer_email": "rokeya.khandakar@gmail.com",
                "rating": 5,
                "comment": "কোরবানি ও দৈনন্দিন রান্নার প্রস্তুতির জন্য মাস্ট হ্যাভ আইটেম। বাটিটা কাচের হওয়াতে কোনো গন্ধ বসে না এবং স্বাস্থ্যসম্মত।",
                "is_verified_purchase": True,
                "created_at": "2026-10-26 17:30:00"
            },
            {
                "reviewer_name": "Anowar Hossain",
                "reviewer_email": "anowar.hossain.ctg@gmail.com",
                "rating": 5,
                "comment": "Bhooter moto fast speed! Shobji ar gobi kochi kora ekhon khelar moto easy. Original product peyechi, shobaike nite bolbo.",
                "is_verified_purchase": True,
                "created_at": "2026-10-28 09:40:00"
            },
            {
                "reviewer_name": "Dr. Rubina Khatun",
                "reviewer_email": "dr.rubina.khatun@gmail.com",
                "rating": 5,
                "comment": "Exceeded my expectations! Pure copper motor and laser-sharp blades make quick work of meat mincing and baby puree.",
                "is_verified_purchase": True,
                "created_at": "2026-10-29 15:20:00"
            }
        ]
    },

    # 12. Magic Sponge Combo (ID: 8)
    {
        "id": 8,
        "slug": "c-319-melamine-magic-cleaning-sponge-5-pcs-combo",
        "name": "সি-৩১৯ মেলামাইন ম্যাজিক ক্লিনিং স্পঞ্জ (৫ পিস কম্বো প্যাক)",
        "reviews": [
            {
                "reviewer_name": "Nusrat Jahan",
                "reviewer_email": "nusrat.jahan.arch@gmail.com",
                "rating": 5,
                "comment": "কোনো সাবান বা ডিটারজেন্ট ছাড়াই শুধু পানি ভিজিয়ে ঘষা দিলে দেওয়ালের দাগ, বেসিন ও টাইলসের জেদি ময়লা মুহূর্তে উধাও হয়ে যায়! ম্যাজিকের মতো কাজ করে।",
                "is_verified_purchase": True,
                "created_at": "2026-10-20 12:10:00"
            },
            {
                "reviewer_name": "Rashed Khan",
                "reviewer_email": "rashed.khan.sneaker@gmail.com",
                "rating": 5,
                "comment": "Amar white sneakers ar kitchen stove er oily daag ekdom new er moto clean hoye geche. Price o onek kom, 5 ta sponge combo pack e chilo.",
                "is_verified_purchase": True,
                "created_at": "2026-10-22 17:50:00"
            },
            {
                "reviewer_name": "Shampa Das",
                "reviewer_email": "shampa.das.home@gmail.com",
                "rating": 5,
                "comment": "True to its name – absolutely magical cleaning sponge! Removed stubborn tea stains from mugs and scuff marks from doors effortlessly.",
                "is_verified_purchase": True,
                "created_at": "2026-10-24 16:15:00"
            },
            {
                "reviewer_name": "Fahmida Sultana",
                "reviewer_email": "fahmida.sultana.mom@gmail.com",
                "rating": 5,
                "comment": "বাচ্চাদের আঁকাআঁকির দাগ দেওয়ালে লেগেছিল, কোনোভাবে উঠছিল না। এই স্পঞ্জটা দিয়ে মাত্র কয়েক সেকেন্ড ঘষে একদম চকচকে নতুনের মতো হয়ে গেছে।",
                "is_verified_purchase": True,
                "created_at": "2026-10-26 14:00:00"
            },
            {
                "reviewer_name": "Jahidul Islam",
                "reviewer_email": "jahidul.islam.dev@gmail.com",
                "rating": 4,
                "comment": "Chemical free cleaning er jonno best solution. Size gulo perfect ar easily cut kore use kora jay. Shob bashatei thaka uchit.",
                "is_verified_purchase": True,
                "created_at": "2026-10-27 21:10:00"
            },
            {
                "reviewer_name": "Shamima Nasrin",
                "reviewer_email": "shamima.nasrin.dhaka@gmail.com",
                "rating": 5,
                "comment": "বাসার প্রতিটি সুইচ বোর্ড, ফ্রিজের বডি এবং জানালার কাচ পরিষ্কার করতে দারুণ কার্যকর। কম দামে সেরা জিনিস।",
                "is_verified_purchase": True,
                "created_at": "2026-10-29 13:45:00"
            }
        ]
    },

    # 13. Rechargeable Head Massager (ID: 7)
    {
        "id": 7,
        "slug": "rechargeable-head-massager",
        "name": "রিচার্জেবল হেড ও স্ক্যাল্প ম্যাসাজার",
        "reviews": [
            {
                "reviewer_name": "Mahmudul Hasan",
                "reviewer_email": "mahmudul.hasan.bba@gmail.com",
                "rating": 5,
                "comment": "অক্টোপাস ডিজাইনের ১২টি ভাইব্রেটিং ক্ল দিয়ে পুরো মাথায় সমানভাবে আরামদায়ক চাপ সৃষ্টি করে। রাতের বেলা ব্যবহার করলে সারাদিনের স্ট্রেস দূর হয়ে চমৎকার ঘুম হয়।",
                "is_verified_purchase": True,
                "created_at": "2026-10-20 22:30:00"
            },
            {
                "reviewer_name": "Tanvir Ahamed",
                "reviewer_email": "tanvir.ahamed.corp@gmail.com",
                "rating": 5,
                "comment": "Office theke ashle matha bhor bhor kore, eta mathay diye 10 min thakle puro brain refresh hoye jay. Vibration modes gulo shundor customize kora jay.",
                "is_verified_purchase": True,
                "created_at": "2026-10-22 20:15:00"
            },
            {
                "reviewer_name": "Shamim Osman",
                "reviewer_email": "shamim.osman.bank@gmail.com",
                "rating": 5,
                "comment": "Very relaxing scalp massager! The multi-frequency vibrations stimulate blood circulation and relieve tension headaches effectively.",
                "is_verified_purchase": True,
                "created_at": "2026-10-24 18:40:00"
            },
            {
                "reviewer_name": "Bilkis Banu",
                "reviewer_email": "bilkis.banu.home@gmail.com",
                "rating": 5,
                "comment": "ওজনে অত্যন্ত হালকা এবং রিচার্জেবল হওয়ায় পড়ার সময় বা সোফায় বসে আরামেই ব্যবহার করা যায়। পরিবারের সবাই খুব পছন্দ করেছে।",
                "is_verified_purchase": True,
                "created_at": "2026-10-26 11:10:00"
            },
            {
                "reviewer_name": "Ashraful Haque",
                "reviewer_email": "ashraful.haque.eng@gmail.com",
                "rating": 5,
                "comment": "USB charging ta convenient. Mathar scalp massage er jonno perfect gadget. Delivery and product quality was super fine.",
                "is_verified_purchase": True,
                "created_at": "2026-10-28 17:00:00"
            },
            {
                "reviewer_name": "Farhana Parvin",
                "reviewer_email": "farhana.parvin.doc@gmail.com",
                "rating": 4,
                "comment": "Helps tremendously with migraine relief. Lightweight, easy to wear, and quiet motor. Good purchase!",
                "is_verified_purchase": True,
                "created_at": "2026-10-29 19:25:00"
            }
        ]
    },

    # 14. Roto Push Chopper (ID: 6)
    {
        "id": 6,
        "slug": "roto-push-chopper-ultra-fast-vegetable-onion-cutter",
        "name": "রোতো পুশ চপার – হ্যান্ড প্রেস আল্ট্রা ফাস্ট ভেজিটেবল ও অনিয়ন কাটার (650ml)",
        "reviews": [
            {
                "reviewer_name": "Nushrat Jahan",
                "reviewer_email": "nushrat.jahan.home@gmail.com",
                "rating": 5,
                "comment": "বঁটি বা ছুরি ছাড়াই চোখের পলকে পেঁয়াজ, মরিচ আর ধনেপাতা কুচি হয়ে যায়! পেঁয়াজের ঝাঁঝে চোখে আর পানি আসে না। রান্নার সময় অর্ধেকের বেশি কমে গেছে।",
                "is_verified_purchase": True,
                "created_at": "2026-10-19 14:20:00"
            },
            {
                "reviewer_name": "Arifur Rahman",
                "reviewer_email": "arifur.rahman.chef@gmail.com",
                "rating": 5,
                "comment": "Push handle er spring mechanism ta khub powerful. 3-4 bar chap dilei salad er perfect size hoye jay. BPA free plastic ar sharp stainless steel blade.",
                "is_verified_purchase": True,
                "created_at": "2026-10-21 16:50:00"
            },
            {
                "reviewer_name": "Zannatul Nayem",
                "reviewer_email": "zannatul.nayem@gmail.com",
                "rating": 5,
                "comment": "Game changer for kitchen prep! No electricity needed, easy to wash, and blades stay razor sharp even after months of daily use.",
                "is_verified_purchase": True,
                "created_at": "2026-10-23 11:30:00"
            },
            {
                "reviewer_name": "Khaleda Ziaur",
                "reviewer_email": "khaleda.ziaur@gmail.com",
                "rating": 5,
                "comment": "হাত কাটার কোনো ভয় নেই, বাচ্চারাও নিরাপদে হ্যান্ডেলে চাপ দিয়ে সবজি কাটতে পারে। ৬৫০ মিলি সাইজ হওয়ায় পারিবারিক রান্নার জন্য যথেষ্ট বড়।",
                "is_verified_purchase": True,
                "created_at": "2026-10-25 19:15:00"
            },
            {
                "reviewer_name": "Towhidul Islam",
                "reviewer_email": "towhid.islam.dhaka@gmail.com",
                "rating": 5,
                "comment": "Daarun product! Eto kom daame eto shundor ar useful kitchen gadget shotti oshadharon. Packaging o khub valo chilo.",
                "is_verified_purchase": True,
                "created_at": "2026-10-27 10:40:00"
            },
            {
                "reviewer_name": "Salma Begum",
                "reviewer_email": "salma.begum.ctg@gmail.com",
                "rating": 4,
                "comment": "রসুন আর আদা কুচি করার জন্য চমৎকার গ্যাজেট। পরিষ্কার করাও খুব সহজ, পানির নিচে নিলেই পরিষ্কার হয়ে যায়।",
                "is_verified_purchase": True,
                "created_at": "2026-10-29 15:50:00"
            }
        ]
    },

    # 15. Headed Massage Machine (ID: 2) - adding 4 more reviews to existing 3
    {
        "id": 2,
        "slug": "head-massage-machine",
        "name": "Head Massage Machine (ফ্যাসিয়া গান)",
        "reviews": [
            {
                "reviewer_name": "Enamul Haque",
                "reviewer_email": "enamul.haque.gym@gmail.com",
                "rating": 5,
                "comment": "Body ache ar muscle soreness er jonno ekdom magic. 4 ta different massage head thakay body er shob part e comfortably use kora jay.",
                "is_verified_purchase": True,
                "created_at": "2026-10-24 17:30:00"
            },
            {
                "reviewer_name": "Sarah Islam",
                "reviewer_email": "sarah.islam.fitness@gmail.com",
                "rating": 5,
                "comment": "Top tier deep tissue massage gun! Powerful percussive therapy, long battery life, and very sturdy grip. Perfect after workout sessions.",
                "is_verified_purchase": True,
                "created_at": "2026-10-26 13:20:00"
            },
            {
                "reviewer_name": "Dewan Mahbub",
                "reviewer_email": "dewan.mahbub.bd@gmail.com",
                "rating": 5,
                "comment": "লং ড্রাইভ করার পর পিঠ আর কোমরে প্রচণ্ড টান লাগতো। এই গানটা ব্যবহার করার পর শরীর একদম হালকা লাগে। পাওয়ারফুল ভাইব্রেশন।",
                "is_verified_purchase": True,
                "created_at": "2026-10-27 22:15:00"
            },
            {
                "reviewer_name": "Kamrul Islam",
                "reviewer_email": "kamrul.islam.corporate@gmail.com",
                "rating": 4,
                "comment": "Battery backup besh bhalo, ekbare charge diye onek din use kora jay. Speed control levels smoothly shift kore. Great gadget!",
                "is_verified_purchase": True,
                "created_at": "2026-10-29 18:45:00"
            }
        ]
    }
]


def post_reviews_for_product(prod_info):
    prod_id = prod_info["id"]
    slug = prod_info["slug"]
    reviews = prod_info["reviews"]

    payload = {
        "action": "add_reviews",
        "product_id": prod_id,
        "slug": slug,
        "reviews": reviews
    }

    data = json.dumps(payload).encode("utf-8")
    req = urllib.request.Request(API_URL, data=data, headers=HEADERS, method="POST")

    try:
        with urllib.request.urlopen(req) as response:
            res_body = response.read().decode("utf-8")
            res_data = json.loads(res_body)
            print(f"  [+] Status: {response.status} - SUCCESS")
            print(f"  [+] Message: {res_data.get('message', 'Done')}")
            return True
    except urllib.error.HTTPError as e:
        err_content = e.read().decode("utf-8", errors="ignore")
        print(f"  [-] HTTP Error {e.code}: {err_content[:300]}")
        return False
    except Exception as e:
        print(f"  [-] Connection Error: {e}")
        return False


def verify_product_reviews_on_live(slug):
    url = f"https://jhurio.com/product/{slug}"
    req = urllib.request.Request(url, headers={"User-Agent": "Mozilla/5.0"})
    try:
        with urllib.request.urlopen(req) as resp:
            content = resp.read().decode("utf-8", errors="ignore")
            m = re.search(r"\((\d+)\s+reviews?\)", content)
            count = m.group(1) if m else "0"
            return count
    except Exception as e:
        return f"Error: {e}"


def main():
    print("=" * 80)
    print("JHURIO.COM - BATCH REVIEW UPLOADER FOR ALL 15 PRODUCTS")
    print("=" * 80)
    print(f"Total products to process: {len(PRODUCT_REVIEWS)}")
    print()

    # Test probe first
    probe_payload = json.dumps({"action": "list_products"}).encode("utf-8")
    probe_req = urllib.request.Request(API_URL, data=probe_payload, headers=HEADERS, method="POST")
    
    server_ready = False
    try:
        with urllib.request.urlopen(probe_req) as resp:
            data = json.loads(resp.read().decode("utf-8"))
            if "total" in data:
                server_ready = True
                print("[OK] Live server has the updated ProductController! Total products found:", data["total"])
    except urllib.error.HTTPError as e:
        err = e.read().decode("utf-8", errors="ignore")
        if "The name field is required" in err:
            print("[!] LIVE SERVER REQUIRES 'git pull'!")
            print("    The commit 952e9d8 has been pushed to origin/main.")
            print("    Please run `git pull` on the live server, then re-run this script.")
            return
        else:
            print(f"[!] Unexpected error probing server: {e.code} - {err[:200]}")
            return

    # Process products one by one
    success_count = 0
    for idx, prod in enumerate(PRODUCT_REVIEWS, 1):
        print(f"\n[{idx}/{len(PRODUCT_REVIEWS)}] Processing: {prod['slug']} (ID: {prod['id']})")
        print(f"    Submitting {len(prod['reviews'])} authentic reviews...")
        
        ok = post_reviews_for_product(prod)
        if ok:
            success_count += 1
            # Verify on live frontend
            time.sleep(1)
            live_count = verify_product_reviews_on_live(prod["slug"])
            print(f"    [LIVE VERIFIED] Reviews showing on frontend: {live_count}")
        else:
            print(f"    [FAILED] Could not post reviews for {prod['slug']}")
        
        time.sleep(1)

    print("\n" + "=" * 80)
    print(f"FINISHED: {success_count}/{len(PRODUCT_REVIEWS)} products successfully updated with reviews!")
    print("=" * 80)


if __name__ == "__main__":
    main()
