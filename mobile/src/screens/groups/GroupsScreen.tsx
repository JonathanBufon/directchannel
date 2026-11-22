// mobile/src/screens/groups/GroupsScreen.tsx
import React, { useState, useCallback } from 'react';
import { View, StyleSheet, FlatList } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { RootNavigationProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Label } from '../../components/atoms/Label';
import { Button } from '../../components/atoms/Button';
import { GroupRow, GroupData } from '../../components/molecules/GroupRow';
import { listVirtualizationConfig } from '../../constants/list';
import { colors, spacing, radii } from '../../theme';

// Dados iniciais com o grupo especificado no PDF (Pág. 10 e 24)
const INITIAL_GROUPS: GroupData[] = [
    {
        id: 'grp_equipe_projeto',
        name: 'Equipe do projeto',
        memberCount: 4,
    },
];

export const GroupsScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();
    const [groups] = useState<GroupData[]>(INITIAL_GROUPS);

    const handleOpenGroup = useCallback(
        (group: GroupData) => {
            navigation.navigate('GroupChat', {
                groupId: group.id,
                groupName: group.name,
                memberCount: group.memberCount,
            });
        },
        [navigation]
    );

    const renderItem = useCallback(
        ({ item }: { item: GroupData }) => (
            <GroupRow group={item} onPress={handleOpenGroup} />
        ),
        [handleOpenGroup]
    );

    const keyExtractor = useCallback((item: GroupData) => item.id, []);

    return (
        <ScreenTemplate scrollable={false}>
            {/* Título Oficial */}
            <View style={styles.header}>
                <Label variant="title" style={styles.headerTitle}>
                    Grupos
                </Label>
                <Label variant="caption" style={styles.headerSubtitle}>
                    As mensagens dos grupos também são protegidas de ponta a ponta
                </Label>
            </View>

            <View style={styles.securityBadge}>
                <Label variant="caption" style={styles.securityText}>
                    🔒 Criptografia com rotação de chaves por participante
                </Label>
            </View>

            {/* Lista de Grupos Virtualizada */}
            <FlatList
                data={groups}
                renderItem={renderItem}
                keyExtractor={keyExtractor}
                contentContainerStyle={styles.listContent}
                {...listVirtualizationConfig}
                ListEmptyComponent={
                    <View style={styles.emptyContainer}>
                        <Label variant="body" style={styles.emptyTitle}>
                            Você não participa de nenhum grupo
                        </Label>
                        <Label variant="caption" style={styles.emptySubtitle}>
                            Crie um novo grupo para trocar mensagens e arquivos criptografados.
                        </Label>
                    </View>
                }
            />

            {/* Botão de Criação de Grupo (T11 Wireframe) */}
            <View style={styles.footerContainer}>
                <Button
                    title="Criar grupo"
                    variant="primary"
                    onPress={() => navigation.navigate('CreateGroup')}
                    style={styles.createButton}
                />
            </View>
        </ScreenTemplate>
    );
};

const styles = StyleSheet.create({
    header: {
        marginBottom: spacing.md,
    },
    headerTitle: {
        fontWeight: '700',
        color: colors.text,
    },
    headerSubtitle: {
        color: colors.textSecondary,
        marginTop: spacing.xs,
    },
    securityBadge: {
        backgroundColor: colors.protection,
        padding: spacing.md,
        borderRadius: radii.sm,
        marginBottom: spacing.md,
    },
    securityText: {
        color: colors.primary,
        fontWeight: '600',
        textAlign: 'center',
    },
    listContent: {
        flexGrow: 1,
    },
    emptyContainer: {
        paddingVertical: spacing.xl * 2,
        alignItems: 'center',
    },
    emptyTitle: {
        fontWeight: '600',
        marginBottom: spacing.xs,
    },
    emptySubtitle: {
        textAlign: 'center',
        color: colors.textSecondary,
    },
    footerContainer: {
        paddingVertical: spacing.md,
    },
    createButton: {
        width: '100%',
    },
});