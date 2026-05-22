import React, {useCallback, useEffect, useState} from 'react';
import {
  ActivityIndicator,
  FlatList,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import {NativeStackNavigationProp} from '@react-navigation/native-stack';
import {propertiesApi} from '../../api/properties';
import {PropertyCard} from '../../components/PropertyCard';
import {Property, SearchStackParamList} from '../../types';
import {colors, spacing, typography} from '../../utils/theme';

type Props = {
  navigation: NativeStackNavigationProp<SearchStackParamList, 'SearchScreen'>;
};

const TYPES = ['Appartement', 'Studio', 'Villa', 'Chambre', 'Maison'];
const SORTS = [
  {value: 'newest', label: 'Plus récent'},
  {value: 'price_asc', label: 'Prix ↑'},
  {value: 'price_desc', label: 'Prix ↓'},
  {value: 'rating', label: 'Note'},
];

export const SearchScreen: React.FC<Props> = ({navigation}) => {
  const [city, setCity] = useState('');
  const [type, setType] = useState('');
  const [sort, setSort] = useState('newest');
  const [properties, setProperties] = useState<Property[]>([]);
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [loading, setLoading] = useState(false);
  const [loadingMore, setLoadingMore] = useState(false);

  const search = useCallback(async (reset = false) => {
    if (reset) {
      setPage(1);
      setLoading(true);
    } else {
      setLoadingMore(true);
    }
    try {
      const currentPage = reset ? 1 : page;
      const res = await propertiesApi.list({
        city: city || undefined,
        type: type || undefined,
        sort,
        page: currentPage,
      });
      const {data, last_page} = res.data;
      setLastPage(last_page);
      if (reset) {
        setProperties(data);
      } else {
        setProperties(prev => [...prev, ...data]);
        setPage(p => p + 1);
      }
    } finally {
      setLoading(false);
      setLoadingMore(false);
    }
  }, [city, type, sort, page]);

  useEffect(() => {
    search(true);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [sort, type]);

  const loadMore = () => {
    if (!loadingMore && page <= lastPage) {
      search(false);
    }
  };

  return (
    <View style={styles.screen}>
      {/* Header */}
      <View style={styles.header}>
        <Text style={styles.title}>Rechercher</Text>
      </View>

      {/* Search bar */}
      <View style={styles.searchRow}>
        <TextInput
          style={styles.searchInput}
          placeholder="Ville, quartier..."
          placeholderTextColor={colors.textLight}
          value={city}
          onChangeText={setCity}
          onSubmitEditing={() => search(true)}
          returnKeyType="search"
        />
        <TouchableOpacity style={styles.searchBtn} onPress={() => search(true)}>
          <Text style={styles.searchBtnText}>Chercher</Text>
        </TouchableOpacity>
      </View>

      {/* Type filter */}
      <FlatList
        data={TYPES}
        horizontal
        showsHorizontalScrollIndicator={false}
        keyExtractor={item => item}
        contentContainerStyle={styles.filterRow}
        renderItem={({item}) => (
          <TouchableOpacity
            style={[styles.chip, type === item && styles.chipActive]}
            onPress={() => setType(type === item ? '' : item)}>
            <Text style={[styles.chipText, type === item && styles.chipTextActive]}>
              {item}
            </Text>
          </TouchableOpacity>
        )}
      />

      {/* Sort */}
      <FlatList
        data={SORTS}
        horizontal
        showsHorizontalScrollIndicator={false}
        keyExtractor={item => item.value}
        contentContainerStyle={styles.sortRow}
        renderItem={({item}) => (
          <TouchableOpacity
            style={[styles.sortChip, sort === item.value && styles.sortChipActive]}
            onPress={() => setSort(item.value)}>
            <Text style={[styles.sortText, sort === item.value && styles.sortTextActive]}>
              {item.label}
            </Text>
          </TouchableOpacity>
        )}
      />

      {/* Results */}
      {loading ? (
        <View style={styles.loadingWrap}>
          <ActivityIndicator size="large" color={colors.primary} />
        </View>
      ) : (
        <FlatList
          data={properties}
          keyExtractor={item => item.id.toString()}
          contentContainerStyle={styles.list}
          onEndReached={loadMore}
          onEndReachedThreshold={0.3}
          ListEmptyComponent={
            <View style={styles.empty}>
              <Text style={styles.emptyText}>Aucun logement trouvé</Text>
              <Text style={styles.emptySubText}>Essayez une autre ville ou type</Text>
            </View>
          }
          ListFooterComponent={
            loadingMore ? <ActivityIndicator color={colors.primary} style={{margin: 16}} /> : null
          }
          renderItem={({item}) => (
            <PropertyCard
              property={item}
              onPress={() => navigation.navigate('PropertyDetail', {property: item})}
            />
          )}
        />
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  screen: {flex: 1, backgroundColor: colors.background},
  header: {padding: spacing.lg, paddingTop: spacing.xl + 8},
  title: {...typography.h1, color: colors.text},
  searchRow: {
    flexDirection: 'row',
    paddingHorizontal: spacing.lg,
    gap: 8,
    marginBottom: 12,
  },
  searchInput: {
    flex: 1,
    backgroundColor: colors.surface,
    borderRadius: 12,
    paddingHorizontal: 14,
    paddingVertical: 12,
    borderWidth: 1.5,
    borderColor: colors.border,
    fontSize: 14,
    color: colors.text,
  },
  searchBtn: {
    backgroundColor: colors.primary,
    borderRadius: 12,
    paddingHorizontal: 16,
    justifyContent: 'center',
  },
  searchBtnText: {color: '#fff', fontWeight: '700', fontSize: 13},
  filterRow: {paddingHorizontal: spacing.lg, gap: 8, marginBottom: 8},
  chip: {
    paddingHorizontal: 14,
    paddingVertical: 7,
    borderRadius: 999,
    backgroundColor: colors.surface,
    borderWidth: 1.5,
    borderColor: colors.border,
  },
  chipActive: {backgroundColor: colors.primary, borderColor: colors.primary},
  chipText: {fontSize: 13, fontWeight: '600', color: colors.textSecondary},
  chipTextActive: {color: '#fff'},
  sortRow: {paddingHorizontal: spacing.lg, gap: 8, marginBottom: 12},
  sortChip: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 999,
    backgroundColor: colors.gray100,
  },
  sortChipActive: {backgroundColor: colors.primary + '20'},
  sortText: {fontSize: 12, fontWeight: '600', color: colors.textSecondary},
  sortTextActive: {color: colors.primary},
  list: {paddingHorizontal: spacing.lg, paddingBottom: 80},
  loadingWrap: {flex: 1, justifyContent: 'center', alignItems: 'center'},
  empty: {alignItems: 'center', paddingTop: 60},
  emptyText: {...typography.h3, color: colors.textSecondary},
  emptySubText: {...typography.body, color: colors.textLight, marginTop: 8},
});
