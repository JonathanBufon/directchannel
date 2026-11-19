// mobile/src/screens/files/FileViewerScreen.tsx
import React, { useState } from 'react';
import { View, StyleSheet, Pressable, Alert } from 'react-native';
import { useNavigation, useRoute } from '@react-navigation/native';
import { RootNavigationProp, FileViewerRouteProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Label } from '../../components/atoms/Label';
import { Button } from '../../components/atoms/Button';
import { colors, spacing, radii } from '../../theme';
import { pickDocument, SelectedDocument } from '../../services/natives';

type ActionStatus = 'idle' | 'processing' | 'completed' | 'error';

// Limite estipulado pelo requisito RNF13 (25 Megabytes em bytes)
const MAX_FILE_SIZE_BYTES = 25 * 1024 * 1024;

// Helper para exibição amigável de tamanho
function formatBytes(bytes?: number): string {
    if (!bytes || bytes === 0) return 'Tamanho desconhecido';
    const kb = bytes / 1024;
    if (kb < 1024) return `${kb.toFixed(1)} KB`;
    const mb = kb / 1024;
    return `${mb.toFixed(1)} MB`;
}

export const FileViewerScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();
    const route = useRoute<FileViewerRouteProp>();
    const { fileName: initialName, fileSize: initialSize, isIncoming = true } = route.params;

    // Estado dinâmico do arquivo selecionado
    const [currentFile, setCurrentFile] = useState<{
        name: string;
        sizeFormatted: string;
        sizeBytes?: number;
        uri?: string;
    }>({
        name: initialName,
        sizeFormatted: initialSize,
    });

    const [status, setStatus] = useState<ActionStatus>('idle');
    const [errorMessage, setErrorMessage] = useState<string | null>(null);

    // Validação em tempo real do limite de 25 MB (RNF13)
    const isOverLimit = currentFile.sizeBytes
        ? currentFile.sizeBytes > MAX_FILE_SIZE_BYTES
        : false;

    const initial = currentFile.name ? currentFile.name.charAt(0).toUpperCase() : 'F';

    // 1. Ação de Selecionar Arquivo Real no Aparelho
    const handleSelectFile = async () => {
        try {
            const doc = await pickDocument();
            if (!doc) return; // Usuário cancelou

            setCurrentFile({
                name: doc.name,
                sizeFormatted: formatBytes(doc.size),
                sizeBytes: doc.size,
                uri: doc.uri,
            });

            setStatus('idle');
            setErrorMessage(null);
        } catch (error) {
            Alert.alert('Erro ao Selecionar', (error as Error).message);
        }
    };

    // 2. Simulação de Download de Arquivo Recebido
    const handleDownload = async () => {
        setStatus('processing');
        setErrorMessage(null);

        try {
            await new Promise((resolve) => setTimeout(resolve, 1500));
            setStatus('completed');

            Alert.alert(
                'Arquivo Concluído',
                `${currentFile.name} foi verificado contra adulteração e decifrado localmente.`
            );
        } catch {
            setStatus('error');
            setErrorMessage('Falha ao decifrar e descarregar o arquivo.');
        }
    };

    // 3. Simulação de Envio de Arquivo Cifrado
    const handleUpload = async () => {
        if (isOverLimit) {
            Alert.alert(
                'Arquivo Recusado',
                'O arquivo selecionado ultrapassa o limite máximo permitido de 25 MB (RNF13).'
            );
            return;
        }

        setStatus('processing');
        setErrorMessage(null);

        try {
            await new Promise((resolve) => setTimeout(resolve, 1500));
            setStatus('completed');

            Alert.alert(
                'Arquivo Enviado',
                `${currentFile.name} foi cifrado antes do upload com as chaves locais da conversa.`,
                [{ text: 'Voltar à conversa', onPress: () => navigation.goBack() }]
            );
        } catch {
            setStatus('error');
            setErrorMessage('Falha ao transmitir o arquivo.');
        }
    };

    const renderHeader = () => (
        <View style={styles.header}>
            <Pressable onPress={() => navigation.goBack()} style={styles.backButton}>
                <Label variant="title" style={styles.backIcon}>
                    ‹
                </Label>
            </Pressable>
            <Label variant="title" style={styles.headerTitle}>
                Arquivo
            </Label>
        </View>
    );

    return (
        <ScreenTemplate scrollable>
            {renderHeader()}

            {/* Card Principal de Identificação do Arquivo */}
            <View style={styles.fileCard}>
                <View style={styles.badge}>
                    <Label variant="title" style={styles.badgeText}>
                        {initial}
                    </Label>
                </View>

                <Label variant="title" style={styles.fileName}>
                    {currentFile.name}
                </Label>
                <Label variant="caption" style={styles.fileMeta}>
                    Documento · {currentFile.sizeFormatted}
                </Label>

                {/* Tag de Segurança Oficial do DirectChannel */}
                <View style={styles.securityTag}>
                    <Label variant="caption" style={styles.securityText}>
                        🔒 Arquivo protegido de ponta a ponta
                    </Label>
                </View>
            </View>

            {/* Alerta caso exceda o limite de 25 MB */}
            {isOverLimit ? (
                <View style={styles.errorBox}>
                    <Label variant="body" style={styles.errorText}>
                        Arquivo acima do limite de 25 MB (RNF13). O upload não é permitido.
                    </Label>
                </View>
            ) : null}

            {errorMessage ? (
                <View style={styles.errorBox}>
                    <Label variant="body" style={styles.errorText}>
                        {errorMessage}
                    </Label>
                </View>
            ) : null}

            {/* Seção de Ações Conforme o Wireframe T07 */}
            <View style={styles.actionsContainer}>
                {/* Botão de seleção de arquivo exibido apenas no modo de envio */}
                {!isIncoming ? (
                    <Button
                        title="Selecionar arquivo do aparelho"
                        variant="secondary"
                        onPress={handleSelectFile}
                        style={styles.actionButton}
                    />
                ) : null}

                {isIncoming ? (
                    <Button
                        title={
                            status === 'completed'
                                ? 'Arquivo baixado (Abrir novamente)'
                                : 'Baixar arquivo recebido'
                        }
                        variant="primary"
                        isLoading={status === 'processing'}
                        onPress={handleDownload}
                        style={styles.actionButton}
                    />
                ) : (
                    <Button
                        title="Enviar arquivo selecionado"
                        variant="primary"
                        isLoading={status === 'processing'}
                        disabled={isOverLimit}
                        onPress={handleUpload}
                        style={styles.actionButton}
                    />
                )}

                <View style={styles.helperBox}>
                    <Label variant="caption" style={styles.helperText}>
                        O arquivo será aberto no seu dispositivo de forma segura. O servidor não possui chaves de leitura.
                    </Label>
                </View>
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
    fileCard: {
        backgroundColor: colors.surface,
        padding: spacing.xl,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: colors.border,
        alignItems: 'center',
        marginBottom: spacing.lg,
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
        fontSize: 28,
        color: colors.primary,
        fontWeight: '700',
    },
    fileName: {
        fontWeight: '700',
        color: colors.text,
        textAlign: 'center',
    },
    fileMeta: {
        color: colors.textSecondary,
        marginTop: spacing.xs,
        marginBottom: spacing.md,
    },
    securityTag: {
        backgroundColor: colors.protection,
        paddingHorizontal: spacing.md,
        paddingVertical: spacing.xs,
        borderRadius: radii.full,
    },
    securityText: {
        color: colors.primary,
        fontWeight: '600',
    },
    actionsContainer: {
        width: '100%',
    },
    actionButton: {
        width: '100%',
        marginBottom: spacing.md,
    },
    helperBox: {
        backgroundColor: colors.surface,
        padding: spacing.md,
        borderRadius: radii.sm,
        borderWidth: 1,
        borderColor: colors.border,
        marginTop: spacing.sm,
    },
    helperText: {
        color: colors.textSecondary,
        textAlign: 'center',
        lineHeight: 18,
    },
    errorBox: {
        backgroundColor: '#FDEDEC',
        padding: spacing.md,
        borderRadius: radii.sm,
        borderLeftWidth: 4,
        borderLeftColor: colors.error,
        marginBottom: spacing.md,
    },
    errorText: {
        color: colors.error,
        fontWeight: '500',
    },
});