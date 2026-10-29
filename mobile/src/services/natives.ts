// mobile/src/services/natives.ts
import * as ImagePicker from 'expo-image-picker';
import * as Location from 'expo-location';
import * as Notifications from 'expo-notifications';
import { Platform } from 'react-native';

// Interface para retorno padronizado de coordenadas
export interface GeoCoordinates {
  latitude: number;
  longitude: number;
}


// Configura o comportamento das notificações quando o app está aberto em primeiro plano.
export function setupNotificationHandler(): void {
  Notifications.setNotificationHandler({
    handleNotification: async () => ({
      shouldShowBanner: true,
      shouldShowList: true,
      shouldPlaySound: true,
      shouldSetBadge: false,
    }),
  });
}

// Solicita permissão para emissão de notificações
export async function requestNotificationPermission(): Promise<boolean> {
  const { status: existingStatus } = await Notifications.getPermissionsAsync();
  let finalStatus = existingStatus;

  if (existingStatus !== 'granted') {
    const { status } = await Notifications.requestPermissionsAsync();
    finalStatus = status;
  }

  return finalStatus === 'granted';
}

// Dispara uma notificação local agendada.
export async function sendLocalNotification(
  title: string,
  body: string,
  delayInSeconds: number = 2
): Promise<void> {
  const hasPermission = await requestNotificationPermission();
  if (!hasPermission) {
    throw new Error('Permissão de notificações não concedida pelo usuário.');
  }

  await Notifications.scheduleNotificationAsync({
    content: {
      title,
      body,
      sound: true,
    },
    trigger: {
      type: Notifications.SchedulableTriggerInputTypes.TIME_INTERVAL,
      seconds: delayInSeconds,
    },
  });
}


/**
 * Solicita permissão e abre a câmera para captura de foto.
 * Retorna o URI local da imagem capturada ou null se o usuário cancelar.
 */
export async function capturePhoto(): Promise<string | null> {
  // Solicita permissão em tempo de execução
  const permission = await ImagePicker.requestCameraPermissionsAsync();
  if (!permission.granted) {
    throw new Error('Permissão de acesso à câmera negada.');
  }

  const result = await ImagePicker.launchCameraAsync({
    allowsEditing: true,
    aspect: [1, 1], // Quadrado para avatar/perfil
    quality: 0.8,
  });

  if (result.canceled || !result.assets || result.assets.length === 0) {
    return null;
  }

  return result.assets[0].uri;
}

/**
 * Solicita permissão e abre a galeria do aparelho.
 * Retorna o URI da imagem selecionada ou null se cancelar.
 */
export async function pickImageFromGallery(): Promise<string | null> {
  const permission = await ImagePicker.requestMediaLibraryPermissionsAsync();
  if (!permission.granted) {
    throw new Error('Permissão de acesso à galeria negada.');
  }

  const result = await ImagePicker.launchImageLibraryAsync({
    mediaTypes: ImagePicker.MediaTypeOptions.Images,
    allowsEditing: true,
    aspect: [1, 1],
    quality: 0.8,
  });

  if (result.canceled || !result.assets || result.assets.length === 0) {
    return null;
  }

  return result.assets[0].uri;
}

// Solicita permissão e obtém as coordenadas geográficas atuais do aparelho.
export async function getCurrentCoordinates(): Promise<GeoCoordinates> {
  // Solicita permissão apenas enquanto o app estiver em uso (regra da seção 8 do material)
  const { status } = await Location.requestForegroundPermissionsAsync();
  if (status !== 'granted') {
    throw new Error('Permissão de localização negada.');
  }

  const location = await Location.getCurrentPositionAsync({
    accuracy: Location.Accuracy.Balanced,
  });

  return {
    latitude: location.coords.latitude,
    longitude: location.coords.longitude,
  };
}