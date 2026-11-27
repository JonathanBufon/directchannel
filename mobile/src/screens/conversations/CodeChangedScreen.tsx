// mobile/src/screens/conversations/CodeChangedScreen.tsx
import React from 'react';
import { View, StyleSheet, Pressable } from 'react-native';
import { useNavigation, useRoute } from '@react-navigation/native';
import { RootNavigationProp, CodeChangedRouteProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Label } from '../../components/atoms/Label';
import { Button } from '../../components/atoms/Button';
import { colors, spacing, radii } from '../../theme';

export const CodeChangedScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();
    const route = useRoute<CodeChangedRouteProp>();
    const { userId, name, username } = route.params;

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
                    Código alterado
                </Label>
            </View>

            {/* Card de Alerta de Mudança de Identidade */}
            <View style={styles.card}>
                <View style={styles.badge}>
                    <Label variant="title" style={styles.badgeText}>
                        CS
                    </Label>
                </View>

                <Label variant="title" style={styles.title}>
                    Confirme seu contato
                </Label>
                <Label variant="body" style={styles.subtitle}>
                    O código de segurança de {name} mudou.
                </Label>

                <View style={styles.securityBox}>
                    <Label variant="caption" style={styles.securityText}>
                        Confira a identidade antes de enviar novas mensagens. Isso pode indicar que {name} reinstalou o aplicativo ou trocou de dispositivo.
                    </Label>
                </View>

                {/* Botão para Verificar Nova Chave (T09) */}
                <Button
                    title="Verificar novo código"
                    variant="primary"
                    onPress={() => navigation.replace('VerifyContact', { userId, name, username })}
                    style={styles.actionButton}
                />

                {/* Voltar sem enviar */}
                <Pressable onPress={() => navigation.goBack()} style={styles.cancelLink}>
                    <Label variant="body" style={styles.cancelText}>
                        Voltar sem enviar ›
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
    card: {
        backgroundColor: colors.surface,
        padding: spacing.xl,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: colors.border,
        alignItems: 'center',
        marginTop: spacing.md,
    },
    badge: {
        width: 64,
        height: 64,
        borderRadius: 32,
        backgroundColor: colors.protection,
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: spacing.md,
    },
    badgeText: {
        fontSize: 24,
        color: colors.primary,
        fontWeight: '700',
    },
    title: {
        fontWeight: '700',
        color: colors.text,
        marginBottom: spacing.xs,
        textAlign: 'center',
    },
    subtitle: {
        color: colors.textSecondary,
        textAlign: 'center',
        marginBottom: spacing.lg,
    },
    securityBox: {
        backgroundColor: colors.protection,
        padding: spacing.md,
        borderRadius: radii.sm,
        marginBottom: spacing.xl,
        width: '100%',
    },
    securityText: {
        color: colors.primary,
        textAlign: 'center',
        lineHeight: 18,
    },
    actionButton: {
        width: '100%',
        marginBottom: spacing.md,
    },
    cancelLink: {
        paddingVertical: spacing.sm,
    },
    cancelText: {
        color: colors.text,
        fontWeight: '500',
    },
});