import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';
import React from 'react';
import {
  Pressable,
  StyleSheet,
  Text,
  TextInput,
  useColorScheme,
  View,
} from 'react-native';

import { Colors, Spacing } from '@/constants/theme';
import { useCart } from '@/context/CartContext';

interface HeaderProps {
  searchQuery?: string;
  onSearchChange?: (text: string) => void;
  onSubmitSearch?: () => void;
  showSearchBar?: boolean;
}

export const Header: React.FC<HeaderProps> = ({
  searchQuery = '',
  onSearchChange,
  onSubmitSearch,
  showSearchBar = true,
}) => {
  const router = useRouter();
  const scheme = useColorScheme();
  const colors = Colors[scheme === 'dark' ? 'dark' : 'light'];
  const { itemCount } = useCart();

  return (
    <View style={[styles.container, { backgroundColor: colors.surface, borderBottomColor: colors.border }]}>
      {/* Top Bar: Brand Logo & Cart Icon */}
      <View style={styles.topRow}>
        <Pressable onPress={() => router.push('/')} style={styles.brandContainer}>
          <Text style={[styles.brandPrefix, { color: colors.text }]}>Jhurio</Text>
          <View style={[styles.brandBadge, { backgroundColor: colors.primary }]}>
            <Text style={styles.brandBadgeText}>SHOP</Text>
          </View>
        </Pressable>

        <View style={styles.actionsRow}>
          <Pressable
            onPress={() => router.push('/orders')}
            style={[styles.iconButton, { backgroundColor: colors.backgroundElement }]}>
            <Ionicons name="receipt-outline" size={20} color={colors.text} />
          </Pressable>

          <Pressable
            onPress={() => router.push('/cart')}
            style={[styles.iconButton, { backgroundColor: colors.backgroundElement }]}>
            <Ionicons name="bag-handle-outline" size={20} color={colors.text} />
            {itemCount > 0 && (
              <View style={[styles.badge, { backgroundColor: colors.danger }]}>
                <Text style={styles.badgeText}>{itemCount > 9 ? '9+' : itemCount}</Text>
              </View>
            )}
          </Pressable>
        </View>
      </View>

      {/* Search Bar Input */}
      {showSearchBar && (
        <View style={[styles.searchBox, { backgroundColor: colors.backgroundElement, borderColor: colors.border }]}>
          <Ionicons name="search-outline" size={18} color={colors.textSecondary} style={styles.searchIcon} />
          <TextInput
            placeholder="Search products in Jhurio..."
            placeholderTextColor={colors.textMuted}
            value={searchQuery}
            onChangeText={onSearchChange}
            onSubmitEditing={onSubmitSearch}
            returnKeyType="search"
            style={[styles.searchInput, { color: colors.text }]}
          />
          {searchQuery.length > 0 && (
            <Pressable onPress={() => onSearchChange && onSearchChange('')} style={styles.clearButton}>
              <Ionicons name="close-circle" size={16} color={colors.textSecondary} />
            </Pressable>
          )}
        </View>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    paddingHorizontal: Spacing.four,
    paddingTop: Spacing.three,
    paddingBottom: Spacing.three,
    borderBottomWidth: StyleSheet.hairlineWidth,
  },
  topRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: Spacing.two,
  },
  brandContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.one,
  },
  brandPrefix: {
    fontSize: 22,
    fontWeight: '900',
    letterSpacing: -0.5,
  },
  brandBadge: {
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 6,
  },
  brandBadgeText: {
    color: '#ffffff',
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 0.5,
  },
  actionsRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.two,
  },
  iconButton: {
    width: 38,
    height: 38,
    borderRadius: 19,
    alignItems: 'center',
    justifyContent: 'center',
    position: 'relative',
  },
  badge: {
    position: 'absolute',
    top: -2,
    right: -2,
    minWidth: 18,
    height: 18,
    borderRadius: 9,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 4,
  },
  badgeText: {
    color: '#ffffff',
    fontSize: 10,
    fontWeight: 'bold',
  },
  searchBox: {
    flexDirection: 'row',
    alignItems: 'center',
    borderRadius: 12,
    borderWidth: 1,
    paddingHorizontal: Spacing.three,
    height: 42,
  },
  searchIcon: {
    marginRight: Spacing.two,
  },
  searchInput: {
    flex: 1,
    fontSize: 14,
    height: '100%',
    padding: 0,
  },
  clearButton: {
    padding: 4,
  },
});
