import React, {useCallback, useEffect, useState} from 'react';
import {
  FlatList,
  RefreshControl,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import {NativeStackNavigationProp} from '@react-navigation/native-stack';
import {useFocusEffect} from '@react-navigation/native';
import apiClient from '../../api/client';
import {LoadingSpinner} from '../../components/LoadingSpinner';
import {useAuth} from '../../store/AuthContext';
import {Conversation, MessagesStackParamList} from '../../types';
import {colors, radius, spacing, typography} from '../../utils/theme';
import {relativeTime} from '../../utils/helpers';

type Props = {
  navigation: NativeStackNavigationProp<MessagesStackParamList, 'ConversationList'>;
};

export const ConversationListScreen: React.FC<Props> = ({navigation}) => {
  const {user} = useAuth();
  const [conversations, setConversations] = useState<Conversation[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const load = async () => {
    try {
      const res = await apiClient.get('/messages');
      setConversations(res.data.data ?? []);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useFocusEffect(useCallback(() => { load(); }, []));

  if (loading) return <LoadingSpinner fullScreen message="Chargement..." />;

  return (
    <View style={styles.screen}>
      <View style={styles.header}>
        <Text style={styles.title}>Messages</Text>
      </View>

      <FlatList
        data={conversations}
        keyExtractor={item => item.id.toString()}
        contentContainerStyle={styles.list}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={() => { setRefreshing(true); load(); }}
            tintColor={colors.primary}
          />
        }
        ListEmptyComponent={
          <View style={styles.empty}>
            <Text style={styles.emptyEmoji}>💬</Text>
            <Text style={styles.emptyText}>Aucun message</Text>
            <Text style={styles.emptySubText}>
              Vos conversations apparaîtront ici.
            </Text>
          </View>
        }
        renderItem={({item}) => {
          const other = user?.id === item.tenant_id ? item.owner : item.tenant;
          const hasUnread = (item.unread_count ?? 0) > 0;

          return (
            <TouchableOpacity
              style={styles.convItem}
              onPress={() =>
                navigation.navigate('Conversation', {
                  conversationId: item.id,
                  title: other?.name ?? 'Conversation',
                })
              }
              activeOpacity={0.8}>
              {/* Avatar */}
              <View style={[styles.avatar, hasUnread && styles.avatarActive]}>
                <Text style={styles.avatarText}>
                  {(other?.name ?? 'U').charAt(0).toUpperCase()}
                </Text>
              </View>

              {/* Content */}
              <View style={styles.convContent}>
                <View style={styles.convHeader}>
                  <Text style={[styles.convName, hasUnread && styles.convNameBold]}>
                    {other?.name ?? 'Utilisateur'}
                  </Text>
                  <Text style={styles.convTime}>
                    {relativeTime(item.last_message_at)}
                  </Text>
                </View>
                <Text style={styles.convProperty} numberOfLines={1}>
                  🏠 {item.property?.title ?? 'Propriété'}
                </Text>
                <View style={styles.convFooter}>
                  <Text style={[styles.convLastMsg, hasUnread && styles.convLastMsgBold]} numberOfLines={1}>
                    {item.last_message?.body ?? 'Démarrer la conversation'}
                  </Text>
                  {hasUnread && (
                    <View style={styles.unreadBadge}>
                      <Text style={styles.unreadText}>{item.unread_count}</Text>
                    </View>
                  )}
                </View>
              </View>
            </TouchableOpacity>
          );
        }}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  screen: {flex: 1, backgroundColor: colors.background},
  header: {padding: spacing.lg, paddingTop: spacing.xl + 8},
  title: {...typography.h1, color: colors.text},
  list: {paddingBottom: 80},
  convItem: {
    flexDirection: 'row',
    padding: spacing.md,
    paddingHorizontal: spacing.lg,
    gap: 12,
    borderBottomWidth: 1,
    borderBottomColor: colors.borderLight,
    backgroundColor: colors.surface,
  },
  avatar: {
    width: 52,
    height: 52,
    borderRadius: 26,
    backgroundColor: colors.gray200,
    alignItems: 'center',
    justifyContent: 'center',
    flexShrink: 0,
  },
  avatarActive: {backgroundColor: colors.primary + '20'},
  avatarText: {fontSize: 20, fontWeight: '700', color: colors.primary},
  convContent: {flex: 1},
  convHeader: {flexDirection: 'row', justifyContent: 'space-between', marginBottom: 2},
  convName: {fontSize: 15, fontWeight: '500', color: colors.text},
  convNameBold: {fontWeight: '700'},
  convTime: {...typography.caption, color: colors.textTertiary},
  convProperty: {...typography.caption, color: colors.textSecondary, marginBottom: 4},
  convFooter: {flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center'},
  convLastMsg: {...typography.body, color: colors.textSecondary, flex: 1},
  convLastMsgBold: {fontWeight: '600', color: colors.text},
  unreadBadge: {
    backgroundColor: colors.primary,
    borderRadius: radius.full,
    minWidth: 20,
    height: 20,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 6,
    marginLeft: 8,
  },
  unreadText: {color: '#fff', fontSize: 11, fontWeight: '700'},
  empty: {alignItems: 'center', paddingTop: 80},
  emptyEmoji: {fontSize: 48, marginBottom: 12},
  emptyText: {...typography.h3, color: colors.textSecondary},
  emptySubText: {...typography.body, color: colors.textTertiary, marginTop: 8},
});
