// mobile/src/screens/admin/AdminSettingsScreen.tsx
import React, { useState, useEffect } from 'react';
import { View, StyleSheet, Pressable, Alert } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { RootNavigationProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Label } from '../../components/atoms/Label';
import { Input } from '../../components/atoms/Input';
import { Button } from '../../components/atoms/Button';
import { colors, spacing, radii } from '../../theme';
import { savePreference, getPreference } from '../../services/storage';

export const AdminSettingsScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();

    const [maxFileSize, setMaxFileSize] = useState('25 MB');
    const [logRetention, setLogRetention] = useState('30 dias');
    const [isLoading, setIsLoading] = useState(false);

    useEffect(() => {
        async function loadSettings() {
            const savedSize = await getPreference<string>('admin_max_file_size', '25 MB');
            const savedLogs = await getPreference<string>('admin_log_retention', '30 dias');
            setMaxFileSize(savedSize);
            setLogRetention(savedLogs);
        }
        loadSettings();
    }, []);

    const handleSaveSettings = async () => {
        setIsLoading(true);
        try {
            await savePreference('admin_max_file_size', maxFileSize);
            await savePreference('admin_log_retention', logRetention);

            Alert.alert(
                'Configurações Salvas',
                'Os parâmetros operacionais da plataforma foram atualizados.',
                [{ text: 'OK', onPress: () => navigation.goBack() }]
            );
        } catch {
            Alert.alert('Erro', 'Não foi possível salvar as configurações.');
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
                    Configurações
                </Label>
            </View>

            {/* Formulário de Parâmetros da Plataforma */}
            <View style={styles.card}>
                {/* RNF13: Tamanho Máximo de Arquivo */}
                <View style={styles.fieldBox}>
                    <Label variant="body" style={styles.fieldLabel}>
                        Tamanho máximo do arquivo
                    </Label>
                    <Input
                        value={maxFileSize}
                        onChangeText={setMaxFileSize}
                        style={styles.input}
                    />
                </View>

                {/* RNF15: Retenção de Logs Operacionais */}
                <View style={styles.fieldBox}>
                    <Label variant="body" style={styles.fieldLabel}>
                        Retenção de logs operacionais
                    </Label>
                    <Input
                        value={logRetention}
                        onChangeText={setLogRetention}
                        style={styles.input}
                    />
                </View>

                {/* Botão de Gravação */}
                <Button
                    title="Salvar configuração"
                    variant="primary"
                    isLoading={isLoading}
                    onPress={handleSaveSettings}
                    style={styles.actionButton}
                />
            </View>

            {/* Tag Estática Obrigatória de Segurança */}
            <View style={styles.lockBox}>
                <Label variant="body" style={styles.lockText}>
                    🔒 Criptografia não pode ser desativada
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
    fieldBox: {
        marginBottom: spacing.md,
    },
    fieldLabel: {
        fontWeight: '600',
        color: colors.text,
        marginBottom: spacing.xs,
    },
    input: {
        backgroundColor: colors.background,
    },
    actionButton: {
        marginTop: spacing.sm,
    },
    lockBox: {
        backgroundColor: colors.protection,
        padding: spacing.md,
        borderRadius: radii.sm,
        alignItems: 'center',
    },
    lockText: {
        color: colors.primary,
        fontWeight: '700',
    },
});