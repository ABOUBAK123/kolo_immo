import React, {useState} from 'react';
import {
  Alert,
  ScrollView,
  StatusBar,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import {NativeStackScreenProps} from '@react-navigation/native-stack';
import {authApi} from '../../api/auth';
import {Button} from '../../components/Button';
import {Input} from '../../components/Input';
import {useAuth} from '../../store/AuthContext';
import {ProfileStackParamList} from '../../types';
import {colors, radius, shadows, spacing, typography} from '../../utils/theme';

type Props = NativeStackScreenProps<ProfileStackParamList, 'ProfileScreen'>;

const KYC_INFO: Record<string, {label: string; color: string; emoji: string; desc: string}> = {
  pending:  {label: 'En attente', color: colors.warning, emoji: '⏳', desc: 'Vérification en cours'},
  verified: {label: 'Vérifié',    color: colors.success, emoji: '✅', desc: 'Identité confirmée'},
  rejected: {label: 'Rejeté',     color: colors.danger,  emoji: '❌', desc: 'Documents non valides'},
};

export const ProfileScreen: React.FC<Props> = ({navigation}) => {
  const {user, logout, updateUser} = useAuth();
  const [editing, setEditing] = useState(false);
  const [name, setName]       = useState(user?.name ?? '');
  const [city, setCity]       = useState(user?.city ?? '');
  const [saving, setSaving]   = useState(false);

  const kyc = KYC_INFO[user?.kyc_status ?? 'pending'];
  const trust = user?.trust_score ?? 0;
  const trustColor = trust >= 80 ? colors.success : trust >= 50 ? colors.warning : colors.danger;

  const handleSave = async () => {
    setSaving(true);
    try {
      const res = await authApi.updateProfile({name, city});
      updateUser(res.data.data);
      setEditing(false);
      Alert.alert('Profil mis à jour', 'Vos informations ont été enregistrées.');
    } catch (err: any) {
      Alert.alert('Erreur', err.response?.data?.message ?? 'Mise à jour échouée.');
    } finally {
      setSaving(false);
    }
  };

  const handleLogout = () =>
    Alert.alert('Déconnexion', 'Voulez-vous vraiment vous déconnecter ?', [
      {text: 'Annuler', style: 'cancel'},
      {text: 'Déconnecter', style: 'destructive', onPress: logout},
    ]);

  const roleLabel =
    user?.role === 'owner' ? '🔑 Propriétaire' :
    user?.role === 'both'  ? '🔄 Locataire & Propriétaire' :
    user?.role === 'agent' ? '💼 Agent immobilier' :
    '🏠 Locataire';

  return (
    <View style={styles.screen}>
      <StatusBar barStyle="light-content" backgroundColor={colors.primary} />

      <ScrollView showsVerticalScrollIndicator={true}>
        {/* ── Hero ── */}
        <View style={styles.hero}>
          <View style={styles.avatar}>
            <Text style={styles.avatarText}>
              {(user?.name ?? 'K').charAt(0).toUpperCase()}
            </Text>
          </View>
          <Text style={styles.heroName}>{user?.name}</Text>
          <Text style={styles.heroContact}>{user?.email ?? user?.phone}</Text>
          <View style={styles.roleBadge}>
            <Text style={styles.roleText}>{roleLabel}</Text>
          </View>
        </View>

        {/* ── Stats ── */}
        <View style={styles.statsCard}>
          {/* KYC */}
          <View style={styles.statItem}>
            <Text style={styles.statEmoji}>{kyc.emoji}</Text>
            <Text style={[styles.statValue, {color: kyc.color}]}>{kyc.label}</Text>
            <Text style={styles.statLabel}>Identité</Text>
          </View>

          <View style={styles.statSep} />

          {/* Trust score */}
          <View style={styles.statItem}>
            <Text style={[styles.statBig, {color: trustColor}]}>{trust}</Text>
            <Text style={styles.statLabel}>Score de confiance</Text>
            <View style={styles.trustBarOuter}>
              <View style={[styles.trustBarInner, {width: `${trust}%` as any, backgroundColor: trustColor}]} />
            </View>
          </View>

          <View style={styles.statSep} />

          {/* Country */}
          <View style={styles.statItem}>
            <Text style={styles.statEmoji}>🌍</Text>
            <Text style={styles.statValue}>{user?.country ?? '—'}</Text>
            <Text style={styles.statLabel}>Pays</Text>
          </View>
        </View>

        {/* ── Code agent ── */}
        {user?.role === 'agent' && (
          <View style={styles.agentCodeCard}>
            <Text style={styles.agentCodeTitle}>Votre code agent</Text>
            {user?.agent_code ? (
              <>
                <Text style={styles.agentCodeSub}>Communiquez ce code aux propriétaires et locataires que vous inscrivez sur la plateforme.</Text>
                <View style={styles.agentCodeBox}>
                  <Text style={styles.agentCodeValue}>{user.agent_code}</Text>
                </View>
              </>
            ) : (
              <Text style={styles.agentCodeSub}>Votre code sera généré automatiquement dès que votre compte sera activé par l'administrateur.</Text>
            )}
          </View>
        )}

        {/* ── Info section ── */}
        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <Text style={styles.sectionTitle}>Informations personnelles</Text>
            <TouchableOpacity
              style={styles.editToggle}
              onPress={() => setEditing(v => !v)}>
              <Text style={styles.editToggleText}>{editing ? '✕  Annuler' : '✏️  Modifier'}</Text>
            </TouchableOpacity>
          </View>

          {editing ? (
            <View style={styles.editForm}>
              <Input
                label="Nom complet"
                value={name}
                onChangeText={setName}
              />
              <Input
                label="Ville"
                value={city}
                onChangeText={setCity}
                placeholder="Ex: Abidjan"
              />
              <Button
                title="Enregistrer les modifications"
                onPress={handleSave}
                loading={saving}
                fullWidth
                size="lg"
              />
            </View>
          ) : (
            <View style={styles.infoList}>
              {[
                {label: 'Nom',       value: user?.name,                icon: '👤'},
                {label: 'Email',     value: user?.email ?? '—',        icon: '📧'},
                {label: 'Téléphone', value: user?.phone ?? '—',        icon: '📱'},
                {label: 'Ville',     value: user?.city ?? '—',         icon: '📍'},
                {label: 'Pays',      value: user?.country ?? '—',      icon: '🌍'},
              ].map(info => (
                <View key={info.label} style={styles.infoRow}>
                  <Text style={styles.infoIcon}>{info.icon}</Text>
                  <View style={styles.infoContent}>
                    <Text style={styles.infoLabel}>{info.label}</Text>
                    <Text style={styles.infoValue}>{info.value}</Text>
                  </View>
                </View>
              ))}
            </View>
          )}
        </View>

        {/* ── KYC Banner ── */}
        {user?.kyc_status === 'verified' ? (
          <View style={[styles.kycBanner, styles.kycBannerVerified]}>
            <View style={styles.kycLeft}>
              <Text style={[styles.kycTitle, {color: colors.success}]}>Identité vérifiée</Text>
              <Text style={styles.kycDesc}>Votre compte est pleinement vérifié.</Text>
            </View>
            <View style={styles.kycBtnVerified}>
              <Text style={styles.kycBtnVerifiedText}>✓ Vérifié</Text>
            </View>
          </View>
        ) : user?.kyc_status === 'pending' ? (
          <View style={[styles.kycBanner, styles.kycBannerPending]}>
            <View style={styles.kycLeft}>
              <Text style={[styles.kycTitle, {color: colors.warning}]}>Vérification en cours</Text>
              <Text style={styles.kycDesc}>Nos équipes examinent vos documents (24-48h).</Text>
            </View>
          </View>
        ) : (
          <View style={styles.kycBanner}>
            <View style={styles.kycLeft}>
              <Text style={styles.kycTitle}>Vérifiez votre identité</Text>
              <Text style={styles.kycDesc}>
                Augmentez votre crédibilité et accédez à plus de fonctionnalités.
              </Text>
            </View>
            <TouchableOpacity style={styles.kycBtn}>
              <Text style={styles.kycBtnText}>Vérifier →</Text>
            </TouchableOpacity>
          </View>
        )}

        {/* ── Favorites shortcut ── */}
        <View style={styles.section}>
          <TouchableOpacity
            style={[styles.actionRow, {borderRadius: radius.lg}]}
            activeOpacity={0.7}
            onPress={() => navigation.navigate('Favorites')}>
            <View style={styles.actionLeft}>
              <Text style={styles.actionIcon}>♥</Text>
              <Text style={styles.actionLabel}>Mes favoris</Text>
            </View>
            <Text style={styles.actionChevron}>›</Text>
          </TouchableOpacity>
        </View>

        {/* ── Owner section ── */}
        {(user?.role === 'owner' || user?.role === 'both') && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Espace propriétaire</Text>
            <View style={styles.actionList}>
              <TouchableOpacity
                style={styles.actionRow}
                activeOpacity={0.7}
                onPress={() => navigation.navigate('OwnerProperties')}>
                <View style={styles.actionLeft}>
                  <Text style={styles.actionIcon}>🏠</Text>
                  <Text style={styles.actionLabel}>Mes logements</Text>
                </View>
                <Text style={styles.actionChevron}>›</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={styles.actionRow}
                activeOpacity={0.7}
                onPress={() => navigation.navigate('OwnerCommissions')}>
                <View style={styles.actionLeft}>
                  <Text style={styles.actionIcon}>💰</Text>
                  <Text style={styles.actionLabel}>Mes commissions (3%)</Text>
                </View>
                <Text style={styles.actionChevron}>›</Text>
              </TouchableOpacity>
            </View>
          </View>
        )}

        {/* ── Actions ── */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Compte</Text>
          <View style={styles.actionList}>
            {[
              {label: 'Paramètres de notification', icon: '🔔'},
              {label: 'Confidentialité et sécurité', icon: '🔒'},
              {label: 'Aide et support',             icon: '💬'},
              {label: 'À propos de Kolo Immo',       icon: 'ℹ️'},
            ].map(a => (
              <TouchableOpacity key={a.label} style={styles.actionRow} activeOpacity={0.7}>
                <View style={styles.actionLeft}>
                  <Text style={styles.actionIcon}>{a.icon}</Text>
                  <Text style={styles.actionLabel}>{a.label}</Text>
                </View>
                <Text style={styles.actionChevron}>›</Text>
              </TouchableOpacity>
            ))}
          </View>
        </View>

        {/* ── Logout ── */}
        <View style={styles.logoutSection}>
          <TouchableOpacity style={styles.logoutBtn} onPress={handleLogout} activeOpacity={0.8}>
            <Text style={styles.logoutText}>🚪  Se déconnecter</Text>
          </TouchableOpacity>
        </View>

        <View style={{height: 90}} />
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  screen: {flex: 1, backgroundColor: colors.background},

  hero: {
    backgroundColor: colors.primary,
    alignItems: 'center',
    paddingTop: 52,
    paddingBottom: spacing.xl,
    paddingHorizontal: spacing.lg,
  },
  avatar: {
    width: 88,
    height: 88,
    borderRadius: radius.full,
    backgroundColor: colors.accent,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 14,
    borderWidth: 3,
    borderColor: 'rgba(255,255,255,0.35)',
    ...shadows.md,
  },
  avatarText: {color: '#fff', fontSize: 38, fontWeight: '900'},
  heroName: {...typography.h2, color: '#fff', textAlign: 'center'},
  heroContact: {...typography.body, color: 'rgba(255,255,255,0.72)', marginTop: 4, textAlign: 'center'},
  roleBadge: {
    marginTop: 10,
    backgroundColor: 'rgba(255,255,255,0.18)',
    paddingHorizontal: 16,
    paddingVertical: 6,
    borderRadius: radius.full,
  },
  roleText: {...typography.label, color: '#fff'},

  statsCard: {
    flexDirection: 'row',
    backgroundColor: colors.surface,
    marginHorizontal: spacing.lg,
    marginTop: -16,
    borderRadius: radius.xl,
    padding: spacing.md,
    ...shadows.md,
  },
  statItem: {flex: 1, alignItems: 'center', gap: 4},
  statEmoji: {fontSize: 22},
  statBig: {fontSize: 22, fontWeight: '800'},
  statValue: {...typography.h4, color: colors.text},
  statLabel: {...typography.caption, color: colors.textTertiary, textAlign: 'center'},
  statSep: {width: 1, backgroundColor: colors.border, marginHorizontal: 4},
  trustBarOuter: {width: '80%', height: 4, backgroundColor: colors.gray200, borderRadius: 2, overflow: 'hidden'},
  trustBarInner: {height: 4, borderRadius: 2},

  agentCodeCard: {
    backgroundColor: '#F5F3FF',
    borderWidth: 1,
    borderColor: '#DDD6FE',
    marginHorizontal: spacing.lg,
    marginTop: spacing.md,
    borderRadius: radius.xl,
    padding: spacing.md,
  },
  agentCodeTitle: {...typography.label, color: '#5B21B6', fontWeight: '700', marginBottom: 4},
  agentCodeSub: {...typography.bodySm, color: '#6D28D9', lineHeight: 18},
  agentCodeBox: {
    marginTop: 10,
    alignSelf: 'flex-start',
    backgroundColor: colors.surface,
    borderWidth: 1,
    borderColor: '#DDD6FE',
    borderRadius: radius.lg,
    paddingHorizontal: 16,
    paddingVertical: 10,
  },
  agentCodeValue: {...typography.h4, color: '#5B21B6', letterSpacing: 1, fontWeight: '800'},

  section: {
    backgroundColor: colors.surface,
    marginHorizontal: spacing.lg,
    marginTop: spacing.md,
    borderRadius: radius.xl,
    padding: spacing.md,
    ...shadows.xs,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 14,
  },
  sectionTitle: {...typography.h4, color: colors.text},
  editToggle: {
    backgroundColor: colors.primaryFaint,
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: radius.full,
  },
  editToggleText: {...typography.label, color: colors.primary},

  editForm: {gap: 0},

  infoList: {gap: 0},
  infoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: colors.borderLight,
    gap: 12,
  },
  infoIcon: {fontSize: 18, width: 24, textAlign: 'center'},
  infoContent: {flex: 1},
  infoLabel: {...typography.caption, color: colors.textTertiary},
  infoValue: {...typography.body, color: colors.text, fontWeight: '500', marginTop: 1},

  kycBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    marginHorizontal: spacing.lg,
    marginTop: spacing.md,
    backgroundColor: colors.accentFaint,
    borderRadius: radius.xl,
    padding: spacing.md,
    borderWidth: 1.5,
    borderColor: colors.accentLight,
  },
  kycBannerVerified: {
    backgroundColor: '#f0fdf4',
    borderColor: '#86efac',
  },
  kycBannerPending: {
    backgroundColor: '#fffbeb',
    borderColor: '#fcd34d',
  },
  kycLeft: {flex: 1},
  kycTitle: {...typography.h4, color: colors.accent},
  kycDesc: {...typography.caption, color: colors.textSecondary, marginTop: 3},
  kycBtn: {
    backgroundColor: colors.accent,
    paddingHorizontal: 14,
    paddingVertical: 9,
    borderRadius: radius.lg,
  },
  kycBtnText: {color: '#fff', fontWeight: '700', fontSize: 13},
  kycBtnVerified: {
    backgroundColor: '#16a34a',
    paddingHorizontal: 14,
    paddingVertical: 9,
    borderRadius: radius.lg,
  },
  kycBtnVerifiedText: {color: '#fff', fontWeight: '700', fontSize: 13},

  actionList: {gap: 0},
  actionRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: colors.borderLight,
  },
  actionLeft: {flexDirection: 'row', alignItems: 'center', gap: 12},
  actionIcon: {fontSize: 18, width: 24, textAlign: 'center'},
  actionLabel: {...typography.body, color: colors.textMed},
  actionChevron: {fontSize: 20, color: colors.textTertiary, fontWeight: '300'},

  logoutSection: {
    marginHorizontal: spacing.lg,
    marginTop: spacing.md,
  },
  logoutBtn: {
    backgroundColor: colors.dangerLight,
    borderRadius: radius.xl,
    paddingVertical: 16,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: colors.danger + '30',
  },
  logoutText: {...typography.button, color: colors.danger},
});
