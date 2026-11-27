// mobile/src/screens/admin/AdminUsersScreen.tsx
import React, { useState } from 'react';
import { View, StyleSheet, Pressable, FlatList } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { RootNavigationProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Label } from '../../components/atoms/Label';
import { Input } from '../../components/atoms/Input';
import { colors, spacing, radii, dimensions } from '../../theme';

interface AdminUserItem {
    id: string;
    name: string;
    username: string;
    status: 'Ativa' | 'Bloqueada';
}

const INITIAL_ADMIN_USERS: AdminUserItem[] = [
    { id: 'usr_cd', name: 'Conta de exemplo', username: '@contaexemplo', status: 'Ativa' },
    { id: 'usr_marina', name: 'Marina Costa', username: '@marina', status: 'Ativa' },
    { id: 'usr_pedro', name: 'Pedro Lima', username: '@pedro', status: 'Ativa' },
    { id: 'usr_spammer', name: 'Spam Bot 01', username: '@spambot', status: 'Bloqueada' },
];

export const AdminUsersScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();
    const [search, setSearch] = useState('');
    const [users] = useState<AdminUserItem[]>(INITIAL_ADMIN_USERS);

    const filteredUsers = users.filter(
        (u) =>
            u.name.toLowerCase().includes(search.toLowerCase()) ||
            u.username.toLowerCase().includes(search.toLowerCase())
    );

    return (
        <ScreenTemplate scrollable={false}>
            {/* Cabeçalho */}
            <View style={styles.header}>
                <Pressable onPress={() => navigation.goBack()} style={styles.backButton}>
                    <Label variant="title" style={styles.backIcon}>
                        ‹
                    </Label>
                </Pressable>
                <Label variant="title" style={styles.headerTitle}>
                    Usuários
                </Label>
            </View>

            {/* Campo de Busca por Identificador */}
            <View style={styles.searchBox}>
                <Label variant="caption" style={styles.searchLabel}>
                    Buscar usuário
                </Label>
                <Input
                    placeholder="Nome ou identificador"
                    value={search}
                    onChangeText={setSearch}
                    autoCapitalize="none"
                    style={styles.searchInput}
                />
            </View>

            {/* Lista de Contas */}
            <FlatList
                data={filteredUsers}
                keyExtractor={(item) => item.id}
                contentContainerStyle={styles.listContent}
                renderItem={({ item }) => {
                    const isBlocked = item.status === 'Bloqueada';
                    const initials = item.name.slice(0, 2).toUpperCase();

                    return (
                        <Pressable
                            style={styles.userCard}
                            onPress={() =>
                                navigation.navigate('AdminManageAccount', {
                                    userId: item.id,
                                    name: item.name,
                                    username: item.username,
                                    initialStatus: item.status,
                                })
                            }
                        >
                            <View style={styles.avatar}>
                                <Label variant="body" style={styles.avatarText}>
                                    {initials}
                                </Label>
                            </View>

                            <View style={styles.userInfo}>
                                <Label variant="body" style={styles.userName}>
                                    {item.name}
                                </Label>
                                <Label variant="caption" style={styles.userHandle}>
                                    {item.username}
                                </Label>
                            </View>

                            {/* Status Badge */}
                            <View
                                style={[
                                    styles.statusBadge,
                                    isBlocked ? styles.statusBadgeBlocked : styles.statusBadgeActive,
                                ]}
                            >
                                <Label
                                    variant="caption"
                                    style={isBlocked ? styles.statusTextBlocked : styles.statusTextActive}
                                >
                                    {item.status}
                                </Label>
                            </View>

                            <Label variant="title" style={styles.chevron}>
                                ›
                            </Label>
                        </Pressable>
                    );
                }}
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
    headerTitle: {
        fontWeight: '700',
        color: colors.text,
    },
    searchBox: {
        marginBottom: spacing.md,
    },
    searchLabel: {
        fontWeight: '600',
        color: colors.textSecondary,
        marginBottom: spacing.xs,
    },
    searchInput: {
        backgroundColor: colors.surface,
    },
    listContent: {
        paddingBottom: spacing.xl,
    },
    userCard: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: colors.surface,
        padding: spacing.md,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: colors.border,
        marginBottom: spacing.sm,
        minHeight: dimensions.minTouchArea,
    },
    avatar: {
        width: 44,
        height: 44,
        borderRadius: 22,
        backgroundColor: colors.protection,
        alignItems: 'center',
        justifyContent: 'center',
        marginRight: spacing.md,
    },
    avatarText: {
        color: colors.primary,
        fontWeight: '700',
    },
    userInfo: {
        flex: 1,
    },
    userName: {
        fontWeight: '600',
        color: colors.text,
    },
    userHandle: {
        color: colors.textSecondary,
        marginTop: 2,
    },
    statusBadge: {
        paddingHorizontal: spacing.sm,
        paddingVertical: 4,
        borderRadius: radii.sm,
        marginRight: spacing.sm,
    },
    statusBadgeActive: {
        backgroundColor: '#E8F8F5',
    },
    statusBadgeBlocked: {
        backgroundColor: '#FDEDEC',
    },
    statusTextActive: {
        color: colors.success,
        fontWeight: '600',
        fontSize: 11,
    },
    statusTextBlocked: {
        color: colors.error,
        fontWeight: '600',
        fontSize: 11,
    },
    chevron: {
        color: colors.textSecondary,
        fontSize: 22,
    },
});