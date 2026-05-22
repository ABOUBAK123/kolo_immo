import React from 'react';
import {StyleSheet, Text, TouchableOpacity, View} from 'react-native';
import {Booking} from '../types';
import {colors, radius, shadows, spacing, typography} from '../utils/theme';
import {formatCFA, formatDate, getStatusColor} from '../utils/helpers';

interface BookingCardProps {
  booking: Booking;
  onPress: () => void;
}

const STATUS_INFO: Record<string, {label: string; emoji: string}> = {
  pending:   {label: 'En attente', emoji: '⏳'},
  confirmed: {label: 'Confirmée',  emoji: '✅'},
  cancelled: {label: 'Annulée',    emoji: '❌'},
  completed: {label: 'Terminée',   emoji: '🏁'},
  disputed:  {label: 'Litige',     emoji: '⚠️'},
};

export const BookingCard: React.FC<BookingCardProps> = ({booking, onPress}) => {
  const statusColor = getStatusColor(booking.status);
  const info = STATUS_INFO[booking.status] ?? {label: booking.status, emoji: '📋'};

  return (
    <TouchableOpacity
      style={[styles.card, shadows.sm, {borderLeftColor: statusColor}]}
      onPress={onPress}
      activeOpacity={0.85}>

      {/* Top */}
      <View style={styles.top}>
        <View style={{flex: 1}}>
          <Text style={styles.property} numberOfLines={1}>
            {booking.property?.title ?? 'Propriété'}
          </Text>
          {booking.property?.city ? (
            <Text style={styles.city}>📍 {booking.property.city}</Text>
          ) : null}
        </View>
        <View style={[styles.badge, {backgroundColor: statusColor + '18'}]}>
          <Text style={styles.badgeEmoji}>{info.emoji}</Text>
          <Text style={[styles.badgeLabel, {color: statusColor}]}>{info.label}</Text>
        </View>
      </View>

      <View style={styles.divider} />

      {/* Dates */}
      <View style={styles.datesRow}>
        <View style={styles.dateBlock}>
          <Text style={styles.dateCaption}>Arrivée</Text>
          <Text style={styles.dateValue}>{formatDate(booking.check_in)}</Text>
        </View>
        <View style={styles.nightsPill}>
          <Text style={styles.nightsNum}>{booking.nights}</Text>
          <Text style={styles.nightsLbl}>nuit{booking.nights > 1 ? 's' : ''}</Text>
        </View>
        <View style={[styles.dateBlock, {alignItems: 'flex-end'}]}>
          <Text style={styles.dateCaption}>Départ</Text>
          <Text style={styles.dateValue}>{formatDate(booking.check_out)}</Text>
        </View>
      </View>

      <View style={styles.divider} />

      {/* Bottom */}
      <View style={styles.bottom}>
        <Text style={styles.ref}>Réf. {booking.reference}</Text>
        <Text style={styles.total}>{formatCFA(booking.total_amount)}</Text>
      </View>
    </TouchableOpacity>
  );
};

const styles = StyleSheet.create({
  card: {
    backgroundColor: colors.surface,
    borderRadius: radius.xl,
    padding: spacing.md,
    marginBottom: spacing.sm,
    borderLeftWidth: 4,
  },
  top: {flexDirection: 'row', alignItems: 'flex-start', gap: 10},
  property: {...typography.h4, color: colors.text, marginBottom: 2},
  city: {...typography.caption, color: colors.textSecondary},

  badge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: radius.full,
  },
  badgeEmoji: {fontSize: 11},
  badgeLabel: {...typography.labelSm, fontWeight: '700'},

  divider: {height: 1, backgroundColor: colors.borderLight, marginVertical: 12},

  datesRow: {flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center'},
  dateBlock: {flex: 1},
  dateCaption: {...typography.caption, color: colors.textTertiary, marginBottom: 2},
  dateValue: {...typography.label, color: colors.textMed, fontWeight: '600'},

  nightsPill: {
    alignItems: 'center',
    backgroundColor: colors.primaryFaint,
    borderRadius: radius.md,
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderWidth: 1,
    borderColor: colors.primaryLight,
  },
  nightsNum: {fontSize: 17, fontWeight: '800', color: colors.primary},
  nightsLbl: {...typography.caption, color: colors.primaryMid},

  bottom: {flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center'},
  ref: {...typography.caption, color: colors.textTertiary},
  total: {fontSize: 16, fontWeight: '800', color: colors.primary},
});
