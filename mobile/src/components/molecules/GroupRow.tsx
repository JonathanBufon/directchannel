// mobile/src/components/molecules/GroupRow.tsx
import React, { memo } from 'react';
import { View, StyleSheet, Pressable } from 'react-native';
import { Label } from '../atoms/Label';
import { colors, spacing, radii, dimensions } from '../../theme';

export interface GroupData {
    id: string;
    name: string;
    memberCount: number;
}

export interface GroupRowProps {
    group: GroupData;
    onPress: (group: GroupData) => void;
}

export const GroupRow = memo<GroupRowProps>(({ group, onPress }) => {
    // Extrai iniciais de até 2 palavras (ex: Equipe do projeto -> ED)
    const words = group.name.trim().split(' ');
    const initials = words.length > 1
        ? `${words[0].charAt(0)}${words[1].charAt(0)}`.toUpperCase()
        : group.name.slice(0, 2).toUpperCase();

    return (
        <Pressable
            style={({ pressed }) => [styles.card, pressed && styles.pressed]}
            onPress={() => onPress(group)}
        >
            <View style={styles.avatar}>
                <Label variant="body" style={styles.avatarText}>
                    {initials}
                </Label>
            </View>

            <View style={styles.info}>
                <Label variant="body" style={styles.name}>
                    {group.name}
                </Label>
                <Label variant="caption" style={styles.memberCount}>
                    {group.memberCount} participantes
                </Label>
            </View>

            <Label variant="title" style={styles.chevron}>
                ›
            </Label>
        </Pressable>
    );
});

GroupRow.displayName = 'GroupRow';

const styles = StyleSheet.create({
    card: {
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
    pressed: {
        opacity: 0.7,
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
    info: {
        flex: 1,
    },
    name: {
        fontWeight: '600',
        color: colors.text,
    },
    memberCount: {
        color: colors.textSecondary,
        marginTop: 2,
    },
    chevron: {
        color: colors.textSecondary,
        fontSize: 22,
        marginLeft: spacing.sm,
    },
});