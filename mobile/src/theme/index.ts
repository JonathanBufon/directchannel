// Paleta de cores oficial baseada na documentação de UX/UI e materiais da disciplina
export const colors = {

  primary: '#087C80',        // Verde-petróleo (ação primária)
  background: '#F8FAFA',     // Superfície geral do aplicativo
  surface: '#FFFFFF',        // Fundo de cards e campos de entrada
  protection: '#E9F4F2',    // Destaque de segurança/criptografia
  text: '#172E3B',           // Texto principal escuro
  textSecondary: '#6A7D87',  // Rótulos secundários e placeholders

  // Estados semânticos e bordas
  border: '#D0DCDC',
  borderFocus: '#087C80',
  error: '#C0392B',          // Mensagens de erro e borda de alerta
  success: '#1E8E5A',
  disabled: '#B8C6C6',
} as const;

export const spacing = {
  xs: 4,
  sm: 8,
  md: 16,
  lg: 24,
  xl: 32,
} as const;

export const radii = {
  xs: 2,
  sm: 6,
  md: 8,
  lg: 16,
  full: 9999,
} as const;

export const typography = {
  title: 24,
  subtitle: 18,
  body: 16,
  caption: 13,
} as const;


export const dimensions = {
  buttonHeight: 48,
  inputHeight: 48,
  minTouchArea: 44,
} as const;