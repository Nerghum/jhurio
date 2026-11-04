import { Ionicons } from '@expo/vector-icons';
import { Image } from 'expo-image';
import { useRouter } from 'expo-router';
import React from 'react';
import {
  Pressable,
  StyleSheet,
  Text,
  useColorScheme,
  View,
} from 'react-native';

import { Config } from '@/constants/config';
import { Colors, Spacing } from '@/constants/theme';
import { useCart } from '@/context/CartContext';
import { Product } from '@/types';

interface ProductCardProps {
  product: Product;
  onPress?: () => void;
}

export const ProductCard: React.FC<ProductCardProps> = ({ product, onPress }) => {
  const router = useRouter();
  const scheme = useColorScheme();
  const colors = Colors[scheme === 'dark' ? 'dark' : 'light'];
  const { addToCart } = useCart();

  const handleCardPress = () => {
    if (onPress) {
      onPress();
    } else {
      router.push(`/product/${product.slug || product.id}` as any);
    }
  };

  const priceNum = Number(product.price);
  const compareNum = product.compare_at_price ? Number(product.compare_at_price) : null;
  const hasDiscount = compareNum && compareNum > priceNum;
  const discountPercent = hasDiscount
    ? Math.round(((compareNum - priceNum) / compareNum) * 100)
    : 0;

  const imageUrl = product.thumbnail?.url || 'https://jhurio.com/assets/images/placeholder.png';

  return (
    <Pressable
      onPress={handleCardPress}
      style={({ pressed }) => [
        styles.card,
        {
          backgroundColor: colors.surface,
          borderColor: colors.border,
          transform: [{ scale: pressed ? 0.98 : 1 }],
        },
      ]}>
      {/* Product Thumbnail */}
      <View style={[styles.imageContainer, { backgroundColor: colors.backgroundElement }]}>
        <Image
          source={{ uri: imageUrl }}
          style={styles.image}
          contentFit="cover"
          transition={200}
        />

        {/* Discount Badge */}
        {hasDiscount && (
          <View style={[styles.discountBadge, { backgroundColor: colors.danger }]}>
            <Text style={styles.discountBadgeText}>-{discountPercent}%</Text>
          </View>
        )}

        {/* Featured Tag */}
        {product.is_featured && !hasDiscount && (
          <View style={[styles.discountBadge, { backgroundColor: colors.accent }]}>
            <Text style={styles.discountBadgeText}>HOT</Text>
          </View>
        )}
      </View>

      {/* Info Section */}
      <View style={styles.info}>
        <Text
          numberOfLines={2}
          style={[styles.title, { color: colors.text }]}>
          {product.name}
        </Text>

        {/* Pricing */}
        <View style={styles.priceRow}>
          <Text style={[styles.price, { color: colors.primary }]}>
            {Config.CURRENCY}{priceNum.toLocaleString()}
          </Text>
          {hasDiscount && (
            <Text style={[styles.comparePrice, { color: colors.textMuted }]}>
              {Config.CURRENCY}{compareNum.toLocaleString()}
            </Text>
          )}
        </View>

        {/* Add to Cart Action */}
        <Pressable
          onPress={(e) => {
            e.stopPropagation();
            addToCart(product, 1);
          }}
          style={({ pressed }) => [
            styles.addButton,
            {
              backgroundColor: colors.primary,
              opacity: pressed ? 0.85 : 1,
            },
          ]}>
          <Ionicons name="cart-outline" size={16} color="#ffffff" style={styles.addIcon} />
          <Text style={styles.addButtonText}>Add to Cart</Text>
        </Pressable>
      </View>
    </Pressable>
  );
};

const styles = StyleSheet.create({
  card: {
    borderRadius: 16,
    borderWidth: 1,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 6,
    elevation: 2,
    flex: 1,
    margin: Spacing.one,
  },
  imageContainer: {
    width: '100%',
    aspectRatio: 1,
    position: 'relative',
    overflow: 'hidden',
  },
  image: {
    width: '100%',
    height: '100%',
  },
  discountBadge: {
    position: 'absolute',
    top: 8,
    left: 8,
    paddingHorizontal: 7,
    paddingVertical: 3,
    borderRadius: 8,
  },
  discountBadgeText: {
    color: '#ffffff',
    fontSize: 10,
    fontWeight: '800',
  },
  info: {
    padding: Spacing.three,
    flex: 1,
    justifyContent: 'space-between',
  },
  title: {
    fontSize: 13,
    fontWeight: '600',
    lineHeight: 18,
    minHeight: 36,
  },
  priceRow: {
    flexDirection: 'row',
    alignItems: 'baseline',
    gap: 6,
    marginTop: Spacing.two,
    marginBottom: Spacing.two,
  },
  price: {
    fontSize: 16,
    fontWeight: '800',
  },
  comparePrice: {
    fontSize: 12,
    textDecorationLine: 'line-through',
  },
  addButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 8,
    borderRadius: 10,
    marginTop: Spacing.one,
  },
  addIcon: {
    marginRight: 4,
  },
  addButtonText: {
    color: '#ffffff',
    fontSize: 12,
    fontWeight: '700',
  },
});
