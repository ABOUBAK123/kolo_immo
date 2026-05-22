import React, {useRef, useState} from 'react';
import {
  Alert,
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
import {colors, spacing, typography} from '../../utils/theme';

type Props = {
  navigation: NativeStackNavigationProp<AuthStackParamList, 'OTP'>;
  route: RouteProp<AuthStackParamList, 'OTP'>;
};

export const OtpScreen: React.FC<Props> = ({navigation, route}) => {
  const {phone, userId} = route.params;
  const {login} = useAuth();
  const [otp, setOtp] = useState(['', '', '', '', '', '']);
  const [loading, setLoading] = useState(false);
  const refs = Array.from({length: 6}, () => useRef<TextInput>(null));

  const handleChange = (value: string, index: number) => {
    const newOtp = [...otp];
    newOtp[index] = value;
    setOtp(newOtp);
    if (value && index < 5) {
      refs[index + 1].current?.focus();
    }
  };

  const handleKeyPress = (key: string, index: number) => {
    if (key === 'Backspace' && !otp[index] && index > 0) {
      refs[index - 1].current?.focus();
    }
  };

  const handleVerify = async () => {
    const code = otp.join('');
    if (code.length < 6) {
      Alert.alert('Erreur', 'Entrez le code à 6 chiffres.');
      return;
    }
    setLoading(true);
    try {
      const res = await authApi.verifyOtp(phone, code, userId);
      await login(res.data.user, res.data.token);
    } catch (err: any) {
      Alert.alert('Erreur', err.response?.data?.message ?? 'Code invalide ou expiré.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      <TouchableOpacity onPress={() => navigation.goBack()} style={styles.back}>
        <Text style={styles.backArrow}>←</Text>
      </TouchableOpacity>

      <View style={styles.content}>
        <View style={styles.iconWrap}>
          <Text style={styles.icon}>📱</Text>
        </View>
        <Text style={styles.title}>Vérification</Text>
        <Text style={styles.sub}>
          Code envoyé au{'\n'}
          <Text style={styles.phone}>{phone}</Text>
        </Text>

        <View style={styles.otpRow}>
          {otp.map((digit, i) => (
            <TextInput
              key={i}
              ref={refs[i]}
              style={[styles.otpBox, digit ? styles.otpBoxFilled : null]}
              value={digit}
              onChangeText={v => handleChange(v.replace(/\D/g, '').slice(-1), i)}
              onKeyPress={({nativeEvent}) => handleKeyPress(nativeEvent.key, i)}
              keyboardType="number-pad"
              maxLength={1}
              textAlign="center"
              selectTextOnFocus
            />
          ))}
        </View>

        <Button
          title="Valider"
          onPress={handleVerify}
          loading={loading}
          fullWidth
          style={styles.btn}
        />

        <TouchableOpacity style={styles.resend}>
          <Text style={styles.resendText}>Renvoyer le code</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {flex: 1, backgroundColor: colors.background, padding: spacing.lg},
  back: {marginTop: 8, marginBottom: 16},
  backArrow: {fontSize: 22, color: colors.primary},
  content: {flex: 1, justifyContent: 'center', alignItems: 'center'},
  iconWrap: {
    width: 80,
    height: 80,
    borderRadius: 24,
    backgroundColor: colors.primary + '15',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 24,
  },
  icon: {fontSize: 36},
  title: {...typography.h1, color: colors.text, marginBottom: 12, textAlign: 'center'},
  sub: {...typography.body, color: colors.textSecondary, textAlign: 'center', lineHeight: 24},
  phone: {color: colors.primary, fontWeight: '700'},
  otpRow: {flexDirection: 'row', gap: 12, marginVertical: 32},
  otpBox: {
    width: 48,
    height: 56,
    borderWidth: 2,
    borderColor: colors.border,
    borderRadius: 14,
    fontSize: 22,
    fontWeight: '700',
    color: colors.text,
    backgroundColor: colors.surface,
    textAlign: 'center',
  },
  otpBoxFilled: {borderColor: colors.primary, backgroundColor: colors.primary + '08'},
  btn: {width: '100%'},
  resend: {marginTop: 20},
  resendText: {color: colors.primary, fontWeight: '600', fontSize: 14},
});
