// mobile/src/screens/admin/AdminManageAccountScreen.tsx
import React, { useState } from 'react';
import { View, StyleSheet, Pressable, Alert } from 'react-native';
import { useNavigation, useRoute } from '@react-navigation/native';
import { RootNavigationProp, AdminManageAccountRouteProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Label } from '../../components/atoms/Label';
import { Input } from '../../components/atoms/Input';
import { Button } from '../../components/atoms/Button';
import { colors, spacing, radii } from '../../theme';

export const AdminManageAccountScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();
    const route = useRoute<AdminManageAccountRouteProp>();
    const { name, username, initialStatus } = route.params;

    const [status, setStatus] = useState<'Ativa' | 'Bloqueada'>(initialStatus);
    const [reason, setReason] = useState('Violação das regras de uso');
    const [isLoading, setIsLoading] = useState(false);

    const isBlocked = status === 'Bloqueada';
    const initials = name.slice(0, 2).toUpperCase();

    const handleToggleAccountStatus = async () => {
        if (!reason.trim()) {
            Alert.alert('Justificativa Obrigatória', 'Informe o motivo administrativo da alteração.');
            return;
        }

        setIsLoading(true);
        try {
            // Simulação do registro de auditoria administrativa
            await new Promise((resolve) => setTimeout(resolve, 800));

            const nextStatus = isBlocked ? 'Ativa' : 'Bloqueada';
            setStatus(nextStatus);

            Alert.alert(
                'Ação Administrativa Concluída',
                `A conta de ${name} agora está marcada como ${nextStatus}. Justificativa e autoria foram registradas no log de auditoria.`,
                [{ text: 'OK', onPress: () => navigation.goBack() }]
            );
        } catch {
            Alert.alert('Erro', 'Não foi possível alterar o estado da conta.');
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
                    Gerenciar conta
                </Label>
            </View>

            {/* Card da Conta */}
            <View style={styles.profileCard}>
                <View style={styles.avatar}>
                    <Label variant="title" style={styles.avatarText}>
                        {initials}
                    </Label>
                </View>

                <Label variant="title" style={styles.name}>
                    {name}
                </Label>
                <Label variant="caption" style={styles.username}>
                    {username}
                </Label>
            </View>

            {/* Formulário de Auditoria e Decisão */}
            <View style={styles.formCard}>
                <View style={styles.fieldBox}>
                    <Label variant="body" style={styles.fieldLabel}>
                        Estado atual
                    </Label>
                    <View style={[styles.statusBox, isBlocked ? styles.boxBlocked : styles.boxActive]}>
                        <Label variant="body" style={isBlocked ? styles.statusBlocked : styles.statusActive}>
                            {status}
                        </Label>
                    </View>
                </View>

                <View style={styles.fieldBox}>
                    <Label variant="body" style={styles.fieldLabel}>
                        Motivo administrativo
                    </Label>
                    <Input
                        value={reason}
                        onChangeText={setReason}
                        placeholder="Ex: Violação dos termos de conduta"
                        style={styles.reasonInput}
                    />
                </View>

                {/* Botão de Decisão Alternante (RF23) */}
                <Button
                    title={isBlocked ? 'Desbloquear conta' : 'Confirmar bloqueio'}
                    variant={isBlocked ? 'primary' : 'danger'}
                    isLoading={isLoading}
                    onPress={handleToggleAccountStatus}
                    style={styles.actionButton}
                />
            </View>

            {/* Aviso de Privacidade Estrito (RNF14) */}
            <View style={styles.securityBox}>
                <Label variant="caption" style={styles.securityText}>
                    🔒 O administrador não acessa conversas privadas. Todas as mensagens permanecem cifradas de ponta a ponta.
                </Label>
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
        width: 64,
        height: 64,
        borderRadius: 32,
        backgroundColor: colors.protection,
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: spacing.md,
    },
    avatarText: {
        fontSize: 24,
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
    },
    formCard: {
        backgroundColor: colors.surface,
        padding: spacing.lg,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: colors.border,
        marginBottom: spacing.lg,
    },
    fieldBox: {
        marginBottom: spacing.md,
    },
    fieldLabel: {
        fontWeight: '600',
        color: colors.text,
        marginBottom: spacing.xs,
    },
    statusBox: {
        padding: spacing.md,
        borderRadius: radii.md,
        borderWidth: 1,
    },
    boxActive: {
        backgroundColor: '#E8F8F5',
        borderColor: '#A3E4D7',
    },
    boxBlocked: {
        backgroundColor: '#FDEDEC',
        borderColor: '#FADBD8',
    },
    statusActive: {
        color: colors.success,
        fontWeight: '700',
    },
    statusBlocked: {
        color: colors.error,
        fontWeight: '700',
    },
    reasonInput: {
        backgroundColor: colors.background,
    },
    actionButton: {
        marginTop: spacing.sm,
    },
    securityBox: {
        backgroundColor: colors.protection,
        padding: spacing.md,
        borderRadius: radii.sm,
        alignItems: 'center',
    },
    securityText: {
        color: colors.primary,
        textAlign: 'center',
        lineHeight: 18,
    },
});