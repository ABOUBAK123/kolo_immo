import React, {useState} from 'react';
import {
  Alert,
  Image,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  StatusBar,
  StyleSheet,
  Switch,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import {NativeStackScreenProps} from '@react-navigation/native-stack';
import {launchImageLibrary} from 'react-native-image-picker';
import {ownerPropertiesApi} from '../../api/ownerProperties';
import {ProfileStackParamList} from '../../types';
import {colors, radius, shadows, spacing, typography} from '../../utils/theme';

type Props = NativeStackScreenProps<ProfileStackParamList, 'OwnerAddProperty'>;

// ─── Static data ─────────────────────────────────────────────────────────────

const PROPERTY_TYPES = [
  {value: 'studio',    label: 'Studio'},
  {value: 'apartment', label: 'Appartement'},
  {value: 'villa',     label: 'Villa'},
  {value: 'room',      label: 'Chambre'},
  {value: 'duplex',    label: 'Duplex'},
  {value: 'house',     label: 'Maison'},
];

const AMENITIES = [
  {key: 'wifi',             label: 'WiFi'},
  {key: 'tv',               label: 'TV'},
  {key: 'air_conditioning', label: 'Climatisation'},
  {key: 'fan',              label: 'Ventilateur'},
  {key: 'equipped_kitchen', label: 'Cuisine équipée'},
  {key: 'refrigerator',     label: 'Réfrigérateur'},
  {key: 'hot_water',        label: 'Eau chaude'},
  {key: 'parking',          label: 'Parking'},
  {key: 'pool',             label: 'Piscine'},
  {key: 'balcony',          label: 'Balcon'},
  {key: 'security',         label: 'Gardiennage'},
  {key: 'generator',        label: 'Groupe électrogène'},
  {key: 'washer',           label: 'Machine à laver'},
  {key: 'workspace',        label: 'Espace de travail'},
  {key: 'garden',           label: 'Jardin'},
  {key: 'elevator',         label: 'Ascenseur'},
];

// ─── Helpers ──────────────────────────────────────────────────────────────────

function SectionHeader({title}: {title: string}) {
  return (
    <View style={styles.sectionHeader}>
      <Text style={styles.sectionTitle}>{title}</Text>
    </View>
  );
}

function Field({label, required, children}: {label: string; required?: boolean; children: React.ReactNode}) {
  return (
    <View style={styles.field}>
      <Text style={styles.fieldLabel}>
        {label}{required && <Text style={styles.required}> *</Text>}
      </Text>
      {children}
    </View>
  );
}

function Stepper({value, onChange, min = 0, max = 50}: {value: number; onChange: (v: number) => void; min?: number; max?: number}) {
  return (
    <View style={styles.stepper}>
      <TouchableOpacity
        style={[styles.stepBtn, value <= min && styles.stepBtnDisabled]}
        onPress={() => value > min && onChange(value - 1)}
        disabled={value <= min}>
        <Text style={styles.stepBtnText}>−</Text>
      </TouchableOpacity>
      <Text style={styles.stepValue}>{value}</Text>
      <TouchableOpacity
        style={[styles.stepBtn, value >= max && styles.stepBtnDisabled]}
        onPress={() => value < max && onChange(value + 1)}
        disabled={value >= max}>
        <Text style={styles.stepBtnText}>+</Text>
      </TouchableOpacity>
    </View>
  );
}

// ─── Screen ───────────────────────────────────────────────────────────────────

interface PhotoAsset {
  uri: string;
  type?: string;
  fileName?: string;
}

export const OwnerAddPropertyScreen: React.FC<Props> = ({navigation}) => {
  // Basic info
  const [type, setType]               = useState('apartment');
  const [title, setTitle]             = useState('');
  const [description, setDescription] = useState('');

  // Location
  const [country, setCountry]   = useState("Côte d'Ivoire");
  const [city, setCity]         = useState('');
  const [district, setDistrict] = useState('');
  const [address, setAddress]   = useState('');

  // Details
  const [capacity, setCapacity]   = useState(2);
  const [bedrooms, setBedrooms]   = useState(1);
  const [bathrooms, setBathrooms] = useState(1);

  // Pricing
  const [pricePerNight, setPricePerNight] = useState('');
  const [deposit, setDeposit]             = useState('');
  const [bookingType, setBookingType]     = useState<'instant' | 'request'>('instant');
  const [cancelPolicy, setCancelPolicy]   = useState<'flexible' | 'moderate' | 'strict'>('flexible');

  // Rules
  const [allowPets,    setAllowPets]    = useState(false);
  const [allowSmoking, setAllowSmoking] = useState(false);
  const [allowParties, setAllowParties] = useState(false);

  // Amenities
  const [amenities, setAmenities] = useState<string[]>([]);

  // Photos
  const [photos, setPhotos] = useState<PhotoAsset[]>([]);

  const [saving, setSaving] = useState(false);

  // ── Amenity toggle ────────────────────────────────────────────────────────
  const toggleAmenity = (key: string) =>
    setAmenities(prev =>
      prev.includes(key) ? prev.filter(k => k !== key) : [...prev, key],
    );

  // ── Pick photos ───────────────────────────────────────────────────────────
  const pickPhotos = () => {
    launchImageLibrary(
      {mediaType: 'photo', selectionLimit: 10, quality: 0.8},
      response => {
        if (response.didCancel || response.errorCode) return;
        const assets = response.assets ?? [];
        setPhotos(prev => {
          const existingUris = prev.map(p => p.uri);
          const newAssets = assets
            .filter(a => a.uri && !existingUris.includes(a.uri!))
            .map(a => ({uri: a.uri!, type: a.type ?? 'image/jpeg', fileName: a.fileName ?? 'photo.jpg'}));
          return [...prev, ...newAssets];
        });
      },
    );
  };

  const removePhoto = (index: number) =>
    setPhotos(prev => prev.filter((_, i) => i !== index));

  // ── Submit ────────────────────────────────────────────────────────────────
  const handleSubmit = async () => {
    if (!title.trim()) { Alert.alert('Champ requis', 'Veuillez entrer un titre.'); return; }
    if (description.trim().length < 50) { Alert.alert('Champ requis', 'La description doit faire au moins 50 caractères.'); return; }
    if (!city.trim()) { Alert.alert('Champ requis', 'Veuillez entrer la ville.'); return; }
    if (!pricePerNight || Number(pricePerNight) < 1000) { Alert.alert('Champ requis', 'Le prix par nuit doit être au moins 1 000 FCFA.'); return; }

    setSaving(true);
    try {
      const fd = new FormData();
      fd.append('type', type);
      fd.append('title', title.trim());
      fd.append('description', description.trim());
      fd.append('country', country.trim());
      fd.append('city', city.trim());
      if (district.trim()) fd.append('district', district.trim());
      if (address.trim()) fd.append('address', address.trim());
      fd.append('capacity', String(capacity));
      fd.append('bedrooms', String(bedrooms));
      fd.append('bathrooms', String(bathrooms));
      fd.append('price_per_night', pricePerNight);
      if (deposit) fd.append('deposit_amount', deposit);
      fd.append('booking_type', bookingType);
      fd.append('cancellation_policy', cancelPolicy);
      fd.append('allow_pets', allowPets ? '1' : '0');
      fd.append('allow_smoking', allowSmoking ? '1' : '0');
      fd.append('allow_parties', allowParties ? '1' : '0');
      amenities.forEach((a, i) => fd.append(`amenities[${i}]`, a));
      photos.forEach((p, i) =>
        fd.append(`photos[${i}]`, {uri: p.uri, type: p.type, name: p.fileName} as any),
      );

      await ownerPropertiesApi.store(fd);

      Alert.alert(
        'Logement créé !',
        'Votre bien a été enregistré comme brouillon. Activez-le depuis "Mes logements" pour le publier.',
        [{text: 'Voir mes logements', onPress: () => navigation.navigate('OwnerProperties')}],
      );
    } catch (err: any) {
      const msg = err.response?.data?.message ?? 'Erreur lors de l\'enregistrement.';
      const errors = err.response?.data?.errors;
      if (errors) {
        const first = Object.values(errors)[0] as string[];
        Alert.alert('Erreur de validation', first?.[0] ?? msg);
      } else {
        Alert.alert('Erreur', msg);
      }
    } finally {
      setSaving(false);
    }
  };

  // ─── Render ───────────────────────────────────────────────────────────────

  return (
    <View style={styles.screen}>
      <StatusBar barStyle="light-content" backgroundColor={colors.primary} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity style={styles.backBtn} onPress={() => navigation.goBack()}>
          <Text style={styles.backIcon}>‹</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Ajouter un bien</Text>
        <View style={{width: 40}} />
      </View>

      <KeyboardAvoidingView
        style={{flex: 1}}
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
        <ScrollView
          showsVerticalScrollIndicator={true}
          contentContainerStyle={styles.content}
          keyboardShouldPersistTaps="handled">

          {/* ── Type de bien ── */}
          <SectionHeader title="Type de bien" />
          <View style={styles.card}>
            <View style={styles.typeGrid}>
              {PROPERTY_TYPES.map(t => (
                <TouchableOpacity
                  key={t.value}
                  style={[styles.typeChip, type === t.value && styles.typeChipActive]}
                  onPress={() => setType(t.value)}
                  activeOpacity={0.7}>
                  <Text style={[styles.typeChipText, type === t.value && styles.typeChipTextActive]}>
                    {t.label}
                  </Text>
                </TouchableOpacity>
              ))}
            </View>
          </View>

          {/* ── Informations ── */}
          <SectionHeader title="Informations" />
          <View style={styles.card}>
            <Field label="Titre de l'annonce" required>
              <TextInput
                style={styles.input}
                value={title}
                onChangeText={setTitle}
                placeholder="Ex : Appartement moderne au Plateau"
                placeholderTextColor={colors.textTertiary}
                maxLength={150}
              />
            </Field>
            <Field label="Description" required>
              <TextInput
                style={[styles.input, styles.textarea]}
                value={description}
                onChangeText={setDescription}
                placeholder="Décrivez votre logement en détail (min. 50 caractères)..."
                placeholderTextColor={colors.textTertiary}
                multiline
                numberOfLines={5}
                maxLength={3000}
                textAlignVertical="top"
              />
              <Text style={styles.charCount}>{description.length}/3000</Text>
            </Field>
          </View>

          {/* ── Localisation ── */}
          <SectionHeader title="Localisation" />
          <View style={styles.card}>
            <Field label="Pays" required>
              <TextInput
                style={styles.input}
                value={country}
                onChangeText={setCountry}
                placeholderTextColor={colors.textTertiary}
              />
            </Field>
            <Field label="Ville" required>
              <TextInput
                style={styles.input}
                value={city}
                onChangeText={setCity}
                placeholder="Ex : Abidjan"
                placeholderTextColor={colors.textTertiary}
              />
            </Field>
            <Field label="Quartier / Commune">
              <TextInput
                style={styles.input}
                value={district}
                onChangeText={setDistrict}
                placeholder="Ex : Cocody, Yopougon..."
                placeholderTextColor={colors.textTertiary}
              />
            </Field>
            <Field label="Adresse complète">
              <TextInput
                style={styles.input}
                value={address}
                onChangeText={setAddress}
                placeholder="Ex : Rue des Jardins, Villa 12"
                placeholderTextColor={colors.textTertiary}
              />
            </Field>
          </View>

          {/* ── Caractéristiques ── */}
          <SectionHeader title="Caractéristiques" />
          <View style={styles.card}>
            <View style={styles.row}>
              <View style={styles.rowItem}>
                <Text style={styles.fieldLabel}>Capacité (pers.)</Text>
                <Stepper value={capacity} onChange={setCapacity} min={1} max={50} />
              </View>
              <View style={styles.rowItem}>
                <Text style={styles.fieldLabel}>Chambres</Text>
                <Stepper value={bedrooms} onChange={setBedrooms} min={0} max={20} />
              </View>
              <View style={styles.rowItem}>
                <Text style={styles.fieldLabel}>Salle de bain</Text>
                <Stepper value={bathrooms} onChange={setBathrooms} min={1} max={10} />
              </View>
            </View>
          </View>

          {/* ── Tarification ── */}
          <SectionHeader title="Tarification" />
          <View style={styles.card}>
            <Field label="Prix par nuit (FCFA)" required>
              <TextInput
                style={styles.input}
                value={pricePerNight}
                onChangeText={setPricePerNight}
                placeholder="Ex : 25000"
                placeholderTextColor={colors.textTertiary}
                keyboardType="numeric"
              />
            </Field>
            <Field label="Caution (FCFA)">
              <TextInput
                style={styles.input}
                value={deposit}
                onChangeText={setDeposit}
                placeholder="Ex : 50000 (optionnel)"
                placeholderTextColor={colors.textTertiary}
                keyboardType="numeric"
              />
            </Field>

            <Text style={[styles.fieldLabel, {marginBottom: 8}]}>Type de réservation</Text>
            <View style={styles.optionRow}>
              {([['instant', 'Instantanée'], ['request', 'Sur demande']] as const).map(([val, lbl]) => (
                <TouchableOpacity
                  key={val}
                  style={[styles.optionChip, bookingType === val && styles.optionChipActive]}
                  onPress={() => setBookingType(val)}
                  activeOpacity={0.7}>
                  <Text style={[styles.optionChipText, bookingType === val && styles.optionChipTextActive]}>{lbl}</Text>
                </TouchableOpacity>
              ))}
            </View>

            <Text style={[styles.fieldLabel, {marginBottom: 8, marginTop: 14}]}>Politique d'annulation</Text>
            <View style={styles.optionRow}>
              {([['flexible', 'Flexible'], ['moderate', 'Modérée'], ['strict', 'Stricte']] as const).map(([val, lbl]) => (
                <TouchableOpacity
                  key={val}
                  style={[styles.optionChip, cancelPolicy === val && styles.optionChipActive]}
                  onPress={() => setCancelPolicy(val)}
                  activeOpacity={0.7}>
                  <Text style={[styles.optionChipText, cancelPolicy === val && styles.optionChipTextActive]}>{lbl}</Text>
                </TouchableOpacity>
              ))}
            </View>
          </View>

          {/* ── Règles ── */}
          <SectionHeader title="Règles de la maison" />
          <View style={styles.card}>
            {([
              ['Animaux acceptés',  allowPets,    setAllowPets],
              ['Fumée autorisée',   allowSmoking, setAllowSmoking],
              ['Fêtes autorisées',  allowParties, setAllowParties],
            ] as [string, boolean, (v: boolean) => void][]).map(([label, val, setter]) => (
              <View key={label} style={styles.ruleRow}>
                <Text style={styles.ruleLabel}>{label}</Text>
                <Switch
                  value={val}
                  onValueChange={setter}
                  trackColor={{false: colors.gray200, true: colors.primaryLight}}
                  thumbColor={val ? colors.primary : colors.gray400}
                />
              </View>
            ))}
          </View>

          {/* ── Équipements ── */}
          <SectionHeader title="Équipements" />
          <View style={styles.card}>
            <View style={styles.amenityGrid}>
              {AMENITIES.map(a => {
                const selected = amenities.includes(a.key);
                return (
                  <TouchableOpacity
                    key={a.key}
                    style={[styles.amenityChip, selected && styles.amenityChipActive]}
                    onPress={() => toggleAmenity(a.key)}
                    activeOpacity={0.7}>
                    <Text style={[styles.amenityText, selected && styles.amenityTextActive]}>
                      {a.label}
                    </Text>
                  </TouchableOpacity>
                );
              })}
            </View>
          </View>

          {/* ── Photos ── */}
          <SectionHeader title="Photos" />
          <View style={styles.card}>
            <TouchableOpacity style={styles.photoPicker} onPress={pickPhotos} activeOpacity={0.7}>
              <Text style={styles.photoPickerIcon}>📷</Text>
              <Text style={styles.photoPickerText}>
                {photos.length === 0 ? 'Ajouter des photos' : `Ajouter d'autres photos (${photos.length} sélectionnée${photos.length > 1 ? 's' : ''})`}
              </Text>
              <Text style={styles.photoPickerSub}>JPG, PNG — max 5 Mo par photo</Text>
            </TouchableOpacity>

            {photos.length > 0 && (
              <View style={styles.photoGrid}>
                {photos.map((p, i) => (
                  <View key={i} style={styles.photoThumb}>
                    <Image source={{uri: p.uri}} style={styles.photoImg} />
                    {i === 0 && (
                      <View style={styles.photoCoverBadge}>
                        <Text style={styles.photoCoverText}>Principale</Text>
                      </View>
                    )}
                    <TouchableOpacity
                      style={styles.photoRemove}
                      onPress={() => removePhoto(i)}
                      hitSlop={{top: 6, bottom: 6, left: 6, right: 6}}>
                      <Text style={styles.photoRemoveText}>×</Text>
                    </TouchableOpacity>
                  </View>
                ))}
              </View>
            )}
          </View>

          {/* ── Submit ── */}
          <TouchableOpacity
            style={[styles.submitBtn, saving && styles.submitBtnDisabled]}
            onPress={handleSubmit}
            disabled={saving}
            activeOpacity={0.85}>
            <Text style={styles.submitText}>
              {saving ? 'Enregistrement...' : '✓  Créer le logement'}
            </Text>
          </TouchableOpacity>

          <View style={{height: 40}} />
        </ScrollView>
      </KeyboardAvoidingView>
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

  sectionHeader: {marginTop: spacing.md, marginBottom: 6},
  sectionTitle: {...typography.label, color: colors.primary, textTransform: 'uppercase', letterSpacing: 0.6},

  card: {
    backgroundColor: colors.surface,
    borderRadius: radius.xl,
    padding: spacing.md,
    marginBottom: 4,
    ...shadows.xs,
  },

  // Type chips
  typeGrid: {flexDirection: 'row', flexWrap: 'wrap', gap: 8},
  typeChip: {
    paddingHorizontal: 16, paddingVertical: 9,
    borderRadius: radius.full,
    borderWidth: 1.5, borderColor: colors.border,
    backgroundColor: colors.surface,
  },
  typeChipActive: {backgroundColor: colors.primary, borderColor: colors.primary},
  typeChipText: {...typography.label, color: colors.textMed},
  typeChipTextActive: {color: '#fff'},

  // Fields
  field: {marginBottom: 14},
  fieldLabel: {...typography.label, color: colors.textMed, marginBottom: 6},
  required: {color: colors.danger},
  input: {
    borderWidth: 1.5, borderColor: colors.border,
    borderRadius: radius.lg,
    paddingHorizontal: 14, paddingVertical: 11,
    fontSize: 14, color: colors.text,
    backgroundColor: colors.surface,
  },
  textarea: {height: 110, paddingTop: 12},
  charCount: {...typography.caption, color: colors.textTertiary, textAlign: 'right', marginTop: 4},

  // Stepper row
  row: {flexDirection: 'row', gap: 8},
  rowItem: {flex: 1, alignItems: 'center'},
  stepper: {flexDirection: 'row', alignItems: 'center', gap: 14, marginTop: 6},
  stepBtn: {
    width: 36, height: 36,
    borderRadius: radius.full,
    backgroundColor: colors.primaryFaint,
    alignItems: 'center', justifyContent: 'center',
  },
  stepBtnDisabled: {opacity: 0.35},
  stepBtnText: {fontSize: 20, color: colors.primary, fontWeight: '700', lineHeight: 22},
  stepValue: {...typography.h4, color: colors.text, minWidth: 20, textAlign: 'center'},

  // Option chips (booking type / cancel policy)
  optionRow: {flexDirection: 'row', flexWrap: 'wrap', gap: 8},
  optionChip: {
    paddingHorizontal: 14, paddingVertical: 8,
    borderRadius: radius.full,
    borderWidth: 1.5, borderColor: colors.border,
  },
  optionChipActive: {backgroundColor: colors.primary, borderColor: colors.primary},
  optionChipText: {...typography.label, color: colors.textMed},
  optionChipTextActive: {color: '#fff'},

  // Rules
  ruleRow: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    paddingVertical: 10,
    borderBottomWidth: 1, borderBottomColor: colors.borderLight,
  },
  ruleLabel: {...typography.body, color: colors.textMed},

  // Amenity grid
  amenityGrid: {flexDirection: 'row', flexWrap: 'wrap', gap: 8},
  amenityChip: {
    paddingHorizontal: 12, paddingVertical: 7,
    borderRadius: radius.full,
    borderWidth: 1.5, borderColor: colors.border,
    backgroundColor: colors.surface,
  },
  amenityChipActive: {backgroundColor: colors.primaryFaint, borderColor: colors.primary},
  amenityText: {...typography.label, color: colors.textMed},
  amenityTextActive: {color: colors.primary},

  // Photos
  photoPicker: {
    borderWidth: 2, borderStyle: 'dashed', borderColor: colors.border,
    borderRadius: radius.xl,
    padding: spacing.lg,
    alignItems: 'center',
    gap: 4,
  },
  photoPickerIcon: {fontSize: 32, marginBottom: 4},
  photoPickerText: {...typography.h4, color: colors.primary},
  photoPickerSub: {...typography.caption, color: colors.textTertiary, marginTop: 2},

  photoGrid: {
    flexDirection: 'row', flexWrap: 'wrap',
    gap: 8, marginTop: 14,
  },
  photoThumb: {width: '30%', aspectRatio: 1, borderRadius: radius.lg, overflow: 'hidden', position: 'relative'},
  photoImg: {width: '100%', height: '100%', resizeMode: 'cover'},
  photoCoverBadge: {
    position: 'absolute', bottom: 4, left: 4,
    backgroundColor: colors.primary,
    paddingHorizontal: 6, paddingVertical: 2,
    borderRadius: radius.sm,
  },
  photoCoverText: {color: '#fff', fontSize: 9, fontWeight: '700'},
  photoRemove: {
    position: 'absolute', top: 4, right: 4,
    width: 22, height: 22,
    backgroundColor: 'rgba(0,0,0,0.55)',
    borderRadius: 11,
    alignItems: 'center', justifyContent: 'center',
  },
  photoRemoveText: {color: '#fff', fontSize: 15, fontWeight: '700', lineHeight: 17},

  // Submit
  submitBtn: {
    marginTop: spacing.lg,
    backgroundColor: colors.accent,
    borderRadius: radius.xl,
    paddingVertical: 17,
    alignItems: 'center',
    ...shadows.md,
  },
  submitBtnDisabled: {opacity: 0.6},
  submitText: {...typography.button, color: '#fff', fontSize: 16},
});
