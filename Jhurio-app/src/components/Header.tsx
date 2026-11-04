import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';
import React, { useRef } from 'react';
import {
  Animated,
  Platform,
  Pressable,
  StyleSheet,
  Text,
  TextInput,
  useColorScheme,
  View,
} from 'react-native';

import { Colors, Radius, Shadow, Spacing } from '@/constants/theme';
import { useCart } from '@/context/CartContext';

interface HeaderProps {
  searchQuery?: string;
  onSearchChange?: (text: string) => void;
  onSubmitSearch?: () => void;
  showSearchBar?: boolean;
  title?: string;
  showBack?: boolean;
}

export const Header: React.FC<HeaderProps> = ({
  searchQuery = '',
  onSearchChange,
  onSubmitSearch,
  showSearchBar = true,
  title,
  showBack = false,
}) => {
  const router = useRouter();
  const scheme = useColorScheme();
  const colors = Colors[scheme === 'dark' ? 'dark' : 'light'];
  const { itemCount } = useCart();

  const cartScaleAnim = useRef(new Animated.Value(1)).current;

  const handleCartPress = () => {
    Animated.sequence([
      Animated.timing(cartScaleAnim, { toValue: 0.85, duration: 100, useNativeDriver: true }),
      Animated.spring(cartScaleAnim, { toValue: 1, useNativeDriver: true }),
    ]).start();
    router.push('/cart');
  };

  return (
    <View style={[styles.container, { backgroundColor: colors.surface, borderBottomColor: colors.border }]}>
      {/* Top Row */}
      <View style={styles.topRow}>
        {showBack ? (
          <Pressable onPress={() => router.back()} style={styles.backBtn} hitSlop={8}>
            <Ionicons name="chevron-back" size={24} color={colors.text} />
          </Pressable>
        ) : (
          <View style={styles.logoWrap}>
            <View style={[styles.logoIcon, { backgroundColor: colors.primary }]}>
              <Text style={styles.logoLetter}>J</Text>
            </View>
            <View>
              <Text style={[styles.logoText, { color: colors.text }]}>Jhurio</Text>
              <Text style={[styles.logoSub, { color: colors.textMuted }]}>Shop Smart 🛍️</Text>
            </View>
          </View>
        )}

        {title ? (
          <Text style={[styles.titleText, { color: colors.text }]}>{title}</Text>
        ) : null}

        <View style={styles.actions}>
          {/* Notifications */}
          <Pressable
            style={[styles.iconBtn, { backgroundColor: colors.backgroundElement }]}
            hitSlop={8}>
            <Ionicons name="notifications-outline" size={20} color={colors.text} />
          </Pressable>

          {/* Cart */}
          <Animated.View style={{ transform: [{ scale: cartScaleAnim }] }}>
            <Pressable
              onPress={handleCartPress}
              style={[styles.iconBtn, { backgroundColor: colors.backgroundElement }]}
              hitSlop={8}>
              <Ionicons name="cart-outline" size={20} color={colors.text} />
              {itemCount > 0 && (
                <View style={[styles.badge, { backgroundColor: colors.danger }]}>
                  <Text style={styles.badgeText}>{itemCount > 99 ? '99+' : itemCount}</Text>
                </View>
              )}
            </Pressable>
          </Animated.View>
        </View>
      </View>

      {/* Search Bar */}
      {showSearchBar && (
        <View style={[styles.searchContainer, { backgroundColor: colors.backgroundElement, borderColor: colors.border }]}>
          <Ionicons name="search-outline" size={18} color={colors.textMuted} style={styles.searchIcon} />
          <TextInput
            value={searchQuery}
            onChangeText={onSearchChange}
            onSubmitEditing={onSubmitSearch}
            placeholder="Search products..."
            placeholderTextColor={colors.textMuted}
            returnKeyType="search"
            style={[styles.searchInput, { color: colors.text }]}
          />
          {searchQuery.length > 0 && (
            <Pressable onPress={() => onSearchChange?.('')} hitSlop={8}>
              <Ionicons name="close-circle" size={16} color={colors.textMuted} />
            </Pressable>
          )}
        </View>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    paddingTop: Platform.OS === 'android' ? Spacing.two : 0,
    paddingHorizontal: Spacing.three,
    paddingBottom: Spacing.two,
    borderBottomWidth: StyleSheet.hairlineWidth,
    ...Shadow.sm,
  },
  topRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: Spacing.two,
    minHeight: 44,
  },
  backBtn: {
    width: 36,
    height: 36,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: Radius.full,
  },
  logoWrap: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.two,
    flex: 1,
  },
  logoIcon: {
    width: 36,
    height: 36,
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
  },
  logoLetter: {
    color: '#fff',
    fontSize: 20,
    fontWeight: '900',
  },
  logoText: {
    fontSize: 18,
    fontWeight: '800',
    letterSpacing: -0.5,
  },
  logoSub: {
    fontSize: 11,
    fontWeight: '500',
  },
  titleText: {
    fontSize: 17,
    fontWeight: '700',
    flex: 1,
    textAlign: 'center',
  },
  actions: {
    flexDirection: 'row',
    gap: Spacing.two,
    alignItems: 'center',
  },
  iconBtn: {
    width: 38,
    height: 38,
    borderRadius: Radius.md,
    alignItems: 'center',
    justifyContent: 'center',
    position: 'relative',
  },
  badge: {
    position: 'absolute',
    top: -4,
    right: -4,
    minWidth: 18,
    height: 18,
    borderRadius: 9,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 4,
    borderWidth: 2,
    borderColor: '#fff',
  },
  badgeText: {
    color: '#fff',
    fontSize: 9,
    fontWeight: '800',
  },
  searchContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    borderRadius: Radius.lg,
    paddingHorizontal: Spacing.three,
    height: 42,
    borderWidth: 1,
  },
  searchIcon: {
    marginRight: Spacing.two,
  },
  searchInput: {
    flex: 1,
    fontSize: 14,
    fontWeight: '500',
    padding: 0,
  },
});
