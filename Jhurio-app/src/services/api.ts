import { Config } from '@/constants/config';
import {
  Banner,
  Category,
  CouponApplyResult,
  CreateOrderPayload,
  DeliveryMethod,
  Order,
  Product,
  Review,
} from '@/types';

class ApiError extends Error {
  constructor(public status: number, message: string, public data?: any) {
    super(message);
    this.name = 'ApiError';
  }
}

async function request<T>(endpoint: string, options: RequestInit = {}): Promise<T> {
  const url = `${Config.API_URL}${endpoint.startsWith('/') ? endpoint : `/${endpoint}`}`;

  const headers: Record<string, string> = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${Config.API_TOKEN}`,
    ...(options.headers as Record<string, string> || {}),
  };

  try {
    const response = await fetch(url, {
      ...options,
      headers,
    });

    const data = await response.json().catch(() => null);

    if (!response.ok) {
      const errorMsg = data?.message || data?.error || `HTTP error ${response.status}`;
      throw new ApiError(response.status, errorMsg, data);
    }

    return data as T;
  } catch (error) {
    if (error instanceof ApiError) {
      throw error;
    }
    throw new ApiError(0, (error as Error).message || 'Network request failed');
  }
}

export const ApiService = {
  // ─── System & Settings ───────────────────────────────────────
  async ping(): Promise<{ status: string; server_time: string; api_version: string }> {
    return request('/ping');
  },

  async getSettings(keys?: string): Promise<{ settings: Record<string, string> }> {
    const query = keys ? `?keys=${encodeURIComponent(keys)}` : '';
    return request(`/settings${query}`);
  },

  // ─── Banners ─────────────────────────────────────────────────
  async getBanners(type: string = 'slider', isActive: boolean = true): Promise<Banner[]> {
    const res = await request<{ total: number; banners: Banner[] }>(
      `/banners?type=${type}&is_active=${isActive ? 1 : 0}`
    );
    return res.banners || [];
  },

  // ─── Categories ──────────────────────────────────────────────
  async getCategories(limit?: number): Promise<Category[]> {
    const query = limit ? `?limit=${limit}` : '';
    const res = await request<{ total: number; categories: Category[] }>(`/categories${query}`);
    return res.categories || [];
  },

  // ─── Products ────────────────────────────────────────────────
  async getProducts(params?: {
    status?: string;
    search?: string;
    category?: string;
    is_featured?: boolean;
    limit?: number;
    page?: number;
  }): Promise<{ total: number; products: Product[]; current_page?: number; last_page?: number }> {
    const searchParams = new URLSearchParams();
    searchParams.append('status', params?.status || 'published');

    if (params?.search) searchParams.append('search', params.search);
    if (params?.category) searchParams.append('category', params.category);
    if (params?.is_featured !== undefined) searchParams.append('is_featured', params.is_featured ? '1' : '0');
    if (params?.limit) searchParams.append('limit', params.limit.toString());
    if (params?.page) searchParams.append('page', params.page.toString());

    return request(`/products?${searchParams.toString()}`);
  },

  async getProductDetail(idOrSlug: string | number): Promise<Product> {
    const res = await request<{ product: Product }>(`/products/${idOrSlug}`);
    return res.product;
  },

  // ─── Reviews ─────────────────────────────────────────────────
  async getReviews(productId: number, limit: number = 50): Promise<Review[]> {
    const res = await request<{ total: number; reviews: Review[] }>(
      `/reviews?product_id=${productId}&status=approved&limit=${limit}`
    );
    return res.reviews || [];
  },

  async submitReview(data: {
    product_id: number;
    reviewer_name: string;
    rating: number;
    comment: string;
    reviewer_phone?: string;
  }): Promise<{ message: string; review: Review }> {
    return request('/reviews', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  // ─── Delivery Methods ────────────────────────────────────────
  async getDeliveryMethods(): Promise<DeliveryMethod[]> {
    const res = await request<{ total: number; delivery_methods: DeliveryMethod[] }>(
      '/delivery-methods'
    );
    return res.delivery_methods || [];
  },

  // ─── Coupons ─────────────────────────────────────────────────
  async applyCoupon(code: string, orderTotal: number): Promise<CouponApplyResult> {
    return request('/coupons/apply', {
      method: 'POST',
      body: JSON.stringify({ code, order_total: orderTotal }),
    });
  },

  // ─── Orders ──────────────────────────────────────────────────
  async createOrder(payload: CreateOrderPayload): Promise<{ success: boolean; message: string; order: Order }> {
    return request('/orders', {
      method: 'POST',
      body: JSON.stringify(payload),
    });
  },

  async getCustomerOrders(phone: string, limit: number = 20, page: number = 1): Promise<{
    total: number;
    orders: Order[];
    current_page: number;
    last_page: number;
  }> {
    return request(`/orders?search=${encodeURIComponent(phone)}&limit=${limit}&page=${page}`);
  },

  async getOrderDetail(id: number): Promise<Order> {
    const res = await request<{ order: Order }>(`/orders/${id}`);
    return res.order;
  },

  // ─── Telemetry ───────────────────────────────────────────────
  async trackEvent(eventType: string, data: Record<string, any> = {}): Promise<void> {
    try {
      await request('/track', {
        method: 'POST',
        body: JSON.stringify({
          payload: {
            event_type: eventType,
            url: `app://${eventType}`,
            ...data,
          },
        }),
      });
    } catch {
      // Ignore background telemetry errors
    }
  },
};
