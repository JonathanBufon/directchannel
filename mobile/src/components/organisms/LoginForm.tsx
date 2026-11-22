// mobile/src/components/organisms/LoginForm.tsx
import React, { useState, useRef } from 'react';
import { View, StyleSheet, Pressable } from 'react-native';
import { FormField } from '../molecules/FormField';
import { ErrorMessage } from '../molecules/ErrorMessage';
import { Button } from '../atoms/Button';
import { Label } from '../atoms/Label';
import { InputRef } from '../atoms/Input';
import { validateEmail, validatePassword, getFirstError } from '../../utils/validation';
import { colors, spacing } from '../../theme';

export interface LoginFormData {
    email: string;
    password: string;
}

export interface LoginFormProps {
    onSubmit: (data: LoginFormData) => void;
    isLoading?: boolean;
    errorMessage?: string | null;
    onForgotPassword?: () => void;
    onCreateAccount?: () => void;
}

export const LoginForm: React.FC<LoginFormProps> = ({
    onSubmit,
    isLoading = false,
    errorMessage = null,
    onForgotPassword,
    onCreateAccount,
}) => {
    // Estado local dos valores dos inputs
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');

    // Rastreio de interação: evita mostrar borda vermelha antes do usuário interagir
    const [touched, setTouched] = useState<{ email?: boolean; password?: boolean }>({});

    // Referência tipada para passar o foco para a senha ao clicar "Avançar" no teclado
    const passwordInputRef = useRef<InputRef>(null);

    // Mapa de erros reavaliado a cada render com funções puras
    const errors = {
        email: validateEmail(email),
        password: validatePassword(password),
    };

    const markTouched = (field: 'email' | 'password') => {
        setTouched((prev) => ({ ...prev, [field]: true }));
    };

    const handleSubmit = () => {
        // Marca todos os campos como tocados ao tentar enviar
        setTouched({ email: true, password: true });

        const hasValidationError = getFirstError(errors);
        if (hasValidationError || isLoading) {
            return;
        }

        // Dispara para a Screen com e-mail sanitizado (trim)
        onSubmit({
            email: email.trim(),
            password,
        });
    };

    return (
        <View style={styles.container}>
            {/* Mensagem de erro de nível superior (API / Servidor / Rede) */}
            <ErrorMessage message={errorMessage} />

            {/* Campo E-mail */}
            <FormField
                label="E-mail"
                placeholder="voce@exemplo.com"
                value={email}
                onChangeText={setEmail}
                onBlur={() => markTouched('email')}
                errorMessage={touched.email ? errors.email : null}
                keyboardType="email-address"
                autoCapitalize="none"
                autoCorrect={false}
                returnKeyType="next"
                onSubmitEditing={() => passwordInputRef.current?.focus()}
                blurOnSubmit={false}
            />

            {/* Campo Senha */}
            <FormField
                ref={passwordInputRef}
                label="Senha"
                placeholder="••••••••"
                value={password}
                onChangeText={setPassword}
                onBlur={() => markTouched('password')}
                errorMessage={touched.password ? errors.password : null}
                secureTextEntry
                returnKeyType="done"
                onSubmitEditing={handleSubmit}
            />

            {/* Botão Primário */}
            <Button
                title="Entrar"
                variant="primary"
                isLoading={isLoading}
                onPress={handleSubmit}
                style={styles.submitButton}
            />

            {/* Ações secundárias (conforme layout oficial T01) */}
            {onForgotPassword ? (
                <Pressable onPress={onForgotPassword} style={styles.linkButton}>
                    <Label variant="body" style={styles.linkText}>
                        Esqueci minha senha ›
                    </Label>
                </Pressable>
            ) : null}

            {onCreateAccount ? (
                <Pressable onPress={onCreateAccount} style={styles.linkButton}>
                    <Label variant="body" style={styles.linkText}>
                        Criar conta ›
                    </Label>
                </Pressable>
            ) : null}
        </View>
    );
};

const styles = StyleSheet.create({
    container: {
        width: '100%',
    },
    submitButton: {
        marginTop: spacing.sm,
        marginBottom: spacing.md,
    },
    linkButton: {
        paddingVertical: spacing.sm,
        alignItems: 'center',
        justifyContent: 'center',
    },
    linkText: {
        color: colors.text,
    },
});