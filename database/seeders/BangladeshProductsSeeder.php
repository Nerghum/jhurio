<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\Media;
use App\Models\Tag;
use Illuminate\Support\Str;

class BangladeshProductsSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Categories
        $categories = [
            'Traditional Wear' => Category::firstOrCreate(['name' => 'Traditional Wear', 'slug' => 'traditional-wear']),
            'Handicrafts' => Category::firstOrCreate(['name' => 'Handicrafts', 'slug' => 'handicrafts']),
            'Food & Beverages' => Category::firstOrCreate(['name' => 'Food & Beverages', 'slug' => 'food-beverages']),
            'Accessories' => Category::firstOrCreate(['name' => 'Accessories', 'slug' => 'accessories']),
            'Flash Sale' => Category::firstOrCreate(['name' => 'Flash Sale', 'slug' => 'flash-sale']),
            'Featured' => Category::firstOrCreate(['name' => 'Featured Products', 'slug' => 'featured-products']),
            'Top Selling' => Category::firstOrCreate(['name' => 'Top Selling', 'slug' => 'top-selling']),
        ];

        // 2. Create Tags
        $tags = [
            'Premium' => Tag::firstOrCreate(['name' => 'Premium', 'slug' => 'premium']),
            'Local' => Tag::firstOrCreate(['name' => 'Local Made', 'slug' => 'local-made']),
            'Handmade' => Tag::firstOrCreate(['name' => 'Handmade', 'slug' => 'handmade']),
            'Trending' => Tag::firstOrCreate(['name' => 'Trending', 'slug' => 'trending']),
        ];

        // Helper to create media
        $createMedia = function($url, $name) {
            return Media::create([
                'filename' => $name,
                'file_path' => $url, // Store the URL directly here since we updated Media to support it
                'file_type' => 'image/jpeg',
                'file_size' => 1024,
            ]);
        };

        // 3. Define 10 BD Products
        $productsData = [
            [
                'name' => 'Authentic Dhakai Jamdani Saree',
                'cat' => 'Traditional Wear',
                'price' => 15000,
                'stock' => 5,
                'thumb' => 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1583391733958-65e2786a3449?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1605763240000-7e93b172d754?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'
                ],
                'desc' => 'Experience the rich heritage of Bangladesh with this authentic handcrafted Dhakai Jamdani. Known for its lightweight, breathable fabric and intricate geometric motifs, this saree is perfect for weddings and festive occasions.',
                'tags' => ['Premium', 'Handmade', 'Local']
            ],
            [
                'name' => 'Premium Sylhet Tea Leaves',
                'cat' => 'Food & Beverages',
                'price' => 500,
                'stock' => 120,
                'thumb' => 'https://images.unsplash.com/photo-1597481499750-3e6b22637e12?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1564890369478-c89ca6d9cde9?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'
                ],
                'desc' => 'Directly from the lush green gardens of Sreemangal, Sylhet. This premium black tea offers a robust flavor, rich aroma, and the perfect start to your morning.',
                'tags' => ['Premium', 'Local']
            ],
            [
                'name' => 'Men\'s Cotton Panjabi',
                'cat' => 'Traditional Wear',
                'price' => 2500,
                'stock' => 45,
                'thumb' => 'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1507679799987-c73779587ccf?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'
                ],
                'desc' => 'A classic cotton Panjabi featuring elegant embroidery around the collar and chest. Comfortable, breathable, and designed for Eid, Pohela Boishakh, or casual Friday prayers.',
                'tags' => ['Trending', 'Local']
            ],
            [
                'name' => 'Nakshi Kantha (Hand-Stitched Quilt)',
                'cat' => 'Handicrafts',
                'price' => 4500,
                'stock' => 12,
                'thumb' => 'https://images.unsplash.com/photo-1620063231317-0f81d11394ff?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1533616688419-b7a585564566?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'
                ],
                'desc' => 'A centuries-old Bengali tradition. Each Nakshi Kantha is uniquely hand-stitched by rural artisans, featuring vibrant threads telling stories of rural life. Perfect as a light winter blanket or a decorative throw.',
                'tags' => ['Handmade', 'Premium', 'Local']
            ],
            [
                'name' => 'Pure Sundarbans Honey',
                'cat' => 'Food & Beverages',
                'price' => 1200,
                'stock' => 30,
                'thumb' => 'https://images.unsplash.com/photo-1587049352847-81a56d773c1c?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1587049352851-8d4e8913478d?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'
                ],
                'desc' => '100% pure, raw, and unfiltered honey collected deep from the mangrove forests of the Sundarbans. Packed with natural antioxidants and a distinct, rich taste.',
                'tags' => ['Premium', 'Local']
            ],
            [
                'name' => 'Jute Handbag (Eco-Friendly)',
                'cat' => 'Handicrafts',
                'price' => 850,
                'stock' => 60,
                'thumb' => 'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'
                ],
                'desc' => 'Made from the "Golden Fiber" of Bangladesh. This stylish, eco-friendly jute handbag is highly durable, spacious, and perfect for everyday use or grocery shopping while reducing plastic use.',
                'tags' => ['Handmade', 'Trending', 'Local']
            ],
            [
                'name' => 'Rickshaw Art Painted Kettle',
                'cat' => 'Handicrafts',
                'price' => 1500,
                'stock' => 15,
                'thumb' => 'https://images.unsplash.com/photo-1583344654924-11e2f8d0be46?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1596704017254-9b121068fb31?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'
                ],
                'desc' => 'Bring the vibrant, colorful streets of Dhaka into your home! This functional tea kettle features authentic, hand-painted Rickshaw motifs. A stunning showpiece or functional serving kettle.',
                'tags' => ['Handmade', 'Premium']
            ],
            [
                'name' => 'Rajshahi Silk Saree',
                'cat' => 'Traditional Wear',
                'price' => 8000,
                'stock' => 25,
                'thumb' => 'https://images.unsplash.com/photo-1610116306796-6fea9f4fae38?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1583391733958-65e2786a3449?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'
                ],
                'desc' => 'Renowned for its luxurious feel and glossy finish, the Rajshahi Silk Saree is a symbol of elegance. It features soft fabric that drapes beautifully for formal gatherings and evening wear.',
                'tags' => ['Premium', 'Trending']
            ],
            [
                'name' => 'Apex Style Leather Wallet',
                'cat' => 'Accessories',
                'price' => 1200,
                'stock' => 100,
                'thumb' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1559564121-657e2d93e507?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'
                ],
                'desc' => 'Crafted from premium quality Bangladeshi cowhide leather. This slim bifold wallet features multiple card slots, two cash compartments, and RFID blocking technology.',
                'tags' => ['Local']
            ],
            [
                'name' => 'Handcrafted Terracotta Showpiece',
                'cat' => 'Handicrafts',
                'price' => 600,
                'stock' => 40,
                'thumb' => 'https://images.unsplash.com/photo-1610706249298-50800e28f219?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1610706249298-50800e28f219?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'
                ],
                'desc' => 'Made from the clay of the Padma river, this intricate terracotta showpiece reflects the rural aesthetics and pottery heritage of Bangladesh. Perfect for home or office decor.',
                'tags' => ['Handmade', 'Local']
            ],
        ];

        foreach ($productsData as $data) {
            $thumbMedia = $createMedia($data['thumb'], 'thumb-'.Str::slug($data['name']));
            
            $product = Product::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'category_id' => $categories[$data['cat']]->id,
                'price' => $data['price'],
                'stock_quantity' => $data['stock'],
                'thumbnail_media_id' => $thumbMedia->id,
                'description' => $data['desc'],
                'content' => '<p>' . $data['desc'] . '</p><h3>Specifications</h3><ul><li>Origin: Bangladesh</li><li>100% Authentic Quality Guaranteed</li></ul>',
                'status' => 'published'
            ]);

            // Add gallery
            foreach ($data['gallery'] as $idx => $gUrl) {
                $gMedia = $createMedia($gUrl, 'gallery-'.Str::slug($data['name']).'-'.$idx);
                $product->gallery()->attach($gMedia->id, ['sort_order' => $idx]);
            }

            // Attach Tags
            foreach ($data['tags'] as $tagName) {
                $product->tags()->attach($tags[$tagName]->id);
            }

            // Add to Flash Sale or Featured randomly to populate homepage blocks
            if (rand(0, 1) == 1) {
                $product->category_id = $categories['Flash Sale']->id;
                $product->save();
            } else if (rand(0, 1) == 1) {
                $product->category_id = $categories['Featured']->id;
                $product->save();
            }
        }
    }
}
