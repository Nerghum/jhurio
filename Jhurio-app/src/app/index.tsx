import { Ionicons } from '@expo/vector-icons';
import { Image } from 'expo-image';
import { useRouter } from 'expo-router';
import React, { useEffect, useRef, useState } from 'react';
import {
  ActivityIndicator,
  Animated,
  Dimensions,
  FlatList,
  NativeScrollEvent,
  NativeSyntheticEvent,
  Pressable,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  useColorScheme,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { Header } from '@/components/Header';
import { ProductCard } from '@/components/ProductCard';
import { Config } from '@/constants/config';
import { BottomTabInset, Colors, Radius, Shadow, Spacing } from '@/constants/theme';
import { ApiService } from '@/services/api';
import { Banner, Category, Product } from '@/types';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

const CATEGORY_ICONS: Record<string, string> = {
  electronics: '⚡',
  fashion: '👗',
  kitchen: '🍳',
  health: '💊',
  sports: '⚽',
  beauty: '💄',
  gadgets: '📱',
  home: '🏠',
  default: '🛍️',
};

function getCategoryIcon(slug: string): string {
  const key = slug.toLowerCase();
  for (const k in CATEGORY_ICONS) {
    if (key.includes(k)) return CATEGORY_ICONS[k];
  }
  return CATEGORY_ICONS.default;
}

function SkeletonBox({ width, height, borderRadius = 8 }: { width: number | string; height: number; borderRadius?: number }) {
  const anim = useRef(new Animated.Value(0.4)).current;
  useEffect(() => {
    Animated.loop(
      Animated.sequence([
        Animated.timing(anim, { toValue: 1, duration: 700, useNativeDriver: true }),
        Animated.timing(anim, { toValue: 0.4, duration: 700, useNativeDriver: true }),
      ]),
    ).start();
  }, [anim]);
  const scheme = useColorScheme();
  const colors = Colors[scheme === 'dark' ? 'dark' : 'light'];
  return (
    <Animated.View
      style={{
        width: width as any,
        height,
        borderRadius,
        backgroundColor: colors.skeleton,
        opacity: anim,
      }}
    />
  );
}

function HomeSkeleton() {
  return (
    <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={{ paddingBottom: 100 }}>
      {/* Banner skeleton */}
      <View style={{ marginHorizontal: Spacing.three, marginTop: Spacing.two, marginBottom: Spacing.three }}>
        <SkeletonBox width="100%" height={180} borderRadius={Radius.xl} />
      </View>
      {/* Categories */}
      <View style={{ flexDirection: 'row', gap: Spacing.two, paddingHorizontal: Spacing.three, marginBottom: Spacing.three }}>
        {[1, 2, 3, 4].map((i) => (
          <SkeletonBox key={i} width={70} height={80} borderRadius={Radius.lg} />
        ))}
      </View>
      {/* Products */}
      <View style={{ flexDirection: 'row', flexWrap: 'wrap', paddingHorizontal: Spacing.two }}>
        {[1, 2, 3, 4].map((i) => (
          <View key={i} style={{ width: '50%', padding: 6 }}>
            <SkeletonBox width="100%" height={180} borderRadius={Radius.lg} />
            <View style={{ marginTop: 8, gap: 6 }}>
              <SkeletonBox width="80%" height={12} />
              <SkeletonBox width="50%" height={12} />
            </View>
          </View>
        ))}
      </View>
    </ScrollView>
  );
}

export default function HomeScreen() {
  const router = useRouter();
  const scheme = useColorScheme();
  const colors = Colors[scheme === 'dark' ? 'dark' : 'light'];

  const [banners, setBanners] = useState<Banner[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [featuredProducts, setFeaturedProducts] = useState<Product[]>([]);
  const [latestProducts, setLatestProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [activeBannerIndex, setActiveBannerIndex] = useState(0);

  const bannerScrollRef = useRef<ScrollView>(null);

  const loadData = async () => {
    try {
      const [bannersRes, categoriesRes, featuredRes, latestRes] = await Promise.allSettled([
        ApiService.getBanners('slider'),
        ApiService.getCategories(10),
        ApiService.getProducts({ is_featured: true, limit: 6 }),
        ApiService.getProducts({ limit: 20 }),
      ]);

      if (bannersRes.status === 'fulfilled') setBanners(bannersRes.value);
      if (categoriesRes.status === 'fulfilled') setCategories(categoriesRes.value);
      if (featuredRes.status === 'fulfilled') setFeaturedProducts(featuredRes.value.products || []);
      if (latestRes.status === 'fulfilled') setLatestProducts(latestRes.value.products || []);
    } catch (e) {
      console.warn('Failed to fetch home data', e);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    loadData();
    ApiService.trackEvent('page_view', { page: 'home' });
  }, []);

  // Auto-scroll banners
  useEffect(() => {
    if (banners.length <= 1) return;
    const timer = setInterval(() => {
      setActiveBannerIndex((prev) => {
        const next = (prev + 1) % banners.length;
        const bannerWidth = SCREEN_WIDTH - Spacing.three * 2;
        bannerScrollRef.current?.scrollTo({ x: next * (bannerWidth + Spacing.two), animated: true });
        return next;
      });
    }, 3500);
    return () => clearInterval(timer);
  }, [banners.length]);

  const handleBannerScroll = (e: NativeSyntheticEvent<NativeScrollEvent>) => {
    const bannerWidth = SCREEN_WIDTH - Spacing.three * 2;
    const index = Math.round(e.nativeEvent.contentOffset.x / (bannerWidth + Spacing.two));
    setActiveBannerIndex(index);
  };

  const handleRefresh = () => {
    setRefreshing(true);
    loadData();
  };

  const handleSearchSubmit = () => {
    if (searchQuery.trim()) {
      router.push({ pathname: '/explore', params: { search: searchQuery.trim() } } as any);
    }
  };

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['top']}>
      <Header
        searchQuery={searchQuery}
        onSearchChange={setSearchQuery}
        onSubmitSearch={handleSearchSubmit}
        showSearchBar={true}
      />

      {loading ? (
        <HomeSkeleton />
      ) : (
        <ScrollView
          showsVerticalScrollIndicator={false}
          contentContainerStyle={[styles.scrollContent, { paddingBottom: BottomTabInset + Spacing.five }]}
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={handleRefresh}
              colors={[colors.primary]}
              tintColor={colors.primary}
            />
          }>

          {/* ── Hero Banner ── */}
          {banners.length > 0 && (
            <View style={styles.bannerSection}>
              <ScrollView
                ref={bannerScrollRef}
                horizontal
                pagingEnabled={false}
                showsHorizontalScrollIndicator={false}
                snapToInterval={SCREEN_WIDTH - Spacing.three * 2 + Spacing.two}
                decelerationRate="fast"
                onScroll={handleBannerScroll}
                scrollEventThrottle={16}
                contentContainerStyle={{ gap: Spacing.two }}>
                {banners.map((banner) => {
                  const imgUri = banner.media?.url || banner.image_url;
                  if (!imgUri) return null;
                  return (
                    <Pressable
                      key={banner.id}
                      onPress={() => banner.link_url && router.push(banner.link_url as any)}
                      style={[styles.bannerCard, { width: SCREEN_WIDTH - Spacing.three * 2, ...Shadow.md }]}>
                      <Image
                        source={{ uri: imgUri }}
                        style={styles.bannerImage}
                        contentFit="cover"
                        transition={300}
                      />
                      <View style={styles.bannerOverlay} />
                    </Pressable>
                  );
                })}
              </ScrollView>

              {/* Dot indicators */}
              {banners.length > 1 && (
                <View style={styles.bannerDots}>
                  {banners.map((_, i) => (
                    <View
                      key={i}
                      style={[
                        styles.dot,
                        {
                          backgroundColor: i === activeBannerIndex ? colors.primary : colors.border,
                          width: i === activeBannerIndex ? 20 : 6,
                        },
                      ]}
                    />
                  ))}
                </View>
              )}
            </View>
          )}

          {/* ── Promo Chips ── */}
          <View style={styles.promoRow}>
            {[
              { icon: '🚚', label: 'Free Delivery', sub: 'On orders 1000+' },
              { icon: '↩️', label: 'Easy Return', sub: '7-day return' },
              { icon: '🔒', label: 'Secure Pay', sub: '100% Safe' },
            ].map((item, i) => (
              <View key={i} style={[styles.promoChip, { backgroundColor: colors.surface, borderColor: colors.border, ...Shadow.sm }]}>
                <Text style={styles.promoIcon}>{item.icon}</Text>
                <Text style={[styles.promoLabel, { color: colors.text }]}>{item.label}</Text>
                <Text style={[styles.promoSub, { color: colors.textMuted }]}>{item.sub}</Text>
              </View>
            ))}
          </View>

          {/* ── Categories ── */}
          {categories.length > 0 && (
            <View style={styles.section}>
              <View style={styles.sectionHeader}>
                <Text style={[styles.sectionTitle, { color: colors.text }]}>Categories</Text>
                <Pressable onPress={() => router.push('/explore')} style={styles.viewAllBtn}>
                  <Text style={[styles.viewAllText, { color: colors.primary }]}>See All</Text>
                  <Ionicons name="chevron-forward" size={14} color={colors.primary} />
                </Pressable>
              </View>

              <ScrollView
                horizontal
                showsHorizontalScrollIndicator={false}
                contentContainerStyle={styles.categoriesScroll}>
                {categories.map((cat) => (
                  <Pressable
                    key={cat.id}
                    onPress={() => router.push({ pathname: '/explore', params: { category: cat.slug } } as any)}
                    style={({ pressed }) => [
                      styles.categoryCard,
                      {
                        backgroundColor: colors.surface,
                        borderColor: colors.border,
                        ...Shadow.sm,
                        opacity: pressed ? 0.8 : 1,
                      },
                    ]}>
                    <View style={[styles.categoryIconWrap, { backgroundColor: colors.primaryLight }]}>
                      <Text style={styles.categoryEmoji}>{getCategoryIcon(cat.slug)}</Text>
                    </View>
                    <Text numberOfLines={2} style={[styles.categoryName, { color: colors.text }]}>
                      {cat.name}
                    </Text>
                  </Pressable>
                ))}
              </ScrollView>
            </View>
          )}

          {/* ── Hot Deals ── */}
          {featuredProducts.length > 0 && (
            <View style={styles.section}>
              <View style={styles.sectionHeader}>
                <View style={styles.titleRow}>
                  <View style={[styles.titlePill, { backgroundColor: colors.danger }]}>
                    <Text style={styles.titlePillText}>🔥 HOT</Text>
                  </View>
                  <Text style={[styles.sectionTitle, { color: colors.text }]}>Top Deals</Text>
                </View>
                <Pressable
                  onPress={() => router.push({ pathname: '/explore', params: { is_featured: '1' } } as any)}
                  style={styles.viewAllBtn}>
                  <Text style={[styles.viewAllText, { color: colors.primary }]}>See All</Text>
                  <Ionicons name="chevron-forward" size={14} color={colors.primary} />
                </Pressable>
              </View>

              {/* Horizontal hot deals */}
              <ScrollView
                horizontal
                showsHorizontalScrollIndicator={false}
                contentContainerStyle={styles.hotDealsScroll}>
                {featuredProducts.map((product) => {
                  const priceNum = Number(product.price);
                  const compareNum = product.compare_at_price ? Number(product.compare_at_price) : null;
                  const hasDiscount = compareNum && compareNum > priceNum;
                  const discountPercent = hasDiscount
                    ? Math.round(((compareNum - priceNum) / compareNum) * 100)
                    : 0;
                  const imageUrl = product.thumbnail?.url || '';

                  return (
                    <Pressable
                      key={product.id}
                      onPress={() => router.push(`/product/${product.slug || product.id}` as any)}
                      style={({ pressed }) => [
                        styles.hotDealCard,
                        { backgroundColor: colors.surface, borderColor: colors.border, ...Shadow.md, opacity: pressed ? 0.9 : 1 },
                      ]}>
                      <View style={[styles.hotDealImageWrap, { backgroundColor: colors.backgroundElement }]}>
                        {imageUrl ? (
                          <Image source={{ uri: imageUrl }} style={styles.hotDealImage} contentFit="cover" transition={200} />
                        ) : null}
                        {hasDiscount && (
                          <View style={[styles.hotDealBadge, { backgroundColor: colors.danger }]}>
                            <Text style={styles.hotDealBadgeText}>-{discountPercent}%</Text>
                          </View>
                        )}
                      </View>
                      <View style={styles.hotDealInfo}>
                        <Text numberOfLines={2} style={[styles.hotDealTitle, { color: colors.text }]}>
                          {product.name}
                        </Text>
                        <Text style={[styles.hotDealPrice, { color: colors.primary }]}>
                          {Config.CURRENCY}{priceNum.toLocaleString()}
                        </Text>
                        {hasDiscount && (
                          <Text style={[styles.hotDealCompare, { color: colors.textMuted }]}>
                            {Config.CURRENCY}{compareNum.toLocaleString()}
                          </Text>
                        )}
                      </View>
                    </Pressable>
                  );
                })}
              </ScrollView>
            </View>
          )}

          {/* ── Latest Products ── */}
          <View style={styles.section}>
            <View style={styles.sectionHeader}>
              <View style={styles.titleRow}>
                <Ionicons name="sparkles" size={18} color={colors.accent} />
                <Text style={[styles.sectionTitle, { color: colors.text }]}>New Arrivals</Text>
              </View>
              <Pressable onPress={() => router.push('/explore')} style={styles.viewAllBtn}>
                <Text style={[styles.viewAllText, { color: colors.primary }]}>Browse All</Text>
                <Ionicons name="chevron-forward" size={14} color={colors.primary} />
              </Pressable>
            </View>

            {latestProducts.length === 0 ? (
              <View style={styles.emptyContainer}>
                <Text style={[styles.emptyText, { color: colors.textSecondary }]}>
                  No products available right now.
                </Text>
              </View>
            ) : (
              <View style={styles.grid}>
                {latestProducts.map((product) => (
                  <View key={`latest-${product.id}`} style={styles.gridCol}>
                    <ProductCard product={product} />
                  </View>
                ))}
              </View>
            )}
          </View>
        </ScrollView>
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },
  scrollContent: { paddingTop: Spacing.two },

  // Banner
  bannerSection: {
    paddingHorizontal: Spacing.three,
    marginBottom: Spacing.three,
  },
  bannerCard: {
    height: 180,
    borderRadius: Radius.xl,
    overflow: 'hidden',
  },
  bannerImage: { width: '100%', height: '100%' },
  bannerOverlay: {
    ...StyleSheet.absoluteFillObject,
    background: 'linear-gradient(to bottom, transparent 50%, rgba(0,0,0,0.3))',
  },
  bannerDots: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: Spacing.two,
    gap: 4,
  },
  dot: {
    height: 6,
    borderRadius: 3,
    transition: 'width 0.3s',
  },

  // Promo
  promoRow: {
    flexDirection: 'row',
    paddingHorizontal: Spacing.three,
    gap: Spacing.two,
    marginBottom: Spacing.two,
  },
  promoChip: {
    flex: 1,
    alignItems: 'center',
    padding: Spacing.two,
    borderRadius: Radius.md,
    borderWidth: StyleSheet.hairlineWidth,
    gap: 2,
  },
  promoIcon: { fontSize: 18 },
  promoLabel: { fontSize: 10, fontWeight: '700', textAlign: 'center' },
  promoSub: { fontSize: 9, textAlign: 'center' },

  // Section
  section: {
    marginBottom: Spacing.three,
    paddingHorizontal: Spacing.three,
  },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: Spacing.two + 4,
  },
  titleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.one + 2,
  },
  titlePill: {
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
  },
  titlePillText: {
    color: '#fff',
    fontSize: 10,
    fontWeight: '800',
  },
  sectionTitle: {
    fontSize: 17,
    fontWeight: '800',
    letterSpacing: -0.3,
  },
  viewAllBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 2,
  },
  viewAllText: {
    fontSize: 13,
    fontWeight: '700',
  },

  // Categories
  categoriesScroll: {
    gap: Spacing.two,
    paddingBottom: 4,
  },
  categoryCard: {
    width: 72,
    alignItems: 'center',
    padding: Spacing.two,
    borderRadius: Radius.lg,
    borderWidth: StyleSheet.hairlineWidth,
    gap: Spacing.one,
  },
  categoryIconWrap: {
    width: 44,
    height: 44,
    borderRadius: Radius.lg,
    alignItems: 'center',
    justifyContent: 'center',
  },
  categoryEmoji: { fontSize: 22 },
  categoryName: {
    fontSize: 10,
    fontWeight: '600',
    textAlign: 'center',
    lineHeight: 13,
  },

  // Hot Deals
  hotDealsScroll: {
    gap: Spacing.two,
    paddingBottom: 4,
    paddingRight: 4,
  },
  hotDealCard: {
    width: 150,
    borderRadius: Radius.lg,
    overflow: 'hidden',
    borderWidth: StyleSheet.hairlineWidth,
  },
  hotDealImageWrap: {
    width: '100%',
    height: 130,
    position: 'relative',
  },
  hotDealImage: { width: '100%', height: '100%' },
  hotDealBadge: {
    position: 'absolute',
    top: 6,
    left: 6,
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 5,
  },
  hotDealBadgeText: {
    color: '#fff',
    fontSize: 10,
    fontWeight: '800',
  },
  hotDealInfo: {
    padding: Spacing.two + 2,
    gap: 3,
  },
  hotDealTitle: {
    fontSize: 12,
    fontWeight: '600',
    lineHeight: 16,
    minHeight: 32,
  },
  hotDealPrice: {
    fontSize: 14,
    fontWeight: '800',
  },
  hotDealCompare: {
    fontSize: 11,
    textDecorationLine: 'line-through',
  },

  // Grid
  grid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginHorizontal: -(Spacing.one + 2),
  },
  gridCol: { width: '50%' },
  emptyContainer: { padding: Spacing.four, alignItems: 'center' },
  emptyText: { fontSize: 14 },
});
