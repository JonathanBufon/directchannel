// mobile/src/screens/LoginScreen.tsx
import React, { useState } from 'react';
import { View, StyleSheet, Alert } from 'react-native';
import { CenteredTemplate } from '../../components/templates/CenteredTemplate';
import { LoginForm, LoginFormData } from '../../components/organisms/LoginForm';
import { Label } from '../../components/atoms/Label';
import { colors, spacing, radii } from '../../theme';

export const LoginScreen: React.FC = () => {
  // Estado assíncrono controlado pela Screen
  const [isLoading, setIsLoading] = useState(false);
  const [apiError, setApiError] = useState<string | null>(null);

  // Orquestrador de submissão do formulário
  const handleLoginSubmit = async (credentials: LoginFormData) => {
    setIsLoading(true);
    setApiError(null);

    try {
      // Simulação da chamada de autenticação (será substituída por services/auth.ts)
      await new Promise((resolve) => setTimeout(resolve, 1500));

      Alert.alert('Sucesso', `Autenticado com: ${credentials.email}`);
    } catch (error) {
      setApiError('Não foi possível conectar ao servidor. Tente novamente.');
    } finally {
      setIsLoading(false);
    }
  };

  // Cabeçalho oficial DirectChannel (Logo "D" + Título + Subtítulo)
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
        onForgotPassword={() => Alert.alert('Navegação', 'Ir para T03 (Recuperar acesso)')}
        onCreateAccount={() => Alert.alert('Navegação', 'Ir para T02 (Criar conta)')}
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