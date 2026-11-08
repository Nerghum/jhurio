import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  FlatList,
  Image,
  Modal,
  Platform,
  RefreshControl,
  SafeAreaView,
  ScrollView,
  Share,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  useColorScheme,
  View,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

import { Colors } from '@/constants/theme';
import { Config } from '@/constants/config';
import { AffiliateService } from '@/services/affiliateApi';

type AffiliateData = {
  id: number;
  name: string;
  phone: string;
  email?: string;
  code: string;
  status: 'pending' | 'approved' | 'rejected' | 'suspended';
  balance: number;
  pending_balance: number;
  lifetime_earnings: number;
  payout_method: string;
  payout_account: string;
  referral_link?: string;
};

type ReferralItem = {
  id: number;
  order_id: number;
  order_amount: number;
  commission_amount: number;
  status: 'pending' | 'approved' | 'cancelled';
  order_status: string;
  customer_name?: string;
  items_breakdown?: any[];
  created_at: string;
};

type PayoutItem = {
  id: number;
  amount: number;
  payout_method: string;
  account_details: string;
  status: 'pending' | 'paid' | 'rejected';
  transaction_id?: string;
  admin_notes?: string;
  requested_at?: string;
  processed_at?: string;
  created_at?: string;
};

export default function AffiliateScreen() {
  const scheme = useColorScheme();
  const isDark = scheme === 'dark';
  const colors = Colors[isDark ? 'dark' : 'light'];

  // Auth & View state
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [affiliate, setAffiliate] = useState<AffiliateData | null>(null);
  const [isPendingReview, setIsPendingReview] = useState(false);
  const [pendingNotice, setPendingNotice] = useState<string | null>(null);

  // Tab mode for unauthenticated state: 'register' | 'login'
  const [authMode, setAuthMode] = useState<'register' | 'login'>('register');

  // Registration Form
  const [name, setName] = useState('');
  const [phone, setPhone] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [payoutMethod, setPayoutMethod] = useState<'bkash' | 'nagad' | 'rocket' | 'bank'>('bkash');
  const [payoutAccount, setPayoutAccount] = useState('');
  const [notes, setNotes] = useState('');
  const [submittingAuth, setSubmittingAuth] = useState(false);

  // Login Form
  const [loginPhone, setLoginPhone] = useState('');
  const [loginPassword, setLoginPassword] = useState('');

  // Dashboard state
  const [dashboardTab, setDashboardTab] = useState<'links' | 'referrals' | 'payouts'>('links');
  const [referrals, setReferrals] = useState<ReferralItem[]>([]);
  const [payouts, setPayouts] = useState<PayoutItem[]>([]);
  const [products, setProducts] = useState<any[]>([]);
  const [copiedLink, setCopiedLink] = useState(false);

  // Payout Request Modal
  const [payoutModalVisible, setPayoutModalVisible] = useState(false);
  const [payoutAmount, setPayoutAmount] = useState('');
  const [payoutModalMethod, setPayoutModalMethod] = useState('bkash');
  const [payoutModalAccount, setPayoutModalAccount] = useState('');
  const [submittingPayout, setSubmittingPayout] = useState(false);

  // Toast / Status Message
  const [toastMessage, setToastMessage] = useState<string | null>(null);

  const showToast = (msg: string) => {
    setToastMessage(msg);
    setTimeout(() => setToastMessage(null), 3000);
  };

  useEffect(() => {
    checkInitialState();
  }, []);

  const checkInitialState = async () => {
    try {
      setLoading(true);
      const token = await AffiliateService.getStoredToken();
      if (!token) {
        setAffiliate(null);
        setLoading(false);
        return;
      }

      await loadDashboard();
    } catch (err) {
      setAffiliate(null);
    } finally {
      setLoading(false);
    }
  };

  const loadDashboard = async () => {
    try {
      const res = await AffiliateService.getDashboard();
      if (res.ok && res.data) {
        setAffiliate(res.data.affiliate);
        setReferrals(res.data.referrals || []);
        setPayouts(res.data.payouts || []);
        setIsPendingReview(false);

        // Fetch products for links
        const linksRes = await AffiliateService.getLinks();
        if (linksRes.ok && linksRes.data?.products) {
          setProducts(linksRes.data.products);
        }
      } else if (res.status === 403 && res.data?.status === 'pending') {
        setIsPendingReview(true);
        setPendingNotice(res.data.message);
      } else if (res.status === 401) {
        await AffiliateService.logout();
        setAffiliate(null);
      }
    } catch (err) {
      console.warn('Dashboard load error:', err);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    if (affiliate) {
      await loadDashboard();
    } else {
      await checkInitialState();
    }
    setRefreshing(false);
  };

  // Submit Registration
  const handleRegister = async () => {
    if (!name.trim() || !phone.trim() || !password.trim() || !payoutAccount.trim()) {
      Alert.alert('Required Fields', 'Please fill in Name, Phone, Password, and Wallet Number.');
      return;
    }

    try {
      setSubmittingAuth(true);
      const res = await AffiliateService.register({
        name: name.trim(),
        phone: phone.trim(),
        email: email.trim() || undefined,
        password: password.trim(),
        payout_method: payoutMethod,
        payout_account: payoutAccount.trim(),
        notes: notes.trim() || undefined,
      });

      if (res.ok) {
        setIsPendingReview(true);
        setPendingNotice(res.data?.message || 'Your application is under review by admin.');
        Alert.alert(
          'Application Submitted! 🎉',
          res.data?.message || 'Admin will review and approve your affiliate registration soon.'
        );
      } else {
        const msg = res.data?.message || Object.values(res.data?.errors || {})[0] || 'Registration failed.';
        Alert.alert('Error', String(msg));
      }
    } catch (err: any) {
      Alert.alert('Connection Error', err?.message || 'Unable to register right now.');
    } finally {
      setSubmittingAuth(false);
    }
  };

  // Submit Login
  const handleLogin = async () => {
    if (!loginPhone.trim() || !loginPassword.trim()) {
      Alert.alert('Required Fields', 'Please enter your phone number and password.');
      return;
    }

    try {
      setSubmittingAuth(true);
      const res = await AffiliateService.login(loginPhone.trim(), loginPassword.trim());

      if (res.ok) {
        await loadDashboard();
        showToast('Logged in successfully!');
      } else if (res.data?.status === 'pending') {
        setIsPendingReview(true);
        setPendingNotice(res.data.message);
      } else {
        Alert.alert('Login Failed', res.data?.message || 'Invalid credentials.');
      }
    } catch (err: any) {
      Alert.alert('Connection Error', err?.message || 'Unable to log in.');
    } finally {
      setSubmittingAuth(false);
    }
  };

  // Logout
  const handleLogout = async () => {
    Alert.alert('Log Out', 'Are you sure you want to log out of the affiliate portal?', [
      { text: 'Cancel', style: 'cancel' },
      {
        text: 'Log Out',
        style: 'destructive',
        onPress: async () => {
          await AffiliateService.logout();
          setAffiliate(null);
          setIsPendingReview(false);
          showToast('Logged out successfully');
        },
      },
    ]);
  };

  // Safe Copy / Share Helper
  const safeCopy = async (text: string) => {
    try {
      await Share.share({
        message: text,
        title: 'Jhurio Affiliate Link',
      });
    } catch (e) {
      console.log('Share error:', e);
    }
  };

  // Copy Store Link
  const copyStoreLink = async () => {
    if (!affiliate?.code) return;
    const link = `https://jhurio.com?ref=${affiliate.code}`;
    await safeCopy(link);
    setCopiedLink(true);
    showToast('Referral link copied to clipboard!');
    setTimeout(() => setCopiedLink(false), 2500);
  };

  // Share Link
  const shareStoreLink = async () => {
    if (!affiliate?.code) return;
    const link = `https://jhurio.com?ref=${affiliate.code}`;
    try {
      await Share.share({
        message: `Shop premium products on Jhurio with special deals! Check it out: ${link}`,
        url: link,
      });
    } catch {}
  };

  // Copy Product Link
  const copyProductLink = async (pLink: string) => {
    await safeCopy(pLink);
    showToast('Product link copied!');
  };

  // Share Product Link
  const shareProductLink = async (name: string, pLink: string) => {
    try {
      await Share.share({
        message: `Buy ${name} on Jhurio: ${pLink}`,
        url: pLink,
      });
    } catch {}
  };

  // Request Payout
  const handleRequestPayout = async () => {
    const amt = parseFloat(payoutAmount);
    const balance = affiliate?.balance || 0;

    if (isNaN(amt) || amt < 100) {
      Alert.alert('Invalid Amount', 'Minimum payout request is ৳100.');
      return;
    }
    if (amt > balance) {
      Alert.alert('Insufficient Balance', `You can withdraw up to ৳${balance}.`);
      return;
    }
    if (!payoutModalAccount.trim()) {
      Alert.alert('Missing Details', 'Please enter your wallet or account number.');
      return;
    }

    try {
      setSubmittingPayout(true);
      const res = await AffiliateService.requestPayout(amt, payoutModalMethod, payoutModalAccount.trim());

      if (res.ok) {
        setPayoutModalVisible(false);
        setPayoutAmount('');
        await loadDashboard();
        Alert.alert(
          'Payout Requested! 💸',
          'Your payout request has been sent to admin. Admin will process payment and attach the Transaction ID.'
        );
      } else {
        Alert.alert('Request Failed', res.data?.message || 'Unable to request payout.');
      }
    } catch (err: any) {
      Alert.alert('Error', err?.message || 'Network error.');
    } finally {
      setSubmittingPayout(false);
    }
  };

  if (loading) {
    return (
      <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]}>
        <View style={styles.centerContainer}>
          <ActivityIndicator size="large" color={colors.primary} />
          <Text style={[styles.loadingText, { color: colors.textSecondary }]}>Loading Affiliate Portal...</Text>
        </View>
      </SafeAreaView>
    );
  }

  // =========================================================================
  // VIEW: PENDING REVIEW
  // =========================================================================
  if (isPendingReview && !affiliate) {
    return (
      <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]}>
        <ScrollView
          contentContainerStyle={styles.scrollContent}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[colors.primary]} />}>
          
          <View style={styles.pendingCard}>
            <View style={styles.pendingIconWrap}>
              <Ionicons name="time-outline" size={42} color="#D97706" />
            </View>
            <Text style={styles.pendingTitle}>Application Under Review</Text>
            <Text style={styles.pendingDescription}>
              {pendingNotice ||
                'Your registration has been submitted and is currently being reviewed by our admin team.'}
            </Text>

            <View style={styles.pendingInfoBox}>
              <Ionicons name="information-circle-outline" size={20} color="#D97706" />
              <Text style={styles.pendingInfoText}>
                Once approved, you will have instant access to your referral link generator, live commission tracking, and payout requests.
              </Text>
            </View>

            <TouchableOpacity style={styles.refreshBtn} onPress={onRefresh}>
              <Ionicons name="refresh" size={18} color="#FFFFFF" />
              <Text style={styles.refreshBtnText}>Check Status</Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.backBtn}
              onPress={() => {
                setIsPendingReview(false);
                setAuthMode('login');
              }}>
              <Text style={styles.backBtnText}>Switch to Login</Text>
            </TouchableOpacity>
          </View>
        </ScrollView>
      </SafeAreaView>
    );
  }

  // =========================================================================
  // VIEW: NOT LOGGED IN (REGISTER OR LOGIN)
  // =========================================================================
  if (!affiliate) {
    return (
      <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]}>
        <ScrollView
          contentContainerStyle={styles.scrollContent}
          showsVerticalScrollIndicator={false}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[colors.primary]} />}>
          
          {/* Hero Header */}
          <View style={[styles.heroCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
            <View style={styles.badgeRow}>
              <View style={styles.partnerBadge}>
                <Ionicons name="sparkles" size={12} color={colors.primary} />
                <Text style={[styles.partnerBadgeText, { color: colors.primary }]}>JHURIO PARTNER PROGRAM</Text>
              </View>
            </View>
            <Text style={[styles.heroTitle, { color: colors.text }]}>Earn with Jhurio Affiliate</Text>
            <Text style={[styles.heroSubtitle, { color: colors.textSecondary }]}>
              Promote products and earn up to ৳300 per product. Direct payouts to your bKash or Nagad wallet.
            </Text>

            {/* Commission Rules Pill */}
            <View style={styles.rulePill}>
              <Ionicons name="cash-outline" size={18} color="#059669" />
              <Text style={styles.rulePillText}>
                Revenue Share: <Text style={styles.boldText}>150 Tk or 10%</Text> (whichever is more) up to <Text style={styles.boldText}>300 Tk</Text> per product! Bundles count as single product.
              </Text>
            </View>
          </View>

          {/* Segmented Switcher */}
          <View style={[styles.segmentedWrap, { backgroundColor: isDark ? '#1F2937' : '#F3F4F6' }]}>
            <TouchableOpacity
              style={[styles.segmentBtn, authMode === 'register' && styles.segmentActive]}
              onPress={() => setAuthMode('register')}>
              <Text
                style={[
                  styles.segmentText,
                  { color: authMode === 'register' ? '#FFFFFF' : colors.textSecondary },
                ]}>
                Apply Now
              </Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={[styles.segmentBtn, authMode === 'login' && styles.segmentActive]}
              onPress={() => setAuthMode('login')}>
              <Text
                style={[
                  styles.segmentText,
                  { color: authMode === 'login' ? '#FFFFFF' : colors.textSecondary },
                ]}>
                Partner Login
              </Text>
            </TouchableOpacity>
          </View>

          {/* Registration Form */}
          {authMode === 'register' ? (
            <View style={[styles.formCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
              <Text style={[styles.formHeader, { color: colors.text }]}>Submit Application</Text>
              <Text style={[styles.formSubHeader, { color: colors.textSecondary }]}>
                Admin will review your registration and activate your partner portal.
              </Text>

              <View style={styles.inputGroup}>
                <Text style={[styles.inputLabel, { color: colors.textSecondary }]}>FULL NAME *</Text>
                <View style={[styles.inputContainer, { backgroundColor: isDark ? '#1F2937' : '#F9FAFB', borderColor: colors.border }]}>
                  <Ionicons name="person-outline" size={18} color={colors.textSecondary} style={styles.inputIcon} />
                  <TextInput
                    style={[styles.textInput, { color: colors.text }]}
                    placeholder="e.g. Ashik Rahman"
                    placeholderTextColor="#9CA3AF"
                    value={name}
                    onChangeText={setName}
                  />
                </View>
              </View>

              <View style={styles.inputGroup}>
                <Text style={[styles.inputLabel, { color: colors.textSecondary }]}>PHONE NUMBER (LOGIN ID) *</Text>
                <View style={[styles.inputContainer, { backgroundColor: isDark ? '#1F2937' : '#F9FAFB', borderColor: colors.border }]}>
                  <Ionicons name="call-outline" size={18} color={colors.textSecondary} style={styles.inputIcon} />
                  <TextInput
                    style={[styles.textInput, { color: colors.text }]}
                    placeholder="01XXXXXXXXX"
                    placeholderTextColor="#9CA3AF"
                    keyboardType="phone-pad"
                    value={phone}
                    onChangeText={setPhone}
                  />
                </View>
              </View>

              <View style={styles.inputGroup}>
                <Text style={[styles.inputLabel, { color: colors.textSecondary }]}>EMAIL (OPTIONAL)</Text>
                <View style={[styles.inputContainer, { backgroundColor: isDark ? '#1F2937' : '#F9FAFB', borderColor: colors.border }]}>
                  <Ionicons name="mail-outline" size={18} color={colors.textSecondary} style={styles.inputIcon} />
                  <TextInput
                    style={[styles.textInput, { color: colors.text }]}
                    placeholder="you@domain.com"
                    placeholderTextColor="#9CA3AF"
                    keyboardType="email-address"
                    autoCapitalize="none"
                    value={email}
                    onChangeText={setEmail}
                  />
                </View>
              </View>

              <View style={styles.inputGroup}>
                <Text style={[styles.inputLabel, { color: colors.textSecondary }]}>PASSWORD *</Text>
                <View style={[styles.inputContainer, { backgroundColor: isDark ? '#1F2937' : '#F9FAFB', borderColor: colors.border }]}>
                  <Ionicons name="lock-closed-outline" size={18} color={colors.textSecondary} style={styles.inputIcon} />
                  <TextInput
                    style={[styles.textInput, { color: colors.text }]}
                    placeholder="Min 6 characters"
                    placeholderTextColor="#9CA3AF"
                    secureTextEntry
                    value={password}
                    onChangeText={setPassword}
                  />
                </View>
              </View>

              {/* Payout Method Selection */}
              <View style={styles.inputGroup}>
                <Text style={[styles.inputLabel, { color: colors.textSecondary }]}>PREFERRED PAYOUT METHOD *</Text>
                <View style={styles.pillsRow}>
                  {(['bkash', 'nagad', 'rocket', 'bank'] as const).map((m) => (
                    <TouchableOpacity
                      key={m}
                      style={[
                        styles.pillBtn,
                        payoutMethod === m && styles.pillBtnActive,
                        { borderColor: payoutMethod === m ? colors.primary : colors.border },
                      ]}
                      onPress={() => setPayoutMethod(m)}>
                      <Text
                        style={[
                          styles.pillBtnText,
                          { color: payoutMethod === m ? colors.primary : colors.textSecondary },
                          payoutMethod === m && styles.boldText,
                        ]}>
                        {m.toUpperCase()}
                      </Text>
                    </TouchableOpacity>
                  ))}
                </View>
              </View>

              <View style={styles.inputGroup}>
                <Text style={[styles.inputLabel, { color: colors.textSecondary }]}>WALLET / ACCOUNT NUMBER *</Text>
                <View style={[styles.inputContainer, { backgroundColor: isDark ? '#1F2937' : '#F9FAFB', borderColor: colors.border }]}>
                  <Ionicons name="wallet-outline" size={18} color={colors.textSecondary} style={styles.inputIcon} />
                  <TextInput
                    style={[styles.textInput, { color: colors.text }]}
                    placeholder="01XXXXXXXXX"
                    placeholderTextColor="#9CA3AF"
                    keyboardType="numeric"
                    value={payoutAccount}
                    onChangeText={setPayoutAccount}
                  />
                </View>
              </View>

              <View style={styles.inputGroup}>
                <Text style={[styles.inputLabel, { color: colors.textSecondary }]}>WHERE WILL YOU PROMOTE? (OPTIONAL)</Text>
                <View style={[styles.inputContainer, { backgroundColor: isDark ? '#1F2937' : '#F9FAFB', borderColor: colors.border, height: 64 }]}>
                  <TextInput
                    style={[styles.textInput, { color: colors.text, height: 56 }]}
                    placeholder="Facebook page, YouTube, TikTok, WhatsApp..."
                    placeholderTextColor="#9CA3AF"
                    multiline
                    value={notes}
                    onChangeText={setNotes}
                  />
                </View>
              </View>

              <TouchableOpacity
                style={[styles.primaryActionBtn, { backgroundColor: colors.primary }]}
                disabled={submittingAuth}
                onPress={handleRegister}>
                {submittingAuth ? (
                  <ActivityIndicator color="#FFFFFF" />
                ) : (
                  <>
                    <Ionicons name="paper-plane-outline" size={18} color="#FFFFFF" />
                    <Text style={styles.primaryActionText}>Submit Application</Text>
                  </>
                )}
              </TouchableOpacity>
            </View>
          ) : (
            <View style={[styles.formCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
              <Text style={[styles.formHeader, { color: colors.text }]}>Partner Portal Login</Text>
              <Text style={[styles.formSubHeader, { color: colors.textSecondary }]}>
                Log in to generate affiliate links, view sales, and request payouts.
              </Text>

              <View style={styles.inputGroup}>
                <Text style={[styles.inputLabel, { color: colors.textSecondary }]}>REGISTERED PHONE *</Text>
                <View style={[styles.inputContainer, { backgroundColor: isDark ? '#1F2937' : '#F9FAFB', borderColor: colors.border }]}>
                  <Ionicons name="call-outline" size={18} color={colors.textSecondary} style={styles.inputIcon} />
                  <TextInput
                    style={[styles.textInput, { color: colors.text }]}
                    placeholder="01XXXXXXXXX"
                    placeholderTextColor="#9CA3AF"
                    keyboardType="phone-pad"
                    value={loginPhone}
                    onChangeText={setLoginPhone}
                  />
                </View>
              </View>

              <View style={styles.inputGroup}>
                <Text style={[styles.inputLabel, { color: colors.textSecondary }]}>PASSWORD *</Text>
                <View style={[styles.inputContainer, { backgroundColor: isDark ? '#1F2937' : '#F9FAFB', borderColor: colors.border }]}>
                  <Ionicons name="lock-closed-outline" size={18} color={colors.textSecondary} style={styles.inputIcon} />
                  <TextInput
                    style={[styles.textInput, { color: colors.text }]}
                    placeholder="Your password"
                    placeholderTextColor="#9CA3AF"
                    secureTextEntry
                    value={loginPassword}
                    onChangeText={setLoginPassword}
                  />
                </View>
              </View>

              <TouchableOpacity
                style={[styles.primaryActionBtn, { backgroundColor: colors.primary }]}
                disabled={submittingAuth}
                onPress={handleLogin}>
                {submittingAuth ? (
                  <ActivityIndicator color="#FFFFFF" />
                ) : (
                  <>
                    <Ionicons name="log-in-outline" size={20} color="#FFFFFF" />
                    <Text style={styles.primaryActionText}>Log In to Portal</Text>
                  </>
                )}
              </TouchableOpacity>
            </View>
          )}
        </ScrollView>
      </SafeAreaView>
    );
  }

  // =========================================================================
  // VIEW: APPROVED AFFILIATE PORTAL (DASHBOARD)
  // =========================================================================
  const baseUrl = Config.SITE_URL || 'https://jhurio.com';
  const primaryLink = `${baseUrl}?ref=${affiliate.code}`;

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]}>
      {/* Toast Notification */}
      {toastMessage && (
        <View style={styles.toastWrap}>
          <Ionicons name="checkmark-circle" size={18} color="#10B981" />
          <Text style={styles.toastText}>{toastMessage}</Text>
        </View>
      )}

      <ScrollView
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[colors.primary]} />}>
        
        {/* Affiliate Header Card */}
        <View style={[styles.portalHeader, { backgroundColor: colors.surface, borderColor: colors.border }]}>
          <View style={styles.headerLeft}>
            <View style={[styles.avatarCircle, { backgroundColor: colors.primary }]}>
              <Text style={styles.avatarText}>{affiliate.name?.charAt(0).toUpperCase() || 'P'}</Text>
            </View>
            <View>
              <View style={styles.partnerNameRow}>
                <Text style={[styles.partnerName, { color: colors.text }]} numberOfLines={1}>
                  {affiliate.name}
                </Text>
                <View style={styles.approvedBadge}>
                  <Ionicons name="checkmark-circle" size={12} color="#059669" />
                  <Text style={styles.approvedBadgeText}>Approved</Text>
                </View>
              </View>
              <Text style={[styles.partnerCode, { color: colors.primary }]}>
                Referral Code: <Text style={styles.monoBold}>{affiliate.code}</Text>
              </Text>
            </View>
          </View>

          <TouchableOpacity style={styles.logoutIconBtn} onPress={handleLogout}>
            <Ionicons name="log-out-outline" size={22} color={colors.textSecondary} />
          </TouchableOpacity>
        </View>

        {/* Balance Metric Grid */}
        <View style={styles.metricsGrid}>
          {/* Available Balance (Withdrawable) */}
          <View style={[styles.metricCardPrimary, { backgroundColor: '#059669' }]}>
            <Text style={styles.metricPrimaryLabel}>AVAILABLE BALANCE</Text>
            <Text style={styles.metricPrimaryVal}>৳{Number(affiliate.balance || 0).toFixed(2)}</Text>
            <TouchableOpacity
              style={styles.payoutTriggerBtn}
              onPress={() => {
                setPayoutAmount(String(affiliate.balance > 0 ? affiliate.balance : ''));
                setPayoutModalMethod(affiliate.payout_method || 'bkash');
                setPayoutModalAccount(affiliate.payout_account || '');
                setPayoutModalVisible(true);
              }}>
              <Ionicons name="arrow-up-circle-outline" size={16} color="#059669" />
              <Text style={styles.payoutTriggerText}>Request Payout</Text>
            </TouchableOpacity>
          </View>

          {/* Pending Delivery Balance */}
          <View style={[styles.metricCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
            <Text style={[styles.metricLabel, { color: '#D97706' }]}>PENDING DELIVERY</Text>
            <Text style={[styles.metricVal, { color: colors.text }]}>৳{Number(affiliate.pending_balance || 0).toFixed(2)}</Text>
            <Text style={[styles.metricSub, { color: colors.textSecondary }]}>Credits once delivered</Text>
          </View>

          {/* Lifetime Earnings */}
          <View style={[styles.metricCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
            <Text style={[styles.metricLabel, { color: colors.primary }]}>LIFETIME EARNED</Text>
            <Text style={[styles.metricVal, { color: colors.text }]}>৳{Number(affiliate.lifetime_earnings || 0).toFixed(2)}</Text>
            <Text style={[styles.metricSub, { color: colors.textSecondary }]}>Total commission revenue</Text>
          </View>

          {/* Total Orders Referred */}
          <View style={[styles.metricCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
            <Text style={[styles.metricLabel, { color: colors.textSecondary }]}>TOTAL REFERRALS</Text>
            <Text style={[styles.metricVal, { color: colors.text }]}>{referrals.length}</Text>
            <Text style={[styles.metricSub, { color: colors.textSecondary }]}>Orders placed via your link</Text>
          </View>
        </View>

        {/* Primary Link Share Banner */}
        <View style={[styles.linkShareCard, { backgroundColor: isDark ? '#1E293B' : '#ECFDF5', borderColor: '#A7F3D0' }]}>
          <View style={styles.linkShareHead}>
            <Ionicons name="link-outline" size={20} color="#059669" />
            <Text style={styles.linkShareTitle}>Your Primary Referral Link</Text>
          </View>
          <Text style={styles.linkShareUrl} numberOfLines={1}>{primaryLink}</Text>
          <View style={styles.linkActionsRow}>
            <TouchableOpacity style={styles.copyLinkBtn} onPress={copyStoreLink}>
              <Ionicons name={copiedLink ? 'checkmark' : 'copy-outline'} size={16} color="#FFFFFF" />
              <Text style={styles.copyLinkText}>{copiedLink ? 'Copied!' : 'Copy Link'}</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.shareLinkBtn} onPress={shareStoreLink}>
              <Ionicons name="share-social-outline" size={16} color="#059669" />
              <Text style={styles.shareLinkText}>Share</Text>
            </TouchableOpacity>
          </View>
        </View>

        {/* Commission Rule Explainer */}
        <View style={[styles.ruleCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
          <Ionicons name="information-circle" size={18} color={colors.primary} />
          <Text style={[styles.ruleCardText, { color: colors.textSecondary }]}>
            <Text style={{ fontWeight: 'bold', color: colors.text }}>Earning Rules: </Text>
            You earn ৳150 or 10% of product price (whichever is higher) up to ৳300 per product. Bundle products count as a single product. Commission is awarded when order delivery is successful.
          </Text>
        </View>

        {/* Dashboard Navigation Tabs */}
        <View style={[styles.dashboardTabs, { borderBottomColor: colors.border }]}>
          <TouchableOpacity
            style={[styles.dashTabBtn, dashboardTab === 'links' && [styles.dashTabActive, { borderBottomColor: colors.primary }]]}
            onPress={() => setDashboardTab('links')}>
            <Ionicons name="grid-outline" size={16} color={dashboardTab === 'links' ? colors.primary : colors.textSecondary} />
            <Text style={[styles.dashTabText, { color: dashboardTab === 'links' ? colors.primary : colors.textSecondary }, dashboardTab === 'links' && styles.boldText]}>
              Products
            </Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={[styles.dashTabBtn, dashboardTab === 'referrals' && [styles.dashTabActive, { borderBottomColor: colors.primary }]]}
            onPress={() => setDashboardTab('referrals')}>
            <Ionicons name="cart-outline" size={16} color={dashboardTab === 'referrals' ? colors.primary : colors.textSecondary} />
            <Text style={[styles.dashTabText, { color: dashboardTab === 'referrals' ? colors.primary : colors.textSecondary }, dashboardTab === 'referrals' && styles.boldText]}>
              Orders ({referrals.length})
            </Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={[styles.dashTabBtn, dashboardTab === 'payouts' && [styles.dashTabActive, { borderBottomColor: colors.primary }]]}
            onPress={() => setDashboardTab('payouts')}>
            <Ionicons name="receipt-outline" size={16} color={dashboardTab === 'payouts' ? colors.primary : colors.textSecondary} />
            <Text style={[styles.dashTabText, { color: dashboardTab === 'payouts' ? colors.primary : colors.textSecondary }, dashboardTab === 'payouts' && styles.boldText]}>
              Payouts ({payouts.length})
            </Text>
          </TouchableOpacity>
        </View>

        {/* TAB 1: PRODUCT LINKS */}
        {dashboardTab === 'links' && (
          <View style={styles.tabContent}>
            <Text style={[styles.sectionTitle, { color: colors.text }]}>Promote Specific Products</Text>
            <Text style={[styles.sectionSub, { color: colors.textSecondary }]}>
              Copy a direct link for any product. Visitors will be tracked to your affiliate code.
            </Text>

            {products.map((p) => {
              const pPrice = parseFloat(p.price || 0);
              const tenPct = 0.1 * pPrice;
              const estComm = Math.min(300, Math.max(150, tenPct));
              const pLink = `${baseUrl}/product/${p.slug || p.id}?ref=${affiliate.code}`;

              return (
                <View key={p.id} style={[styles.productLinkCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
                  <Image
                    source={{ uri: p.thumbnail_url || 'https://jhurio.com/assets/images/placeholder.png' }}
                    style={styles.productLinkThumb}
                  />
                  <View style={styles.productLinkInfo}>
                    <Text style={[styles.productLinkName, { color: colors.text }]} numberOfLines={1}>
                      {p.name}
                    </Text>
                    <View style={styles.productPriceRow}>
                      <Text style={[styles.productPrice, { color: colors.text }]}>৳{pPrice}</Text>
                      <View style={styles.commissionTag}>
                        <Text style={styles.commissionTagText}>Earn ৳{estComm.toFixed(0)}</Text>
                      </View>
                    </View>
                    <View style={styles.productActionsRow}>
                      <TouchableOpacity style={styles.btnSmallCopy} onPress={() => copyProductLink(pLink)}>
                        <Ionicons name="copy-outline" size={13} color="#FFFFFF" />
                        <Text style={styles.btnSmallCopyText}>Copy Link</Text>
                      </TouchableOpacity>
                      <TouchableOpacity style={styles.btnSmallShare} onPress={() => shareProductLink(p.name, pLink)}>
                        <Ionicons name="share-outline" size={14} color={colors.primary} />
                      </TouchableOpacity>
                    </View>
                  </View>
                </View>
              );
            })}
          </View>
        )}

        {/* TAB 2: REFERRALS / ORDERS */}
        {dashboardTab === 'referrals' && (
          <View style={styles.tabContent}>
            <Text style={[styles.sectionTitle, { color: colors.text }]}>Referred Customer Orders</Text>
            <Text style={[styles.sectionSub, { color: colors.textSecondary }]}>
              Orders placed by your referrals and commission status.
            </Text>

            {referrals.length === 0 ? (
              <View style={styles.emptyState}>
                <Ionicons name="cart-outline" size={48} color={colors.textSecondary} />
                <Text style={[styles.emptyStateTitle, { color: colors.text }]}>No referral orders yet</Text>
                <Text style={[styles.emptyStateSub, { color: colors.textSecondary }]}>
                  Share your links on social media and WhatsApp to start earning!
                </Text>
              </View>
            ) : (
              referrals.map((ref) => {
                const isDelivered = ['delivered', 'completed'].includes(ref.order_status?.toLowerCase());
                const isCancelled = ['cancelled', 'returned'].includes(ref.order_status?.toLowerCase());

                return (
                  <View key={ref.id} style={[styles.referralCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
                    <View style={styles.referralCardHeader}>
                      <View>
                        <Text style={[styles.referralOrderId, { color: colors.primary }]}>Order #{ref.order_id}</Text>
                        <Text style={[styles.referralDate, { color: colors.textSecondary }]}>
                          {new Date(ref.created_at).toLocaleDateString()}
                        </Text>
                      </View>
                      <View style={styles.referralAmountBox}>
                        <Text style={styles.referralCommLabel}>Commission</Text>
                        <Text style={styles.referralCommAmount}>৳{Number(ref.commission_amount).toFixed(2)}</Text>
                      </View>
                    </View>

                    <View style={styles.statusBadgeRow}>
                      <View
                        style={[
                          styles.statusBadge,
                          isDelivered
                            ? styles.badgeSuccess
                            : isCancelled
                            ? styles.badgeDanger
                            : styles.badgeWarning,
                        ]}>
                        <Ionicons
                          name={isDelivered ? 'checkmark-circle' : isCancelled ? 'close-circle' : 'time'}
                          size={12}
                          color={isDelivered ? '#059669' : isCancelled ? '#DC2626' : '#D97706'}
                        />
                        <Text
                          style={[
                            styles.statusBadgeText,
                            { color: isDelivered ? '#059669' : isCancelled ? '#DC2626' : '#D97706' },
                          ]}>
                          Order: {ref.order_status?.toUpperCase()}
                        </Text>
                      </View>

                      <View
                        style={[
                          styles.statusBadge,
                          ref.status === 'approved'
                            ? styles.badgeSuccess
                            : ref.status === 'cancelled'
                            ? styles.badgeDanger
                            : styles.badgeWarning,
                        ]}>
                        <Text
                          style={[
                            styles.statusBadgeText,
                            {
                              color:
                                ref.status === 'approved'
                                  ? '#059669'
                                  : ref.status === 'cancelled'
                                  ? '#DC2626'
                                  : '#D97706',
                            },
                          ]}>
                          {ref.status === 'approved' ? 'Earned' : ref.status === 'cancelled' ? 'Cancelled' : 'Pending Delivery'}
                        </Text>
                      </View>
                    </View>
                  </View>
                );
              })
            )}
          </View>
        )}

        {/* TAB 3: PAYOUTS */}
        {dashboardTab === 'payouts' && (
          <View style={styles.tabContent}>
            <View style={styles.payoutsHeaderRow}>
              <View>
                <Text style={[styles.sectionTitle, { color: colors.text }]}>Withdrawal History</Text>
                <Text style={[styles.sectionSub, { color: colors.textSecondary }]}>
                  Admin approves with Transaction ID
                </Text>
              </View>
              <TouchableOpacity
                style={[styles.btnNewPayout, { backgroundColor: colors.primary }]}
                onPress={() => {
                  setPayoutAmount(String(affiliate.balance > 0 ? affiliate.balance : ''));
                  setPayoutModalMethod(affiliate.payout_method || 'bkash');
                  setPayoutModalAccount(affiliate.payout_account || '');
                  setPayoutModalVisible(true);
                }}>
                <Ionicons name="add-circle" size={16} color="#FFFFFF" />
                <Text style={styles.btnNewPayoutText}>New Request</Text>
              </TouchableOpacity>
            </View>

            {payouts.length === 0 ? (
              <View style={styles.emptyState}>
                <Ionicons name="wallet-outline" size={48} color={colors.textSecondary} />
                <Text style={[styles.emptyStateTitle, { color: colors.text }]}>No payout requests yet</Text>
                <Text style={[styles.emptyStateSub, { color: colors.textSecondary }]}>
                  Once you reach at least ৳100 in available balance, you can request a payout.
                </Text>
              </View>
            ) : (
              payouts.map((pay) => {
                const isPaid = pay.status === 'paid';
                const isRejected = pay.status === 'rejected';

                return (
                  <View key={pay.id} style={[styles.payoutCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
                    <View style={styles.payoutCardTop}>
                      <View>
                        <Text style={[styles.payoutIdText, { color: colors.textSecondary }]}>Payout #{pay.id}</Text>
                        <Text style={[styles.payoutAmountText, { color: colors.text }]}>৳{Number(pay.amount).toFixed(2)}</Text>
                      </View>
                      <View
                        style={[
                          styles.statusBadge,
                          isPaid ? styles.badgeSuccess : isRejected ? styles.badgeDanger : styles.badgeWarning,
                        ]}>
                        <Text
                          style={[
                            styles.statusBadgeText,
                            { color: isPaid ? '#059669' : isRejected ? '#DC2626' : '#D97706' },
                          ]}>
                          {pay.status.toUpperCase()}
                        </Text>
                      </View>
                    </View>

                    <View style={styles.payoutDetailsRow}>
                      <Text style={[styles.payoutMethodText, { color: colors.textSecondary }]}>
                        {pay.payout_method?.toUpperCase()} &bull; {pay.account_details}
                      </Text>
                    </View>

                    {/* Transaction ID if Paid */}
                    {pay.transaction_id && (
                      <View style={styles.trxBox}>
                        <Ionicons name="shield-checkmark" size={16} color="#059669" />
                        <Text style={styles.trxText}>
                          Transaction ID: <Text style={styles.monoBold}>{pay.transaction_id}</Text>
                        </Text>
                      </View>
                    )}

                    {pay.admin_notes && (
                      <Text style={[styles.adminNoteText, { color: colors.textSecondary }]}>
                        Note: {pay.admin_notes}
                      </Text>
                    )}
                  </View>
                );
              })
            )}
          </View>
        )}
      </ScrollView>

      {/* Payout Request Modal */}
      <Modal
        visible={payoutModalVisible}
        transparent
        animationType="slide"
        onRequestClose={() => setPayoutModalVisible(false)}>
        <View style={styles.modalBackdrop}>
          <View style={[styles.modalContent, { backgroundColor: colors.surface }]}>
            <View style={styles.modalHeader}>
              <Text style={[styles.modalTitle, { color: colors.text }]}>Request Payout</Text>
              <TouchableOpacity
                onPress={() => setPayoutModalVisible(false)}
                hitSlop={{ top: 15, bottom: 15, left: 15, right: 15 }}>
                <Ionicons name="close" size={24} color={colors.textSecondary} />
              </TouchableOpacity>
            </View>

            <Text style={[styles.modalBalanceSub, { color: colors.textSecondary }]}>
              Available Balance:{' '}
              <Text style={{ fontWeight: 'bold', color: '#059669' }}>
                ৳{Number(affiliate.balance || 0).toFixed(2)}
              </Text>
            </Text>

            <View style={styles.inputGroup}>
              <Text style={[styles.inputLabel, { color: colors.textSecondary }]}>AMOUNT TO WITHDRAW (৳) *</Text>
              <View style={[styles.inputContainer, { backgroundColor: isDark ? '#1F2937' : '#F9FAFB', borderColor: colors.border }]}>
                <TextInput
                  style={[styles.textInput, { color: colors.text, fontSize: 18, fontWeight: 'bold' }]}
                  placeholder="Min 100"
                  placeholderTextColor="#9CA3AF"
                  keyboardType="numeric"
                  value={payoutAmount}
                  onChangeText={setPayoutAmount}
                />
              </View>
            </View>

            {/* Quick Amount Buttons */}
            <View style={styles.quickAmountRow}>
              {[100, 200, 500].map((val) => (
                <TouchableOpacity
                  key={val}
                  style={[styles.quickAmtBtn, { borderColor: colors.border }]}
                  onPress={() => setPayoutAmount(String(val))}>
                  <Text style={[styles.quickAmtText, { color: colors.text }]}>৳{val}</Text>
                </TouchableOpacity>
              ))}
              <TouchableOpacity
                style={[styles.quickAmtBtn, { borderColor: colors.primary }]}
                onPress={() => setPayoutAmount(String(affiliate.balance))}>
                <Text style={[styles.quickAmtText, { color: colors.primary, fontWeight: 'bold' }]}>Max</Text>
              </TouchableOpacity>
            </View>

            <View style={styles.inputGroup}>
              <Text style={[styles.inputLabel, { color: colors.textSecondary }]}>WALLET / ACCOUNT NUMBER *</Text>
              <View style={[styles.inputContainer, { backgroundColor: isDark ? '#1F2937' : '#F9FAFB', borderColor: colors.border }]}>
                <TextInput
                  style={[styles.textInput, { color: colors.text }]}
                  placeholder="01XXXXXXXXX"
                  placeholderTextColor="#9CA3AF"
                  value={payoutModalAccount}
                  onChangeText={setPayoutModalAccount}
                />
              </View>
            </View>

            <TouchableOpacity
              style={[styles.primaryActionBtn, { backgroundColor: '#059669', marginTop: 16 }]}
              disabled={submittingPayout}
              onPress={handleRequestPayout}>
              {submittingPayout ? (
                <ActivityIndicator color="#FFFFFF" />
              ) : (
                <>
                  <Ionicons name="checkmark-circle-outline" size={20} color="#FFFFFF" />
                  <Text style={styles.primaryActionText}>Confirm & Send Request</Text>
                </>
              )}
            </TouchableOpacity>
          </View>
        </View>
      </Modal>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  scrollContent: {
    padding: 16,
    paddingBottom: 120,
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 12,
    fontSize: 14,
  },
  boldText: {
    fontWeight: 'bold',
  },
  monoBold: {
    fontFamily: Platform.OS === 'ios' ? 'Courier' : 'monospace',
    fontWeight: 'bold',
  },
  toastWrap: {
    position: 'absolute',
    top: 10,
    left: 20,
    right: 20,
    zIndex: 999,
    backgroundColor: '#064E3B',
    padding: 12,
    borderRadius: 12,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    shadowColor: '#000',
    shadowOpacity: 0.2,
    shadowRadius: 6,
    elevation: 8,
  },
  toastText: {
    color: '#ECFDF5',
    fontSize: 13,
    fontWeight: '600',
  },

  // Hero Card
  heroCard: {
    borderRadius: 20,
    padding: 20,
    borderWidth: 1,
    marginBottom: 16,
  },
  badgeRow: {
    flexDirection: 'row',
    marginBottom: 8,
  },
  partnerBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ECFDF5',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 8,
    gap: 4,
  },
  partnerBadgeText: {
    fontSize: 10,
    fontWeight: '800',
  },
  heroTitle: {
    fontSize: 22,
    fontWeight: '800',
    letterSpacing: -0.5,
  },
  heroSubtitle: {
    fontSize: 13,
    marginTop: 6,
    lineHeight: 18,
  },
  rulePill: {
    marginTop: 14,
    backgroundColor: '#ECFDF5',
    borderRadius: 12,
    padding: 12,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    borderWidth: 1,
    borderColor: '#A7F3D0',
  },
  rulePillText: {
    fontSize: 12,
    color: '#065F46',
    flex: 1,
    lineHeight: 17,
  },

  // Segmented Switcher
  segmentedWrap: {
    flexDirection: 'row',
    borderRadius: 14,
    padding: 4,
    marginBottom: 16,
  },
  segmentBtn: {
    flex: 1,
    paddingVertical: 10,
    borderRadius: 10,
    alignItems: 'center',
  },
  segmentActive: {
    backgroundColor: '#059669',
  },
  segmentText: {
    fontSize: 13,
    fontWeight: '700',
  },

  // Form Card
  formCard: {
    borderRadius: 20,
    padding: 20,
    borderWidth: 1,
  },
  formHeader: {
    fontSize: 18,
    fontWeight: '800',
  },
  formSubHeader: {
    fontSize: 12,
    marginTop: 4,
    marginBottom: 16,
  },
  inputGroup: {
    marginBottom: 14,
  },
  inputLabel: {
    fontSize: 11,
    fontWeight: '700',
    marginBottom: 6,
    letterSpacing: 0.5,
  },
  inputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    borderRadius: 12,
    borderWidth: 1,
    paddingHorizontal: 12,
    height: 48,
  },
  inputIcon: {
    marginRight: 8,
  },
  textInput: {
    flex: 1,
    fontSize: 14,
  },
  pillsRow: {
    flexDirection: 'row',
    gap: 8,
  },
  pillBtn: {
    flex: 1,
    paddingVertical: 8,
    borderRadius: 10,
    borderWidth: 1,
    alignItems: 'center',
  },
  pillBtnActive: {
    backgroundColor: '#ECFDF5',
  },
  pillBtnText: {
    fontSize: 11,
  },
  primaryActionBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 14,
    paddingVertical: 14,
    gap: 8,
    marginTop: 8,
  },
  primaryActionText: {
    color: '#FFFFFF',
    fontSize: 15,
    fontWeight: '700',
  },

  // Pending Status View
  pendingCard: {
    backgroundColor: '#FFFBEB',
    borderRadius: 24,
    padding: 24,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#FCD34D',
    marginTop: 20,
  },
  pendingIconWrap: {
    width: 72,
    height: 72,
    borderRadius: 36,
    backgroundColor: '#FEF3C7',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 16,
  },
  pendingTitle: {
    fontSize: 20,
    fontWeight: '800',
    color: '#92400E',
    marginBottom: 8,
  },
  pendingDescription: {
    fontSize: 13,
    color: '#B45309',
    textAlign: 'center',
    lineHeight: 18,
    marginBottom: 16,
  },
  pendingInfoBox: {
    flexDirection: 'row',
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: 12,
    gap: 10,
    marginBottom: 20,
  },
  pendingInfoText: {
    fontSize: 12,
    color: '#92400E',
    flex: 1,
    lineHeight: 17,
  },
  refreshBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#D97706',
    borderRadius: 12,
    paddingVertical: 12,
    paddingHorizontal: 24,
    gap: 8,
    width: '100%',
    marginBottom: 12,
  },
  refreshBtnText: {
    color: '#FFFFFF',
    fontWeight: '700',
    fontSize: 14,
  },
  backBtn: {
    paddingVertical: 8,
  },
  backBtnText: {
    color: '#B45309',
    fontSize: 13,
    fontWeight: '600',
  },

  // Dashboard Styles
  portalHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderRadius: 20,
    padding: 16,
    borderWidth: 1,
    marginBottom: 16,
  },
  headerLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    flex: 1,
  },
  avatarCircle: {
    width: 44,
    height: 44,
    borderRadius: 22,
    justifyContent: 'center',
    alignItems: 'center',
  },
  avatarText: {
    color: '#FFFFFF',
    fontSize: 18,
    fontWeight: '800',
  },
  partnerNameRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  partnerName: {
    fontSize: 16,
    fontWeight: '800',
    maxWidth: 160,
  },
  approvedBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ECFDF5',
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 6,
    gap: 3,
  },
  approvedBadgeText: {
    color: '#059669',
    fontSize: 10,
    fontWeight: '700',
  },
  partnerCode: {
    fontSize: 12,
    marginTop: 2,
  },
  logoutIconBtn: {
    padding: 8,
  },

  // Metrics Grid
  metricsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
    marginBottom: 16,
  },
  metricCardPrimary: {
    width: '100%',
    borderRadius: 18,
    padding: 18,
    shadowColor: '#059669',
    shadowOpacity: 0.25,
    shadowRadius: 8,
    elevation: 4,
  },
  metricPrimaryLabel: {
    color: '#D1FAE5',
    fontSize: 11,
    fontWeight: '700',
    letterSpacing: 0.5,
  },
  metricPrimaryVal: {
    color: '#FFFFFF',
    fontSize: 28,
    fontWeight: '900',
    marginTop: 2,
    marginBottom: 12,
  },
  payoutTriggerBtn: {
    backgroundColor: '#FFFFFF',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 8,
    paddingHorizontal: 14,
    borderRadius: 10,
    alignSelf: 'flex-start',
    gap: 6,
  },
  payoutTriggerText: {
    color: '#059669',
    fontWeight: '800',
    fontSize: 12,
  },
  metricCard: {
    width: '48%',
    borderRadius: 16,
    padding: 14,
    borderWidth: 1,
  },
  metricLabel: {
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 0.5,
  },
  metricVal: {
    fontSize: 20,
    fontWeight: '800',
    marginTop: 2,
  },
  metricSub: {
    fontSize: 10,
    marginTop: 2,
  },

  // Link Share Banner
  linkShareCard: {
    borderRadius: 16,
    padding: 14,
    borderWidth: 1,
    marginBottom: 16,
  },
  linkShareHead: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    marginBottom: 6,
  },
  linkShareTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: '#065F46',
  },
  linkShareUrl: {
    fontSize: 12,
    color: '#047857',
    fontFamily: Platform.OS === 'ios' ? 'Courier' : 'monospace',
    fontWeight: '600',
    marginBottom: 10,
  },
  linkActionsRow: {
    flexDirection: 'row',
    gap: 10,
  },
  copyLinkBtn: {
    flex: 1,
    backgroundColor: '#059669',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 8,
    borderRadius: 10,
    gap: 6,
  },
  copyLinkText: {
    color: '#FFFFFF',
    fontSize: 12,
    fontWeight: '700',
  },
  shareLinkBtn: {
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#A7F3D0',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 10,
    gap: 6,
  },
  shareLinkText: {
    color: '#059669',
    fontSize: 12,
    fontWeight: '700',
  },

  ruleCard: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    borderRadius: 14,
    padding: 12,
    borderWidth: 1,
    gap: 10,
    marginBottom: 16,
  },
  ruleCardText: {
    fontSize: 11,
    lineHeight: 16,
    flex: 1,
  },

  // Dashboard Tabs
  dashboardTabs: {
    flexDirection: 'row',
    borderBottomWidth: 1,
    marginBottom: 16,
  },
  dashTabBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    paddingHorizontal: 14,
    gap: 6,
    borderBottomWidth: 2,
    borderBottomColor: 'transparent',
  },
  dashTabActive: {
    borderBottomColor: '#059669',
  },
  dashTabText: {
    fontSize: 13,
  },

  tabContent: {
    marginBottom: 30,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '800',
  },
  sectionSub: {
    fontSize: 11,
    marginTop: 2,
    marginBottom: 14,
  },

  // Product Link Cards
  productLinkCard: {
    flexDirection: 'row',
    borderRadius: 16,
    borderWidth: 1,
    padding: 12,
    marginBottom: 10,
    alignItems: 'center',
  },
  productLinkThumb: {
    width: 64,
    height: 64,
    borderRadius: 10,
    backgroundColor: '#E5E7EB',
  },
  productLinkInfo: {
    flex: 1,
    marginLeft: 12,
  },
  productLinkName: {
    fontSize: 13,
    fontWeight: '700',
  },
  productPriceRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginTop: 4,
  },
  productPrice: {
    fontSize: 13,
    fontWeight: '800',
  },
  commissionTag: {
    backgroundColor: '#ECFDF5',
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 6,
  },
  commissionTagText: {
    color: '#059669',
    fontSize: 11,
    fontWeight: '700',
  },
  productActionsRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginTop: 8,
  },
  btnSmallCopy: {
    backgroundColor: '#059669',
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
    gap: 4,
  },
  btnSmallCopyText: {
    color: '#FFFFFF',
    fontSize: 11,
    fontWeight: '700',
  },
  btnSmallShare: {
    padding: 4,
  },

  // Referral Order Cards
  referralCard: {
    borderRadius: 16,
    borderWidth: 1,
    padding: 14,
    marginBottom: 10,
  },
  referralCardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
  },
  referralOrderId: {
    fontSize: 14,
    fontWeight: '800',
  },
  referralDate: {
    fontSize: 11,
    marginTop: 2,
  },
  referralAmountBox: {
    alignItems: 'flex-end',
  },
  referralCommLabel: {
    fontSize: 10,
    color: '#9CA3AF',
    textTransform: 'uppercase',
  },
  referralCommAmount: {
    fontSize: 16,
    fontWeight: '900',
    color: '#059669',
  },
  statusBadgeRow: {
    flexDirection: 'row',
    gap: 8,
    marginTop: 10,
  },
  statusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 8,
    gap: 4,
  },
  statusBadgeText: {
    fontSize: 10,
    fontWeight: '700',
  },
  badgeSuccess: {
    backgroundColor: '#ECFDF5',
  },
  badgeWarning: {
    backgroundColor: '#FFFBEB',
  },
  badgeDanger: {
    backgroundColor: '#FEF2F2',
  },

  // Payout Cards
  payoutsHeaderRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  btnNewPayout: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 10,
    gap: 4,
  },
  btnNewPayoutText: {
    color: '#FFFFFF',
    fontSize: 12,
    fontWeight: '700',
  },
  payoutCard: {
    borderRadius: 16,
    borderWidth: 1,
    padding: 14,
    marginBottom: 10,
  },
  payoutCardTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
  },
  payoutIdText: {
    fontSize: 11,
    fontWeight: '600',
  },
  payoutAmountText: {
    fontSize: 18,
    fontWeight: '900',
    marginTop: 2,
  },
  payoutDetailsRow: {
    marginTop: 6,
  },
  payoutMethodText: {
    fontSize: 12,
    fontWeight: '600',
  },
  trxBox: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ECFDF5',
    padding: 8,
    borderRadius: 8,
    marginTop: 8,
    gap: 6,
  },
  trxText: {
    fontSize: 12,
    color: '#065F46',
  },
  adminNoteText: {
    fontSize: 11,
    fontStyle: 'italic',
    marginTop: 6,
  },

  emptyState: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 40,
  },
  emptyStateTitle: {
    fontSize: 16,
    fontWeight: '700',
    marginTop: 10,
  },
  emptyStateSub: {
    fontSize: 12,
    textAlign: 'center',
    marginTop: 4,
    maxWidth: 240,
  },

  // Modal
  modalBackdrop: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.6)',
    justifyContent: 'flex-end',
  },
  modalContent: {
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    padding: 24,
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: '800',
  },
  modalBalanceSub: {
    fontSize: 13,
    marginTop: 4,
    marginBottom: 16,
  },
  quickAmountRow: {
    flexDirection: 'row',
    gap: 8,
    marginBottom: 14,
  },
  quickAmtBtn: {
    flex: 1,
    paddingVertical: 6,
    borderRadius: 8,
    borderWidth: 1,
    alignItems: 'center',
  },
  quickAmtText: {
    fontSize: 12,
  },
});
