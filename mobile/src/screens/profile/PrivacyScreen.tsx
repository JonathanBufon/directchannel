// mobile/src/screens/profile/PrivacyScreen.tsx
import React, { useState, useEffect } from 'react';
import { View, StyleSheet, Pressable, Switch } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { RootNavigationProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Label } from '../../components/atoms/Label';
import { colors, spacing, radii, dimensions } from '../../theme';
import { savePreference, getPreference } from '../../services/storage';

export const PrivacyScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();

    // Estados dos switches de privacidade (iniciam desativados por padrão - RF26)
    const [notificationPreview, setNotificationPreview] = useState(false);
    const [readReceipts, setReadReceipts] = useState(false);

    // Carrega preferências persistidas no AsyncStorage
    useEffect(() => {
        async function loadPreferences() {
            const preview = await getPreference<boolean>('notification_preview', false);
            const receipts = await getPreference<boolean>('read_receipts', false);
            setNotificationPreview(preview);
            setReadReceipts(receipts);
        }
        loadPreferences();
    }, []);

    // Alterna e persiste a prévia nas notificações
    const handleTogglePreview = async (value: boolean) => {
        setNotificationPreview(value);
        await savePreference('notification_preview', value);
    };

    // Alterna e persiste as confirmações de leitura
    const handleToggleReceipts = async (value: boolean) => {
        setReadReceipts(value);
        await savePreference('read_receipts', value);
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
                    Privacidade
                </Label>
            </View>

            {/* Indicador Permanente de Criptografia (RF26: Não pode ser desativada) */}
            <View style={styles.encryptionCard}>
                <Label variant="body" style={styles.encryptionText}>
                    🔒 Criptografia: sempre ativa
                </Label>
            </View>

            {/* Tabela de Preferências com Switches */}
            <View style={styles.preferencesCard}>
                {/* Switch 1: Prévia nas notificações */}
                <View style={styles.switchRow}>
                    <View style={styles.switchTextContainer}>
                        <Label variant="body" style={styles.switchTitle}>
                            Prévia nas notificações
                        </Label>
                        <Label variant="caption" style={styles.switchSubtitle}>
                            Oculta o remetente e o conteúdo na tela de bloqueio
                        </Label>
                    </View>
                    <Switch
                        value={notificationPreview}
                        onValueChange={handleTogglePreview}
                        trackColor={{ false: colors.border, true: colors.primary }}
                        thumbColor={colors.surface}
                    />
                </View>

                {/* Switch 2: Confirmações de leitura */}
                <View style={styles.switchRow}>
                    <View style={styles.switchTextContainer}>
                        <Label variant="body" style={styles.switchTitle}>
                            Confirmações de leitura
                        </Label>
                        <Label variant="caption" style={styles.switchSubtitle}>
                            Indica quando você visualizou as mensagens recebidas
                        </Label>
                    </View>
                    <Switch
                        value={readReceipts}
                        onValueChange={handleToggleReceipts}
                        trackColor={{ false: colors.border, true: colors.primary }}
                        thumbColor={colors.surface}
                    />
                </View>

                {/* Atalho para Contatos Bloqueados (T19) */}
                <Pressable
                    style={[styles.switchRow, styles.lastRow]}
                    onPress={() => navigation.navigate('BlockedContacts')}
                >
                    <View style={styles.switchTextContainer}>
                        <Label variant="body" style={styles.switchTitle}>
                            Contatos bloqueados
                        </Label>
                        <Label variant="caption" style={styles.switchSubtitle}>
                            Gerenciar pessoas impedidas de interagir
                        </Label>
                    </View>
                    <Label variant="title" style={styles.chevron}>
                        ›
                    </Label>
                </Pressable>
            </View>

            {/* Aviso Criptográfico (Wireframe T18) */}
            <View style={styles.warningBox}>
                <Label variant="caption" style={styles.warningText}>
                    Redefinir a senha não recupera o histórico sem as chaves originais salvas no dispositivo.
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
    encryptionCard: {
        backgroundColor: colors.protection,
        padding: spacing.md,
        borderRadius: radii.md,
        alignItems: 'center',
        marginBottom: spacing.lg,
    },
    encryptionText: {
        color: colors.primary,
        fontWeight: '700',
    },
    preferencesCard: {
        backgroundColor: colors.surface,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: colors.border,
        marginBottom: spacing.lg,
        overflow: 'hidden',
    },
    switchRow: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        padding: spacing.md,
        borderBottomWidth: 1,
        borderBottomColor: colors.border,
        minHeight: dimensions.minTouchArea,
    },
    lastRow: {
        borderBottomWidth: 0,
    },
    switchTextContainer: {
        flex: 1,
        paddingRight: spacing.md,
    },
    switchTitle: {
        fontWeight: '600',
        color: colors.text,
    },
    switchSubtitle: {
        color: colors.textSecondary,
        marginTop: 2,
    },
    chevron: {
        color: colors.textSecondary,
        fontSize: 22,
    },
    warningBox: {
        backgroundColor: colors.surface,
        padding: spacing.md,
        borderRadius: radii.sm,
        borderWidth: 1,
        borderColor: colors.border,
    },
    warningText: {
        color: colors.textSecondary,
        textAlign: 'center',
        lineHeight: 18,
    },
});