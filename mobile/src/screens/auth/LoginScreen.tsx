// mobile/src/screens/LoginScreen.tsx
import React, { useState, useEffect } from 'react';
import { View, StyleSheet, Alert } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { RootNavigationProp } from '../../types/navigation';
import { CenteredTemplate } from '../../components/templates/CenteredTemplate';
import { LoginForm, LoginFormData } from '../../components/organisms/LoginForm';
import { Label } from '../../components/atoms/Label';
import { colors, spacing, radii } from '../../theme';
import { saveAuthToken, saveUserProfile, getAuthToken } from '../../services/storage';

export const LoginScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();
    const [isLoading, setIsLoading] = useState(false);
    const [apiError, setApiError] = useState<string | null>(null);

    // Splash relê sessão persistida
    useEffect(() => {
        async function checkExistingSession() {
            const existingToken = await getAuthToken();
            if (existingToken) {
                navigation.replace('Main');
            }
        }
        checkExistingSession();
    }, [navigation]);

    const handleLoginSubmit = async (credentials: LoginFormData) => {
        setIsLoading(true);
        setApiError(null);

        try {
            // Simulação da resposta da API com Token JWT e Perfil
            await new Promise((resolve) => setTimeout(resolve, 1000));

            const mockJwtToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.directchannel_secure_payload';

            // 1. Salva Token no Cofre (SecureStore)
            await saveAuthToken(mockJwtToken);

            // 2. Salva Dados Públicos no AsyncStorage
            await saveUserProfile({
                id: 'usr_1',
                name: credentials.email.split('@')[0],
                username: `@${credentials.email.split('@')[0]}`,
                email: credentials.email,
                avatarUri: null,
            });

            // 3. Navega com replace (remove o login da pilha)
            navigation.replace('Main');
        } catch (error) {
            setApiError('Não foi possível conectar ao servidor. Tente novamente.');
        } finally {
            setIsLoading(false);
        }
    };

    const renderHeader = () => (
        <View style={styles.header}>
            <View style={styles.logoBadge}>
                <Label variant="title" style={styles.logoText}>
                    D
                </Label>
            </View>
            <Label variant="title" style={styles.title}>
                DirectChannel
            </Label>
            <Label variant="caption" style={styles.subtitle}>
                Suas conversas. Sua privacidade.
            </Label>
        </View>
    );

    return (
        <CenteredTemplate header={renderHeader()}>
            <LoginForm
                onSubmit={handleLoginSubmit}
                isLoading={isLoading}
                errorMessage={apiError}
                onForgotPassword={() => navigation.navigate('RecoverAccess')}
                onCreateAccount={() => navigation.navigate('Register')}
            />
        </CenteredTemplate>
    );
};

const styles = StyleSheet.create({
    header: {
        alignItems: 'center',
        marginBottom: spacing.lg,
    },
    logoBadge: {
        width: 52,
        height: 52,
        borderRadius: radii.md,
        backgroundColor: colors.protection,
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: spacing.sm,
    },
    logoText: {
        color: colors.primary,
        fontWeight: '700',
    },
    title: {
        fontWeight: '700',
    },
    subtitle: {
        marginTop: spacing.xs,
        color: colors.textSecondary,
    },
});