// mobile/src/components/molecules/ErrorMessage.tsx
import React from 'react';
import { View, StyleSheet, ViewStyle } from 'react-native';
import { Label } from '../atoms/Label';
import { colors, spacing, radii } from '../../theme';

export interface ErrorMessageProps {
  message?: string | null;
  style?: ViewStyle;
}

export const ErrorMessage: React.FC<ErrorMessageProps> = ({ message, style }) => {
  // Retorna nulo se não houver erro ativo
  if (!message) {
    return null;
  }

  return (
    <View style={[styles.container, style]}>
      <Label variant="body" style={styles.text}>
        {message}
      </Label>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#FDEDEC', // Fundo vermelho suave para destaque
    borderLeftWidth: 4,
    borderLeftColor: colors.error,
    padding: spacing.md,
    borderRadius: radii.sm,
    marginBottom: spacing.md,
    width: '100%',
  },
  text: {
    color: colors.error,
    fontWeight: '500',
  },
});