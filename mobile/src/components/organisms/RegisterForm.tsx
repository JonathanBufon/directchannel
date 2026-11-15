// mobile/src/components/organisms/RegisterForm.tsx
import React, { useState, useRef } from 'react';
import { View, StyleSheet } from 'react-native';
import { FormField } from '../molecules/FormField';
import { ErrorMessage } from '../molecules/ErrorMessage';
import { Button } from '../atoms/Button';
import { InputRef } from '../atoms/Input';
import {
  validateName,
  validateUsername,
  validateEmail,
  validatePassword,
  validatePasswordConfirmation,
  getFirstError,
} from '../../utils/validation';
import { spacing } from '../../theme';

export interface RegisterFormData {
  name: string;
  username: string;
  email: string;
  password: string;
}

export interface RegisterFormProps {
  onSubmit: (data: RegisterFormData) => void;
  isLoading?: boolean;
  errorMessage?: string | null;
}

type TouchedFields = Record<keyof RegisterFormData | 'confirmPassword', boolean>;

export const RegisterForm: React.FC<RegisterFormProps> = ({
  onSubmit,
  isLoading = false,
  errorMessage = null,
}) => {
  // Estado dos campos
  const [name, setName] = useState('');
  const [username, setUsername] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');

  // Controle de interação para exibir erros
  const [touched, setTouched] = useState<Partial<TouchedFields>>({});

  // Referências para encadeamento de foco do teclado
  const usernameRef = useRef<InputRef>(null);
  const emailRef = useRef<InputRef>(null);
  const passwordRef = useRef<InputRef>(null);
  const confirmRef = useRef<InputRef>(null);

  // Computação pura de erros baseada no estado atual
  const errors = {
    name: validateName(name),
    username: validateUsername(username),
    email: validateEmail(email),
    password: validatePassword(password, { requireMinLength: true }),
    confirmPassword: validatePasswordConfirmation(password, confirmPassword),
  };

  const markTouched = (field: keyof TouchedFields) => {
    setTouched((prev) => ({ ...prev, [field]: true }));
  };

  const handleSubmit = () => {
    // Força exibição de todos os erros ao tentar submeter
    setTouched({
      name: true,
      username: true,
      email: true,
      password: true,
      confirmPassword: true,
    });

    const hasValidationError = getFirstError(errors);
    if (hasValidationError || isLoading) {
      return;
    }

    onSubmit({
      name: name.trim(),
      username: username.trim(),
      email: email.trim(),
      password,
    });
  };

  return (
    <View style={styles.container}>
      <ErrorMessage message={errorMessage} />

      <FormField
        label="Nome público"
        placeholder="Seu nome"
        value={name}
        onChangeText={setName}
        onBlur={() => markTouched('name')}
        errorMessage={touched.name ? errors.name : null}
        autoCapitalize="words"
        returnKeyType="next"
        onSubmitEditing={() => usernameRef.current?.focus()}
        blurOnSubmit={false}
      />

      <FormField
        ref={usernameRef}
        label="Identificador"
        placeholder="@seunome"
        value={username}
        onChangeText={setUsername}
        onBlur={() => markTouched('username')}
        errorMessage={touched.username ? errors.username : null}
        autoCapitalize="none"
        autoCorrect={false}
        returnKeyType="next"
        onSubmitEditing={() => emailRef.current?.focus()}
        blurOnSubmit={false}
      />

      <FormField
        ref={emailRef}
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
        onSubmitEditing={() => passwordRef.current?.focus()}
        blurOnSubmit={false}
      />

      <FormField
        ref={passwordRef}
        label="Senha"
        placeholder="Crie uma senha"
        value={password}
        onChangeText={setPassword}
        onBlur={() => markTouched('password')}
        errorMessage={touched.password ? errors.password : null}
        secureTextEntry
        returnKeyType="next"
        onSubmitEditing={() => confirmRef.current?.focus()}
        blurOnSubmit={false}
      />

      <FormField
        ref={confirmRef}
        label="Confirmar senha"
        placeholder="Repita sua senha"
        value={confirmPassword}
        onChangeText={setConfirmPassword}
        onBlur={() => markTouched('confirmPassword')}
        errorMessage={touched.confirmPassword ? errors.confirmPassword : null}
        secureTextEntry
        returnKeyType="done"
        onSubmitEditing={handleSubmit}
      />

      <Button
        title="Criar conta"
        variant="primary"
        isLoading={isLoading}
        onPress={handleSubmit}
        style={styles.submitButton}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    width: '100%',
  },
  submitButton: {
    marginTop: spacing.md,
  },
});