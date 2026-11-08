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
import { BottomTabInset, Colors, Radius, Shadow, Spacing } from '@/constants/theme';
import { useCart } from '@/context/CartContext';
import { ApiService } from '@/services/api';
import { Order } from '@/types';

const STATUS_CONFIG: Record<string, { label: string; icon: string; bg: string; text: string; step: number }> = {
  pending:    { label: 'Pending',    icon: 'time-outline',          bg: '#fef3c7', text: '#b45309', step: 1 },
  processing: { label: 'Processing', icon: 'sync-outline',          bg: '#e0f2fe', text: '#0369a1', step: 2 },
  shipped:    { label: 'Shipped',    icon: 'car-outline',           bg: '#ede9fe', text: '#6d28d9', step: 3 },
  delivered:  { label: 'Delivered',  icon: 'checkmark-circle-outline', bg: '#dcfce7', text: '#15803d', step: 4 },
  completed:  { label: 'Completed',  icon: 'checkmark-circle',      bg: '#dcfce7', text: '#15803d', step: 4 },
  cancelled:  { label: 'Cancelled',  icon: 'close-circle-outline',  bg: '#fee2e2', text: '#b91c1c', step: 0 },
};

const ORDER_STEPS = ['Placed', 'Processing', 'Shipped', 'Delivered'];

export default function OrdersScreen() {
  const scheme = useColorScheme();
  const colors = Colors[scheme === 'dark' ? 'dark' : 'light'];
  const { customerPhone, setCustomerPhone } = useCart();

  const [phoneInput, setPhoneInput] = useState(customerPhone || '');
  const [orders, setOrders] = useState<Order[]>([]);
  const [loading, setLoading] = useState(false);
  const [hasSearched, setHasSearched] = useState(false);

  const fetchOrders = async (phone: string) => {
    const clean = phone.trim();
    if (!clean || clean.length < 8) return;
    setLoading(true);
    setHasSearched(true);
    try {
      const res = await ApiService.getCustomerOrders(clean);
      setOrders(res.orders || []);
      setCustomerPhone(clean);
    } catch {
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

  const getStatusConfig = (status: string) =>
    STATUS_CONFIG[status] || { label: status, icon: 'ellipsis-horizontal-outline', bg: '#f3f4f6', text: '#4b5563', step: 0 };

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['top']}>
      {/* Header */}
      <View style={[styles.header, { backgroundColor: colors.surface, borderBottomColor: colors.border }]}>
        <Text style={[styles.headerTitle, { color: colors.text }]}>📦 My Orders</Text>
        {orders.length > 0 && (
          <View style={[styles.orderCountBadge, { backgroundColor: colors.primaryLight }]}>
            <Text style={[styles.orderCountText, { color: colors.primary }]}>{orders.length}</Text>
          </View>
        )}
      </View>

      {/* Phone Lookup */}
      <View style={[styles.lookupSection, { backgroundColor: colors.surface, borderBottomColor: colors.border }]}>
        <View style={styles.lookupRow}>
          <View style={[styles.phoneInput, { backgroundColor: colors.backgroundElement, borderColor: colors.border }]}>
            <Ionicons name="call-outline" size={16} color={colors.textMuted} />
            <TextInput
              value={phoneInput}
              onChangeText={setPhoneInput}
              onSubmitEditing={() => fetchOrders(phoneInput)}
              placeholder="Enter phone number to track"
              placeholderTextColor={colors.textMuted}
              keyboardType="phone-pad"
              returnKeyType="search"
              style={[styles.phoneInputText, { color: colors.text }]}
            />
            {phoneInput.length > 0 && (
              <Pressable onPress={() => { setPhoneInput(''); setOrders([]); setHasSearched(false); }} hitSlop={8}>
                <Ionicons name="close-circle" size={16} color={colors.textMuted} />
              </Pressable>
            )}
          </View>
          <Pressable
            onPress={() => fetchOrders(phoneInput)}
            disabled={loading || !phoneInput.trim()}
            style={[styles.trackBtn, { backgroundColor: colors.primary }]}>
            {loading ? (
              <ActivityIndicator size="small" color="#fff" />
            ) : (
              <>
                <Ionicons name="search-outline" size={16} color="#fff" />
                <Text style={styles.trackBtnText}>Track</Text>
              </>
            )}
          </Pressable>
        </View>
      </View>

      {/* Content */}
      {!hasSearched && !loading ? (
        <View style={styles.introContainer}>
          <View style={[styles.introIllustration, { backgroundColor: colors.primaryLight }]}>
            <Text style={styles.introEmoji}>📦</Text>
          </View>
          <Text style={[styles.introTitle, { color: colors.text }]}>Track Your Orders</Text>
          <Text style={[styles.introSub, { color: colors.textSecondary }]}>
            Enter the phone number you used when placing your order to view your order history and delivery status.
          </Text>
        </View>
      ) : loading ? (
        <View style={styles.centerContainer}>
          <ActivityIndicator size="large" color={colors.primary} />
          <Text style={[styles.loadingText, { color: colors.textSecondary }]}>Searching orders…</Text>
        </View>
      ) : orders.length === 0 ? (
        <View style={styles.centerContainer}>
          <View style={[styles.emptyIcon, { backgroundColor: colors.backgroundElement }]}>
            <Ionicons name="receipt-outline" size={48} color={colors.textMuted} />
          </View>
          <Text style={[styles.emptyTitle, { color: colors.text }]}>No orders found</Text>
          <Text style={[styles.emptySub, { color: colors.textSecondary }]}>
            No orders found for {phoneInput}. Double-check the number.
          </Text>
        </View>
      ) : (
        <FlatList
          data={orders}
          keyExtractor={(item) => item.id.toString()}
          showsVerticalScrollIndicator={false}
          contentContainerStyle={{ padding: Spacing.three, gap: Spacing.three, paddingBottom: BottomTabInset + Spacing.five }}
          renderItem={({ item }) => {
            const statusConf = getStatusConfig(item.status);
            const dateStr = new Date(item.created_at).toLocaleDateString('en-GB', {
              day: 'numeric', month: 'short', year: 'numeric',
            });
            const isCancelled = item.status === 'cancelled';

            return (
              <View style={[styles.orderCard, { backgroundColor: colors.surface, borderColor: colors.border, ...Shadow.sm }]}>
                {/* Order header */}
                <View style={styles.orderTop}>
                  <View>
                    <Text style={[styles.orderId, { color: colors.text }]}>Order #{item.id}</Text>
                    <Text style={[styles.orderDate, { color: colors.textMuted }]}>{dateStr}</Text>
                  </View>
                  <View style={[styles.statusBadge, { backgroundColor: statusConf.bg }]}>
                    <Ionicons name={statusConf.icon as any} size={12} color={statusConf.text} />
                    <Text style={[styles.statusText, { color: statusConf.text }]}>{statusConf.label}</Text>
                  </View>
                </View>

                {/* Progress Tracker */}
                {!isCancelled && (
                  <View style={styles.progressSection}>
                    <View style={styles.progressTrack}>
                      {ORDER_STEPS.map((step, idx) => {
                        const done = statusConf.step > idx;
                        const active = statusConf.step === idx + 1;
                        return (
                          <React.Fragment key={idx}>
                            <View style={styles.progressStep}>
                              <View style={[
                                styles.progressDot,
                                { backgroundColor: done || active ? colors.primary : colors.backgroundElement, borderColor: done || active ? colors.primary : colors.border },
                              ]}>
                                {done ? (
                                  <Ionicons name="checkmark" size={10} color="#fff" />
                                ) : (
                                  <View style={[styles.innerDot, { backgroundColor: active ? '#fff' : colors.border }]} />
                                )}
                              </View>
                              <Text style={[
                                styles.stepLabel,
                                { color: done || active ? colors.primary : colors.textMuted, fontWeight: active ? '800' : '500' },
                              ]} numberOfLines={1}>
                                {step}
                              </Text>
                            </View>
                            {idx < ORDER_STEPS.length - 1 && (
                              <View style={[styles.progressLine, { backgroundColor: done ? colors.primary : colors.border }]} />
                            )}
                          </React.Fragment>
                        );
                      })}
                    </View>
                  </View>
                )}

                <View style={[styles.divider, { backgroundColor: colors.border }]} />

                {/* Items */}
                {item.items && item.items.length > 0 && (
                  <View style={styles.itemList}>
                    {item.items.map((orderItem) => (
                      <View key={orderItem.id} style={styles.itemRow}>
                        <View style={[styles.itemDot, { backgroundColor: colors.primary }]} />
                        <Text numberOfLines={1} style={[styles.itemName, { color: colors.text }]}>
                          {orderItem.quantity}× {orderItem.product_name}
                        </Text>
                        <Text style={[styles.itemPrice, { color: colors.textSecondary }]}>
                          {Config.CURRENCY}{Number(orderItem.price).toLocaleString()}
                        </Text>
                      </View>
                    ))}
                  </View>
                )}

                <View style={[styles.divider, { backgroundColor: colors.border }]} />

                {/* Footer */}
                <View style={styles.orderFooter}>
                  <View style={{ flex: 1 }}>
                    <Text style={[styles.addressText, { color: colors.textMuted }]} numberOfLines={1}>
                      📍 {item.shipping_address || (item as any).customer_address || 'Standard Delivery'}
                    </Text>
                    <Text style={[styles.paymentText, { color: colors.textMuted }]}>
                      💳 Cash on Delivery
                    </Text>
                  </View>
                  <View style={{ alignItems: 'flex-end' }}>
                    <Text style={[styles.totalLabel, { color: colors.textMuted }]}>Total</Text>
                    <Text style={[styles.totalAmount, { color: colors.primary }]}>
                      {Config.CURRENCY}{Number(item.total_amount ?? (item as any).total ?? 0).toLocaleString()}
                    </Text>
                  </View>
                </View>
              </View>
            );
          }}
        />
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },

  header: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.two,
    paddingHorizontal: Spacing.three,
    paddingVertical: Spacing.two + 2,
    borderBottomWidth: StyleSheet.hairlineWidth,
  },
  headerTitle: { fontSize: 18, fontWeight: '800', flex: 1 },
  orderCountBadge: {
    paddingHorizontal: 10,
    paddingVertical: 3,
    borderRadius: Radius.full,
  },
  orderCountText: { fontSize: 12, fontWeight: '800' },

  lookupSection: {
    paddingHorizontal: Spacing.three,
    paddingVertical: Spacing.two + 2,
    borderBottomWidth: StyleSheet.hairlineWidth,
  },
  lookupRow: { flexDirection: 'row', gap: Spacing.two, alignItems: 'center' },
  phoneInput: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.two,
    paddingHorizontal: Spacing.three,
    height: 44,
    borderRadius: Radius.lg,
    borderWidth: 1,
  },
  phoneInputText: { flex: 1, fontSize: 14, fontWeight: '500', padding: 0 },
  trackBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    paddingHorizontal: Spacing.three,
    height: 44,
    borderRadius: Radius.lg,
  },
  trackBtnText: { color: '#fff', fontWeight: '800', fontSize: 13 },

  // Intro
  introContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: Spacing.five,
    gap: Spacing.three,
  },
  introIllustration: {
    width: 120,
    height: 120,
    borderRadius: 60,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: Spacing.two,
  },
  introEmoji: { fontSize: 56 },
  introTitle: { fontSize: 22, fontWeight: '800' },
  introSub: { fontSize: 14, textAlign: 'center', lineHeight: 20 },

  centerContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: Spacing.two,
    padding: Spacing.five,
  },
  loadingText: { fontSize: 14 },
  emptyIcon: {
    width: 96,
    height: 96,
    borderRadius: 48,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: Spacing.two,
  },
  emptyTitle: { fontSize: 20, fontWeight: '800' },
  emptySub: { fontSize: 13, textAlign: 'center' },

  // Order card
  orderCard: {
    borderRadius: Radius.xl,
    borderWidth: StyleSheet.hairlineWidth,
    overflow: 'hidden',
  },
  orderTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    padding: Spacing.four,
    paddingBottom: 0,
  },
  orderId: { fontSize: 15, fontWeight: '800' },
  orderDate: { fontSize: 12, marginTop: 2 },
  statusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: Radius.full,
  },
  statusText: { fontSize: 11, fontWeight: '800' },

  // Progress
  progressSection: {
    paddingHorizontal: Spacing.four,
    paddingTop: Spacing.three,
  },
  progressTrack: {
    flexDirection: 'row',
    alignItems: 'flex-start',
  },
  progressStep: {
    alignItems: 'center',
    gap: 4,
    width: 56,
  },
  progressDot: {
    width: 24,
    height: 24,
    borderRadius: 12,
    borderWidth: 2,
    alignItems: 'center',
    justifyContent: 'center',
  },
  innerDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
  },
  stepLabel: { fontSize: 9, textAlign: 'center' },
  progressLine: {
    flex: 1,
    height: 2,
    marginTop: 11,
    borderRadius: 1,
  },

  divider: { height: 1, marginHorizontal: Spacing.four, marginVertical: Spacing.two },

  // Items
  itemList: { paddingHorizontal: Spacing.four, gap: 6 },
  itemRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  itemDot: { width: 5, height: 5, borderRadius: 3 },
  itemName: { flex: 1, fontSize: 13, fontWeight: '600' },
  itemPrice: { fontSize: 12 },

  // Footer
  orderFooter: {
    flexDirection: 'row',
    padding: Spacing.four,
    paddingTop: 0,
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    gap: Spacing.two,
  },
  addressText: { fontSize: 12, marginBottom: 3 },
  paymentText: { fontSize: 12 },
  totalLabel: { fontSize: 11 },
  totalAmount: { fontSize: 16, fontWeight: '900' },
});
