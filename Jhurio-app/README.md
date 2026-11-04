# Jhurio Mobile App (React Native & Expo)

Official cross-platform mobile shopping application for **Jhurio E-Commerce** ([https://jhurio.com](https://jhurio.com)), built with **React Native**, **Expo SDK 58**, **TypeScript**, and **Expo Router**.

---

## 🌟 Key Features

- **🏠 Dynamic Home Feed**:
  - Live hero slider banners (`GET /api/banners?type=slider`)
  - Interactive categories horizontal scroll (`GET /api/categories`)
  - Hot deals & featured products section
  - Latest products grid with real pricing and stock status
- **🔍 Catalog & Instant Search**:
  - Live search bar with debounced keyword filtering
  - Category pill filter tabs
  - Sort by Latest, Price (Low to High), and Price (High to Low)
- **🛍️ Product Details Screen**:
  - Image gallery carousel with swipe gestures & indicator dots
  - Dynamic Bundle Tier Offers (e.g. "Buy 2 Get 10% Off", "Buy 3 Get ৳300 Off")
  - Variation selector (color, model)
  - Rich Storytelling Cards (`image_text_sections`)
  - Verified customer reviews & star ratings
  - Sticky bottom purchase bar with 1-click Cash on Delivery
- **🛒 Cart & Checkout**:
  - Persistent shopping cart backed by `@react-native-async-storage/async-storage`
  - Steppers to adjust quantities or remove items
  - Shipping area selection (Inside Dhaka ৳70 / Outside Dhaka ৳120)
  - Coupon code input with live validation (`POST /api/coupons/apply`)
  - 1-Click Cash on Delivery order placement (`POST /api/orders`)
  - Instant Order Confirmation screen showing Order ID & details
- **📦 Order Tracking**:
  - Track orders by customer mobile number (`GET /api/orders?search={phone}`)
  - Real-time status tags: `Pending`, `Processing`, `Shipped`, `Delivered`, `Cancelled`
  - Displays ordered items, delivery address, date, and COD total

---

## 🚀 Quickstart Guide

### 1. Prerequisites
- **Node.js**: v18+ (tested on Node v22)
- **npm** or **yarn**
- **Expo Go App**: Install from Google Play Store (Android) or Apple App Store (iOS) on your physical phone, OR set up an Android Studio Emulator / Xcode Simulator.

### 2. Setup & Configuration

Navigate into the app directory:
```bash
cd Jhurio-app
```

Check the environment configuration in `.env`:
```env
EXPO_PUBLIC_API_URL=https://jhurio.com/api
EXPO_PUBLIC_API_TOKEN=prozRcAArjlDDhV8ZRI3qeVFKRYsIy8c
EXPO_PUBLIC_SITE_URL=https://jhurio.com
EXPO_PUBLIC_CURRENCY=৳
```

### 3. Run Development Server

```bash
# Start Expo development server (scan QR code with Expo Go app on phone):
npm start

# Or directly target Android Emulator / USB Connected Device:
npm run android

# Or target iOS Simulator:
npm run ios

# Or test in browser:
npm run web
```

---

## 📁 Project Architecture

```
Jhurio-app/
├── assets/                  # App icons, splash screens, tab bar icons
├── src/
│   ├── app/                 # Expo Router file-based pages
│   │   ├── _layout.tsx      # Root layout with CartProvider & ThemeProvider
│   │   ├── index.tsx        # Home Screen (Hero, Categories, Hot Deals, Latest)
│   │   ├── explore.tsx      # Shop & Search Screen (Filters, Sorting, Grid)
│   │   ├── cart.tsx         # Cart & Cash on Delivery Checkout
│   │   ├── orders.tsx       # Customer Order Tracking Screen
│   │   └── product/
│   │       └── [id].tsx     # Product Details (Gallery, Bundles, Reviews, Buy Now)
│   ├── components/          # Reusable UI components
│   │   ├── Header.tsx       # Brand Logo, Search Bar & Cart Counter
│   │   ├── ProductCard.tsx  # Product card with badges & Add to Cart
│   │   └── app-tabs.tsx     # Native / Web bottom navigation bar
│   ├── constants/           # Brand theme colors, spacing & API config
│   │   ├── config.ts        # Base URL, API Token, Currency
│   │   └── theme.ts         # Jhurio Emerald Green (#059669) & Amber palette
│   ├── context/
│   │   └── CartContext.tsx  # Global Cart & Customer details state (AsyncStorage)
│   ├── services/
│   │   └── api.ts           # Central API Client for Jhurio backend
│   └── types/
│       └── index.ts         # Full TypeScript definitions (Product, Order, etc.)
├── app.json                 # Expo configuration & Android package (com.jhurio.app)
└── package.json             # Dependencies & scripts
```

---

## 📱 Generating an Android APK / App Bundle

To build a standalone production APK or Google Play AAB using Expo Application Services (EAS):

1. Install EAS CLI:
   ```bash
   npm install -g eas-cli
   ```
2. Log in with your Expo account:
   ```bash
   eas login
   ```
3. Configure build credentials:
   ```bash
   eas build:configure
   ```
4. Build APK for Android testing:
   ```bash
   eas build --platform android --profile preview
   ```
5. Build production AAB for Google Play Store:
   ```bash
   eas build --platform android --profile production
   ```
