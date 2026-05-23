import React, {useMemo, useState} from 'react';
import {
  Alert,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  StatusBar,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import {NativeStackNavigationProp} from '@react-navigation/native-stack';
import {RouteProp} from '@react-navigation/native';
import DatePicker from 'react-native-date-picker';
import {bookingsApi} from '../../api/bookings';
import {Button} from '../../components/Button';
import {HomeStackParamList} from '../../types';
import {colors, radius, shadows, spacing, typography} from '../../utils/theme';
import {formatCFA} from '../../utils/helpers';

type Props = {
  navigation: NativeStackNavigationProp<HomeStackParamList, 'BookingCreate'>;
  route: RouteProp<HomeStackParamList, 'BookingCreate'>;
};

// ─── Helpers ──────────────────────────────────────────────────────────────────

const toDateStr = (d: Date): string => {
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${day}`;
};

const displayDate = (d: Date): string =>
  d.toLocaleDateString('fr-FR', {day: '2-digit', month: 'long', year: 'numeric'});

const addDays = (d: Date, n: number): Date => {
  const r = new Date(d);
  r.setDate(r.getDate() + n);
  return r;
};

const parseInitial = (str: string | undefined): Date | null => {
  if (!str) return null;
  const d = new Date(str);
  return isNaN(d.getTime()) ? null : d;
};

// ─── Screen ───────────────────────────────────────────────────────────────────

export const BookingCreateScreen: React.FC<Props> = ({navigation, route}) => {
  const {property} = route.params;

  // Arrival date (from params or null)
  const [checkInDate, setCheckInDate] = useState<Date | null>(
    parseInitial(route.params.checkIn),
  );
  // Nights stepper
  const [nights, setNights] = useState<number>(() => {
    if (route.params.checkIn && route.params.checkOut) {
      const s = new Date(route.params.checkIn).getTime();
      const e = new Date(route.params.checkOut).getTime();
      const d = Math.round((e - s) / 86400000);
      return d > 0 ? d : 1;
    }
    return 1;
  });

  const [pickerOpen, setPickerOpen]         = useState(false);
  const [guests, setGuests]                 = useState(1);
  const [specialRequests, setSpecialRequests] = useState('');
  const [loading, setLoading]               = useState(false);

  // Departure = arrival + nights (derived)
  const checkOutDate = useMemo(
    () => (checkInDate ? addDays(checkInDate, nights) : null),
    [checkInDate, nights],
  );

  // Pricing
  const subtotal   = nights * property.price_per_night;
  const platformFee = Math.round(subtotal * 0.1);
  const total      = subtotal + platformFee;

  const today = useMemo(() => {
    const d = new Date();
    d.setHours(0, 0, 0, 0);
    return d;
  }, []);

  // ── Submit ────────────────────────────────────────────────────────────────
  const handleBook = async () => {
    if (!checkInDate || !checkOutDate) {
      Alert.alert('Date requise', 'Veuillez sélectionner une date d\'arrivée.');
      return;
    }
    if (checkInDate < today) {
      Alert.alert('Date invalide', 'La date d\'arrivée ne peut pas être dans le passé.');
      return;
    }
    setLoading(true);
    try {
      const res = await bookingsApi.create({
        property_id:      property.id,
        check_in:         toDateStr(checkInDate),
        check_out:        toDateStr(checkOutDate),
        guests,
        special_requests: specialRequests || undefined,
      });
      navigation.replace('BookingDetail', {bookingId: res.data.data.id});
    } catch (err: any) {
      Alert.alert('Erreur', err.response?.data?.message ?? 'Réservation échouée.');
    } finally {
      setLoading(false);
    }
  };

  // ─── Render ───────────────────────────────────────────────────────────────
  return (
    <View style={styles.screen}>
      <StatusBar barStyle="light-content" backgroundColor={colors.primary} />

      {/* ── Header ── */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
          <Text style={styles.backIcon}>‹</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Réserver</Text>
        <View style={{width: 40}} />
      </View>

      <KeyboardAvoidingView
        style={{flex: 1}}
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
        <ScrollView
          contentContainerStyle={styles.content}
          showsVerticalScrollIndicator={true}
          keyboardShouldPersistTaps="handled">

          {/* ── Property summary ── */}
          <View style={styles.propertySummary}>
            <View style={styles.propertyIcon}>
              <Text style={{fontSize: 18}}>🏠</Text>
            </View>
            <View style={{flex: 1}}>
              <Text style={styles.propertyTitle} numberOfLines={1}>
                {property.title}
              </Text>
              <Text style={styles.propertyCity}>
                {property.city} · {formatCFA(property.price_per_night)} / nuit
              </Text>
            </View>
          </View>

          {/* ── Dates du séjour ── */}
          <View style={styles.card}>
            <Text style={styles.cardTitle}>📅  Dates du séjour</Text>

            {/* Arrivée — date picker */}
            <View style={styles.dateRow}>
              <View style={styles.dateIconWrap}>
                <Text style={styles.dateIcon}>✈️</Text>
              </View>
              <View style={styles.dateMeta}>
                <Text style={styles.dateMetaLabel}>Arrivée</Text>
                {checkInDate ? (
                  <Text style={styles.dateMetaValue}>{displayDate(checkInDate)}</Text>
                ) : (
                  <Text style={styles.dateMetaPlaceholder}>Sélectionner une date</Text>
                )}
              </View>
              <TouchableOpacity
                style={styles.datePickerBtn}
                onPress={() => setPickerOpen(true)}
                activeOpacity={0.75}>
                <Text style={styles.datePickerBtnText}>
                  {checkInDate ? 'Modifier' : 'Choisir'}
                </Text>
              </TouchableOpacity>
            </View>

            <View style={styles.divider} />

            {/* Nombre de nuits — stepper */}
            <View style={styles.nightsRow}>
              <View style={styles.dateIconWrap}>
                <Text style={styles.dateIcon}>🌙</Text>
              </View>
              <View style={styles.dateMeta}>
                <Text style={styles.dateMetaLabel}>Durée du séjour</Text>
                <Text style={styles.dateMetaValue}>
                  {nights} nuit{nights > 1 ? 's' : ''}
                </Text>
              </View>
              <View style={styles.stepper}>
                <TouchableOpacity
                  style={[styles.stepBtn, nights <= 1 && styles.stepBtnDisabled]}
                  onPress={() => setNights(n => Math.max(1, n - 1))}
                  disabled={nights <= 1}
                  activeOpacity={0.7}>
                  <Text style={styles.stepBtnText}>−</Text>
                </TouchableOpacity>
                <Text style={styles.stepValue}>{nights}</Text>
                <TouchableOpacity
                  style={[styles.stepBtn, nights >= 90 && styles.stepBtnDisabled]}
                  onPress={() => setNights(n => Math.min(90, n + 1))}
                  disabled={nights >= 90}
                  activeOpacity={0.7}>
                  <Text style={styles.stepBtnText}>+</Text>
                </TouchableOpacity>
              </View>
            </View>

            <View style={styles.divider} />

            {/* Départ — calculé automatiquement */}
            <View style={styles.dateRow}>
              <View style={styles.dateIconWrap}>
                <Text style={styles.dateIcon}>🏁</Text>
              </View>
              <View style={styles.dateMeta}>
                <Text style={styles.dateMetaLabel}>Départ</Text>
                {checkOutDate ? (
                  <Text style={styles.dateMetaValue}>{displayDate(checkOutDate)}</Text>
                ) : (
                  <Text style={styles.dateMetaPlaceholder}>— calculé automatiquement —</Text>
                )}
              </View>
              {checkOutDate && (
                <View style={styles.autoTag}>
                  <Text style={styles.autoTagText}>Auto</Text>
                </View>
              )}
            </View>
          </View>

          {/* ── Voyageurs ── */}
          <View style={styles.card}>
            <Text style={styles.cardTitle}>👥  Voyageurs</Text>
            <View style={styles.guestsRow}>
              <TouchableOpacity
                style={[styles.stepBtn, guests <= 1 && styles.stepBtnDisabled]}
                onPress={() => setGuests(g => Math.max(1, g - 1))}
                disabled={guests <= 1}>
                <Text style={styles.stepBtnText}>−</Text>
              </TouchableOpacity>
              <View style={styles.guestCenter}>
                <Text style={styles.guestCount}>{guests}</Text>
                <Text style={styles.guestLabel}>
                  voyageur{guests > 1 ? 's' : ''} / {property.capacity} max
                </Text>
              </View>
              <TouchableOpacity
                style={[styles.stepBtn, guests >= property.capacity && styles.stepBtnDisabled]}
                onPress={() => setGuests(g => Math.min(property.capacity, g + 1))}
                disabled={guests >= property.capacity}>
                <Text style={styles.stepBtnText}>+</Text>
              </TouchableOpacity>
            </View>
          </View>

          {/* ── Demandes particulières ── */}
          <View style={styles.card}>
            <Text style={styles.cardTitle}>💬  Demandes particulières</Text>
            <TextInput
              style={styles.textarea}
              multiline
              numberOfLines={3}
              placeholder="Arrivée tardive, berceau, allergies..."
              placeholderTextColor={colors.textTertiary}
              value={specialRequests}
              onChangeText={setSpecialRequests}
              textAlignVertical="top"
            />
          </View>

          {/* ── Récapitulatif prix ── */}
          {checkInDate && (
            <View style={styles.card}>
              <Text style={styles.cardTitle}>💰  Récapitulatif</Text>

              <View style={styles.summaryBox}>
                <View style={styles.summaryRow}>
                  <Text style={styles.summaryLabel}>
                    {formatCFA(property.price_per_night)} × {nights} nuit{nights > 1 ? 's' : ''}
                  </Text>
                  <Text style={styles.summaryValue}>{formatCFA(subtotal)}</Text>
                </View>
                <View style={styles.summaryRow}>
                  <Text style={styles.summaryLabel}>Frais de service (10%)</Text>
                  <Text style={styles.summaryValue}>{formatCFA(platformFee)}</Text>
                </View>
                <View style={styles.summaryDivider} />
                <View style={styles.summaryRow}>
                  <Text style={styles.totalLabel}>Total à payer</Text>
                  <Text style={styles.totalValue}>{formatCFA(total)}</Text>
                </View>
              </View>
            </View>
          )}

          {/* ── Confirm button ── */}
          <Button
            title={
              !checkInDate
                ? 'Sélectionnez une date d\'arrivée'
                : `Confirmer — ${formatCFA(total)}`
            }
            onPress={handleBook}
            loading={loading}
            fullWidth
            style={styles.confirmBtn}
            disabled={!checkInDate || loading}
          />

          <View style={{height: 40}} />
        </ScrollView>
      </KeyboardAvoidingView>

      {/* ── Date Picker Modal ── */}
      <DatePicker
        modal
        open={pickerOpen}
        date={checkInDate ?? today}
        mode="date"
        locale="fr"
        title="Date d'arrivée"
        confirmText="Confirmer"
        cancelText="Annuler"
        minimumDate={today}
        onConfirm={date => {
          setPickerOpen(false);
          // Strip time to midnight local
          const d = new Date(date);
          d.setHours(0, 0, 0, 0);
          setCheckInDate(d);
        }}
        onCancel={() => setPickerOpen(false)}
      />
    </View>
  );
};

// ─── Styles ───────────────────────────────────────────────────────────────────

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
    width: 40, height: 40,
    alignItems: 'center', justifyContent: 'center',
    backgroundColor: 'rgba(255,255,255,0.18)',
    borderRadius: radius.full,
  },
  backIcon: {color: '#fff', fontSize: 26, fontWeight: '300', marginTop: -2},
  headerTitle: {...typography.h3, color: '#fff'},

  content: {padding: spacing.md},

  // Property summary
  propertySummary: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    backgroundColor: colors.primaryFaint,
    borderRadius: radius.xl,
    padding: spacing.md,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: colors.primaryLight,
  },
  propertyIcon: {
    width: 44, height: 44,
    backgroundColor: colors.primaryLight,
    borderRadius: radius.lg,
    alignItems: 'center', justifyContent: 'center',
  },
  propertyTitle: {...typography.h4, color: colors.primary},
  propertyCity: {...typography.caption, color: colors.textSecondary, marginTop: 2},

  // Card
  card: {
    backgroundColor: colors.surface,
    borderRadius: radius.xl,
    padding: spacing.md,
    marginBottom: 12,
    ...shadows.sm,
  },
  cardTitle: {...typography.h4, color: colors.text, marginBottom: 14},

  // Dates
  dateRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 4,
    gap: 12,
  },
  nightsRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 4,
    gap: 12,
  },
  dateIconWrap: {
    width: 36, height: 36,
    backgroundColor: colors.background,
    borderRadius: radius.lg,
    alignItems: 'center', justifyContent: 'center',
    flexShrink: 0,
  },
  dateIcon: {fontSize: 18},
  dateMeta: {flex: 1},
  dateMetaLabel: {...typography.caption, color: colors.textTertiary, marginBottom: 2},
  dateMetaValue: {...typography.h4, color: colors.text},
  dateMetaPlaceholder: {...typography.body, color: colors.textTertiary, fontStyle: 'italic'},

  datePickerBtn: {
    backgroundColor: colors.primary,
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: radius.full,
    flexShrink: 0,
  },
  datePickerBtnText: {...typography.labelSm, color: '#fff'},

  divider: {
    height: 1,
    backgroundColor: colors.borderLight,
    marginVertical: 12,
  },

  autoTag: {
    backgroundColor: colors.successLight,
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: radius.full,
    flexShrink: 0,
  },
  autoTagText: {...typography.labelSm, color: colors.success},

  // Stepper
  stepper: {flexDirection: 'row', alignItems: 'center', gap: 12, flexShrink: 0},
  stepBtn: {
    width: 36, height: 36,
    borderRadius: radius.full,
    backgroundColor: colors.primaryFaint,
    borderWidth: 1.5,
    borderColor: colors.primaryLight,
    alignItems: 'center', justifyContent: 'center',
  },
  stepBtnDisabled: {opacity: 0.3},
  stepBtnText: {fontSize: 20, color: colors.primary, fontWeight: '700', lineHeight: 24},
  stepValue: {...typography.h4, color: colors.text, minWidth: 28, textAlign: 'center'},

  // Guests
  guestsRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.sm,
  },
  guestCenter: {alignItems: 'center'},
  guestCount: {fontSize: 28, fontWeight: '800', color: colors.primary},
  guestLabel: {...typography.caption, color: colors.textTertiary, marginTop: 2},

  // Textarea
  textarea: {
    borderWidth: 1.5,
    borderColor: colors.border,
    borderRadius: radius.lg,
    padding: 12,
    fontSize: 14,
    color: colors.text,
    minHeight: 80,
  },

  // Summary
  summaryBox: {
    backgroundColor: colors.background,
    borderRadius: radius.lg,
    padding: 14,
  },
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  summaryLabel: {...typography.body, color: colors.textSecondary},
  summaryValue: {...typography.body, color: colors.text, fontWeight: '500'},
  summaryDivider: {
    height: 1, backgroundColor: colors.border,
    marginVertical: 8,
  },
  totalLabel: {...typography.h4, color: colors.text},
  totalValue: {fontSize: 20, fontWeight: '800', color: colors.primary},

  confirmBtn: {marginTop: 4},
});
