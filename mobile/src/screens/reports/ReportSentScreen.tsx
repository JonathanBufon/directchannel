// mobile/src/screens/reports/ReportSentScreen.tsx
import React from 'react';
import { View, StyleSheet, Pressable } from 'react-native';
import { useNavigation, useRoute } from '@react-navigation/native';
import { RootNavigationProp, ReportSentRouteProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Label } from '../../components/atoms/Label';
import { Button } from '../../components/atoms/Button';
import { colors, spacing, radii } from '../../theme';

export const ReportSentScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();
    const route = useRoute<ReportSentRouteProp>();
    const { userId, contactName } = route.params;

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
                    Denúncia enviada
                </Label>
            </View>

            {/* Card de Confirmação Segura */}
            <View style={styles.card}>
                <View style={styles.badge}>
                    <Label variant="title" style={styles.badgeText}>
                        RS
                    </Label>
                </View>

                <Label variant="title" style={styles.title}>
                    Recebemos sua denúncia
                </Label>
                <Label variant="body" style={styles.subtitle}>
                    A equipe avaliará o relato enviado. Suas conversas não foram encaminhadas.
                </Label>

                {/* Voltar ao Contato */}
                <Button
                    title="Voltar ao contato"
                    variant="primary"
                    onPress={() => navigation.goBack()}
                    style={styles.actionButton}
                />

                {/* Bloquear Também (Encaminha para T21) */}
                <Pressable
                    onPress={() => navigation.replace('BlockContact', { userId, name: contactName })}
                    style={styles.blockLink}
                >
                    <Label variant="body" style={styles.blockText}>
                        Bloquear também ›
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
        lineHeight: 20,
        marginBottom: spacing.xl,
    },
    actionButton: {
        width: '100%',
        marginBottom: spacing.md,
    },
    blockLink: {
        paddingVertical: spacing.sm,
    },
    blockText: {
        color: colors.error,
        fontWeight: '600',
    },
});