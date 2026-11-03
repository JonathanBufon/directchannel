// mobile/src/screens/ProfileScreen.tsx
import React, { useEffect, useState } from 'react';
import { View, StyleSheet, ScrollView, Image, Alert } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { RootNavigationProp } from '../../types/navigation';
import { Label } from '../../components/atoms/Label';
import { Button } from '../../components/atoms/Button';
import { colors, spacing, radii } from '../../theme';
import {
  getAuthToken,
  getUserProfile,
  saveUserProfile,
  clearUserSession,
  UserProfile,
} from '../../services/storage';
import {
  capturePhoto,
  pickImageFromGallery,
  getCurrentCoordinates,
  sendLocalNotification,
  GeoCoordinates,
} from '../../services/natives';

export const ProfileScreen: React.FC = () => {
  const navigation = useNavigation<RootNavigationProp>();

  // Estados locais da tela
  const [user, setUser] = useState<UserProfile | null>(null);
  const [tokenPreview, setTokenPreview] = useState<string>('Carregando...');
  const [coordinates, setCoordinates] = useState<GeoCoordinates | null>(null);
  const [isLoadingGps, setIsLoadingGps] = useState(false);
  const [isLoadingPhoto, setIsLoadingPhoto] = useState(false);

  // Carrega dados da gaveta segura (Cofre) e do AsyncStorage ao montar
  useEffect(() => {
    async function loadStoredData() {
      const token = await getAuthToken();
      setTokenPreview(token ? `${token.substring(0, 15)}... (Protegido no Cofre)` : 'Nenhum token encontrado');

      const profile = await getUserProfile();
      if (profile) {
        setUser(profile);
      }
    }

    loadStoredData();
  }, []);

  // 1. Recurso Nativo: Câmera
  const handleTakePhoto = async () => {
    try {
      setIsLoadingPhoto(true);
      const uri = await capturePhoto();
      if (uri && user) {
        const updatedProfile = { ...user, avatarUri: uri };
        setUser(updatedProfile);
        await saveUserProfile(updatedProfile); // Persiste a URI no AsyncStorage
      }
    } catch (error) {
      Alert.alert('Erro na Câmera', (error as Error).message);
    } finally {
      setIsLoadingPhoto(false);
    }
  };

  // 2. Recurso Nativo: Galeria
  const handlePickGallery = async () => {
    try {
      setIsLoadingPhoto(true);
      const uri = await pickImageFromGallery();
      if (uri && user) {
        const updatedProfile = { ...user, avatarUri: uri };
        setUser(updatedProfile);
        await saveUserProfile(updatedProfile);
      }
    } catch (error) {
      Alert.alert('Erro na Galeria', (error as Error).message);
    } finally {
      setIsLoadingPhoto(false);
    }
  };

  // 3. Recurso Nativo: GPS / Geolocalização
  const handleGetLocation = async () => {
    try {
      setIsLoadingGps(true);
      const coords = await getCurrentCoordinates();
      setCoordinates(coords);
    } catch (error) {
      Alert.alert('Erro de Localização', (error as Error).message);
    } finally {
      setIsLoadingGps(false);
    }
  };

  // 4. Recurso Nativo: Notificação Local
  const handleSendNotification = async () => {
    try {
      await sendLocalNotification(
        'DirectChannel: Alerta de Segurança',
        'Seu dispositivo foi verificado e as chaves estão ativas.',
        2
      );
      Alert.alert('Notificação Agendada', 'O aviso chegará em 2 segundos.');
    } catch (error) {
      Alert.alert('Erro na Notificação', (error as Error).message);
    }
  };

  // 5. Logout Seguro (Limpeza de Cofre e Reset)
  const handleLogout = async () => {
    await clearUserSession();
    // navigation.replace garante que a tela interna saia da pilha de navegação
    navigation.replace('Login');
  };

  return (
    <ScrollView contentContainerStyle={styles.container}>
      {/* Bloco de Perfil e Avatar */}
      <View style={styles.card}>
        <View style={styles.avatarContainer}>
          {user?.avatarUri ? (
            <Image source={{ uri: user.avatarUri }} style={styles.avatarImage} />
          ) : (
            <View style={styles.avatarPlaceholder}>
              <Label variant="title" style={styles.avatarText}>
                {user?.name ? user.name.charAt(0).toUpperCase() : 'U'}
              </Label>
            </View>
          )}
        </View>

        <Label variant="title" style={styles.userName}>{user?.name ?? 'Usuário'}</Label>
        <Label variant="caption">{user?.username ?? '@identificador'}</Label>
        <Label variant="caption">{user?.email ?? 'email@exemplo.com'}</Label>

        {/* Botões de Ação para Câmera / Galeria */}
        <View style={styles.buttonRow}>
          <Button
            title="Tirar Foto"
            variant="secondary"
            isLoading={isLoadingPhoto}
            onPress={handleTakePhoto}
            style={styles.halfButton}
          />
          <Button
            title="Galeria"
            variant="secondary"
            isLoading={isLoadingPhoto}
            onPress={handlePickGallery}
            style={styles.halfButton}
          />
        </View>
      </View>

      {/* Bloco de Persistência Segura (Demonstração do Cofre) */}
      <View style={styles.card}>
        <Label variant="subtitle" style={styles.sectionTitle}>
          Cofre Seguro (SecureStore)
        </Label>
        <Label variant="caption">Token JWT autenticado em memória segura:</Label>
        <View style={styles.tokenBox}>
          <Label variant="caption" style={styles.tokenText}>{tokenPreview}</Label>
        </View>
      </View>

      {/* Bloco de Recursos Nativos: GPS e Notificação */}
      <View style={styles.card}>
        <Label variant="subtitle" style={styles.sectionTitle}>
          Hardware e Permissões
        </Label>

        <Button
          title={isLoadingGps ? 'Obtendo GPS...' : 'Obter Posição GPS'}
          variant="secondary"
          isLoading={isLoadingGps}
          onPress={handleGetLocation}
          style={styles.actionButton}
        />

        {coordinates ? (
          <View style={styles.gpsResult}>
            <Label variant="body">Lat: {coordinates.latitude.toFixed(6)}</Label>
            <Label variant="body">Long: {coordinates.longitude.toFixed(6)}</Label>
          </View>
        ) : null}

        <Button
          title="Disparar Notificação Local"
          variant="secondary"
          onPress={handleSendNotification}
          style={styles.actionButton}
        />
      </View>

      {/* Logout Atômico */}
      <Button
        title="Encerrar Sessão (Logout)"
        variant="danger"
        onPress={handleLogout}
        style={styles.logoutButton}
      />
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    padding: spacing.lg,
    backgroundColor: colors.background,
  },
  card: {
    backgroundColor: colors.surface,
    padding: spacing.lg,
    borderRadius: radii.md,
    borderWidth: 1,
    borderColor: colors.border,
    marginBottom: spacing.md,
    alignItems: 'center',
  },
  avatarContainer: {
    marginBottom: spacing.md,
  },
  avatarImage: {
    width: 90,
    height: 90,
    borderRadius: 45,
  },
  avatarPlaceholder: {
    width: 90,
    height: 90,
    borderRadius: 45,
    backgroundColor: colors.protection,
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarText: {
    color: colors.primary,
    fontSize: 32,
    fontWeight: '700',
  },
  userName: {
    marginBottom: spacing.xs,
  },
  buttonRow: {
    flexDirection: 'row',
    marginTop: spacing.md,
    gap: spacing.sm,
    width: '100%',
  },
  halfButton: {
    flex: 1,
  },
  sectionTitle: {
    marginBottom: spacing.sm,
    alignSelf: 'flex-start',
  },
  tokenBox: {
    backgroundColor: colors.protection,
    padding: spacing.sm,
    borderRadius: radii.sm,
    marginTop: spacing.xs,
    width: '100%',
  },
  tokenText: {
    color: colors.primary,
    fontWeight: '600',
  },
  actionButton: {
    width: '100%',
    marginBottom: spacing.sm,
  },
  gpsResult: {
    backgroundColor: colors.background,
    padding: spacing.sm,
    borderRadius: radii.sm,
    width: '100%',
    marginBottom: spacing.sm,
    alignItems: 'center',
  },
  logoutButton: {
    marginTop: spacing.md,
    marginBottom: spacing.xl,
  },
});