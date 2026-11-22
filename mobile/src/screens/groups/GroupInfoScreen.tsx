// mobile/src/screens/groups/GroupInfoScreen.tsx
import React from 'react';
import { View, StyleSheet, Pressable } from 'react-native';
import { useNavigation, useRoute } from '@react-navigation/native';
import { RootNavigationProp, GroupInfoRouteProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Label } from '../../components/atoms/Label';
import { colors, spacing, radii, dimensions } from '../../theme';

// Lista de membros conforme wireframe T14 (Pág. 11 e 26)
const MEMBERS = [
    { id: 'usr_me', name: 'Você', role: 'administrador' },
    { id: 'usr_marina', name: 'Marina Costa', role: 'membro' },
    { id: 'usr_pedro', name: 'Pedro Lima', role: 'membro' },
    { id: 'usr_ana', name: 'Ana Souza', role: 'membro' },
];

export const GroupInfoScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();
    const route = useRoute<GroupInfoRouteProp>();
    const { groupId, groupName, memberCount, isAdmin = true } = route.params;

    // Extrai iniciais do grupo
    const words = groupName.trim().split(' ');
    const initials = words.length > 1
        ? `${words[0].charAt(0)}${words[1].charAt(0)}`.toUpperCase()
        : groupName.slice(0, 2).toUpperCase();

    return (
        <ScreenTemplate scrollable>
            {/* Cabeçalho */}
            <View style={styles.header}>
                <Pressable onPress={() => navigation.goBack()} style={styles.backButton}>
                    <Label variant="title" style={styles.backIcon}>
                        ‹
                    </Label>
                </Pressable>
                <Label variant="title" style={styles.headerTitle}>
                    Informações do grupo
                </Label>
            </View>

            {/* Card de Identificação */}
            <View style={styles.profileCard}>
                <View style={styles.avatar}>
                    <Label variant="title" style={styles.avatarText}>
                        {initials}
                    </Label>
                </View>

                <Label variant="title" style={styles.name}>
                    {groupName}
                </Label>
                <Label variant="caption" style={styles.meta}>
                    {memberCount} participantes
                </Label>
            </View>

            {/* Ação Administrativa: Alterar nome e participantes (Apenas Administrador) */}
            {isAdmin ? (
                <Pressable
                    style={styles.adminActionCard}
                    onPress={() => navigation.navigate('ManageGroup', { groupId, groupName })}
                >
                    <Label variant="body" style={styles.adminActionText}>
                        Alterar nome e participantes
                    </Label>
                    <Label variant="title" style={styles.chevron}>
                        ›
                    </Label>
                </Pressable>
            ) : null}

            {/* Lista de Membros */}
            <View style={styles.membersCard}>
                <Label variant="caption" style={styles.membersHeader}>
                    PARTICIPANTES ({MEMBERS.length})
                </Label>

                {MEMBERS.map((member, index) => (
                    <View
                        key={member.id}
                        style={[
                            styles.memberRow,
                            index === MEMBERS.length - 1 ? styles.lastRow : undefined,
                        ]}
                    >
                        <View style={styles.memberAvatar}>
                            <Label variant="caption" style={styles.memberAvatarText}>
                                {member.name.charAt(0).toUpperCase()}
                            </Label>
                        </View>

                        <View style={styles.memberInfo}>
                            <Label variant="body" style={styles.memberName}>
                                {member.name}
                            </Label>
                            {member.role === 'administrador' ? (
                                <Label variant="caption" style={styles.adminBadge}>
                                    administrador
                                </Label>
                            ) : null}
                        </View>
                    </View>
                ))}
            </View>

            {/* Aviso de Isolamento Criptográfico (RF15 / Wireframe T14) */}
            <View style={styles.securityBox}>
                <Label variant="caption" style={styles.securityText}>
                    🔒 Novos participantes não recebem o histórico anterior à sua entrada.
                </Label>
            </View>

            {/* Ação de Saída do Grupo (Leva à T24) */}
            <Pressable
                style={styles.leaveButton}
                onPress={() => navigation.navigate('LeaveGroup', { groupId, groupName })}
            >
                <Label variant="body" style={styles.leaveText}>
                    Sair do grupo
                </Label>
                <Label variant="title" style={styles.leaveChevron}>
                    ›
                </Label>
            </Pressable>
        </ScreenTemplate>
    );
};

const styles = StyleSheet.create({
    header: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: spacing.lg,
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
    profileCard: {
        backgroundColor: colors.surface,
        padding: spacing.xl,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: colors.border,
        alignItems: 'center',
        marginBottom: spacing.md,
    },
    avatar: {
        width: 72,
        height: 72,
        borderRadius: 36,
        backgroundColor: colors.protection,
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: spacing.md,
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
    meta: {
        color: colors.textSecondary,
        marginTop: spacing.xs,
    },
    adminActionCard: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        backgroundColor: colors.surface,
        padding: spacing.md,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: colors.border,
        marginBottom: spacing.md,
        minHeight: dimensions.minTouchArea,
    },
    adminActionText: {
        fontWeight: '600',
        color: colors.primary,
    },
    chevron: {
        color: colors.primary,
        fontSize: 22,
    },
    membersCard: {
        backgroundColor: colors.surface,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: colors.border,
        padding: spacing.md,
        marginBottom: spacing.md,
    },
    membersHeader: {
        color: colors.textSecondary,
        fontWeight: '700',
        letterSpacing: 1,
        marginBottom: spacing.sm,
    },
    memberRow: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingVertical: spacing.sm,
        borderBottomWidth: 1,
        borderBottomColor: colors.border,
    },
    lastRow: {
        borderBottomWidth: 0,
    },
    memberAvatar: {
        width: 36,
        height: 36,
        borderRadius: 18,
        backgroundColor: colors.protection,
        alignItems: 'center',
        justifyContent: 'center',
        marginRight: spacing.md,
    },
    memberAvatarText: {
        color: colors.primary,
        fontWeight: '700',
    },
    memberInfo: {
        flex: 1,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
    },
    memberName: {
        fontWeight: '500',
        color: colors.text,
    },
    adminBadge: {
        color: colors.primary,
        fontWeight: '600',
        fontSize: 12,
    },
    securityBox: {
        backgroundColor: colors.protection,
        padding: spacing.md,
        borderRadius: radii.sm,
        marginBottom: spacing.lg,
    },
    securityText: {
        color: colors.primary,
        textAlign: 'center',
        lineHeight: 18,
    },
    leaveButton: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        backgroundColor: colors.surface,
        padding: spacing.md,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: '#FADBD8',
        marginBottom: spacing.xl,
        minHeight: dimensions.minTouchArea,
    },
    leaveText: {
        color: colors.error,
        fontWeight: '600',
    },
    leaveChevron: {
        color: colors.error,
        fontSize: 22,
    },
});