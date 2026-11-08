import AsyncStorage from '@react-native-async-storage/async-storage';
import React, { createContext, useContext, useEffect, useState } from 'react';

import { BundleTier, CartItem, Product, ProductVariation } from '@/types';

interface CartContextType {
  items: CartItem[];
  addToCart: (product: Product, quantity?: number, variation?: ProductVariation, tier?: BundleTier) => void;
  removeFromCart: (productId: number, variationId?: number) => void;
  updateQuantity: (productId: number, quantity: number, variationId?: number) => void;
  clearCart: () => void;
  itemCount: number;
  subtotal: number;
  customerPhone: string;
  setCustomerPhone: (phone: string) => void;
  customerName: string;
  setCustomerName: (name: string) => void;
  customerAddress: string;
  setCustomerAddress: (address: string) => void;
  customerDistrict: string;
  setCustomerDistrict: (district: string) => void;
}

const CartContext = createContext<CartContextType | undefined>(undefined);

const STORAGE_KEYS = {
  CART: '@jhurio_cart_items',
  CUSTOMER_PHONE: '@jhurio_customer_phone',
  CUSTOMER_NAME: '@jhurio_customer_name',
  CUSTOMER_ADDRESS: '@jhurio_customer_address',
  CUSTOMER_DISTRICT: '@jhurio_customer_district',
};

export const CartProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [items, setItems] = useState<CartItem[]>([]);
  const [customerPhone, setCustomerPhoneState] = useState('');
  const [customerName, setCustomerNameState] = useState('');
  const [customerAddress, setCustomerAddressState] = useState('');
  const [customerDistrict, setCustomerDistrictState] = useState('ঢাকা');

  // Load persisted state on mount
  useEffect(() => {
    (async () => {
      try {
        const [savedCart, savedPhone, savedName, savedAddress, savedDistrict] = await Promise.all([
          AsyncStorage.getItem(STORAGE_KEYS.CART),
          AsyncStorage.getItem(STORAGE_KEYS.CUSTOMER_PHONE),
          AsyncStorage.getItem(STORAGE_KEYS.CUSTOMER_NAME),
          AsyncStorage.getItem(STORAGE_KEYS.CUSTOMER_ADDRESS),
          AsyncStorage.getItem(STORAGE_KEYS.CUSTOMER_DISTRICT),
        ]);

        if (savedCart) {
          try {
            const parsed = JSON.parse(savedCart);
            if (Array.isArray(parsed)) {
              const sanitized: CartItem[] = parsed
                .filter((it: any) => it && (it.product || it.id))
                .map((it: any) => {
                  if (it.product && it.product.id) return it;
                  return {
                    product: {
                      id: it.id || 0,
                      name: it.name || 'Product',
                      price: it.price || 0,
                      slug: it.slug || '',
                      status: 'published',
                      thumbnail: it.thumbnail || (it.image ? { id: 0, url: it.image } : undefined),
                    },
                    quantity: it.quantity || 1,
                    variation: it.variation,
                    selectedTier: it.selectedTier,
                  };
                });
              setItems(sanitized);
            }
          } catch {}
        }
        if (savedPhone) setCustomerPhoneState(savedPhone);
        if (savedName) setCustomerNameState(savedName);
        if (savedAddress) setCustomerAddressState(savedAddress);
        if (savedDistrict) setCustomerDistrictState(savedDistrict);
      } catch (e) {
        console.warn('Failed to restore cart state', e);
      }
    })();
  }, []);

  const saveCart = async (newItems: CartItem[]) => {
    setItems(newItems);
    try {
      await AsyncStorage.setItem(STORAGE_KEYS.CART, JSON.stringify(newItems));
    } catch (e) {
      console.warn('Failed to save cart', e);
    }
  };

  const setCustomerPhone = (phone: string) => {
    setCustomerPhoneState(phone);
    AsyncStorage.setItem(STORAGE_KEYS.CUSTOMER_PHONE, phone).catch(() => {});
  };

  const setCustomerName = (name: string) => {
    setCustomerNameState(name);
    AsyncStorage.setItem(STORAGE_KEYS.CUSTOMER_NAME, name).catch(() => {});
  };

  const setCustomerAddress = (address: string) => {
    setCustomerAddressState(address);
    AsyncStorage.setItem(STORAGE_KEYS.CUSTOMER_ADDRESS, address).catch(() => {});
  };

  const setCustomerDistrict = (district: string) => {
    setCustomerDistrictState(district);
    AsyncStorage.setItem(STORAGE_KEYS.CUSTOMER_DISTRICT, district).catch(() => {});
  };

  const addToCart = (
    product: Product,
    quantity: number = 1,
    variation?: ProductVariation,
    tier?: BundleTier
  ) => {
    const existingIndex = items.findIndex(
      (item) => item.product.id === product.id && item.variation?.id === variation?.id
    );

    if (existingIndex > -1) {
      const updated = [...items];
      updated[existingIndex].quantity += quantity;
      if (tier) updated[existingIndex].selectedTier = tier;
      saveCart(updated);
    } else {
      saveCart([
        ...items,
        {
          product,
          quantity,
          variation,
          selectedTier: tier,
        },
      ]);
    }
  };

  const removeFromCart = (productId: number, variationId?: number) => {
    const updated = items.filter(
      (item) => !(item.product.id === productId && item.variation?.id === variationId)
    );
    saveCart(updated);
  };

  const updateQuantity = (productId: number, quantity: number, variationId?: number) => {
    if (quantity <= 0) {
      removeFromCart(productId, variationId);
      return;
    }
    const updated = items.map((item) => {
      if (item.product.id === productId && item.variation?.id === variationId) {
        return { ...item, quantity };
      }
      return item;
    });
    saveCart(updated);
  };

  const clearCart = () => {
    saveCart([]);
  };

  const itemCount = items.reduce((sum, item) => sum + (item?.quantity || 0), 0);

  const subtotal = items.reduce((sum, item) => {
    if (!item?.product) return sum;
    let unitPrice = Number(item.variation?.price ?? item.product?.price ?? 0);

    // Apply bundle tier discount if tier matched
    if (item.selectedTier) {
      const tier = item.selectedTier;
      if (tier.discount_type === 'percentage') {
        unitPrice = unitPrice * (1 - tier.discount_value / 100);
      } else if (tier.discount_type === 'fixed') {
        const totalDiscount = tier.discount_value;
        const perItemDiscount = totalDiscount / Math.max(1, tier.quantity);
        unitPrice = Math.max(0, unitPrice - perItemDiscount);
      }
    }

    return sum + unitPrice * (item.quantity || 1);
  }, 0);

  return (
    <CartContext.Provider
      value={{
        items,
        addToCart,
        removeFromCart,
        updateQuantity,
        clearCart,
        itemCount,
        subtotal,
        customerPhone,
        setCustomerPhone,
        customerName,
        setCustomerName,
        customerAddress,
        setCustomerAddress,
        customerDistrict,
        setCustomerDistrict,
      }}>
      {children}
    </CartContext.Provider>
  );
};

export const useCart = () => {
  const context = useContext(CartContext);
  if (!context) {
    throw new Error('useCart must be used within a CartProvider');
  }
  return context;
};
