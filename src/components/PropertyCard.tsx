import React from 'react';
import {
  Image,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import {Property} from '../types';
import {colors, radius, shadows, spacing, typography} from '../utils/theme';
import {formatCFA, getImageUrl} from '../utils/helpers';

interface PropertyCardProps {
  property: Property;
  onPress: () => void;
  horizontal?: boolean;
}

export const PropertyCard: React.FC<PropertyCardProps> = ({
  property,
  onPress,
  horizontal = false,
}) => {
  const coverUrl =
    property.cover_photo
      ? getImageUrl(property.cover_photo)
      : property.photos?.find(p => p.is_cover)?.path
      ? getImageUrl(property.photos.find(p => p.is_cover)!.path)
      : null;

  if (horizontal) {
    return (
      <TouchableOpacity style={[styles.hCard, shadows.sm]} onPress={onPress} activeOpacity={0.85}>
        {coverUrl ? (
          <Image source={{uri: coverUrl}} style={styles.hImage} />
        ) : (
          <View style={[styles.hImage, styles.placeholder]} />
        )}
        <View style={styles.hContent}>
          <Text style={styles.city}>{property.city}</Text>
          <Text style={styles.title} numberOfLines={2}>{property.title}</Text>
          <View style={styles.row}>
            <Text style={styles.price}>{formatCFA(property.price_per_night)}</Text>
            <Text style={styles.night}>/nuit</Text>
          </View>
          {property.rating_avg > 0 && (
            <View style={styles.row}>
              <Text style={styles.star}>★</Text>
              <Text style={styles.rating}>{property.rating_avg.toFixed(1)}</Text>
              <Text style={styles.reviewCount}>({property.reviews_count})</Text>
            </View>
          )}
        </View>
      </TouchableOpacity>
    );
  }

  return (
    <TouchableOpacity style={[styles.card, shadows.sm]} onPress={onPress} activeOpacity={0.85}>
      {coverUrl ? (
        <Image source={{uri: coverUrl}} style={styles.image} />
      ) : (
        <View style={[styles.image, styles.placeholder]} />
      )}
      <View style={styles.badge}>
        <Text style={styles.badgeText}>{property.type}</Text>
      </View>
      <View style={styles.content}>
        <Text style={styles.city}>{property.city}</Text>
        <Text style={styles.title} numberOfLines={2}>{property.title}</Text>
        <View style={[styles.row, {justifyContent: 'space-between', marginTop: 8}]}>
          <View style={styles.row}>
            <Text style={styles.price}>{formatCFA(property.price_per_night)}</Text>
            <Text style={styles.night}>/nuit</Text>
          </View>
          {property.rating_avg > 0 && (
            <View style={styles.row}>
              <Text style={styles.star}>★</Text>
              <Text style={styles.rating}>{property.rating_avg.toFixed(1)}</Text>
            </View>
          )}
        </View>
        <View style={[styles.row, {marginTop: 6, gap: 8}]}>
          <Text style={styles.meta}>{property.bedrooms} ch.</Text>
          <Text style={styles.metaDot}>·</Text>
          <Text style={styles.meta}>{property.capacity} pers.</Text>
          <Text style={styles.metaDot}>·</Text>
          <Text style={styles.meta}>{property.bathrooms} sdb.</Text>
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
  image: {width: '100%', height: 200, backgroundColor: colors.gray200},
  placeholder: {backgroundColor: colors.gray200},
  badge: {
    position: 'absolute',
    top: 12,
    left: 12,
    backgroundColor: 'rgba(0,0,0,0.55)',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: radius.full,
  },
  badgeText: {color: '#fff', fontSize: 11, fontWeight: '600'},
  content: {padding: spacing.md},
  city: {...typography.caption, color: colors.textSecondary, textTransform: 'uppercase', letterSpacing: 0.5},
  title: {...typography.h3, color: colors.text, marginTop: 2},
  row: {flexDirection: 'row', alignItems: 'center', gap: 4},
  price: {fontSize: 16, fontWeight: '700', color: colors.primary},
  night: {...typography.caption, color: colors.textSecondary},
  star: {color: '#F59E0B', fontSize: 13},
  rating: {fontSize: 13, fontWeight: '600', color: colors.text},
  reviewCount: {...typography.caption, color: colors.textSecondary},
  meta: {...typography.caption, color: colors.textSecondary},
  metaDot: {...typography.caption, color: colors.textLight},

  // Horizontal card
  hCard: {
    backgroundColor: colors.surface,
    borderRadius: radius.lg,
    flexDirection: 'row',
    overflow: 'hidden',
    marginBottom: spacing.sm,
  },
  hImage: {width: 110, height: 100, backgroundColor: colors.gray200},
  hContent: {flex: 1, padding: 12, justifyContent: 'center'},
});
