export interface Media {
  id: number;
  url: string;
  filename?: string;
  file_path?: string;
}

export interface Category {
  id: number;
  name: string;
  slug: string;
  description?: string;
  products_count?: number;
  media?: Media;
}

export interface Banner {
  id: number;
  title?: string;
  type: 'slider' | 'right_top' | 'right_bottom' | 'bottom_strip' | string;
  image_url?: string;
  link_url?: string;
  is_active: boolean;
  order: number;
  media?: Media;
}

export interface ProductOptionValue {
  name: string;
  color_code?: string;
}

export interface ProductOption {
  name: string;
  values?: ProductOptionValue[];
}

export interface BundleTier {
  quantity: number;
  title: string;
  badge?: string;
  discount_type: 'percentage' | 'fixed';
  discount_value: number;
}

export interface BundleSettings {
  enabled: boolean;
  heading?: string;
  subtitle?: string;
  tiers?: BundleTier[];
}

export interface ImageTextSection {
  id?: string;
  title: string;
  subtitle?: string;
  content: string;
  image_url: string;
  button_text?: string;
  button_url?: string;
  image_position?: 'left' | 'right';
}

export interface ProductVariation {
  id: number;
  product_id: number;
  name: string;
  price?: string | number;
  stock_quantity?: number;
}

export interface Product {
  id: number;
  name: string;
  slug: string;
  price: string | number;
  compare_at_price?: string | number | null;
  purchase_price?: string | number;
  stock_quantity?: number | null;
  status: 'published' | 'draft' | string;
  is_featured?: boolean;
  description?: string;
  content?: string;
  thumbnail?: Media;
  gallery?: Media[];
  categories?: Category[];
  options?: ProductOption[];
  bundle_settings?: BundleSettings;
  image_text_sections?: ImageTextSection[];
  video_url?: string | null;
  approved_reviews_count?: number;
  variations?: ProductVariation[];
}

export interface Review {
  id: number;
  product_id: number;
  reviewer_name: string;
  rating: number;
  comment: string;
  status: 'approved' | 'pending' | 'spam' | string;
  created_at: string;
}

export interface DeliveryMethod {
  id: number;
  name: string;
  cost: string | number;
  is_active: boolean;
}

export interface CartItem {
  product: Product;
  quantity: number;
  variation?: ProductVariation;
  selectedTier?: BundleTier;
}

export interface OrderItem {
  id: number;
  product_id: number;
  product_name: string;
  variation_name?: string | null;
  price: string | number;
  quantity: number;
  product?: Product;
}

export interface OrderLog {
  id: number;
  status: string;
  note?: string;
  created_at: string;
}

export interface Order {
  id: number;
  name: string;
  phone: string;
  shipping_address: string;
  total_amount: string | number;
  discount_amount?: string | number | null;
  delivery_cost?: string | number | null;
  coupon_code?: string | null;
  status: 'pending' | 'processing' | 'shipped' | 'delivered' | 'completed' | 'cancelled';
  created_at: string;
  items?: OrderItem[];
  delivery_method?: DeliveryMethod;
  logs?: OrderLog[];
}

export interface CreateOrderPayload {
  name: string;
  phone: string;
  address: string;
  district: string;
  delivery_method_id: number;
  coupon_code?: string | null;
  product_id?: number;
  variation_id?: number | null;
  quantity?: number;
  bundle_info?: Record<string, any>;
  items?: {
    product_id: number;
    quantity: number;
    variation_id?: number | null;
  }[];
  order_bump_product_id?: number;
  visitor_id?: string;
  session_id?: string;
}

export interface CouponApplyResult {
  success: boolean;
  message: string;
  coupon_code?: string;
  type?: 'fixed' | 'percentage';
  value?: number;
  discount_amount?: number;
  final_total?: number;
}
