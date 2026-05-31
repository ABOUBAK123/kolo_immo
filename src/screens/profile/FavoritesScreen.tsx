import React, {useCallback, useState} from 'react';
import {
  FlatList,
  Image,
  RefreshControl,
  StatusBar,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import {useFocusEffect} from '@react-navigation/native';
import {NativeStackNavigationProp} from '@react-navigation/native-stack';
import {favoritesApi, FavoriteProperty} from '../../api/favorites';
import {favoritesApi as fav} from '../../api/favorites';
import {LoadingSpinner} from '../../components/LoadingSpinner';
import {ProfileStackParamList} from '../../types';
import {colors, radius, shadows, spacing, typography} from '../../utils/theme';
import {formatCFA, getImageUrl} from '../../utils/helpers';

type Props = {
  navigation: NativeStackNavigationProp<ProfileStackParamList, 'Favorites'>;
};

export const FavoritesScreen: React.FC<Props> = ({navigation}) => {
  const [items, setItems]     = useState<FavoriteProperty[]>([]);
  const [loading, setLoading] = useState(true);

  const load = async () => {
    setLoading(true);
    try {
      const res = await favoritesApi.list();
      setItems(res.data.data);
    } catch { /* ignore */ }
    finally { setLoading(false); }
  };

  useFocusEffect(useCallback(() => { load(); }, []));

  const handleRemove = async (id: number) => {
    await fav.toggle(id);
    setItems(prev => prev.filter(p => p.id !== id));
  };

  if (loading) return <LoadingSpinner />;

  return (
    <View style={styles.screen}>
      <StatusBar barStyle="light-content" backgroundColor={colors.primary} />
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Mes favoris</Text>
        <View style={{width: 36}} />
      </View>

      {items.length === 0 ? (
        <View style={styles.empty}>
          <Text style={styles.emptyEmoji}>♡</Text>
          <Text style={styles.emptyTitle}>Aucun favori</Text>
          <Text style={styles.emptySub}>Appuyez sur ♡ sur un bien pour le retrouver ici.</Text>
        </View>
      ) : (
        <FlatList
          data={items}
          keyExtractor={i => String(i.id)}
          contentContainerStyle={styles.list}
          refreshControl={<RefreshControl refreshing={loading} onRefresh={load} tintColor={colors.primary} />}
          renderItem={({item}) => (
            <View style={[styles.card, shadows.sm]}>
              {item.price_dropped && (
                <View style={styles.dropBanner}>
                  <Text style={styles.dropText}>🎉 Prix baissé !</Text>
                </View>
              )}
              {item.cover_photo_url ? (
                <Image source={{uri: getImageUrl(item.cover_photo_url!)}} style={styles.img} />
              ) : (
                <View style={[styles.img, styles.imgPlaceholder]}>
                  <Text style={{fontSize: 32}}>🏠</Text>
                </View>
              )}
              <View style={styles.cardBody}>
                <Text style={styles.cardTitle} numberOfLines={2}>{item.title}</Text>
                <Text style={styles.cardCity}>📍 {item.city}</Text>
                <View style={styles.cardFooter}>
                  <View>
                    <Text style={styles.cardPrice}>{formatCFA(item.price_per_night)}<Text style={styles.cardNight}> /nuit</Text></Text>
                    {item.price_dropped && item.price_at_save && (
                      <Text style={styles.oldPrice}>{formatCFA(item.price_at_save)}</Text>
                    )}
                  </View>
                  <TouchableOpacity onPress={() => handleRemove(item.id)} style={styles.removeBtn}>
                    <Text style={{fontSize: 16, color: '#ef4444'}}>♥</Text>
                  </TouchableOpacity>
                </View>
              </View>
            </View>
          )}
        />
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.background },

  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: colors.primary,
    paddingHorizontal: spacing.lg,
    paddingTop: 48,
    paddingBottom: spacing.md,
  },
  backBtn: {
    width: 36, height: 36, borderRadius: radius.full,
    backgroundColor: 'rgba(255,255,255,0.18)',
    alignItems: 'center', justifyContent: 'center',
  },
  backArrow: { color: '#fff', fontSize: 20, fontWeight: '700' },
  headerTitle: { ...typography.h3, color: '#fff' },

  list: { padding: spacing.lg },

  card: {
    backgroundColor: colors.surface,
    borderRadius: radius.xl,
    overflow: 'hidden',
    marginBottom: spacing.md,
    flexDirection: 'row',
  },
  dropBanner: {
    position: 'absolute', top: 0, left: 0, right: 0, zIndex: 1,
    backgroundColor: '#22c55e',
    paddingVertical: 4,
    alignItems: 'center',
  },
  dropText: { color: '#fff', fontSize: 11, fontWeight: '700' },
  img: { width: 110, height: 110 },
  imgPlaceholder: { backgroundColor: colors.primaryFaint, alignItems: 'center', justifyContent: 'center' },
  cardBody: { flex: 1, padding: 12, justifyContent: 'space-between' },
  cardTitle: { ...typography.h4, color: colors.text, lineHeight: 20, marginBottom: 4 },
  cardCity: { ...typography.caption, color: colors.textSecondary },
  cardFooter: { flexDirection: 'row', alignItems: 'flex-end', justifyContent: 'space-between', marginTop: 8 },
  cardPrice: { fontSize: 16, fontWeight: '800', color: colors.primary },
  cardNight: { ...typography.caption, color: colors.textSecondary, fontWeight: '400' },
  oldPrice: { ...typography.caption, color: colors.textTertiary, textDecorationLine: 'line-through' },
  removeBtn: {
    width: 32, height: 32, borderRadius: radius.full,
    backgroundColor: '#fee2e2',
    alignItems: 'center', justifyContent: 'center',
  },

  empty: { flex: 1, alignItems: 'center', justifyContent: 'center', padding: spacing.xl },
  emptyEmoji: { fontSize: 56, color: '#f87171', marginBottom: 16 },
  emptyTitle: { ...typography.h3, color: colors.text, marginBottom: 8 },
  emptySub: { ...typography.body, color: colors.textSecondary, textAlign: 'center' },
});
