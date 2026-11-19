// mobile/src/screens/ChatScreen.tsx
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
import { RootNavigationProp, ChatRouteProp } from '../../types/navigation';
import { Label } from '../../components/atoms/Label';
import { MessageBubble, ChatMessage, MessageAttachment } from '../../components/molecules/MessageBubble';
import { listVirtualizationConfig } from '../../constants/list';
import { colors, spacing, radii } from '../../theme';

// Histórico inicial baseado no wireframe T06 (Página 08 e 21)
// Em listas invertidas, a mensagem mais recente fica no índice 0
const INITIAL_MESSAGES: ChatMessage[] = [
    {
        id: 'msg_3',
        time: '10:42',
        isMine: false,
        attachment: {
            name: 'projeto.pdf',
            size: '2,4 MB',
            type: 'application/pdf',
        },
    },
    {
        id: 'msg_2',
        text: 'Sim. A parte de privacidade está pronta.',
        time: '10:39',
        isMine: true,
    },
    {
        id: 'msg_1',
        text: 'Oi! Conseguiu revisar o projeto?',
        time: '10:38',
        isMine: false,
    },
];

export const ChatScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();
    const route = useRoute<ChatRouteProp>();
    const { userId, name, username } = route.params;

    const [messages, setMessages] = useState<ChatMessage[]>(INITIAL_MESSAGES);
    const [inputText, setInputText] = useState('');

    // Envio de nova mensagem cifrada localmente
    const handleSendMessage = () => {
        const trimmed = inputText.trim();
        if (!trimmed) return;

        const newMessage: ChatMessage = {
            id: `msg_${Date.now()}`,
            text: trimmed,
            time: 'Agora',
            isMine: true,
        };

        // Adiciona no início do array por conta do FlatList invertido
        setMessages((prev) => [newMessage, ...prev]);
        setInputText('');
    };

    const handleOpenAttachment = useCallback(
        (attachment: MessageAttachment) => {
            navigation.navigate('FileViewer', {
                fileName: attachment.name,
                fileSize: attachment.size,
                isIncoming: true,
            });
        },
        [navigation]
    );

    const renderItem = useCallback(
        ({ item }: { item: ChatMessage }) => (
            <MessageBubble message={item} onOpenAttachment={handleOpenAttachment} />
        ),
        [handleOpenAttachment]
    );

    const keyExtractor = useCallback((item: ChatMessage) => item.id, []);

    return (
        <SafeAreaView style={styles.safeArea} edges={['top', 'bottom']}>
            {/* 1. Cabeçalho Customizado */}
            <View style={styles.header}>
                <Pressable onPress={() => navigation.goBack()} style={styles.backButton}>
                    <Label variant="title" style={styles.backIcon}>
                        ‹
                    </Label>
                </Pressable>

                <View style={styles.headerTitleBox}>
                    <Label variant="title" style={styles.contactName}>
                        {name}
                    </Label>
                    <Label variant="caption" style={styles.headerSubtitle}>
                        {username}
                    </Label>
                </View>

                <Pressable
                    onPress={() =>
                    navigation.navigate('ContactInfo', {
                        userId,
                        name,
                        username,
                    })
                    }
                    style={styles.headerAction}
                >
                    <Label variant="caption" style={styles.headerActionText}>
                    Info ›
                    </Label>
                </Pressable>
            </View>

            {/* 2. Banner de Segurança e Atalho de Verificação (T06 Wireframe) */}
            <View style={styles.securityBanner}>
                <Label variant="caption" style={styles.securityText}>
                    🔒 Protegida de ponta a ponta
                </Label>
            </View>

            <Pressable
                onPress={() =>
                navigation.navigate('VerifyContact', {
                    userId,
                    name,
                    username,
                })
                }
                style={styles.verifyRow}
            >
                <Label variant="caption" style={styles.verifyText}>
                Verificar código de segurança deste contato ›
                </Label>
            </Pressable>

            {/* 3. Área de Mensagens (Inverted FlatList) */}
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

                {/* 4. Barra de Envio de Mensagem */}
                <View style={styles.inputBar}>
                    <Pressable
                        onPress={() =>
                            navigation.navigate('FileViewer', {
                                fileName: 'novo_documento.pdf',
                                fileSize: '1,8 MB',
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
                        placeholder="Mensagem..."
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
    contactName: {
        fontSize: 18,
        fontWeight: '700',
        color: colors.text,
    },
    headerSubtitle: {
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
    verifyRow: {
        paddingVertical: spacing.xs,
        alignItems: 'center',
        backgroundColor: colors.surface,
        borderBottomWidth: 1,
        borderBottomColor: colors.border,
    },
    verifyText: {
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