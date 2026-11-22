// mobile/src/screens/groups/GroupChatScreen.tsx
import React, { useState, useCallback } from 'react';
import {
    View,
    StyleSheet,
    FlatList,
    Pressable,
    TextInput,
    KeyboardAvoidingView,
    Platform,
    Alert,
} from 'react-native';
import { useNavigation, useRoute } from '@react-navigation/native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { RootNavigationProp, GroupChatRouteProp } from '../../types/navigation';
import { Label } from '../../components/atoms/Label';
import { MessageBubble, ChatMessage, MessageAttachment } from '../../components/molecules/MessageBubble';
import { listVirtualizationConfig } from '../../constants/list';
import { colors, spacing, radii } from '../../theme';

const INITIAL_GROUP_MESSAGES: ChatMessage[] = [
    {
        id: 'grp_msg_3',
        time: '11:15',
        isMine: false,
        authorName: 'Pedro Lima',
        attachment: {
            name: 'wireframes.pdf',
            size: '1,8 MB',
            type: 'application/pdf',
        },
    },
    {
        id: 'grp_msg_2',
        text: 'Vou atualizar o documento.',
        time: '11:14',
        isMine: true,
    },
    {
        id: 'grp_msg_1',
        text: 'Layout aprovado.',
        time: '11:12',
        authorName: 'Pedro Lima',
        isMine: false,
    },
];

export const GroupChatScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();
    const route = useRoute<GroupChatRouteProp>();
    const { groupName, memberCount } = route.params;

    const [messages, setMessages] = useState<ChatMessage[]>(INITIAL_GROUP_MESSAGES);
    const [inputText, setInputText] = useState('');

    const handleSendMessage = () => {
        const trimmed = inputText.trim();
        if (!trimmed) return;

        const newMessage: ChatMessage = {
            id: `grp_msg_${Date.now()}`,
            text: trimmed,
            time: 'Agora',
            isMine: true,
        };

        setMessages((prev) => [newMessage, ...prev]);
        setInputText('');
    };

    const handleOpenAttachment = useCallback((attachment: MessageAttachment) => {
        navigation.navigate('FileViewer', {
            fileName: attachment.name,
            fileSize: attachment.size,
            isIncoming: true,
        });
    }, [navigation]);

    const renderItem = useCallback(
        ({ item }: { item: ChatMessage }) => (
            <MessageBubble message={item} onOpenAttachment={handleOpenAttachment} />
        ),
        [handleOpenAttachment]
    );

    const keyExtractor = useCallback((item: ChatMessage) => item.id, []);

    return (
        <SafeAreaView style={styles.safeArea} edges={['top', 'bottom']}>
            {/* 1. Cabeçalho */}
            <View style={styles.header}>
                <Pressable onPress={() => navigation.goBack()} style={styles.backButton}>
                    <Label variant="title" style={styles.backIcon}>
                        ‹
                    </Label>
                </Pressable>

                <View style={styles.headerTitleBox}>
                    <Label variant="title" style={styles.groupTitle}>
                        {groupName}
                    </Label>
                    <Label variant="caption" style={styles.memberSubtitle}>
                        {memberCount} participantes
                    </Label>
                </View>

                <Pressable
                    onPress={() => Alert.alert('Informações do Grupo', 'Abrirá T14 (Membros e Gestão)')}
                    style={styles.headerAction}
                >
                    <Label variant="caption" style={styles.headerActionText}>
                        Info ›
                    </Label>
                </Pressable>
            </View>

            {/* 2. Banner de Criptografia do Grupo */}
            <View style={styles.securityBanner}>
                <Label variant="caption" style={styles.securityText}>
                    🔒 Grupo protegido de ponta a ponta
                </Label>
            </View>

            {/* 1. Botão Info no Header: */}
            <Pressable
                onPress={() =>
                    navigation.navigate('GroupInfo', {
                        groupId: route.params.groupId,
                        groupName: route.params.groupName,
                        memberCount: route.params.memberCount,
                        isAdmin: true,
                    })
                }
                style={styles.headerAction}
            >
                <Label variant="caption" style={styles.headerActionText}>
                    Info ›
                </Label>
            </Pressable>

            {/* 2. Barra de Informações: */}
            <Pressable
                onPress={() =>
                    navigation.navigate('GroupInfo', {
                        groupId: route.params.groupId,
                        groupName: route.params.groupName,
                        memberCount: route.params.memberCount,
                        isAdmin: true,
                    })
                }
                style={styles.infoBar}
            >
                <Label variant="caption" style={styles.infoBarText}>
                    Informações · {route.params.memberCount} participantes ›
                </Label>
            </Pressable>

            {/* 3. Área de Mensagens Coletivas */}
            <KeyboardAvoidingView
                style={styles.chatArea}
                behavior={Platform.OS === 'ios' ? 'padding' : undefined}
            >
                <FlatList
                    data={messages}
                    renderItem={renderItem}
                    keyExtractor={keyExtractor}
                    inverted
                    contentContainerStyle={styles.listContent}
                    showsVerticalScrollIndicator={false}
                    {...listVirtualizationConfig}
                />

                {/* 4. Barra de Envio */}
                <View style={styles.inputBar}>
                    <Pressable
                        onPress={() =>
                            navigation.navigate('FileViewer', {
                                fileName: 'novo_anexo_grupo.pdf',
                                fileSize: '1,5 MB',
                                isIncoming: false,
                            })
                        }
                        style={styles.attachButton}
                    >
                        <Label variant="title" style={styles.attachIcon}>
                            +
                        </Label>
                    </Pressable>

                    <TextInput
                        placeholder="Mensagem ao grupo..."
                        placeholderTextColor={colors.textSecondary}
                        value={inputText}
                        onChangeText={setInputText}
                        style={styles.input}
                        multiline
                    />

                    <Pressable
                        onPress={handleSendMessage}
                        disabled={!inputText.trim()}
                        style={[
                            styles.sendButton,
                            !inputText.trim() ? styles.sendButtonDisabled : undefined,
                        ]}
                    >
                        <Label variant="body" style={styles.sendButtonText}>
                            ›
                        </Label>
                    </Pressable>
                </View>
            </KeyboardAvoidingView>
        </SafeAreaView>
    );
};

const styles = StyleSheet.create({
    safeArea: {
        flex: 1,
        backgroundColor: colors.background,
    },
    header: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: spacing.lg,
        paddingVertical: spacing.sm,
        backgroundColor: colors.surface,
        borderBottomWidth: 1,
        borderBottomColor: colors.border,
    },
    backButton: {
        paddingRight: spacing.md,
        paddingVertical: spacing.xs,
    },
    backIcon: {
        color: colors.text,
        fontSize: 28,
        lineHeight: 32,
    },
    headerTitleBox: {
        flex: 1,
    },
    groupTitle: {
        fontSize: 18,
        fontWeight: '700',
        color: colors.text,
    },
    memberSubtitle: {
        color: colors.textSecondary,
        fontSize: 12,
    },
    headerAction: {
        paddingHorizontal: spacing.sm,
        paddingVertical: spacing.xs,
    },
    headerActionText: {
        color: colors.primary,
        fontWeight: '600',
    },
    securityBanner: {
        backgroundColor: colors.protection,
        paddingVertical: spacing.xs,
        alignItems: 'center',
    },
    securityText: {
        color: colors.primary,
        fontWeight: '600',
        fontSize: 12,
    },
    infoBar: {
        paddingVertical: spacing.xs,
        alignItems: 'center',
        backgroundColor: colors.surface,
        borderBottomWidth: 1,
        borderBottomColor: colors.border,
    },
    infoBarText: {
        color: colors.primary,
        fontWeight: '500',
    },
    chatArea: {
        flex: 1,
    },
    listContent: {
        paddingHorizontal: spacing.lg,
        paddingVertical: spacing.md,
    },
    inputBar: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: spacing.md,
        paddingVertical: spacing.sm,
        backgroundColor: colors.surface,
        borderTopWidth: 1,
        borderTopColor: colors.border,
        gap: spacing.sm,
    },
    attachButton: {
        width: 40,
        height: 40,
        borderRadius: 20,
        backgroundColor: colors.protection,
        alignItems: 'center',
        justifyContent: 'center',
    },
    attachIcon: {
        color: colors.primary,
        fontSize: 24,
        lineHeight: 28,
    },
    input: {
        flex: 1,
        minHeight: 40,
        maxHeight: 100,
        backgroundColor: colors.background,
        borderRadius: radii.md,
        paddingHorizontal: spacing.md,
        paddingVertical: spacing.xs,
        fontSize: 15,
        color: colors.text,
    },
    sendButton: {
        width: 40,
        height: 40,
        borderRadius: 20,
        backgroundColor: colors.primary,
        alignItems: 'center',
        justifyContent: 'center',
    },
    sendButtonDisabled: {
        backgroundColor: colors.disabled,
    },
    sendButtonText: {
        color: '#FFFFFF',
        fontWeight: 'bold',
        fontSize: 20,
        lineHeight: 22,
    },
});