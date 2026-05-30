import React, {useEffect, useRef, useState} from 'react';
import {
  Alert,
  StatusBar,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import {NativeStackNavigationProp} from '@react-navigation/native-stack';
import {RouteProp} from '@react-navigation/native';
import {authApi} from '../../api/auth';
import {Button} from '../../components/Button';
import {useAuth} from '../../store/AuthContext';
import {AuthStackParamList} from '../../types';
import {colors, radius, shadows, spacing, typography} from '../../utils/theme';

type Props = {
  navigation: NativeStackNavigationProp<AuthStackParamList, 'OTP'>;
  route: RouteProp<AuthStackParamList, 'OTP'>;
};

const RESEND_DELAY = 60;

export const OtpScreen: React.FC<Props> = ({navigation, route}) => {
  const {phone} = route.params;
  const {login} = useAuth();
  const [otp, setOtp] = useState(['', '', '', '', '', '']);
  const [loading, setLoading] = useState(false);
  const [resending, setResending] = useState(false);
  const [countdown, setCountdown] = useState(RESEND_DELAY);
  const ref0 = useRef<TextInput>(null);
  const ref1 = useRef<TextInput>(null);
  const ref2 = useRef<TextInput>(null);
  const ref3 = useRef<TextInput>(null);
  const ref4 = useRef<TextInput>(null);
  const ref5 = useRef<TextInput>(null);
  const refs = [ref0, ref1, ref2, ref3, ref4, ref5];

  useEffect(() => {
    if (countdown <= 0) return;
    const t = setTimeout(() => setCountdown(c => c - 1), 1000);
    return () => clearTimeout(t);
  }, [countdown]);

  const handleChange = (value: string, index: number) => {
    const digit = value.replace(/\D/g, '').slice(-1);
    const next = [...otp];
    next[index] = digit;
    setOtp(next);
    if (digit && index < 5) refs[index + 1].current?.focus();
    if (!digit && index > 0) refs[index - 1].current?.focus();
  };

  const handleKeyPress = (key: string, index: number) => {
    if (key === 'Backspace' && !otp[index] && index > 0) {
      refs[index - 1].current?.focus();
    }
  };

  const handleVerify = async () => {
    const code = otp.join('');
    if (code.length < 6) {
      Alert.alert('Code incomplet', 'Entrez les 6 chiffres du code.');
      return;
    }
    setLoading(true);
    try {
      const res = await authApi.verifyOtp(phone, code);
      const data = res.data.data;
      await login(data.user, data.token);
    } catch (err: any) {
      Alert.alert('Code invalide', err.response?.data?.message ?? 'Code incorrect ou expiré.');
      setOtp(['', '', '', '', '', '']);
      refs[0].current?.focus();
    } finally {
      setLoading(false);
    }
  };

  const isComplete = otp.every(d => d !== '');

  return (
    <View style={styles.screen}>
      <StatusBar barStyle="light-content" backgroundColor={colors.primary} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity style={styles.backBtn} onPress={() => navigation.goBack()}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
      </View>

      {/* Content */}
      <View style={styles.content}>
        <View style={styles.iconWrap}>
          <Text style={styles.iconEmoji}>💬</Text>
        </View>

        <Text style={styles.title}>Code de vérification</Text>
        <Text style={styles.sub}>
          Nous avons envoyé un code à
        </Text>
        <Text style={styles.phone}>{phone}</Text>

        {/* OTP Boxes */}
        <View style={styles.otpRow}>
          {otp.map((digit, i) => (
            <TextInput
              key={i}
              ref={refs[i]}
              style={[styles.box, digit && styles.boxFilled, isComplete && styles.boxComplete]}
              value={digit}
              onChangeText={v => handleChange(v, i)}
              onKeyPress={({nativeEvent}) => handleKeyPress(nativeEvent.key, i)}
              keyboardType="number-pad"
              maxLength={1}
              textAlign="center"
              selectTextOnFocus
              autoFocus={i === 0}
            />
          ))}
        </View>

        <Button
          title="Valider le code"
          onPress={handleVerify}
          loading={loading}
          fullWidth
          size="lg"
          style={styles.btn}
        />

        {/* Resend */}
        <View style={styles.resendRow}>
          {countdown > 0 ? (
            <Text style={styles.resendTimer}>
              Renvoyer dans{' '}
              <Text style={styles.resendTimerBold}>{countdown}s</Text>
            </Text>
          ) : (
            <TouchableOpacity
              disabled={resending}
              onPress={async () => {
                setResending(true);
                try {
                  await authApi.resendOtp(phone);
                  setCountdown(RESEND_DELAY);
                  setOtp(['', '', '', '', '', '']);
                  refs[0].current?.focus();
                } catch {
                  Alert.alert('Erreur', 'Impossible de renvoyer le code. Réessayez.');
                } finally {
                  setResending(false);
                }
              }}>
              <Text style={styles.resendLink}>{resending ? 'Envoi...' : 'Renvoyer le code'}</Text>
            </TouchableOpacity>
          )}
        </View>

        <Text style={styles.hint}>
          Vérifiez aussi vos SMS et WhatsApp
        </Text>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.background },

  header: {
    backgroundColor: colors.primary,
    paddingHorizontal: spacing.lg,
    paddingTop: 48,
    paddingBottom: spacing.lg,
  },
  backBtn: {
    width: 38,
    height: 38,
    borderRadius: radius.full,
    backgroundColor: 'rgba(255,255,255,0.18)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  backArrow: { color: '#fff', fontSize: 20, fontWeight: '700' },

  content: {
    flex: 1,
    alignItems: 'center',
    paddingHorizontal: spacing.xl,
    paddingTop: spacing.xxl,
  },

  iconWrap: {
    width: 88,
    height: 88,
    borderRadius: radius.xxl,
    backgroundColor: colors.primaryFaint,
    borderWidth: 2,
    borderColor: colors.primaryLight,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: spacing.lg,
    ...shadows.sm,
  },
  iconEmoji: { fontSize: 40 },

  title: { ...typography.h2, color: colors.text, textAlign: 'center', marginBottom: 8 },
  sub:   { ...typography.body, color: colors.textSecondary, textAlign: 'center' },
  phone: {
    ...typography.h4,
    color: colors.primary,
    textAlign: 'center',
    marginTop: 4,
    marginBottom: spacing.xl,
  },

  otpRow: {
    flexDirection: 'row',
    gap: 10,
    marginBottom: spacing.xl,
  },
  box: {
    width: 50,
    height: 60,
    borderWidth: 2,
    borderColor: colors.border,
    borderRadius: radius.lg,
    fontSize: 26,
    fontWeight: '800',
    color: colors.text,
    backgroundColor: colors.surface,
    ...shadows.xs,
  },
  boxFilled: {
    borderColor: colors.primary,
    backgroundColor: colors.primaryFaint,
    color: colors.primary,
  },
  boxComplete: { borderColor: colors.success },

  btn: { width: '100%', marginBottom: spacing.lg },

  resendRow: { marginTop: 4 },
  resendTimer: { ...typography.body, color: colors.textSecondary, textAlign: 'center' },
  resendTimerBold: { fontWeight: '700', color: colors.textMed },
  resendLink: { ...typography.body, color: colors.primary, fontWeight: '700', textAlign: 'center' },

  hint: {
    ...typography.caption,
    color: colors.textTertiary,
    textAlign: 'center',
    marginTop: spacing.lg,
    paddingHorizontal: spacing.md,
  },
});
