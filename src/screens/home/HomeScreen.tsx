import React, {useEffect, useState} from 'react';
import {
  FlatList,
  RefreshControl,
  ScrollView,
  StatusBar,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import {NativeStackNavigationProp} from '@react-navigation/native-stack';
import {propertiesApi} from '../../api/properties';
import {PropertyCard} from '../../components/PropertyCard';
import {LoadingSpinner} from '../../components/LoadingSpinner';
import {useAuth} from '../../store/AuthContext';
import {Property, HomeStackParamList} from '../../types';
import {colors, radius, shadows, spacing, typography} from '../../utils/theme';

type Props = {
  navigation: NativeStackNavigationProp<HomeStackParamList, 'HomeScreen'>;
};

const CATEGORIES = [
  {label: 'Studio',      emoji: '🏢', value: 'Studio'},
  {label: 'Appartement', emoji: '🏠', value: 'Appartement'},
  {label: 'Villa',       emoji: '🏡', value: 'Villa'},
  {label: 'Chambre',     emoji: '🛏',  value: 'Chambre'},
  {label: 'Maison',      emoji: '🏘',  value: 'Maison'},
];

const CITIES = ['Abidjan', 'Dakar', 'Ouagadougou', 'Bamako', 'Cotonou', 'Lomé'];

export const HomeScreen: React.FC<Props> = ({navigation}) => {
  const {user} = useAuth();
  const [featured, setFeatured] = useState<Property[]>([]);
  const [recent, setRecent]     = useState<Property[]>([]);
  const [selectedCity, setSelectedCity] = useState<string | null>(null);
  const [loading, setLoading]   = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const [featRes, recRes] = await Promise.all([
        propertiesApi.featured(),
        propertiesApi.list({sort: 'newest', page: 1}),
      ]);
      setFeatured(featRes.data.data ?? []);
      setRecent(recRes.data.data?.properties ?? []);
    } catch {
      // silent fail
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => { loadData(); }, []);

  const onRefresh = () => { setRefreshing(true); loadData(); };

  const goToProperty = (property: Property) =>
    navigation.navigate('PropertyDetail', {property});

  const goToSearch = (type?: string) =>
    navigation.getParent()?.navigate('Search', {type});

  const filteredRecent = selectedCity
    ? recent.filter(p => p.city === selectedCity)
    : recent;

  if (loading) return <LoadingSpinner fullScreen message="Chargement..." />;

  const firstName = user?.name?.split(' ')[0] ?? 'vous';

  return (
    <View style={styles.screen}>
      <StatusBar barStyle="light-content" backgroundColor={colors.primary} />

      <ScrollView
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={colors.accent} />
        }>

        {/* ── Header ── */}
        <View style={styles.header}>
          <View>
            <Text style={styles.greeting}>Bonjour, {firstName} 👋</Text>
            <Text style={styles.subtitle}>Où voulez-vous séjourner ?</Text>
          </View>
          <View style={styles.avatar}>
            <Text style={styles.avatarText}>
              {(user?.name ?? 'K').charAt(0).toUpperCase()}
            </Text>
          </View>
        </View>

        {/* ── Search bar ── */}
        <View style={styles.searchWrap}>
          <TouchableOpacity
            style={styles.searchBar}
            onPress={() => goToSearch()}
            activeOpacity={0.85}>
            <Text style={styles.searchIcon}>🔍</Text>
            <Text style={styles.searchPlaceholder}>Ville, type de logement...</Text>
          </TouchableOpacity>
        </View>

        {/* ── Categories ── */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Explorer par type</Text>
          <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.categoriesRow}>
            {CATEGORIES.map(cat => (
              <TouchableOpacity
                key={cat.value}
                style={styles.catCard}
                onPress={() => goToSearch(cat.value)}
                activeOpacity={0.8}>
                <View style={styles.catIcon}>
                  <Text style={styles.catEmoji}>{cat.emoji}</Text>
                </View>
                <Text style={styles.catLabel}>{cat.label}</Text>
              </TouchableOpacity>
            ))}
          </ScrollView>
        </View>

        {/* ── Featured ── */}
        {featured.length > 0 && (
          <View style={styles.section}>
            <View style={styles.sectionHeader}>
              <Text style={styles.sectionTitle}>✨ Coups de cœur</Text>
              <TouchableOpacity onPress={() => goToSearch()}>
                <Text style={styles.seeAll}>Voir tout</Text>
              </TouchableOpacity>
            </View>
            <FlatList
              data={featured}
              horizontal
              showsHorizontalScrollIndicator={false}
              keyExtractor={item => item.id.toString()}
              contentContainerStyle={{paddingHorizontal: spacing.lg, gap: 12}}
              renderItem={({item}) => (
                <View style={{width: 270}}>
                  <PropertyCard property={item} onPress={() => goToProperty(item)} />
                </View>
              )}
            />
          </View>
        )}

        {/* ── City filter ── */}
        <View style={styles.section}>
          <Text style={[styles.sectionTitle, {paddingHorizontal: spacing.lg}]}>
            📍 Explorer par ville
          </Text>
          <ScrollView
            horizontal
            showsHorizontalScrollIndicator={false}
            contentContainerStyle={styles.cityRow}>
            <TouchableOpacity
              style={[styles.cityChip, !selectedCity && styles.cityChipActive]}
              onPress={() => setSelectedCity(null)}>
              <Text style={[styles.cityText, !selectedCity && styles.cityTextActive]}>Tout</Text>
            </TouchableOpacity>
            {CITIES.map(city => (
              <TouchableOpacity
                key={city}
                style={[styles.cityChip, selectedCity === city && styles.cityChipActive]}
                onPress={() => setSelectedCity(city)}>
                <Text style={[styles.cityText, selectedCity === city && styles.cityTextActive]}>
                  {city}
                </Text>
              </TouchableOpacity>
            ))}
          </ScrollView>
        </View>

        {/* ── Recent ── */}
        <View style={[styles.section, {paddingHorizontal: spacing.lg}]}>
          <View style={styles.sectionHeader}>
            <Text style={styles.sectionTitle}>🏠 Logements récents</Text>
            <Text style={styles.countLabel}>{filteredRecent.length} annonces</Text>
          </View>
          {filteredRecent.length === 0 ? (
            <View style={styles.empty}>
              <Text style={styles.emptyEmoji}>🏙️</Text>
              <Text style={styles.emptyText}>Aucun logement disponible</Text>
              <Text style={styles.emptySub}>Essayez une autre ville ou revenez plus tard</Text>
            </View>
          ) : (
            filteredRecent.map(p => (
              <PropertyCard key={p.id} property={p} onPress={() => goToProperty(p)} />
            ))
          )}
        </View>

        <View style={{height: 90}} />
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.background },

  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    backgroundColor: colors.primary,
    paddingHorizontal: spacing.lg,
    paddingTop: 52,
    paddingBottom: spacing.lg,
  },
  greeting: { ...typography.h2, color: '#fff', marginBottom: 2 },
  subtitle: { ...typography.body, color: 'rgba(255,255,255,0.72)' },
  avatar: {
    width: 46,
    height: 46,
    borderRadius: radius.full,
    backgroundColor: colors.accent,
    alignItems: 'center',
    justifyContent: 'center',
    ...shadows.sm,
  },
  avatarText: { color: '#fff', fontSize: 20, fontWeight: '800' },

  searchWrap: {
    backgroundColor: colors.primary,
    paddingHorizontal: spacing.lg,
    paddingBottom: 20,
  },
  searchBar: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.surface,
    borderRadius: radius.xl,
    paddingHorizontal: 16,
    paddingVertical: 14,
    gap: 10,
    ...shadows.md,
  },
  searchIcon: { fontSize: 18 },
  searchPlaceholder: { ...typography.body, color: colors.textTertiary, flex: 1 },

  section: { marginBottom: 8 },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: spacing.lg,
    marginBottom: 12,
    marginTop: 20,
  },
  sectionTitle: { ...typography.h3, color: colors.text },
  seeAll: { ...typography.label, color: colors.primaryMid, fontWeight: '600' },
  countLabel: { ...typography.caption, color: colors.textTertiary },

  // Categories
  categoriesRow: { paddingHorizontal: spacing.lg, gap: 10, paddingBottom: 4, paddingTop: 4 },
  catCard: { alignItems: 'center', width: 72 },
  catIcon: {
    width: 56,
    height: 56,
    borderRadius: radius.xl,
    backgroundColor: colors.surface,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 6,
    ...shadows.sm,
    borderWidth: 1,
    borderColor: colors.border,
  },
  catEmoji: { fontSize: 26 },
  catLabel: { ...typography.caption, color: colors.textSecondary, textAlign: 'center', fontWeight: '500' },

  // City chips
  cityRow: { paddingHorizontal: spacing.lg, gap: 8, paddingVertical: 4 },
  cityChip: {
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: radius.full,
    backgroundColor: colors.surface,
    borderWidth: 1.5,
    borderColor: colors.border,
    ...shadows.xs,
  },
  cityChipActive: { backgroundColor: colors.primary, borderColor: colors.primary },
  cityText: { ...typography.label, color: colors.textSecondary },
  cityTextActive: { color: '#fff' },

  // Empty state
  empty: { alignItems: 'center', paddingVertical: 40 },
  emptyEmoji: { fontSize: 52, marginBottom: 12 },
  emptyText: { ...typography.h4, color: colors.textSecondary, textAlign: 'center' },
  emptySub: { ...typography.body, color: colors.textTertiary, textAlign: 'center', marginTop: 6 },
});
