// mobile/src/screens/admin/AdminReviewReportScreen.tsx
import React, { useState } from 'react';
import { View, StyleSheet, Pressable, Alert } from 'react-native';
import { useNavigation, useRoute } from '@react-navigation/native';
import { RootNavigationProp, AdminReviewReportRouteProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Label } from '../../components/atoms/Label';
import { Input } from '../../components/atoms/Input';
import { Button } from '../../components/atoms/Button';
import { colors, spacing, radii } from '../../theme';

export const AdminReviewReportScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();
    const route = useRoute<AdminReviewReportRouteProp>();
    const { reportId, reportedUser, category, status: initialStatus } = route.params;

    const [decision, setDecision] = useState('Analisado - registrar justificativa');
    const [isLoading, setIsLoading] = useState(false);

    const handleCompleteReview = async () => {
        if (!decision.trim()) {
            Alert.alert('Justificativa Obrigatória', 'Registre uma decisão antes de concluir.');
            return;
        }

        setIsLoading(true);
        try {
            await new Promise((resolve) => setTimeout(resolve, 800));

            Alert.alert(
                'Análise Concluída',
                `O ${reportId} foi arquivado com a justificativa registrada. O relato foi finalizado sem expor mensagens privadas.`,
                [{ text: 'OK', onPress: () => navigation.goBack() }]
            );
        } catch {
            Alert.alert('Erro', 'Não foi possível concluir a análise.');
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
                    Analisar denúncia
                </Label>
            </View>

            {/* Identificação do Relato */}
            <View style={styles.card}>
                <View style={styles.statusRow}>
                    <Label variant="title" style={styles.reportCode}>
                        {reportId}
                    </Label>
                    <View style={styles.statusBadge}>
                        <Label variant="caption" style={styles.statusText}>
                            {initialStatus}
                        </Label>
                    </View>
                </View>

                <View style={styles.fieldBox}>
                    <Label variant="caption" style={styles.fieldLabel}>
                        Usuário denunciado
                    </Label>
                    <View style={styles.readOnlyBox}>
                        <Label variant="body" style={styles.readOnlyText}>
                            {reportedUser}
                        </Label>
                    </View>
                </View>

                <View style={styles.fieldBox}>
                    <Label variant="caption" style={styles.fieldLabel}>
                        Motivo
                    </Label>
                    <View style={styles.readOnlyBox}>
                        <Label variant="body" style={styles.readOnlyText}>
                            {category}
                        </Label>
                    </View>
                </View>

                <View style={styles.fieldBox}>
                    <Label variant="caption" style={styles.fieldLabel}>
                        Decisão
                    </Label>
                    <Input
                        value={decision}
                        onChangeText={setDecision}
                        style={styles.decisionInput}
                    />
                </View>

                {/* Botão de Conclusão (RF24) */}
                <Button
                    title="Concluir análise"
                    variant="primary"
                    isLoading={isLoading}
                    onPress={handleCompleteReview}
                    style={styles.actionButton}
                />

                {/* Atalho de Governança para A03 */}
                <Pressable
                    style={styles.manageAccountLink}
                    onPress={() =>
                        navigation.navigate('AdminManageAccount', {
                            userId: 'usr_reported',
                            name: reportedUser.replace('@', ''),
                            username: reportedUser,
                            initialStatus: 'Ativa',
                        })
                    }
                >
                    <Label variant="body" style={styles.manageAccountText}>
                        Gerenciar conta denunciada ›
                    </Label>
                </Pressable>
            </View>

            {/* Aviso de Privacidade Estrito (RNF14) */}
            <View style={styles.securityBox}>
                <Label variant="caption" style={styles.securityText}>
                    🔒 Somente o relato é consultado. Não há acesso às conversas ou arquivos privados.
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
    card: {
        backgroundColor: colors.surface,
        padding: spacing.lg,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: colors.border,
        marginBottom: spacing.lg,
    },
    statusRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: spacing.md,
    },
    reportCode: {
        fontWeight: '700',
        color: colors.text,
    },
    statusBadge: {
        backgroundColor: '#FDEDEC',
        paddingHorizontal: spacing.sm,
        paddingVertical: 4,
        borderRadius: radii.sm,
    },
    statusText: {
        color: colors.error,
        fontWeight: '700',
        fontSize: 11,
    },
    fieldBox: {
        marginBottom: spacing.md,
    },
    fieldLabel: {
        fontWeight: '600',
        color: colors.textSecondary,
        marginBottom: spacing.xs,
    },
    readOnlyBox: {
        backgroundColor: colors.background,
        padding: spacing.md,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: colors.border,
    },
    readOnlyText: {
        color: colors.text,
        fontWeight: '500',
    },
    decisionInput: {
        backgroundColor: colors.background,
    },
    actionButton: {
        marginTop: spacing.sm,
        marginBottom: spacing.md,
    },
    manageAccountLink: {
        alignItems: 'center',
        paddingVertical: spacing.xs,
    },
    manageAccountText: {
        color: colors.primary,
        fontWeight: '600',
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