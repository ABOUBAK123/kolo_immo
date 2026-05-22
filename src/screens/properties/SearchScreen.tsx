import React, {useCallback, useEffect, useState} from 'react';
import {
  ActivityIndicator,
  FlatList,
  StatusBar,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import {NativeStackNavigationProp} from '@react-navigation/native-stack';
import {RouteProp} from '@react-navigation/native';
import {propertiesApi} from '../../api/properties';
import {PropertyCard} from '../../components/PropertyCard';
import {Property, SearchStackParamList} from '../../types';
import {colors, radius, shadows, spacing, typography} from '../../utils/theme';

type Props = {
  navigation: NativeStackNavigationProp<SearchStackParamList, 'SearchScreen'>;
  route?: RouteProp<any, any>;
};

const TYPES = ['Tout', 'Appartement', 'Studio', 'Villa', 'Chambre', 'Maison'];
const SORTS = [
  {value: 'newest',     label: 'Plus récent'},
  {value: 'price_asc',  label: 'Prix croissant'},
  {value: 'price_desc', label: 'Prix décroissant'},
  {value: 'rating',     label: 'Mieux noté'},
];

export const SearchScreen: React.FC<Props> = ({navigation, route}) => {
  const initialType = (route?.params as any)?.type ?? '';
  const [city, setCity]           = useState('');
  const [type, setType]           = useState(initialType);
  const [sort, setSort]           = useState('newest');
  const [showSort, setShowSort]   = useState(false);
  const [properties, setProperties] = useState<Property[]>([]);
  const [page, setPage]           = useState(1);
  const [lastPage, setLastPage]   = useState(1);
  const [loading, setLoading]     = useState(false);
  const [loadingMore, setLoadingMore] = useState(false);
  const [total, setTotal]         = useState(0);

  const search = useCallback(async (reset = false) => {
    if (reset) { setPage(1); setLoading(true); }
    else setLoadingMore(true);
    try {
      const currentPage = reset ? 1 : page;
      const res = await propertiesApi.list({
        city: city || undefined,
        type: type || undefined,
        sort,
        page: currentPage,
      });
      const {properties: items, pagination} = res.data.data;
      setLastPage(pagination.last_page);
      setTotal(pagination.total);
      if (reset) {
        setProperties(items ?? []);
      } else {
        setProperties(prev => [...prev, ...(items ?? [])]);
        setPage(p => p + 1);
      }
    } finally {
      setLoading(false);
      setLoadingMore(false);
    }
  }, [city, type, sort, page]);

  useEffect(() => { search(true); }, [type, sort]);
  // eslint-disable-next-line react-hooks/exhaustive-deps

  const sortLabel = SORTS.find(s => s.value === sort)?.label ?? 'Trier';

  return (
    <View style={styles.screen}>
      <StatusBar barStyle="light-content" backgroundColor={colors.primary} />

      {/* ── Search header ── */}
      <View style={styles.header}>
        <View style={styles.searchRow}>
          <View style={styles.searchBox}>
            <Text style={styles.searchIcon}>🔍</Text>
            <TextInput
              style={styles.searchInput}
              value={city}
              onChangeText={setCity}
              onSubmitEditing={() => search(true)}
              placeholder="Ville (Abidjan, Dakar...)"
              placeholderTextColor="rgba(255,255,255,0.55)"
              returnKeyType="search"
              autoCorrect={false}
            />
            {city.length > 0 && (
              <TouchableOpacity onPress={() => { setCity(''); search(true); }}>
                <Text style={styles.clearBtn}>✕</Text>
              </TouchableOpacity>
            )}
          </View>
          <TouchableOpacity
            style={styles.searchBtn}
            onPress={() => search(true)}>
            <Text style={styles.searchBtnText}>OK</Text>
          </TouchableOpacity>
        </View>

        {/* Type filter pills */}
        <FlatList
          data={TYPES}
          horizontal
          showsHorizontalScrollIndicator={false}
          keyExtractor={t => t}
          contentContainerStyle={styles.typesRow}
          renderItem={({item}) => {
            const active = item === 'Tout' ? !type : type === item;
            return (
              <TouchableOpacity
                style={[styles.typeChip, active && styles.typeChipActive]}
                onPress={() => setType(item === 'Tout' ? '' : item)}>
                <Text style={[styles.typeText, active && styles.typeTextActive]}>{item}</Text>
              </TouchableOpacity>
            );
          }}
        />
      </View>

      {/* ── Results bar ── */}
      <View style={styles.resultsBar}>
        <Text style={styles.resultsText}>
          {loading ? 'Recherche...' : `${total} logement${total !== 1 ? 's' : ''}`}
        </Text>
        <TouchableOpacity
          style={styles.sortBtn}
          onPress={() => setShowSort(v => !v)}>
          <Text style={styles.sortBtnText}>⇅ {sortLabel}</Text>
        </TouchableOpacity>
      </View>

      {/* Sort dropdown */}
      {showSort && (
        <View style={styles.sortDropdown}>
          {SORTS.map(s => (
            <TouchableOpacity
              key={s.value}
              style={[styles.sortItem, sort === s.value && styles.sortItemActive]}
              onPress={() => { setSort(s.value); setShowSort(false); }}>
              <Text style={[styles.sortItemText, sort === s.value && styles.sortItemTextActive]}>
                {sort === s.value ? '✓  ' : '     '}{s.label}
              </Text>
            </TouchableOpacity>
          ))}
        </View>
      )}

      {/* ── List ── */}
      {loading ? (
        <View style={styles.loadingWrap}>
          <ActivityIndicator size="large" color={colors.primary} />
          <Text style={styles.loadingText}>Recherche en cours...</Text>
        </View>
      ) : (
        <FlatList
          data={properties}
          keyExtractor={item => item.id.toString()}
          contentContainerStyle={styles.list}
          showsVerticalScrollIndicator={false}
          onEndReached={() => { if (page <= lastPage && !loadingMore) search(); }}
          onEndReachedThreshold={0.3}
          ListEmptyComponent={
            <View style={styles.empty}>
              <Text style={styles.emptyEmoji}>🏙️</Text>
              <Text style={styles.emptyTitle}>Aucun résultat</Text>
              <Text style={styles.emptySub}>Essayez d'autres critères de recherche</Text>
            </View>
          }
          ListFooterComponent={
            loadingMore ? (
              <View style={styles.footer}>
                <ActivityIndicator color={colors.primary} />
              </View>
            ) : null
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

  header: {
    backgroundColor: colors.primary,
    paddingTop: 48,
    paddingBottom: 12,
    paddingHorizontal: spacing.lg,
  },
  searchRow: {flexDirection: 'row', gap: 10, alignItems: 'center', marginBottom: 12},
  searchBox: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(255,255,255,0.18)',
    borderRadius: radius.xl,
    paddingHorizontal: 14,
    paddingVertical: 10,
    gap: 8,
  },
  searchIcon: {fontSize: 16, opacity: 0.8},
  searchInput: {flex: 1, ...typography.body, color: '#fff', padding: 0},
  clearBtn: {color: 'rgba(255,255,255,0.7)', fontSize: 14, fontWeight: '600'},
  searchBtn: {
    backgroundColor: colors.accent,
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderRadius: radius.xl,
  },
  searchBtnText: {color: '#fff', fontWeight: '700', fontSize: 13},

  typesRow: {gap: 8, paddingBottom: 4},
  typeChip: {
    paddingHorizontal: 14,
    paddingVertical: 7,
    borderRadius: radius.full,
    backgroundColor: 'rgba(255,255,255,0.18)',
  },
  typeChipActive: {backgroundColor: colors.accent},
  typeText: {...typography.label, color: 'rgba(255,255,255,0.8)'},
  typeTextActive: {color: '#fff', fontWeight: '700'},

  resultsBar: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: spacing.lg,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
    backgroundColor: colors.surface,
  },
  resultsText: {...typography.label, color: colors.textSecondary},
  sortBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    backgroundColor: colors.gray100,
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: radius.full,
  },
  sortBtnText: {...typography.label, color: colors.textMed},

  sortDropdown: {
    position: 'absolute',
    top: 178,
    right: spacing.lg,
    backgroundColor: colors.surface,
    borderRadius: radius.xl,
    borderWidth: 1,
    borderColor: colors.border,
    zIndex: 100,
    ...shadows.lg,
    overflow: 'hidden',
  },
  sortItem: {paddingHorizontal: 20, paddingVertical: 14},
  sortItemActive: {backgroundColor: colors.primaryFaint},
  sortItemText: {...typography.body, color: colors.textMed},
  sortItemTextActive: {color: colors.primary, fontWeight: '600'},

  list: {padding: spacing.lg, paddingBottom: 90},
  loadingWrap: {flex: 1, alignItems: 'center', justifyContent: 'center', gap: 12},
  loadingText: {...typography.body, color: colors.textSecondary},
  footer: {paddingVertical: 20, alignItems: 'center'},

  empty: {alignItems: 'center', paddingTop: 60},
  emptyEmoji: {fontSize: 52, marginBottom: 14},
  emptyTitle: {...typography.h3, color: colors.textSecondary, textAlign: 'center'},
  emptySub: {...typography.body, color: colors.textTertiary, textAlign: 'center', marginTop: 8},
});
