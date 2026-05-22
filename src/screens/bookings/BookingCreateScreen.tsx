import React, {useState} from 'react';
import {
  Alert,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import {NativeStackNavigationProp} from '@react-navigation/native-stack';
import {RouteProp} from '@react-navigation/native';
import {bookingsApi} from '../../api/bookings';
import {Button} from '../../components/Button';
import {HomeStackParamList} from '../../types';
import {colors, radius, spacing, typography} from '../../utils/theme';
import {diffInDays, formatCFA, formatDate} from '../../utils/helpers';

type Props = {
  navigation: NativeStackNavigationProp<HomeStackParamList, 'BookingCreate'>;
  route: RouteProp<HomeStackParamList, 'BookingCreate'>;
};

export const BookingCreateScreen: React.FC<Props> = ({navigation, route}) => {
  const {property} = route.params;
  const [checkIn, setCheckIn] = useState(route.params.checkIn ?? '');
  const [checkOut, setCheckOut] = useState(route.params.checkOut ?? '');
  const [guests, setGuests] = useState(1);
  const [specialRequests, setSpecialRequests] = useState('');
  const [loading, setLoading] = useState(false);

  const nights = checkIn && checkOut ? Math.max(diffInDays(checkIn, checkOut), 0) : 0;
  const subtotal = nights * property.price_per_night;
  const platformFee = Math.round(subtotal * 0.1);
  const total = subtotal + platformFee;

  const handleBook = async () => {
    if (!checkIn || !checkOut) {
      Alert.alert('Erreur', 'Sélectionnez les dates d\'arrivée et de départ.');
      return;
    }
    if (nights < 1) {
      Alert.alert('Erreur', 'Le départ doit être après l\'arrivée.');
      return;
    }
    setLoading(true);
    try {
      const res = await bookingsApi.create({
        property_id: property.id,
        check_in: checkIn,
        check_out: checkOut,
        guests,
        special_requests: specialRequests || undefined,
      });
      const booking = res.data.data;
      navigation.replace('BookingDetail', {bookingId: booking.id});
    } catch (err: any) {
      Alert.alert('Erreur', err.response?.data?.message ?? 'Réservation échouée.');
    } finally {
      setLoading(false);
    }
  };

  const DateInput: React.FC<{label: string; value: string; onChange: (v: string) => void}> = ({
    label, value, onChange,
  }) => (
    <View style={styles.dateField}>
      <Text style={styles.dateLabel}>{label}</Text>
      <TextInput
        style={styles.dateInput}
        placeholder="YYYY-MM-DD"
        placeholderTextColor={colors.textLight}
        value={value}
        onChangeText={onChange}
        keyboardType="numeric"
      />
    </View>
  );

  return (
    <KeyboardAvoidingView
      style={styles.flex}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <ScrollView contentContainerStyle={styles.container}>
        {/* Header */}
        <View style={styles.header}>
          <TouchableOpacity onPress={() => navigation.goBack()} style={styles.back}>
            <Text style={styles.backArrow}>←</Text>
          </TouchableOpacity>
          <Text style={styles.title}>Réserver</Text>
        </View>

        {/* Property summary */}
        <View style={styles.propertySummary}>
          <Text style={styles.propertyTitle} numberOfLines={1}>{property.title}</Text>
          <Text style={styles.propertyCity}>{property.city}</Text>
        </View>

        {/* Dates */}
        <View style={styles.card}>
          <Text style={styles.cardTitle}>Dates du séjour</Text>
          <View style={styles.datesRow}>
            <DateInput label="Arrivée" value={checkIn} onChange={setCheckIn} />
            <View style={styles.dateSep}><Text style={styles.dateSepText}>→</Text></View>
            <DateInput label="Départ" value={checkOut} onChange={setCheckOut} />
          </View>
        </View>

        {/* Guests */}
        <View style={styles.card}>
          <Text style={styles.cardTitle}>Voyageurs</Text>
          <View style={styles.guestsRow}>
            <TouchableOpacity
              style={styles.guestBtn}
              onPress={() => setGuests(g => Math.max(1, g - 1))}>
              <Text style={styles.guestBtnText}>−</Text>
            </TouchableOpacity>
            <Text style={styles.guestCount}>{guests}</Text>
            <TouchableOpacity
              style={styles.guestBtn}
              onPress={() => setGuests(g => Math.min(property.capacity, g + 1))}>
              <Text style={styles.guestBtnText}>+</Text>
            </TouchableOpacity>
            <Text style={styles.guestMax}>/ {property.capacity} max</Text>
          </View>
        </View>

        {/* Special requests */}
        <View style={styles.card}>
          <Text style={styles.cardTitle}>Demandes particulières</Text>
          <TextInput
            style={styles.textarea}
            multiline
            numberOfLines={3}
            placeholder="Arrivée tardive, berceau, régime alimentaire..."
            placeholderTextColor={colors.textLight}
            value={specialRequests}
            onChangeText={setSpecialRequests}
          />
        </View>

        {/* Price breakdown */}
        {nights > 0 && (
          <View style={styles.card}>
            <Text style={styles.cardTitle}>Détail du prix</Text>
            <View style={styles.priceRow}>
              <Text style={styles.priceLabel}>
                {formatCFA(property.price_per_night)} × {nights} nuit{nights > 1 ? 's' : ''}
              </Text>
              <Text style={styles.priceValue}>{formatCFA(subtotal)}</Text>
            </View>
            <View style={styles.priceRow}>
              <Text style={styles.priceLabel}>Frais de service (10%)</Text>
              <Text style={styles.priceValue}>{formatCFA(platformFee)}</Text>
            </View>
            <View style={[styles.priceRow, styles.totalRow]}>
              <Text style={styles.totalLabel}>Total</Text>
              <Text style={styles.totalValue}>{formatCFA(total)}</Text>
            </View>
          </View>
        )}

        <Button
          title={`Confirmer la réservation${nights > 0 ? ` — ${formatCFA(total)}` : ''}`}
          onPress={handleBook}
          loading={loading}
          fullWidth
          style={styles.confirmBtn}
          disabled={nights < 1}
        />
      </ScrollView>
    </KeyboardAvoidingView>
  );
};

const styles = StyleSheet.create({
  flex: {flex: 1, backgroundColor: colors.background},
  container: {padding: spacing.lg, paddingTop: spacing.xl},
  header: {flexDirection: 'row', alignItems: 'center', marginBottom: 20},
  back: {marginRight: 12},
  backArrow: {fontSize: 22, color: colors.primary},
  title: {...typography.h2, color: colors.text},
  propertySummary: {
    backgroundColor: colors.primary + '10',
    borderRadius: radius.lg,
    padding: 14,
    marginBottom: 16,
    borderLeftWidth: 4,
    borderLeftColor: colors.primary,
  },
  propertyTitle: {fontSize: 15, fontWeight: '700', color: colors.primary},
  propertyCity: {...typography.caption, color: colors.textSecondary, marginTop: 2},
  card: {
    backgroundColor: colors.surface,
    borderRadius: radius.xl,
    padding: spacing.md,
    marginBottom: 12,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: {width: 0, height: 1},
    shadowOpacity: 0.05,
    shadowRadius: 4,
  },
  cardTitle: {...typography.h3, color: colors.text, marginBottom: 12},
  datesRow: {flexDirection: 'row', alignItems: 'center'},
  dateField: {flex: 1},
  dateLabel: {...typography.caption, color: colors.textSecondary, marginBottom: 4},
  dateInput: {
    borderWidth: 1.5,
    borderColor: colors.border,
    borderRadius: radius.md,
    paddingHorizontal: 12,
    paddingVertical: 10,
    fontSize: 14,
    color: colors.text,
    backgroundColor: colors.background,
  },
  dateSep: {paddingHorizontal: 8, paddingTop: 16},
  dateSepText: {fontSize: 18, color: colors.textLight},
  guestsRow: {flexDirection: 'row', alignItems: 'center', gap: 16},
  guestBtn: {
    width: 40,
    height: 40,
    borderRadius: 20,
    borderWidth: 1.5,
    borderColor: colors.primary,
    alignItems: 'center',
    justifyContent: 'center',
  },
  guestBtnText: {fontSize: 22, color: colors.primary, lineHeight: 28},
  guestCount: {fontSize: 22, fontWeight: '700', color: colors.text, minWidth: 30, textAlign: 'center'},
  guestMax: {...typography.caption, color: colors.textSecondary},
  textarea: {
    borderWidth: 1.5,
    borderColor: colors.border,
    borderRadius: radius.md,
    padding: 12,
    fontSize: 14,
    color: colors.text,
    textAlignVertical: 'top',
    minHeight: 80,
  },
  priceRow: {flexDirection: 'row', justifyContent: 'space-between', marginBottom: 8},
  priceLabel: {...typography.body, color: colors.textSecondary},
  priceValue: {...typography.body, color: colors.text, fontWeight: '500'},
  totalRow: {
    marginTop: 8,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: colors.border,
    marginBottom: 0,
  },
  totalLabel: {fontSize: 16, fontWeight: '700', color: colors.text},
  totalValue: {fontSize: 18, fontWeight: '800', color: colors.primary},
  confirmBtn: {marginTop: 8},
});
