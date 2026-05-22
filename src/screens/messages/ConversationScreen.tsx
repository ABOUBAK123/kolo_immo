import React, {useEffect, useRef, useState} from 'react';
import {
  Alert,
  FlatList,
  KeyboardAvoidingView,
  Platform,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import {NativeStackNavigationProp} from '@react-navigation/native-stack';
import {RouteProp} from '@react-navigation/native';
import apiClient from '../../api/client';
import {LoadingSpinner} from '../../components/LoadingSpinner';
import {useAuth} from '../../store/AuthContext';
import {ChatMessage, MessagesStackParamList} from '../../types';
import {colors, radius, spacing, typography} from '../../utils/theme';
import {relativeTime} from '../../utils/helpers';

type Props = {
  navigation: NativeStackNavigationProp<MessagesStackParamList, 'Conversation'>;
  route: RouteProp<MessagesStackParamList, 'Conversation'>;
};

export const ConversationScreen: React.FC<Props> = ({navigation, route}) => {
  const {conversationId, title} = route.params;
  const {user} = useAuth();
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [loading, setLoading] = useState(true);
  const [body, setBody] = useState('');
  const [sending, setSending] = useState(false);
  const flatRef = useRef<FlatList>(null);

  const load = async () => {
    try {
      const res = await apiClient.get(`/messages/${conversationId}`);
      const msgs = res.data.conversation?.messages ?? [];
      setMessages(msgs);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    load();
    // Mark as read
    apiClient.post(`/messages/${conversationId}/read`).catch(() => {});
  }, [conversationId]);

  const handleSend = async () => {
    if (!body.trim()) return;
    setSending(true);
    const tempMsg: ChatMessage = {
      id: Date.now(),
      conversation_id: conversationId,
      sender_id: user!.id,
      body: body.trim(),
      attachment_path: null,
      attachment_type: 'none',
      read_at: null,
      created_at: new Date().toISOString(),
      sender: user as any,
    };
    setMessages(prev => [...prev, tempMsg]);
    setBody('');
    try {
      await apiClient.post(`/messages/${conversationId}/send`, {body: body.trim()});
    } catch {
      Alert.alert('Erreur', 'Message non envoyé.');
      setMessages(prev => prev.filter(m => m.id !== tempMsg.id));
    } finally {
      setSending(false);
    }
  };

  useEffect(() => {
    if (messages.length > 0) {
      setTimeout(() => flatRef.current?.scrollToEnd({animated: true}), 100);
    }
  }, [messages.length]);

  if (loading) return <LoadingSpinner fullScreen message="Chargement..." />;

  return (
    <KeyboardAvoidingView
      style={styles.screen}
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
      keyboardVerticalOffset={Platform.OS === 'ios' ? 90 : 0}>

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.back}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <View style={styles.headerAvatar}>
          <Text style={styles.headerAvatarText}>{title.charAt(0).toUpperCase()}</Text>
        </View>
        <Text style={styles.headerTitle} numberOfLines={1}>{title}</Text>
      </View>

      {/* Messages */}
      <FlatList
        ref={flatRef}
        data={messages}
        keyExtractor={item => item.id.toString()}
        contentContainerStyle={styles.messageList}
        showsVerticalScrollIndicator={false}
        ListEmptyComponent={
          <View style={styles.emptyConv}>
            <Text style={styles.emptyConvText}>Démarrez la conversation 👋</Text>
          </View>
        }
        renderItem={({item, index}) => {
          const isMine = item.sender_id === user?.id;
          const prevMsg = index > 0 ? messages[index - 1] : null;
          const showTime =
            !prevMsg ||
            new Date(item.created_at).getTime() - new Date(prevMsg.created_at).getTime() > 300000;

          return (
            <>
              {showTime && (
                <Text style={styles.timeStamp}>{relativeTime(item.created_at)}</Text>
              )}
              <View style={[styles.msgRow, isMine && styles.msgRowMine]}>
                {!isMine && (
                  <View style={styles.msgAvatar}>
                    <Text style={styles.msgAvatarText}>
                      {(item.sender?.name ?? '?').charAt(0).toUpperCase()}
                    </Text>
                  </View>
                )}
                <View
                  style={[
                    styles.bubble,
                    isMine ? styles.bubbleMine : styles.bubbleTheirs,
                  ]}>
                  {item.body ? (
                    <Text style={[styles.bubbleText, isMine && styles.bubbleTextMine]}>
                      {item.body}
                    </Text>
                  ) : null}
                  {item.attachment_path && (
                    <Text style={[styles.attachment, isMine && styles.bubbleTextMine]}>
                      📎 Pièce jointe
                    </Text>
                  )}
                </View>
              </View>
            </>
          );
        }}
      />

      {/* Input bar */}
      <View style={styles.inputBar}>
        <TextInput
          style={styles.input}
          placeholder="Écrire un message..."
          placeholderTextColor={colors.textTertiary}
          value={body}
          onChangeText={setBody}
          multiline
          maxLength={2000}
        />
        <TouchableOpacity
          style={[styles.sendBtn, !body.trim() && styles.sendBtnDisabled]}
          onPress={handleSend}
          disabled={!body.trim() || sending}>
          <Text style={styles.sendBtnText}>↑</Text>
        </TouchableOpacity>
      </View>
    </KeyboardAvoidingView>
  );
};

const styles = StyleSheet.create({
  screen: {flex: 1, backgroundColor: colors.background},
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    padding: spacing.md,
    paddingTop: spacing.xl,
    backgroundColor: colors.surface,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  back: {padding: 4},
  backArrow: {fontSize: 22, color: colors.primary},
  headerAvatar: {
    width: 38,
    height: 38,
    borderRadius: 19,
    backgroundColor: colors.primary + '20',
    alignItems: 'center',
    justifyContent: 'center',
  },
  headerAvatarText: {fontSize: 16, fontWeight: '700', color: colors.primary},
  headerTitle: {...typography.h3, color: colors.text, flex: 1},
  messageList: {padding: spacing.md, gap: 4, paddingBottom: 8},
  timeStamp: {
    textAlign: 'center',
    ...typography.caption,
    color: colors.textTertiary,
    marginVertical: 10,
  },
  msgRow: {flexDirection: 'row', alignItems: 'flex-end', gap: 6, marginBottom: 4},
  msgRowMine: {flexDirection: 'row-reverse'},
  msgAvatar: {
    width: 30,
    height: 30,
    borderRadius: 15,
    backgroundColor: colors.gray200,
    alignItems: 'center',
    justifyContent: 'center',
    flexShrink: 0,
  },
  msgAvatarText: {fontSize: 13, fontWeight: '600', color: colors.textSecondary},
  bubble: {
    maxWidth: '78%',
    paddingHorizontal: 14,
    paddingVertical: 10,
    borderRadius: radius.xl,
  },
  bubbleMine: {
    backgroundColor: colors.primary,
    borderBottomRightRadius: 4,
  },
  bubbleTheirs: {
    backgroundColor: colors.surface,
    borderBottomLeftRadius: 4,
    borderWidth: 1,
    borderColor: colors.border,
  },
  bubbleText: {...typography.body, color: colors.text, lineHeight: 22},
  bubbleTextMine: {color: '#fff'},
  attachment: {...typography.caption, color: colors.textSecondary},
  emptyConv: {alignItems: 'center', paddingTop: 60},
  emptyConvText: {...typography.body, color: colors.textSecondary},
  inputBar: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    gap: 10,
    padding: spacing.md,
    backgroundColor: colors.surface,
    borderTopWidth: 1,
    borderTopColor: colors.border,
  },
  input: {
    flex: 1,
    backgroundColor: colors.background,
    borderRadius: radius.lg,
    paddingHorizontal: 14,
    paddingVertical: 10,
    fontSize: 14,
    color: colors.text,
    maxHeight: 100,
    borderWidth: 1.5,
    borderColor: colors.border,
  },
  sendBtn: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: colors.primary,
    alignItems: 'center',
    justifyContent: 'center',
    flexShrink: 0,
  },
  sendBtnDisabled: {backgroundColor: colors.gray300},
  sendBtnText: {color: '#fff', fontSize: 20, fontWeight: '700'},
});
