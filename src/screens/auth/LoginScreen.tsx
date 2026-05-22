import React, {useState} from 'react';
import {
  Alert,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  StatusBar,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import {NativeStackNavigationProp} from '@react-navigation/native-stack';
import {authApi} from '../../api/auth';
import {Button} from '../../components/Button';
import {Input} from '../../components/Input';
import {useAuth} from '../../store/AuthContext';
import {AuthStackParamList} from '../../types';
import {colors, radius, shadows, spacing, typography} from '../../utils/theme';

type Props = {
  navigation: NativeStackNavigationProp<AuthStackParamList, 'Login'>;
};

export const LoginScreen: React.FC<Props> = ({navigation}) => {
  const {login} = useAuth();
  const [identifier, setIdentifier] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [usePhone, setUsePhone] = useState(false);
  const [loading, setLoading] = useState(false);

  const handleLogin = async () => {
    if (!identifier.trim() || !password) {
      Alert.alert('Champs requis', 'Veuillez remplir tous les champs.');
      return;
    }
    setLoading(true);
    try {
      const payload = usePhone ? {phone: identifier, password} : {email: identifier, password};
      const res = await authApi.login(payload);
      await login(res.data.data.user, res.data.data.token);
    } catch (err: any) {
      Alert.alert(
        'Connexion échouée',
        err.response?.data?.message ?? 'Identifiants incorrects.',
      );
    } finally {
      setLoading(false);
    }
  };

  return (
    <KeyboardAvoidingView
      style={styles.flex}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <StatusBar barStyle="light-content" backgroundColor={colors.primary} />

      <ScrollView
        contentContainerStyle={styles.scroll}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}>

        {/* Hero */}
        <View style={styles.hero}>
          <View style={styles.logoWrap}>
            <Text style={styles.logoLetter}>K</Text>
          </View>
          <Text style={styles.appName}>Kolo Immo</Text>
          <Text style={styles.tagline}>Locations meublées en Afrique de l'Ouest</Text>
        </View>

        {/* Form card */}
        <View style={styles.card}>
          <Text style={styles.cardTitle}>Bon retour 👋</Text>
          <Text style={styles.cardSub}>Connectez-vous à votre compte</Text>

          {/* Toggle email / phone */}
          <View style={styles.toggle}>
            {(['email', 'phone'] as const).map(mode => (
              <TouchableOpacity
                key={mode}
                style={[styles.toggleBtn, (mode === 'phone') === usePhone && styles.toggleActive]}
                onPress={() => setUsePhone(mode === 'phone')}>
                <Text style={[
                  styles.toggleText,
                  (mode === 'phone') === usePhone && styles.toggleTextActive,
                ]}>
                  {mode === 'email' ? '📧  Email' : '📱  Téléphone'}
                </Text>
              </TouchableOpacity>
            ))}
          </View>

          <Input
            label={usePhone ? 'Numéro de téléphone' : 'Adresse email'}
            value={identifier}
            onChangeText={setIdentifier}
            placeholder={usePhone ? '+225 07 00 00 00 00' : 'votre@email.com'}
            keyboardType={usePhone ? 'phone-pad' : 'email-address'}
            autoCapitalize="none"
            autoCorrect={false}
          />

          <Input
            label="Mot de passe"
            value={password}
            onChangeText={setPassword}
            placeholder="••••••••"
            secureTextEntry={!showPassword}
            rightIcon={
              <TouchableOpacity onPress={() => setShowPassword(v => !v)}>
                <Text style={styles.eyeIcon}>{showPassword ? '🙈' : '👁️'}</Text>
              </TouchableOpacity>
            }
          />

          <Button
            title="Se connecter"
            onPress={handleLogin}
            loading={loading}
            fullWidth
            size="lg"
            style={styles.loginBtn}
          />

          <View style={styles.divider}>
            <View style={styles.dividerLine} />
            <Text style={styles.dividerText}>ou</Text>
            <View style={styles.dividerLine} />
          </View>

          <TouchableOpacity
            style={styles.registerBtn}
            onPress={() => navigation.navigate('Register')}
            activeOpacity={0.8}>
            <Text style={styles.registerText}>
              Pas encore de compte ?{' '}
              <Text style={styles.registerLink}>Créer un compte</Text>
            </Text>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
};

const styles = StyleSheet.create({
  flex: { flex: 1, backgroundColor: colors.primary },
  scroll: { flexGrow: 1 },

  // Hero
  hero: {
    alignItems: 'center',
    paddingTop: spacing.xl + 24,
    paddingBottom: spacing.xl,
    backgroundColor: colors.primary,
  },
  logoWrap: {
    width: 72,
    height: 72,
    borderRadius: 22,
    backgroundColor: colors.accent,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 14,
    ...shadows.md,
  },
  logoLetter: { color: '#fff', fontSize: 38, fontWeight: '900' },
  appName: { ...typography.h1, color: '#fff', marginBottom: 6 },
  tagline: { ...typography.bodySm, color: 'rgba(255,255,255,0.72)', textAlign: 'center' },

  // Card
  card: {
    flex: 1,
    backgroundColor: colors.background,
    borderTopLeftRadius: 32,
    borderTopRightRadius: 32,
    padding: spacing.lg,
    paddingTop: spacing.xl,
    minHeight: '65%',
  },
  cardTitle: { ...typography.h2, color: colors.text, marginBottom: 4 },
  cardSub:   { ...typography.body, color: colors.textSecondary, marginBottom: 24 },

  // Toggle
  toggle: {
    flexDirection: 'row',
    backgroundColor: colors.gray100,
    borderRadius: radius.lg,
    padding: 4,
    marginBottom: 20,
  },
  toggleBtn: {
    flex: 1,
    paddingVertical: 9,
    borderRadius: radius.md,
    alignItems: 'center',
  },
  toggleActive: { backgroundColor: colors.surface, ...shadows.xs },
  toggleText: { ...typography.label, color: colors.textSecondary },
  toggleTextActive: { color: colors.primary, fontWeight: '600' },

  eyeIcon: { fontSize: 16 },

  loginBtn: { marginTop: 8 },

  divider: {
    flexDirection: 'row',
    alignItems: 'center',
    marginVertical: 20,
    gap: 10,
  },
  dividerLine: { flex: 1, height: 1, backgroundColor: colors.border },
  dividerText: { ...typography.caption, color: colors.textTertiary },

  registerBtn: { alignItems: 'center', paddingVertical: 4 },
  registerText: { ...typography.body, color: colors.textSecondary },
  registerLink: { color: colors.primary, fontWeight: '700' },
});
