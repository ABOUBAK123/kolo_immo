import React, {useState} from 'react';
import {
  Alert,
  Dimensions,
  Image,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import {NativeStackNavigationProp} from '@react-navigation/native-stack';
import {RouteProp} from '@react-navigation/native';
import {useAuth} from '../../store/AuthContext';
import {Button} from '../../components/Button';
import {HomeStackParamList} from '../../types';
import {colors, radius, spacing, typography} from '../../utils/theme';
import {formatCFA, getImageUrl} from '../../utils/helpers';

type Props = {
  navigation: NativeStackNavigationProp<HomeStackParamList, 'PropertyDetail'>;
  route: RouteProp<HomeStackParamList, 'PropertyDetail'>;
};

const {width} = Dimensions.get('window');

export const PropertyDetailScreen: React.FC<Props> = ({navigation, route}) => {
  const {property} = route.params;
  const {user} = useAuth();
  const [activePhoto, setActivePhoto] = useState(0);

  const photos = property.photos ?? [];
  const coverUrl = property.cover_photo
    ? getImageUrl(property.cover_photo)
    : photos[0]?.path ? getImageUrl(photos[0].path) : null;

  const allPhotos = photos.length > 0
    ? photos.map(p => getImageUrl(p.path))
    : coverUrl ? [coverUrl] : [];

  const handleBook = () => {
    if (!user) {
      Alert.alert('Connexion requise', 'Connectez-vous pour réserver.');
      return;
    }
    navigation.navigate('BookingCreate', {property});
  };

  const amenityEmoji: Record<string, string> = {
    wifi: '📶', parking: '🚗', pool: '🏊', gym: '💪',
    ac: '❄️', kitchen: '🍳', tv: '📺', washer: '🫧',
    balcony: '🌅', garden: '🌿', security: '🔒',
  };

  return (
    <View style={styles.screen}>
      <ScrollView showsVerticalScrollIndicator={false}>
        {/* Photo gallery */}
        <View style={styles.gallery}>
          {allPhotos.length > 0 ? (
            <ScrollView
              horizontal
              pagingEnabled
              showsHorizontalScrollIndicator={false}
              onScroll={e => {
                const idx = Math.round(e.nativeEvent.contentOffset.x / width);
                setActivePhoto(idx);
              }}
              scrollEventThrottle={16}>
              {allPhotos.map((url, i) => (
                <Image key={i} source={{uri: url}} style={styles.photo} />
              ))}
            </ScrollView>
          ) : (
            <View style={[styles.photo, styles.placeholder]}>
              <Text style={styles.placeholderText}>🏠</Text>
            </View>
          )}

          {/* Back button */}
          <TouchableOpacity
            style={styles.backBtn}
            onPress={() => navigation.goBack()}>
            <Text style={styles.backArrow}>←</Text>
          </TouchableOpacity>

          {/* Photo counter */}
          {allPhotos.length > 1 && (
            <View style={styles.photoCounter}>
              <Text style={styles.photoCounterText}>
                {activePhoto + 1}/{allPhotos.length}
              </Text>
            </View>
          )}
        </View>

        <View style={styles.content}>
          {/* Header info */}
          <View style={styles.row}>
            <View style={styles.typeBadge}>
              <Text style={styles.typeBadgeText}>{property.type}</Text>
            </View>
            {property.rating_avg > 0 && (
              <View style={styles.row}>
                <Text style={styles.star}>★</Text>
                <Text style={styles.rating}>{property.rating_avg.toFixed(1)}</Text>
                <Text style={styles.reviews}>({property.reviews_count} avis)</Text>
              </View>
            )}
          </View>

          <Text style={styles.title}>{property.title}</Text>
          <Text style={styles.location}>📍 {property.city}, {property.country}</Text>

          {/* Stats row */}
          <View style={styles.statsRow}>
            {[
              {icon: '🛏', label: `${property.bedrooms} ch.`},
              {icon: '🚿', label: `${property.bathrooms} sdb.`},
              {icon: '👥', label: `${property.capacity} pers.`},
              ...(property.area ? [{icon: '📐', label: `${property.area}m²`}] : []),
            ].map((s, i) => (
              <View key={i} style={styles.statItem}>
                <Text style={styles.statIcon}>{s.icon}</Text>
                <Text style={styles.statLabel}>{s.label}</Text>
              </View>
            ))}
          </View>

          {/* Description */}
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Description</Text>
            <Text style={styles.description}>{property.description}</Text>
          </View>

          {/* Amenities */}
          {property.amenities && property.amenities.length > 0 && (
            <View style={styles.section}>
              <Text style={styles.sectionTitle}>Équipements</Text>
              <View style={styles.amenitiesGrid}>
                {property.amenities.map(a => (
                  <View key={a.id} style={styles.amenityItem}>
                    <Text style={styles.amenityIcon}>
                      {amenityEmoji[a.amenity] ?? '✓'}
                    </Text>
                    <Text style={styles.amenityLabel}>{a.amenity}</Text>
                  </View>
                ))}
              </View>
            </View>
          )}

          {/* Rules */}
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Règles</Text>
            <View style={styles.rulesRow}>
              <View style={styles.ruleItem}>
                <Text>{property.allow_pets ? '✅' : '❌'}</Text>
                <Text style={styles.ruleText}>Animaux</Text>
              </View>
              <View style={styles.ruleItem}>
                <Text>{property.allow_smoking ? '✅' : '❌'}</Text>
                <Text style={styles.ruleText}>Tabac</Text>
              </View>
              <View style={styles.ruleItem}>
                <Text>{property.allow_parties ? '✅' : '❌'}</Text>
                <Text style={styles.ruleText}>Fêtes</Text>
              </View>
            </View>
          </View>

          {/* Host */}
          {property.owner && (
            <View style={styles.section}>
              <Text style={styles.sectionTitle}>Hôte</Text>
              <View style={styles.hostRow}>
                <View style={styles.hostAvatar}>
                  <Text style={styles.hostAvatarText}>
                    {property.owner.name.charAt(0).toUpperCase()}
                  </Text>
                </View>
                <View>
                  <Text style={styles.hostName}>{property.owner.name}</Text>
                  <Text style={styles.hostCity}>{property.owner.city ?? ''}</Text>
                </View>
              </View>
            </View>
          )}
        </View>
      </ScrollView>

      {/* Bottom booking bar */}
      <View style={styles.bottomBar}>
        <View>
          <Text style={styles.price}>{formatCFA(property.price_per_night)}</Text>
          <Text style={styles.perNight}>par nuit</Text>
        </View>
        <Button title="Réserver" onPress={handleBook} style={styles.bookBtn} />
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  screen: {flex: 1, backgroundColor: colors.background},
  gallery: {height: 300, backgroundColor: colors.gray200},
  photo: {width, height: 300, resizeMode: 'cover'},
  placeholder: {alignItems: 'center', justifyContent: 'center'},
  placeholderText: {fontSize: 64},
  backBtn: {
    position: 'absolute',
    top: 48,
    left: 16,
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: 'rgba(255,255,255,0.9)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  backArrow: {fontSize: 20, color: colors.text},
  photoCounter: {
    position: 'absolute',
    bottom: 12,
    right: 12,
    backgroundColor: 'rgba(0,0,0,0.5)',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 999,
  },
  photoCounterText: {color: '#fff', fontSize: 12, fontWeight: '600'},
  content: {padding: spacing.lg},
  row: {flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 8},
  typeBadge: {
    backgroundColor: colors.primary + '15',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: radius.full,
  },
  typeBadgeText: {fontSize: 12, fontWeight: '600', color: colors.primary},
  star: {color: '#F59E0B', fontSize: 14},
  rating: {fontSize: 14, fontWeight: '700', color: colors.text},
  reviews: {...typography.caption, color: colors.textSecondary},
  title: {...typography.h2, color: colors.text, marginBottom: 6},
  location: {...typography.body, color: colors.textSecondary, marginBottom: 16},
  statsRow: {
    flexDirection: 'row',
    backgroundColor: colors.gray50,
    borderRadius: radius.lg,
    padding: 12,
    marginBottom: 20,
    justifyContent: 'space-around',
  },
  statItem: {alignItems: 'center', gap: 4},
  statIcon: {fontSize: 20},
  statLabel: {fontSize: 12, fontWeight: '600', color: colors.text},
  section: {marginBottom: 20},
  sectionTitle: {...typography.h3, color: colors.text, marginBottom: 10},
  description: {...typography.bodyMd, color: colors.textSecondary, lineHeight: 24},
  amenitiesGrid: {flexDirection: 'row', flexWrap: 'wrap', gap: 8},
  amenityItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: colors.gray50,
    paddingHorizontal: 12,
    paddingVertical: 7,
    borderRadius: radius.md,
    borderWidth: 1,
    borderColor: colors.border,
  },
  amenityIcon: {fontSize: 14},
  amenityLabel: {fontSize: 12, fontWeight: '500', color: colors.text},
  rulesRow: {flexDirection: 'row', gap: 16},
  ruleItem: {alignItems: 'center', gap: 4},
  ruleText: {...typography.caption, color: colors.textSecondary},
  hostRow: {flexDirection: 'row', alignItems: 'center', gap: 12},
  hostAvatar: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: colors.primary,
    alignItems: 'center',
    justifyContent: 'center',
  },
  hostAvatarText: {color: '#fff', fontSize: 20, fontWeight: '700'},
  hostName: {fontSize: 15, fontWeight: '600', color: colors.text},
  hostCity: {...typography.caption, color: colors.textSecondary},
  bottomBar: {
    backgroundColor: colors.surface,
    padding: spacing.lg,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderTopWidth: 1,
    borderTopColor: colors.border,
    elevation: 8,
    shadowColor: '#000',
    shadowOffset: {width: 0, height: -2},
    shadowOpacity: 0.08,
    shadowRadius: 8,
  },
  price: {fontSize: 22, fontWeight: '800', color: colors.primary},
  perNight: {...typography.caption, color: colors.textSecondary},
  bookBtn: {minWidth: 140},
});
