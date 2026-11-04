import { Ionicons } from '@expo/vector-icons';
import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Pressable,
  StyleSheet,
  Text,
  TextInput,
  useColorScheme,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { Config } from '@/constants/config';
import { BottomTabInset, Colors, Spacing } from '@/constants/theme';
import { useCart } from '@/context/CartContext';
import { ApiService } from '@/services/api';
import { Order } from '@/types';

export default function OrdersScreen() {
  const scheme = useColorScheme();
  const colors = Colors[scheme === 'dark' ? 'dark' : 'light'];
  const { customerPhone, setCustomerPhone } = useCart();

  const [phoneInput, setPhoneInput] = useState(customerPhone || '');
  const [orders, setOrders] = useState<Order[]>([]);
  const [loading, setLoading] = useState(false);
  const [hasSearched, setHasSearched] = useState(false);

  const fetchOrders = async (phone: string) => {
    const cleanPhone = phone.trim();
    if (!cleanPhone || cleanPhone.length < 8) return;

    setLoading(true);
    setHasSearched(true);

    try {
      const res = await ApiService.getCustomerOrders(cleanPhone);
      setOrders(res.orders || []);
      setCustomerPhone(cleanPhone);
    } catch (e) {
      console.warn('Failed to load orders', e);
      setOrders([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (customerPhone) {
      setPhoneInput(customerPhone);
      fetchOrders(customerPhone);
    }
  }, []);

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'pending':
        return { bg: '#fef3c7', text: '#b45309' };
      case 'processing':
        return { bg: '#e0f2fe', text: '#0369a1' };
      case 'shipped':
        return { bg: '#ede9fe', text: '#6d28d9' };
      case 'delivered':
      case 'completed':
        return { bg: '#dcfce7', text: '#15803d' };
      case 'cancelled':
        return { bg: '#fee2e2', text: '#b91c1c' };
      default:
        return { bg: '#f3f4f6', text: '#4b5563' };
    }
  };

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['top']}>
      {/* Header */}
      <View style={[styles.header, { backgroundColor: colors.surface, borderBottomColor: colors.border }]}>
        <Text style={[styles.headerTitle, { color: colors.text }]}>Track My Orders</Text>
      </View>

      <View style={styles.content}>
        {/* Phone Lookup Box */}
        <View style={[styles.lookupCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
          <Text style={[styles.lookupLabel, { color: colors.textSecondary }]}>
            Enter Mobile Number to Track Orders:
          </Text>
          <View style={styles.inputRow}>
            <TextInput
              placeholder="017XXXXXXXX"
              placeholderTextColor={colors.textMuted}
              keyboardType="phone-pad"
              value={phoneInput}
              onChangeText={setPhoneInput}
              onSubmitEditing={() => fetchOrders(phoneInput)}
              style={[
                styles.input,
                { backgroundColor: colors.backgroundElement, borderColor: colors.border, color: colors.text },
              ]}
            />
            <Pressable
              onPress={() => fetchOrders(phoneInput)}
              disabled={loading || !phoneInput.trim()}
              style={[styles.trackBtn, { backgroundColor: colors.primary }]}>
              {loading ? (
                <ActivityIndicator size="small" color="#ffffff" />
              ) : (
                <Text style={styles.trackBtnText}>Track</Text>
              )}
            </Pressable>
          </View>
        </View>

        {/* Orders List */}
        {loading ? (
          <View style={styles.centerContainer}>
            <ActivityIndicator size="large" color={colors.primary} />
            <Text style={[styles.loadingText, { color: colors.textSecondary }]}>
              Searching your orders...
            </Text>
          </View>
        ) : hasSearched && orders.length === 0 ? (
          <View style={styles.centerContainer}>
            <Ionicons name="receipt-outline" size={48} color={colors.textMuted} />
            <Text style={[styles.emptyTitle, { color: colors.text }]}>No orders found</Text>
            <Text style={[styles.emptySubtitle, { color: colors.textSecondary }]}>
              No previous orders found for {phoneInput}. Please verify the number.
            </Text>
          </View>
        ) : (
          <FlatList
            data={orders}
            keyExtractor={(item) => item.id.toString()}
            showsVerticalScrollIndicator={false}
            contentContainerStyle={{ paddingBottom: BottomTabInset + Spacing.five }}
            renderItem={({ item }) => {
              const statusStyle = getStatusColor(item.status);
              const dateStr = new Date(item.created_at).toLocaleDateString('en-GB', {
                day: 'numeric',
                month: 'short',
                year: 'numeric',
              });

              return (
                <View style={[styles.orderCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
                  {/* Top: ID & Status */}
                  <View style={styles.orderTop}>
                    <View>
                      <Text style={[styles.orderId, { color: colors.text }]}>Order #{item.id}</Text>
                      <Text style={[styles.orderDate, { color: colors.textSecondary }]}>{dateStr}</Text>
                    </View>

                    <View style={[styles.statusBadge, { backgroundColor: statusStyle.bg }]}>
                      <Text style={[styles.statusText, { color: statusStyle.text }]}>
                        {item.status.toUpperCase()}
                      </Text>
                    </View>
                  </View>

                  <View style={[styles.divider, { backgroundColor: colors.border }]} />

                  {/* Items List */}
                  {item.items && item.items.length > 0 && (
                    <View style={styles.itemList}>
                      {item.items.map((orderItem) => (
                        <View key={orderItem.id} style={styles.itemRow}>
                          <Text numberOfLines={1} style={[styles.itemTitle, { color: colors.text }]}>
                            {orderItem.quantity}x {orderItem.product_name}
                          </Text>
                          <Text style={[styles.itemPrice, { color: colors.textSecondary }]}>
                            {Config.CURRENCY}{Number(orderItem.price).toLocaleString()}
                          </Text>
                        </View>
                      ))}
                    </View>
                  )}

                  <View style={[styles.divider, { backgroundColor: colors.border }]} />

                  {/* Address & Total */}
                  <View style={styles.orderBottom}>
                    <View style={{ flex: 1, marginRight: Spacing.two }}>
                      <Text numberOfLines={1} style={[styles.addressText, { color: colors.textSecondary }]}>
                        📍 {item.shipping_address}
                      </Text>
                    </View>

                    <Text style={[styles.totalAmount, { color: colors.primary }]}>
                      {Config.CURRENCY}{Number(item.total_amount).toLocaleString()}
                    </Text>
                  </View>
                </View>
              );
            }}
          />
        )}
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  header: {
    paddingHorizontal: Spacing.four,
    paddingVertical: Spacing.three,
    borderBottomWidth: StyleSheet.hairlineWidth,
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '800',
  },
  content: {
    flex: 1,
    padding: Spacing.four,
  },
  lookupCard: {
    padding: Spacing.three,
    borderRadius: 14,
    borderWidth: 1,
    marginBottom: Spacing.three,
  },
  lookupLabel: {
    fontSize: 13,
    fontWeight: '600',
    marginBottom: Spacing.two,
  },
  inputRow: {
    flexDirection: 'row',
    gap: Spacing.two,
  },
  input: {
    flex: 1,
    height: 44,
    borderRadius: 10,
    borderWidth: 1,
    paddingHorizontal: Spacing.three,
    fontSize: 14,
  },
  trackBtn: {
    paddingHorizontal: Spacing.four,
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
  },
  trackBtnText: {
    color: '#ffffff',
    fontSize: 13,
    fontWeight: '700',
  },
  centerContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: Spacing.five,
    gap: Spacing.two,
  },
  loadingText: {
    fontSize: 14,
  },
  emptyTitle: {
    fontSize: 18,
    fontWeight: '800',
  },
  emptySubtitle: {
    fontSize: 13,
    textAlign: 'center',
  },
  orderCard: {
    padding: Spacing.four,
    borderRadius: 16,
    borderWidth: 1,
    marginBottom: Spacing.three,
  },
  orderTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
  },
  orderId: {
    fontSize: 16,
    fontWeight: '800',
  },
  orderDate: {
    fontSize: 12,
    marginTop: 2,
  },
  statusBadge: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
  },
  statusText: {
    fontSize: 11,
    fontWeight: '800',
    letterSpacing: 0.5,
  },
  divider: {
    height: 1,
    marginVertical: Spacing.two,
  },
  itemList: {
    gap: 4,
  },
  itemRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  itemTitle: {
    flex: 1,
    fontSize: 13,
    fontWeight: '600',
    marginRight: 8,
  },
  itemPrice: {
    fontSize: 13,
  },
  orderBottom: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  addressText: {
    fontSize: 12,
  },
  totalAmount: {
    fontSize: 16,
    fontWeight: '900',
  },
});
