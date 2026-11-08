import { Ionicons } from '@expo/vector-icons';
import { useLocalSearchParams, useRouter } from 'expo-router';
import React, { useEffect, useRef, useState } from 'react';
import {
  Animated,
  FlatList,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  useColorScheme,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { ProductCard } from '@/components/ProductCard';
import { BottomTabInset, Colors, Radius, Shadow, Spacing } from '@/constants/theme';
import { ApiService } from '@/services/api';
import { Category, Product } from '@/types';

type SortKey = 'latest' | 'price_low' | 'price_high';

const SORT_OPTIONS: { key: SortKey; label: string; icon: string }[] = [
  { key: 'latest', label: 'Latest', icon: 'time-outline' },
  { key: 'price_low', label: 'Price ↑', icon: 'trending-up-outline' },
  { key: 'price_high', label: 'Price ↓', icon: 'trending-down-outline' },
];

function ProductSkeleton() {
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
    <View style={{ width: '50%', padding: 6 }}>
      <Animated.View style={{ opacity: anim, borderRadius: Radius.lg, overflow: 'hidden', backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border }}>
        <View style={{ width: '100%', height: 160, backgroundColor: colors.skeleton }} />
        <View style={{ padding: 10, gap: 6 }}>
          <View style={{ height: 11, width: '90%', borderRadius: 4, backgroundColor: colors.skeleton }} />
          <View style={{ height: 11, width: '70%', borderRadius: 4, backgroundColor: colors.skeleton }} />
          <View style={{ height: 14, width: '50%', borderRadius: 4, backgroundColor: colors.skeleton }} />
        </View>
      </Animated.View>
    </View>
  );
}

export default function ExploreScreen() {
  const router = useRouter();
  const params = useLocalSearchParams<{ search?: string; category?: string; is_featured?: string }>();
  const scheme = useColorScheme();
  const colors = Colors[scheme === 'dark' ? 'dark' : 'light'];

  const [searchQuery, setSearchQuery] = useState(params.search || '');
  const [selectedCategory, setSelectedCategory] = useState<string | null>(params.category || null);
  const [categories, setCategories] = useState<Category[]>([]);
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  const [sortBy, setSortBy] = useState<SortKey>('latest');
  const [showSortSheet, setShowSortSheet] = useState(false);

  const sortSheetAnim = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    ApiService.getCategories().then(setCategories).catch(() => {});
  }, []);

  useEffect(() => {
    if (params.search !== undefined) setSearchQuery(params.search);
    if (params.category !== undefined) setSelectedCategory(params.category);
  }, [params.search, params.category]);

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
        if (!isCancelled) setProducts(res.products || []);
      })
      .catch(() => {})
      .finally(() => {
        if (!isCancelled) setLoading(false);
      });
    return () => { isCancelled = true; };
  }, [searchQuery, selectedCategory, params.is_featured]);

  const sortedProducts = [...products].sort((a, b) => {
    const pa = Number(a.price), pb = Number(b.price);
    if (sortBy === 'price_low') return pa - pb;
    if (sortBy === 'price_high') return pb - pa;
    return 0;
  });

  const toggleSortSheet = () => {
    if (showSortSheet) {
      Animated.timing(sortSheetAnim, { toValue: 0, duration: 200, useNativeDriver: true }).start(() =>
        setShowSortSheet(false)
      );
    } else {
      setShowSortSheet(true);
      Animated.timing(sortSheetAnim, { toValue: 1, duration: 250, useNativeDriver: true }).start();
    }
  };

  const currentSort = SORT_OPTIONS.find((s) => s.key === sortBy)!;

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={['top']}>

      {/* Top Search Bar */}
      <View style={[styles.searchHeader, { backgroundColor: colors.surface, borderBottomColor: colors.border }]}>
        <View style={[styles.searchBar, { backgroundColor: colors.backgroundElement, borderColor: colors.border }]}>
          <Ionicons name="search-outline" size={18} color={colors.textMuted} />
          <TextInput
            value={searchQuery}
            onChangeText={setSearchQuery}
            placeholder="Search products..."
            placeholderTextColor={colors.textMuted}
            returnKeyType="search"
            style={[styles.searchInput, { color: colors.text }]}
          />
          {searchQuery.length > 0 && (
            <Pressable onPress={() => setSearchQuery('')} hitSlop={8}>
              <Ionicons name="close-circle" size={16} color={colors.textMuted} />
            </Pressable>
          )}
        </View>
        <Pressable
          onPress={toggleSortSheet}
          style={[styles.sortBtn, { backgroundColor: colors.primaryLight, borderColor: colors.primary }]}>
          <Ionicons name={currentSort.icon as any} size={15} color={colors.primary} />
          <Text style={[styles.sortBtnText, { color: colors.primary }]}>{currentSort.label}</Text>
        </Pressable>
      </View>

      {/* Category Filter */}
      <View style={[styles.categoryBar, { backgroundColor: colors.surface, borderBottomColor: colors.border }]}>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.categoryScroll}>
          <Pressable
            onPress={() => setSelectedCategory(null)}
            style={[
              styles.chip,
              {
                backgroundColor: selectedCategory === null ? colors.primary : colors.backgroundElement,
                borderColor: selectedCategory === null ? colors.primary : 'transparent',
              },
            ]}>
            <Text style={[styles.chipText, { color: selectedCategory === null ? '#fff' : colors.textSecondary }]}>
              🛍️ All
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
                    borderColor: isSelected ? colors.primary : 'transparent',
                  },
                ]}>
                <Text style={[styles.chipText, { color: isSelected ? '#fff' : colors.textSecondary }]}>
                  {cat.name}
                </Text>
              </Pressable>
            );
          })}
        </ScrollView>
      </View>

      {/* Result count */}
      <View style={[styles.resultBar, { backgroundColor: colors.background }]}>
        <Text style={[styles.resultText, { color: colors.textMuted }]}>
          {loading ? 'Loading...' : `${products.length} products found`}
        </Text>
      </View>

      {/* Product Grid */}
      {loading ? (
        <View style={styles.skeletonGrid}>
          {[1, 2, 3, 4, 5, 6].map((i) => <ProductSkeleton key={i} />)}
        </View>
      ) : sortedProducts.length === 0 ? (
        <View style={styles.emptyContainer}>
          <View style={[styles.emptyIcon, { backgroundColor: colors.primaryLight }]}>
            <Ionicons name="search-outline" size={48} color={colors.primary} />
          </View>
          <Text style={[styles.emptyTitle, { color: colors.text }]}>No products found</Text>
          <Text style={[styles.emptySubtitle, { color: colors.textSecondary }]}>
            Try a different keyword or category
          </Text>
          <Pressable
            onPress={() => { setSearchQuery(''); setSelectedCategory(null); }}
            style={[styles.clearBtn, { backgroundColor: colors.primary }]}>
            <Text style={styles.clearBtnText}>Clear Filters</Text>
          </Pressable>
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

      {/* Sort Bottom Sheet */}
      {showSortSheet && (
        <Pressable style={styles.sheetBackdrop} onPress={toggleSortSheet}>
          <Animated.View
            style={[
              styles.sortSheet,
              {
                backgroundColor: colors.surface,
                borderColor: colors.border,
                transform: [{ translateY: sortSheetAnim.interpolate({ inputRange: [0, 1], outputRange: [200, 0] }) }],
              },
            ]}>
            <View style={[styles.sheetHandle, { backgroundColor: colors.border }]} />
            <Text style={[styles.sheetTitle, { color: colors.text }]}>Sort By</Text>
            {SORT_OPTIONS.map((opt) => (
              <Pressable
                key={opt.key}
                onPress={() => { setSortBy(opt.key); toggleSortSheet(); }}
                style={[
                  styles.sheetOption,
                  {
                    backgroundColor: sortBy === opt.key ? colors.primaryLight : 'transparent',
                    borderColor: sortBy === opt.key ? colors.primary : 'transparent',
                  },
                ]}>
                <Ionicons
                  name={opt.icon as any}
                  size={20}
                  color={sortBy === opt.key ? colors.primary : colors.textSecondary}
                />
                <Text style={[styles.sheetOptionText, { color: sortBy === opt.key ? colors.primary : colors.text }]}>
                  {opt.label}
                </Text>
                {sortBy === opt.key && (
                  <Ionicons name="checkmark-circle" size={20} color={colors.primary} style={{ marginLeft: 'auto' }} />
                )}
              </Pressable>
            ))}
          </Animated.View>
        </Pressable>
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },

  searchHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.two,
    paddingHorizontal: Spacing.three,
    paddingVertical: Spacing.two,
    borderBottomWidth: StyleSheet.hairlineWidth,
  },
  searchBar: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.two,
    borderRadius: Radius.lg,
    paddingHorizontal: Spacing.three,
    height: 42,
    borderWidth: 1,
  },
  searchInput: {
    flex: 1,
    fontSize: 14,
    fontWeight: '500',
    padding: 0,
  },
  sortBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    paddingHorizontal: Spacing.two + 2,
    paddingVertical: Spacing.two,
    borderRadius: Radius.lg,
    borderWidth: 1,
  },
  sortBtnText: { fontSize: 12, fontWeight: '700' },

  categoryBar: {
    borderBottomWidth: StyleSheet.hairlineWidth,
    paddingVertical: Spacing.two,
  },
  categoryScroll: {
    paddingHorizontal: Spacing.three,
    gap: Spacing.one + 2,
  },
  chip: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: Radius.full,
    borderWidth: 1.5,
  },
  chipText: { fontSize: 12.5, fontWeight: '600' },

  resultBar: {
    paddingHorizontal: Spacing.three,
    paddingVertical: Spacing.one + 2,
  },
  resultText: { fontSize: 12, fontWeight: '500' },

  skeletonGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    paddingHorizontal: Spacing.two,
    paddingTop: Spacing.two,
  },
  listContent: { paddingHorizontal: Spacing.two, paddingTop: Spacing.two },
  gridCol: { width: '50%' },

  emptyContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: Spacing.five,
    gap: Spacing.three,
  },
  emptyIcon: {
    width: 96,
    height: 96,
    borderRadius: 48,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: Spacing.two,
  },
  emptyTitle: { fontSize: 20, fontWeight: '800' },
  emptySubtitle: { fontSize: 14, textAlign: 'center' },
  clearBtn: {
    marginTop: Spacing.two,
    paddingHorizontal: Spacing.four,
    paddingVertical: Spacing.two + 2,
    borderRadius: Radius.full,
  },
  clearBtnText: { color: '#fff', fontWeight: '700', fontSize: 15 },

  // Sort sheet
  sheetBackdrop: {
    ...StyleSheet.absoluteFill as any,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'flex-end',
  },
  sortSheet: {
    borderTopLeftRadius: Radius.xxl,
    borderTopRightRadius: Radius.xxl,
    padding: Spacing.four,
    paddingBottom: Spacing.five,
    borderWidth: StyleSheet.hairlineWidth,
    gap: Spacing.two,
  },
  sheetHandle: {
    width: 40,
    height: 4,
    borderRadius: 2,
    alignSelf: 'center',
    marginBottom: Spacing.two,
  },
  sheetTitle: { fontSize: 17, fontWeight: '800', marginBottom: Spacing.two },
  sheetOption: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.three,
    padding: Spacing.three,
    borderRadius: Radius.lg,
    borderWidth: 1.5,
  },
  sheetOptionText: { fontSize: 15, fontWeight: '600' },
});
