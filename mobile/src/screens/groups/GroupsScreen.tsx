// mobile/src/screens/GroupsScreen.tsx
import React from 'react';
import { View, StyleSheet } from 'react-native';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Label } from '../../components/atoms/Label';
import { Button } from '../../components/atoms/Button';
import { colors, spacing, radii } from '../../theme';

export const GroupsScreen: React.FC = () => {
  return (
    <ScreenTemplate
      title="Grupos"
      subtitle="Comunicação segura para equipes"
    >
      <View style={styles.securityBadge}>
        <Label variant="caption" style={styles.securityText}>
          🔒 Criptografia com rotação de chaves por participante
        </Label>
      </View>

      <View style={styles.emptyContainer}>
        <Label variant="body" style={styles.emptyTitle}>
          Você não participa de nenhum grupo
        </Label>
        <Label variant="caption" style={styles.emptySubtitle}>
          Crie um novo grupo para trocar mensagens e arquivos criptografados.
        </Label>

        <Button
          title="Criar grupo"
          variant="primary"
          onPress={() => {}}
          style={styles.actionButton}
        />
      </View>
    </ScreenTemplate>
  );
};

const styles = StyleSheet.create({
  securityBadge: {
    backgroundColor: colors.protection,
    padding: spacing.md,
    borderRadius: radii.sm,
    marginBottom: spacing.lg,
  },
  securityText: {
    color: colors.primary,
    fontWeight: '600',
    textAlign: 'center',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingVertical: spacing.xl * 2,
  },
  emptyTitle: {
    fontWeight: '600',
    marginBottom: spacing.xs,
  },
  emptySubtitle: {
    textAlign: 'center',
    marginBottom: spacing.lg,
    paddingHorizontal: spacing.md,
  },
  actionButton: {
    width: '100%',
  },
});