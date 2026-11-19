// mobile/src/components/molecules/MessageBubble.tsx
import React, { memo } from 'react';
import { View, StyleSheet, Pressable } from 'react-native';
import { Label } from '../atoms/Label';
import { colors, spacing, radii, typography } from '../../theme';

export interface MessageAttachment {
    name: string;
    size: string;
    type: string;
}

export interface ChatMessage {
    id: string;
    text?: string;
    isMine: boolean;
    time: string;
    attachment?: MessageAttachment;
}

export interface MessageBubbleProps {
    message: ChatMessage;
    onOpenAttachment?: (attachment: MessageAttachment) => void;
}

export const MessageBubble = memo<MessageBubbleProps>(({ message, onOpenAttachment }) => {
    const { isMine, text, time, attachment } = message;

    return (
        <View style={[styles.wrapper, isMine ? styles.wrapperMine : styles.wrapperTheir]}>
            <View style={[styles.bubble, isMine ? styles.bubbleMine : styles.bubbleTheir]}>
                {/* Renderiza arquivo anexo se existir */}
                {attachment ? (
                    <View style={styles.attachmentContainer}>
                        <View style={styles.fileHeader}>
                            <View style={styles.fileIconBox}>
                                <Label variant="caption" style={styles.fileIconText}>
                                    PDF
                                </Label>
                            </View>
                            <View style={styles.fileMeta}>
                                <Label variant="body" style={isMine ? styles.textMine : styles.textTheir}>
                                    {attachment.name}
                                </Label>
                                <Label variant="caption" style={isMine ? styles.subtextMine : styles.subtextTheir}>
                                    {attachment.size}
                                </Label>
                            </View>
                        </View>

                        <Pressable
                            onPress={() => onOpenAttachment?.(attachment)}
                            style={styles.openFileButton}
                        >
                            <Label
                                variant="caption"
                                style={isMine ? styles.linkMine : styles.linkTheir}
                            >
                                Abrir arquivo ›
                            </Label>
                        </Pressable>
                    </View>
                ) : null}

                {/* Mensagem de texto normal */}
                {text ? (
                    <Label
                        variant="body"
                        style={[styles.messageText, isMine ? styles.textMine : styles.textTheir]}
                    >
                        {text}
                    </Label>
                ) : null}

                {/* Rodapé com horário de entrega */}
                <Label
                    variant="caption"
                    style={[styles.timeText, isMine ? styles.timeMine : styles.timeTheir]}
                >
                    {time}
                </Label>
            </View>
        </View>
    );
});

MessageBubble.displayName = 'MessageBubble';

const styles = StyleSheet.create({
    wrapper: {
        width: '100%',
        marginVertical: spacing.xs,
        flexDirection: 'row',
    },
    wrapperMine: {
        justifyContent: 'flex-end',
    },
    wrapperTheir: {
        justifyContent: 'flex-start',
    },
    bubble: {
        maxWidth: '82%',
        paddingHorizontal: spacing.md,
        paddingVertical: spacing.sm,
        borderRadius: radii.lg,
    },
    bubbleMine: {
        backgroundColor: colors.primary,
        borderBottomRightRadius: radii.xs,
    },
    bubbleTheir: {
        backgroundColor: colors.surface,
        borderWidth: 1,
        borderColor: colors.border,
        borderBottomLeftRadius: radii.xs,
    },
    messageText: {
        lineHeight: 22,
    },
    textMine: {
        color: '#FFFFFF',
    },
    textTheir: {
        color: colors.text,
    },
    timeText: {
        fontSize: 11,
        marginTop: 4,
        alignSelf: 'flex-end',
    },
    timeMine: {
        color: 'rgba(255, 255, 255, 0.75)',
    },
    timeTheir: {
        color: colors.textSecondary,
    },
    // Estilos de anexo
    attachmentContainer: {
        marginBottom: spacing.xs,
    },
    fileHeader: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: spacing.xs,
    },
    fileIconBox: {
        width: 36,
        height: 36,
        borderRadius: radii.sm,
        backgroundColor: colors.protection,
        alignItems: 'center',
        justifyContent: 'center',
        marginRight: spacing.sm,
    },
    fileIconText: {
        color: colors.primary,
        fontWeight: '700',
        fontSize: 10,
    },
    fileMeta: {
        flex: 1,
    },
    subtextMine: {
        color: 'rgba(255, 255, 255, 0.8)',
        fontSize: typography.caption,
    },
    subtextTheir: {
        color: colors.textSecondary,
        fontSize: typography.caption,
    },
    openFileButton: {
        paddingTop: spacing.xs,
        borderTopWidth: 0.5,
        borderTopColor: 'rgba(255, 255, 255, 0.25)',
    },
    linkMine: {
        color: '#FFFFFF',
        fontWeight: '600',
    },
    linkTheir: {
        color: colors.primary,
        fontWeight: '600',
    },
});