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
  navigation: NativeStackNavigationProp<AuthStackParamList, 'Login'>;
};

export const LoginScreen: React.FC<Props> = ({navigation}) => {
  const {login} = useAuth();
  const [identifier, setIdentifier] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [usePhone, setUsePhone] = useState(false);

  const handleLogin = async () => {
    if (!identifier.trim() || !password) {
      Alert.alert('Erreur', 'Veuillez remplir tous les champs.');
      return;
    }
    setLoading(true);
    try {
      const payload = usePhone
        ? {phone: identifier, password}
        : {email: identifier, password};
      const res = await authApi.login(payload);
      await login(res.data.user, res.data.token);
    } catch (err: any) {
      const msg =
        err.response?.data?.message ?? 'Email/téléphone ou mot de passe incorrect.';
      Alert.alert('Erreur de connexion', msg);
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

        {/* Logo */}
        <View style={styles.logoWrap}>
          <View style={styles.logoCircle}>
            <Text style={styles.logoText}>K</Text>
          </View>
          <Text style={styles.logoName}>Kolo Immo</Text>
          <Text style={styles.tagline}>Location de résidences meublées</Text>
        </View>

        {/* Card */}
        <View style={styles.card}>
          <Text style={styles.cardTitle}>Connexion</Text>
          <Text style={styles.cardSub}>
            {usePhone ? 'Entrez votre numéro de téléphone' : 'Entrez votre adresse email'}
          </Text>

          <TouchableOpacity
            style={styles.switchMode}
            onPress={() => setUsePhone(!usePhone)}>
            <Text style={styles.switchText}>
              {usePhone ? '📧 Utiliser l\'email' : '📱 Utiliser le téléphone'}
            </Text>
          </TouchableOpacity>

          <Input
            label={usePhone ? 'Numéro de téléphone' : 'Email'}
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
            secureTextEntry
          />

          <Button
            title="Se connecter"
            onPress={handleLogin}
            loading={loading}
            fullWidth
            style={styles.loginBtn}
          />
        </View>

        {/* Register link */}
        <View style={styles.footer}>
          <Text style={styles.footerText}>Pas encore de compte ? </Text>
          <TouchableOpacity onPress={() => navigation.navigate('Register')}>
            <Text style={styles.footerLink}>S'inscrire</Text>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
};

const styles = StyleSheet.create({
  flex: {flex: 1, backgroundColor: colors.background},
  container: {flexGrow: 1, justifyContent: 'center', padding: spacing.lg},
  logoWrap: {alignItems: 'center', marginBottom: 32},
  logoCircle: {
    width: 72,
    height: 72,
    borderRadius: 24,
    backgroundColor: colors.primary,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 12,
  },
  logoText: {color: '#fff', fontSize: 36, fontWeight: '800'},
  logoName: {fontSize: 26, fontWeight: '800', color: colors.primary},
  tagline: {...typography.caption, color: colors.textSecondary, marginTop: 4},
  card: {
    backgroundColor: colors.surface,
    borderRadius: 24,
    padding: spacing.lg,
    shadowColor: '#000',
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.07,
    shadowRadius: 12,
    elevation: 4,
  },
  cardTitle: {...typography.h2, color: colors.text, marginBottom: 4},
  cardSub: {...typography.body, color: colors.textSecondary, marginBottom: 16},
  switchMode: {
    alignSelf: 'flex-start',
    backgroundColor: colors.primary + '12',
    paddingHorizontal: 14,
    paddingVertical: 7,
    borderRadius: 999,
    marginBottom: 20,
  },
  switchText: {fontSize: 13, fontWeight: '600', color: colors.primary},
  loginBtn: {marginTop: 8},
  footer: {
    flexDirection: 'row',
    justifyContent: 'center',
    marginTop: 24,
  },
  footerText: {...typography.body, color: colors.textSecondary},
  footerLink: {
    ...typography.body,
    color: colors.primary,
    fontWeight: '700',
  },
});
