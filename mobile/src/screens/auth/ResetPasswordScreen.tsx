// mobile/src/screens/auth/ResetPasswordScreen.tsx
import React, { useState } from 'react';
import { View, StyleSheet, Pressable, Alert } from 'react-native';
import { useNavigation, useRoute } from '@react-navigation/native';
import { RootNavigationProp, ResetPasswordRouteProp } from '../../types/navigation';
import { CenteredTemplate } from '../../components/templates/CenteredTemplate';
import { FormField } from '../../components/molecules/FormField';
import { Button } from '../../components/atoms/Button';
import { Label } from '../../components/atoms/Label';
import { colors, spacing, radii } from '../../theme';
import { validatePassword, validatePasswordConfirmation, getFirstError } from '../../utils/validation';

export const ResetPasswordScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();
    const route = useRoute<ResetPasswordRouteProp>();

    const [code, setCode] = useState('');
    const [password, setPassword] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [touched, setTouched] = useState<{ [key: string]: boolean }>({});
    const [isLoading, setIsLoading] = useState(false);

    const errors = {
        code: !code.trim() ? 'Informe o código recebido.' : null,
        password: validatePassword(password, { requireMinLength: true }),
        confirmPassword: validatePasswordConfirmation(password, confirmPassword),
    };

    const markTouched = (field: string) => {
        setTouched((prev) => ({ ...prev, [field]: true }));
    };

    const handleSaveNewPassword = async () => {
        setTouched({ code: true, password: true, confirmPassword: true });

        const hasError = getFirstError(errors);
        if (hasError || isLoading) return;

        setIsLoading(true);
        try {
            // Simulação da atualização de senha no servidor
            await new Promise((resolve) => setTimeout(resolve, 1500));

            Alert.alert(
                'Senha Atualizada',
                'Sua senha foi redefinida com sucesso. Faça login com suas novas credenciais.',
                [{ text: 'Ir para o Login', onPress: () => navigation.replace('Login') }]
            );
        } catch {
            Alert.alert('Erro', 'Não foi possível redefinir sua senha.');
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
                    Redefinir senha
                </Label>
                <Label variant="caption" style={styles.subtitle}>
                    Se o e-mail estiver cadastrado, você receberá instruções.
                </Label>
            </View>
        </View>
    );

    return (
        <CenteredTemplate header={renderHeader()}>
            <View style={styles.form}>
                <FormField
                    label="Código recebido"
                    placeholder="000000"
                    value={code}
                    onChangeText={setCode}
                    onBlur={() => markTouched('code')}
                    errorMessage={touched.code ? errors.code : null}
                    keyboardType="number-pad"
                    maxLength={6}
                />

                <FormField
                    label="Nova senha"
                    placeholder="••••••••"
                    value={password}
                    onChangeText={setPassword}
                    onBlur={() => markTouched('password')}
                    errorMessage={touched.password ? errors.password : null}
                    secureTextEntry
                />

                <FormField
                    label="Confirmar nova senha"
                    placeholder="••••••••"
                    value={confirmPassword}
                    onChangeText={setConfirmPassword}
                    onBlur={() => markTouched('confirmPassword')}
                    errorMessage={touched.confirmPassword ? errors.confirmPassword : null}
                    secureTextEntry
                />

                <Button
                    title="Salvar nova senha"
                    variant="primary"
                    isLoading={isLoading}
                    onPress={handleSaveNewPassword}
                    style={styles.submitButton}
                />
            </View>
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
    form: {
        width: '100%',
    },
    submitButton: {
        marginTop: spacing.md,
    },
});