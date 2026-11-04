import { Ionicons } from '@expo/vector-icons';
import { Image } from 'expo-image';
import { useLocalSearchParams, useRouter } from 'expo-router';
import React, { useEffect, useRef, useState } from 'react';
import {
  Alert,
  Animated,
  Dimensions,
  NativeScrollEvent,
  NativeSyntheticEvent,
  Platform,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  useColorScheme,
  View,
} from 'react-native';
import { SafeAreaView, useSafeAreaInsets } from 'react-native-safe-area-context';

import { Config } from '@/constants/config';
import { Colors, Radius, Shadow, Spacing } from '@/constants/theme';
import { useCart } from '@/context/CartContext';
import { ApiService } from '@/services/api';
import { BundleTier, Product, ProductVariation, Review } from '@/types';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

function SkeletonLine({ width, height = 12 }: { width: string | number; height?: number }) {
  const anim = useRef(new Animated.Value(0.5)).current;
  const scheme = useColorScheme();
  const colors = Colors[scheme === 'dark' ? 'dark' : 'light'];
  useEffect(() => {
    Animated.loop(
      Animated.sequence([
        Animated.timing(anim, { toValue: 1, duration: 700, useNativeDriver: true }),
        Animated.timing(anim, { toValue: 0.5, duration: 700, useNativeDriver: true }),
      ]),
    ).start();
  }, [anim]);
  return (
    <Animated.View style={{ width: width as any, height, borderRadius: 6, backgroundColor: colors.skeleton, opacity: anim }} />
  );
}

function ProductDetailSkeleton() {
  const scheme = useColorScheme();
  const colors = Colors[scheme === 'dark' ? 'dark' : 'light'];
  return (
    <View style={{ flex: 1 }}>
      <View style={{ width: SCREEN_WIDTH, height: 320, backgroundColor: colors.skeleton }} />
      <View style={{ padding: Spacing.four, gap: Spacing.two + 4 }}>
        <SkeletonLine width="90%" height={18} />
        <SkeletonLine width="70%" height={18} />
        <SkeletonLine width="40%" height={24} />
        <SkeletonLine width="60%" height={12} />
      </View>
    </View>
  );
}

export default function ProductDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const router = useRouter();
  const scheme = useColorScheme();
  const colors = Colors[scheme === 'dark' ? 'dark' : 'light'];
  const { addToCart, itemCount } = useCart();
  const insets = useSafeAreaInsets();

  const [product, setProduct] = useState<Product | null>(null);
  const [reviews, setReviews] = useState<Review[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedImageIndex, setSelectedImageIndex] = useState(0);
  const [selectedVariation, setSelectedVariation] = useState<ProductVariation | undefined>(undefined);
  const [selectedTier, setSelectedTier] = useState<BundleTier | undefined>(undefined);
  const [quantity, setQuantity] = useState(1);
  const [addedAnim] = useState(new Animated.Value(1));
  const [wishlist, setWishlist] = useState(false);

  const mainScrollRef = useRef<ScrollView>(null);
  const thumbScrollRef = useRef<ScrollView>(null);

  useEffect(() => {
    if (!id) return;
    setLoading(true);
    ApiService.getProductDetail(id)
      .then((prod) => {
        setProduct(prod);
        if (prod.variations?.length) setSelectedVariation(prod.variations[0]);
        if (prod.bundle_settings?.enabled && prod.bundle_settings.tiers?.length) {
          setSelectedTier(prod.bundle_settings.tiers[0]);
          setQuantity(prod.bundle_settings.tiers[0].quantity);
        }
        ApiService.getReviews(prod.id).then(setReviews).catch(() => {});
        ApiService.trackEvent('view_product', { product_id: prod.id, name: prod.name });
      })
      .catch((e) => console.warn('Failed to load product', e))
      .finally(() => setLoading(false));
  }, [id]);

  if (loading) {
    return (
      <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['top']}>
        <View style={[styles.headerBar, { backgroundColor: colors.surface, borderBottomColor: colors.border }]}>
          <Pressable onPress={() => router.back()} style={[styles.headerIconBtn, { backgroundColor: colors.backgroundElement }]}>
            <Ionicons name="chevron-back" size={22} color={colors.text} />
          </Pressable>
          <SkeletonLine width={160} height={15} />
          <View style={[styles.headerIconBtn, { backgroundColor: colors.backgroundElement }]}>
            <Ionicons name="cart-outline" size={22} color={colors.text} />
          </View>
        </View>
        <ProductDetailSkeleton />
      </SafeAreaView>
    );
  }

  if (!product) {
    return (
      <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['top']}>
        <View style={[styles.headerBar, { backgroundColor: colors.surface, borderBottomColor: colors.border }]}>
          <Pressable onPress={() => router.back()} style={[styles.headerIconBtn, { backgroundColor: colors.backgroundElement }]}>
            <Ionicons name="chevron-back" size={22} color={colors.text} />
          </Pressable>
        </View>
        <View style={styles.errorContainer}>
          <View style={[styles.errorIconWrap, { backgroundColor: '#fee2e2' }]}>
            <Ionicons name="alert-circle-outline" size={48} color={colors.danger} />
          </View>
          <Text style={[styles.errorTitle, { color: colors.text }]}>Product Not Found</Text>
          <Pressable onPress={() => router.back()} style={[styles.errorBtn, { backgroundColor: colors.primary }]}>
            <Text style={styles.errorBtnText}>Go Back</Text>
          </Pressable>
        </View>
      </SafeAreaView>
    );
  }

  // Compile gallery
  const allImages: string[] = [];
  if (product.thumbnail?.url) allImages.push(product.thumbnail.url);
  product.gallery?.forEach((g) => { if (g.url && !allImages.includes(g.url)) allImages.push(g.url); });
  if (!allImages.length) allImages.push('https://jhurio.com/assets/images/placeholder.png');

  const basePrice = Number(selectedVariation?.price ?? product.price);
  const comparePrice = product.compare_at_price ? Number(product.compare_at_price) : null;
  const hasDiscount = comparePrice && comparePrice > basePrice;
  const discountPercent = hasDiscount ? Math.round(((comparePrice - basePrice) / comparePrice) * 100) : 0;

  const totalPrice = basePrice * quantity;

  const handleMainScroll = (e: NativeSyntheticEvent<NativeScrollEvent>) => {
    const idx = Math.round(e.nativeEvent.contentOffset.x / SCREEN_WIDTH);
    setSelectedImageIndex(idx);
    // scroll thumbs
    thumbScrollRef.current?.scrollTo({ x: idx * 60, animated: true });
  };

  const handleThumbPress = (idx: number) => {
    setSelectedImageIndex(idx);
    mainScrollRef.current?.scrollTo({ x: idx * SCREEN_WIDTH, animated: true });
  };

  const handleAddToCart = () => {
    addToCart(product, quantity, selectedVariation, selectedTier);
    Animated.sequence([
      Animated.timing(addedAnim, { toValue: 0.9, duration: 100, useNativeDriver: true }),
      Animated.spring(addedAnim, { toValue: 1, useNativeDriver: true }),
    ]).start();
    Alert.alert('✅ Added to Cart', `${product.name} added successfully!`, [
      { text: 'Continue Shopping' },
      { text: 'View Cart', onPress: () => router.push('/cart') },
    ]);
  };

  const handleBuyNow = () => {
    addToCart(product, quantity, selectedVariation, selectedTier);
    router.push('/cart');
  };

  const avgRating = reviews.length > 0
    ? (reviews.reduce((sum, r) => sum + r.rating, 0) / reviews.length).toFixed(1)
    : null;

  const BOTTOM_BAR_HEIGHT = 70 + insets.bottom;

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['top']}>

      {/* Top Header */}
      <View style={[styles.headerBar, { backgroundColor: colors.surface, borderBottomColor: colors.border }]}>
        <Pressable onPress={() => router.back()} style={[styles.headerIconBtn, { backgroundColor: colors.backgroundElement }]}>
          <Ionicons name="chevron-back" size={22} color={colors.text} />
        </Pressable>

        <Text numberOfLines={1} style={[styles.headerTitle, { color: colors.text }]}>
          {product.name}
        </Text>

        <View style={{ flexDirection: 'row', gap: Spacing.one + 2 }}>
          <Pressable
            onPress={() => setWishlist((w) => !w)}
            style={[styles.headerIconBtn, { backgroundColor: colors.backgroundElement }]}>
            <Ionicons name={wishlist ? 'heart' : 'heart-outline'} size={20} color={wishlist ? colors.danger : colors.text} />
          </Pressable>
          <Pressable onPress={() => router.push('/cart')} style={[styles.headerIconBtn, { backgroundColor: colors.backgroundElement }]}>
            <Ionicons name="cart-outline" size={20} color={colors.text} />
            {itemCount > 0 && (
              <View style={[styles.headerBadge, { backgroundColor: colors.danger }]}>
                <Text style={styles.headerBadgeText}>{itemCount}</Text>
              </View>
            )}
          </Pressable>
        </View>
      </View>

      <ScrollView
        showsVerticalScrollIndicator={false}
        contentContainerStyle={{ paddingBottom: BOTTOM_BAR_HEIGHT + Spacing.three }}>

        {/* ── Image Carousel ── */}
        <View style={[styles.imageCarousel, { backgroundColor: colors.surface }]}>
          <ScrollView
            ref={mainScrollRef}
            horizontal
            pagingEnabled
            showsHorizontalScrollIndicator={false}
            onScroll={handleMainScroll}
            scrollEventThrottle={16}>
            {allImages.map((uri, idx) => (
              <View key={idx} style={{ width: SCREEN_WIDTH, height: 320 }}>
                <Image source={{ uri }} style={styles.mainImage} contentFit="contain" />
              </View>
            ))}
          </ScrollView>

          {/* Dot indicators */}
          {allImages.length > 1 && (
            <View style={styles.dotsRow}>
              {allImages.map((_, i) => (
                <View
                  key={i}
                  style={[
                    styles.dot,
                    {
                      backgroundColor: i === selectedImageIndex ? colors.primary : colors.border,
                      width: i === selectedImageIndex ? 20 : 6,
                    },
                  ]}
                />
              ))}
            </View>
          )}

          {/* Discount tag */}
          {hasDiscount && (
            <View style={[styles.discountTag, { backgroundColor: colors.danger }]}>
              <Text style={styles.discountTagText}>SAVE {discountPercent}%</Text>
            </View>
          )}
        </View>

        {/* Thumbnail Strip */}
        {allImages.length > 1 && (
          <ScrollView
            ref={thumbScrollRef}
            horizontal
            showsHorizontalScrollIndicator={false}
            contentContainerStyle={styles.thumbStrip}
            style={[{ backgroundColor: colors.surface }]}>
            {allImages.map((uri, idx) => (
              <Pressable
                key={idx}
                onPress={() => handleThumbPress(idx)}
                style={[
                  styles.thumb,
                  {
                    borderColor: idx === selectedImageIndex ? colors.primary : colors.border,
                    backgroundColor: colors.backgroundElement,
                  },
                ]}>
                <Image source={{ uri }} style={styles.thumbImage} contentFit="cover" />
              </Pressable>
            ))}
          </ScrollView>
        )}

        {/* ── Product Info Card ── */}
        <View style={[styles.infoCard, { backgroundColor: colors.surface, borderColor: colors.border, ...Shadow.sm }]}>
          {/* Category tag */}
          {product.categories && product.categories.length > 0 && (
            <View style={[styles.categoryTag, { backgroundColor: colors.primaryLight }]}>
              <Text style={[styles.categoryTagText, { color: colors.primary }]}>
                {product.categories[0].name ?? 'Product'}
              </Text>
            </View>
          )}

          <Text style={[styles.productName, { color: colors.text }]}>{product.name}</Text>

          {/* Rating row */}
          {avgRating && (
            <View style={styles.ratingRow}>
              <View style={styles.stars}>
                {[1,2,3,4,5].map((s) => (
                  <Ionicons
                    key={s}
                    name={s <= Math.round(Number(avgRating)) ? 'star' : 'star-outline'}
                    size={14}
                    color="#f59e0b"
                  />
                ))}
              </View>
              <Text style={[styles.ratingText, { color: colors.text }]}>{avgRating}</Text>
              <Text style={[styles.ratingCount, { color: colors.textMuted }]}>
                ({reviews.length} reviews)
              </Text>
            </View>
          )}

          {/* Price row */}
          <View style={styles.priceRow}>
            <Text style={[styles.priceText, { color: colors.primary }]}>
              {Config.CURRENCY}{basePrice.toLocaleString()}
            </Text>
            {hasDiscount && (
              <Text style={[styles.compareText, { color: colors.textMuted }]}>
                {Config.CURRENCY}{comparePrice.toLocaleString()}
              </Text>
            )}
          </View>

          {/* COD Badge */}
          <View style={[styles.codBadge, { backgroundColor: colors.primaryLight, borderColor: colors.primary }]}>
            <Ionicons name="cash-outline" size={14} color={colors.primary} />
            <Text style={[styles.codText, { color: colors.primary }]}>
              Cash on Delivery Available
            </Text>
          </View>
        </View>

        {/* ── Bundle Tiers ── */}
        {product.bundle_settings?.enabled && product.bundle_settings.tiers && (
          <View style={[styles.section, { backgroundColor: colors.surface, borderColor: colors.border }]}>
            <View style={styles.sectionTitleRow}>
              <Ionicons name="pricetag-outline" size={18} color={colors.primary} />
              <Text style={[styles.sectionTitle, { color: colors.text }]}>
                {product.bundle_settings.heading || 'Bundle Offers'}
              </Text>
            </View>
            {product.bundle_settings.subtitle && (
              <Text style={[styles.sectionSub, { color: colors.textSecondary }]}>
                {product.bundle_settings.subtitle}
              </Text>
            )}
            <View style={styles.bundleGrid}>
              {product.bundle_settings.tiers.map((tier, idx) => {
                const isSelected = selectedTier?.quantity === tier.quantity;
                return (
                  <Pressable
                    key={idx}
                    onPress={() => { setSelectedTier(tier); setQuantity(tier.quantity); }}
                    style={[
                      styles.bundleCard,
                      {
                        backgroundColor: isSelected ? colors.primaryLight : colors.backgroundElement,
                        borderColor: isSelected ? colors.primary : colors.border,
                      },
                    ]}>
                    {tier.badge && (
                      <View style={[styles.tierBadge, { backgroundColor: colors.accent }]}>
                        <Text style={styles.tierBadgeText}>{tier.badge}</Text>
                      </View>
                    )}
                    {isSelected && (
                      <View style={[styles.checkmark, { backgroundColor: colors.primary }]}>
                        <Ionicons name="checkmark" size={12} color="#fff" />
                      </View>
                    )}
                    <Text style={[styles.tierTitle, { color: colors.text }]}>{tier.title}</Text>
                    <Text style={[styles.tierQty, { color: colors.primary }]}>
                      {tier.quantity} {tier.quantity > 1 ? 'pcs' : 'pc'}
                    </Text>
                  </Pressable>
                );
              })}
            </View>
          </View>
        )}

        {/* ── Variations ── */}
        {product.variations && product.variations.length > 0 && (
          <View style={[styles.section, { backgroundColor: colors.surface, borderColor: colors.border }]}>
            <Text style={[styles.sectionTitle, { color: colors.text }]}>Select Option</Text>
            <View style={styles.variationsRow}>
              {product.variations.map((v) => {
                const isSel = selectedVariation?.id === v.id;
                return (
                  <Pressable
                    key={v.id}
                    onPress={() => setSelectedVariation(v)}
                    style={[
                      styles.variationPill,
                      {
                        backgroundColor: isSel ? colors.primary : colors.backgroundElement,
                        borderColor: isSel ? colors.primary : colors.border,
                      },
                    ]}>
                    <Text style={[styles.variationText, { color: isSel ? '#fff' : colors.text }]}>
                      {v.name}
                    </Text>
                  </Pressable>
                );
              })}
            </View>
          </View>
        )}

        {/* ── Quantity ── */}
        {!product.bundle_settings?.enabled && (
          <View style={[styles.section, { backgroundColor: colors.surface, borderColor: colors.border }]}>
            <Text style={[styles.sectionTitle, { color: colors.text }]}>Quantity</Text>
            <View style={styles.qtyRow}>
              <Pressable
                onPress={() => setQuantity((q) => Math.max(1, q - 1))}
                style={[styles.qtyBtn, { backgroundColor: colors.backgroundElement, borderColor: colors.border }]}>
                <Ionicons name="remove" size={18} color={colors.text} />
              </Pressable>
              <View style={[styles.qtyDisplay, { borderColor: colors.border }]}>
                <Text style={[styles.qtyText, { color: colors.text }]}>{quantity}</Text>
              </View>
              <Pressable
                onPress={() => setQuantity((q) => q + 1)}
                style={[styles.qtyBtn, { backgroundColor: colors.primary }]}>
                <Ionicons name="add" size={18} color="#fff" />
              </Pressable>
            </View>
          </View>
        )}

        {/* ── Feature Sections ── */}
        {product.image_text_sections && product.image_text_sections.length > 0 && (
          <View style={styles.featureSections}>
            {product.image_text_sections.map((sec, idx) => (
              <View key={idx} style={[styles.featureCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
                {sec.image_url ? (
                  <Image source={{ uri: sec.image_url }} style={styles.featureImage} contentFit="cover" />
                ) : null}
                <View style={styles.featureContent}>
                  {sec.subtitle && (
                    <Text style={[styles.featureSubtitle, { color: colors.primary }]}>{sec.subtitle}</Text>
                  )}
                  <Text style={[styles.featureTitle, { color: colors.text }]}>{sec.title}</Text>
                  <Text style={[styles.featureBody, { color: colors.textSecondary }]}>
                    {sec.content.replace(/<[^>]*>?/gm, '')}
                  </Text>
                </View>
              </View>
            ))}
          </View>
        )}

        {/* ── Reviews ── */}
        {reviews.length > 0 && (
          <View style={[styles.section, { backgroundColor: colors.surface, borderColor: colors.border }]}>
            <View style={styles.sectionTitleRow}>
              <Ionicons name="star" size={18} color="#f59e0b" />
              <Text style={[styles.sectionTitle, { color: colors.text }]}>
                Customer Reviews ({reviews.length})
              </Text>
            </View>
            {reviews.slice(0, 5).map((rev) => (
              <View key={rev.id} style={[styles.reviewItem, { borderBottomColor: colors.border }]}>
                <View style={styles.reviewTop}>
                  <View style={[styles.reviewAvatar, { backgroundColor: colors.primaryLight }]}>
                    <Text style={[styles.reviewAvatarText, { color: colors.primary }]}>
                      {rev.reviewer_name.charAt(0).toUpperCase()}
                    </Text>
                  </View>
                  <View style={{ flex: 1 }}>
                    <Text style={[styles.reviewerName, { color: colors.text }]}>{rev.reviewer_name}</Text>
                    <View style={styles.reviewStars}>
                      {[1,2,3,4,5].map((s) => (
                        <Ionicons
                          key={s}
                          name={s <= rev.rating ? 'star' : 'star-outline'}
                          size={12}
                          color="#f59e0b"
                        />
                      ))}
                    </View>
                  </View>
                </View>
                {rev.comment && (
                  <Text style={[styles.reviewComment, { color: colors.textSecondary }]}>{rev.comment}</Text>
                )}
              </View>
            ))}
          </View>
        )}
      </ScrollView>

      {/* ── Sticky Bottom Action Bar ── */}
      <View
        style={[
          styles.bottomBar,
          {
            backgroundColor: colors.surface,
            borderTopColor: colors.border,
            paddingBottom: insets.bottom + Spacing.two,
          },
        ]}>
        <View style={styles.bottomPriceWrap}>
          <Text style={[styles.bottomLabel, { color: colors.textMuted }]}>Total</Text>
          <Text style={[styles.bottomPrice, { color: colors.primary }]}>
            {Config.CURRENCY}{totalPrice.toLocaleString()}
          </Text>
        </View>

        <View style={styles.bottomActions}>
          <Animated.View style={{ transform: [{ scale: addedAnim }] }}>
            <Pressable
              onPress={handleAddToCart}
              style={[styles.addCartBtn, { backgroundColor: colors.primaryLight, borderColor: colors.primary }]}>
              <Ionicons name="cart-outline" size={18} color={colors.primary} />
              <Text style={[styles.addCartText, { color: colors.primary }]}>Add Cart</Text>
            </Pressable>
          </Animated.View>

          <Pressable
            onPress={handleBuyNow}
            style={[styles.buyNowBtn, { backgroundColor: colors.primary }]}>
            <Ionicons name="flash" size={16} color="#fff" />
            <Text style={styles.buyNowText}>Order Now · COD</Text>
          </Pressable>
        </View>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },

  // Header
  headerBar: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: Spacing.three,
    paddingVertical: Spacing.two,
    borderBottomWidth: StyleSheet.hairlineWidth,
    gap: Spacing.two,
  },
  headerTitle: {
    flex: 1,
    fontSize: 15,
    fontWeight: '700',
  },
  headerIconBtn: {
    width: 38,
    height: 38,
    borderRadius: Radius.md,
    alignItems: 'center',
    justifyContent: 'center',
    position: 'relative',
  },
  headerBadge: {
    position: 'absolute',
    top: -4,
    right: -4,
    minWidth: 16,
    height: 16,
    borderRadius: 8,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 3,
    borderWidth: 1.5,
    borderColor: '#fff',
  },
  headerBadgeText: { color: '#fff', fontSize: 8, fontWeight: '800' },

  // Error / Loading states
  errorContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: Spacing.five,
    gap: Spacing.three,
  },
  errorIconWrap: {
    width: 96,
    height: 96,
    borderRadius: 48,
    alignItems: 'center',
    justifyContent: 'center',
  },
  errorTitle: { fontSize: 20, fontWeight: '800' },
  errorBtn: {
    paddingHorizontal: Spacing.four,
    paddingVertical: Spacing.two + 2,
    borderRadius: Radius.full,
    marginTop: Spacing.two,
  },
  errorBtnText: { color: '#fff', fontWeight: '700' },

  // Image carousel
  imageCarousel: { position: 'relative' },
  mainImage: { width: '100%', height: '100%' },
  dotsRow: {
    position: 'absolute',
    bottom: 10,
    alignSelf: 'center',
    flexDirection: 'row',
    gap: 5,
  },
  dot: { height: 6, borderRadius: 3 },
  discountTag: {
    position: 'absolute',
    top: 14,
    left: 14,
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 7,
  },
  discountTagText: { color: '#fff', fontSize: 11, fontWeight: '800' },

  // Thumbnail strip
  thumbStrip: {
    paddingHorizontal: Spacing.three,
    paddingVertical: Spacing.two,
    gap: Spacing.two,
  },
  thumb: {
    width: 56,
    height: 56,
    borderRadius: Radius.md,
    overflow: 'hidden',
    borderWidth: 2,
  },
  thumbImage: { width: '100%', height: '100%' },

  // Info card
  infoCard: {
    marginHorizontal: Spacing.three,
    marginTop: Spacing.two,
    padding: Spacing.four,
    borderRadius: Radius.xl,
    borderWidth: StyleSheet.hairlineWidth,
    gap: Spacing.two,
  },
  categoryTag: {
    alignSelf: 'flex-start',
    paddingHorizontal: 10,
    paddingVertical: 3,
    borderRadius: Radius.full,
  },
  categoryTagText: { fontSize: 11, fontWeight: '700', textTransform: 'uppercase' },
  productName: { fontSize: 18, fontWeight: '800', lineHeight: 24 },
  ratingRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
  },
  stars: { flexDirection: 'row', gap: 2 },
  ratingText: { fontSize: 13, fontWeight: '800' },
  ratingCount: { fontSize: 12 },
  priceRow: { flexDirection: 'row', alignItems: 'baseline', gap: 8 },
  priceText: { fontSize: 24, fontWeight: '900' },
  compareText: { fontSize: 15, textDecorationLine: 'line-through' },
  codBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: Radius.md,
    borderWidth: 1,
    alignSelf: 'flex-start',
    marginTop: 2,
  },
  codText: { fontSize: 12, fontWeight: '700' },

  // Sections
  section: {
    marginHorizontal: Spacing.three,
    marginTop: Spacing.two,
    padding: Spacing.four,
    borderRadius: Radius.xl,
    borderWidth: StyleSheet.hairlineWidth,
    gap: Spacing.two + 2,
  },
  sectionTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.two,
  },
  sectionTitle: { fontSize: 15, fontWeight: '800' },
  sectionSub: { fontSize: 12, marginTop: -Spacing.one },

  // Bundle
  bundleGrid: { gap: Spacing.two },
  bundleCard: {
    padding: Spacing.three,
    borderRadius: Radius.lg,
    borderWidth: 2,
    position: 'relative',
  },
  tierBadge: {
    position: 'absolute',
    top: -8,
    right: 12,
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 5,
  },
  tierBadgeText: { color: '#fff', fontSize: 9, fontWeight: '800' },
  checkmark: {
    position: 'absolute',
    top: -8,
    left: 12,
    width: 20,
    height: 20,
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
  },
  tierTitle: { fontSize: 13, fontWeight: '700' },
  tierQty: { fontSize: 12, fontWeight: '800' },

  // Variations
  variationsRow: { flexDirection: 'row', flexWrap: 'wrap', gap: Spacing.two },
  variationPill: {
    paddingHorizontal: 14,
    paddingVertical: 7,
    borderRadius: Radius.full,
    borderWidth: 1.5,
  },
  variationText: { fontSize: 13, fontWeight: '700' },

  // Quantity
  qtyRow: { flexDirection: 'row', alignItems: 'center', gap: Spacing.two },
  qtyBtn: {
    width: 40,
    height: 40,
    borderRadius: Radius.md,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
  },
  qtyDisplay: {
    minWidth: 56,
    height: 40,
    borderRadius: Radius.md,
    borderWidth: 1.5,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 10,
  },
  qtyText: { fontSize: 16, fontWeight: '800' },

  // Feature sections
  featureSections: {
    marginHorizontal: Spacing.three,
    marginTop: Spacing.two,
    gap: Spacing.two,
  },
  featureCard: {
    borderRadius: Radius.xl,
    overflow: 'hidden',
    borderWidth: StyleSheet.hairlineWidth,
  },
  featureImage: { width: '100%', height: 200 },
  featureContent: { padding: Spacing.four, gap: 4 },
  featureSubtitle: { fontSize: 11, fontWeight: '800', textTransform: 'uppercase', letterSpacing: 0.5 },
  featureTitle: { fontSize: 16, fontWeight: '800' },
  featureBody: { fontSize: 13, lineHeight: 20 },

  // Reviews
  reviewItem: { paddingVertical: Spacing.three, borderBottomWidth: StyleSheet.hairlineWidth, gap: Spacing.two },
  reviewTop: { flexDirection: 'row', alignItems: 'flex-start', gap: Spacing.two },
  reviewAvatar: {
    width: 36,
    height: 36,
    borderRadius: 18,
    alignItems: 'center',
    justifyContent: 'center',
  },
  reviewAvatarText: { fontSize: 15, fontWeight: '800' },
  reviewerName: { fontSize: 13, fontWeight: '700', marginBottom: 2 },
  reviewStars: { flexDirection: 'row', gap: 2 },
  reviewComment: { fontSize: 13, lineHeight: 19, paddingLeft: 44 },

  // Bottom bar
  bottomBar: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: Spacing.three,
    paddingTop: Spacing.two + 2,
    borderTopWidth: StyleSheet.hairlineWidth,
    ...Shadow.lg,
  },
  bottomPriceWrap: { gap: 2 },
  bottomLabel: { fontSize: 11 },
  bottomPrice: { fontSize: 20, fontWeight: '900' },
  bottomActions: { flexDirection: 'row', gap: Spacing.two, alignItems: 'center' },
  addCartBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    paddingHorizontal: Spacing.three,
    paddingVertical: 11,
    borderRadius: Radius.lg,
    borderWidth: 1.5,
  },
  addCartText: { fontSize: 13, fontWeight: '700' },
  buyNowBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    paddingHorizontal: Spacing.three + 4,
    paddingVertical: 11,
    borderRadius: Radius.lg,
    ...Shadow.sm,
  },
  buyNowText: { color: '#fff', fontSize: 13, fontWeight: '800' },
});
