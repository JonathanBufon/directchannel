// mobile/src/screens/groups/LeaveGroupScreen.tsx
import React, { useState } from 'react';
import { View, StyleSheet, Pressable, Alert } from 'react-native';
import { useNavigation, useRoute } from '@react-navigation/native';
import { RootNavigationProp, LeaveGroupRouteProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Label } from '../../components/atoms/Label';
import { Button } from '../../components/atoms/Button';
import { colors, spacing, radii } from '../../theme';

export const LeaveGroupScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();
    const route = useRoute<LeaveGroupRouteProp>();
    const { groupName } = route.params;

    const [isLoading, setIsLoading] = useState(false);

    const handleConfirmLeave = async () => {
        setIsLoading(true);
        try {
            // Simulação da remoção do dispositivo das chaves da equipe
            await new Promise((resolve) => setTimeout(resolve, 1000));

            Alert.alert(
                'Saída Confirmada',
                `Você saiu de ${groupName}. As chaves das próximas mensagens foram atualizadas.`,
                [
                    {
                        text: 'OK',
                        onPress: () => {
                            // Retorna para a área principal do app
                            navigation.navigate('Main');
                        },
                    },
                ]
            );
        } catch {
            Alert.alert('Erro', 'Não foi possível concluir a saída do grupo.');
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
                    Sair do grupo
                </Label>
            </View>

            {/* Card de Confirmação */}
            <View style={styles.confirmCard}>
                <View style={styles.badge}>
                    <Label variant="title" style={styles.badgeText}>
                        SD
                    </Label>
                </View>

                <Label variant="title" style={styles.title}>
                    Sair deste grupo?
                </Label>
                <Label variant="body" style={styles.warningText}>
                    Você deixará de receber novas mensagens de {groupName}.
                </Label>

                <View style={styles.ruleNotice}>
                    <Label variant="caption" style={styles.ruleText}>
                        Se você for o único responsável, outro administrador deverá ser nomeado. As chaves de criptografia das próximas mensagens serão rotacionadas.
                    </Label>
                </View>

                {/* Botão de Confirmação */}
                <Button
                    title="Confirmar saída"
                    variant="danger"
                    isLoading={isLoading}
                    onPress={handleConfirmLeave}
                    style={styles.confirmButton}
                />

                {/* Cancelar / Permanecer */}
                <Pressable onPress={() => navigation.goBack()} style={styles.cancelLink}>
                    <Label variant="body" style={styles.cancelText}>
                        Continuar no grupo ›
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
    confirmCard: {
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
        backgroundColor: '#FADBD8',
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: spacing.md,
    },
    badgeText: {
        color: colors.error,
        fontWeight: '700',
        fontSize: 24,
    },
    title: {
        fontWeight: '700',
        color: colors.text,
        marginBottom: spacing.xs,
        textAlign: 'center',
    },
    warningText: {
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