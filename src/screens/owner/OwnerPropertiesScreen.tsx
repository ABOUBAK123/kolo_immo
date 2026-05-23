import React, {useCallback, useEffect, useState} from 'react';
import {
  Alert,
  FlatList,
  Image,
  RefreshControl,
  StatusBar,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import {NativeStackScreenProps} from '@react-navigation/native-stack';
import {ownerPropertiesApi} from '../../api/ownerProperties';
import {LoadingSpinner} from '../../components/LoadingSpinner';
import {Property} from '../../types';
import {ProfileStackParamList} from '../../types';
import {colors, radius, shadows, spacing, typography} from '../../utils/theme';

type Props = NativeStackScreenProps<ProfileStackParamList, 'OwnerProperties'>;

const STATUS_CONFIG: Record<string, {label: string; bg: string; text: string}> = {
  active:    {label: 'Actif',      bg: colors.successLight, text: colors.success},
  inactive:  {label: 'Inactif',    bg: colors.warningLight, text: colors.warning},
  draft:     {label: 'Brouillon',  bg: colors.gray100,      text: colors.gray500},
  suspended: {label: 'Suspendu',   bg: colors.dangerLight,  text: colors.danger},
};

export const OwnerPropertiesScreen: React.FC<Props> = ({navigation}) => {
  const [properties, setProperties] = useState<Property[]>([]);
  const [loading, setLoading]       = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [toggling, setToggling]     = useState<number | null>(null);

  const load = useCallback(async (silent = false) => {
    if (!silent) setLoading(true);
    try {
      const res = await ownerPropertiesApi.list(1);
      setProperties(res.data.data.properties ?? []);
    } catch {
      Alert.alert('Erreur', 'Impossible de charger vos logements.');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    load();
  }, [load]);

  const handleRefresh = () => {
    setRefreshing(true);
    load(true);
  };

  const handleToggle = async (property: Property) => {
    if (property.status === 'suspended') {
      Alert.alert('Suspendu', 'Ce logement a été suspendu par l\'administrateur.');
      return;
    }
    setToggling(property.id);
    try {
      const res = await ownerPropertiesApi.toggleStatus(property.id);
      if (res.data.kyc_required) {
        Alert.alert(
          'Vérification requise',
          res.data.message,
          [{text: 'OK'}],
        );
      } else {
        setProperties(prev =>
          prev.map(p =>
            p.id === property.id
              ? {...p, status: res.data.data?.status as any ?? p.status}
              : p,
          ),
        );
      }
    } catch (err: any) {
      Alert.alert('Erreur', err.response?.data?.message ?? 'Action échouée.');
    } finally {
      setToggling(null);
    }
  };

  const handleDelete = (property: Property) => {
    Alert.alert(
      'Supprimer ce logement',
      `Supprimer "${property.title}" définitivement ?`,
      [
        {text: 'Annuler', style: 'cancel'},
        {
          text: 'Supprimer',
          style: 'destructive',
          onPress: async () => {
            try {
              await ownerPropertiesApi.destroy(property.id);
              setProperties(prev => prev.filter(p => p.id !== property.id));
            } catch {
              Alert.alert('Erreur', 'Suppression échouée.');
            }
          },
        },
      ],
    );
  };

  const renderItem = ({item}: {item: Property}) => {
    const status = STATUS_CONFIG[item.status ?? 'draft'] ?? STATUS_CONFIG.draft;
    const isToggling = toggling === item.id;
    const canToggle = item.status !== 'suspended';

    return (
      <View style={styles.card}>
        {/* Photo */}
        {item.cover_photo_url ? (
          <Image source={{uri: item.cover_photo_url}} style={styles.photo} />
        ) : (
          <View style={[styles.photo, styles.photoPlaceholder]}>
            <Text style={styles.photoPlaceholderIcon}>🏠</Text>
          </View>
        )}

        {/* Content */}
        <View style={styles.cardBody}>
          <View style={styles.cardHeader}>
            <Text style={styles.cardTitle} numberOfLines={1}>{item.title}</Text>
            <View style={[styles.statusBadge, {backgroundColor: status.bg}]}>
              <Text style={[styles.statusText, {color: status.text}]}>{status.label}</Text>
            </View>
          </View>

          <Text style={styles.cardSub}>{item.city}, {item.country}</Text>
          <Text style={styles.cardPrice}>
            {Number(item.price_per_night).toLocaleString('fr-FR')} FCFA
            <Text style={styles.cardPriceSub}> / nuit</Text>
          </Text>

          {/* Actions */}
          <View style={styles.cardActions}>
            {canToggle && (
              <TouchableOpacity
                style={[
                  styles.actionBtn,
                  item.status === 'active' ? styles.btnDeactivate : styles.btnActivate,
                  isToggling && styles.btnDisabled,
                ]}
                onPress={() => handleToggle(item)}
                disabled={isToggling}
                activeOpacity={0.7}>
                <Text style={[
                  styles.actionBtnText,
                  item.status === 'active' ? styles.btnDeactivateText : styles.btnActivateText,
                ]}>
                  {isToggling ? '...' : item.status === 'active' ? 'Désactiver' : 'Activer'}
                </Text>
              </TouchableOpacity>
            )}

            <TouchableOpacity
              style={[styles.actionBtn, styles.btnDelete]}
              onPress={() => handleDelete(item)}
              activeOpacity={0.7}>
              <Text style={styles.btnDeleteText}>Supprimer</Text>
            </TouchableOpacity>
          </View>
        </View>
      </View>
    );
  };

  if (loading) {
    return (
      <View style={styles.screen}>
        <StatusBar barStyle="light-content" backgroundColor={colors.primary} />
        <View style={styles.header}>
          <TouchableOpacity style={styles.backBtn} onPress={() => navigation.goBack()}>
            <Text style={styles.backIcon}>‹</Text>
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Mes logements</Text>
          <View style={{width: 40}} />
        </View>
        <LoadingSpinner message="Chargement..." />
      </View>
    );
  }

  return (
    <View style={styles.screen}>
      <StatusBar barStyle="light-content" backgroundColor={colors.primary} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity style={styles.backBtn} onPress={() => navigation.goBack()}>
          <Text style={styles.backIcon}>‹</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Mes logements</Text>
        <View style={{width: 40}} />
      </View>

      <FlatList
        data={properties}
        keyExtractor={item => String(item.id)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={handleRefresh}
            tintColor={colors.primary}
          />
        }
        ListEmptyComponent={
          <View style={styles.empty}>
            <Text style={styles.emptyIcon}>🏠</Text>
            <Text style={styles.emptyTitle}>Aucun logement</Text>
            <Text style={styles.emptySub}>
              Créez vos annonces depuis le site web Kolo Immo.
            </Text>
          </View>
        }
      />
    </View>
  );
};

const styles = StyleSheet.create({
  screen: {flex: 1, backgroundColor: colors.background},

  header: {
    backgroundColor: colors.primary,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingTop: 48,
    paddingBottom: 16,
    paddingHorizontal: spacing.md,
  },
  backBtn: {
    width: 40,
    height: 40,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: 'rgba(255,255,255,0.18)',
    borderRadius: radius.full,
  },
  backIcon: {color: '#fff', fontSize: 26, fontWeight: '300', marginTop: -2},
  headerTitle: {...typography.h3, color: '#fff'},

  list: {padding: spacing.md, gap: 12, paddingBottom: 100},

  card: {
    backgroundColor: colors.surface,
    borderRadius: radius.xl,
    overflow: 'hidden',
    ...shadows.sm,
  },
  photo: {width: '100%', height: 160},
  photoPlaceholder: {
    backgroundColor: colors.gray100,
    alignItems: 'center',
    justifyContent: 'center',
  },
  photoPlaceholderIcon: {fontSize: 40},

  cardBody: {padding: spacing.md},
  cardHeader: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    gap: 8,
    marginBottom: 4,
  },
  cardTitle: {
    ...typography.h4,
    color: colors.text,
    flex: 1,
  },
  statusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: radius.full,
    flexShrink: 0,
  },
  statusText: {...typography.labelSm},

  cardSub: {...typography.caption, color: colors.textTertiary, marginBottom: 4},
  cardPrice: {...typography.h4, color: colors.primary, marginBottom: 14},
  cardPriceSub: {...typography.caption, color: colors.textTertiary, fontWeight: '400'},

  cardActions: {flexDirection: 'row', gap: 8},
  actionBtn: {
    flex: 1,
    paddingVertical: 9,
    borderRadius: radius.lg,
    alignItems: 'center',
  },
  actionBtnText: {...typography.buttonSm},
  btnActivate: {backgroundColor: colors.successLight},
  btnActivateText: {color: colors.success},
  btnDeactivate: {backgroundColor: colors.warningLight},
  btnDeactivateText: {color: colors.warning},
  btnDelete: {backgroundColor: colors.dangerLight},
  btnDeleteText: {...typography.buttonSm, color: colors.danger},
  btnDisabled: {opacity: 0.5},

  empty: {
    alignItems: 'center',
    paddingTop: 80,
    paddingHorizontal: spacing.xl,
  },
  emptyIcon: {fontSize: 52, marginBottom: 16},
  emptyTitle: {...typography.h3, color: colors.text, marginBottom: 8},
  emptySub: {
    ...typography.body,
    color: colors.textTertiary,
    textAlign: 'center',
    lineHeight: 22,
  },
});
