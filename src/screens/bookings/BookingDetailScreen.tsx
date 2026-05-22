import React, {useEffect, useState} from 'react';
import {
  Alert,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import {NativeStackNavigationProp} from '@react-navigation/native-stack';
import {RouteProp} from '@react-navigation/native';
import {bookingsApi} from '../../api/bookings';
import {Button} from '../../components/Button';
import {LoadingSpinner} from '../../components/LoadingSpinner';
import {Booking, BookingsStackParamList} from '../../types';
import {colors, radius, spacing, typography} from '../../utils/theme';
import {formatCFA, formatDate, getStatusColor, getStatusLabel} from '../../utils/helpers';

type Props = {
  navigation: NativeStackNavigationProp<BookingsStackParamList, 'BookingDetail'>;
  route: RouteProp<BookingsStackParamList, 'BookingDetail'>;
};

export const BookingDetailScreen: React.FC<Props> = ({navigation, route}) => {
  const {bookingId} = route.params;
  const [booking, setBooking] = useState<Booking | null>(null);
  const [loading, setLoading] = useState(true);
  const [cancelling, setCancelling] = useState(false);

  useEffect(() => {
    bookingsApi.show(bookingId)
      .then(res => setBooking(res.data.data))
      .catch(() => Alert.alert('Erreur', 'Réservation introuvable.'))
      .finally(() => setLoading(false));
  }, [bookingId]);

  const handleCancel = () => {
    Alert.alert(
      'Annuler la réservation',
      'Voulez-vous vraiment annuler cette réservation ?',
      [
        {text: 'Non', style: 'cancel'},
        {
          text: 'Oui, annuler',
          style: 'destructive',
          onPress: async () => {
            setCancelling(true);
            try {
              await bookingsApi.cancel(bookingId);
              setBooking(prev => prev ? {...prev, status: 'cancelled'} : null);
              Alert.alert('Succès', 'Réservation annulée.');
            } catch (err: any) {
              Alert.alert('Erreur', err.response?.data?.message ?? 'Annulation échouée.');
            } finally {
              setCancelling(false);
            }
          },
        },
      ],
    );
  };

  if (loading) return <LoadingSpinner fullScreen message="Chargement..." />;
  if (!booking) return null;

  const statusColor = getStatusColor(booking.status);
  const canCancel = ['pending', 'confirmed'].includes(booking.status);
  const canPay = booking.status === 'pending' && booking.payment_status !== 'paid';

  return (
    <ScrollView style={styles.screen} showsVerticalScrollIndicator={false}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.back}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <Text style={styles.title}>Réservation</Text>
      </View>

      {/* Status card */}
      <View style={[styles.statusCard, {borderColor: statusColor + '40', backgroundColor: statusColor + '08'}]}>
        <View style={[styles.statusDot, {backgroundColor: statusColor}]} />
        <View>
          <Text style={[styles.statusLabel, {color: statusColor}]}>
            {getStatusLabel(booking.status)}
          </Text>
          <Text style={styles.statusRef}>Réf: #{booking.reference}</Text>
        </View>
      </View>

      {/* Property */}
      <View style={styles.card}>
        <Text style={styles.cardTitle}>Logement</Text>
        <Text style={styles.propertyName}>{booking.property?.title ?? 'Propriété'}</Text>
        <Text style={styles.propertyCity}>{booking.property?.city ?? ''}</Text>
      </View>

      {/* Dates */}
      <View style={styles.card}>
        <Text style={styles.cardTitle}>Dates</Text>
        <View style={styles.datesRow}>
          <View style={styles.dateCol}>
            <Text style={styles.dateType}>Arrivée</Text>
            <Text style={styles.dateValue}>{formatDate(booking.check_in)}</Text>
          </View>
          <View style={styles.nightBadge}>
            <Text style={styles.nightText}>{booking.nights}n</Text>
          </View>
          <View style={[styles.dateCol, {alignItems: 'flex-end'}]}>
            <Text style={styles.dateType}>Départ</Text>
            <Text style={styles.dateValue}>{formatDate(booking.check_out)}</Text>
          </View>
        </View>
        <Text style={styles.guestInfo}>👥 {booking.guests} voyageur{booking.guests > 1 ? 's' : ''}</Text>
      </View>

      {/* Payment summary */}
      <View style={styles.card}>
        <Text style={styles.cardTitle}>Paiement</Text>
        <View style={styles.priceRow}>
          <Text style={styles.priceLabel}>Sous-total</Text>
          <Text style={styles.priceValue}>{formatCFA(booking.subtotal)}</Text>
        </View>
        <View style={styles.priceRow}>
          <Text style={styles.priceLabel}>Frais de service</Text>
          <Text style={styles.priceValue}>{formatCFA(booking.service_fee ?? 0)}</Text>
        </View>
        <View style={[styles.priceRow, styles.totalRow]}>
          <Text style={styles.totalLabel}>Total</Text>
          <Text style={styles.totalValue}>{formatCFA(booking.total_amount)}</Text>
        </View>
        <View style={[styles.paymentStatus, {backgroundColor: booking.payment_status === 'paid' ? '#10B98115' : '#F59E0B15'}]}>
          <Text style={{fontSize: 13, fontWeight: '600', color: booking.payment_status === 'paid' ? '#10B981' : '#F59E0B'}}>
            {booking.payment_status === 'paid' ? '✅ Payé' : '⏳ En attente de paiement'}
          </Text>
        </View>
      </View>

      {/* Actions */}
      <View style={styles.actionsCard}>
        {canPay && (
          <Button
            title="💳 Payer maintenant"
            onPress={() => navigation.navigate('Payment', {bookingId: booking.id})}
            fullWidth
            style={styles.payBtn}
          />
        )}
        {canCancel && (
          <Button
            title="Annuler la réservation"
            variant="outline"
            onPress={handleCancel}
            loading={cancelling}
            fullWidth
          />
        )}
      </View>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  screen: {flex: 1, backgroundColor: colors.background},
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: spacing.lg,
    paddingTop: spacing.xl + 8,
  },
  back: {marginRight: 12},
  backArrow: {fontSize: 22, color: colors.primary},
  title: {...typography.h2, color: colors.text},
  statusCard: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    marginHorizontal: spacing.lg,
    marginBottom: 12,
    padding: 14,
    borderRadius: radius.lg,
    borderWidth: 1.5,
  },
  statusDot: {width: 10, height: 10, borderRadius: 5},
  statusLabel: {fontSize: 15, fontWeight: '700'},
  statusRef: {...typography.caption, color: colors.textSecondary, marginTop: 2, fontFamily: 'monospace'},
  card: {
    backgroundColor: colors.surface,
    borderRadius: radius.xl,
    padding: spacing.md,
    marginHorizontal: spacing.lg,
    marginBottom: 12,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: {width: 0, height: 1},
    shadowOpacity: 0.05,
    shadowRadius: 4,
  },
  cardTitle: {...typography.h3, color: colors.text, marginBottom: 10},
  propertyName: {fontSize: 16, fontWeight: '600', color: colors.text},
  propertyCity: {...typography.caption, color: colors.textSecondary, marginTop: 2},
  datesRow: {flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between'},
  dateCol: {},
  dateType: {...typography.caption, color: colors.textSecondary},
  dateValue: {fontSize: 15, fontWeight: '600', color: colors.text, marginTop: 2},
  nightBadge: {
    backgroundColor: colors.primary + '15',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: radius.full,
  },
  nightText: {fontSize: 13, fontWeight: '700', color: colors.primary},
  guestInfo: {...typography.body, color: colors.textSecondary, marginTop: 12},
  priceRow: {flexDirection: 'row', justifyContent: 'space-between', marginBottom: 8},
  priceLabel: {...typography.body, color: colors.textSecondary},
  priceValue: {...typography.body, color: colors.text},
  totalRow: {
    paddingTop: 10,
    borderTopWidth: 1,
    borderTopColor: colors.border,
    marginBottom: 10,
  },
  totalLabel: {fontSize: 15, fontWeight: '700', color: colors.text},
  totalValue: {fontSize: 17, fontWeight: '800', color: colors.primary},
  paymentStatus: {padding: 10, borderRadius: radius.md, alignItems: 'center'},
  actionsCard: {padding: spacing.lg, gap: 10},
  payBtn: {marginBottom: 4},
});
