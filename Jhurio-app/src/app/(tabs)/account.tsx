import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';
import React, { useState } from 'react';
import {
  Alert,
  KeyboardAvoidingView,
  Platform,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  useColorScheme,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { BottomTabInset, Colors, Radius, Shadow, Spacing } from '@/constants/theme';
import { useAuth } from '@/context/AuthContext';
import { useCart } from '@/context/CartContext';

type Screen = 'profile' | 'login' | 'register';

function FormField({
  label,
  value,
  onChange,
  placeholder,
  icon,
  keyboardType,
  secureTextEntry,
  colors,
}: any) {
  return (
    <View style={{ gap: 6, marginBottom: 14 }}>
      <Text style={[{ fontSize: 12, fontWeight: '700', color: colors.textSecondary }]}>{label}</Text>
      <View
        style={{
          flexDirection: 'row',
          alignItems: 'center',
          gap: 10,
          backgroundColor: colors.backgroundElement,
          borderRadius: Radius.lg,
          paddingHorizontal: 14,
          height: 48,
          borderWidth: 1.5,
          borderColor: colors.border,
        }}>
        <Ionicons name={icon} size={16} color={colors.textMuted} />
        <TextInput
          value={value}
          onChangeText={onChange}
          placeholder={placeholder}
          placeholderTextColor={colors.textMuted}
          keyboardType={keyboardType || 'default'}
          secureTextEntry={secureTextEntry}
          autoCapitalize="none"
          style={{ flex: 1, fontSize: 14, color: colors.text, padding: 0 }}
        />
      </View>
    </View>
  );
}

function LoginScreen({ onSwitch, colors }: { onSwitch: (s: Screen) => void; colors: any }) {
  const { login } = useAuth();
  const { setCustomerPhone, setCustomerName } = useCart();
  const [phone, setPhone] = useState('');
  const [name, setName] = useState('');
  const [loading, setLoading] = useState(false);

  const handleLogin = async () => {
    if (!phone.trim() || phone.trim().length < 10)
      return Alert.alert('Error', 'Enter a valid phone number');
    if (!name.trim()) return Alert.alert('Error', 'Enter your name');
    setLoading(true);
    await login({ name: name.trim(), phone: phone.trim(), email: '', address: '', district: 'ঢাকা' });
    setCustomerPhone(phone.trim());
    setCustomerName(name.trim());
    setLoading(false);
  };

  return (
    <KeyboardAvoidingView style={{ flex: 1 }} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <ScrollView contentContainerStyle={{ padding: 24, flexGrow: 1, justifyContent: 'center' }}>
        {/* Logo area */}
        <View style={{ alignItems: 'center', marginBottom: 36 }}>
          <View style={[styles.bigLogo, { backgroundColor: colors.primary }]}>
            <Text style={{ color: '#fff', fontSize: 36, fontWeight: '900' }}>J</Text>
          </View>
          <Text style={{ fontSize: 26, fontWeight: '900', color: colors.text, marginTop: 16 }}>Welcome Back!</Text>
          <Text style={{ fontSize: 14, color: colors.textMuted, marginTop: 6, textAlign: 'center' }}>
            Sign in to track your orders and enjoy a personalized experience
          </Text>
        </View>

        {/* Form card */}
        <View style={[styles.card, { backgroundColor: colors.surface, borderColor: colors.border }]}>
          <FormField label="Full Name *" value={name} onChange={setName} placeholder="Your name" icon="person-outline" colors={colors} />
          <FormField label="Phone Number *" value={phone} onChange={setPhone} placeholder="01XXXXXXXXX" icon="call-outline" keyboardType="phone-pad" colors={colors} />

          <Pressable
            onPress={handleLogin}
            disabled={loading}
            style={[styles.btn, { backgroundColor: colors.primary, opacity: loading ? 0.7 : 1, marginTop: 8 }]}>
            <Ionicons name="log-in-outline" size={18} color="#fff" />
            <Text style={{ color: '#fff', fontWeight: '800', fontSize: 15 }}>
              {loading ? 'Signing in...' : 'Sign In'}
            </Text>
          </Pressable>
        </View>

        <View style={{ flexDirection: 'row', justifyContent: 'center', marginTop: 20, gap: 6 }}>
          <Text style={{ color: colors.textMuted, fontSize: 14 }}>New to Jhurio?</Text>
          <Pressable onPress={() => onSwitch('register')}>
            <Text style={{ color: colors.primary, fontWeight: '800', fontSize: 14 }}>Create Account</Text>
          </Pressable>
        </View>

        {/* Guest info */}
        <View style={[styles.infoBox, { backgroundColor: colors.primaryLight, marginTop: 24 }]}>
          <Ionicons name="information-circle-outline" size={16} color={colors.primary} />
          <Text style={{ flex: 1, color: colors.primary, fontSize: 12, fontWeight: '600' }}>
            No password needed — just your name and phone number to get started!
          </Text>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

function RegisterScreen({ onSwitch, colors }: { onSwitch: (s: Screen) => void; colors: any }) {
  const { login } = useAuth();
  const { setCustomerPhone, setCustomerName, setCustomerAddress, setCustomerDistrict } = useCart();
  const [form, setForm] = useState({ name: '', phone: '', email: '', address: '', district: 'ঢাকা' });
  const [loading, setLoading] = useState(false);

  const update = (field: string) => (val: string) => setForm((f) => ({ ...f, [field]: val }));

  const handleRegister = async () => {
    if (!form.name.trim()) return Alert.alert('Error', 'Enter your full name');
    if (!form.phone.trim() || form.phone.trim().length < 10) return Alert.alert('Error', 'Enter a valid phone number');
    setLoading(true);
    await login({ name: form.name.trim(), phone: form.phone.trim(), email: form.email.trim(), address: form.address.trim(), district: form.district.trim() || 'ঢাকা' });
    setCustomerPhone(form.phone.trim());
    setCustomerName(form.name.trim());
    if (form.address.trim()) setCustomerAddress(form.address.trim());
    if (form.district.trim()) setCustomerDistrict(form.district.trim());
    setLoading(false);
  };

  return (
    <KeyboardAvoidingView style={{ flex: 1 }} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <ScrollView contentContainerStyle={{ padding: 24 }}>
        <View style={{ alignItems: 'center', marginBottom: 28 }}>
          <View style={[styles.bigLogo, { backgroundColor: colors.primary }]}>
            <Text style={{ color: '#fff', fontSize: 36, fontWeight: '900' }}>J</Text>
          </View>
          <Text style={{ fontSize: 24, fontWeight: '900', color: colors.text, marginTop: 16 }}>Create Account</Text>
          <Text style={{ fontSize: 13, color: colors.textMuted, marginTop: 6, textAlign: 'center' }}>
            Join Jhurio for exclusive deals and order tracking
          </Text>
        </View>

        <View style={[styles.card, { backgroundColor: colors.surface, borderColor: colors.border }]}>
          <FormField label="Full Name *" value={form.name} onChange={update('name')} placeholder="Your full name" icon="person-outline" colors={colors} />
          <FormField label="Phone Number *" value={form.phone} onChange={update('phone')} placeholder="01XXXXXXXXX" icon="call-outline" keyboardType="phone-pad" colors={colors} />
          <FormField label="Email (optional)" value={form.email} onChange={update('email')} placeholder="email@example.com" icon="mail-outline" keyboardType="email-address" colors={colors} />
          <FormField label="Address" value={form.address} onChange={update('address')} placeholder="House, Road, Area" icon="location-outline" colors={colors} />
          <FormField label="District" value={form.district} onChange={update('district')} placeholder="e.g. Dhaka" icon="map-outline" colors={colors} />

          <Pressable
            onPress={handleRegister}
            disabled={loading}
            style={[styles.btn, { backgroundColor: colors.primary, opacity: loading ? 0.7 : 1, marginTop: 8 }]}>
            <Ionicons name="person-add-outline" size={18} color="#fff" />
            <Text style={{ color: '#fff', fontWeight: '800', fontSize: 15 }}>
              {loading ? 'Creating...' : 'Create Account'}
            </Text>
          </Pressable>
        </View>

        <View style={{ flexDirection: 'row', justifyContent: 'center', marginTop: 20, gap: 6 }}>
          <Text style={{ color: colors.textMuted, fontSize: 14 }}>Already have an account?</Text>
          <Pressable onPress={() => onSwitch('login')}>
            <Text style={{ color: colors.primary, fontWeight: '800', fontSize: 14 }}>Sign In</Text>
          </Pressable>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

function ProfileScreen({ colors }: { colors: any }) {
  const scheme = useColorScheme();
  const { user, logout, updateProfile } = useAuth();
  const { setCustomerPhone, setCustomerName, setCustomerAddress, setCustomerDistrict, orders: _ } = useCart() as any;
  const router = useRouter();
  const [editing, setEditing] = useState(false);
  const [form, setForm] = useState({
    name: user?.name || '',
    email: user?.email || '',
    address: user?.address || '',
    district: user?.district || '',
  });

  const update = (field: string) => (val: string) => setForm((f) => ({ ...f, [field]: val }));

  const handleSave = async () => {
    await updateProfile(form);
    if (form.name) setCustomerName(form.name);
    if (form.address) setCustomerAddress(form.address);
    if (form.district) setCustomerDistrict(form.district);
    setEditing(false);
    Alert.alert('✅ Saved', 'Your profile has been updated!');
  };

  const handleLogout = () => {
    Alert.alert('Sign Out', 'Are you sure you want to sign out?', [
      { text: 'Cancel', style: 'cancel' },
      { text: 'Sign Out', style: 'destructive', onPress: () => logout() },
    ]);
  };

  const initials = (user?.name || 'U').split(' ').map((n: string) => n[0]).slice(0, 2).join('').toUpperCase();

  return (
    <ScrollView contentContainerStyle={{ paddingBottom: BottomTabInset + 24 }}>
      {/* Profile hero */}
      <View style={[styles.profileHero, { backgroundColor: colors.primary }]}>
        <View style={styles.avatarRing}>
          <View style={[styles.avatar, { backgroundColor: '#fff' }]}>
            <Text style={[styles.avatarText, { color: colors.primary }]}>{initials}</Text>
          </View>
        </View>
        <Text style={styles.profileName}>{user?.name}</Text>
        <Text style={styles.profilePhone}>{user?.phone}</Text>
        {user?.email ? <Text style={styles.profileEmail}>{user.email}</Text> : null}
      </View>

      {/* Quick actions */}
      <View style={[styles.quickActions, { backgroundColor: colors.surface, borderColor: colors.border }]}>
        {[
          { icon: 'receipt-outline', label: 'My Orders', action: () => router.push('/orders') },
          { icon: 'heart-outline', label: 'Wishlist', action: () => {} },
          { icon: 'location-outline', label: 'Addresses', action: () => setEditing(true) },
          { icon: 'gift-outline', label: 'Rewards', action: () => {} },
        ].map((item, i) => (
          <Pressable key={i} onPress={item.action} style={styles.quickAction}>
            <View style={[styles.quickIcon, { backgroundColor: colors.primaryLight }]}>
              <Ionicons name={item.icon as any} size={22} color={colors.primary} />
            </View>
            <Text style={[styles.quickLabel, { color: colors.text }]}>{item.label}</Text>
          </Pressable>
        ))}
      </View>

      {/* Profile Details */}
      <View style={[styles.card, { marginHorizontal: 16, marginTop: 16, backgroundColor: colors.surface, borderColor: colors.border }]}>
        <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 16 }}>
          <Text style={{ fontSize: 16, fontWeight: '800', color: colors.text }}>Profile Details</Text>
          <Pressable onPress={() => editing ? handleSave() : setEditing(true)}
            style={[{ paddingHorizontal: 14, paddingVertical: 7, borderRadius: 10, backgroundColor: colors.primaryLight }]}>
            <Text style={{ color: colors.primary, fontWeight: '800', fontSize: 13 }}>
              {editing ? 'Save' : 'Edit'}
            </Text>
          </Pressable>
        </View>

        {editing ? (
          <>
            <FormField label="Full Name" value={form.name} onChange={update('name')} placeholder="Your name" icon="person-outline" colors={colors} />
            <FormField label="Email" value={form.email} onChange={update('email')} placeholder="email@example.com" icon="mail-outline" colors={colors} />
            <FormField label="Address" value={form.address} onChange={update('address')} placeholder="House, Road, Area" icon="location-outline" colors={colors} />
            <FormField label="District" value={form.district} onChange={update('district')} placeholder="e.g. Dhaka" icon="map-outline" colors={colors} />
            <Pressable onPress={() => setEditing(false)} style={{ alignItems: 'center', paddingVertical: 10 }}>
              <Text style={{ color: colors.textMuted, fontWeight: '600' }}>Cancel</Text>
            </Pressable>
          </>
        ) : (
          [
            { icon: 'call-outline', label: 'Phone', value: user?.phone || '—' },
            { icon: 'mail-outline', label: 'Email', value: user?.email || '—' },
            { icon: 'location-outline', label: 'Address', value: user?.address || '—' },
            { icon: 'map-outline', label: 'District', value: user?.district || '—' },
          ].map((row, i) => (
            <View key={i} style={[{ flexDirection: 'row', alignItems: 'center', gap: 12, paddingVertical: 12 }, i > 0 && { borderTopWidth: 1, borderTopColor: colors.border }]}>
              <View style={[{ width: 36, height: 36, borderRadius: 10, backgroundColor: colors.primaryLight, alignItems: 'center', justifyContent: 'center' }]}>
                <Ionicons name={row.icon as any} size={16} color={colors.primary} />
              </View>
              <View style={{ flex: 1 }}>
                <Text style={{ fontSize: 11, color: colors.textMuted, fontWeight: '600' }}>{row.label}</Text>
                <Text style={{ fontSize: 14, color: colors.text, fontWeight: '600', marginTop: 1 }}>{row.value}</Text>
              </View>
            </View>
          ))
        )}
      </View>

      {/* Affiliate Partner Shortcut Card */}
      <Pressable
        onPress={() => router.push('/(tabs)/affiliate' as any)}
        style={[
          styles.card,
          {
            marginHorizontal: 16,
            marginTop: 16,
            backgroundColor: scheme === 'dark' ? '#064E3B' : '#ECFDF5',
            borderColor: '#A7F3D0',
            flexDirection: 'row',
            alignItems: 'center',
            gap: 14,
            padding: 16,
          },
        ]}>
        <View
          style={{
            width: 44,
            height: 44,
            borderRadius: 14,
            backgroundColor: '#059669',
            alignItems: 'center',
            justifyContent: 'center',
          }}>
          <Ionicons name="sparkles" size={22} color="#FFFFFF" />
        </View>
        <View style={{ flex: 1 }}>
          <Text style={{ fontSize: 15, fontWeight: '800', color: scheme === 'dark' ? '#ECFDF5' : '#065F46' }}>
            Affiliate Partner Program
          </Text>
          <Text style={{ fontSize: 12, color: scheme === 'dark' ? '#A7F3D0' : '#047857', marginTop: 2 }}>
            Earn up to ৳300 per product referral. Tap to open portal.
          </Text>
        </View>
        <Ionicons name="chevron-forward" size={18} color={scheme === 'dark' ? '#A7F3D0' : '#059669'} />
      </Pressable>

      {/* Menu items */}
      <View style={[styles.card, { marginHorizontal: 16, marginTop: 16, backgroundColor: colors.surface, borderColor: colors.border }]}>
        {[
          { icon: 'shield-checkmark-outline', label: 'Privacy Policy', color: colors.primary },
          { icon: 'document-text-outline', label: 'Terms of Service', color: colors.primary },
          { icon: 'headset-outline', label: 'Help & Support', color: colors.primary },
          { icon: 'star-outline', label: 'Rate the App', color: colors.accent },
        ].map((item, i) => (
          <Pressable key={i} style={[{ flexDirection: 'row', alignItems: 'center', paddingVertical: 14, gap: 14 }, i > 0 && { borderTopWidth: 1, borderTopColor: colors.border }]}>
            <Ionicons name={item.icon as any} size={20} color={item.color} />
            <Text style={{ flex: 1, fontSize: 14, fontWeight: '600', color: colors.text }}>{item.label}</Text>
            <Ionicons name="chevron-forward" size={16} color={colors.textMuted} />
          </Pressable>
        ))}
      </View>

      {/* Sign Out */}
      <Pressable onPress={handleLogout} style={[styles.btn, { backgroundColor: '#fee2e2', marginHorizontal: 16, marginTop: 16 }]}>
        <Ionicons name="log-out-outline" size={18} color="#b91c1c" />
        <Text style={{ color: '#b91c1c', fontWeight: '800', fontSize: 15 }}>Sign Out</Text>
      </Pressable>
    </ScrollView>
  );
}

export default function AccountScreen() {
  const scheme = useColorScheme();
  const colors = Colors[scheme === 'dark' ? 'dark' : 'light'];
  const { isLoggedIn } = useAuth();
  const [screen, setScreen] = useState<Screen>('login');

  return (
    <SafeAreaView style={[{ flex: 1, backgroundColor: colors.background }]} edges={['top']}>
      {/* Header */}
      {!isLoggedIn && (
        <View style={[styles.header, { backgroundColor: colors.surface, borderBottomColor: colors.border }]}>
          <View style={{ flexDirection: 'row', gap: 2 }}>
            <Pressable
              onPress={() => setScreen('login')}
              style={[styles.tabPill, { backgroundColor: screen === 'login' ? colors.primary : 'transparent' }]}>
              <Text style={{ color: screen === 'login' ? '#fff' : colors.textMuted, fontWeight: '700', fontSize: 14 }}>Sign In</Text>
            </Pressable>
            <Pressable
              onPress={() => setScreen('register')}
              style={[styles.tabPill, { backgroundColor: screen === 'register' ? colors.primary : 'transparent' }]}>
              <Text style={{ color: screen === 'register' ? '#fff' : colors.textMuted, fontWeight: '700', fontSize: 14 }}>Register</Text>
            </Pressable>
          </View>
        </View>
      )}
      {isLoggedIn ? (
        <ProfileScreen colors={colors} />
      ) : screen === 'login' ? (
        <LoginScreen onSwitch={setScreen} colors={colors} />
      ) : (
        <RegisterScreen onSwitch={setScreen} colors={colors} />
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  header: {
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: StyleSheet.hairlineWidth,
  },
  tabPill: {
    paddingHorizontal: 20,
    paddingVertical: 8,
    borderRadius: 10,
  },
  bigLogo: {
    width: 80,
    height: 80,
    borderRadius: 24,
    alignItems: 'center',
    justifyContent: 'center',
    ...Shadow.md,
  },
  card: {
    padding: 20,
    borderRadius: 20,
    borderWidth: StyleSheet.hairlineWidth,
    ...Shadow.sm,
  },
  btn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    paddingVertical: 14,
    borderRadius: 14,
    ...Shadow.sm,
  },
  infoBox: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 10,
    padding: 14,
    borderRadius: 14,
  },
  // Profile
  profileHero: {
    alignItems: 'center',
    paddingTop: 32,
    paddingBottom: 28,
    gap: 4,
  },
  avatarRing: {
    width: 92,
    height: 92,
    borderRadius: 46,
    backgroundColor: 'rgba(255,255,255,0.3)',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 12,
  },
  avatar: {
    width: 80,
    height: 80,
    borderRadius: 40,
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarText: { fontSize: 28, fontWeight: '900' },
  profileName: { fontSize: 22, fontWeight: '900', color: '#fff' },
  profilePhone: { fontSize: 14, color: 'rgba(255,255,255,0.8)', marginTop: 2 },
  profileEmail: { fontSize: 12, color: 'rgba(255,255,255,0.65)', marginTop: 1 },
  quickActions: {
    flexDirection: 'row',
    marginHorizontal: 16,
    marginTop: -20,
    borderRadius: 20,
    borderWidth: StyleSheet.hairlineWidth,
    paddingVertical: 16,
    ...Shadow.md,
  },
  quickAction: { flex: 1, alignItems: 'center', gap: 8 },
  quickIcon: {
    width: 48,
    height: 48,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
  },
  quickLabel: { fontSize: 11, fontWeight: '700', textAlign: 'center' },
});
