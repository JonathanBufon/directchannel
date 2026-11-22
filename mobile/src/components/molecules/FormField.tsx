// mobile/src/components/molecules/FormField.tsx
import React, { forwardRef } from 'react';
import { View, StyleSheet, ViewStyle } from 'react-native';
import { Label } from '../atoms/Label';
import { Input, InputProps, InputRef } from '../atoms/Input';
import { colors, spacing, typography } from '../../theme';

export interface FormFieldProps extends InputProps {
    label: string;
    errorMessage?: string | null;
    containerStyle?: ViewStyle;
}

// Repassa o InputRef até o átomo Input
export const FormField = forwardRef<InputRef, FormFieldProps>(
    ({ label, errorMessage, containerStyle, ...inputProps }, ref) => {
        const hasError = Boolean(errorMessage);

        return (
            <View style={[styles.container, containerStyle]}>
                <Label variant="body" style={styles.label}>
                    {label}
                </Label>

                <Input
                    ref={ref}
                    hasError={hasError}
                    {...inputProps}
                />

                {hasError ? (
                    <Label variant="caption" style={styles.errorText}>
                        {errorMessage}
                    </Label>
                ) : null}
            </View>
        );
    }
);

FormField.displayName = 'FormField';

const styles = StyleSheet.create({
    container: {
        marginBottom: spacing.md,
        width: '100%',
    },
    label: {
        marginBottom: spacing.xs,
        fontWeight: '500',
    },
    errorText: {
        color: colors.error,
        fontSize: typography.caption,
        marginTop: spacing.xs,
    },
});