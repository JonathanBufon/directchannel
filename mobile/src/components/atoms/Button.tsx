// mobile/src/components/atoms/Button.tsx
import React from 'react';
import {
  Pressable,
  Text,
  ActivityIndicator,
  StyleSheet,
  PressableProps,
  ViewStyle,
} from 'react-native';
import { colors, spacing, radii, dimensions, typography } from '../../theme';

export type ButtonVariant = 'primary' | 'secondary' | 'danger';

interface ButtonProps extends Omit<PressableProps, 'style'> {
  title: string;
  variant?: ButtonVariant;
  isLoading?: boolean;
  style?: ViewStyle;
}

export const Button: React.FC<ButtonProps> = ({
  title,
  variant = 'primary',
  isLoading = false,
  disabled = false,
  style,
  onPress,
  ...rest
}) => {
  const isInteractionDisabled = disabled || isLoading;

  return (
    <Pressable
      onPress={onPress}
      disabled={isInteractionDisabled}
      style={({ pressed }) => [
        styles.base,
        styles[variant],
        pressed && !isInteractionDisabled && styles.pressed,
        isInteractionDisabled && styles.disabled,
        style,
      ]}
      {...rest}
    >
      {isLoading ? (
        <ActivityIndicator
          color={variant === 'secondary' ? colors.primary : colors.surface}
          size="small"
        />
      ) : (
        <Text
          style={[
            styles.textBase,
            variant === 'secondary' ? styles.textSecondary : styles.textLight,
          ]}
        >
          {title}
        </Text>
      )}
    </Pressable>
  );
};

const styles = StyleSheet.create({
  base: {
    minHeight: dimensions.buttonHeight,
    minWidth: dimensions.minTouchArea,
    borderRadius: radii.md,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.sm,
  },
  // Variações visuais
  primary: {
    backgroundColor: colors.primary,
  },
  secondary: {
    backgroundColor: 'transparent',
    borderWidth: 1,
    borderColor: colors.primary,
  },
  danger: {
    backgroundColor: colors.error,
  },
  // Estados de interação
  pressed: {
    opacity: 0.8,
  },
  disabled: {
    backgroundColor: colors.disabled,
    borderColor: colors.disabled,
  },
  // Estilos de texto
  textBase: {
    fontSize: typography.body,
    fontWeight: '600',
  },
  textLight: {
    color: colors.surface,
  },
  textSecondary: {
    color: colors.primary,
  },
});