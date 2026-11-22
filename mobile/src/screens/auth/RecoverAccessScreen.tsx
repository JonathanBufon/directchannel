// mobile/src/screens/RecoverAccessScreen.tsx
import React, { useState } from 'react';
import { View, StyleSheet, Pressable, Alert } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { RootNavigationProp } from '../../types/navigation';
import { CenteredTemplate } from '../../components/templates/CenteredTemplate';
import { RecoverAccessForm } from '../../components/organisms/RecoverAccessForm';
import { Label } from '../../components/atoms/Label';
import { colors, spacing } from '../../theme';

export const RecoverAccessScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();
    const [isLoading, setIsLoading] = useState(false);
    const [apiError, setApiError] = useState<string | null>(null);

    const handleRecoverSubmit = async (email: string) => {
        setIsLoading(true);
        setApiError(null);

        try {
            // Simulação da chamada da API
            await new Promise((resolve) => setTimeout(resolve, 1500));

            // Requisito RF27: Fluxo não revela cadastro do e-mail.
            Alert.alert(
                'Instruções enviadas',
                'Se o e-mail estiver cadastrado em nossa base, você receberá um código de recuperação em instantes.',
                [{ text: 'OK', onPress: () => navigation.goBack() }]
            );
        } catch (error) {
            setApiError('Ocorreu um erro ao processar seu pedido. Tente novamente.');
        } finally {
            setIsLoading(false);
        }
    };

    const renderHeader = () => (
        <View style={styles.header}>
            <Pressable onPress={() => navigation.goBack()} style={styles.backButton}>
                <Label variant="title" style={styles.backIcon}>
                    ‹
                </Label>
            </Pressable>
            <View>
                <Label variant="title" style={styles.title}>
                    Recuperar acesso
                </Label>
                <Label variant="caption" style={styles.subtitle}>
                    Recupere o acesso à sua conta.
                </Label>
            </View>
        </View>
    );

    return (
        <CenteredTemplate header={renderHeader()}>
            <RecoverAccessForm
                onSubmit={handleRecoverSubmit}
                isLoading={isLoading}
                errorMessage={apiError}
            />
        </CenteredTemplate>
    );
};

const styles = StyleSheet.create({
    header: {
        width: '100%',
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
    title: {
        fontWeight: '700',
        color: colors.text,
    },
    subtitle: {
        marginTop: spacing.xs,
        color: colors.textSecondary,
    },
});