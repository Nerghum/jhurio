import { Ionicons } from '@expo/vector-icons';
import { Image } from 'expo-image';
import { useRouter } from 'expo-router';
import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Dimensions,
  FlatList,
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
import { BottomTabInset, Colors, Spacing } from '@/constants/theme';
import { ApiService } from '@/services/api';
import { Banner, Category, Product } from '@/types';

const { width } = Dimensions.get('window');

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

  const handleRefresh = () => {
    setRefreshing(true);
    loadData();
  };

  const handleSearchSubmit = () => {
    if (searchQuery.trim()) {
      router.push({
        pathname: '/explore',
        params: { search: searchQuery.trim() },
      } as any);
    }
  };

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['top']}>
      {/* Top Header */}
      <Header
        searchQuery={searchQuery}
        onSearchChange={setSearchQuery}
        onSubmitSearch={handleSearchSubmit}
      />

      {loading ? (
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={colors.primary} />
          <Text style={[styles.loadingText, { color: colors.textSecondary }]}>
            Loading Jhurio store...
          </Text>
        </View>
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
          {/* 1. Hero Sliders Carousel */}
          {banners.length > 0 && (
            <View style={styles.sliderSection}>
              <ScrollView
                horizontal
                pagingEnabled
                showsHorizontalScrollIndicator={false}
                style={styles.bannerScroll}>
                {banners.map((banner) => {
                  const imgUri = banner.media?.url || banner.image_url;
                  if (!imgUri) return null;
                  return (
                    <Pressable
                      key={banner.id}
                      onPress={() => banner.link_url && router.push(banner.link_url as any)}
                      style={[styles.bannerCard, { width: width - Spacing.four * 2 }]}>
                      <Image
                        source={{ uri: imgUri }}
                        style={styles.bannerImage}
                        contentFit="cover"
                        transition={300}
                      />
                    </Pressable>
                  );
                })}
              </ScrollView>
            </View>
          )}

          {/* 2. Popular Categories */}
          {categories.length > 0 && (
            <View style={styles.section}>
              <View style={styles.sectionHeader}>
                <Text style={[styles.sectionTitle, { color: colors.text }]}>Categories</Text>
                <Pressable onPress={() => router.push('/explore')}>
                  <Text style={[styles.viewAllText, { color: colors.primary }]}>View All</Text>
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
                    style={[styles.categoryPill, { backgroundColor: colors.surface, borderColor: colors.border }]}>
                    <View style={[styles.categoryIconWrap, { backgroundColor: colors.primaryLight }]}>
                      <Ionicons name="grid" size={16} color={colors.primary} />
                    </View>
                    <Text numberOfLines={1} style={[styles.categoryName, { color: colors.text }]}>
                      {cat.name}
                    </Text>
                  </Pressable>
                ))}
              </ScrollView>
            </View>
          )}

          {/* 3. Featured / Trending Products */}
          {featuredProducts.length > 0 && (
            <View style={styles.section}>
              <View style={styles.sectionHeader}>
                <View style={styles.titleRow}>
                  <Ionicons name="flame" size={20} color={colors.accent} />
                  <Text style={[styles.sectionTitle, { color: colors.text }]}>Hot Deals</Text>
                </View>
                <Pressable onPress={() => router.push({ pathname: '/explore', params: { is_featured: '1' } } as any)}>
                  <Text style={[styles.viewAllText, { color: colors.primary }]}>See More</Text>
                </Pressable>
              </View>

              <View style={styles.grid}>
                {featuredProducts.map((product) => (
                  <View key={`featured-${product.id}`} style={styles.gridCol}>
                    <ProductCard product={product} />
                  </View>
                ))}
              </View>
            </View>
          )}

          {/* 4. Latest Products */}
          <View style={styles.section}>
            <View style={styles.sectionHeader}>
              <View style={styles.titleRow}>
                <Ionicons name="sparkles" size={18} color={colors.primary} />
                <Text style={[styles.sectionTitle, { color: colors.text }]}>Latest Products</Text>
              </View>
              <Pressable onPress={() => router.push('/explore')}>
                <Text style={[styles.viewAllText, { color: colors.primary }]}>Browse Shop</Text>
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
  container: {
    flex: 1,
  },
  scrollContent: {
    paddingTop: Spacing.two,
  },
  loadingContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: Spacing.two,
  },
  loadingText: {
    fontSize: 14,
  },
  sliderSection: {
    paddingHorizontal: Spacing.four,
    marginBottom: Spacing.three,
  },
  bannerScroll: {
    borderRadius: 16,
    overflow: 'hidden',
  },
  bannerCard: {
    height: 160,
    borderRadius: 16,
    overflow: 'hidden',
  },
  bannerImage: {
    width: '100%',
    height: '100%',
  },
  section: {
    marginTop: Spacing.two,
    marginBottom: Spacing.two,
    paddingHorizontal: Spacing.four,
  },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: Spacing.two,
  },
  titleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '800',
  },
  viewAllText: {
    fontSize: 13,
    fontWeight: '700',
  },
  categoriesScroll: {
    gap: Spacing.two,
    paddingVertical: 4,
  },
  categoryPill: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: Spacing.two,
    paddingVertical: 6,
    borderRadius: 20,
    borderWidth: 1,
    gap: 6,
  },
  categoryIconWrap: {
    width: 26,
    height: 26,
    borderRadius: 13,
    alignItems: 'center',
    justifyContent: 'center',
  },
  categoryName: {
    fontSize: 13,
    fontWeight: '600',
  },
  grid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginHorizontal: -Spacing.one,
  },
  gridCol: {
    width: '50%',
  },
  emptyContainer: {
    padding: Spacing.four,
    alignItems: 'center',
  },
  emptyText: {
    fontSize: 14,
  },
});
