import { Config } from '@/constants/config';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Platform } from 'react-native';

const STORAGE_KEY_TOKEN = '@jhurio_affiliate_token';
const STORAGE_KEY_DATA = '@jhurio_affiliate_data';

// If in Android emulator, also support local dev fallback 10.0.2.2:8000
const LOCAL_API_URL = Platform.OS === 'android' ? 'http://10.0.2.2:8000/api' : 'http://localhost:8000/api';

async function requestWithFallback(endpoint: string, options: RequestInit = {}): Promise<any> {
  // First try Config.API_URL
  const urlsToTry = [
    `${Config.API_URL}${endpoint}`,
    `${LOCAL_API_URL}${endpoint}`,
  ];

  let lastError: any = null;

  for (const url of urlsToTry) {
    try {
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 6000);

      const res = await fetch(url, {
        ...options,
        signal: controller.signal,
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          ...(options.headers || {}),
        },
      });

      clearTimeout(timeoutId);

      const json = await res.json().catch(() => null);

      if (res.ok || res.status === 401 || res.status === 403 || res.status === 422) {
        return { ok: res.ok, status: res.status, data: json };
      }
    } catch (err) {
      lastError = err;
    }
  }

  throw lastError || new Error('Network error. Unable to connect to affiliate server.');
}

export const AffiliateService = {
  async register(params: {
    name: string;
    phone: string;
    email?: string;
    password: string;
    payout_method: string;
    payout_account: string;
    notes?: string;
  }) {
    const res = await requestWithFallback('/affiliate/register', {
      method: 'POST',
      body: JSON.stringify(params),
    });
    return res;
  },

  async login(phone: string, password: string) {
    const res = await requestWithFallback('/affiliate/login', {
      method: 'POST',
      body: JSON.stringify({ phone, password }),
    });

    if (res.ok && res.data?.token) {
      await AsyncStorage.setItem(STORAGE_KEY_TOKEN, res.data.token);
      await AsyncStorage.setItem(STORAGE_KEY_DATA, JSON.stringify(res.data.affiliate));
    }

    return res;
  },

  async getStoredToken(): Promise<string | null> {
    try {
      return await AsyncStorage.getItem(STORAGE_KEY_TOKEN);
    } catch {
      return null;
    }
  },

  async getStoredAffiliate(): Promise<any | null> {
    try {
      const str = await AsyncStorage.getItem(STORAGE_KEY_DATA);
      return str ? JSON.parse(str) : null;
    } catch {
      return null;
    }
  },

  async logout() {
    try {
      await AsyncStorage.multiRemove([STORAGE_KEY_TOKEN, STORAGE_KEY_DATA]);
    } catch {
    }
  },

  async getDashboard() {
    const token = await this.getStoredToken();
    if (!token) return { ok: false, status: 401, data: { message: 'Not logged in' } };

    const res = await requestWithFallback('/affiliate/dashboard', {
      method: 'GET',
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    if (res.ok && res.data?.affiliate) {
      await AsyncStorage.setItem(STORAGE_KEY_DATA, JSON.stringify(res.data.affiliate));
    }

    return res;
  },

  async requestPayout(amount: number, payout_method?: string, account_details?: string) {
    const token = await this.getStoredToken();
    if (!token) return { ok: false, status: 401, data: { message: 'Not logged in' } };

    return await requestWithFallback('/affiliate/payout-request', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({
        amount,
        payout_method,
        account_details,
      }),
    });
  },

  async getLinks() {
    const token = await this.getStoredToken();
    if (!token) return { ok: false, status: 401, data: { message: 'Not logged in' } };

    return await requestWithFallback('/affiliate/links', {
      method: 'GET',
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });
  },
};
