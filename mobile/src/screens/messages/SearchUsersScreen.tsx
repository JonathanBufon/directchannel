// mobile/src/screens/SearchUsersScreen.tsx
import React, { useState, useCallback } from 'react';
import { View, StyleSheet, FlatList, Pressable, Alert } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { RootNavigationProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Input } from '../../components/atoms/Input';
import { Label } from '../../components/atoms/Label';
import { UserRow, UserRowData } from '../../components/molecules/UserRow';
import { listVirtualizationConfig } from '../../constants/list';
import { colors, spacing } from '../../theme';

// Base de contatos fictícia (compatível com os wireframes e mockups do PDF)
const MOCK_USERS: UserRowData[] = [
  { id: 'usr_marina', name: 'Marina Costa', username: '@marina' },
  { id: 'usr_pedro', name: 'Pedro Lima', username: '@pedro' },
  { id: 'usr_ana', name: 'Ana Souza', username: '@ana' },
  { id: 'usr_lucas', name: 'Lucas Almeida', username: '@lucas' },
];

export const SearchUsersScreen: React.FC = () => {
  const navigation = useNavigation<RootNavigationProp>();
  const [query, setQuery] = useState('');

  // Filtra em memória sem expor e-mails (Requisito RF05)
  const filteredUsers = MOCK_USERS.filter((user) => {
    const cleanQuery = query.trim().toLowerCase();
    if (!cleanQuery) return true;
    return (
      user.name.toLowerCase().includes(cleanQuery) ||
      user.username.toLowerCase().includes(cleanQuery)
    );
  });

  const handleSelectUser = useCallback((user: UserRowData) => {
    navigation.navigate('Chat', {
      userId: user.id,
      name: user.name,
      username: user.username,
    });
  }, [navigation]);

  const renderItem = useCallback(
    ({ item }: { item: UserRowData }) => (
      <UserRow user={item} onPress={handleSelectUser} />
    ),
    [handleSelectUser]
  );

  const keyExtractor = useCallback((item: UserRowData) => item.id, []);

  const renderHeader = () => (
    <View style={styles.header}>
      <Pressable onPress={() => navigation.goBack()} style={styles.backButton}>
        <Label variant="title" style={styles.backIcon}>
          ‹
        </Label>
      </Pressable>
      <View>
        <Label variant="title" style={styles.title}>
          Buscar usuários
        </Label>
        <Label variant="caption" style={styles.subtitle}>
          Seu e-mail não aparece nos resultados
        </Label>
      </View>
    </View>
  );

  return (
    <ScreenTemplate scrollable={false}>
      {renderHeader()}

      <View style={styles.searchContainer}>
        <Input
          placeholder="Buscar por @identificador ou nome"
          value={query}
          onChangeText={setQuery}
          autoCapitalize="none"
          autoCorrect={false}
          returnKeyType="search"
          style={styles.searchInput}
        />
      </View>

      <FlatList
        data={filteredUsers}
        renderItem={renderItem}
        keyExtractor={keyExtractor}
        contentContainerStyle={styles.listContent}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}
        {...listVirtualizationConfig}
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <Label variant="body" style={styles.emptyTitle}>
              Nenhum usuário encontrado
            </Label>
            <Label variant="caption" style={styles.emptySubtitle}>
              Verifique a grafia do @identificador pesquisado.
            </Label>
          </View>
        }
      />
    </ScreenTemplate>
  );
};

const styles = StyleSheet.create({
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  backButton: {
    paddingRight: spacing.md,
    paddingVertical: spacing.xs,
  },
  backIcon: {
    color: colors.text,
    fontSize: 28,
    lineHeight: 32,
  },
  title: {
    fontWeight: '700',
    color: colors.text,
  },
  subtitle: {
    color: colors.textSecondary,
    marginTop: 2,
  },
  searchContainer: {
    marginBottom: spacing.md,
  },
  searchInput: {
    backgroundColor: colors.surface,
  },
  listContent: {
    paddingBottom: spacing.xl,
  },
  emptyContainer: {
    paddingVertical: spacing.xl * 2,
    alignItems: 'center',
  },
  emptyTitle: {
    fontWeight: '600',
    marginBottom: spacing.xs,
    color: colors.text,
  },
  emptySubtitle: {
    color: colors.textSecondary,
    textAlign: 'center',
  },
});