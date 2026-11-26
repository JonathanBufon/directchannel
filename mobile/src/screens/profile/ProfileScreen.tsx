// mobile/src/screens/profile/ProfileScreen.tsx
import React, { useState, useCallback } from 'react';
import { View, StyleSheet, Pressable, Image, Alert } from 'react-native';
import { useNavigation, useFocusEffect } from '@react-navigation/native';
import { RootNavigationProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Label } from '../../components/atoms/Label';
import { colors, spacing, radii, dimensions } from '../../theme';
import { getUserProfile, clearUserSession, UserProfile } from '../../services/storage';

export const ProfileScreen: React.FC = () => {
  const navigation = useNavigation<RootNavigationProp>();
  const [user, setUser] = useState<UserProfile | null>(null);

  // Recarrega os dados do AsyncStorage sempre que a tela ganha foco
  useFocusEffect(
    useCallback(() => {
      async function loadProfile() {
        const storedProfile = await getUserProfile();
        if (storedProfile) {
          setUser(storedProfile);
        }
      }
      loadProfile();
    }, [])
  );

  const handleLogout = async () => {
    Alert.alert('Sair da conta', 'Deseja realmente encerrar sua sessão neste aparelho?', [
      { text: 'Cancelar', style: 'cancel' },
      {
        text: 'Sair',
        style: 'destructive',
        onPress: async () => {
          await clearUserSession();
          navigation.replace('Login');
        },
      },
    ]);
  };

  const initial = user?.name ? user.name.slice(0, 2).toUpperCase() : 'SP';

  return (
    <ScreenTemplate scrollable>
      {/* Título Principal */}
      <View style={styles.header}>
        <Label variant="title" style={styles.headerTitle}>
          Perfil
        </Label>
      </View>

      {/* Card Principal do Usuário (Sem exibir e-mail conforme RF03/RF04) */}
      <View style={styles.profileSummary}>
        <View style={styles.avatar}>
          {user?.avatarUri ? (
            <Image source={{ uri: user.avatarUri }} style={styles.avatarImage} />
          ) : (
            <Label variant="title" style={styles.avatarText}>
              {initial}
            </Label>
          )}
        </View>

        <Label variant="title" style={styles.name}>
          {user?.name ?? 'Seu perfil'}
        </Label>
        <Label variant="caption" style={styles.username}>
          {user?.username ?? '@seuusuario'}
        </Label>
      </View>

      {/* Lista de Opções Institucionais (Wireframe T16) */}
      <View style={styles.menuCard}>
        {/* 1. Editar Perfil (T17) */}
        <Pressable
          style={styles.menuRow}
          onPress={() => navigation.navigate('EditProfile')}
        >
          <Label variant="body" style={styles.menuText}>
            Editar perfil
          </Label>
          <Label variant="title" style={styles.chevron}>
            ›
          </Label>
        </Pressable>
        {/* 2. Privacidade (T18) */}   
        <Pressable
            style={styles.menuRow}
            onPress={() => navigation.navigate('Privacy')}
        >
            <Label variant="body" style={styles.menuText}>
                Privacidade
            </Label>
            <Label variant="title" style={styles.chevron}>
                ›
            </Label>
        </Pressable>
        {/* 3. Contatos Bloqueados (T19) */}
        <Pressable
            style={styles.menuRow}
            onPress={() => navigation.navigate('BlockedContacts')}
        >
            <Label variant="body" style={styles.menuText}>
                Contatos bloqueados
            </Label>
            <Label variant="title" style={styles.chevron}>
                ›
            </Label>
        </Pressable>

        {/* 4. Sair da Conta (Logout Atômico) */}
        <Pressable style={[styles.menuRow, styles.lastRow]} onPress={handleLogout}>
          <Label variant="body" style={styles.logoutText}>
            Sair da conta
          </Label>
          <Label variant="title" style={styles.logoutChevron}>
            ›
          </Label>
        </Pressable>
      </View>
    </ScreenTemplate>
  );
};

const styles = StyleSheet.create({
  header: {
    marginBottom: spacing.lg,
  },
  headerTitle: {
    fontWeight: '700',
    color: colors.text,
  },
  profileSummary: {
    alignItems: 'center',
    marginBottom: spacing.xl,
  },
  avatar: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: colors.protection,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: spacing.sm,
    overflow: 'hidden',
  },
  avatarImage: {
    width: '100%',
    height: '100%',
  },
  avatarText: {
    fontSize: 28,
    color: colors.primary,
    fontWeight: '700',
  },
  name: {
    fontWeight: '700',
    color: colors.text,
  },
  username: {
    color: colors.textSecondary,
    marginTop: spacing.xs,
  },
  menuCard: {
    backgroundColor: colors.surface,
    borderRadius: radii.md,
    borderWidth: 1,
    borderColor: colors.border,
    overflow: 'hidden',
  },
  menuRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.md,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
    minHeight: dimensions.minTouchArea,
  },
  lastRow: {
    borderBottomWidth: 0,
  },
  menuText: {
    fontWeight: '500',
    color: colors.text,
  },
  chevron: {
    color: colors.textSecondary,
    fontSize: 22,
  },
  logoutText: {
    fontWeight: '600',
    color: colors.error,
  },
  logoutChevron: {
    color: colors.error,
    fontSize: 22,
  },
});