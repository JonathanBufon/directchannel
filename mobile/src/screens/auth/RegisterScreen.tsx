// mobile/src/screens/RegisterScreen.tsx
import React, { useState } from 'react';
import { View, StyleSheet, Pressable } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { RootNavigationProp } from '../../types/navigation';
import { CenteredTemplate } from '../../components/templates/CenteredTemplate';
import { RegisterForm, RegisterFormData } from '../../components/organisms/RegisterForm';
import { Label } from '../../components/atoms/Label';
import { colors, spacing } from '../../theme';
import { saveAuthToken, saveUserProfile } from '../../services/storage';

export const RegisterScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();
    const [isLoading, setIsLoading] = useState(false);
    const [apiError, setApiError] = useState<string | null>(null);

    const handleRegisterSubmit = async (data: RegisterFormData) => {
        setIsLoading(true);
        setApiError(null);

        try {
            // Simulação da chamada da API
            await new Promise((resolve) => setTimeout(resolve, 1500));

            const mockJwtToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.directchannel_new_user';

            // 1. Salva Token no Cofre (SecureStore)
            await saveAuthToken(mockJwtToken);

            // 2. Salva Dados Públicos no AsyncStorage
            await saveUserProfile({
                id: `usr_${Date.now()}`,
                name: data.name,
                username: data.username,
                email: data.email,
                avatarUri: null,
            });

            // 3. Substitui a rota atual pela área logada
            navigation.replace('Main');
        } catch (error) {
            setApiError('Falha ao criar conta. Verifique sua conexão.');
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
            <Label variant="title" style={styles.title}>
                Criar conta
            </Label>
        </View>
    );

    return (
        <CenteredTemplate header={renderHeader()}>
            <RegisterForm
                onSubmit={handleRegisterSubmit}
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
        fontSize: 28, // Ícone customizado levemente maior
        lineHeight: 32,
    },
    title: {
        fontWeight: '700',
        color: colors.text,
    },
});