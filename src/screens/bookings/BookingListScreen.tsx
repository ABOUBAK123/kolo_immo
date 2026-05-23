import React, {useCallback, useState} from 'react';
import {
  FlatList,
  RefreshControl,
  StatusBar,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import {NativeStackNavigationProp} from '@react-navigation/native-stack';
import {useFocusEffect} from '@react-navigation/native';
import {bookingsApi} from '../../api/bookings';
import {BookingCard} from '../../components/BookingCard';
import {LoadingSpinner} from '../../components/LoadingSpinner';
import {Booking, BookingsStackParamList} from '../../types';
import {colors, radius, spacing, typography} from '../../utils/theme';

type Props = {
  navigation: NativeStackNavigationProp<BookingsStackParamList, 'BookingList'>;
};

const FILTERS = [
  {value: '',          label: 'Tout',        emoji: '📋'},
  {value: 'pending',   label: 'En attente',  emoji: '⏳'},
  {value: 'confirmed', label: 'Confirmées',  emoji: '✅'},
  {value: 'completed', label: 'Terminées',   emoji: '🏁'},
  {value: 'cancelled', label: 'Annulées',    emoji: '❌'},
];

export const BookingListScreen: React.FC<Props> = ({navigation}) => {
  const [bookings, setBookings] = useState<Booking[]>([]);
  const [filter, setFilter]     = useState('');
  const [loading, setLoading]   = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const load = async () => {
    try {
      const res = await bookingsApi.list();
      setBookings(res.data.data?.bookings ?? []);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useFocusEffect(useCallback(() => { load(); }, []));

  const filtered = filter ? bookings.filter(b => b.status === filter) : bookings;

  if (loading) return <LoadingSpinner fullScreen message="Chargement..." />;

  return (
    <View style={styles.screen}>
      <StatusBar barStyle="light-content" backgroundColor={colors.primary} />

      {/* Header */}
      <View style={styles.header}>
        <Text style={styles.title}>Mes séjours</Text>
        <Text style={styles.subtitle}>{bookings.length} réservation{bookings.length !== 1 ? 's' : ''}</Text>
      </View>

      {/* Filter chips */}
      <View style={styles.filtersWrap}>
        <FlatList
          data={FILTERS}
          horizontal
          showsHorizontalScrollIndicator={false}
          keyExtractor={f => f.value}
          contentContainerStyle={styles.filtersRow}
          renderItem={({item}) => {
            const active = filter === item.value;
            return (
              <TouchableOpacity
                style={[styles.chip, active && styles.chipActive]}
                onPress={() => setFilter(item.value)}>
                <Text style={styles.chipEmoji}>{item.emoji}</Text>
                <Text style={[styles.chipText, active && styles.chipTextActive]}>
                  {item.label}
                </Text>
              </TouchableOpacity>
            );
          }}
        />
      </View>

      <FlatList
        data={filtered}
        keyExtractor={item => item.id.toString()}
        contentContainerStyle={styles.list}
        showsVerticalScrollIndicator={true}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={() => { setRefreshing(true); load(); }}
            tintColor={colors.primary}
          />
        }
        ListEmptyComponent={
          <View style={styles.empty}>
            <Text style={styles.emptyEmoji}>📅</Text>
            <Text style={styles.emptyTitle}>Aucune réservation</Text>
            <Text style={styles.emptySub}>
              {filter
                ? 'Pas de réservation avec ce statut.'
                : 'Vos séjours apparaîtront ici après réservation.'}
            </Text>
          </View>
        }
        renderItem={({item}) => (
          <BookingCard
            booking={item}
            onPress={() => navigation.navigate('BookingDetail', {bookingId: item.id})}
          />
        )}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  screen: {flex: 1, backgroundColor: colors.background},

  header: {
    backgroundColor: colors.primary,
    paddingHorizontal: spacing.lg,
    paddingTop: 52,
    paddingBottom: spacing.lg,
  },
  title:    {...typography.h1, color: '#fff'},
  subtitle: {...typography.body, color: 'rgba(255,255,255,0.7)', marginTop: 2},

  filtersWrap: {
    backgroundColor: colors.surface,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  filtersRow: {paddingHorizontal: spacing.lg, paddingVertical: 10, gap: 8},
  chip: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: radius.full,
    backgroundColor: colors.gray100,
    borderWidth: 1.5,
    borderColor: 'transparent',
  },
  chipActive: {backgroundColor: colors.primaryFaint, borderColor: colors.primary},
  chipEmoji: {fontSize: 13},
  chipText: {...typography.label, color: colors.textSecondary},
  chipTextActive: {color: colors.primary, fontWeight: '600'},

  list: {padding: spacing.lg, paddingBottom: 90},

  empty: {alignItems: 'center', paddingTop: 60, paddingHorizontal: spacing.xl},
  emptyEmoji: {fontSize: 52, marginBottom: 14},
  emptyTitle: {...typography.h3, color: colors.textSecondary, textAlign: 'center'},
  emptySub: {
    ...typography.body,
    color: colors.textTertiary,
    textAlign: 'center',
    marginTop: 8,
    lineHeight: 22,
  },
});
