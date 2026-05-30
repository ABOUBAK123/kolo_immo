import React, {useState} from 'react';
import {
  KeyboardAvoidingView,
  Modal,
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
  navigation: NativeStackNavigationProp<AuthStackParamList, 'Register'>;
};

const STEPS = ['Infos', 'Sécurité', 'Profil'];
const ROLES: {value: 'tenant' | 'owner' | 'both' | 'agent'; emoji: string; label: string; desc: string}[] = [
  {value: 'tenant', emoji: '🏠', label: 'Locataire',        desc: 'Je cherche un logement'},
  {value: 'owner',  emoji: '🔑', label: 'Propriétaire',     desc: 'Je loue mon bien'},
  {value: 'both',   emoji: '🔄', label: 'Les deux',         desc: 'Locataire et propriétaire'},
  {value: 'agent',  emoji: '💼', label: 'Agent immobilier', desc: 'Je gère des biens (3% commission)'},
];
const COUNTRIES: {name: string; code: string}[] = [
  {name: "Bénin",         code: 'BJ'},
  {name: "Burkina Faso",  code: 'BF'},
  {name: "Cap-Vert",      code: 'CV'},
  {name: "Côte d'Ivoire", code: 'CI'},
  {name: "Gambie",        code: 'GM'},
  {name: "Ghana",         code: 'GH'},
  {name: "Guinée",        code: 'GN'},
  {name: "Guinée-Bissau", code: 'GW'},
  {name: "Libéria",       code: 'LR'},
  {name: "Mali",          code: 'ML'},
  {name: "Mauritanie",    code: 'MR'},
  {name: "Niger",         code: 'NE'},
  {name: "Nigeria",       code: 'NG'},
  {name: "Sénégal",       code: 'SN'},
  {name: "Sierra Leone",  code: 'SL'},
  {name: "Togo",          code: 'TG'},
];

export const RegisterScreen: React.FC<Props> = ({navigation}) => {
  useAuth();
  const [step, setStep] = useState(0);

  // Step 0 — Infos
  const [name, setName]       = useState('');
  const [email, setEmail]     = useState('');
  const [phone, setPhone]     = useState('');
  // Step 1 — Sécurité
  const [password, setPassword]   = useState('');
  const [confirm, setConfirm]     = useState('');
  const [showPwd, setShowPwd]     = useState(false);
  // Step 2 — Profil
  const [role, setRole]           = useState<'tenant' | 'owner' | 'both' | 'agent'>('tenant');
  const [country, setCountry]     = useState('CI');

  const [errors, setErrors]   = useState<Record<string, string>>({});
  const [loading, setLoading] = useState(false);
  const [modal, setModal]     = useState<{visible: boolean; message: string; needsActivation: boolean; phone: string | null; userId: number | null}>({
    visible: false, message: '', needsActivation: false, phone: null, userId: null,
  });

  const validateStep = () => {
    const e: Record<string, string> = {};
    if (step === 0) {
      if (!name.trim()) e.name = 'Nom requis';
      if (!email && !phone) e.contact = 'Email ou téléphone requis';
    }
    if (step === 1) {
      if (!password || password.length < 8) e.password = 'Minimum 8 caractères';
      else if (!/[a-zA-Z]/.test(password)) e.password = 'Le mot de passe doit contenir des lettres';
      else if (!/[0-9]/.test(password)) e.password = 'Le mot de passe doit contenir des chiffres';
      if (password !== confirm) e.confirm = 'Les mots de passe ne correspondent pas';
    }
    setErrors(e);
    return Object.keys(e).length === 0;
  };

  const nextStep = () => {
    if (validateStep()) setStep(s => s + 1);
  };

  const handleRegister = async () => {
    setErrors({});
    setLoading(true);
    try {
      const payload: any = {name, password, role, country};
      if (email) payload.email = email;
      if (phone) payload.phone = phone;

      const res = await authApi.register(payload);
      const regData = res.data.data;
      const userPhone: string | null = regData.phone ?? phone ?? null;

      setModal({
        visible: true,
        message: res.data.message ?? 'Votre compte a bien été créé.',
        needsActivation: regData.needs_activation ?? false,
        phone: userPhone,
        userId: regData.user_id,
      });
    } catch (err: any) {
      const apiErrors = err.response?.data?.errors ?? {};
      if (Object.keys(apiErrors).length > 0) {
        const mapped: Record<string, string> = {};
        Object.entries(apiErrors).forEach(([k, v]) => {
          mapped[k] = Array.isArray(v) ? v[0] : (v as string);
        });
        setErrors(mapped);
        setStep(0);
      } else {
        setModal({
          visible: true,
          message: err.response?.data?.message ?? 'Inscription échouée. Veuillez réessayer.',
          needsActivation: false,
          phone: null,
          userId: null,
        });
      }
    } finally {
      setLoading(false);
    }
  };

  const handleModalClose = () => {
    const {phone: p, userId, needsActivation} = modal;
    setModal(m => ({...m, visible: false}));
    if (p && userId && !needsActivation) {
      navigation.navigate('OTP', {phone: p, userId});
    } else {
      navigation.navigate('Login');
    }
  };

  return (
    <KeyboardAvoidingView
      style={styles.flex}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <StatusBar barStyle="light-content" backgroundColor={colors.primary} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity
          style={styles.backBtn}
          onPress={() => (step > 0 ? setStep(s => s - 1) : navigation.goBack())}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Créer un compte</Text>
        <View style={{width: 36}} />
      </View>

      {/* Progress dots */}
      <View style={styles.progress}>
        {STEPS.map((label, i) => (
          <React.Fragment key={i}>
            <View style={styles.stepItem}>
              <View style={[styles.stepDot, i <= step && styles.stepDotActive, i < step && styles.stepDotDone]}>
                {i < step
                  ? <Text style={styles.stepCheck}>✓</Text>
                  : <Text style={[styles.stepNum, i === step && styles.stepNumActive]}>{i + 1}</Text>
                }
              </View>
              <Text style={[styles.stepLabel, i === step && styles.stepLabelActive]}>{label}</Text>
            </View>
            {i < STEPS.length - 1 && (
              <View style={[styles.stepLine, i < step && styles.stepLineDone]} />
            )}
          </React.Fragment>
        ))}
      </View>

      <ScrollView
        contentContainerStyle={styles.body}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={true}>

        {/* ── Step 0 ── */}
        {step === 0 && (
          <View>
            <Text style={styles.stepTitle}>Vos informations</Text>
            <Text style={styles.stepSub}>Dites-nous qui vous êtes</Text>

            <Input
              label="Nom complet *"
              value={name}
              onChangeText={t => { setName(t); setErrors(e => ({...e, name: ''})); }}
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
              hint="Au moins email ou téléphone requis"
            />
            {errors.contact ? <Text style={styles.errorMsg}>{errors.contact}</Text> : null}

            <Button title="Suivant →" onPress={nextStep} fullWidth size="lg" style={{marginTop: 8}} />
          </View>
        )}

        {/* ── Step 1 ── */}
        {step === 1 && (
          <View>
            <Text style={styles.stepTitle}>Sécurité du compte</Text>
            <Text style={styles.stepSub}>Choisissez un mot de passe fort</Text>

            <Input
              label="Mot de passe *"
              value={password}
              onChangeText={t => { setPassword(t); setErrors(e => ({...e, password: ''})); }}
              placeholder="Minimum 8 caractères"
              secureTextEntry={!showPwd}
              error={errors.password}
              rightIcon={
                <TouchableOpacity onPress={() => setShowPwd(v => !v)}>
                  <Text style={{fontSize: 16}}>{showPwd ? '🙈' : '👁️'}</Text>
                </TouchableOpacity>
              }
            />
            <Input
              label="Confirmer le mot de passe *"
              value={confirm}
              onChangeText={t => { setConfirm(t); setErrors(e => ({...e, confirm: ''})); }}
              placeholder="Répétez votre mot de passe"
              secureTextEntry={!showPwd}
              error={errors.confirm}
            />

            <View style={styles.pwdTips}>
              {['8 caractères minimum', 'Au moins une lettre (a-z)', 'Au moins un chiffre (0-9)'].map(tip => (
                <View key={tip} style={styles.pwdTip}>
                  <Text style={styles.pwdTipDot}>•</Text>
                  <Text style={styles.pwdTipText}>{tip}</Text>
                </View>
              ))}
            </View>

            <Button title="Suivant →" onPress={nextStep} fullWidth size="lg" style={{marginTop: 8}} />
          </View>
        )}

        {/* ── Step 2 ── */}
        {step === 2 && (
          <View>
            <Text style={styles.stepTitle}>Votre profil</Text>
            <Text style={styles.stepSub}>Personnalisez votre expérience</Text>

            <Text style={styles.fieldLabel}>Je suis</Text>
            <View style={styles.rolesGrid}>
              {ROLES.map(r => (
                <TouchableOpacity
                  key={r.value}
                  style={[styles.roleCard, role === r.value && styles.roleCardActive]}
                  onPress={() => setRole(r.value)}
                  activeOpacity={0.8}>
                  <Text style={styles.roleEmoji}>{r.emoji}</Text>
                  <Text style={[styles.roleLabel, role === r.value && styles.roleLabelActive]}>
                    {r.label}
                  </Text>
                  <Text style={styles.roleDesc}>{r.desc}</Text>
                </TouchableOpacity>
              ))}
            </View>

            <Text style={[styles.fieldLabel, {marginTop: 20}]}>Pays</Text>
            <View style={styles.countriesRow}>
              {COUNTRIES.map(c => (
                <TouchableOpacity
                  key={c.code}
                  style={[styles.countryChip, country === c.code && styles.countryChipActive]}
                  onPress={() => setCountry(c.code)}>
                  <Text style={[styles.countryText, country === c.code && styles.countryTextActive]}>
                    {c.name}
                  </Text>
                </TouchableOpacity>
              ))}
            </View>

            {(role === 'owner' || role === 'agent') && (
              <View style={styles.activationNotice}>
                <Text style={styles.activationNoticeText}>
                  ⏳ Ce type de compte sera activé par l'administrateur après vérification.
                </Text>
              </View>
            )}

            <Button
              title="Créer mon compte"
              onPress={handleRegister}
              loading={loading}
              fullWidth
              size="lg"
              style={{marginTop: 24}}
            />
          </View>
        )}
      </ScrollView>

      {/* Footer */}
      <View style={styles.footer}>
        <Text style={styles.footerText}>Déjà un compte ? </Text>
        <TouchableOpacity onPress={() => navigation.navigate('Login')}>
          <Text style={styles.footerLink}>Se connecter</Text>
        </TouchableOpacity>
      </View>

      {/* Post-registration modal */}
      <Modal transparent animationType="fade" visible={modal.visible} onRequestClose={handleModalClose}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalCard}>
            <Text style={styles.modalIcon}>{modal.needsActivation ? '⏳' : '✅'}</Text>
            <Text style={styles.modalTitle}>
              {modal.needsActivation ? 'Compte créé !' : 'Bienvenue !'}
            </Text>
            <Text style={styles.modalMessage}>{modal.message}</Text>
            <TouchableOpacity style={styles.modalBtn} onPress={handleModalClose} activeOpacity={0.85}>
              <Text style={styles.modalBtnText}>
                {modal.needsActivation ? 'Se connecter' : modal.phone ? 'Vérifier mon téléphone' : 'Se connecter'}
              </Text>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>
    </KeyboardAvoidingView>
  );
};

const styles = StyleSheet.create({
  flex: { flex: 1, backgroundColor: colors.background },

  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: colors.primary,
    paddingHorizontal: spacing.lg,
    paddingTop: Platform.OS === 'ios' ? 56 : 40,
    paddingBottom: spacing.md,
  },
  backBtn: {
    width: 36,
    height: 36,
    borderRadius: radius.full,
    backgroundColor: 'rgba(255,255,255,0.18)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  backArrow: { color: '#fff', fontSize: 20, fontWeight: '700', marginTop: -2 },
  headerTitle: { ...typography.h3, color: '#fff' },

  progress: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.primary,
    paddingHorizontal: spacing.xl,
    paddingBottom: spacing.lg,
  },
  stepItem: { alignItems: 'center', gap: 6 },
  stepDot: {
    width: 32,
    height: 32,
    borderRadius: radius.full,
    backgroundColor: 'rgba(255,255,255,0.2)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  stepDotActive: { backgroundColor: colors.accent },
  stepDotDone:   { backgroundColor: colors.success },
  stepNum: { ...typography.label, color: 'rgba(255,255,255,0.6)', fontWeight: '700' },
  stepNumActive: { color: '#fff' },
  stepCheck: { color: '#fff', fontSize: 14, fontWeight: '700' },
  stepLabel: { ...typography.caption, color: 'rgba(255,255,255,0.55)' },
  stepLabelActive: { color: '#fff', fontWeight: '600' },
  stepLine: { flex: 1, height: 2, backgroundColor: 'rgba(255,255,255,0.2)', marginBottom: 20 },
  stepLineDone: { backgroundColor: colors.success },

  body: { padding: spacing.lg, paddingTop: spacing.xl },
  stepTitle: { ...typography.h2, color: colors.text, marginBottom: 4 },
  stepSub:   { ...typography.body, color: colors.textSecondary, marginBottom: 24 },

  errorMsg: { ...typography.caption, color: colors.danger, marginBottom: 12, marginTop: -4 },

  pwdTips: {
    backgroundColor: colors.primaryFaint,
    borderRadius: radius.lg,
    padding: 14,
    marginBottom: 16,
    gap: 6,
  },
  pwdTip: { flexDirection: 'row', gap: 8, alignItems: 'center' },
  pwdTipDot: { color: colors.primary, fontWeight: '700', fontSize: 16 },
  pwdTipText: { ...typography.bodySm, color: colors.primaryMid },

  fieldLabel: { ...typography.label, color: colors.textMed, marginBottom: 10 },

  rolesGrid: { gap: 10 },
  roleCard: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 14,
    borderRadius: radius.xl,
    borderWidth: 2,
    borderColor: colors.border,
    backgroundColor: colors.surface,
    padding: 14,
    ...shadows.xs,
  },
  roleCardActive: { borderColor: colors.primary, backgroundColor: colors.primaryFaint },
  roleEmoji: { fontSize: 28, width: 36, textAlign: 'center' },
  roleLabel: { ...typography.h4, color: colors.text, flex: 1 },
  roleLabelActive: { color: colors.primary },
  roleDesc: { ...typography.caption, color: colors.textSecondary },

  countriesRow: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
  countryChip: {
    paddingHorizontal: 16,
    paddingVertical: 9,
    borderRadius: radius.full,
    borderWidth: 1.5,
    borderColor: colors.border,
    backgroundColor: colors.surface,
  },
  countryChipActive: { backgroundColor: colors.primary, borderColor: colors.primary },
  countryText: { ...typography.label, color: colors.textSecondary },
  countryTextActive: { color: '#fff' },

  footer: {
    flexDirection: 'row',
    justifyContent: 'center',
    paddingVertical: spacing.md,
    borderTopWidth: 1,
    borderTopColor: colors.border,
    backgroundColor: colors.background,
  },
  footerText: { ...typography.body, color: colors.textSecondary },
  footerLink: { ...typography.body, color: colors.primary, fontWeight: '700' },

  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.55)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: spacing.xl,
  },
  modalCard: {
    width: '100%',
    backgroundColor: colors.surface,
    borderRadius: radius.xxl,
    padding: spacing.xl,
    alignItems: 'center',
    ...shadows.md,
  },
  modalIcon:    { fontSize: 48, marginBottom: spacing.md },
  modalTitle:   { ...typography.h2, color: colors.text, marginBottom: 8, textAlign: 'center' },
  modalMessage: { ...typography.body, color: colors.textSecondary, textAlign: 'center', marginBottom: spacing.xl, lineHeight: 22 },
  modalBtn: {
    width: '100%',
    backgroundColor: colors.primary,
    borderRadius: radius.lg,
    paddingVertical: 14,
    alignItems: 'center',
  },
  modalBtnText: { ...typography.label, color: '#fff', fontSize: 16 },

  activationNotice: {
    backgroundColor: '#fef9c3',
    borderWidth: 1,
    borderColor: '#fde047',
    borderRadius: radius.lg,
    padding: 12,
    marginTop: spacing.md,
  },
  activationNoticeText: { ...typography.bodySm, color: '#854d0e', lineHeight: 20 },
});
