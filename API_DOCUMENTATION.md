# Jhurio Store API & CLI Documentation

Comprehensive guide for managing the live store ([https://jhurio.com](https://jhurio.com)) via API and the custom CLI tool.

---

## Table of Contents
1. [Credentials & Authentication](#1-credentials--authentication)
2. [CLI Tool Quickstart (`./jhurio-cli`)](#2-cli-tool-quickstart-jhurio-cli)
3. [CLI Command Reference](#3-cli-command-reference)
   - [Dashboard & System](#dashboard--system)
   - [Products Management](#products-management)
   - [Categories Management](#categories-management)
   - [Orders Management](#orders-management)
   - [Reviews Management](#reviews-management)
   - [Banners Management](#banners-management)
   - [Homepage Builder & Hero Banners](#homepage-builder--hero-banners)
   - [Site Settings](#site-settings)
   - [Stock & Inventory](#stock--inventory)
   - [Media Library](#media-library)
   - [Coupons](#coupons)
   - [Raw API Calls](#raw-api-calls)
4. [Full REST API Reference](#4-full-rest-api-reference)
5. [Direct Universal Action Runner (`POST /api/cli`)](#5-direct-universal-action-runner-post-apicli)
6. [Ready-to-Use Curl Recipes](#6-ready-to-use-curl-recipes)

---

## 1. Credentials & Authentication

| Parameter | Value |
|---|---|
| **Live Target Site** | `https://jhurio.com` |
| **API Base URL** | `https://jhurio.com/api` |
| **API Token** | `prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c` |
| **Header** | `Authorization: Bearer prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c` |
| **Content-Type** | `application/json` |
| **Accept** | `application/json` |

---

## 2. CLI Tool Quickstart (`./jhurio-cli`)

The project comes with a built-in CLI tool executable directly from the repository root:

```bash
# Direct runner (zero dependencies, uses built-in Python 3):
./jhurio-cli <subcommand> [options]

# Or via Laravel Artisan:
php artisan jhurio:cli <subcommand> [options]
```

### Global Options
- `--url <URL>`: Override target URL (default: `https://jhurio.com` or `JHURIO_API_URL` env).
- `--token <TOKEN>`: Override Bearer token (default: `prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c` or `JHURIO_API_TOKEN` env).
- `--json`: Output raw clean JSON instead of styled tables (ideal for piping into `jq`).
- `-v, --verbose`: Print debug HTTP requests and payloads.

---

## 3. CLI Command Reference

### Dashboard & System

```bash
# Check API connection & health:
./jhurio-cli ping

# Get live store summary dashboard (products, orders, revenue, reviews, banners):
./jhurio-cli stats
```

---

### Products Management

```bash
# 1. List all products (table formatted):
./jhurio-cli products list

# 2. Filter products by status, keyword, or category:
./jhurio-cli products list --status published
./jhurio-cli products list --search "চপার"
./jhurio-cli products list --category "kitchen-items" --limit 10

# 3. View detailed product by ID or Slug:
./jhurio-cli products get 19
./jhurio-cli products get roto-push-chopper

# 4. Quick adjust stock:
./jhurio-cli products set-stock 19 65

# 5. Quick update price:
./jhurio-cli products update 19 --price 1390 --compare-at-price 1890

# 6. Update product attributes via JSON:
./jhurio-cli products update 19 --data '{"status": "published", "stock_quantity": 40}'

# 7. Create product with full JSON (supports image URLs auto-download):
./jhurio-cli products create --data '{
  "name": "নতুন প্রিমিয়াম প্রোডাক্ট",
  "slug": "notun-premium-product",
  "price": 990,
  "compare_at_price": 1490,
  "purchase_price": 450,
  "stock_quantity": 50,
  "status": "published",
  "categories": ["Gadget", "Trending"],
  "tags": ["New", "Gadget"],
  "description": "<p>আকর্ষণীয় সংক্ষিপ্ত বিবরণ</p>",
  "content": "<div>বিস্তারিত বিবরণ</div>",
  "image_urls": [
    "https://raw.githubusercontent.com/Nerghum/jhurio/main/public/assets/images/products/sample.png"
  ]
}'

# 8. Delete a product:
./jhurio-cli products delete 19
```

---

### Categories Management

```bash
# List all categories with product counts:
./jhurio-cli categories list

# Create a category:
./jhurio-cli categories create --name "স্মার্ট গ্যাজেট" --slug "smart-gadget" --description "সেরা গ্যাজেট কালেকশন"

# Delete a category by ID or slug:
./jhurio-cli categories delete smart-gadget
```

---

### Orders Management

```bash
# List all orders:
./jhurio-cli orders list

# Filter orders by status (pending, processing, shipped, delivered, completed, cancelled):
./jhurio-cli orders list --status pending
./jhurio-cli orders list --search "01711"

# View complete order details (customer, items, delivery, audit logs):
./jhurio-cli orders get 101

# Update order status:
./jhurio-cli orders set-status 101 shipped --note "কুরিয়ারে হস্তান্তর করা হয়েছে"
./jhurio-cli orders set-status 101 delivered --note "গ্রাহক পণ্য গ্রহণ করেছেন"
```

---

### Reviews Management

```bash
# List reviews (optionally filter by product or status):
./jhurio-cli reviews list
./jhurio-cli reviews list --product-id 19
./jhurio-cli reviews list --status pending

# Add customer review:
./jhurio-cli reviews add --product-id 19 --name "কামরুল হাসান" --rating 5 --comment "অসাধারণ কোয়ালিটি, খুবই দ্রুত ডেলিভারি পেয়েছি!"

# Delete review:
./jhurio-cli reviews delete 85
```

---

### Banners Management

```bash
# List all banners (slider, right_top, right_bottom, bottom_strip):
./jhurio-cli banners list
./jhurio-cli banners list --type slider

# Create a new banner directly with an image URL:
./jhurio-cli banners create --type slider --title "শীতকালীন অফার" --image-url "https://example.com/banner.png" --link "/shop"

# Delete a banner:
./jhurio-cli banners delete 4
```

---

### Homepage Builder & Hero Banners

```bash
# List homepage builder sections (hero, flash_sale, product blocks, faq, etc.):
./jhurio-cli homepage list

# View single section data:
./jhurio-cli homepage get hero

# 1-Click Update Hero Section Banners:
./jhurio-cli homepage set-hero \
  --slider-image "https://jhurio.com/storage/media/banner1.png" \
  --slider-link "/shop" \
  --right-top-image "https://jhurio.com/storage/media/top.jpg" \
  --right-top-link "/shop?category=trending" \
  --right-bottom-image "https://jhurio.com/storage/media/bottom.jpg" \
  --right-bottom-link "/shop?category=winter-essentials"

# Update any homepage section JSON data:
./jhurio-cli homepage update flash_sale --data '{"title": "হট উইন্টার ফ্ল্যাশ সেল", "subtitle": "সীমিত সময়ের অফার"}'
```

---

### Site Settings

```bash
# List all store settings:
./jhurio-cli settings list

# Get a specific setting:
./jhurio-cli settings get site_name
./jhurio-cli settings get exit_popup_enabled

# Update a setting:
./jhurio-cli settings set exit_popup_enabled "1"
./jhurio-cli settings set order_bump_title "বিশেষ ডিসকাউন্টে এটিও যুক্ত করুন!"
```

---

### Stock & Inventory

```bash
# List inventory levels:
./jhurio-cli stock list

# Show low stock products (<= 5 units):
./jhurio-cli stock list --low

# Adjust stock:
./jhurio-cli stock adjust --product-id 19 --quantity 45 --notes "মাসিক ইনভেন্টরি অডিট"

# Record stock purchase (updates cost & inventory):
./jhurio-cli stock purchase --product-id 19 --quantity 50 --purchase-price 320 --supplier "ঢাকা ইম্পোর্টস"
```

---

### Media Library

```bash
# List uploaded media:
./jhurio-cli media list --limit 20

# Upload an image to media library from any public URL:
./jhurio-cli media upload --url "https://example.com/product.jpg"
```

---

### Coupons

```bash
# List coupons:
./jhurio-cli raw GET /api/coupons

# Create discount coupon:
./jhurio-cli raw POST /api/coupons --data '{
  "code": "WINTER50",
  "type": "fixed",
  "value": 50,
  "min_order_value": 500,
  "is_active": true
}'
```

---

### Raw API Calls

Send any raw HTTP request with automatic authentication:

```bash
# GET request:
./jhurio-cli raw GET /api/orders

# POST request:
./jhurio-cli raw POST /api/cli --data '{"action": "stats"}'
```

---

## 4. Full REST API Reference

All endpoints require header: `Authorization: Bearer <token>`.

### Products
| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/products` | List products. Filters: `?status=`, `?search=`, `?category=`, `?limit=` |
| `GET` | `/api/products/{id_or_slug}` | Detailed product with gallery, variations, reviews |
| `POST` | `/api/products` | Create product (downloads `image_urls`, attaches tags & categories) |
| `PUT / PATCH` | `/api/products/{id_or_slug}` | Update product fields, prices, stock, image sections |
| `DELETE` | `/api/products/{id_or_slug}` | Delete product |

### Categories
| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/categories` | List all categories with product counts |
| `GET` | `/api/categories/{id_or_slug}` | Single category |
| `POST` | `/api/categories` | Create category (`name`, `slug`, `description`, pixels) |
| `PUT / PATCH` | `/api/categories/{id_or_slug}` | Update category |
| `DELETE` | `/api/categories/{id_or_slug}` | Delete category |

### Orders
| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/orders` | List orders (`?status=`, `?search=`, `?limit=`) |
| `GET` | `/api/orders/{id}` | Complete order with items, customer, delivery, logs |
| `PUT / PATCH` | `/api/orders/{id}` | Update order status, note, shipping address |
| `DELETE` | `/api/orders/{id}` | Delete order |

### Reviews
| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/reviews` | List reviews (`?product_id=`, `?status=`) |
| `POST` | `/api/reviews` | Add single customer review |
| `POST` | `/api/reviews/batch` | Add batch reviews in 1 call |
| `PUT / PATCH` | `/api/reviews/{id}` | Update status (`approved`, `pending`, `spam`) |
| `DELETE` | `/api/reviews/{id}` | Delete review |

### Banners
| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/banners` | List banners (`?type=slider|right_top|right_bottom|bottom_strip`) |
| `POST` | `/api/banners` | Create banner with `image_url` or `media_id` |
| `PUT / PATCH` | `/api/banners/{id}` | Update banner image, title, link, order |
| `DELETE` | `/api/banners/{id}` | Delete banner |

### Homepage Sections
| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/homepage-sections` | List all homepage sections |
| `GET` | `/api/homepage-sections/{id_or_type}` | Get section details |
| `POST` | `/api/homepage-sections` | Create new section |
| `POST` | `/api/homepage-sections/hero` | Quick update hero banner images & links |
| `PUT / PATCH` | `/api/homepage-sections/{id_or_type}` | Update section data, title, subtitle, order |
| `DELETE` | `/api/homepage-sections/{id_or_type}` | Delete section |

### Settings
| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/settings` | Get all settings (`?keys=site_name,exit_popup_enabled`) |
| `GET` | `/api/settings/{key}` | Get single setting value |
| `POST` | `/api/settings` | Update setting key-value pairs |

### Stock
| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/stock` | List product stock levels (`?low_stock=1`) |
| `POST` | `/api/stock/adjust` | Adjust stock quantity (`product_id`, `stock_quantity` or `delta`) |
| `POST` | `/api/stock/purchases` | Record stock purchase log |

### Media Library
| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/media` | List media files |
| `POST` | `/api/media` | Upload media via `image_url` or file |
| `DELETE` | `/api/media/{id}` | Delete media file |

---

## 5. Direct Universal Action Runner (`POST /api/cli`)

Use `POST /api/cli` for rapid operations:

```json
// Stats
POST /api/cli
{ "action": "stats" }

// Query any model
POST /api/cli
{
  "action": "model_query",
  "model": "Order",
  "where": { "status": "pending" },
  "limit": 10
}

// Update any model record
POST /api/cli
{
  "action": "model_update",
  "model": "Product",
  "id": 15,
  "data": { "stock_quantity": 45, "price": 890 }
}

// Quick order status
POST /api/cli
{
  "action": "quick_order",
  "order_id": 101,
  "status": "shipped"
}

// Quick hero banner update
POST /api/cli
{
  "action": "quick_hero",
  "slider_image": "https://jhurio.com/storage/media/banner.png",
  "slider_link": "/shop"
}
```

---

## 6. Ready-to-Use Curl Recipes

### Get Dashboard Metrics
```bash
curl -s -X POST https://jhurio.com/api/cli \
  -H "Authorization: Bearer prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c" \
  -H "Content-Type: application/json" \
  -d '{"action": "stats"}'
```

### List Products
```bash
curl -s -X GET "https://jhurio.com/api/products?limit=10" \
  -H "Authorization: Bearer prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c"
```

### Update Product Price & Stock
```bash
curl -s -X PUT "https://jhurio.com/api/products/19" \
  -H "Authorization: Bearer prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c" \
  -H "Content-Type: application/json" \
  -d '{"price": 1390, "stock_quantity": 40}'
```

### Update Order Status
```bash
curl -s -X PUT "https://jhurio.com/api/orders/101" \
  -H "Authorization: Bearer prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c" \
  -H "Content-Type: application/json" \
  -d '{"status": "delivered", "note": "Delivered successfully"}'
```

### Update Hero Banner
```bash
curl -s -X POST https://jhurio.com/api/homepage-sections/hero \
  -H "Authorization: Bearer prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c" \
  -H "Content-Type: application/json" \
  -d '{
    "slider_image": "https://jhurio.com/storage/media/banner.png",
    "slider_link": "/shop"
  }'
```
