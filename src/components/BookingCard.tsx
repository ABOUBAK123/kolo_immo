import React from 'react';
import {StyleSheet, Text, TouchableOpacity, View} from 'react-native';
import {Booking} from '../types';
import {colors, radius, shadows, spacing, typography} from '../utils/theme';
import {formatCFA, formatDate, getStatusColor, getStatusLabel} from '../utils/helpers';

interface BookingCardProps {
  booking: Booking;
  onPress: () => void;
}

export const BookingCard: React.FC<BookingCardProps> = ({booking, onPress}) => {
  const statusColor = getStatusColor(booking.status);

  return (
    <TouchableOpacity style={[styles.card, shadows.sm]} onPress={onPress} activeOpacity={0.85}>
      <View style={styles.header}>
        <View style={styles.refRow}>
          <Text style={styles.ref}>#{booking.reference}</Text>
          <View style={[styles.badge, {backgroundColor: statusColor + '20'}]}>
            <Text style={[styles.badgeText, {color: statusColor}]}>
              {getStatusLabel(booking.status)}
            </Text>
          </View>
        </View>
      </View>
      <Text style={styles.property} numberOfLines={1}>
        {booking.property?.title ?? 'Propriété'}
      </Text>
      <Text style={styles.location}>{booking.property?.city ?? ''}</Text>
      <View style={styles.divider} />
      <View style={styles.footer}>
        <View>
          <Text style={styles.dateLabel}>Arrivée</Text>
          <Text style={styles.date}>{formatDate(booking.check_in)}</Text>
        </View>
        <View style={styles.nightBadge}>
          <Text style={styles.nightText}>{booking.nights} nuit{booking.nights > 1 ? 's' : ''}</Text>
        </View>
        <View style={{alignItems: 'flex-end'}}>
          <Text style={styles.dateLabel}>Départ</Text>
          <Text style={styles.date}>{formatDate(booking.check_out)}</Text>
        </View>
      </View>
      <View style={styles.amount}>
        <Text style={styles.amountLabel}>Total</Text>
        <Text style={styles.amountValue}>{formatCFA(booking.total_amount)}</Text>
      </View>
    </TouchableOpacity>
  );
};

const styles = StyleSheet.create({
  card: {
    backgroundColor: colors.surface,
    borderRadius: radius.xl,
    padding: spacing.md,
    marginBottom: spacing.md,
  },
  header: {marginBottom: 8},
  refRow: {flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center'},
  ref: {...typography.caption, color: colors.textSecondary, fontFamily: 'monospace'},
  badge: {paddingHorizontal: 10, paddingVertical: 3, borderRadius: radius.full},
  badgeText: {fontSize: 12, fontWeight: '600'},
  property: {...typography.h3, color: colors.text},
  location: {...typography.caption, color: colors.textSecondary, marginTop: 2},
  divider: {height: 1, backgroundColor: colors.border, marginVertical: 12},
  footer: {flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center'},
  dateLabel: {...typography.caption, color: colors.textSecondary},
  date: {fontSize: 13, fontWeight: '600', color: colors.text, marginTop: 2},
  nightBadge: {
    backgroundColor: colors.primary + '15',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: radius.full,
  },
  nightText: {fontSize: 13, fontWeight: '600', color: colors.primary},
  amount: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: 12,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: colors.border,
  },
  amountLabel: {...typography.body, color: colors.textSecondary},
  amountValue: {fontSize: 16, fontWeight: '700', color: colors.primary},
});
