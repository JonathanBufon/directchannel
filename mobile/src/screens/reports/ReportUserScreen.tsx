// mobile/src/screens/reports/ReportUserScreen.tsx
import React, { useState } from 'react';
import { View, StyleSheet, Pressable, TextInput, Alert } from 'react-native';
import { useNavigation, useRoute } from '@react-navigation/native';
import { RootNavigationProp, ReportUserRouteProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Label } from '../../components/atoms/Label';
import { Button } from '../../components/atoms/Button';
import { colors, spacing, radii } from '../../theme';

// Motivos canônicos conforme wireframes T10 e A04
const REASONS = [
    'Assédio ou contato indesejado',
    'Spam ou mensagens repetitivas',
    'Golpe, fraude ou conduta suspeita',
];

export const ReportUserScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();
    const route = useRoute<ReportUserRouteProp>();
    const { userId, username } = route.params;

    const [selectedReason, setSelectedReason] = useState(REASONS[0]);
    const [description, setDescription] = useState('');
    const [isLoading, setIsLoading] = useState(false);

    const handleSubmitReport = async () => {
        if (!selectedReason.trim()) {
            Alert.alert('Motivo Obrigatório', 'Selecione ou informe o motivo da denúncia.');
            return;
        }

        setIsLoading(true);
        try {
            // Simulação do envio do relato operacional (sem anexos de conversa)
            await new Promise((resolve) => setTimeout(resolve, 1000));

            // Substitui a rota atual pela tela de confirmação T23
            navigation.replace('ReportSent', {
                userId,
                contactName: username,
            });
        } catch {
            Alert.alert('Erro', 'Não foi possível registrar a denúncia. Tente novamente.');
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
                    Denunciar usuário
                </Label>
            </View>

            {/* Identificação do Usuário Denunciado */}
            <View style={styles.userBadge}>
                <Label variant="body" style={styles.userLabel}>
                    Usuário: <Label variant="body" style={styles.userHighlight}>{username}</Label>
                </Label>
            </View>

            {/* Seleção do Motivo Obrigatório (RF20) */}
            <View style={styles.fieldBox}>
                <Label variant="body" style={styles.fieldLabel}>
                    Motivo (obrigatório)
                </Label>

                {REASONS.map((reason) => {
                    const isSelected = selectedReason === reason;
                    return (
                        <Pressable
                            key={reason}
                            onPress={() => setSelectedReason(reason)}
                            style={[
                                styles.reasonCard,
                                isSelected ? styles.reasonCardSelected : undefined,
                            ]}
                        >
                            <View
                                style={[
                                    styles.radio,
                                    isSelected ? styles.radioSelected : undefined,
                                ]}
                            >
                                {isSelected ? <View style={styles.radioInner} /> : null}
                            </View>
                            <Label variant="body" style={styles.reasonText}>
                                {reason}
                            </Label>
                        </Pressable>
                    );
                })}
            </View>

            {/* Descrição dos Fatos (Multilinha) */}
            <View style={styles.fieldBox}>
                <Label variant="body" style={styles.fieldLabel}>
                    Descrição do comportamento
                </Label>
                <TextInput
                    placeholder="Descreva o que aconteceu de forma objetiva..."
                    placeholderTextColor={colors.textSecondary}
                    value={description}
                    onChangeText={setDescription}
                    multiline
                    numberOfLines={4}
                    style={styles.textArea}
                />
            </View>

            {/* Aviso de Privacidade Estrito do DirectChannel */}
            <View style={styles.privacyNotice}>
                <Label variant="caption" style={styles.privacyText}>
                    🔒 Não inclua mensagens privadas ou arquivos na descrição. O operador da plataforma não possui chaves de leitura do seu histórico.
                </Label>
            </View>

            {/* Botão de Envio */}
            <Button
                title="Enviar denúncia"
                variant="primary"
                isLoading={isLoading}
                onPress={handleSubmitReport}
                style={styles.submitButton}
            />

            {/* Cancelar */}
            <Pressable onPress={() => navigation.goBack()} style={styles.cancelLink}>
                <Label variant="body" style={styles.cancelText}>
                    Cancelar ›
                </Label>
            </Pressable>
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
    userBadge: {
        backgroundColor: colors.surface,
        padding: spacing.md,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: colors.border,
        marginBottom: spacing.lg,
    },
    userLabel: {
        color: colors.textSecondary,
    },
    userHighlight: {
        fontWeight: '700',
        color: colors.text,
    },
    fieldBox: {
        marginBottom: spacing.lg,
    },
    fieldLabel: {
        fontWeight: '600',
        marginBottom: spacing.xs,
        color: colors.text,
    },
    reasonCard: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: colors.surface,
        padding: spacing.md,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: colors.border,
        marginBottom: spacing.xs,
    },
    reasonCardSelected: {
        borderColor: colors.primary,
        backgroundColor: colors.protection,
    },
    radio: {
        width: 20,
        height: 20,
        borderRadius: 10,
        borderWidth: 2,
        borderColor: colors.border,
        alignItems: 'center',
        justifyContent: 'center',
        marginRight: spacing.md,
    },
    radioSelected: {
        borderColor: colors.primary,
    },
    radioInner: {
        width: 10,
        height: 10,
        borderRadius: 5,
        backgroundColor: colors.primary,
    },
    reasonText: {
        flex: 1,
        fontWeight: '500',
        color: colors.text,
    },
    textArea: {
        backgroundColor: colors.surface,
        borderWidth: 1,
        borderColor: colors.border,
        borderRadius: radii.md,
        padding: spacing.md,
        minHeight: 100,
        textAlignVertical: 'top',
        fontSize: 15,
        color: colors.text,
    },
    privacyNotice: {
        backgroundColor: colors.protection,
        padding: spacing.md,
        borderRadius: radii.sm,
        marginBottom: spacing.xl,
    },
    privacyText: {
        color: colors.primary,
        lineHeight: 18,
        textAlign: 'center',
    },
    submitButton: {
        width: '100%',
        marginBottom: spacing.md,
    },
    cancelLink: {
        alignItems: 'center',
        paddingVertical: spacing.sm,
        marginBottom: spacing.xl,
    },
    cancelText: {
        color: colors.text,
        fontWeight: '500',
    },
});