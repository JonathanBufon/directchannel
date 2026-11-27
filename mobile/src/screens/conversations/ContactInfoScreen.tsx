// mobile/src/screens/conversations/ContactInfoScreen.tsx
import React from 'react';
import { View, StyleSheet, Pressable, Alert } from 'react-native';
import { useNavigation, useRoute } from '@react-navigation/native';
import { RootNavigationProp, ContactInfoRouteProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Label } from '../../components/atoms/Label';
import { Button } from '../../components/atoms/Button';
import { colors, spacing, radii, dimensions } from '../../theme';

export const ContactInfoScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();
    const route = useRoute<ContactInfoRouteProp>();
    const { userId, name, username } = route.params;

    const initial = name ? name.charAt(0).toUpperCase() : '?';

    return (
        <ScreenTemplate scrollable>
            {/* Cabeçalho */}
            <View style={styles.header}>
                <Pressable onPress={() => navigation.goBack()} style={styles.backButton}>
                    <Label variant="title" style={styles.backIcon}>
                        ‹
                    </Label>
                </Pressable>
                <Label variant="title" style={styles.headerTitle}>
                    Informações do contato
                </Label>
            </View>

            {/* Card Principal do Contato */}
            <View style={styles.profileCard}>
                <View style={styles.avatar}>
                    <Label variant="title" style={styles.avatarText}>
                        {initial}
                    </Label>
                </View>

                <Label variant="title" style={styles.name}>
                    {name}
                </Label>
                <Label variant="caption" style={styles.username}>
                    {username}
                </Label>

                {/* Botão de Retornar ao Chat */}
                <Button
                    title="Enviar mensagem"
                    variant="primary"
                    onPress={() => navigation.goBack()}
                    style={styles.messageButton}
                />
            </View>

            {/* Lista de Ações de Privacidade e Segurança */}
            <View style={styles.actionsList}>
                {/* Ir para Verificação de Segurança (T09) */}
                <Pressable
                    style={styles.actionRow}
                    onPress={() => navigation.navigate('VerifyContact', { userId, name, username })}
                >
                    <View style={styles.actionTextContainer}>
                        <Label variant="body" style={styles.actionTitle}>
                            Verificar código de segurança
                        </Label>
                        <Label variant="caption" style={styles.actionSubtitle}>
                            Compare a chave pública para evitar espionagem
                        </Label>
                    </View>
                    <Label variant="title" style={styles.chevron}>
                        ›
                    </Label>
                </Pressable>

                {/* Bloquear Contato (T21) */}
                <Pressable
                    style={styles.actionRow}
                    onPress={() => navigation.navigate('BlockContact', { userId, name })}
                >
                    <View style={styles.actionTextContainer}>
                        <Label variant="body" style={styles.actionTitle}>
                            Bloquear contato
                        </Label>
                        <Label variant="caption" style={styles.actionSubtitle}>
                            Impede novas mensagens e chamadas deste usuário
                        </Label>
                    </View>
                    <Label variant="title" style={styles.chevron}>
                        ›
                    </Label>
                </Pressable>

                {/* Denunciar Usuário (T10) */}
                <Pressable
                    style={[styles.actionRow, styles.lastActionRow]}
                    onPress={() => navigation.navigate('ReportUser', { userId, username })}
                >
                    <View style={styles.actionTextContainer}>
                        <Label variant="body" style={styles.dangerTitle}>
                            Denunciar usuário
                        </Label>
                        <Label variant="caption" style={styles.actionSubtitle}>
                            Relatar assédio, spam ou contato indesejado
                        </Label>
                    </View>
                    <Label variant="title" style={styles.chevron}>
                        ›
                    </Label>
                </Pressable>
            </View>
        </ScreenTemplate>
    );
};

const styles = StyleSheet.create({
    header: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: spacing.lg,
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
    headerTitle: {
        fontWeight: '700',
        color: colors.text,
    },
    profileCard: {
        backgroundColor: colors.surface,
        padding: spacing.xl,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: colors.border,
        alignItems: 'center',
        marginBottom: spacing.lg,
    },
    avatar: {
        width: 72,
        height: 72,
        borderRadius: 36,
        backgroundColor: colors.protection,
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: spacing.md,
    },
    avatarText: {
        fontSize: 32,
        color: colors.primary,
        fontWeight: '700',
    },
    name: {
        fontWeight: '700',
        color: colors.text,
    },
    username: {
        color: colors.textSecondary,
        marginTop: spacing.xs,
        marginBottom: spacing.lg,
    },
    messageButton: {
        width: '100%',
    },
    actionsList: {
        backgroundColor: colors.surface,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: colors.border,
        overflow: 'hidden',
    },
    actionRow: {
        flexDirection: 'row',
        alignItems: 'center',
        padding: spacing.md,
        borderBottomWidth: 1,
        borderBottomColor: colors.border,
        minHeight: dimensions.minTouchArea,
    },
    lastActionRow: {
        borderBottomWidth: 0,
    },
    actionTextContainer: {
        flex: 1,
    },
    actionTitle: {
        fontWeight: '600',
        color: colors.text,
    },
    dangerTitle: {
        fontWeight: '600',
        color: colors.error,
    },
    actionSubtitle: {
        color: colors.textSecondary,
        marginTop: 2,
    },
    chevron: {
        color: colors.textSecondary,
        fontSize: 22,
        marginLeft: spacing.sm,
    },
});