import { Ionicons } from '@expo/vector-icons';
import { Image } from 'expo-image';
import { useRouter } from 'expo-router';
import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  KeyboardAvoidingView,
  Platform,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  useColorScheme,
  View,
} from 'react-native';
import { SafeAreaView, useSafeAreaInsets } from 'react-native-safe-area-context';

import { Config } from '@/constants/config';
import { Colors, Radius, Shadow, Spacing } from '@/constants/theme';
import { useCart } from '@/context/CartContext';
import { ApiService } from '@/services/api';
import { DeliveryMethod, Order } from '@/types';

export default function CartScreen() {
  const router = useRouter();
  const scheme = useColorScheme();
  const colors = Colors[scheme === 'dark' ? 'dark' : 'light'];
  const insets = useSafeAreaInsets();

  const {
    items,
    removeFromCart,
    updateQuantity,
    clearCart,
    subtotal,
    customerName, setCustomerName,
    customerPhone, setCustomerPhone,
    customerAddress, setCustomerAddress,
    customerDistrict, setCustomerDistrict,
  } = useCart();

  const [deliveryMethods, setDeliveryMethods] = useState<DeliveryMethod[]>([]);
  const [selectedMethodId, setSelectedMethodId] = useState<number>(1);
  const [couponCode, setCouponCode] = useState('');
  const [appliedCoupon, setAppliedCoupon] = useState<{ code: string; discount: number } | null>(null);
  const [couponLoading, setCouponLoading] = useState(false);
  const [submittingOrder, setSubmittingOrder] = useState(false);
  const [createdOrder, setCreatedOrder] = useState<Order | null>(null);
  const [step, setStep] = useState<'cart' | 'checkout'>('cart');

  useEffect(() => {
    ApiService.getDeliveryMethods()
      .then((methods) => {
        setDeliveryMethods(methods);
        if (methods.length > 0) setSelectedMethodId(methods[0].id);
      })
      .catch(() => {
        setDeliveryMethods([
          { id: 1, name: 'Inside Dhaka', cost: '70.00', is_active: true },
          { id: 2, name: 'Outside Dhaka', cost: '120.00', is_active: true },
        ]);
      });
  }, []);

  const selectedDelivery = deliveryMethods.find((m) => m.id === selectedMethodId);
  const shippingCost = selectedDelivery ? Number(selectedDelivery.cost) : 70;
  const discountAmount = appliedCoupon ? appliedCoupon.discount : 0;
  const grandTotal = Math.max(0, subtotal + shippingCost - discountAmount);

  const handleApplyCoupon = async () => {
    if (!couponCode.trim()) return;
    setCouponLoading(true);
    try {
      const result = await ApiService.applyCoupon(couponCode.trim(), subtotal);
      setAppliedCoupon({ code: couponCode.trim(), discount: result.discount_amount });
      Alert.alert('🎉 Coupon Applied!', `You saved ${Config.CURRENCY}${result.discount_amount.toLocaleString()}`);
    } catch {
      Alert.alert('Invalid Coupon', 'This coupon code is not valid or has expired.');
    } finally {
      setCouponLoading(false);
    }
  };

  const handlePlaceOrder = async () => {
    if (!customerName.trim()) return Alert.alert('Required', 'Please enter your full name.');
    if (!customerPhone.trim() || customerPhone.trim().length < 11)
      return Alert.alert('Required', 'Please enter a valid phone number (11 digits).');
    if (!customerAddress.trim()) return Alert.alert('Required', 'Please enter your delivery address.');

    setSubmittingOrder(true);
    try {
      const payload: any = {
        name: customerName.trim(),
        phone: customerPhone.trim(),
        address: `${customerAddress.trim()}${customerDistrict ? `, ${customerDistrict}` : ''}`,
        district: customerDistrict.trim() || 'Dhaka',
        delivery_method_id: selectedMethodId,
        coupon_code: appliedCoupon?.code || null,
        items: items.map((item) => ({
          product_id: item.product.id,
          quantity: item.quantity,
          variation_id: item.variation?.id ?? null,
        })),
      };

      const res = await ApiService.createOrder(payload);
      setCreatedOrder(res.order);
      clearCart();
    } catch (err: any) {
      Alert.alert('Order Failed', err.message || 'Failed to place your order. Please try again.');
    } finally {
      setSubmittingOrder(false);
    }
  };

  // ── Order Success ──
  if (createdOrder) {
    return (
      <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['top', 'bottom']}>
        <View style={styles.successContainer}>
          <View style={[styles.successIcon, { backgroundColor: '#d1fae5' }]}>
            <Ionicons name="checkmark-circle" size={80} color={colors.success} />
          </View>
          <Text style={[styles.successTitle, { color: colors.text }]}>Order Placed! 🎉</Text>
          <Text style={[styles.successSub, { color: colors.textSecondary }]}>
            Your order #{createdOrder.id} has been received. We'll call you soon to confirm.
          </Text>
          <View style={[styles.orderInfoCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
            <InfoRow icon="call-outline" label="Phone" value={createdOrder.phone} colors={colors} />
            <InfoRow icon="location-outline" label="Address" value={createdOrder.shipping_address} colors={colors} />
            <InfoRow icon="cash-outline" label="Total" value={`${Config.CURRENCY}${Number(createdOrder.total_amount).toLocaleString()}`} colors={colors} />
            <InfoRow icon="car-outline" label="Payment" value="Cash on Delivery" colors={colors} />
          </View>
          <Pressable
            onPress={() => { setCreatedOrder(null); router.push('/'); }}
            style={[styles.successBtn, { backgroundColor: colors.primary }]}>
            <Text style={styles.successBtnText}>Continue Shopping</Text>
          </Pressable>
          <Pressable
            onPress={() => router.push('/orders')}
            style={[styles.trackBtn, { borderColor: colors.primary }]}>
            <Text style={[styles.trackBtnText, { color: colors.primary }]}>Track My Orders</Text>
          </Pressable>
        </View>
      </SafeAreaView>
    );
  }

  // ── Empty Cart ──
  if (items.length === 0) {
    return (
      <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['top', 'bottom']}>
        <View style={[styles.headerBar, { backgroundColor: colors.surface, borderBottomColor: colors.border }]}>
          <Text style={[styles.headerTitle, { color: colors.text }]}>My Cart</Text>
        </View>
        <View style={styles.emptyContainer}>
          <View style={[styles.emptyIcon, { backgroundColor: colors.primaryLight }]}>
            <Ionicons name="cart-outline" size={60} color={colors.primary} />
          </View>
          <Text style={[styles.emptyTitle, { color: colors.text }]}>Your cart is empty</Text>
          <Text style={[styles.emptySub, { color: colors.textSecondary }]}>
            Discover amazing products and add them to your cart
          </Text>
          <Pressable
            onPress={() => router.push('/explore')}
            style={[styles.shopNowBtn, { backgroundColor: colors.primary }]}>
            <Ionicons name="storefront-outline" size={16} color="#fff" />
            <Text style={styles.shopNowText}>Browse Products</Text>
          </Pressable>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['top']}>
      {/* Header */}
      <View style={[styles.headerBar, { backgroundColor: colors.surface, borderBottomColor: colors.border }]}>
        {step === 'checkout' ? (
          <Pressable onPress={() => setStep('cart')} style={styles.backBtn} hitSlop={8}>
            <Ionicons name="chevron-back" size={22} color={colors.text} />
          </Pressable>
        ) : <View style={{ width: 36 }} />}
        <Text style={[styles.headerTitle, { color: colors.text }]}>
          {step === 'cart' ? `Cart (${items.length})` : 'Checkout'}
        </Text>
        {step === 'cart' ? (
          <Pressable onPress={() => { Alert.alert('Clear Cart?', 'Remove all items?', [{ text: 'Cancel' }, { text: 'Clear', onPress: clearCart, style: 'destructive' }]); }} hitSlop={8}>
            <Text style={[styles.clearText, { color: colors.danger }]}>Clear</Text>
          </Pressable>
        ) : <View style={{ width: 36 }} />}
      </View>

      <KeyboardAvoidingView
        style={{ flex: 1 }}
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}>

        {step === 'cart' ? (
          <>
            {/* Cart Items */}
            <ScrollView
              showsVerticalScrollIndicator={false}
              contentContainerStyle={{ paddingBottom: 160, padding: Spacing.three, gap: Spacing.two }}>
              {items.map((item) => {
                const imgUrl = item.product.thumbnail?.url;
                const price = Number(item.variation?.price ?? item.product.price);
                return (
                  <View
                    key={item.cartKey}
                    style={[styles.cartItem, { backgroundColor: colors.surface, borderColor: colors.border, ...Shadow.sm }]}>
                    <View style={[styles.cartImg, { backgroundColor: colors.backgroundElement }]}>
                      {imgUrl ? (
                        <Image source={{ uri: imgUrl }} style={{ width: '100%', height: '100%' }} contentFit="cover" />
                      ) : (
                        <Ionicons name="image-outline" size={30} color={colors.textMuted} />
                      )}
                    </View>
                    <View style={styles.cartInfo}>
                      <Text numberOfLines={2} style={[styles.cartName, { color: colors.text }]}>
                        {item.product.name}
                      </Text>
                      {item.variation && (
                        <Text style={[styles.cartVariant, { color: colors.textMuted }]}>{item.variation.name}</Text>
                      )}
                      {item.selectedTier && (
                        <Text style={[styles.cartVariant, { color: colors.primary }]}>{item.selectedTier.title}</Text>
                      )}
                      <Text style={[styles.cartPrice, { color: colors.primary }]}>
                        {Config.CURRENCY}{(price * item.quantity).toLocaleString()}
                      </Text>
                    </View>
                    <View style={styles.cartActions}>
                      <Pressable
                        onPress={() => removeFromCart(item.product.id, item.variation?.id)}
                        style={[styles.deleteBtn, { backgroundColor: '#fee2e2' }]}>
                        <Ionicons name="trash-outline" size={15} color={colors.danger} />
                      </Pressable>
                      <View style={[styles.qtyControl, { borderColor: colors.border }]}>
                        <Pressable
                          onPress={() => updateQuantity(item.product.id, item.quantity - 1, item.variation?.id)}
                          style={[styles.qtyBtnSmall, { backgroundColor: colors.backgroundElement }]}>
                          <Ionicons name="remove" size={14} color={colors.text} />
                        </Pressable>
                        <Text style={[styles.qtyNumSmall, { color: colors.text }]}>{item.quantity}</Text>
                        <Pressable
                          onPress={() => updateQuantity(item.product.id, item.quantity + 1, item.variation?.id)}
                          style={[styles.qtyBtnSmall, { backgroundColor: colors.primary }]}>
                          <Ionicons name="add" size={14} color="#fff" />
                        </Pressable>
                      </View>
                    </View>
                  </View>
                );
              })}
            </ScrollView>

            {/* Cart Summary + Proceed */}
            <View style={[styles.cartFooter, { backgroundColor: colors.surface, borderTopColor: colors.border, paddingBottom: insets.bottom + Spacing.two }]}>
              <View style={styles.summaryRow}>
                <Text style={[styles.summaryLabel, { color: colors.textSecondary }]}>Subtotal</Text>
                <Text style={[styles.summaryValue, { color: colors.text }]}>{Config.CURRENCY}{subtotal.toLocaleString()}</Text>
              </View>
              <Pressable
                onPress={() => setStep('checkout')}
                style={[styles.checkoutBtn, { backgroundColor: colors.primary }]}>
                <Text style={styles.checkoutBtnText}>Proceed to Checkout</Text>
                <Ionicons name="arrow-forward" size={18} color="#fff" />
              </Pressable>
            </View>
          </>
        ) : (
          <>
            {/* Checkout Form */}
            <ScrollView
              showsVerticalScrollIndicator={false}
              contentContainerStyle={{ paddingBottom: 160, padding: Spacing.three, gap: Spacing.three }}>

              {/* Delivery Info */}
              <View style={[styles.checkoutCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
                <SectionHeader icon="person-outline" title="Delivery Info" colors={colors} />
                <FormInput
                  label="Full Name *"
                  value={customerName}
                  onChange={setCustomerName}
                  placeholder="Enter your full name"
                  icon="person-outline"
                  colors={colors}
                />
                <FormInput
                  label="Phone Number *"
                  value={customerPhone}
                  onChange={setCustomerPhone}
                  placeholder="01XXXXXXXXX"
                  icon="call-outline"
                  keyboardType="phone-pad"
                  colors={colors}
                />
                <FormInput
                  label="Delivery Address *"
                  value={customerAddress}
                  onChange={setCustomerAddress}
                  placeholder="House, Road, Area"
                  icon="location-outline"
                  multiline
                  colors={colors}
                />
                <FormInput
                  label="District"
                  value={customerDistrict}
                  onChange={setCustomerDistrict}
                  placeholder="e.g. Dhaka"
                  icon="map-outline"
                  colors={colors}
                />
              </View>

              {/* Delivery Method */}
              {deliveryMethods.length > 0 && (
                <View style={[styles.checkoutCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
                  <SectionHeader icon="car-outline" title="Shipping Method" colors={colors} />
                  {deliveryMethods.filter((m) => m.is_active).map((method) => (
                    <Pressable
                      key={method.id}
                      onPress={() => setSelectedMethodId(method.id)}
                      style={[
                        styles.methodRow,
                        {
                          backgroundColor: selectedMethodId === method.id ? colors.primaryLight : colors.backgroundElement,
                          borderColor: selectedMethodId === method.id ? colors.primary : 'transparent',
                        },
                      ]}>
                      <View style={[styles.radio, { borderColor: selectedMethodId === method.id ? colors.primary : colors.border }]}>
                        {selectedMethodId === method.id && (
                          <View style={[styles.radioFill, { backgroundColor: colors.primary }]} />
                        )}
                      </View>
                      <View style={{ flex: 1 }}>
                        <Text style={[styles.methodName, { color: colors.text }]}>{method.name}</Text>
                      </View>
                      <Text style={[styles.methodCost, { color: colors.primary }]}>
                        {Config.CURRENCY}{Number(method.cost).toLocaleString()}
                      </Text>
                    </Pressable>
                  ))}
                </View>
              )}

              {/* Coupon */}
              <View style={[styles.checkoutCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
                <SectionHeader icon="pricetag-outline" title="Promo Code" colors={colors} />
                {appliedCoupon ? (
                  <View style={[styles.appliedCoupon, { backgroundColor: '#d1fae5', borderColor: colors.success }]}>
                    <Ionicons name="checkmark-circle" size={18} color={colors.success} />
                    <Text style={[styles.appliedText, { color: colors.success }]}>
                      "{appliedCoupon.code}" — Saved {Config.CURRENCY}{appliedCoupon.discount.toLocaleString()}
                    </Text>
                    <Pressable onPress={() => setAppliedCoupon(null)} hitSlop={8}>
                      <Ionicons name="close-circle" size={18} color={colors.textMuted} />
                    </Pressable>
                  </View>
                ) : (
                  <View style={styles.couponRow}>
                    <View style={[styles.couponInput, { backgroundColor: colors.backgroundElement, borderColor: colors.border }]}>
                      <Ionicons name="pricetag-outline" size={16} color={colors.textMuted} />
                      <TextInput
                        value={couponCode}
                        onChangeText={setCouponCode}
                        placeholder="Enter promo code"
                        placeholderTextColor={colors.textMuted}
                        style={[styles.couponText, { color: colors.text }]}
                        autoCapitalize="characters"
                      />
                    </View>
                    <Pressable
                      onPress={handleApplyCoupon}
                      disabled={couponLoading}
                      style={[styles.couponApplyBtn, { backgroundColor: colors.primary }]}>
                      {couponLoading ? (
                        <ActivityIndicator size="small" color="#fff" />
                      ) : (
                        <Text style={styles.couponApplyText}>Apply</Text>
                      )}
                    </Pressable>
                  </View>
                )}
              </View>

              {/* Order Summary */}
              <View style={[styles.checkoutCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
                <SectionHeader icon="receipt-outline" title="Order Summary" colors={colors} />
                <SummaryLine label="Subtotal" value={`${Config.CURRENCY}${subtotal.toLocaleString()}`} colors={colors} />
                <SummaryLine label={`Shipping (${selectedDelivery?.name ?? '—'})`} value={`${Config.CURRENCY}${shippingCost.toLocaleString()}`} colors={colors} />
                {discountAmount > 0 && (
                  <SummaryLine label="Discount" value={`-${Config.CURRENCY}${discountAmount.toLocaleString()}`} colors={colors} isDiscount />
                )}
                <View style={[styles.divider, { backgroundColor: colors.border }]} />
                <View style={styles.totalRow}>
                  <Text style={[styles.totalLabel, { color: colors.text }]}>Total</Text>
                  <Text style={[styles.totalValue, { color: colors.primary }]}>{Config.CURRENCY}{grandTotal.toLocaleString()}</Text>
                </View>
                <View style={[styles.codNote, { backgroundColor: colors.primaryLight }]}>
                  <Ionicons name="cash-outline" size={16} color={colors.primary} />
                  <Text style={[styles.codNoteText, { color: colors.primary }]}>
                    Cash on Delivery — Pay when you receive
                  </Text>
                </View>
              </View>
            </ScrollView>

            {/* Place Order CTA */}
            <View style={[styles.cartFooter, { backgroundColor: colors.surface, borderTopColor: colors.border, paddingBottom: insets.bottom + Spacing.two }]}>
              <Pressable
                onPress={handlePlaceOrder}
                disabled={submittingOrder}
                style={[styles.checkoutBtn, { backgroundColor: colors.primary, opacity: submittingOrder ? 0.7 : 1 }]}>
                {submittingOrder ? (
                  <ActivityIndicator color="#fff" />
                ) : (
                  <>
                    <Ionicons name="flash" size={18} color="#fff" />
                    <Text style={styles.checkoutBtnText}>Place Order · {Config.CURRENCY}{grandTotal.toLocaleString()}</Text>
                  </>
                )}
              </Pressable>
            </View>
          </>
        )}
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

// ── Helper Components ──

function InfoRow({ icon, label, value, colors }: { icon: string; label: string; value: string; colors: any }) {
  return (
    <View style={{ flexDirection: 'row', alignItems: 'flex-start', gap: 10, paddingVertical: 6 }}>
      <Ionicons name={icon as any} size={16} color={colors.primary} style={{ marginTop: 1 }} />
      <View style={{ flex: 1 }}>
        <Text style={{ fontSize: 11, color: colors.textMuted, fontWeight: '600' }}>{label}</Text>
        <Text style={{ fontSize: 13, color: colors.text, fontWeight: '600', marginTop: 1 }}>{value}</Text>
      </View>
    </View>
  );
}

function SectionHeader({ icon, title, colors }: { icon: string; title: string; colors: any }) {
  return (
    <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: Spacing.two + 2 }}>
      <View style={{ width: 30, height: 30, borderRadius: 8, backgroundColor: colors.primaryLight, alignItems: 'center', justifyContent: 'center' }}>
        <Ionicons name={icon as any} size={15} color={colors.primary} />
      </View>
      <Text style={{ fontSize: 14, fontWeight: '800', color: colors.text }}>{title}</Text>
    </View>
  );
}

function FormInput({ label, value, onChange, placeholder, icon, keyboardType, multiline, colors }: any) {
  return (
    <View style={{ gap: 5, marginBottom: Spacing.two }}>
      <Text style={{ fontSize: 12, fontWeight: '700', color: colors.textSecondary }}>{label}</Text>
      <View style={[{ flexDirection: 'row', alignItems: multiline ? 'flex-start' : 'center', gap: 8, backgroundColor: colors.backgroundElement, borderRadius: Radius.lg, paddingHorizontal: 12, borderWidth: 1, borderColor: colors.border, minHeight: multiline ? 72 : 44, paddingTop: multiline ? 10 : 0 }]}>
        <Ionicons name={icon} size={15} color={colors.textMuted} style={multiline ? { marginTop: 2 } : {}} />
        <TextInput
          value={value}
          onChangeText={onChange}
          placeholder={placeholder}
          placeholderTextColor={colors.textMuted}
          keyboardType={keyboardType || 'default'}
          multiline={multiline}
          style={{ flex: 1, fontSize: 13, color: colors.text, padding: 0, textAlignVertical: multiline ? 'top' : 'center' }}
        />
      </View>
    </View>
  );
}

function SummaryLine({ label, value, colors, isDiscount }: { label: string; value: string; colors: any; isDiscount?: boolean }) {
  return (
    <View style={{ flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 4 }}>
      <Text style={{ fontSize: 13, color: colors.textSecondary }}>{label}</Text>
      <Text style={{ fontSize: 13, fontWeight: '700', color: isDiscount ? colors.success : colors.text }}>{value}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },

  headerBar: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: Spacing.three,
    paddingVertical: Spacing.two + 2,
    borderBottomWidth: StyleSheet.hairlineWidth,
  },
  headerTitle: { fontSize: 17, fontWeight: '800' },
  backBtn: { width: 36, height: 36, alignItems: 'center', justifyContent: 'center' },
  clearText: { fontSize: 13, fontWeight: '700' },

  // Empty cart
  emptyContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: Spacing.five,
    gap: Spacing.three,
  },
  emptyIcon: {
    width: 110,
    height: 110,
    borderRadius: 55,
    alignItems: 'center',
    justifyContent: 'center',
  },
  emptyTitle: { fontSize: 22, fontWeight: '800' },
  emptySub: { fontSize: 14, textAlign: 'center' },
  shopNowBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingHorizontal: Spacing.four,
    paddingVertical: Spacing.two + 2,
    borderRadius: Radius.full,
    marginTop: Spacing.two,
  },
  shopNowText: { color: '#fff', fontWeight: '700', fontSize: 15 },

  // Cart items
  cartItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.two + 2,
    padding: Spacing.two + 4,
    borderRadius: Radius.xl,
    borderWidth: StyleSheet.hairlineWidth,
  },
  cartImg: {
    width: 72,
    height: 72,
    borderRadius: Radius.lg,
    overflow: 'hidden',
    alignItems: 'center',
    justifyContent: 'center',
  },
  cartInfo: { flex: 1, gap: 3 },
  cartName: { fontSize: 13, fontWeight: '600', lineHeight: 18 },
  cartVariant: { fontSize: 11, fontWeight: '500' },
  cartPrice: { fontSize: 15, fontWeight: '800' },
  cartActions: { alignItems: 'flex-end', gap: Spacing.two },
  deleteBtn: {
    width: 30,
    height: 30,
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
  },
  qtyControl: {
    flexDirection: 'row',
    alignItems: 'center',
    borderRadius: Radius.md,
    borderWidth: 1,
    overflow: 'hidden',
  },
  qtyBtnSmall: {
    width: 28,
    height: 28,
    alignItems: 'center',
    justifyContent: 'center',
  },
  qtyNumSmall: {
    width: 28,
    textAlign: 'center',
    fontSize: 13,
    fontWeight: '800',
  },

  // Footer
  cartFooter: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    padding: Spacing.three,
    borderTopWidth: StyleSheet.hairlineWidth,
    gap: Spacing.two,
    ...Shadow.lg,
  },
  summaryRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  summaryLabel: { fontSize: 13 },
  summaryValue: { fontSize: 17, fontWeight: '800' },
  checkoutBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    paddingVertical: 14,
    borderRadius: Radius.lg,
  },
  checkoutBtnText: { color: '#fff', fontSize: 15, fontWeight: '800' },

  // Checkout
  checkoutCard: {
    padding: Spacing.four,
    borderRadius: Radius.xl,
    borderWidth: StyleSheet.hairlineWidth,
    gap: 2,
  },

  // Delivery methods
  methodRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.two + 2,
    padding: Spacing.three,
    borderRadius: Radius.lg,
    borderWidth: 1.5,
    marginBottom: Spacing.two,
  },
  radio: {
    width: 20,
    height: 20,
    borderRadius: 10,
    borderWidth: 2,
    alignItems: 'center',
    justifyContent: 'center',
  },
  radioFill: { width: 10, height: 10, borderRadius: 5 },
  methodName: { fontSize: 13, fontWeight: '700' },
  methodCost: { fontSize: 14, fontWeight: '800' },

  // Coupon
  couponRow: { flexDirection: 'row', gap: Spacing.two, alignItems: 'center' },
  couponInput: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    paddingHorizontal: 12,
    height: 44,
    borderRadius: Radius.lg,
    borderWidth: 1,
  },
  couponText: { flex: 1, fontSize: 14, fontWeight: '600', padding: 0 },
  couponApplyBtn: {
    paddingHorizontal: 16,
    height: 44,
    borderRadius: Radius.lg,
    alignItems: 'center',
    justifyContent: 'center',
  },
  couponApplyText: { color: '#fff', fontWeight: '800', fontSize: 13 },
  appliedCoupon: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    padding: 10,
    borderRadius: Radius.lg,
    borderWidth: 1,
  },
  appliedText: { flex: 1, fontSize: 13, fontWeight: '700' },

  // Summary
  divider: { height: 1, marginVertical: Spacing.two },
  totalRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  totalLabel: { fontSize: 15, fontWeight: '800' },
  totalValue: { fontSize: 20, fontWeight: '900' },
  codNote: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    padding: 10,
    borderRadius: Radius.lg,
    marginTop: Spacing.two,
  },
  codNoteText: { fontSize: 12, fontWeight: '700', flex: 1 },

  // Success
  successContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: Spacing.four,
    gap: Spacing.three,
  },
  successIcon: {
    width: 130,
    height: 130,
    borderRadius: 65,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: Spacing.two,
  },
  successTitle: { fontSize: 26, fontWeight: '900', textAlign: 'center' },
  successSub: { fontSize: 14, textAlign: 'center', lineHeight: 20 },
  orderInfoCard: {
    width: '100%',
    padding: Spacing.four,
    borderRadius: Radius.xl,
    borderWidth: StyleSheet.hairlineWidth,
    gap: 4,
  },
  successBtn: {
    width: '100%',
    paddingVertical: 14,
    borderRadius: Radius.lg,
    alignItems: 'center',
    marginTop: Spacing.two,
  },
  successBtnText: { color: '#fff', fontSize: 16, fontWeight: '800' },
  trackBtn: {
    width: '100%',
    paddingVertical: 13,
    borderRadius: Radius.lg,
    alignItems: 'center',
    borderWidth: 1.5,
  },
  trackBtnText: { fontSize: 15, fontWeight: '700' },
});
