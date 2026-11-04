import { Ionicons } from '@expo/vector-icons';
import { Image } from 'expo-image';
import { useLocalSearchParams, useRouter } from 'expo-router';
import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  Dimensions,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  useColorScheme,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { Config } from '@/constants/config';
import { Colors, Spacing } from '@/constants/theme';
import { useCart } from '@/context/CartContext';
import { ApiService } from '@/services/api';
import { BundleTier, Product, ProductVariation, Review } from '@/types';

const { width } = Dimensions.get('window');

export default function ProductDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const router = useRouter();
  const scheme = useColorScheme();
  const colors = Colors[scheme === 'dark' ? 'dark' : 'light'];
  const { addToCart } = useCart();

  const [product, setProduct] = useState<Product | null>(null);
  const [reviews, setReviews] = useState<Review[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedImageIndex, setSelectedImageIndex] = useState(0);
  const [selectedVariation, setSelectedVariation] = useState<ProductVariation | undefined>(undefined);
  const [selectedTier, setSelectedTier] = useState<BundleTier | undefined>(undefined);
  const [quantity, setQuantity] = useState(1);

  useEffect(() => {
    if (!id) return;
    setLoading(true);

    ApiService.getProductDetail(id)
      .then((prod) => {
        setProduct(prod);
        if (prod.variations && prod.variations.length > 0) {
          setSelectedVariation(prod.variations[0]);
        }
        if (prod.bundle_settings?.enabled && prod.bundle_settings.tiers && prod.bundle_settings.tiers.length > 0) {
          setSelectedTier(prod.bundle_settings.tiers[0]);
          setQuantity(prod.bundle_settings.tiers[0].quantity);
        }
        // Load reviews
        ApiService.getReviews(prod.id).then(setReviews).catch(() => {});
        ApiService.trackEvent('view_product', { product_id: prod.id, name: prod.name });
      })
      .catch((e) => {
        console.warn('Failed to load product', e);
      })
      .finally(() => {
        setLoading(false);
      });
  }, [id]);

  if (loading) {
    return (
      <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]}>
        <View style={styles.centerContainer}>
          <ActivityIndicator size="large" color={colors.primary} />
          <Text style={[styles.loadingText, { color: colors.textSecondary }]}>Loading product details...</Text>
        </View>
      </SafeAreaView>
    );
  }

  if (!product) {
    return (
      <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]}>
        <View style={styles.centerContainer}>
          <Ionicons name="alert-circle-outline" size={48} color={colors.danger} />
          <Text style={[styles.emptyTitle, { color: colors.text }]}>Product Not Found</Text>
          <Pressable onPress={() => router.back()} style={[styles.backBtn, { backgroundColor: colors.primary }]}>
            <Text style={styles.backBtnText}>Go Back</Text>
          </Pressable>
        </View>
      </SafeAreaView>
    );
  }

  // Compile gallery images (thumbnail first, then gallery)
  const allImages: string[] = [];
  if (product.thumbnail?.url) allImages.push(product.thumbnail.url);
  if (product.gallery && product.gallery.length > 0) {
    product.gallery.forEach((g) => {
      if (g.url && !allImages.includes(g.url)) allImages.push(g.url);
    });
  }
  if (allImages.length === 0) {
    allImages.push('https://jhurio.com/assets/images/placeholder.png');
  }

  const basePrice = Number(selectedVariation?.price ?? product.price);
  const comparePrice = product.compare_at_price ? Number(product.compare_at_price) : null;
  const hasDiscount = comparePrice && comparePrice > basePrice;
  const discountPercent = hasDiscount ? Math.round(((comparePrice - basePrice) / comparePrice) * 100) : 0;

  // Handle Add To Cart
  const handleAddToCart = () => {
    addToCart(product, quantity, selectedVariation, selectedTier);
    Alert.alert('Added to Cart', `${product.name} (${quantity} pc) added to your cart.`);
  };

  // Handle Buy Now (1-Click direct to checkout)
  const handleBuyNow = () => {
    addToCart(product, quantity, selectedVariation, selectedTier);
    router.push('/cart');
  };

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['top']}>
      {/* Top Header */}
      <View style={[styles.header, { backgroundColor: colors.surface, borderBottomColor: colors.border }]}>
        <Pressable onPress={() => router.back()} style={styles.headerBtn}>
          <Ionicons name="arrow-back" size={22} color={colors.text} />
        </Pressable>
        <Text numberOfLines={1} style={[styles.headerTitle, { color: colors.text }]}>
          {product.name}
        </Text>
        <Pressable onPress={() => router.push('/cart')} style={styles.headerBtn}>
          <Ionicons name="bag-handle-outline" size={22} color={colors.text} />
        </Pressable>
      </View>

      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.scrollContent}>
        {/* 1. Image Carousel */}
        <View style={styles.carouselContainer}>
          <ScrollView
            horizontal
            pagingEnabled
            showsHorizontalScrollIndicator={false}
            onScroll={(e) => {
              const x = e.nativeEvent.contentOffset.x;
              setSelectedImageIndex(Math.round(x / width));
            }}
            scrollEventThrottle={16}>
            {allImages.map((uri, idx) => (
              <View key={idx} style={{ width, height: 320, backgroundColor: colors.surface }}>
                <Image source={{ uri }} style={styles.carouselImage} contentFit="contain" />
              </View>
            ))}
          </ScrollView>

          {/* Dots Indicator */}
          {allImages.length > 1 && (
            <View style={styles.dotsRow}>
              {allImages.map((_, idx) => (
                <View
                  key={idx}
                  style={[
                    styles.dot,
                    {
                      backgroundColor: idx === selectedImageIndex ? colors.primary : colors.border,
                      width: idx === selectedImageIndex ? 18 : 6,
                    },
                  ]}
                />
              ))}
            </View>
          )}

          {/* Discount Tag */}
          {hasDiscount && (
            <View style={[styles.discountTag, { backgroundColor: colors.danger }]}>
              <Text style={styles.discountTagText}>SAVE {discountPercent}%</Text>
            </View>
          )}
        </View>

        {/* 2. Product Details */}
        <View style={[styles.detailsCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
          <Text style={[styles.productTitle, { color: colors.text }]}>{product.name}</Text>

          {/* Pricing */}
          <View style={styles.priceRow}>
            <Text style={[styles.priceText, { color: colors.primary }]}>
              {Config.CURRENCY}{basePrice.toLocaleString()}
            </Text>
            {hasDiscount && (
              <Text style={[styles.comparePriceText, { color: colors.textMuted }]}>
                {Config.CURRENCY}{comparePrice.toLocaleString()}
              </Text>
            )}
            <View style={[styles.stockBadge, { backgroundColor: colors.primaryLight }]}>
              <Text style={[styles.stockText, { color: colors.primary }]}>In Stock (Cash on Delivery)</Text>
            </View>
          </View>

          {/* Reviews Rating summary */}
          {reviews.length > 0 && (
            <View style={styles.reviewSummary}>
              <Ionicons name="star" size={16} color="#eab308" />
              <Text style={[styles.reviewRating, { color: colors.text }]}>5.0</Text>
              <Text style={[styles.reviewCount, { color: colors.textSecondary }]}>
                ({reviews.length} Customer Reviews)
              </Text>
            </View>
          )}

          <View style={[styles.divider, { backgroundColor: colors.border }]} />

          {/* Bundle Offers (If enabled) */}
          {product.bundle_settings?.enabled && product.bundle_settings.tiers && (
            <View style={styles.bundleSection}>
              <Text style={[styles.sectionHeading, { color: colors.text }]}>
                {product.bundle_settings.heading || 'Special Bundle Offers'}
              </Text>
              <Text style={[styles.sectionSub, { color: colors.textSecondary }]}>
                {product.bundle_settings.subtitle || 'Buy more, save more!'}
              </Text>

              <View style={styles.bundleGrid}>
                {product.bundle_settings.tiers.map((tier, idx) => {
                  const isSelected = selectedTier?.quantity === tier.quantity;
                  return (
                    <Pressable
                      key={idx}
                      onPress={() => {
                        setSelectedTier(tier);
                        setQuantity(tier.quantity);
                      }}
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
                      <Text style={[styles.tierTitle, { color: colors.text }]}>{tier.title}</Text>
                      <Text style={[styles.tierQty, { color: colors.primary }]}>
                        Qty: {tier.quantity} {tier.quantity > 1 ? 'pcs' : 'pc'}
                      </Text>
                    </Pressable>
                  );
                })}
              </View>
            </View>
          )}

          {/* Variations (If available) */}
          {product.variations && product.variations.length > 0 && (
            <View style={styles.variationSection}>
              <Text style={[styles.sectionHeading, { color: colors.text }]}>Select Variation:</Text>
              <View style={styles.variationRow}>
                {product.variations.map((v) => {
                  const isSelected = selectedVariation?.id === v.id;
                  return (
                    <Pressable
                      key={v.id}
                      onPress={() => setSelectedVariation(v)}
                      style={[
                        styles.variationPill,
                        {
                          backgroundColor: isSelected ? colors.primary : colors.backgroundElement,
                          borderColor: isSelected ? colors.primary : colors.border,
                        },
                      ]}>
                      <Text
                        style={[
                          styles.variationText,
                          { color: isSelected ? '#ffffff' : colors.text },
                        ]}>
                        {v.name}
                      </Text>
                    </Pressable>
                  );
                })}
              </View>
            </View>
          )}
        </View>

        {/* 3. Image-Text Feature Highlights */}
        {product.image_text_sections && product.image_text_sections.length > 0 && (
          <View style={styles.featureSection}>
            {product.image_text_sections.map((section, idx) => (
              <View
                key={idx}
                style={[styles.featureCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
                {section.image_url ? (
                  <Image source={{ uri: section.image_url }} style={styles.featureImage} contentFit="cover" />
                ) : null}
                <View style={styles.featureContent}>
                  {section.subtitle && (
                    <Text style={[styles.featureSubtitle, { color: colors.primary }]}>{section.subtitle}</Text>
                  )}
                  <Text style={[styles.featureTitle, { color: colors.text }]}>{section.title}</Text>
                  <Text style={[styles.featureText, { color: colors.textSecondary }]}>
                    {section.content.replace(/<[^>]*>?/gm, '')}
                  </Text>
                </View>
              </View>
            ))}
          </View>
        )}

        {/* 4. Customer Reviews List */}
        {reviews.length > 0 && (
          <View style={[styles.reviewsSection, { backgroundColor: colors.surface, borderColor: colors.border }]}>
            <Text style={[styles.sectionHeading, { color: colors.text }]}>Customer Reviews</Text>
            {reviews.slice(0, 5).map((rev) => (
              <View key={rev.id} style={[styles.reviewItem, { borderBottomColor: colors.border }]}>
                <View style={styles.reviewHeader}>
                  <Text style={[styles.reviewerName, { color: colors.text }]}>{rev.reviewer_name}</Text>
                  <View style={styles.stars}>
                    {[...Array(rev.rating)].map((_, i) => (
                      <Ionicons key={i} name="star" size={14} color="#eab308" />
                    ))}
                  </View>
                </View>
                <Text style={[styles.reviewComment, { color: colors.textSecondary }]}>{rev.comment}</Text>
              </View>
            ))}
          </View>
        )}
      </ScrollView>

      {/* Sticky Bottom Bar */}
      <View style={[styles.bottomBar, { backgroundColor: colors.surface, borderTopColor: colors.border }]}>
        <View style={styles.bottomPrice}>
          <Text style={[styles.bottomLabel, { color: colors.textSecondary }]}>Total Price</Text>
          <Text style={[styles.bottomTotal, { color: colors.primary }]}>
            {Config.CURRENCY}{(basePrice * quantity).toLocaleString()}
          </Text>
        </View>

        <View style={styles.bottomActions}>
          <Pressable onPress={handleAddToCart} style={[styles.cartBtn, { borderColor: colors.primary }]}>
            <Ionicons name="cart-outline" size={18} color={colors.primary} />
          </Pressable>

          <Pressable onPress={handleBuyNow} style={[styles.buyNowBtn, { backgroundColor: colors.primary }]}>
            <Text style={styles.buyNowText}>Order Now (COD)</Text>
          </Pressable>
        </View>
      </View>
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
  headerBtn: {
    padding: 4,
  },
  headerTitle: {
    flex: 1,
    fontSize: 16,
    fontWeight: '800',
    marginHorizontal: Spacing.two,
  },
  scrollContent: {
    paddingBottom: 100,
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
  backBtn: {
    paddingHorizontal: Spacing.four,
    paddingVertical: Spacing.two,
    borderRadius: 10,
    marginTop: Spacing.two,
  },
  backBtnText: {
    color: '#ffffff',
    fontWeight: '700',
  },
  carouselContainer: {
    position: 'relative',
    height: 320,
  },
  carouselImage: {
    width: '100%',
    height: '100%',
  },
  dotsRow: {
    position: 'absolute',
    bottom: 12,
    alignSelf: 'center',
    flexDirection: 'row',
    gap: 6,
  },
  dot: {
    height: 6,
    borderRadius: 3,
  },
  discountTag: {
    position: 'absolute',
    top: 14,
    left: 14,
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
  },
  discountTagText: {
    color: '#ffffff',
    fontSize: 11,
    fontWeight: '800',
  },
  detailsCard: {
    padding: Spacing.four,
    marginHorizontal: Spacing.four,
    marginTop: Spacing.three,
    borderRadius: 16,
    borderWidth: 1,
  },
  productTitle: {
    fontSize: 18,
    fontWeight: '800',
    lineHeight: 24,
  },
  priceRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginTop: Spacing.two,
    flexWrap: 'wrap',
  },
  priceText: {
    fontSize: 22,
    fontWeight: '900',
  },
  comparePriceText: {
    fontSize: 15,
    textDecorationLine: 'line-through',
  },
  stockBadge: {
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
  },
  stockText: {
    fontSize: 11,
    fontWeight: '700',
  },
  reviewSummary: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    marginTop: Spacing.two,
  },
  reviewRating: {
    fontSize: 13,
    fontWeight: '800',
  },
  reviewCount: {
    fontSize: 12,
  },
  divider: {
    height: 1,
    marginVertical: Spacing.three,
  },
  bundleSection: {
    marginTop: Spacing.two,
  },
  sectionHeading: {
    fontSize: 15,
    fontWeight: '800',
    marginBottom: 2,
  },
  sectionSub: {
    fontSize: 12,
    marginBottom: Spacing.two,
  },
  bundleGrid: {
    gap: Spacing.two,
  },
  bundleCard: {
    padding: Spacing.three,
    borderRadius: 12,
    borderWidth: 1.5,
    position: 'relative',
  },
  tierBadge: {
    position: 'absolute',
    top: -8,
    right: 12,
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 6,
  },
  tierBadgeText: {
    color: '#ffffff',
    fontSize: 9,
    fontWeight: '800',
  },
  tierTitle: {
    fontSize: 13,
    fontWeight: '700',
  },
  tierQty: {
    fontSize: 12,
    fontWeight: '800',
    marginTop: 2,
  },
  variationSection: {
    marginTop: Spacing.three,
  },
  variationRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: Spacing.two,
    marginTop: Spacing.two,
  },
  variationPill: {
    paddingHorizontal: 14,
    paddingVertical: 7,
    borderRadius: 10,
    borderWidth: 1,
  },
  variationText: {
    fontSize: 13,
    fontWeight: '700',
  },
  featureSection: {
    paddingHorizontal: Spacing.four,
    marginTop: Spacing.four,
    gap: Spacing.three,
  },
  featureCard: {
    borderRadius: 16,
    borderWidth: 1,
    overflow: 'hidden',
  },
  featureImage: {
    width: '100%',
    height: 180,
  },
  featureContent: {
    padding: Spacing.four,
  },
  featureSubtitle: {
    fontSize: 11,
    fontWeight: '800',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    marginBottom: 4,
  },
  featureTitle: {
    fontSize: 16,
    fontWeight: '800',
    marginBottom: 6,
  },
  featureText: {
    fontSize: 13,
    lineHeight: 19,
  },
  reviewsSection: {
    padding: Spacing.four,
    marginHorizontal: Spacing.four,
    marginTop: Spacing.four,
    borderRadius: 16,
    borderWidth: 1,
  },
  reviewItem: {
    paddingVertical: Spacing.two,
    borderBottomWidth: StyleSheet.hairlineWidth,
  },
  reviewHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 4,
  },
  reviewerName: {
    fontSize: 13,
    fontWeight: '700',
  },
  stars: {
    flexDirection: 'row',
    gap: 2,
  },
  reviewComment: {
    fontSize: 12,
    lineHeight: 18,
  },
  bottomBar: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: Spacing.four,
    paddingVertical: Spacing.three,
    borderTopWidth: StyleSheet.hairlineWidth,
  },
  bottomPrice: {
    justifyContent: 'center',
  },
  bottomLabel: {
    fontSize: 11,
  },
  bottomTotal: {
    fontSize: 18,
    fontWeight: '900',
  },
  bottomActions: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.two,
  },
  cartBtn: {
    width: 44,
    height: 44,
    borderRadius: 12,
    borderWidth: 1.5,
    alignItems: 'center',
    justifyContent: 'center',
  },
  buyNowBtn: {
    paddingHorizontal: Spacing.four,
    height: 44,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
  buyNowText: {
    color: '#ffffff',
    fontSize: 14,
    fontWeight: '800',
  },
});
