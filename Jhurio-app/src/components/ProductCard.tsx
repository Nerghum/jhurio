import { Ionicons } from '@expo/vector-icons';
import { Image } from 'expo-image';
import { useRouter } from 'expo-router';
import React, { useRef } from 'react';
import {
  Animated,
  Pressable,
  StyleSheet,
  Text,
  useColorScheme,
  View,
} from 'react-native';

import { Config } from '@/constants/config';
import { Colors, Radius, Shadow, Spacing } from '@/constants/theme';
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

  const scaleAnim = useRef(new Animated.Value(1)).current;
  const addScaleAnim = useRef(new Animated.Value(1)).current;

  const handleCardPress = () => {
    Animated.sequence([
      Animated.timing(scaleAnim, { toValue: 0.97, duration: 80, useNativeDriver: true }),
      Animated.timing(scaleAnim, { toValue: 1, duration: 80, useNativeDriver: true }),
    ]).start();
    if (onPress) {
      onPress();
    } else {
      router.push(`/product/${product.slug || product.id}` as any);
    }
  };

  const handleAddToCart = (e: any) => {
    e.stopPropagation();
    Animated.sequence([
      Animated.timing(addScaleAnim, { toValue: 0.85, duration: 100, useNativeDriver: true }),
      Animated.spring(addScaleAnim, { toValue: 1, useNativeDriver: true, damping: 10 }),
    ]).start();
    addToCart(product, 1);
  };

  const priceNum = Number(product.price);
  const compareNum = product.compare_at_price ? Number(product.compare_at_price) : null;
  const hasDiscount = compareNum && compareNum > priceNum;
  const discountPercent = hasDiscount
    ? Math.round(((compareNum - priceNum) / compareNum) * 100)
    : 0;

  const imageUrl = product.thumbnail?.url || 'https://jhurio.com/assets/images/placeholder.png';

  return (
    <Animated.View style={{ transform: [{ scale: scaleAnim }] }}>
      <Pressable
        onPress={handleCardPress}
        style={[
          styles.card,
          {
            backgroundColor: colors.surface,
            borderColor: colors.border,
            ...Shadow.sm,
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

          {/* Top badges */}
          <View style={styles.badgeRow}>
            {hasDiscount && (
              <View style={[styles.badge, { backgroundColor: colors.danger }]}>
                <Text style={styles.badgeText}>-{discountPercent}%</Text>
              </View>
            )}
            {product.is_featured && !hasDiscount && (
              <View style={[styles.badge, { backgroundColor: colors.accent }]}>
                <Text style={styles.badgeText}>🔥</Text>
              </View>
            )}
          </View>

          {/* Quick Add Button */}
          <Animated.View style={[styles.addBtnFloat, { transform: [{ scale: addScaleAnim }] }]}>
            <Pressable
              onPress={handleAddToCart}
              style={[styles.addBtnFloatInner, { backgroundColor: colors.primary }]}>
              <Ionicons name="add" size={18} color="#fff" />
            </Pressable>
          </Animated.View>
        </View>

        {/* Info Section */}
        <View style={styles.info}>
          <Text numberOfLines={2} style={[styles.title, { color: colors.text }]}>
            {product.name}
          </Text>

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
        </View>
      </Pressable>
    </Animated.View>
  );
};

const styles = StyleSheet.create({
  card: {
    borderRadius: Radius.lg,
    borderWidth: StyleSheet.hairlineWidth,
    overflow: 'hidden',
    flex: 1,
    margin: Spacing.one + 2,
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
  badgeRow: {
    position: 'absolute',
    top: 8,
    left: 8,
    flexDirection: 'row',
    gap: 4,
  },
  badge: {
    paddingHorizontal: 7,
    paddingVertical: 3,
    borderRadius: 6,
  },
  badgeText: {
    color: '#ffffff',
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 0.5,
  },
  addBtnFloat: {
    position: 'absolute',
    bottom: 8,
    right: 8,
  },
  addBtnFloatInner: {
    width: 32,
    height: 32,
    borderRadius: 16,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 4,
    elevation: 4,
  },
  info: {
    padding: Spacing.two + 4,
  },
  title: {
    fontSize: 12.5,
    fontWeight: '600',
    lineHeight: 17,
    minHeight: 34,
    marginBottom: 4,
  },
  priceRow: {
    flexDirection: 'row',
    alignItems: 'baseline',
    gap: 5,
    flexWrap: 'wrap',
  },
  price: {
    fontSize: 15,
    fontWeight: '800',
  },
  comparePrice: {
    fontSize: 11,
    textDecorationLine: 'line-through',
  },
});
