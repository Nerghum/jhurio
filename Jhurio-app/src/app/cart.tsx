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
import { SafeAreaView } from 'react-native-safe-area-context';

import { Config } from '@/constants/config';
import { BottomTabInset, Colors, Spacing } from '@/constants/theme';
import { useCart } from '@/context/CartContext';
import { ApiService } from '@/services/api';
import { DeliveryMethod, Order } from '@/types';

export default function CartScreen() {
  const router = useRouter();
  const scheme = useColorScheme();
  const colors = Colors[scheme === 'dark' ? 'dark' : 'light'];

  const {
    items,
    removeFromCart,
    updateQuantity,
    clearCart,
    subtotal,
    customerName,
    setCustomerName,
    customerPhone,
    setCustomerPhone,
    customerAddress,
    setCustomerAddress,
    customerDistrict,
    setCustomerDistrict,
  } = useCart();

  const [deliveryMethods, setDeliveryMethods] = useState<DeliveryMethod[]>([]);
  const [selectedMethodId, setSelectedMethodId] = useState<number>(1);
  const [couponCode, setCouponCode] = useState('');
  const [appliedCoupon, setAppliedCoupon] = useState<{ code: string; discount: number } | null>(null);
  const [couponLoading, setCouponLoading] = useState(false);
  const [submittingOrder, setSubmittingOrder] = useState(false);
  const [createdOrder, setCreatedOrder] = useState<Order | null>(null);

  // Load delivery methods
  useEffect(() => {
    ApiService.getDeliveryMethods()
      .then((methods) => {
        setDeliveryMethods(methods);
        if (methods.length > 0) {
          setSelectedMethodId(methods[0].id);
        }
      })
      .catch(() => {
        // Fallback defaults
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

  // Apply Coupon
  const handleApplyCoupon = async () => {
    if (!couponCode.trim()) return;
    setCouponLoading(true);

    try {
      const res = await ApiService.applyCoupon(couponCode.trim(), subtotal);
      if (res.success && res.discount_amount) {
        setAppliedCoupon({
          code: res.coupon_code || couponCode.trim(),
          discount: res.discount_amount,
        });
        Alert.alert('Coupon Applied!', `You saved ${Config.CURRENCY}${res.discount_amount}`);
      } else {
        Alert.alert('Invalid Coupon', res.message || 'Could not apply coupon.');
      }
    } catch (e: any) {
      Alert.alert('Coupon Error', e.message || 'Invalid or expired coupon.');
    } finally {
      setCouponLoading(false);
    }
  };

  // Submit Cash on Delivery Order
  const handlePlaceOrder = async () => {
    if (!customerName.trim()) {
      Alert.alert('Required', 'Please enter your full name.');
      return;
    }
    if (!customerPhone.trim() || customerPhone.trim().length < 10) {
      Alert.alert('Required', 'Please enter a valid mobile number (e.g. 017XXXXXXXX).');
      return;
    }
    if (!customerAddress.trim()) {
      Alert.alert('Required', 'Please enter your full delivery address.');
      return;
    }

    setSubmittingOrder(true);

    try {
      const orderPayload = {
        name: customerName.trim(),
        phone: customerPhone.trim(),
        address: customerAddress.trim(),
        district: customerDistrict.trim() || 'ঢাকা',
        delivery_method_id: selectedMethodId,
        coupon_code: appliedCoupon?.code || undefined,
        items: items.map((i) => ({
          product_id: i.product.id,
          quantity: i.quantity,
          variation_id: i.variation?.id || null,
        })),
      };

      const res = await ApiService.createOrder(orderPayload);

      if (res.success && res.order) {
        setCreatedOrder(res.order);
        clearCart();
        setAppliedCoupon(null);
        setCouponCode('');
      } else {
        Alert.alert('Order Failed', res.message || 'Something went wrong.');
      }
    } catch (e: any) {
      Alert.alert('Order Error', e.message || 'Failed to place order. Please try again.');
    } finally {
      setSubmittingOrder(false);
    }
  };

  // Order Success View
  if (createdOrder) {
    return (
      <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['top']}>
        <ScrollView contentContainerStyle={styles.successContainer}>
          <View style={[styles.successIconBox, { backgroundColor: colors.primaryLight }]}>
            <Ionicons name="checkmark-circle" size={64} color={colors.primary} />
          </View>

          <Text style={[styles.successTitle, { color: colors.text }]}>Order Placed Successfully!</Text>
          <Text style={[styles.successSubtitle, { color: colors.textSecondary }]}>
            Thank you! Your Cash on Delivery order has been received. Our team will verify and dispatch it shortly.
          </Text>

          <View style={[styles.orderSummaryCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
            <View style={styles.orderRow}>
              <Text style={[styles.orderLabel, { color: colors.textSecondary }]}>Order ID:</Text>
              <Text style={[styles.orderValueBold, { color: colors.primary }]}>#{createdOrder.id}</Text>
            </View>

            <View style={styles.orderRow}>
              <Text style={[styles.orderLabel, { color: colors.textSecondary }]}>Customer:</Text>
              <Text style={[styles.orderValue, { color: colors.text }]}>{createdOrder.name}</Text>
            </View>

            <View style={styles.orderRow}>
              <Text style={[styles.orderLabel, { color: colors.textSecondary }]}>Phone:</Text>
              <Text style={[styles.orderValue, { color: colors.text }]}>{createdOrder.phone}</Text>
            </View>

            <View style={styles.orderRow}>
              <Text style={[styles.orderLabel, { color: colors.textSecondary }]}>Total Amount:</Text>
              <Text style={[styles.orderValueBold, { color: colors.primary }]}>
                {Config.CURRENCY}{Number(createdOrder.total_amount).toLocaleString()} (COD)
              </Text>
            </View>
          </View>

          <View style={styles.successActions}>
            <Pressable
              onPress={() => {
                setCreatedOrder(null);
                router.push('/orders');
              }}
              style={[styles.primaryButton, { backgroundColor: colors.primary }]}>
              <Ionicons name="receipt-outline" size={18} color="#ffffff" style={{ marginRight: 6 }} />
              <Text style={styles.primaryButtonText}>Track Order</Text>
            </Pressable>

            <Pressable
              onPress={() => {
                setCreatedOrder(null);
                router.push('/');
              }}
              style={[styles.secondaryButton, { borderColor: colors.border, backgroundColor: colors.surface }]}>
              <Text style={[styles.secondaryButtonText, { color: colors.text }]}>Continue Shopping</Text>
            </Pressable>
          </View>
        </ScrollView>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['top']}>
      {/* Header */}
      <View style={[styles.header, { backgroundColor: colors.surface, borderBottomColor: colors.border }]}>
        <Text style={[styles.headerTitle, { color: colors.text }]}>My Cart & Checkout</Text>
        {items.length > 0 && (
          <Pressable onPress={clearCart}>
            <Text style={[styles.clearText, { color: colors.danger }]}>Clear All</Text>
          </Pressable>
        )}
      </View>

      {items.length === 0 ? (
        <View style={styles.emptyContainer}>
          <Ionicons name="cart-outline" size={64} color={colors.textMuted} />
          <Text style={[styles.emptyTitle, { color: colors.text }]}>Your cart is empty</Text>
          <Text style={[styles.emptySubtitle, { color: colors.textSecondary }]}>
            Browse our shop and add your favorite products!
          </Text>
          <Pressable
            onPress={() => router.push('/explore')}
            style={[styles.primaryButton, { backgroundColor: colors.primary, marginTop: Spacing.four }]}>
            <Text style={styles.primaryButtonText}>Browse Products</Text>
          </Pressable>
        </View>
      ) : (
        <KeyboardAvoidingView
          behavior={Platform.OS === 'ios' ? 'padding' : undefined}
          style={{ flex: 1 }}>
          <ScrollView
            showsVerticalScrollIndicator={false}
            contentContainerStyle={[styles.scrollContent, { paddingBottom: BottomTabInset + Spacing.five }]}>
            {/* 1. Cart Items */}
            <View style={styles.section}>
              <Text style={[styles.sectionHeading, { color: colors.text }]}>
                Items ({items.reduce((s, i) => s + i.quantity, 0)})
              </Text>

              {items.map((item) => {
                const imgUri = item.product.thumbnail?.url || 'https://jhurio.com/assets/images/placeholder.png';
                const unitPrice = Number(item.variation?.price ?? item.product.price);

                return (
                  <View
                    key={`${item.product.id}-${item.variation?.id || 0}`}
                    style={[styles.cartItemCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
                    <Image source={{ uri: imgUri }} style={styles.itemImage} contentFit="cover" />

                    <View style={styles.itemDetails}>
                      <Text numberOfLines={2} style={[styles.itemName, { color: colors.text }]}>
                        {item.product.name}
                      </Text>

                      {item.variation && (
                        <Text style={[styles.itemVariation, { color: colors.textSecondary }]}>
                          Variation: {item.variation.name}
                        </Text>
                      )}

                      <Text style={[styles.itemPrice, { color: colors.primary }]}>
                        {Config.CURRENCY}{unitPrice.toLocaleString()}
                      </Text>

                      {/* Stepper */}
                      <View style={styles.stepperRow}>
                        <View style={[styles.stepper, { backgroundColor: colors.backgroundElement }]}>
                          <Pressable
                            onPress={() => updateQuantity(item.product.id, item.quantity - 1, item.variation?.id)}
                            style={styles.stepBtn}>
                            <Ionicons name="remove" size={14} color={colors.text} />
                          </Pressable>
                          <Text style={[styles.stepQty, { color: colors.text }]}>{item.quantity}</Text>
                          <Pressable
                            onPress={() => updateQuantity(item.product.id, item.quantity + 1, item.variation?.id)}
                            style={styles.stepBtn}>
                            <Ionicons name="add" size={14} color={colors.text} />
                          </Pressable>
                        </View>

                        <Pressable
                          onPress={() => removeFromCart(item.product.id, item.variation?.id)}
                          style={styles.removeBtn}>
                          <Ionicons name="trash-outline" size={16} color={colors.danger} />
                        </Pressable>
                      </View>
                    </View>
                  </View>
                );
              })}
            </View>

            {/* 2. Delivery Method */}
            <View style={styles.section}>
              <Text style={[styles.sectionHeading, { color: colors.text }]}>Delivery Area</Text>
              <View style={styles.deliveryGrid}>
                {deliveryMethods.map((method) => {
                  const isSelected = selectedMethodId === method.id;
                  return (
                    <Pressable
                      key={method.id}
                      onPress={() => setSelectedMethodId(method.id)}
                      style={[
                        styles.deliveryCard,
                        {
                          backgroundColor: colors.surface,
                          borderColor: isSelected ? colors.primary : colors.border,
                        },
                        isSelected && { backgroundColor: colors.primaryLight },
                      ]}>
                      <Ionicons
                        name={isSelected ? 'radio-button-on' : 'radio-button-off'}
                        size={18}
                        color={isSelected ? colors.primary : colors.textSecondary}
                      />
                      <View style={{ flex: 1, marginLeft: 8 }}>
                        <Text style={[styles.deliveryName, { color: colors.text }]}>{method.name}</Text>
                        <Text style={[styles.deliveryCost, { color: colors.primary }]}>
                          {Config.CURRENCY}{Number(method.cost).toLocaleString()}
                        </Text>
                      </View>
                    </Pressable>
                  );
                })}
              </View>
            </View>

            {/* 3. Coupon Code */}
            <View style={styles.section}>
              <Text style={[styles.sectionHeading, { color: colors.text }]}>Discount Coupon</Text>
              <View style={styles.couponRow}>
                <TextInput
                  placeholder="Enter promo code (e.g. WINTER50)"
                  placeholderTextColor={colors.textMuted}
                  value={couponCode}
                  onChangeText={setCouponCode}
                  autoCapitalize="characters"
                  style={[
                    styles.couponInput,
                    { backgroundColor: colors.surface, borderColor: colors.border, color: colors.text },
                  ]}
                />
                <Pressable
                  onPress={handleApplyCoupon}
                  disabled={couponLoading || !couponCode.trim()}
                  style={[styles.applyBtn, { backgroundColor: colors.primary }]}>
                  {couponLoading ? (
                    <ActivityIndicator size="small" color="#ffffff" />
                  ) : (
                    <Text style={styles.applyBtnText}>Apply</Text>
                  )}
                </Pressable>
              </View>

              {appliedCoupon && (
                <View style={[styles.appliedBox, { backgroundColor: colors.primaryLight }]}>
                  <Ionicons name="checkmark-circle" size={16} color={colors.primary} />
                  <Text style={[styles.appliedText, { color: colors.primary }]}>
                    Code "{appliedCoupon.code}" applied (-{Config.CURRENCY}{appliedCoupon.discount})
                  </Text>
                  <Pressable onPress={() => setAppliedCoupon(null)}>
                    <Ionicons name="close" size={16} color={colors.danger} />
                  </Pressable>
                </View>
              )}
            </View>

            {/* 4. Cash on Delivery Form */}
            <View style={styles.section}>
              <Text style={[styles.sectionHeading, { color: colors.text }]}>Delivery Address (COD)</Text>

              <View style={styles.inputGroup}>
                <Text style={[styles.inputLabel, { color: colors.textSecondary }]}>Full Name *</Text>
                <TextInput
                  placeholder="আপনার পুরো নাম লিখুন"
                  placeholderTextColor={colors.textMuted}
                  value={customerName}
                  onChangeText={setCustomerName}
                  style={[styles.input, { backgroundColor: colors.surface, borderColor: colors.border, color: colors.text }]}
                />
              </View>

              <View style={styles.inputGroup}>
                <Text style={[styles.inputLabel, { color: colors.textSecondary }]}>Mobile Number *</Text>
                <TextInput
                  placeholder="017XXXXXXXX"
                  placeholderTextColor={colors.textMuted}
                  keyboardType="phone-pad"
                  value={customerPhone}
                  onChangeText={setCustomerPhone}
                  style={[styles.input, { backgroundColor: colors.surface, borderColor: colors.border, color: colors.text }]}
                />
              </View>

              <View style={styles.inputGroup}>
                <Text style={[styles.inputLabel, { color: colors.textSecondary }]}>Full Delivery Address *</Text>
                <TextInput
                  placeholder="বাসা নং, রোড নং, এলাকা / থানা"
                  placeholderTextColor={colors.textMuted}
                  value={customerAddress}
                  onChangeText={setCustomerAddress}
                  multiline
                  numberOfLines={2}
                  style={[styles.textArea, { backgroundColor: colors.surface, borderColor: colors.border, color: colors.text }]}
                />
              </View>

              <View style={styles.inputGroup}>
                <Text style={[styles.inputLabel, { color: colors.textSecondary }]}>District / City</Text>
                <TextInput
                  placeholder="ঢাকা / চট্টগ্রাম / রাজশাহী ..."
                  placeholderTextColor={colors.textMuted}
                  value={customerDistrict}
                  onChangeText={setCustomerDistrict}
                  style={[styles.input, { backgroundColor: colors.surface, borderColor: colors.border, color: colors.text }]}
                />
              </View>
            </View>

            {/* 5. Cost Summary & Order Button */}
            <View style={[styles.summaryCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
              <View style={styles.summaryRow}>
                <Text style={[styles.summaryLabel, { color: colors.textSecondary }]}>Subtotal</Text>
                <Text style={[styles.summaryVal, { color: colors.text }]}>
                  {Config.CURRENCY}{subtotal.toLocaleString()}
                </Text>
              </View>

              <View style={styles.summaryRow}>
                <Text style={[styles.summaryLabel, { color: colors.textSecondary }]}>Delivery Fee</Text>
                <Text style={[styles.summaryVal, { color: colors.text }]}>
                  +{Config.CURRENCY}{shippingCost.toLocaleString()}
                </Text>
              </View>

              {discountAmount > 0 && (
                <View style={styles.summaryRow}>
                  <Text style={[styles.summaryLabel, { color: colors.danger }]}>Coupon Discount</Text>
                  <Text style={[styles.summaryVal, { color: colors.danger }]}>
                    -{Config.CURRENCY}{discountAmount.toLocaleString()}
                  </Text>
                </View>
              )}

              <View style={[styles.divider, { backgroundColor: colors.border }]} />

              <View style={styles.summaryRow}>
                <Text style={[styles.grandTotalLabel, { color: colors.text }]}>Total (COD)</Text>
                <Text style={[styles.grandTotalVal, { color: colors.primary }]}>
                  {Config.CURRENCY}{grandTotal.toLocaleString()}
                </Text>
              </View>

              <Pressable
                onPress={handlePlaceOrder}
                disabled={submittingOrder}
                style={[styles.checkoutButton, { backgroundColor: colors.primary }]}>
                {submittingOrder ? (
                  <ActivityIndicator size="small" color="#ffffff" />
                ) : (
                  <>
                    <Ionicons name="bag-check" size={20} color="#ffffff" style={{ marginRight: 8 }} />
                    <Text style={styles.checkoutButtonText}>
                      Confirm Order (Cash on Delivery)
                    </Text>
                  </>
                )}
              </Pressable>
            </View>
          </ScrollView>
        </KeyboardAvoidingView>
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: Spacing.four,
    paddingVertical: Spacing.three,
    borderBottomWidth: StyleSheet.hairlineWidth,
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '800',
  },
  clearText: {
    fontSize: 13,
    fontWeight: '700',
  },
  scrollContent: {
    padding: Spacing.four,
  },
  emptyContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: Spacing.five,
    gap: Spacing.two,
  },
  emptyTitle: {
    fontSize: 20,
    fontWeight: '800',
  },
  emptySubtitle: {
    fontSize: 14,
    textAlign: 'center',
  },
  section: {
    marginBottom: Spacing.four,
  },
  sectionHeading: {
    fontSize: 15,
    fontWeight: '800',
    marginBottom: Spacing.two,
  },
  cartItemCard: {
    flexDirection: 'row',
    borderRadius: 14,
    borderWidth: 1,
    padding: Spacing.two,
    marginBottom: Spacing.two,
    alignItems: 'center',
  },
  itemImage: {
    width: 70,
    height: 70,
    borderRadius: 10,
  },
  itemDetails: {
    flex: 1,
    marginLeft: Spacing.three,
  },
  itemName: {
    fontSize: 13,
    fontWeight: '700',
  },
  itemVariation: {
    fontSize: 11,
    marginTop: 2,
  },
  itemPrice: {
    fontSize: 14,
    fontWeight: '800',
    marginTop: 4,
  },
  stepperRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginTop: 6,
  },
  stepper: {
    flexDirection: 'row',
    alignItems: 'center',
    borderRadius: 8,
    paddingHorizontal: 4,
    paddingVertical: 2,
  },
  stepBtn: {
    padding: 4,
  },
  stepQty: {
    fontSize: 13,
    fontWeight: '700',
    paddingHorizontal: 8,
  },
  removeBtn: {
    padding: 6,
  },
  deliveryGrid: {
    flexDirection: 'row',
    gap: Spacing.two,
  },
  deliveryCard: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    padding: Spacing.three,
    borderRadius: 12,
    borderWidth: 1.5,
  },
  deliveryName: {
    fontSize: 13,
    fontWeight: '700',
  },
  deliveryCost: {
    fontSize: 13,
    fontWeight: '800',
    marginTop: 2,
  },
  couponRow: {
    flexDirection: 'row',
    gap: Spacing.two,
  },
  couponInput: {
    flex: 1,
    height: 44,
    borderRadius: 10,
    borderWidth: 1,
    paddingHorizontal: Spacing.three,
    fontSize: 13,
    fontWeight: '600',
  },
  applyBtn: {
    paddingHorizontal: Spacing.four,
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
  },
  applyBtnText: {
    color: '#ffffff',
    fontSize: 13,
    fontWeight: '700',
  },
  appliedBox: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: Spacing.three,
    paddingVertical: 8,
    borderRadius: 8,
    marginTop: Spacing.two,
    gap: 8,
  },
  appliedText: {
    flex: 1,
    fontSize: 12,
    fontWeight: '700',
  },
  inputGroup: {
    marginBottom: Spacing.three,
  },
  inputLabel: {
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 4,
  },
  input: {
    height: 44,
    borderRadius: 10,
    borderWidth: 1,
    paddingHorizontal: Spacing.three,
    fontSize: 14,
  },
  textArea: {
    minHeight: 64,
    borderRadius: 10,
    borderWidth: 1,
    paddingHorizontal: Spacing.three,
    paddingTop: 8,
    fontSize: 14,
    textAlignVertical: 'top',
  },
  summaryCard: {
    padding: Spacing.four,
    borderRadius: 16,
    borderWidth: 1,
    marginTop: Spacing.two,
  },
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: Spacing.two,
  },
  summaryLabel: {
    fontSize: 14,
  },
  summaryVal: {
    fontSize: 14,
    fontWeight: '700',
  },
  divider: {
    height: 1,
    marginVertical: Spacing.two,
  },
  grandTotalLabel: {
    fontSize: 16,
    fontWeight: '800',
  },
  grandTotalVal: {
    fontSize: 18,
    fontWeight: '900',
  },
  checkoutButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 14,
    borderRadius: 12,
    marginTop: Spacing.three,
  },
  checkoutButtonText: {
    color: '#ffffff',
    fontSize: 15,
    fontWeight: '800',
  },
  primaryButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 14,
    paddingHorizontal: Spacing.four,
    borderRadius: 12,
  },
  primaryButtonText: {
    color: '#ffffff',
    fontSize: 14,
    fontWeight: '700',
  },
  secondaryButton: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 14,
    paddingHorizontal: Spacing.four,
    borderRadius: 12,
    borderWidth: 1,
  },
  secondaryButtonText: {
    fontSize: 14,
    fontWeight: '700',
  },
  successContainer: {
    padding: Spacing.five,
    alignItems: 'center',
  },
  successIconBox: {
    width: 100,
    height: 100,
    borderRadius: 50,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: Spacing.three,
    marginTop: Spacing.four,
  },
  successTitle: {
    fontSize: 22,
    fontWeight: '900',
    textAlign: 'center',
    marginBottom: Spacing.two,
  },
  successSubtitle: {
    fontSize: 14,
    textAlign: 'center',
    lineHeight: 20,
    marginBottom: Spacing.four,
  },
  orderSummaryCard: {
    width: '100%',
    padding: Spacing.four,
    borderRadius: 16,
    borderWidth: 1,
    gap: Spacing.two,
    marginBottom: Spacing.four,
  },
  orderRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  orderLabel: {
    fontSize: 13,
  },
  orderValue: {
    fontSize: 13,
    fontWeight: '600',
  },
  orderValueBold: {
    fontSize: 14,
    fontWeight: '800',
  },
  successActions: {
    width: '100%',
    gap: Spacing.two,
  },
});
