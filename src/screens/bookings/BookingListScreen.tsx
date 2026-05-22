import React, {useCallback, useEffect, useState} from 'react';
import {
  FlatList,
  RefreshControl,
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
import {colors, spacing, typography} from '../../utils/theme';

type Props = {
  navigation: NativeStackNavigationProp<BookingsStackParamList, 'BookingList'>;
};

const FILTERS = [
  {value: '', label: 'Tout'},
  {value: 'pending', label: 'En attente'},
  {value: 'confirmed', label: 'Confirmées'},
  {value: 'completed', label: 'Terminées'},
  {value: 'cancelled', label: 'Annulées'},
];

export const BookingListScreen: React.FC<Props> = ({navigation}) => {
  const [bookings, setBookings] = useState<Booking[]>([]);
  const [filter, setFilter] = useState('');
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const load = async () => {
    try {
      const res = await bookingsApi.list();
      setBookings(res.data.data);
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
      <View style={styles.header}>
        <Text style={styles.title}>Mes séjours</Text>
      </View>

      {/* Status filter */}
      <FlatList
        data={FILTERS}
        horizontal
        showsHorizontalScrollIndicator={false}
        keyExtractor={item => item.value}
        contentContainerStyle={styles.filterRow}
        renderItem={({item}) => (
          <TouchableOpacity
            style={[styles.chip, filter === item.value && styles.chipActive]}
            onPress={() => setFilter(item.value)}>
            <Text style={[styles.chipText, filter === item.value && styles.chipTextActive]}>
              {item.label}
            </Text>
          </TouchableOpacity>
        )}
      />

      <FlatList
        data={filtered}
        keyExtractor={item => item.id.toString()}
        contentContainerStyle={styles.list}
        showsVerticalScrollIndicator={false}
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
            <Text style={styles.emptyText}>Aucune réservation</Text>
            <Text style={styles.emptySubText}>
              {filter ? 'Pas de réservation avec ce statut.' : 'Vos réservations apparaîtront ici.'}
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
  header: {padding: spacing.lg, paddingTop: spacing.xl + 8},
  title: {...typography.h1, color: colors.text},
  filterRow: {paddingHorizontal: spacing.lg, gap: 8, marginBottom: 12},
  chip: {
    paddingHorizontal: 14,
    paddingVertical: 7,
    borderRadius: 999,
    backgroundColor: colors.surface,
    borderWidth: 1.5,
    borderColor: colors.border,
  },
  chipActive: {backgroundColor: colors.primary, borderColor: colors.primary},
  chipText: {fontSize: 13, fontWeight: '600', color: colors.textSecondary},
  chipTextActive: {color: '#fff'},
  list: {paddingHorizontal: spacing.lg, paddingBottom: 80},
  empty: {alignItems: 'center', paddingTop: 60},
  emptyEmoji: {fontSize: 48, marginBottom: 12},
  emptyText: {...typography.h3, color: colors.textSecondary},
  emptySubText: {...typography.body, color: colors.textLight, marginTop: 8, textAlign: 'center'},
});
