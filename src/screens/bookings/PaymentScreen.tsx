import React, {useEffect, useState} from 'react';
import {
  Alert,
  Image,
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
import {paymentsApi} from '../../api/payments';
import {apiClient} from '../../api/client';
import {Button} from '../../components/Button';
import {LoadingSpinner} from '../../components/LoadingSpinner';
import {Booking, BookingsStackParamList} from '../../types';
import {colors, radius, spacing, typography} from '../../utils/theme';
import {formatCFA} from '../../utils/helpers';

type Props = {
  navigation: NativeStackNavigationProp<BookingsStackParamList, 'Payment'>;
  route: RouteProp<BookingsStackParamList, 'Payment'>;
};

const METHODS = [
  {value: 'orange_money', label: 'Orange Money', emoji: '🟠', color: '#FF6B00'},
  {value: 'wave',         label: 'Wave',         emoji: '🔵', color: '#1A7EF6'},
  {value: 'mtn_momo',     label: 'MTN MoMo',     emoji: '🟡', color: '#FFCC00'},
  {value: 'moov_money',   label: 'Moov Money',   emoji: '🔵', color: '#0082CA'},
];

export const PaymentScreen: React.FC<Props> = ({navigation, route}) => {
  const {bookingId} = route.params;
  const [booking, setBooking] = useState<Booking | null>(null);
  const [method, setMethod] = useState('orange_money');
  const [phone, setPhone] = useState('');
  const [loading, setLoading] = useState(true);
  const [paying, setPaying] = useState(false);
  const [logos, setLogos] = useState<Record<string, string>>({});

  useEffect(() => {
    Promise.all([
      bookingsApi.show(bookingId).then(res => setBooking(res.data.data)),
      apiClient.get<{data: Record<string, string>}>('/payment-logos')
        .then(res => setLogos(res.data.data ?? {}))
        .catch(() => {}),
    ]).finally(() => setLoading(false));
  }, [bookingId]);

  const handlePay = async () => {
    if (!phone.trim() || phone.length < 8) {
      Alert.alert('Erreur', 'Entrez un numéro de téléphone valide.');
      return;
    }
    setPaying(true);
    try {
      await paymentsApi.initiate(bookingId, {phone_number: phone, method});
      Alert.alert(
        'Paiement initié ✅',
        `Vérifiez votre téléphone ${phone} pour valider le paiement via ${METHODS.find(m => m.value === method)?.label}.`,
        [{text: 'OK', onPress: () => navigation.goBack()}],
      );
    } catch (err: any) {
      Alert.alert('Erreur de paiement', err.response?.data?.message ?? 'Paiement échoué. Réessayez.');
    } finally {
      setPaying(false);
    }
  };

  if (loading) return <LoadingSpinner fullScreen message="Chargement..." />;
  if (!booking) return null;

  return (
    <ScrollView style={styles.screen} showsVerticalScrollIndicator={false}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.back}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <Text style={styles.title}>Paiement</Text>
      </View>

      {/* Amount card */}
      <View style={styles.amountCard}>
        <Text style={styles.amountLabel}>Montant à payer</Text>
        <Text style={styles.amountValue}>{formatCFA(booking.total_amount)}</Text>
        <Text style={styles.amountRef}>Réservation #{booking.reference}</Text>
      </View>

      {/* Method selection */}
      <View style={styles.card}>
        <Text style={styles.cardTitle}>Mode de paiement</Text>
        <View style={styles.methodsGrid}>
          {METHODS.map(m => (
            <TouchableOpacity
              key={m.value}
              style={[
                styles.methodBtn,
                method === m.value && {borderColor: m.color, backgroundColor: m.color + '10'},
              ]}
              onPress={() => setMethod(m.value)}>
              {logos[m.value] ? (
                <Image source={{uri: logos[m.value]}} style={styles.methodLogo} resizeMode="contain" />
              ) : (
                <Text style={styles.methodEmoji}>{m.emoji}</Text>
              )}
              <Text style={[styles.methodLabel, method === m.value && {color: m.color}]}>
                {m.label}
              </Text>
              {method === m.value && (
                <View style={[styles.methodCheck, {backgroundColor: m.color}]}>
                  <Text style={styles.methodCheckText}>✓</Text>
                </View>
              )}
            </TouchableOpacity>
          ))}
        </View>
      </View>

      {/* Phone input */}
      <View style={styles.card}>
        <Text style={styles.cardTitle}>Numéro de téléphone</Text>
        <Text style={styles.phoneHint}>
          Entrez le numéro associé à votre compte {METHODS.find(m => m.value === method)?.label}
        </Text>
        <TextInput
          style={styles.phoneInput}
          placeholder="+225 07 00 00 00 00"
          placeholderTextColor={colors.textTertiary}
          value={phone}
          onChangeText={setPhone}
          keyboardType="phone-pad"
          maxLength={16}
        />
      </View>

      {/* Security note */}
      <View style={styles.securityNote}>
        <Text style={styles.securityText}>
          🔒 Paiement sécurisé via CinetPay. Vos données sont protégées.
        </Text>
      </View>

      <View style={styles.footer}>
        <Button
          title={`Payer ${formatCFA(booking.total_amount)}`}
          onPress={handlePay}
          loading={paying}
          fullWidth
          size="lg"
        />
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
  amountCard: {
    margin: spacing.lg,
    backgroundColor: colors.primary,
    borderRadius: radius.xl,
    padding: 24,
    alignItems: 'center',
  },
  amountLabel: {color: 'rgba(255,255,255,0.8)', fontSize: 14, marginBottom: 8},
  amountValue: {color: '#fff', fontSize: 36, fontWeight: '800'},
  amountRef: {color: 'rgba(255,255,255,0.6)', fontSize: 12, marginTop: 8, fontFamily: 'monospace'},
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
  cardTitle: {...typography.h3, color: colors.text, marginBottom: 12},
  methodsGrid: {flexDirection: 'row', flexWrap: 'wrap', gap: 10},
  methodBtn: {
    width: '47%',
    borderWidth: 2,
    borderColor: colors.border,
    borderRadius: radius.lg,
    padding: 14,
    alignItems: 'center',
    gap: 6,
    position: 'relative',
  },
  methodLogo: {width: 64, height: 32},
  methodEmoji: {fontSize: 28},
  methodLabel: {fontSize: 13, fontWeight: '600', color: colors.textSecondary},
  methodCheck: {
    position: 'absolute',
    top: 8,
    right: 8,
    width: 20,
    height: 20,
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
  },
  methodCheckText: {color: '#fff', fontSize: 11, fontWeight: '700'},
  phoneHint: {...typography.caption, color: colors.textSecondary, marginBottom: 10},
  phoneInput: {
    borderWidth: 1.5,
    borderColor: colors.border,
    borderRadius: radius.md,
    paddingHorizontal: 14,
    paddingVertical: 13,
    fontSize: 16,
    color: colors.text,
    letterSpacing: 1,
  },
  securityNote: {
    marginHorizontal: spacing.lg,
    marginBottom: 12,
    backgroundColor: '#10B98110',
    borderRadius: radius.md,
    padding: 12,
  },
  securityText: {fontSize: 12, color: '#10B981', fontWeight: '500', textAlign: 'center'},
  footer: {padding: spacing.lg},
});
