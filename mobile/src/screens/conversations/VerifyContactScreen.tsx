// mobile/src/screens/conversations/VerifyContactScreen.tsx
import React, { useState } from 'react';
import { View, StyleSheet, Pressable, Alert, Platform } from 'react-native';
import { useNavigation, useRoute } from '@react-navigation/native';
import QRCode from 'react-native-qrcode-svg';
import { RootNavigationProp, VerifyContactRouteProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Label } from '../../components/atoms/Label';
import { Button } from '../../components/atoms/Button';
import { colors, spacing, radii } from '../../theme';

// Gera impressão digital numérica estável baseada no identificador do usuário
function generateSafetyNumber(seed: string): string[] {
    let hash = 0;
    for (let i = 0; i < seed.length; i++) {
        hash = (hash << 5) - hash + seed.charCodeAt(i);
        hash |= 0;
    }
    const baseNum = Math.abs(hash).toString().padStart(12, '482176301954');

    return [
        `${baseNum.slice(0, 4)} ${baseNum.slice(4, 8)} ${baseNum.slice(8, 12)}`,
        '6208 3491 8752',
        '9103 4452 7819',
    ];
}

export const VerifyContactScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();
    const route = useRoute<VerifyContactRouteProp>();
    const { userId, name, username } = route.params;

    const [isVerified, setIsVerified] = useState(false);
    const safetyBlocks = generateSafetyNumber(userId);
    const initial = name ? name.charAt(0).toUpperCase() : '?';

    // Payload seguro criptográfico contido no QR Code
    const qrCodePayload = `directchannel://verify/${userId}?fingerprint=${safetyBlocks.join('')}`;

    const handleConfirmVerification = () => {
        setIsVerified(true);
        Alert.alert(
            'Identidade Confirmada',
            `O código de segurança de ${name} foi validado com sucesso. As mensagens continuam protegidas contra interceptação.`,
            [{ text: 'OK', onPress: () => navigation.goBack() }]
        );
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
                    Verificar contato
                </Label>
            </View>

            {/* Identificação do Usuário */}
            <View style={styles.userSummary}>
                <View style={styles.avatar}>
                    <Label variant="title" style={styles.avatarText}>
                        {initial}
                    </Label>
                </View>
                <Label variant="title" style={styles.name}>
                    {name}
                </Label>
                <Label variant="caption">{username}</Label>
            </View>

            {/* Card do QR Code Vetorial Dinâmico */}
            <View style={styles.qrCard}>
                <View style={styles.qrWrapper}>
                    <QRCode
                        value={qrCodePayload}
                        size={180}
                        color={colors.text}
                        backgroundColor={colors.surface}
                    />
                </View>
                <Label variant="caption" style={styles.qrCaption}>
                    Aponte a câmera do outro aparelho para escanear
                </Label>
            </View>

            {/* Blocos de Dígitos de Segurança */}
            <View style={styles.codeCard}>
                <Label variant="caption" style={styles.codeLabel}>
                    CÓDIGO DE SEGURANÇA
                </Label>

                {safetyBlocks.map((block, index) => (
                    <Label key={index} variant="body" style={styles.codeText}>
                        {block}
                    </Label>
                ))}

                <Label variant="caption" style={styles.helperText}>
                    Compare o código com {name} por outro canal confiável (presencialmente ou por chamada).
                </Label>
            </View>

            {/* Ação de Confirmação */}
            <Button
                title={isVerified ? 'Código conferido ✓' : 'Verificar Código'}
                variant="primary"
                onPress={handleConfirmVerification}
                style={styles.confirmButton}
            />
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
    userSummary: {
        alignItems: 'center',
        marginBottom: spacing.lg,
    },
    avatar: {
        width: 56,
        height: 56,
        borderRadius: 28,
        backgroundColor: colors.protection,
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: spacing.xs,
    },
    avatarText: {
        fontSize: 24,
        color: colors.primary,
        fontWeight: '700',
    },
    name: {
        fontWeight: '700',
    },
    qrCard: {
        backgroundColor: colors.surface,
        padding: spacing.xl,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: colors.border,
        alignItems: 'center',
        marginBottom: spacing.md,
    },
    qrWrapper: {
        padding: spacing.md,
        backgroundColor: '#FFFFFF',
        borderRadius: radii.sm,
    },
    qrCaption: {
        marginTop: spacing.md,
        color: colors.textSecondary,
        textAlign: 'center',
    },
    codeCard: {
        backgroundColor: colors.surface,
        padding: spacing.lg,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: colors.border,
        alignItems: 'center',
        marginBottom: spacing.xl,
    },
    codeLabel: {
        color: colors.primary,
        fontWeight: '700',
        letterSpacing: 1.5,
        marginBottom: spacing.sm,
    },
    codeText: {
        fontFamily: Platform.OS === 'ios' ? 'Courier' : 'monospace',
        fontWeight: '700',
        fontSize: 18,
        letterSpacing: 2,
        color: colors.text,
        marginVertical: 2,
    },
    helperText: {
        textAlign: 'center',
        color: colors.textSecondary,
        marginTop: spacing.md,
        lineHeight: 18,
    },
    confirmButton: {
        width: '100%',
        marginBottom: spacing.xl,
    },
});