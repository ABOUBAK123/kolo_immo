import React, {useEffect, useState} from 'react';
import {
  FlatList,
  RefreshControl,
  ScrollView,
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
import {colors, spacing, typography} from '../../utils/theme';

type Props = {
  navigation: NativeStackNavigationProp<HomeStackParamList, 'HomeScreen'>;
};

const CITIES = ['Abidjan', 'Dakar', 'Ouagadougou', 'Bamako', 'Cotonou', 'Lomé'];

export const HomeScreen: React.FC<Props> = ({navigation}) => {
  const {user} = useAuth();
  const [featured, setFeatured] = useState<Property[]>([]);
  const [recent, setRecent] = useState<Property[]>([]);
  const [selectedCity, setSelectedCity] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const [featuredRes, recentRes] = await Promise.all([
        propertiesApi.featured(),
        propertiesApi.list({sort: 'newest', page: 1}),
      ]);
      setFeatured(featuredRes.data.data ?? []);
      setRecent(recentRes.data.data ?? []);
    } catch {
      // Silent fail — show empty state
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const onRefresh = () => {
    setRefreshing(true);
    loadData();
  };

  const goToProperty = (property: Property) => {
    navigation.navigate('PropertyDetail', {property});
  };

  const filteredRecent = selectedCity
    ? recent.filter(p => p.city === selectedCity)
    : recent;

  if (loading) return <LoadingSpinner fullScreen message="Chargement..." />;

  return (
    <ScrollView
      style={styles.screen}
      showsVerticalScrollIndicator={false}
      refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={colors.primary} />}>

      {/* Header */}
      <View style={styles.header}>
        <View>
          <Text style={styles.greeting}>Bonjour, {user?.name?.split(' ')[0]} 👋</Text>
          <Text style={styles.subtitle}>Trouvez votre logement idéal</Text>
        </View>
        <View style={styles.avatar}>
          <Text style={styles.avatarText}>
            {(user?.name ?? 'U').charAt(0).toUpperCase()}
          </Text>
        </View>
      </View>

      {/* Search banner */}
      <TouchableOpacity
        style={styles.searchBanner}
        onPress={() => navigation.getParent()?.navigate('Search')}
        activeOpacity={0.8}>
        <Text style={styles.searchIcon}>🔍</Text>
        <Text style={styles.searchText}>Ville, dates, voyageurs...</Text>
      </TouchableOpacity>

      {/* Featured */}
      {featured.length > 0 && (
        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <Text style={styles.sectionTitle}>✨ Coups de cœur</Text>
          </View>
          <FlatList
            data={featured}
            horizontal
            showsHorizontalScrollIndicator={false}
            keyExtractor={item => item.id.toString()}
            contentContainerStyle={{paddingHorizontal: spacing.lg, gap: 12}}
            renderItem={({item}) => (
              <View style={{width: 260}}>
                <PropertyCard property={item} onPress={() => goToProperty(item)} />
              </View>
            )}
          />
        </View>
      )}

      {/* City filter */}
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
            <Text style={[styles.cityChipText, !selectedCity && styles.cityChipTextActive]}>
              Tout
            </Text>
          </TouchableOpacity>
          {CITIES.map(city => (
            <TouchableOpacity
              key={city}
              style={[styles.cityChip, selectedCity === city && styles.cityChipActive]}
              onPress={() => setSelectedCity(city)}>
              <Text style={[styles.cityChipText, selectedCity === city && styles.cityChipTextActive]}>
                {city}
              </Text>
            </TouchableOpacity>
          ))}
        </ScrollView>
      </View>

      {/* Recent listings */}
      <View style={[styles.section, {paddingHorizontal: spacing.lg}]}>
        <Text style={styles.sectionTitle}>🏠 Logements récents</Text>
        {filteredRecent.length === 0 ? (
          <View style={styles.empty}>
            <Text style={styles.emptyText}>Aucun logement pour le moment</Text>
          </View>
        ) : (
          filteredRecent.map(property => (
            <PropertyCard
              key={property.id}
              property={property}
              onPress={() => goToProperty(property)}
            />
          ))
        )}
      </View>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  screen: {flex: 1, backgroundColor: colors.background},
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: spacing.lg,
    paddingTop: spacing.xl + 8,
  },
  greeting: {...typography.h2, color: colors.text},
  subtitle: {...typography.body, color: colors.textSecondary, marginTop: 2},
  avatar: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: colors.primary,
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarText: {color: '#fff', fontSize: 18, fontWeight: '700'},
  searchBanner: {
    marginHorizontal: spacing.lg,
    marginBottom: spacing.lg,
    backgroundColor: colors.surface,
    borderRadius: 16,
    paddingHorizontal: 16,
    paddingVertical: 14,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    borderWidth: 1.5,
    borderColor: colors.border,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: {width: 0, height: 1},
    shadowOpacity: 0.06,
    shadowRadius: 4,
  },
  searchIcon: {fontSize: 18},
  searchText: {...typography.body, color: colors.textLight},
  section: {marginBottom: 24},
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.lg,
    marginBottom: 12,
  },
  sectionTitle: {...typography.h3, color: colors.text, marginBottom: 12},
  cityRow: {paddingHorizontal: spacing.lg, gap: 8, paddingBottom: 4},
  cityChip: {
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 999,
    backgroundColor: colors.surface,
    borderWidth: 1.5,
    borderColor: colors.border,
  },
  cityChipActive: {backgroundColor: colors.primary, borderColor: colors.primary},
  cityChipText: {fontSize: 13, fontWeight: '600', color: colors.textSecondary},
  cityChipTextActive: {color: '#fff'},
  empty: {alignItems: 'center', paddingVertical: 40},
  emptyText: {...typography.body, color: colors.textSecondary},
});
