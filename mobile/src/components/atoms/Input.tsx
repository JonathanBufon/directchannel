// mobile/src/components/atoms/Input.tsx
import React, { forwardRef, useState } from 'react';
import {
    TextInput,
    StyleSheet,
    TextInputProps,
} from 'react-native';
import { colors, spacing, radii, dimensions, typography } from '../../theme';

export interface InputProps extends TextInputProps {
    hasError?: boolean;
}

// Extrai o tipo exato da instância do TextInput para uso seguro em refs
export type InputRef = React.ComponentRef<typeof TextInput>;

export const Input = forwardRef<InputRef, InputProps>(
    ({ hasError = false, style, onFocus, onBlur, ...rest }, ref) => {
        // Estado interno para controle visual de foco
        const [isFocused, setIsFocused] = useState(false);

        const handleFocus: TextInputProps['onFocus'] = (event) => {
            setIsFocused(true);
            onFocus?.(event);
        };

        const handleBlur: TextInputProps['onBlur'] = (event) => {
            setIsFocused(false);
            onBlur?.(event);
        };

        // Determina a cor da borda: erro > foco > padrão
        const getBorderColor = () => {
            if (hasError) return colors.error;
            if (isFocused) return colors.borderFocus;
            return colors.border;
        };

        return (
            <TextInput
                ref={ref}
                style={[
                    styles.input,
                    { borderColor: getBorderColor() },
                    style,
                ]}
                placeholderTextColor={colors.textSecondary}
                onFocus={handleFocus}
                onBlur={handleBlur}
                {...rest}
            />
        );
    }
);

Input.displayName = 'Input';

const styles = StyleSheet.create({
    input: {
        height: dimensions.inputHeight,
        backgroundColor: colors.surface,
        borderWidth: 1,
        borderRadius: radii.md,
        paddingHorizontal: spacing.md,
        fontSize: typography.body,
        color: colors.text,
    },
});