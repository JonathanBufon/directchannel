// mobile/src/screens/conversations/BlockContactScreen.tsx
import React, { useState } from 'react';
import { View, StyleSheet, Pressable, Alert } from 'react-native';
import { useNavigation, useRoute } from '@react-navigation/native';
import { RootNavigationProp, BlockContactRouteProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Label } from '../../components/atoms/Label';
import { Button } from '../../components/atoms/Button';
import { colors, spacing, radii } from '../../theme';

export const BlockContactScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();
    const route = useRoute<BlockContactRouteProp>();
    const { name } = route.params;

    const [isLoading, setIsLoading] = useState(false);

    const handleConfirmBlock = async () => {
        setIsLoading(true);
        try {
            await new Promise((resolve) => setTimeout(resolve, 800));

            Alert.alert(
                'Contato Bloqueado',
                `${name} foi bloqueado com sucesso. Você não receberá novas mensagens diretas desta pessoa.`,
                [
                    {
                        text: 'OK',
                        onPress: () => {
                            // Navega diretamente para a lista de bloqueados substituindo a tela de confirmação
                            navigation.replace('BlockedContacts');
                        },
                    },
                ]
            );
        } catch {
            Alert.alert('Erro', 'Não foi possível bloquear o contato.');
        } finally {
            setIsLoading(false);
        }
    };

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
                    Bloquear contato
                </Label>
            </View>

            {/* Card de Confirmação */}
            <View style={styles.card}>
                <View style={styles.badge}>
                    <Label variant="title" style={styles.badgeText}>
                        BM
                    </Label>
                </View>

                <Label variant="title" style={styles.title}>
                    Bloquear {name}?
                </Label>
                <Label variant="body" style={styles.subtitle}>
                    Você deixará de receber novas mensagens diretas deste contato.
                </Label>

                <View style={styles.ruleNotice}>
                    <Label variant="caption" style={styles.ruleText}>
                        O bloqueio não apaga cópias já recebidas no seu dispositivo nem remove membros de grupos em comum.
                    </Label>
                </View>

                {/* Botão de Confirmação */}
                <Button
                    title="Confirmar bloqueio"
                    variant="danger"
                    isLoading={isLoading}
                    onPress={handleConfirmBlock}
                    style={styles.confirmButton}
                />

                {/* Cancelar */}
                <Pressable onPress={() => navigation.goBack()} style={styles.cancelLink}>
                    <Label variant="body" style={styles.cancelText}>
                        Cancelar ›
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
        backgroundColor: '#FDEDEC',
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: spacing.md,
    },
    badgeText: {
        fontSize: 24,
        color: colors.error,
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
    ruleNotice: {
        backgroundColor: colors.protection,
        padding: spacing.md,
        borderRadius: radii.sm,
        marginBottom: spacing.xl,
        width: '100%',
    },
    ruleText: {
        color: colors.primary,
        textAlign: 'center',
        lineHeight: 18,
    },
    confirmButton: {
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