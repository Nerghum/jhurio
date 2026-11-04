import { Ionicons } from '@expo/vector-icons';
import { useLocalSearchParams } from 'expo-router';
import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Pressable,
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
import { Category, Product } from '@/types';

export default function ExploreScreen() {
  const params = useLocalSearchParams<{ search?: string; category?: string; is_featured?: string }>();
  const scheme = useColorScheme();
  const colors = Colors[scheme === 'dark' ? 'dark' : 'light'];

  const [searchQuery, setSearchQuery] = useState(params.search || '');
  const [selectedCategory, setSelectedCategory] = useState<string | null>(params.category || null);
  const [categories, setCategories] = useState<Category[]>([]);
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  const [sortBy, setSortBy] = useState<'latest' | 'price_low' | 'price_high'>('latest');

  // Load categories
  useEffect(() => {
    ApiService.getCategories().then(setCategories).catch(() => {});
  }, []);

  // Update from params if changed
  useEffect(() => {
    if (params.search !== undefined) setSearchQuery(params.search);
    if (params.category !== undefined) setSelectedCategory(params.category);
  }, [params.search, params.category]);

  // Load products based on query and category
  useEffect(() => {
    let isCancelled = false;
    setLoading(true);

    ApiService.getProducts({
      search: searchQuery.trim() || undefined,
      category: selectedCategory || undefined,
      is_featured: params.is_featured === '1' ? true : undefined,
      limit: 50,
    })
      .then((res) => {
        if (!isCancelled) {
          setProducts(res.products || []);
        }
      })
      .catch((e) => {
        console.warn('Failed to load products', e);
      })
      .finally(() => {
        if (!isCancelled) setLoading(false);
      });

    return () => {
      isCancelled = true;
    };
  }, [searchQuery, selectedCategory, params.is_featured]);

  // Sorting
  const sortedProducts = [...products].sort((a, b) => {
    const priceA = Number(a.price);
    const priceB = Number(b.price);
    if (sortBy === 'price_low') return priceA - priceB;
    if (sortBy === 'price_high') return priceB - priceA;
    return 0; // default latest
  });

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['top']}>
      {/* Header */}
      <Header
        searchQuery={searchQuery}
        onSearchChange={setSearchQuery}
        showSearchBar={true}
      />

      {/* Category Pills Filter */}
      <View style={[styles.filterBar, { backgroundColor: colors.surface, borderBottomColor: colors.border }]}>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.categoryChips}>
          <Pressable
            onPress={() => setSelectedCategory(null)}
            style={[
              styles.chip,
              {
                backgroundColor: selectedCategory === null ? colors.primary : colors.backgroundElement,
                borderColor: selectedCategory === null ? colors.primary : colors.border,
              },
            ]}>
            <Text
              style={[
                styles.chipText,
                { color: selectedCategory === null ? '#ffffff' : colors.text },
              ]}>
              All
            </Text>
          </Pressable>

          {categories.map((cat) => {
            const isSelected = selectedCategory === cat.slug;
            return (
              <Pressable
                key={cat.id}
                onPress={() => setSelectedCategory(isSelected ? null : cat.slug)}
                style={[
                  styles.chip,
                  {
                    backgroundColor: isSelected ? colors.primary : colors.backgroundElement,
                    borderColor: isSelected ? colors.primary : colors.border,
                  },
                ]}>
                <Text
                  style={[
                    styles.chipText,
                    { color: isSelected ? '#ffffff' : colors.text },
                  ]}>
                  {cat.name}
                </Text>
              </Pressable>
            );
          })}
        </ScrollView>

        {/* Sort Row */}
        <View style={styles.sortRow}>
          <Text style={[styles.resultCount, { color: colors.textSecondary }]}>
            {products.length} {products.length === 1 ? 'Product' : 'Products'}
          </Text>

          <View style={styles.sortButtons}>
            <Pressable
              onPress={() => setSortBy('latest')}
              style={[
                styles.sortButton,
                sortBy === 'latest' && { backgroundColor: colors.primaryLight, borderColor: colors.primary },
              ]}>
              <Text
                style={[
                  styles.sortButtonText,
                  { color: sortBy === 'latest' ? colors.primary : colors.textSecondary },
                ]}>
                Latest
              </Text>
            </Pressable>

            <Pressable
              onPress={() => setSortBy('price_low')}
              style={[
                styles.sortButton,
                sortBy === 'price_low' && { backgroundColor: colors.primaryLight, borderColor: colors.primary },
              ]}>
              <Text
                style={[
                  styles.sortButtonText,
                  { color: sortBy === 'price_low' ? colors.primary : colors.textSecondary },
                ]}>
                Price ↑
              </Text>
            </Pressable>

            <Pressable
              onPress={() => setSortBy('price_high')}
              style={[
                styles.sortButton,
                sortBy === 'price_high' && { backgroundColor: colors.primaryLight, borderColor: colors.primary },
              ]}>
              <Text
                style={[
                  styles.sortButtonText,
                  { color: sortBy === 'price_high' ? colors.primary : colors.textSecondary },
                ]}>
                Price ↓
              </Text>
            </Pressable>
          </View>
        </View>
      </View>

      {/* Product List */}
      {loading ? (
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={colors.primary} />
          <Text style={[styles.loadingText, { color: colors.textSecondary }]}>Loading catalog...</Text>
        </View>
      ) : sortedProducts.length === 0 ? (
        <View style={styles.emptyContainer}>
          <Ionicons name="search-outline" size={48} color={colors.textMuted} />
          <Text style={[styles.emptyTitle, { color: colors.text }]}>No products found</Text>
          <Text style={[styles.emptySubtitle, { color: colors.textSecondary }]}>
            Try checking your spelling or use another keyword.
          </Text>
        </View>
      ) : (
        <FlatList
          data={sortedProducts}
          keyExtractor={(item) => item.id.toString()}
          numColumns={2}
          contentContainerStyle={[
            styles.listContent,
            { paddingBottom: BottomTabInset + Spacing.five },
          ]}
          renderItem={({ item }) => (
            <View style={styles.gridCol}>
              <ProductCard product={item} />
            </View>
          )}
        />
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  filterBar: {
    paddingVertical: Spacing.two,
    borderBottomWidth: StyleSheet.hairlineWidth,
  },
  categoryChips: {
    paddingHorizontal: Spacing.four,
    gap: Spacing.two,
    marginBottom: Spacing.two,
  },
  chip: {
    paddingHorizontal: 14,
    paddingVertical: 6,
    borderRadius: 20,
    borderWidth: 1,
  },
  chipText: {
    fontSize: 13,
    fontWeight: '600',
  },
  sortRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: Spacing.four,
    paddingTop: 4,
  },
  resultCount: {
    fontSize: 12,
    fontWeight: '600',
  },
  sortButtons: {
    flexDirection: 'row',
    gap: 6,
  },
  sortButton: {
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: 'transparent',
  },
  sortButtonText: {
    fontSize: 11,
    fontWeight: '700',
  },
  listContent: {
    padding: Spacing.two,
  },
  gridCol: {
    width: '50%',
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
  emptyContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: Spacing.five,
    gap: Spacing.two,
  },
  emptyTitle: {
    fontSize: 18,
    fontWeight: '800',
  },
  emptySubtitle: {
    fontSize: 13,
    textAlign: 'center',
  },
});
