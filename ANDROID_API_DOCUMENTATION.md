# Jhurio Android App API Documentation

Complete developer guide and API reference for building the native **Jhurio E-Commerce Android App** (Kotlin / Java).

---

## Table of Contents
1. [Base Configuration & Credentials](#1-base-configuration--credentials)
2. [Android Network Architecture (Retrofit & OkHttp)](#2-android-network-architecture-retrofit--okhttp)
3. [Kotlin Data Transfer Objects (DTOs)](#3-kotlin-data-transfer-objects-dtos)
4. [Retrofit API Service Interface (`JhurioApiService.kt`)](#4-retrofit-api-service-interface-jhurioapiservicekt)
5. [Screen-by-Screen API Integration Guide](#5-screen-by-screen-api-integration-guide)
   - [Screen 1: Splash / App Launch & Config](#screen-1-splash--app-launch--config)
   - [Screen 2: Home Screen (Banners, Categories, Sections)](#screen-2-home-screen-banners-categories-sections)
   - [Screen 3: Product Catalog, Search & Filters](#screen-3-product-catalog-search--filters)
   - [Screen 4: Product Detail Screen (PDP)](#screen-4-product-detail-screen-pdp)
   - [Screen 5: Customer Reviews](#screen-5-customer-reviews)
   - [Screen 6: Delivery Methods & Coupon Discount](#screen-6-delivery-methods--coupon-discount)
   - [Screen 7: Cash on Delivery Checkout & Order Placement](#screen-7-cash-on-delivery-checkout--order-placement)
   - [Screen 8: Order Tracking & Customer Order History](#screen-8-order-tracking--customer-order-history)
   - [Screen 9: Analytics & Event Tracking](#screen-9-analytics--event-tracking)
6. [Complete Curl Test Recipes](#6-complete-curl-test-recipes)

---

## 1. Base Configuration & Credentials

All API requests from the Android app communicate with the live Jhurio backend via HTTPS.

| Parameter | Value | Notes |
|---|---|---|
| **Base URL** | `https://jhurio.com/api/` | All API routes start with this prefix |
| **Auth Type** | Bearer Token | Passed in HTTP header |
| **API Token** | `prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c` | Production Token |
| **Header** | `Authorization: Bearer <API_TOKEN>` | Required on all `/api/*` endpoints |
| **Content-Type** | `application/json` | Required for all POST/PUT requests |
| **Accept** | `application/json` | Enforces JSON error responses |

---

## 2. Android Network Architecture (Retrofit & OkHttp)

### Dependencies (`build.gradle.kts` - app level)

```kotlin
dependencies {
    // Retrofit & Moshi Converter
    implementation("com.squareup.retrofit2:retrofit:2.11.0")
    implementation("com.squareup.retrofit2:converter-moshi:2.11.0")
    implementation("com.squareup.moshi:moshi-kotlin:1.15.1")

    // OkHttp & Logging Interceptor
    implementation("com.squareup.okhttp3:okhttp:4.12.0")
    implementation("com.squareup.okhttp3:logging-interceptor:4.12.0")

    // Image Loading (Coil for Jetpack Compose or Views)
    implementation("io.coil-kt:coil-compose:2.6.0")

    // Kotlin Coroutines
    implementation("org.jetbrains.kotlinx:kotlinx-coroutines-android:1.8.1")
}
```

### OkHttp Client with Auth Interceptor (`ApiClient.kt`)

```kotlin
package com.jhurio.app.data.network

import okhttp3.Interceptor
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.moshi.MoshiConverterFactory
import com.squareup.moshi.Moshi
import com.squareup.moshi.kotlin.reflect.KotlinJsonAdapterFactory
import java.util.concurrent.TimeUnit

object ApiClient {
    private const val BASE_URL = "https://jhurio.com/api/"
    private const val API_TOKEN = "prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c"

    private val authInterceptor = Interceptor { chain ->
        val original = chain.request()
        val request = original.newBuilder()
            .header("Authorization", "Bearer $API_TOKEN")
            .header("Accept", "application/json")
            .header("Content-Type", "application/json")
            .build()
        chain.proceed(request)
    }

    private val loggingInterceptor = HttpLoggingInterceptor().apply {
        level = HttpLoggingInterceptor.Level.BODY
    }

    private val okHttpClient = OkHttpClient.Builder()
        .addInterceptor(authInterceptor)
        .addInterceptor(loggingInterceptor)
        .connectTimeout(30, TimeUnit.SECONDS)
        .readTimeout(30, TimeUnit.SECONDS)
        .writeTimeout(30, TimeUnit.SECONDS)
        .build()

    private val moshi = Moshi.Builder()
        .addLast(KotlinJsonAdapterFactory())
        .build()

    val apiService: JhurioApiService by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .client(okHttpClient)
            .addConverterFactory(MoshiConverterFactory.create(moshi))
            .build()
            .create(JhurioApiService::class.java)
    }
}
```

---

## 3. Kotlin Data Transfer Objects (DTOs)

Save in `com.jhurio.app.data.model`:

```kotlin
package com.jhurio.app.data.model

import com.squareup.moshi.Json

// ─── Settings DTOs ──────────────────────────────────────────
data class SettingsResponse(
    @Json(name = "settings") val settings: Map<String, String?>
)

// ─── Banner DTOs ────────────────────────────────────────────
data class BannerListResponse(
    @Json(name = "total") val total: Int,
    @Json(name = "banners") val banners: List<BannerDto>
)

data class BannerDto(
    @Json(name = "id") val id: Int,
    @Json(name = "title") val title: String?,
    @Json(name = "type") val type: String, // slider, right_top, right_bottom, bottom_strip
    @Json(name = "image_url") val imageUrl: String?,
    @Json(name = "link_url") val linkUrl: String?,
    @Json(name = "is_active") val isActive: Boolean,
    @Json(name = "order") val order: Int,
    @Json(name = "media") val media: MediaDto?
)

data class MediaDto(
    @Json(name = "id") val id: Int,
    @Json(name = "url") val url: String,
    @Json(name = "filename") val filename: String?
)

// ─── Category DTOs ──────────────────────────────────────────
data class CategoryListResponse(
    @Json(name = "total") val total: Int,
    @Json(name = "categories") val categories: List<CategoryDto>
)

data class CategoryDto(
    @Json(name = "id") val id: Int,
    @Json(name = "name") val name: String,
    @Json(name = "slug") val slug: String,
    @Json(name = "description") val description: String?,
    @Json(name = "products_count") val productsCount: Int? = 0,
    @Json(name = "media") val media: MediaDto?
)

// ─── Product DTOs ───────────────────────────────────────────
data class ProductListResponse(
    @Json(name = "total") val total: Int,
    @Json(name = "products") val products: List<ProductDto>,
    @Json(name = "current_page") val currentPage: Int? = 1,
    @Json(name = "last_page") val lastPage: Int? = 1
)

data class ProductDetailResponse(
    @Json(name = "product") val product: ProductDto
)

data class ProductDto(
    @Json(name = "id") val id: Int,
    @Json(name = "name") val name: String,
    @Json(name = "slug") val slug: String,
    @Json(name = "price") val price: String,
    @Json(name = "compare_at_price") val compareAtPrice: String?,
    @Json(name = "stock_quantity") val stockQuantity: Int?,
    @Json(name = "status") val status: String,
    @Json(name = "is_featured") val isFeatured: Boolean? = false,
    @Json(name = "description") val description: String?,
    @Json(name = "content") val content: String?,
    @Json(name = "thumbnail") val thumbnail: MediaDto?,
    @Json(name = "gallery") val gallery: List<MediaDto>? = emptyList(),
    @Json(name = "categories") val categories: List<CategoryDto>? = emptyList(),
    @Json(name = "options") val options: List<ProductOptionDto>? = emptyList(),
    @Json(name = "bundle_settings") val bundleSettings: BundleSettingsDto?,
    @Json(name = "image_text_sections") val imageTextSections: List<ImageTextSectionDto>? = emptyList(),
    @Json(name = "video_url") val videoUrl: String?,
    @Json(name = "approved_reviews_count") val approvedReviewsCount: Int? = 0
)

data class ProductOptionDto(
    @Json(name = "name") val name: String,
    @Json(name = "values") val values: List<ProductOptionValueDto>?
)

data class ProductOptionValueDto(
    @Json(name = "name") val name: String,
    @Json(name = "color_code") val colorCode: String?
)

data class BundleSettingsDto(
    @Json(name = "enabled") val enabled: Boolean,
    @Json(name = "heading") val heading: String?,
    @Json(name = "subtitle") val subtitle: String?,
    @Json(name = "tiers") val tiers: List<BundleTierDto>?
)

data class BundleTierDto(
    @Json(name = "quantity") val quantity: Int,
    @Json(name = "title") val title: String,
    @Json(name = "badge") val badge: String?,
    @Json(name = "discount_type") val discountType: String, // percentage or fixed
    @Json(name = "discount_value") val discountValue: Double
)

data class ImageTextSectionDto(
    @Json(name = "id") val id: String?,
    @Json(name = "title") val title: String,
    @Json(name = "subtitle") val subtitle: String?,
    @Json(name = "content") val content: String,
    @Json(name = "image_url") val imageUrl: String,
    @Json(name = "button_text") val buttonText: String?,
    @Json(name = "button_url") val buttonUrl: String?,
    @Json(name = "image_position") val imagePosition: String? // left or right
)

// ─── Review DTOs ────────────────────────────────────────────
data class ReviewListResponse(
    @Json(name = "total") val total: Int,
    @Json(name = "reviews") val reviews: List<ReviewDto>,
    @Json(name = "current_page") val currentPage: Int? = 1,
    @Json(name = "last_page") val lastPage: Int? = 1
)

data class ReviewDto(
    @Json(name = "id") val id: Int,
    @Json(name = "product_id") val productId: Int,
    @Json(name = "reviewer_name") val reviewerName: String,
    @Json(name = "rating") val rating: Int,
    @Json(name = "comment") val comment: String,
    @Json(name = "status") val status: String,
    @Json(name = "created_at") val createdAt: String
)

data class AddReviewRequest(
    @Json(name = "product_id") val productId: Int,
    @Json(name = "reviewer_name") val reviewerName: String,
    @Json(name = "rating") val rating: Int,
    @Json(name = "comment") val comment: String,
    @Json(name = "reviewer_phone") val reviewerPhone: String? = null
)

// ─── Delivery Method DTOs ───────────────────────────────────
data class DeliveryMethodsResponse(
    @Json(name = "total") val total: Int,
    @Json(name = "delivery_methods") val deliveryMethods: List<DeliveryMethodDto>
)

data class DeliveryMethodDto(
    @Json(name = "id") val id: Int,
    @Json(name = "name") val name: String,
    @Json(name = "cost") val cost: String,
    @Json(name = "is_active") val isActive: Boolean
)

// ─── Coupon DTOs ────────────────────────────────────────────
data class ApplyCouponRequest(
    @Json(name = "code") val code: String,
    @Json(name = "order_total") val orderTotal: Double
)

data class ApplyCouponResponse(
    @Json(name = "success") val success: Boolean,
    @Json(name = "message") val message: String,
    @Json(name = "coupon_code") val couponCode: String?,
    @Json(name = "type") val type: String?,
    @Json(name = "value") val value: Double?,
    @Json(name = "discount_amount") val discountAmount: Double?,
    @Json(name = "final_total") val finalTotal: Double?
)

// ─── Order & Checkout DTOs ──────────────────────────────────
data class CreateOrderRequest(
    @Json(name = "name") val name: String,
    @Json(name = "phone") val phone: String,
    @Json(name = "address") val address: String,
    @Json(name = "district") val district: String,
    @Json(name = "delivery_method_id") val deliveryMethodId: Int,
    @Json(name = "coupon_code") val couponCode: String? = null,
    // Single 1-Click Express:
    @Json(name = "product_id") val productId: Int? = null,
    @Json(name = "variation_id") val variationId: Int? = null,
    @Json(name = "quantity") val quantity: Int? = null,
    @Json(name = "bundle_info") val bundleInfo: Map<String, Any>? = null,
    // Multi-Item Cart:
    @Json(name = "items") val items: List<OrderItemRequest>? = null,
    // Tracking & Bump:
    @Json(name = "order_bump_product_id") val orderBumpProductId: Int? = null,
    @Json(name = "visitor_id") val visitorId: String? = null,
    @Json(name = "session_id") val sessionId: String? = null
)

data class OrderItemRequest(
    @Json(name = "product_id") val productId: Int,
    @Json(name = "quantity") val quantity: Int,
    @Json(name = "variation_id") val variationId: Int? = null
)

data class CreateOrderResponse(
    @Json(name = "success") val success: Boolean,
    @Json(name = "message") val message: String,
    @Json(name = "order") val order: OrderDto
)

data class OrderListResponse(
    @Json(name = "total") val total: Int,
    @Json(name = "orders") val orders: List<OrderDto>,
    @Json(name = "current_page") val currentPage: Int? = 1,
    @Json(name = "last_page") val lastPage: Int? = 1
)

data class OrderDetailResponse(
    @Json(name = "order") val order: OrderDto
)

data class OrderDto(
    @Json(name = "id") val id: Int,
    @Json(name = "name") val name: String,
    @Json(name = "phone") val phone: String,
    @Json(name = "shipping_address") val shippingAddress: String,
    @Json(name = "total_amount") val totalAmount: String,
    @Json(name = "discount_amount") val discountAmount: String?,
    @Json(name = "delivery_cost") val deliveryCost: String?,
    @Json(name = "coupon_code") val couponCode: String?,
    @Json(name = "status") val status: String, // pending, processing, shipped, delivered, cancelled
    @Json(name = "created_at") val createdAt: String,
    @Json(name = "items") val items: List<OrderItemDto>? = emptyList(),
    @Json(name = "delivery_method") val deliveryMethod: DeliveryMethodDto?,
    @Json(name = "logs") val logs: List<OrderLogDto>? = emptyList()
)

data class OrderItemDto(
    @Json(name = "id") val id: Int,
    @Json(name = "product_id") val productId: Int,
    @Json(name = "product_name") val productName: String,
    @Json(name = "variation_name") val variationName: String?,
    @Json(name = "price") val price: String,
    @Json(name = "quantity") val quantity: Int,
    @Json(name = "product") val product: ProductDto?
)

data class OrderLogDto(
    @Json(name = "id") val id: Int,
    @Json(name = "status") val status: String,
    @Json(name = "note") val note: String?,
    @Json(name = "created_at") val createdAt: String
)

// ─── Telemetry / Tracking DTO ───────────────────────────────
data class TrackEventRequest(
    @Json(name = "visitor_id") val visitorId: String?,
    @Json(name = "session_id") val sessionId: String?,
    @Json(name = "payload") val payload: TrackPayload
)

data class TrackPayload(
    @Json(name = "event_type") val eventType: String, // page_view, view_product, add_to_cart, purchase
    @Json(name = "url") val url: String,
    @Json(name = "product_id") val productId: Int? = null,
    @Json(name = "metadata") val metadata: Map<String, Any>? = null
)
```

---

## 4. Retrofit API Service Interface (`JhurioApiService.kt`)

```kotlin
package com.jhurio.app.data.network

import com.jhurio.app.data.model.*
import retrofit2.Response
import retrofit2.http.*

interface JhurioApiService {

    // ─── 1. Health & Store Settings ─────────────────────────
    @GET("ping")
    suspend fun ping(): Response<Map<String, Any>>

    @GET("settings")
    suspend fun getSettings(
        @Query("keys") keys: String? = null
    ): Response<SettingsResponse>

    // ─── 2. Banners & Homepage ──────────────────────────────
    @GET("banners")
    suspend fun getBanners(
        @Query("type") type: String? = null, // "slider", "right_top", "right_bottom", "bottom_strip"
        @Query("is_active") isActive: Boolean = true
    ): Response<BannerListResponse>

    @GET("homepage-sections")
    suspend fun getHomepageSections(): Response<Map<String, Any>>

    // ─── 3. Categories ──────────────────────────────────────
    @GET("categories")
    suspend fun getCategories(
        @Query("limit") limit: Int? = null
    ): Response<CategoryListResponse>

    @GET("categories/{id_or_slug}")
    suspend fun getCategory(
        @Path("id_or_slug") idOrSlug: String
    ): Response<Map<String, Any>>

    // ─── 4. Products ────────────────────────────────────────
    @GET("products")
    suspend fun getProducts(
        @Query("status") status: String = "published",
        @Query("search") search: String? = null,
        @Query("category") category: String? = null,
        @Query("is_featured") isFeatured: Boolean? = null,
        @Query("limit") limit: Int = 20,
        @Query("page") page: Int = 1
    ): Response<ProductListResponse>

    @GET("products/{id_or_slug}")
    suspend fun getProductDetail(
        @Path("id_or_slug") idOrSlug: String
    ): Response<ProductDetailResponse>

    // ─── 5. Reviews ─────────────────────────────────────────
    @GET("reviews")
    suspend fun getReviews(
        @Query("product_id") productId: Int,
        @Query("status") status: String = "approved",
        @Query("limit") limit: Int = 50
    ): Response<ReviewListResponse>

    @POST("reviews")
    suspend fun submitReview(
        @Body reviewRequest: AddReviewRequest
    ): Response<Map<String, Any>>

    // ─── 6. Delivery & Coupons ──────────────────────────────
    @GET("delivery-methods")
    suspend fun getDeliveryMethods(): Response<DeliveryMethodsResponse>

    @POST("coupons/apply")
    suspend fun applyCoupon(
        @Body couponRequest: ApplyCouponRequest
    ): Response<ApplyCouponResponse>

    // ─── 7. Checkout & Orders ───────────────────────────────
    @POST("orders")
    suspend fun createOrder(
        @Body orderRequest: CreateOrderRequest
    ): Response<CreateOrderResponse>

    @GET("orders")
    suspend fun getCustomerOrders(
        @Query("search") customerPhoneOrOrderId: String,
        @Query("limit") limit: Int = 20,
        @Query("page") page: Int = 1
    ): Response<OrderListResponse>

    @GET("orders/{id}")
    suspend fun getOrderDetail(
        @Path("id") id: Int
    ): Response<OrderDetailResponse>

    // ─── 8. Analytics & Telemetry ───────────────────────────
    @POST("track")
    suspend fun trackEvent(
        @Body eventRequest: TrackEventRequest
    ): Response<Map<String, Any>>
}
```

---

## 5. Screen-by-Screen API Integration Guide

### Screen 1: Splash / App Launch & Config

Fetch store brand settings on launch (app title, WhatsApp support number, currency, delivery defaults).

- **Endpoint**: `GET /api/settings`
- **Optional Filter**: `?keys=site_name,site_logo,contact_phone,whatsapp_number,currency_symbol`

#### Sample Response:
```json
{
  "settings": {
    "site_name": "Jhurio",
    "site_tagline": "সেরা পণ্যের বিশ্বস্ত ঠিকানা",
    "currency_symbol": "৳",
    "contact_phone": "017XXXXXXXX",
    "whatsapp_number": "88017XXXXXXXX",
    "default_delivery_charge": "70"
  }
}
```

---

### Screen 2: Home Screen (Banners, Categories, Sections)

The Android home screen combines hero banners, categories grid, and trending/featured products.

#### A. Main Hero Carousel
- **Endpoint**: `GET /api/banners?type=slider&is_active=1`
- **Response**: List of banners with `media.url` or `image_url` and target link (`link_url`).

#### B. Categories Grid
- **Endpoint**: `GET /api/categories`
- **Response**: List of categories with product counts and `media.url` thumbnail icon.

#### C. Featured / Trending Products
- **Endpoint**: `GET /api/products?is_featured=1&status=published&limit=10`

---

### Screen 3: Product Catalog, Search & Filters

Use for search bar and category product lists. Supports pagination (`page=1, 2, ...`).

#### Search by Keyword
- **Endpoint**: `GET /api/products?search={keyword}&limit=20&page=1`

#### Filter by Category
- **Endpoint**: `GET /api/products?category={category_slug}&limit=20&page=1`
- Example: `GET /api/products?category=smart-gadget`

#### Sample Response:
```json
{
  "total": 35,
  "current_page": 1,
  "last_page": 2,
  "products": [
    {
      "id": 19,
      "name": "Roto Push Chopper – Fast Vegetable & Onion Cutter",
      "slug": "roto-push-chopper-ultra-fast-vegetable-onion-cutter",
      "price": "750.00",
      "compare_at_price": "1250.00",
      "stock_quantity": 40,
      "status": "published",
      "is_featured": true,
      "approved_reviews_count": 8,
      "thumbnail": {
        "id": 154,
        "url": "https://jhurio.com/storage/products/push-chopper-main.jpg"
      }
    }
  ]
}
```

---

### Screen 4: Product Detail Screen (PDP)

Loads the entire product detail including gallery images, bundle discounts, rich HTML content, and demonstration video reels.

- **Endpoint**: `GET /api/products/{id_or_slug}`
- **Example**: `GET /api/products/roto-push-chopper-ultra-fast-vegetable-onion-cutter`

#### Key UI Components Driven by this Endpoint:
1. **Image Carousel**: Use `thumbnail.url` as image #1, followed by `gallery` items (`gallery[i].url`).
2. **Pricing & Badges**: Display `price` (৳) and strikethrough `compare_at_price`.
3. **Bundle Tier Selector** (`bundle_settings`):
   - When `bundle_settings.enabled == true`, show bundle selector buttons:
     - 1 pc: Regular price
     - 2 pcs: "Buy 2 Get 10% Off" (`quantity: 2, discount_type: "percentage", discount_value: 10`)
     - 3 pcs: "Buy 3 Get ৳300 Off" (`quantity: 3, discount_type: "fixed", discount_value: 300`)
4. **Rich Storytelling Section** (`image_text_sections`):
   - Renders highlight cards with `image_url`, `title`, and `content` to drive high mobile conversion.

---

### Screen 5: Customer Reviews

#### Fetch Reviews for Product:
- **Endpoint**: `GET /api/reviews?product_id={product_id}&status=approved`

#### Submit New Review:
- **Endpoint**: `POST /api/reviews`
- **Request Body**:
```json
{
  "product_id": 19,
  "reviewer_name": "তানভীর আহমেদ",
  "rating": 5,
  "comment": "খুবই ভালো এবং শক্ত কোয়ালিটি। পেঁয়াজ চপিং খুব দ্রুত হয়!",
  "reviewer_phone": "01711223344"
}
```
- **Response**: `201 Created` with review object.

---

### Screen 6: Delivery Methods & Coupon Discount

#### Fetch Available Delivery Charges:
- **Endpoint**: `GET /api/delivery-methods`

#### Sample Response:
```json
{
  "total": 2,
  "delivery_methods": [
    {
      "id": 1,
      "name": "Inside Dhaka",
      "cost": "70.00",
      "is_active": true
    },
    {
      "id": 2,
      "name": "Outside Dhaka",
      "cost": "120.00",
      "is_active": true
    }
  ]
}
```

#### Validate & Apply Coupon:
- **Endpoint**: `POST /api/coupons/apply`
- **Request Body**:
```json
{
  "code": "WINTER50",
  "order_total": 1500.00
}
```
- **Success Response**:
```json
{
  "success": true,
  "message": "Coupon applied successfully!",
  "coupon_code": "WINTER50",
  "type": "fixed",
  "value": 50.0,
  "discount_amount": 50.0,
  "final_total": 1450.0
}
```

---

### Screen 7: Cash on Delivery Checkout & Order Placement

Place orders seamlessly. The server automatically:
1. Validates product stock and decrements inventory.
2. Creates or links the customer account by phone number.
3. Saves the customer's delivery address for 1-click reorders.
4. Calculates coupon discounts and shipping charges.
5. Sends instant Firebase Push notifications to admin devices.
6. Records Facebook Conversions API (CAPI) Purchase events.

- **Endpoint**: `POST /api/orders`
- **Headers**:
  - `Authorization: Bearer prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c`
  - `Content-Type: application/json`

#### Option A: 1-Click Express Checkout (Single Product):
```json
{
  "name": "রাকিবুল হাসান",
  "phone": "01711223344",
  "address": "বাড়ি ১২, রোড ৫, সেক্টর ৩, উত্তরা",
  "district": "ঢাকা",
  "delivery_method_id": 1,
  "product_id": 19,
  "quantity": 1,
  "coupon_code": "WINTER50"
}
```

#### Option B: Multi-Item Shopping Cart Checkout:
```json
{
  "name": "রাকিবুল হাসান",
  "phone": "01711223344",
  "address": "বাড়ি ১২, রোড ৫, সেক্টর ৩, উত্তরা",
  "district": "ঢাকা",
  "delivery_method_id": 1,
  "coupon_code": null,
  "items": [
    {
      "product_id": 19,
      "quantity": 2,
      "variation_id": null
    },
    {
      "product_id": 13,
      "quantity": 1,
      "variation_id": null
    }
  ]
}
```

#### Success Response (`201 Created`):
```json
{
  "success": true,
  "message": "Order placed successfully",
  "order": {
    "id": 105,
    "name": "রাকিবুল হাসান",
    "phone": "01711223344",
    "shipping_address": "বাড়ি ১২, রোড ৫, সেক্টর ৩, উত্তরা, ঢাকা",
    "total_amount": "820.00",
    "discount_amount": "0.00",
    "delivery_cost": "70.00",
    "coupon_code": null,
    "status": "pending",
    "created_at": "2026-11-03T17:30:00.000000Z",
    "items": [
      {
        "id": 120,
        "product_id": 19,
        "product_name": "Roto Push Chopper – Fast Vegetable & Onion Cutter",
        "variation_name": null,
        "price": "750.00",
        "quantity": 1
      }
    ],
    "delivery_method": {
      "id": 1,
      "name": "Inside Dhaka",
      "cost": "70.00"
    },
    "logs": [
      {
        "id": 85,
        "status": "pending",
        "note": "Order placed via API / Mobile App",
        "created_at": "2026-11-03T17:30:00.000000Z"
      }
    ]
  }
}
```

---

### Screen 8: Order Tracking & Customer Order History

#### A. Track Single Order:
- **Endpoint**: `GET /api/orders/{id}`
- **Example**: `GET /api/orders/105`
- Returns order status (`pending` ➔ `processing` ➔ `shipped` ➔ `delivered`), delivery cost, and full audit trail in `logs`.

#### B. Customer Order History by Phone Number:
Allow customers to see all their past orders by entering their mobile number:
- **Endpoint**: `GET /api/orders?search={phone}`
- **Example**: `GET /api/orders?search=01711223344`

---

### Screen 9: Analytics & Event Tracking

Send user actions to the store's central analytics dashboard.

- **Endpoint**: `POST /api/track`
- **Request Body**:
```json
{
  "visitor_id": "android-uuid-device-1234",
  "session_id": "session-uuid-5678",
  "payload": {
    "event_type": "view_product",
    "url": "app://product/19",
    "product_id": 19,
    "metadata": {
      "platform": "Android",
      "app_version": "1.0.0"
    }
  }
}
```

---

## 6. Complete Curl Test Recipes

### 1. Test Server Ping
```bash
curl -s -X GET "https://jhurio.com/api/ping" \
  -H "Authorization: Bearer prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c"
```

### 2. Fetch Store Settings
```bash
curl -s -X GET "https://jhurio.com/api/settings" \
  -H "Authorization: Bearer prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c"
```

### 3. Fetch Home Sliders
```bash
curl -s -X GET "https://jhurio.com/api/banners?type=slider" \
  -H "Authorization: Bearer prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c"
```

### 4. Fetch Products Catalog
```bash
curl -s -X GET "https://jhurio.com/api/products?limit=10" \
  -H "Authorization: Bearer prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c"
```

### 5. Fetch Delivery Methods
```bash
curl -s -X GET "https://jhurio.com/api/delivery-methods" \
  -H "Authorization: Bearer prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c"
```

### 6. Apply Coupon Code
```bash
curl -s -X POST "https://jhurio.com/api/coupons/apply" \
  -H "Authorization: Bearer prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c" \
  -H "Content-Type: application/json" \
  -d '{"code": "WINTER50", "order_total": 1500}'
```

### 7. Place Cash on Delivery Order
```bash
curl -s -X POST "https://jhurio.com/api/orders" \
  -H "Authorization: Bearer prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Ashikur Rahman",
    "phone": "01700000000",
    "address": "Dhanmondi 27",
    "district": "Dhaka",
    "delivery_method_id": 1,
    "product_id": 19,
    "quantity": 1
  }'
```

### 8. Track Customer Order
```bash
curl -s -X GET "https://jhurio.com/api/orders/105" \
  -H "Authorization: Bearer prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c"
```
