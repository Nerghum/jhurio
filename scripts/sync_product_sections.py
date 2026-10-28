import urllib.request
import json
import sys

API_URL = "https://jhurio.com/api/products/4"
API_TOKEN = "prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c"

payload = {
    "name": "রোতো পুশ চপার – হ্যান্ড প্রেস আল্ট্রা ফাস্ট ভেজিটেবল ও অনিয়ন কাটার (650ml)",
    "price": 599,
    "purchase_price": 280,
    "compare_at_price": 999,
    "stock_quantity": 50,
    "status": "published",
    "categories": ["Kitchen Items", "Kitchen Accessories"],
    "tags": ["পুশ চপার", "ভেজিটেবল কাটার", "অনিয়ন কাটার", "Chopper", "Kitchen Gadgets"],
    "description": "<p>চোখে পানি না ফেলে মাত্র কয়েক সেকেন্ডে পেঁয়াজ, রসুন, কাঁচামরিচ, আদা ও সবজি কুচি করার জাদুকরী সমাধান! কোনো বিদ্যুৎ বা ব্যাটারির ঝামেলা নেই—হালকা চাপেই ৩টি আল্ট্রা-শার্প স্টেইনলেস স্টিল ব্লেড ঘুরবে ঝড়ের গতিতে। মজবুত ফুড-গ্রেড বডি ও সহজে ধোয়ার সুবিধা।</p>",
    "content": """<div class="space-y-6">
    <div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">রান্নাঘরের ঝামেলা কমিয়ে রান্নার প্রস্তুতি করুন ৩ গুণ দ্রুত!</h3>
        <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
            প্রতিদিনের রান্নায় পেঁয়াজ কাটা মানেই চোখে জ্বালাপোড়া ও পানি পড়া, আর বঁটি বা ছুরিতে কুচি করতে গেলে হাত কেটে যাওয়ার ভয় তো থাকেই। <strong>Roto Push Chopper</strong> আপনার এই প্রতিদিনের কষ্টের ইতি টানবে! এই ম্যানুয়াল রোটারি পুশ চপারের সাহায্যে বিদ্যুৎ ছাড়াই মাত্র কয়েক সেকেন্ডে যেকোনো শাকসবজি, পেঁয়াজ, রসুন, কাঁচামরিচ ও সালাদ নিখুঁতভাবে কুচি করতে পারবেন।
        </p>
    </div>

    <div>
        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-3">কেন এই রোতো পুশ চপার প্রতিটি রান্নাঘরের জন্য সেরা?</h4>
        <ul class="space-y-2 text-gray-600 dark:text-gray-300 list-disc list-inside">
            <li><strong>আল্ট্রা-ফাস্ট পুশ মেকানিজম:</strong> হ্যান্ডেলে একবার হালকা চাপ দিলেই ভেতরের ৩টি ব্লেড বহুবার রোটেট করে দ্রুত চপ করে। যত বেশি চাপবেন, কুচি তত বেশি মিহি হবে।</li>
            <li><strong>চোখে পানি আসার ঝামেলা শেষ:</strong> পেঁয়াজ কাটার সময় এর ঝাঁঝালো গন্ধ বাইরে বের হতে পারে না, ফলে চোখ জ্বলা বা পানি পড়ার কোনো সম্ভাবনা নেই।</li>
            <li><strong>১০০% নিরাপদ ও সুরক্ষিত:</strong> হাত ও আঙুল ব্লেডের সংস্পর্শে আসে না, তাই হাত কাটার কোনো ভয় থাকে না।</li>
            <li><strong>ফুড-গ্রেড ও BPA-ফ্রি মেটেরিয়াল:</strong> প্রিমিয়াম কোয়ালিটি প্লাস্টিক এবং ৩-লেয়ারের প্রিমিয়াম স্টেইনলেস স্টিল ব্লেড দিয়ে তৈরি, যা মরিচামুক্ত ও দীর্ঘস্থায়ী।</li>
            <li><strong>বিদ্যুৎ ও ব্যাটারি ছাড়াই চলে:</strong> যেকোনো জায়গায় নিয়ে সহজে ব্যবহার করা যায়, লোডশেডিংয়ের ঝামেলা নেই।</li>
            <li><strong>সহজে পরিষ্কারযোগ্য:</strong> এর প্রতিটি অংশ আলাদা করে পানির নিচে ধুয়ে নেওয়া যায়।</li>
        </ul>
    </div>

    <div>
        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-3">যা যা অনায়াসে কুচি ও চপ করতে পারবেন:</h4>
        <p class="text-gray-600 dark:text-gray-300">
            পেঁয়াজ, রসুন, আদা, কাঁচামরিচ, টমেটো, গাজর, ক্যাপসিকাম, শসা, ধনেপাতা, পুদিনাপাতা, বাদাম ও কাজুবাদাম, সেদ্ধ ডিম এবং পারফেক্ট সালাদ ড্রেসিং।
        </p>
    </div>

    <div class="bg-gray-50 dark:bg-gray-800/60 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
        <h4 class="text-base font-bold text-gray-900 dark:text-white mb-2">স্পেসিফিকেশন:</h4>
        <p class="text-sm text-gray-600 dark:text-gray-300">
            • <strong>মডেল:</strong> Roto Push Chopper 650ml<br>
            • <strong>ক্যাপাসিটি:</strong> ৬৫০ মিলি (বড় পারিবারিক সাইজ)<br>
            • <strong>ব্লেড:</strong> ৩-টিয়ার আল্ট্রা-শার্প স্টেইনলেস স্টিল ব্লেড<br>
            • <strong>বডি:</strong> হেভি ডিউটি আনব্রেকেবল ফুড-গ্রেড প্লাস্টিক<br>
            • <strong>অপারেশন:</strong> সফট-প্রেস স্প্রিং হ্যান্ডেল
        </p>
    </div>
</div>""",
    "image_text_sections": [
        {
            "title": "হালকা এক চাপেই সেকেন্ডে পারফেক্ট কুচি",
            "subtitle": "সুপার ফাস্ট চপিং মেকানিজম",
            "content": "চিরাচরিত ছুরি বা বঁটি দিয়ে পেঁয়াজ কাটার সময় চোখে পানি আসা কিংবা হাত কেটে যাওয়ার ভয় আর নয়! রোতো পুশ চপারের শক্তিশালী স্প্রিং-লোডেড মেকানিজম দিয়ে আলতো চাপে অভ্যন্তরীণ ৩টি স্টেইনলেস স্টিল ব্লেড উচ্চ গতিতে ঘুরে সবজি নিখুঁত সাইজে কুচি করে দেয়। যত বেশি চাপবেন, কুচি তত মিহি হবে।",
            "image_url": "https://jhurio.com/assets/images/products/roto-push-chopper/push-chopper-action.png",
            "image_position": "right",
            "display_position": "after_description",
            "button_text": "অর্ডার করতে চাই",
            "button_url": "#"
        },
        {
            "title": "মরিচারোধক স্টেইনলেস স্টিল ব্লেড ও বিপিএ-মুক্ত নিরাপদ বডি",
            "subtitle": "প্রিমিয়াম ফুড-গ্রেড বিল্ড কোয়ালিটি",
            "content": "উন্নত মানের প্রিমিয়াম ফুড-গ্রেড প্লাস্টিক ও হেভি-ডিউটি স্টেইনলেস স্টিল ব্লেড দিয়ে তৈরি, যা দীর্ঘদিন মরিচামুক্ত ও অত্যন্ত ধারালো থাকে। সম্পূর্ণ বডি সহজে খুলে সাধারণ পানিতে ধুয়ে ফেলা যায়। রান্নার প্রস্তুতিতে আপনার মূল্যবান ৬০-৭০% সময় বাঁচিয়ে দেবে এই চমৎকার কিচেন গ্যাজেটটি।",
            "image_url": "https://jhurio.com/assets/images/products/roto-push-chopper/push-chopper-blades-lifestyle.jpg",
            "image_position": "left",
            "display_position": "after_description",
            "button_text": "এখনই কিনুন",
            "button_url": "#"
        }
    ]
}

data = json.dumps(payload).encode("utf-8")
req = urllib.request.Request(
    API_URL,
    data=data,
    method="PUT",
    headers={
        "Authorization": f"Bearer {API_TOKEN}",
        "Content-Type": "application/json",
        "Accept": "application/json",
        "User-Agent": "Mozilla/5.0"
    }
)

try:
    with urllib.request.urlopen(req) as resp:
        res = json.loads(resp.read().decode())
        print("Successfully updated product sections!")
        print(json.dumps(res, indent=2, ensure_ascii=False))
except urllib.error.HTTPError as e:
    print(f"HTTP Error {e.code}: {e.read().decode()[:400]}")
    sys.exit(1)
except Exception as e:
    print(f"Error: {e}")
    sys.exit(1)
