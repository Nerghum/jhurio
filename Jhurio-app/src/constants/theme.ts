/**
 * Below are the colors that are used in the app. The colors are defined in the light and dark mode.
 * There are many other ways to style your app. For example, [Nativewind](https://www.nativewind.dev/), [Tamagui](https://tamagui.dev/), [unistyles](https://unistyl.es/), etc.
 */

import '@/global.css';

import { Platform } from 'react-native';

export const Colors = {
  light: {
    primary: '#059669', // Emerald 600
    primaryLight: '#ecfdf5',
    primaryDark: '#047857',
    accent: '#f59e0b', // Amber
    accentLight: '#fef3c7',
    text: '#111827',
    textSecondary: '#6b7280',
    textMuted: '#9ca3af',
    background: '#f9fafb',
    surface: '#ffffff',
    backgroundElement: '#f3f4f6',
    backgroundSelected: '#e5e7eb',
    border: '#e5e7eb',
    borderLight: '#f3f4f6',
    danger: '#ef4444',
    success: '#10b981',
    card: '#ffffff',
  },
  dark: {
    primary: '#10b981',
    primaryLight: '#064e3b',
    primaryDark: '#059669',
    accent: '#fbbf24',
    accentLight: '#78350f',
    text: '#f9fafb',
    textSecondary: '#9ca3af',
    textMuted: '#6b7280',
    background: '#0f172a',
    surface: '#1e293b',
    backgroundElement: '#1e293b',
    backgroundSelected: '#334155',
    border: '#334155',
    borderLight: '#1e293b',
    danger: '#f87171',
    success: '#34d399',
    card: '#1e293b',
  },
} as const;

export type ThemeColor = keyof typeof Colors.light & keyof typeof Colors.dark;

export const Fonts = Platform.select({
  ios: {
    /** iOS `UIFontDescriptorSystemDesignDefault` */
    sans: 'system-ui',
    /** iOS `UIFontDescriptorSystemDesignSerif` */
    serif: 'ui-serif',
    /** iOS `UIFontDescriptorSystemDesignRounded` */
    rounded: 'ui-rounded',
    /** iOS `UIFontDescriptorSystemDesignMonospaced` */
    mono: 'ui-monospace',
  },
  default: {
    sans: 'normal',
    serif: 'serif',
    rounded: 'normal',
    mono: 'monospace',
  },
  web: {
    sans: 'var(--font-display)',
    serif: 'var(--font-serif)',
    rounded: 'var(--font-rounded)',
    mono: 'var(--font-mono)',
  },
});

export const Spacing = {
  half: 2,
  one: 4,
  two: 8,
  three: 16,
  four: 24,
  five: 32,
  six: 64,
} as const;

export const BottomTabInset = Platform.select({ ios: 50, android: 80 }) ?? 0;
export const MaxContentWidth = 800;
