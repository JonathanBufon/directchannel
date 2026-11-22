// mobile/src/components/molecules/UserRow.tsx
import React, { memo } from 'react';
import { View, StyleSheet, Pressable } from 'react-native';
import { Label } from '../atoms/Label';
import { colors, spacing, radii, dimensions } from '../../theme';

export interface UserRowData {
    id: string;
    name: string;
    username: string;
}

export interface UserRowProps {
    user: UserRowData;
    onPress: (user: UserRowData) => void;
}

export const UserRow = memo<UserRowProps>(({ user, onPress }) => {
    const initial = user.name ? user.name.charAt(0).toUpperCase() : '?';

    return (
        <Pressable
            style={({ pressed }) => [styles.card, pressed && styles.pressed]}
            onPress={() => onPress(user)}
        >
            <View style={styles.avatar}>
                <Label variant="body" style={styles.avatarText}>
                    {initial}
                </Label>
            </View>

            <View style={styles.info}>
                <Label variant="body" style={styles.name}>
                    {user.name}
                </Label>
                <Label variant="caption" style={styles.username}>
                    {user.username}
                </Label>
            </View>

            <View style={styles.badge}>
                <Label variant="caption" style={styles.badgeText}>
                    Iniciar ›
                </Label>
            </View>
        </Pressable>
    );
});

UserRow.displayName = 'UserRow';

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
    username: {
        color: colors.textSecondary,
        marginTop: 2,
    },
    badge: {
        paddingHorizontal: spacing.sm,
        paddingVertical: spacing.xs,
    },
    badgeText: {
        color: colors.primary,
        fontWeight: '600',
    },
});