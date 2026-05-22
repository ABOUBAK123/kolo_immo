import React, {useState} from 'react';
import {
  Alert,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
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
import {colors, spacing, typography} from '../../utils/theme';

type Props = {
  navigation: NativeStackNavigationProp<AuthStackParamList, 'Register'>;
};

const COUNTRIES = ['CI', 'SN', 'BF', 'ML', 'BJ', 'TG'];
const ROLES: {value: 'tenant' | 'owner' | 'both'; label: string}[] = [
  {value: 'tenant', label: '🏠 Locataire'},
  {value: 'owner', label: '🔑 Propriétaire'},
  {value: 'both', label: '🔄 Les deux'},
];

export const RegisterScreen: React.FC<Props> = ({navigation}) => {
  const {login} = useAuth();
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [password, setPassword] = useState('');
  const [role, setRole] = useState<'tenant' | 'owner' | 'both'>('tenant');
  const [country, setCountry] = useState('CI');
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});

  const handleRegister = async () => {
    setErrors({});
    if (!name.trim()) {
      setErrors(e => ({...e, name: 'Le nom est requis.'}));
      return;
    }
    if (!email && !phone) {
      Alert.alert('Erreur', 'Email ou téléphone requis.');
      return;
    }
    if (!password || password.length < 8) {
      setErrors(e => ({...e, password: 'Minimum 8 caractères.'}));
      return;
    }
    setLoading(true);
    try {
      const payload: any = {name, password, role, country};
      if (email) payload.email = email;
      if (phone) payload.phone = phone;

      const res = await authApi.register(payload);
      const {user, token} = res.data;

      if (user.phone && !user.phone_verified_at) {
        navigation.navigate('OTP', {phone: user.phone, userId: user.id});
      } else {
        await login(user, token);
      }
    } catch (err: any) {
      const apiErrors = err.response?.data?.errors ?? {};
      if (Object.keys(apiErrors).length > 0) {
        const mapped: Record<string, string> = {};
        Object.entries(apiErrors).forEach(([k, v]) => {
          mapped[k] = Array.isArray(v) ? v[0] : (v as string);
        });
        setErrors(mapped);
      } else {
        Alert.alert('Erreur', err.response?.data?.message ?? 'Inscription échouée.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <KeyboardAvoidingView
      style={styles.flex}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <ScrollView
        contentContainerStyle={styles.container}
        keyboardShouldPersistTaps="handled">

        <View style={styles.header}>
          <TouchableOpacity onPress={() => navigation.goBack()} style={styles.back}>
            <Text style={styles.backArrow}>←</Text>
          </TouchableOpacity>
          <Text style={styles.title}>Créer un compte</Text>
        </View>

        <View style={styles.card}>
          <Input
            label="Nom complet *"
            value={name}
            onChangeText={setName}
            placeholder="Jean Dupont"
            error={errors.name}
          />
          <Input
            label="Email"
            value={email}
            onChangeText={setEmail}
            placeholder="jean@email.com"
            keyboardType="email-address"
            autoCapitalize="none"
            error={errors.email}
          />
          <Input
            label="Téléphone"
            value={phone}
            onChangeText={setPhone}
            placeholder="+225 07 00 00 00 00"
            keyboardType="phone-pad"
            error={errors.phone}
          />
          <Input
            label="Mot de passe *"
            value={password}
            onChangeText={setPassword}
            placeholder="Minimum 8 caractères"
            secureTextEntry
            error={errors.password}
          />

          {/* Role selector */}
          <Text style={styles.sectionLabel}>Je suis *</Text>
          <View style={styles.roleRow}>
            {ROLES.map(r => (
              <TouchableOpacity
                key={r.value}
                style={[styles.roleBtn, role === r.value && styles.roleBtnActive]}
                onPress={() => setRole(r.value)}>
                <Text
                  style={[
                    styles.roleBtnText,
                    role === r.value && styles.roleBtnTextActive,
                  ]}>
                  {r.label}
                </Text>
              </TouchableOpacity>
            ))}
          </View>

          {/* Country selector */}
          <Text style={styles.sectionLabel}>Pays</Text>
          <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.countryRow}>
            {COUNTRIES.map(c => (
              <TouchableOpacity
                key={c}
                style={[styles.countryBtn, country === c && styles.countryBtnActive]}
                onPress={() => setCountry(c)}>
                <Text style={[styles.countryText, country === c && styles.countryTextActive]}>
                  {c}
                </Text>
              </TouchableOpacity>
            ))}
          </ScrollView>

          <Button
            title="Créer mon compte"
            onPress={handleRegister}
            loading={loading}
            fullWidth
            style={styles.btn}
          />
        </View>

        <View style={styles.footer}>
          <Text style={styles.footerText}>Déjà un compte ? </Text>
          <TouchableOpacity onPress={() => navigation.navigate('Login')}>
            <Text style={styles.footerLink}>Se connecter</Text>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
};

const styles = StyleSheet.create({
  flex: {flex: 1, backgroundColor: colors.background},
  container: {flexGrow: 1, padding: spacing.lg, paddingTop: spacing.xl},
  header: {flexDirection: 'row', alignItems: 'center', marginBottom: 24},
  back: {marginRight: 12},
  backArrow: {fontSize: 22, color: colors.primary},
  title: {...typography.h2, color: colors.text},
  card: {
    backgroundColor: colors.surface,
    borderRadius: 24,
    padding: spacing.lg,
    elevation: 4,
    shadowColor: '#000',
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.07,
    shadowRadius: 12,
  },
  sectionLabel: {...typography.label, color: colors.text, marginBottom: 8, marginTop: 4},
  roleRow: {flexDirection: 'row', gap: 8, marginBottom: 16},
  roleBtn: {
    flex: 1,
    paddingVertical: 10,
    borderRadius: 12,
    borderWidth: 1.5,
    borderColor: colors.border,
    alignItems: 'center',
  },
  roleBtnActive: {borderColor: colors.primary, backgroundColor: colors.primary + '10'},
  roleBtnText: {fontSize: 12, fontWeight: '600', color: colors.textSecondary},
  roleBtnTextActive: {color: colors.primary},
  countryRow: {flexDirection: 'row', marginBottom: 16},
  countryBtn: {
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 999,
    borderWidth: 1.5,
    borderColor: colors.border,
    marginRight: 8,
  },
  countryBtnActive: {borderColor: colors.primary, backgroundColor: colors.primary},
  countryText: {fontSize: 13, fontWeight: '600', color: colors.textSecondary},
  countryTextActive: {color: '#fff'},
  btn: {marginTop: 8},
  footer: {flexDirection: 'row', justifyContent: 'center', marginTop: 24},
  footerText: {...typography.body, color: colors.textSecondary},
  footerLink: {...typography.body, color: colors.primary, fontWeight: '700'},
});
