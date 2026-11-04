import '@/global.css';
import { Platform } from 'react-native';

export const Colors = {
  light: {
    primary: '#059669',         // Emerald 600
    primaryLight: '#d1fae5',    // Emerald 100
    primaryDark: '#047857',     // Emerald 700
    primaryGradientStart: '#059669',
    primaryGradientEnd: '#10b981',
    accent: '#f59e0b',          // Amber 500
    accentLight: '#fef3c7',
    danger: '#ef4444',
    success: '#10b981',
    warning: '#f59e0b',
    text: '#111827',            // Gray 900
    textSecondary: '#4b5563',   // Gray 600
    textMuted: '#9ca3af',       // Gray 400
    background: '#f1f5f9',      // Slate 100
    surface: '#ffffff',
    surfaceElevated: '#ffffff',
    backgroundElement: '#f8fafc',
    backgroundSelected: '#e2e8f0',
    border: '#e2e8f0',
    borderLight: '#f1f5f9',
    card: '#ffffff',
    skeleton: '#e2e8f0',
    tabBar: '#ffffff',
    tabBarBorder: '#e2e8f0',
    tabIconDefault: '#94a3b8',
    tabIconSelected: '#059669',
    overlay: 'rgba(0,0,0,0.5)',
    badge: '#ef4444',
  },
  dark: {
    primary: '#10b981',
    primaryLight: '#065f46',
    primaryDark: '#059669',
    primaryGradientStart: '#059669',
    primaryGradientEnd: '#10b981',
    accent: '#fbbf24',
    accentLight: '#451a03',
    danger: '#f87171',
    success: '#34d399',
    warning: '#fbbf24',
    text: '#f1f5f9',
    textSecondary: '#94a3b8',
    textMuted: '#64748b',
    background: '#0f172a',
    surface: '#1e293b',
    surfaceElevated: '#263347',
    backgroundElement: '#1e293b',
    backgroundSelected: '#334155',
    border: '#2d3f55',
    borderLight: '#1e293b',
    card: '#1e293b',
    skeleton: '#2d3f55',
    tabBar: '#1e293b',
    tabBarBorder: '#2d3f55',
    tabIconDefault: '#64748b',
    tabIconSelected: '#10b981',
    overlay: 'rgba(0,0,0,0.7)',
    badge: '#f87171',
  },
} as const;

export type ThemeColors = typeof Colors.light;
export type ThemeColor = keyof ThemeColors;

export const Fonts = Platform.select({
  ios: {
    sans: 'system-ui',
    serif: 'ui-serif',
    rounded: 'ui-rounded',
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

export const Radius = {
  sm: 8,
  md: 12,
  lg: 16,
  xl: 20,
  xxl: 24,
  full: 9999,
} as const;

export const Shadow = {
  sm: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 2,
  },
  md: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.08,
    shadowRadius: 12,
    elevation: 4,
  },
  lg: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.12,
    shadowRadius: 20,
    elevation: 8,
  },
} as const;

export const BottomTabInset = Platform.select({ ios: 50, android: 72 }) ?? 0;
export const MaxContentWidth = 800;
