
// Expressão regular de e-mail compatível com a especificação das aulas
export const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

// Limites numéricos para validação de campos
export const LIMITS = {
  nameMin: 2,
  nameMax: 80,
  passwordMin: 6,
} as const;

interface PasswordValidationOptions {
  requireMinLength?: boolean;
}

/**
 * Valida o campo de e-mail.
 * Retorna mensagem de erro ou null se o valor for válido.
 */
export function validateEmail(value: string | undefined | null): string | null {
  const email = String(value ?? '').trim();

  if (!email) {
    return 'Informe o e-mail.';
  }

  if (!EMAIL_REGEX.test(email)) {
    return 'E-mail inválido. Use o formato nome@dominio.com.';
  }

  return null;
}

/**
 * Valida o campo de senha.
 * Para login simples, exige apenas preenchimento.
 * Para cadastro, valide com requireMinLength: true.
 */
export function validatePassword(
  value: string | undefined | null,
  { requireMinLength = false }: PasswordValidationOptions = {}
): string | null {
  const password = String(value ?? '');

  if (!password) {
    return 'Informe a senha.';
  }

  if (requireMinLength && password.length < LIMITS.passwordMin) {
    return `A senha deve ter pelo menos ${LIMITS.passwordMin} caracteres.`;
  }

  return null;
}

/**
 * Valida o nome de exibição ou identificador.
 */
export function validateName(value: string | undefined | null): string | null {
  const name = String(value ?? '').trim();

  if (!name) {
    return 'Informe o nome.';
  }

  if (name.length < LIMITS.nameMin || name.length > LIMITS.nameMax) {
    return `O nome deve ter entre ${LIMITS.nameMin} e ${LIMITS.nameMax} caracteres.`;
  }

  return null;
}

/**
 * Confirma se dois campos de senha coincidem.
 */
export function validatePasswordConfirmation(password: string, confirmation: string): string | null {
  if (!confirmation) {
    return 'Confirme a senha.';
  }

  if (password !== confirmation) {
    return 'As senhas não conferem.';
  }

  return null;
}

/**
 * Percorre o mapa de erros de um formulário e retorna o primeiro erro encontrado.
 * Retorna null se nenhum campo tiver erro.
 */
export function getFirstError(errorMap: Record<string, string | null>): string | null {
  const errors = Object.values(errorMap);
  return errors.find((error): error is string => Boolean(error)) ?? null;
}

/**
 * Valida o identificador único (username).
 * Exige que inicie com '@' e não contenha espaços.
 */
export function validateUsername(value: string | undefined | null): string | null {
  const username = String(value ?? '').trim();

  if (!username) {
    return 'Informe o identificador.';
  }

  if (!username.startsWith('@')) {
    return 'O identificador deve começar com @.';
  }

  if (/\s/.test(username)) {
    return 'O identificador não pode conter espaços.';
  }

  return null;
}