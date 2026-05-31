import React, {useState} from 'react';
import {Image, StyleSheet, Text, TouchableOpacity, View} from 'react-native';
import {Property} from '../types';
import {colors, radius, shadows, spacing, typography} from '../utils/theme';
import {formatCFA, getImageUrl} from '../utils/helpers';
import {favoritesApi} from '../api/favorites';
import {useAuth} from '../store/AuthContext';

interface PropertyCardProps {
  property: Property;
  onPress: () => void;
  horizontal?: boolean;
  initialFaved?: boolean;
}

export const PropertyCard: React.FC<PropertyCardProps> = ({
  property,
  onPress,
  horizontal = false,
  initialFaved = false,
}) => {
  const {user} = useAuth();
  const [faved, setFaved] = useState(initialFaved);
  const [toggling, setToggling] = useState(false);

  const coverUrl =
    property.cover_photo_url
      ? getImageUrl(property.cover_photo_url)
      : property.photos?.find(p => p.is_cover)?.path
      ? getImageUrl(property.photos.find(p => p.is_cover)!.path)
      : null;

  const rating = property.rating_avg > 0;

  const handleFav = async () => {
    if (!user || toggling) return;
    setToggling(true);
    try {
      const res = await favoritesApi.toggle(property.id);
      setFaved(res.data.faved);
    } catch { /* ignore */ }
    finally { setToggling(false); }
  };

  const HeartBtn = () => (
    <TouchableOpacity
      onPress={handleFav}
      hitSlop={{top: 8, bottom: 8, left: 8, right: 8}}
      style={[styles.heartBtn, faved && styles.heartBtnActive]}>
      <Text style={{fontSize: 13, color: faved ? '#fff' : '#ef4444'}}>{faved ? '♥' : '♡'}</Text>
    </TouchableOpacity>
  );

  if (horizontal) {
    return (
      <TouchableOpacity style={[styles.hCard, shadows.sm]} onPress={onPress} activeOpacity={0.85}>
        {coverUrl ? (
          <Image source={{uri: coverUrl}} style={styles.hImage} />
        ) : (
          <View style={[styles.hImage, styles.placeholder]}>
            <Text style={styles.placeholderEmoji}>🏠</Text>
          </View>
        )}
        <View style={styles.hContent}>
          <Text style={styles.hType}>{property.type_label ?? property.type}</Text>
          <Text style={styles.hTitle} numberOfLines={2}>{property.title}</Text>
          <Text style={styles.hCity}>📍 {property.city}</Text>
          <View style={styles.hFooter}>
            <Text style={styles.hPrice}>{formatCFA(property.price_per_night)}</Text>
            <Text style={styles.hNight}>/nuit</Text>
            {rating && (
              <View style={styles.hRating}>
                <Text style={styles.ratingStar}>★</Text>
                <Text style={styles.ratingVal}>{property.rating_avg.toFixed(1)}</Text>
              </View>
            )}
          </View>
        </View>
        {user && <HeartBtn />}
      </TouchableOpacity>
    );
  }

  return (
    <TouchableOpacity style={[styles.card, shadows.sm]} onPress={onPress} activeOpacity={0.88}>
      {/* Image */}
      <View style={styles.imageWrap}>
        {coverUrl ? (
          <Image source={{uri: coverUrl}} style={styles.image} />
        ) : (
          <View style={[styles.image, styles.placeholder]}>
            <Text style={styles.placeholderEmoji}>🏠</Text>
          </View>
        )}
        {/* Type badge */}
        <View style={styles.typeBadge}>
          <Text style={styles.typeBadgeText}>{property.type_label ?? property.type}</Text>
        </View>
        {/* Rating badge */}
        {rating && (
          <View style={styles.ratingBadge}>
            <Text style={styles.ratingStar}>★</Text>
            <Text style={styles.ratingBadgeText}>{property.rating_avg.toFixed(1)}</Text>
          </View>
        )}
        {/* Heart */}
        {user && (
          <View style={styles.heartWrap}>
            <HeartBtn />
          </View>
        )}
      </View>

      {/* Content */}
      <View style={styles.content}>
        <View style={styles.cityRow}>
          <Text style={styles.city}>📍 {property.city}</Text>
          {property.reviews_count > 0 && (
            <Text style={styles.reviews}>{property.reviews_count} avis</Text>
          )}
        </View>
        <Text style={styles.title} numberOfLines={2}>{property.title}</Text>

        {/* Stats row */}
        <View style={styles.statsRow}>
          <View style={styles.stat}>
            <Text style={styles.statEmoji}>🛏</Text>
            <Text style={styles.statText}>{property.bedrooms} ch.</Text>
          </View>
          <View style={styles.statDot} />
          <View style={styles.stat}>
            <Text style={styles.statEmoji}>🚿</Text>
            <Text style={styles.statText}>{property.bathrooms} sdb.</Text>
          </View>
          <View style={styles.statDot} />
          <View style={styles.stat}>
            <Text style={styles.statEmoji}>👤</Text>
            <Text style={styles.statText}>{property.capacity} pers.</Text>
          </View>
        </View>

        {/* Price */}
        <View style={styles.priceRow}>
          <Text style={styles.price}>{formatCFA(property.price_per_night)}</Text>
          <Text style={styles.night}> / nuit</Text>
        </View>
      </View>
    </TouchableOpacity>
  );
};

const styles = StyleSheet.create({
  card: {
    backgroundColor: colors.surface,
    borderRadius: radius.xl,
    overflow: 'hidden',
    marginBottom: spacing.md,
  },

  imageWrap: { position: 'relative' },
  image: { width: '100%', height: 195 },
  placeholder: {
    backgroundColor: colors.primaryFaint,
    alignItems: 'center',
    justifyContent: 'center',
  },
  placeholderEmoji: { fontSize: 48, opacity: 0.4 },

  typeBadge: {
    position: 'absolute',
    top: 12,
    left: 12,
    backgroundColor: 'rgba(0,0,0,0.58)',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: radius.full,
  },
  typeBadgeText: { ...typography.labelSm, color: '#fff' },

  ratingBadge: {
    position: 'absolute',
    top: 12,
    right: 52,
    backgroundColor: 'rgba(255,255,255,0.95)',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: radius.full,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 3,
    ...shadows.xs,
  },
  ratingStar: { color: '#F59E0B', fontSize: 12, fontWeight: '700' },
  ratingBadgeText: { ...typography.labelSm, color: colors.text, fontWeight: '700' },

  heartWrap: { position: 'absolute', top: 10, right: 10 },
  heartBtn: {
    width: 32,
    height: 32,
    borderRadius: radius.full,
    backgroundColor: 'rgba(255,255,255,0.92)',
    alignItems: 'center',
    justifyContent: 'center',
    ...shadows.xs,
  },
  heartBtnActive: { backgroundColor: '#ef4444' },

  content: { padding: spacing.md },
  cityRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 4 },
  city: { ...typography.caption, color: colors.textSecondary },
  reviews: { ...typography.caption, color: colors.textTertiary },
  title: { ...typography.h4, color: colors.text, marginBottom: 10, lineHeight: 22 },

  statsRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 12 },
  stat: { flexDirection: 'row', alignItems: 'center', gap: 4 },
  statEmoji: { fontSize: 12 },
  statText: { ...typography.caption, color: colors.textSecondary },
  statDot: { width: 3, height: 3, borderRadius: 2, backgroundColor: colors.gray300, marginHorizontal: 8 },

  priceRow: { flexDirection: 'row', alignItems: 'baseline', borderTopWidth: 1, borderTopColor: colors.borderLight, paddingTop: 10 },
  price: { fontSize: 18, fontWeight: '800', color: colors.primary },
  night: { ...typography.caption, color: colors.textSecondary },

  ratingVal: { ...typography.caption, color: colors.text, fontWeight: '600' },

  // Horizontal
  hCard: {
    backgroundColor: colors.surface,
    borderRadius: radius.xl,
    flexDirection: 'row',
    overflow: 'hidden',
    marginBottom: spacing.sm,
  },
  hImage: { width: 115, height: 105 },
  hContent: { flex: 1, padding: 12, justifyContent: 'space-between' },
  hType: { ...typography.labelSm, color: colors.textTertiary, textTransform: 'uppercase', marginBottom: 2 },
  hTitle: { ...typography.h4, color: colors.text, flex: 1, lineHeight: 20 },
  hCity: { ...typography.caption, color: colors.textSecondary, marginTop: 4 },
  hFooter: { flexDirection: 'row', alignItems: 'baseline', gap: 2, marginTop: 6 },
  hPrice: { fontSize: 15, fontWeight: '800', color: colors.primary },
  hNight: { ...typography.caption, color: colors.textSecondary, flex: 1 },
  hRating: { flexDirection: 'row', alignItems: 'center', gap: 2 },
});
