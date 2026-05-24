import React, {useCallback, useEffect, useState} from 'react';
import {
  ActivityIndicator,
  Alert,
  FlatList,
  RefreshControl,
  StatusBar,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import {NativeStackScreenProps} from '@react-navigation/native-stack';
import {apiClient} from '../../api/client';
import {ProfileStackParamList} from '../../types';
import {colors, radius, shadows, spacing, typography} from '../../utils/theme';

type Props = NativeStackScreenProps<ProfileStackParamList, 'OwnerCommissions'>;

interface CommissionRow {
  id: number;
  reference: string;
  property: string;
  tenant: string;
  check_in: string;
  check_out: string;
  nights: number;
  total: number;
  commission: number;
  status: string;
}

interface CommissionData {
  commission_rate: number;
  total_commission: number;
  total_revenue: number;
  monthly_commission: number;
  monthly_revenue: number;
  bookings_count: number;
  rows: CommissionRow[];
}

const fmt = (n: number) =>
  new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' FCFA';

const fmtDate = (d: string) =>
  new Date(d).toLocaleDateString('fr-FR', {day: '2-digit', month: 'short', year: '2-digit'});

export const OwnerCommissionsScreen: React.FC<Props> = ({navigation}) => {
  const [data, setData]         = useState<CommissionData | null>(null);
  const [loading, setLoading]   = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const load = useCallback(async (silent = false) => {
    if (!silent) setLoading(true);
    try {
      const res = await apiClient.get<{data: CommissionData}>('/owner/commissions');
      setData(res.data.data);
    } catch {
      Alert.alert('Erreur', 'Impossible de charger les commissions.');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => { load(); }, [load]);

  const renderRow = ({item}: {item: CommissionRow}) => (
    <View style={styles.row}>
      <View style={styles.rowTop}>
        <Text style={styles.rowRef}>{item.reference}</Text>
        <Text style={styles.rowCommission}>{fmt(item.commission)}</Text>
      </View>
      <Text style={styles.rowProperty} numberOfLines={1}>{item.property}</Text>
      <View style={styles.rowBottom}>
        <Text style={styles.rowMeta}>
          {fmtDate(item.check_in)} → {fmtDate(item.check_out)} · {item.nights}n
        </Text>
        <Text style={styles.rowTotal}>{fmt(item.total)}</Text>
      </View>
    </View>
  );

  return (
    <View style={styles.screen}>
      <StatusBar barStyle="light-content" backgroundColor={colors.primary} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity style={styles.backBtn} onPress={() => navigation.goBack()}>
          <Text style={styles.backIcon}>‹</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Mes commissions</Text>
        <View style={styles.backBtn} />
      </View>

      {loading ? (
        <View style={styles.loadingWrap}>
          <ActivityIndicator size="large" color={colors.primary} />
        </View>
      ) : (
        <FlatList
          data={data?.rows ?? []}
          keyExtractor={item => String(item.id)}
          contentContainerStyle={styles.list}
          showsVerticalScrollIndicator={true}
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={() => { setRefreshing(true); load(true); }}
              tintColor={colors.primary}
            />
          }
          ListHeaderComponent={
            data ? (
              <View style={styles.summary}>
                {/* Rate badge */}
                <View style={styles.rateBadge}>
                  <Text style={styles.rateText}>3% par réservation confirmée</Text>
                </View>

                {/* Stats grid */}
                <View style={styles.statsGrid}>
                  <View style={[styles.statCard, {borderColor: colors.successLight}]}>
                    <Text style={styles.statLabel}>Ce mois</Text>
                    <Text style={[styles.statValue, {color: colors.success}]}>
                      {fmt(data.monthly_commission)}
                    </Text>
                    <Text style={styles.statSub}>sur {fmt(data.monthly_revenue)}</Text>
                  </View>
                  <View style={[styles.statCard, {borderColor: colors.primaryFaint ?? '#e8f4fd'}]}>
                    <Text style={styles.statLabel}>Total</Text>
                    <Text style={[styles.statValue, {color: colors.primary}]}>
                      {fmt(data.total_commission)}
                    </Text>
                    <Text style={styles.statSub}>{data.bookings_count} réservation{data.bookings_count !== 1 ? 's' : ''}</Text>
                  </View>
                </View>

                <Text style={styles.listTitle}>Détail</Text>
              </View>
            ) : null
          }
          ListEmptyComponent={
            <View style={styles.empty}>
              <Text style={styles.emptyIcon}>💰</Text>
              <Text style={styles.emptyTitle}>Aucune commission</Text>
              <Text style={styles.emptySub}>
                Les commissions apparaissent dès qu'une réservation est confirmée.
              </Text>
            </View>
          }
          renderItem={renderRow}
        />
      )}
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

  loadingWrap: {flex: 1, alignItems: 'center', justifyContent: 'center'},

  list: {padding: spacing.md, paddingBottom: 100},

  summary: {marginBottom: 16},
  rateBadge: {
    backgroundColor: colors.primaryFaint ?? '#e8f4fd',
    borderRadius: radius.full,
    paddingHorizontal: 14,
    paddingVertical: 6,
    alignSelf: 'flex-start',
    marginBottom: 14,
  },
  rateText: {...typography.label, color: colors.primary},

  statsGrid: {flexDirection: 'row', gap: 12, marginBottom: 20},
  statCard: {
    flex: 1,
    backgroundColor: colors.surface,
    borderRadius: radius.xl,
    borderWidth: 1.5,
    padding: 14,
    ...shadows.sm,
  },
  statLabel: {...typography.caption, color: colors.textTertiary, marginBottom: 4},
  statValue: {...typography.h3, marginBottom: 2},
  statSub: {...typography.caption, color: colors.textTertiary},

  listTitle: {...typography.h4, color: colors.text, marginBottom: 10},

  row: {
    backgroundColor: colors.surface,
    borderRadius: radius.lg,
    padding: spacing.md,
    marginBottom: 10,
    ...shadows.sm,
  },
  rowTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 4,
  },
  rowRef: {...typography.caption, color: colors.textTertiary, fontFamily: 'monospace'},
  rowCommission: {...typography.h4, color: colors.success},
  rowProperty: {...typography.label, color: colors.text, marginBottom: 6},
  rowBottom: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  rowMeta: {...typography.caption, color: colors.textTertiary},
  rowTotal: {...typography.caption, color: colors.textSecondary},

  empty: {alignItems: 'center', paddingTop: 60, paddingHorizontal: spacing.xl},
  emptyIcon: {fontSize: 48, marginBottom: 14},
  emptyTitle: {...typography.h3, color: colors.text, marginBottom: 8},
  emptySub: {...typography.body, color: colors.textTertiary, textAlign: 'center', lineHeight: 22},
});
