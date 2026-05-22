import React, {useState} from 'react';
import {
  Alert,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import {authApi} from '../../api/auth';
import {Button} from '../../components/Button';
import {Input} from '../../components/Input';
import {useAuth} from '../../store/AuthContext';
import {colors, radius, spacing, typography} from '../../utils/theme';

export const ProfileScreen: React.FC = () => {
  const {user, logout, updateUser} = useAuth();
  const [editing, setEditing] = useState(false);
  const [name, setName] = useState(user?.name ?? '');
  const [city, setCity] = useState(user?.city ?? '');
  const [saving, setSaving] = useState(false);

  const handleSave = async () => {
    setSaving(true);
    try {
      const res = await authApi.updateProfile({name, city});
      updateUser(res.data.user);
      setEditing(false);
      Alert.alert('Succès', 'Profil mis à jour.');
    } catch (err: any) {
      Alert.alert('Erreur', err.response?.data?.message ?? 'Mise à jour échouée.');
    } finally {
      setSaving(false);
    }
  };

  const handleLogout = () => {
    Alert.alert(
      'Déconnexion',
      'Voulez-vous vous déconnecter ?',
      [
        {text: 'Annuler', style: 'cancel'},
        {text: 'Déconnecter', style: 'destructive', onPress: logout},
      ],
    );
  };

  const kycLabel: Record<string, {label: string; color: string; emoji: string}> = {
    pending: {label: 'En attente', color: '#F59E0B', emoji: '⏳'},
    verified: {label: 'Vérifié', color: '#10B981', emoji: '✅'},
    rejected: {label: 'Rejeté', color: '#EF4444', emoji: '❌'},
  };
  const kyc = kycLabel[user?.kyc_status ?? 'pending'];

  const trustColor = (user?.trust_score ?? 0) >= 80 ? '#10B981' : (user?.trust_score ?? 0) >= 50 ? '#F59E0B' : '#EF4444';

  return (
    <ScrollView style={styles.screen} showsVerticalScrollIndicator={false}>
      <View style={styles.header}>
        <Text style={styles.title}>Mon profil</Text>
        <TouchableOpacity onPress={handleLogout} style={styles.logoutBtn}>
          <Text style={styles.logoutText}>Déconnexion</Text>
        </TouchableOpacity>
      </View>

      {/* Avatar + name */}
      <View style={styles.profileCard}>
        <View style={styles.avatar}>
          <Text style={styles.avatarText}>
            {(user?.name ?? 'U').charAt(0).toUpperCase()}
          </Text>
        </View>
        <Text style={styles.name}>{user?.name}</Text>
        <Text style={styles.email}>{user?.email ?? user?.phone}</Text>

        <View style={styles.roleBadge}>
          <Text style={styles.roleText}>
            {user?.role === 'owner' ? '🔑 Propriétaire' : user?.role === 'both' ? '🔄 Locataire & Propriétaire' : '🏠 Locataire'}
          </Text>
        </View>
      </View>

      {/* KYC & Trust */}
      <View style={styles.statsRow}>
        <View style={[styles.statCard, {borderColor: kyc.color + '40'}]}>
          <Text style={styles.statEmoji}>{kyc.emoji}</Text>
          <Text style={[styles.statValue, {color: kyc.color}]}>{kyc.label}</Text>
          <Text style={styles.statLabel}>Identité</Text>
        </View>
        <View style={[styles.statCard, {borderColor: trustColor + '40'}]}>
          <Text style={[styles.statValue, {color: trustColor, fontSize: 24}]}>
            {user?.trust_score ?? 0}
          </Text>
          <Text style={styles.statLabel}>Score de confiance</Text>
          <View style={styles.trustBar}>
            <View style={[styles.trustFill, {width: `${user?.trust_score ?? 0}%`, backgroundColor: trustColor}]} />
          </View>
        </View>
      </View>

      {/* Edit profile */}
      <View style={styles.card}>
        <View style={styles.cardHeader}>
          <Text style={styles.cardTitle}>Informations personnelles</Text>
          <TouchableOpacity onPress={() => setEditing(!editing)}>
            <Text style={styles.editBtn}>{editing ? 'Annuler' : 'Modifier'}</Text>
          </TouchableOpacity>
        </View>

        {editing ? (
          <>
            <Input label="Nom complet" value={name} onChangeText={setName} />
            <Input label="Ville" value={city} onChangeText={setCity} placeholder="Ex: Abidjan" />
            <Button title="Enregistrer" onPress={handleSave} loading={saving} fullWidth />
          </>
        ) : (
          <View style={styles.infoList}>
            {[
              {label: 'Nom', value: user?.name},
              {label: 'Email', value: user?.email ?? '—'},
              {label: 'Téléphone', value: user?.phone ?? '—'},
              {label: 'Ville', value: user?.city ?? '—'},
              {label: 'Pays', value: user?.country ?? '—'},
            ].map(info => (
              <View key={info.label} style={styles.infoRow}>
                <Text style={styles.infoLabel}>{info.label}</Text>
                <Text style={styles.infoValue}>{info.value}</Text>
              </View>
            ))}
          </View>
        )}
      </View>

      {/* Verification */}
      {user?.kyc_status !== 'verified' && (
        <View style={styles.kycBanner}>
          <View style={styles.kycBannerLeft}>
            <Text style={styles.kycBannerTitle}>Vérifiez votre identité</Text>
            <Text style={styles.kycBannerText}>
              Augmentez votre score de confiance et débloquez toutes les fonctionnalités.
            </Text>
          </View>
          <TouchableOpacity style={styles.kycBannerBtn}>
            <Text style={styles.kycBannerBtnText}>Vérifier →</Text>
          </TouchableOpacity>
        </View>
      )}

      <View style={{height: 80}} />
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  screen: {flex: 1, backgroundColor: colors.background},
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: spacing.lg,
    paddingTop: spacing.xl + 8,
  },
  title: {...typography.h1, color: colors.text},
  logoutBtn: {padding: 8},
  logoutText: {color: colors.danger, fontWeight: '600', fontSize: 14},
  profileCard: {
    alignItems: 'center',
    padding: spacing.lg,
    backgroundColor: colors.surface,
    marginHorizontal: spacing.lg,
    borderRadius: radius.xl,
    marginBottom: 16,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: {width: 0, height: 1},
    shadowOpacity: 0.06,
    shadowRadius: 4,
  },
  avatar: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: colors.primary,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 12,
  },
  avatarText: {color: '#fff', fontSize: 34, fontWeight: '800'},
  name: {fontSize: 20, fontWeight: '700', color: colors.text},
  email: {...typography.body, color: colors.textSecondary, marginTop: 4},
  roleBadge: {
    marginTop: 10,
    backgroundColor: colors.primary + '15',
    paddingHorizontal: 14,
    paddingVertical: 5,
    borderRadius: radius.full,
  },
  roleText: {fontSize: 13, fontWeight: '600', color: colors.primary},
  statsRow: {
    flexDirection: 'row',
    gap: 12,
    marginHorizontal: spacing.lg,
    marginBottom: 16,
  },
  statCard: {
    flex: 1,
    backgroundColor: colors.surface,
    borderRadius: radius.xl,
    padding: 14,
    alignItems: 'center',
    borderWidth: 1.5,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: {width: 0, height: 1},
    shadowOpacity: 0.05,
    shadowRadius: 3,
  },
  statEmoji: {fontSize: 22, marginBottom: 4},
  statValue: {fontSize: 18, fontWeight: '700', color: colors.text},
  statLabel: {...typography.caption, color: colors.textSecondary, marginTop: 4, textAlign: 'center'},
  trustBar: {
    width: '100%',
    height: 4,
    backgroundColor: colors.gray200,
    borderRadius: 2,
    marginTop: 6,
    overflow: 'hidden',
  },
  trustFill: {height: 4, borderRadius: 2},
  card: {
    backgroundColor: colors.surface,
    borderRadius: radius.xl,
    padding: spacing.md,
    marginHorizontal: spacing.lg,
    marginBottom: 16,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: {width: 0, height: 1},
    shadowOpacity: 0.05,
    shadowRadius: 4,
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  cardTitle: {...typography.h3, color: colors.text},
  editBtn: {color: colors.primary, fontWeight: '600', fontSize: 14},
  infoList: {gap: 2},
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: colors.borderLight,
  },
  infoLabel: {...typography.label, color: colors.textSecondary},
  infoValue: {...typography.body, color: colors.text, fontWeight: '500'},
  kycBanner: {
    flexDirection: 'row',
    gap: 12,
    alignItems: 'center',
    backgroundColor: colors.secondary + '15',
    marginHorizontal: spacing.lg,
    borderRadius: radius.xl,
    padding: 16,
    borderWidth: 1.5,
    borderColor: colors.secondary + '40',
  },
  kycBannerLeft: {flex: 1},
  kycBannerTitle: {fontSize: 14, fontWeight: '700', color: colors.secondary},
  kycBannerText: {...typography.caption, color: colors.textSecondary, marginTop: 2},
  kycBannerBtn: {
    backgroundColor: colors.secondary,
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: radius.md,
  },
  kycBannerBtnText: {color: '#fff', fontWeight: '700', fontSize: 13},
});
