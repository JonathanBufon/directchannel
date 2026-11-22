// mobile/src/components/organisms/RecoverAccessForm.tsx
import React, { useState } from 'react';
import { View, StyleSheet } from 'react-native';
import { FormField } from '../molecules/FormField';
import { ErrorMessage } from '../molecules/ErrorMessage';
import { Button } from '../atoms/Button';
import { Label } from '../atoms/Label';
import { validateEmail } from '../../utils/validation';
import { colors, spacing, radii } from '../../theme';

export interface RecoverAccessFormProps {
    onSubmit: (email: string) => void;
    isLoading?: boolean;
    errorMessage?: string | null;
}

export const RecoverAccessForm: React.FC<RecoverAccessFormProps> = ({
    onSubmit,
    isLoading = false,
    errorMessage = null,
}) => {
    const [email, setEmail] = useState('');
    const [touched, setTouched] = useState(false);

    const emailError = validateEmail(email);

    const handleSubmit = () => {
        setTouched(true);

        if (emailError || isLoading) {
            return;
        }

        onSubmit(email.trim());
    };

    return (
        <View style={styles.container}>
            <ErrorMessage message={errorMessage} />

            <FormField
                label="E-mail"
                placeholder="voce@exemplo.com"
                value={email}
                onChangeText={setEmail}
                onBlur={() => setTouched(true)}
                errorMessage={touched ? emailError : null}
                keyboardType="email-address"
                autoCapitalize="none"
                autoCorrect={false}
                returnKeyType="done"
                onSubmitEditing={handleSubmit}
                blurOnSubmit={true}
            />

            <Button
                title="Enviar instruções"
                variant="primary"
                isLoading={isLoading}
                onPress={handleSubmit}
                style={styles.submitButton}
            />

            <View style={styles.warningBox}>
                <Label variant="caption" style={styles.warningText}>
                    Redefinir a senha não recupera o histórico sem as chaves originais.
                </Label>
            </View>
        </View>
    );
};

const styles = StyleSheet.create({
    container: {
        width: '100%',
    },
    submitButton: {
        marginTop: spacing.md,
        marginBottom: spacing.md,
    },
    warningBox: {
        backgroundColor: colors.protection,
        padding: spacing.md,
        borderRadius: radii.sm,
    },
    warningText: {
        color: colors.textSecondary,
        textAlign: 'center',
    },
});