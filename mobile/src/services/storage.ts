// mobile/src/services/storage.ts
import * as SecureStore from 'expo-secure-store';
import AsyncStorage from '@react-native-async-storage/async-storage';

const STORAGE_KEYS = {
  AUTH_TOKEN: 'directchannel_auth_token',
  USER_PROFILE: 'directchannel_user_profile',
  PREFERENCES_PREFIX: 'directchannel_pref_',
} as const;

export interface UserProfile {
  id: string;
  name: string;
  username: string;
  email: string;
  avatarUri?: string | null;
}

// Salva o token de autenticação
export async function saveAuthToken(token: string): Promise<void> {
  await SecureStore.setItemAsync(STORAGE_KEYS.AUTH_TOKEN, token, {
    keychainAccessible: SecureStore.WHEN_UNLOCKED_THIS_DEVICE_ONLY,
  });
}

//Recupera o token de autenticação
export async function getAuthToken(): Promise<string | null> {
  return await SecureStore.getItemAsync(STORAGE_KEYS.AUTH_TOKEN);
}

//Remove o token
export async function removeAuthToken(): Promise<void> {
  await SecureStore.deleteItemAsync(STORAGE_KEYS.AUTH_TOKEN);
}

// Salva os dados públicos do perfil do usuário no AsyncStorage.
export async function saveUserProfile(profile: UserProfile): Promise<void> {
  const jsonValue = JSON.stringify(profile);
  await AsyncStorage.setItem(STORAGE_KEYS.USER_PROFILE, jsonValue);
}

// Recupera o perfil do usuário do AsyncStorage.
export async function getUserProfile(): Promise<UserProfile | null> {
  const jsonValue = await AsyncStorage.getItem(STORAGE_KEYS.USER_PROFILE);
  if (!jsonValue) {
    return null;
  }
  try {
    return JSON.parse(jsonValue) as UserProfile;
  } catch {
    return null;
  }
}

// Remove os dados de perfil do AsyncStorage.
export async function removeUserProfile(): Promise<void> {
  await AsyncStorage.removeItem(STORAGE_KEYS.USER_PROFILE);
}

// Salva preferências gerais do aplicativo (ex.: notificações ativas).
export async function savePreference<T>(key: string, value: T): Promise<void> {
  const jsonValue = JSON.stringify(value);
  await AsyncStorage.setItem(`${STORAGE_KEYS.PREFERENCES_PREFIX}${key}`, jsonValue);
}

// Recupera preferência com suporte a valor padrão.
export async function getPreference<T>(key: string, defaultValue: T): Promise<T> {
  const jsonValue = await AsyncStorage.getItem(`${STORAGE_KEYS.PREFERENCES_PREFIX}${key}`);
  if (jsonValue === null) {
    return defaultValue;
  }
  try {
    return JSON.parse(jsonValue) as T;
  } catch {
    return defaultValue;
  }
}


/**
 * Limpa o cofre e os dados de sessão do usuário simultaneamente.
 * Mantém apenas as preferências gerais do aparelho.
 */
export async function clearUserSession(): Promise<void> {
  await Promise.all([
    removeAuthToken(),
    removeUserProfile(),
  ]);
}