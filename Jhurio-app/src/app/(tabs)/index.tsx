import { Ionicons } from '@expo/vector-icons';
import { Image } from 'expo-image';
import { useRouter } from 'expo-router';
import React, { useEffect, useRef, useState } from 'react';
import {
  Animated,
  Dimensions,
  NativeScrollEvent,
  NativeSyntheticEvent,
  Pressable,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  useColorScheme,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { ProductCard } from '@/components/ProductCard';
import { Config } from '@/constants/config';
import { BottomTabInset, Colors, Radius, Shadow, Spacing } from '@/constants/theme';
import { useCart } from '@/context/CartContext';
import { ApiService } from '@/services/api';
import { Banner, Category, Product } from '@/types';

const { width: SW } = Dimensions.get('window');

const CATEGORY_ICONS: Record<string, string> = {
  electronics: '⚡', fashion: '👗', kitchen: '🍳', health: '💊',
  sports: '⚽', beauty: '💄', gadgets: '📱', home: '🏠', default: '🛍️',
};

function getCategoryIcon(slug: string): string {
  const key = slug.toLowerCase();
  for (const k in CATEGORY_ICONS) {
    if (key.includes(k)) return CATEGORY_ICONS[k];
  }
  return CATEGORY_ICONS.default;
}

function SkeletonBox({ width, height, borderRadius = 10 }: { width: number | string; height: number; borderRadius?: number }) {
  const anim = useRef(new Animated.Value(0.4)).current;
  const scheme = useColorScheme();
  const colors = Colors[scheme === 'dark' ? 'dark' : 'light'];
  useEffect(() => {
    Animated.loop(Animated.sequence([
      Animated.timing(anim, { toValue: 1, duration: 700, useNativeDriver: true }),
      Animated.timing(anim, { toValue: 0.4, duration: 700, useNativeDriver: true }),
    ])).start();
  }, [anim]);
  return <Animated.View style={{ width: width as any, height, borderRadius, backgroundColor: colors.skeleton, opacity: anim }} />;
}

function HomeSkeleton() {
  return (
    <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={{ paddingBottom: 100 }}>
      <View style={{ marginHorizontal: 16, marginTop: 12, marginBottom: 16 }}>
        <SkeletonBox width="100%" height={190} borderRadius={20} />
      </View>
      <View style={{ flexDirection: 'row', gap: 10, paddingHorizontal: 16, marginBottom: 16 }}>
        {[1, 2, 3, 4].map((i) => <SkeletonBox key={i} width={72} height={82} borderRadius={16} />)}
      </View>
      <View style={{ flexDirection: 'row', flexWrap: 'wrap', paddingHorizontal: 10 }}>
        {[1, 2, 3, 4].map((i) => (
          <View key={i} style={{ width: '50%', padding: 6 }}>
            <SkeletonBox width="100%" height={190} borderRadius={16} />
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
  const { itemCount } = useCart();

  const [banners, setBanners] = useState<Banner[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [featuredProducts, setFeaturedProducts] = useState<Product[]>([]);
  const [latestProducts, setLatestProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [activeBannerIndex, setActiveBannerIndex] = useState(0);

  const bannerRef = useRef<any>(null);

  const loadData = async () => {
    try {
      const [bannersRes, catsRes, featRes, latRes] = await Promise.allSettled([
        ApiService.getBanners('slider'),
        ApiService.getCategories(10),
        ApiService.getProducts({ is_featured: true, limit: 8 }),
        ApiService.getProducts({ limit: 20 }),
      ]);
      if (bannersRes.status === 'fulfilled') setBanners(bannersRes.value);
      if (catsRes.status === 'fulfilled') setCategories(catsRes.value);
      if (featRes.status === 'fulfilled') setFeaturedProducts(featRes.value.products || []);
      if (latRes.status === 'fulfilled') setLatestProducts(latRes.value.products || []);
    } catch {}
    finally { setLoading(false); setRefreshing(false); }
  };

  useEffect(() => { loadData(); ApiService.trackEvent('page_view', { page: 'home' }); }, []);

  useEffect(() => {
    if (banners.length <= 1) return;
    const bannerW = SW - 32;
    const timer = setInterval(() => {
      setActiveBannerIndex((prev) => {
        const next = (prev + 1) % banners.length;
        bannerRef.current?.scrollTo({ x: next * (bannerW + 10), animated: true });
        return next;
      });
    }, 3800);
    return () => clearInterval(timer);
  }, [banners.length]);

  const handleBannerScroll = (e: NativeSyntheticEvent<NativeScrollEvent>) => {
    const bannerW = SW - 32;
    const idx = Math.round(e.nativeEvent.contentOffset.x / (bannerW + 10));
    setActiveBannerIndex(idx);
  };

  const handleSearch = () => {
    if (searchQuery.trim()) {
      router.push({ pathname: '/explore', params: { search: searchQuery.trim() } } as any);
    }
  };

  const isDark = scheme === 'dark';

  return (
    <SafeAreaView style={[s.container, { backgroundColor: colors.background }]} edges={['top']}>
      {/* ── Header ── */}
      <View style={[s.header, { backgroundColor: colors.surface, borderBottomColor: colors.border }]}>
        <View style={s.headerLeft}>
          <View style={[s.logo, { backgroundColor: colors.primary }]}>
            <Text style={s.logoText}>J</Text>
          </View>
          <View>
            <Text style={[s.appName, { color: colors.text }]}>Jhurio</Text>
            <Text style={[s.tagline, { color: colors.textMuted }]}>Shop Smart 🛍️</Text>
          </View>
        </View>
        <View style={s.headerRight}>
          <Pressable
            onPress={() => router.push('/cart')}
            style={[s.iconBtn, { backgroundColor: colors.backgroundElement }]}>
            <Ionicons name="cart-outline" size={22} color={colors.text} />
            {itemCount > 0 && (
              <View style={[s.badge, { backgroundColor: colors.danger }]}>
                <Text style={s.badgeText}>{itemCount > 9 ? '9+' : itemCount}</Text>
              </View>
            )}
          </Pressable>
        </View>
      </View>

      {/* ── Search Bar ── */}
      <View style={[s.searchWrap, { backgroundColor: colors.surface, borderBottomColor: colors.border }]}>
        <Pressable
          onPress={handleSearch}
          style={[s.searchBar, { backgroundColor: colors.backgroundElement, borderColor: colors.border }]}>
          <Ionicons name="search-outline" size={18} color={colors.textMuted} />
          <TextInput
            value={searchQuery}
            onChangeText={setSearchQuery}
            onSubmitEditing={handleSearch}
            placeholder="Search products..."
            placeholderTextColor={colors.textMuted}
            returnKeyType="search"
            style={[s.searchInput, { color: colors.text }]}
          />
          {searchQuery.length > 0 && (
            <Pressable onPress={() => setSearchQuery('')} hitSlop={8}>
              <Ionicons name="close-circle" size={16} color={colors.textMuted} />
            </Pressable>
          )}
        </Pressable>
        <Pressable
          onPress={() => router.push('/explore')}
          style={[s.filterBtn, { backgroundColor: colors.primaryLight }]}>
          <Ionicons name="options-outline" size={20} color={colors.primary} />
        </Pressable>
      </View>

      {loading ? (
        <HomeSkeleton />
      ) : (
        <ScrollView
          showsVerticalScrollIndicator={false}
          contentContainerStyle={[s.scroll, { paddingBottom: BottomTabInset + 32 }]}
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={() => { setRefreshing(true); loadData(); }}
              colors={[colors.primary]}
              tintColor={colors.primary}
            />
          }>

          {/* ── Hero Banners ── */}
          {banners.length > 0 && (
            <View style={s.bannerSection}>
              <ScrollView
                ref={bannerRef}
                horizontal
                pagingEnabled={false}
                showsHorizontalScrollIndicator={false}
                snapToInterval={SW - 32 + 10}
                decelerationRate="fast"
                onScroll={handleBannerScroll}
                scrollEventThrottle={16}
                contentContainerStyle={{ gap: 10 }}>
                {banners.map((banner) => {
                  const imgUri = banner.media?.url || banner.image_url;
                  if (!imgUri) return null;
                  return (
                    <Pressable
                      key={banner.id}
                      onPress={() => banner.link_url && router.push(banner.link_url as any)}
                      style={[s.bannerCard, { width: SW - 32, ...Shadow.md }]}>
                      <Image source={{ uri: imgUri }} style={s.bannerImage} contentFit="cover" transition={300} />
                      <View style={s.bannerGradient} />
                    </Pressable>
                  );
                })}
              </ScrollView>
              {banners.length > 1 && (
                <View style={s.dotsRow}>
                  {banners.map((_, i) => (
                    <View
                      key={i}
                      style={[
                        s.dot,
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

          {/* ── Promo Strip ── */}
          <View style={[s.promoStrip, { backgroundColor: colors.surface, borderColor: colors.border }]}>
            {[
              { icon: '🚚', text: 'Free Delivery', sub: '৳1000+' },
              { icon: '↩️', text: 'Easy Return', sub: '7 days' },
              { icon: '🔒', text: 'Secure Pay', sub: '100% Safe' },
            ].map((item, i) => (
              <View key={i} style={[s.promoItem, i < 2 && { borderRightWidth: 1, borderRightColor: colors.border }]}>
                <Text style={s.promoEmoji}>{item.icon}</Text>
                <Text style={[s.promoText, { color: colors.text }]}>{item.text}</Text>
                <Text style={[s.promoSub, { color: colors.textMuted }]}>{item.sub}</Text>
              </View>
            ))}
          </View>

          {/* ── Categories ── */}
          {categories.length > 0 && (
            <View style={s.section}>
              <View style={s.sectionHeader}>
                <Text style={[s.sectionTitle, { color: colors.text }]}>Categories</Text>
                <Pressable onPress={() => router.push('/explore')} style={s.seeAll}>
                  <Text style={[s.seeAllText, { color: colors.primary }]}>See All</Text>
                  <Ionicons name="chevron-forward" size={14} color={colors.primary} />
                </Pressable>
              </View>
              <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ gap: 10 }}>
                {categories.map((cat) => (
                  <Pressable
                    key={cat.id}
                    onPress={() => router.push({ pathname: '/explore', params: { category: cat.slug } } as any)}
                    style={({ pressed }) => [s.catCard, { backgroundColor: colors.surface, borderColor: colors.border, ...Shadow.sm, opacity: pressed ? 0.8 : 1 }]}>
                    <View style={[s.catIcon, { backgroundColor: colors.primaryLight }]}>
                      <Text style={{ fontSize: 24 }}>{getCategoryIcon(cat.slug)}</Text>
                    </View>
                    <Text numberOfLines={2} style={[s.catName, { color: colors.text }]}>{cat.name}</Text>
                  </Pressable>
                ))}
              </ScrollView>
            </View>
          )}

          {/* ── Hot Deals ── */}
          {featuredProducts.length > 0 && (
            <View style={s.section}>
              <View style={s.sectionHeader}>
                <View style={s.sectionTitleRow}>
                  <View style={[s.hotBadge, { backgroundColor: colors.danger }]}>
                    <Text style={s.hotBadgeText}>🔥 HOT</Text>
                  </View>
                  <Text style={[s.sectionTitle, { color: colors.text }]}>Top Deals</Text>
                </View>
                <Pressable onPress={() => router.push({ pathname: '/explore', params: { is_featured: '1' } } as any)} style={s.seeAll}>
                  <Text style={[s.seeAllText, { color: colors.primary }]}>See All</Text>
                  <Ionicons name="chevron-forward" size={14} color={colors.primary} />
                </Pressable>
              </View>
              <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ gap: 12 }}>
                {featuredProducts.map((p) => {
                  const price = Number(p.price);
                  const compare = p.compare_at_price ? Number(p.compare_at_price) : null;
                  const disc = compare && compare > price ? Math.round(((compare - price) / compare) * 100) : 0;
                  const imgUrl = p.thumbnail?.url || '';
                  return (
                    <Pressable
                      key={p.id}
                      onPress={() => router.push(`/product/${p.slug || p.id}` as any)}
                      style={({ pressed }) => [s.dealCard, { backgroundColor: colors.surface, borderColor: colors.border, ...Shadow.md, opacity: pressed ? 0.9 : 1 }]}>
                      <View style={[s.dealImgWrap, { backgroundColor: colors.backgroundElement }]}>
                        {imgUrl ? <Image source={{ uri: imgUrl }} style={s.dealImg} contentFit="cover" transition={200} /> : null}
                        {disc > 0 && (
                          <View style={[s.discBadge, { backgroundColor: colors.danger }]}>
                            <Text style={s.discBadgeText}>-{disc}%</Text>
                          </View>
                        )}
                      </View>
                      <View style={{ padding: 10, gap: 4 }}>
                        <Text numberOfLines={2} style={[s.dealTitle, { color: colors.text }]}>{p.name}</Text>
                        <Text style={[s.dealPrice, { color: colors.primary }]}>{Config.CURRENCY}{price.toLocaleString()}</Text>
                        {compare && compare > price && (
                          <Text style={[s.dealCompare, { color: colors.textMuted }]}>{Config.CURRENCY}{compare.toLocaleString()}</Text>
                        )}
                      </View>
                    </Pressable>
                  );
                })}
              </ScrollView>
            </View>
          )}

          {/* ── New Arrivals Grid ── */}
          <View style={s.section}>
            <View style={s.sectionHeader}>
              <View style={s.sectionTitleRow}>
                <Ionicons name="sparkles" size={18} color={colors.accent} />
                <Text style={[s.sectionTitle, { color: colors.text }]}>New Arrivals</Text>
              </View>
              <Pressable onPress={() => router.push('/explore')} style={s.seeAll}>
                <Text style={[s.seeAllText, { color: colors.primary }]}>Browse All</Text>
                <Ionicons name="chevron-forward" size={14} color={colors.primary} />
              </Pressable>
            </View>
            {latestProducts.length === 0 ? (
              <View style={s.emptySection}>
                <Text style={[{ color: colors.textMuted, fontSize: 14 }]}>No products available</Text>
              </View>
            ) : (
              <View style={s.grid}>
                {latestProducts.map((p) => (
                  <View key={p.id} style={s.gridCol}>
                    <ProductCard product={p} />
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

const s = StyleSheet.create({
  container: { flex: 1 },
  // Header
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderBottomWidth: StyleSheet.hairlineWidth,
  },
  headerLeft: { flexDirection: 'row', alignItems: 'center', gap: 10 },
  headerRight: { flexDirection: 'row', gap: 8 },
  logo: {
    width: 38,
    height: 38,
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
  },
  logoText: { color: '#fff', fontSize: 18, fontWeight: '900' },
  appName: { fontSize: 17, fontWeight: '800', letterSpacing: -0.3 },
  tagline: { fontSize: 11, marginTop: 1 },
  iconBtn: {
    width: 40,
    height: 40,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
    position: 'relative',
  },
  badge: {
    position: 'absolute',
    top: -4,
    right: -4,
    minWidth: 17,
    height: 17,
    borderRadius: 9,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 4,
    borderWidth: 1.5,
    borderColor: '#fff',
  },
  badgeText: { color: '#fff', fontSize: 8, fontWeight: '800' },
  // Search
  searchWrap: {
    flexDirection: 'row',
    gap: 10,
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderBottomWidth: StyleSheet.hairlineWidth,
  },
  searchBar: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    borderRadius: 14,
    paddingHorizontal: 14,
    height: 44,
    borderWidth: 1,
  },
  searchInput: { flex: 1, fontSize: 14, fontWeight: '500', padding: 0 },
  filterBtn: {
    width: 44,
    height: 44,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
  },
  scroll: { paddingTop: 12 },
  // Banner
  bannerSection: { paddingHorizontal: 16, marginBottom: 16 },
  bannerCard: {
    height: 190,
    borderRadius: 20,
    overflow: 'hidden',
  },
  bannerImage: { width: '100%', height: '100%' },
  bannerGradient: {
    ...StyleSheet.absoluteFill as any,
    backgroundColor: 'rgba(0,0,0,0.12)',
  },
  dotsRow: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: 10,
    gap: 5,
  },
  dot: { height: 6, borderRadius: 3 },
  // Promo
  promoStrip: {
    flexDirection: 'row',
    marginHorizontal: 16,
    borderRadius: 16,
    borderWidth: StyleSheet.hairlineWidth,
    marginBottom: 16,
    overflow: 'hidden',
  },
  promoItem: {
    flex: 1,
    alignItems: 'center',
    paddingVertical: 12,
    gap: 3,
  },
  promoEmoji: { fontSize: 20 },
  promoText: { fontSize: 10, fontWeight: '700', textAlign: 'center' },
  promoSub: { fontSize: 9, textAlign: 'center' },
  // Section
  section: { paddingHorizontal: 16, marginBottom: 20 },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  sectionTitleRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  sectionTitle: { fontSize: 17, fontWeight: '800', letterSpacing: -0.3 },
  hotBadge: {
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
  },
  hotBadgeText: { color: '#fff', fontSize: 10, fontWeight: '800' },
  seeAll: { flexDirection: 'row', alignItems: 'center', gap: 2 },
  seeAllText: { fontSize: 13, fontWeight: '700' },
  // Categories
  catCard: {
    width: 74,
    alignItems: 'center',
    padding: 10,
    borderRadius: 16,
    borderWidth: StyleSheet.hairlineWidth,
    gap: 6,
  },
  catIcon: {
    width: 46,
    height: 46,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
  },
  catName: { fontSize: 10, fontWeight: '600', textAlign: 'center', lineHeight: 13 },
  // Deal cards
  dealCard: {
    width: 155,
    borderRadius: 16,
    overflow: 'hidden',
    borderWidth: StyleSheet.hairlineWidth,
  },
  dealImgWrap: { width: '100%', height: 135, position: 'relative' },
  dealImg: { width: '100%', height: '100%' },
  discBadge: {
    position: 'absolute',
    top: 6,
    left: 6,
    paddingHorizontal: 7,
    paddingVertical: 2,
    borderRadius: 6,
  },
  discBadgeText: { color: '#fff', fontSize: 10, fontWeight: '800' },
  dealTitle: { fontSize: 12, fontWeight: '600', lineHeight: 16, minHeight: 32 },
  dealPrice: { fontSize: 14, fontWeight: '800' },
  dealCompare: { fontSize: 11, textDecorationLine: 'line-through' },
  // Grid
  grid: { flexDirection: 'row', flexWrap: 'wrap', marginHorizontal: -6 },
  gridCol: { width: '50%', paddingHorizontal: 6, marginBottom: 2 },
  emptySection: { paddingVertical: 24, alignItems: 'center' },
});
