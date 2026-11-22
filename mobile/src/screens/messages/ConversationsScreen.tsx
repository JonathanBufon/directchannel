// mobile/src/screens/ConversationsScreen.tsx
import React from 'react';
import { View, StyleSheet } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { RootNavigationProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Label } from '../../components/atoms/Label';
import { Button } from '../../components/atoms/Button';
import { colors, spacing, radii } from '../../theme';

export const ConversationsScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();

    return (
        <ScreenTemplate
            title="Conversas"
            subtitle="Criptografia de ponta a ponta sempre ativa"
        >
            <View style={styles.securityBadge}>
                <Label variant="caption" style={styles.securityText}>
                    🔒 Mensagens protegidas por chaves locais no dispositivo
                </Label>
            </View>

            <View style={styles.emptyContainer}>
                <Label variant="body" style={styles.emptyTitle}>
                    Nenhuma conversa ativa
                </Label>
                <Label variant="caption" style={styles.emptySubtitle}>
                    Inicie uma conversa privada pesquisando por um identificador público.
                </Label>

                <Button
                    title="Nova conversa"
                    variant="primary"
                    onPress={() => navigation.navigate('SearchUsers')}
                    style={styles.actionButton}
                />
            </View>
        </ScreenTemplate>
    );
};

const styles = StyleSheet.create({
    securityBadge: {
        backgroundColor: colors.protection,
        padding: spacing.md,
        borderRadius: radii.sm,
        marginBottom: spacing.lg,
    },
    securityText: {
        color: colors.primary,
        fontWeight: '600',
        textAlign: 'center',
    },
    emptyContainer: {
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
        paddingVertical: spacing.xl * 2,
    },
    emptyTitle: {
        fontWeight: '600',
        marginBottom: spacing.xs,
    },
    emptySubtitle: {
        textAlign: 'center',
        marginBottom: spacing.lg,
        paddingHorizontal: spacing.md,
    },
    actionButton: {
        width: '100%',
    },
});