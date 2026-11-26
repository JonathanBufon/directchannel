// mobile/src/screens/profile/BlockedContactsScreen.tsx
import React, { useState } from 'react';
import { View, StyleSheet, Pressable, Alert } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { RootNavigationProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Label } from '../../components/atoms/Label';
import { Button } from '../../components/atoms/Button';
import { colors, spacing, radii } from '../../theme';

interface BlockedUser {
    id: string;
    name: string;
    username: string;
}

// Contato bloqueado inicial conforme wireframe T19 (Páginas 13 e 28)
const INITIAL_BLOCKED: BlockedUser[] = [
    { id: 'usr_marina', name: 'Marina Costa', username: '@marina' },
];

export const BlockedContactsScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();
    const [blockedList, setBlockedList] = useState<BlockedUser[]>(INITIAL_BLOCKED);

    const handleUnblock = (user: BlockedUser) => {
        Alert.alert(
            `Desbloquear ${user.name}?`,
            'Você voltará a receber mensagens e solicitações diretas deste usuário.',
            [
                { text: 'Cancelar', style: 'cancel' },
                {
                    text: 'Desbloquear',
                    onPress: () => {
                        // Remove o usuário da lista localmente
                        setBlockedList((prev) => prev.filter((item) => item.id !== user.id));
                        Alert.alert('Desbloqueado', `${user.name} foi desbloqueado com sucesso.`);
                    },
                },
            ]
        );
    };

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
                    Contatos bloqueados
                </Label>
            </View>

            {/* Lista de Contatos Bloqueados */}
            {blockedList.length > 0 ? (
                blockedList.map((contact) => (
                    <View key={contact.id} style={styles.blockedCard}>
                        <View style={styles.contactHeader}>
                            <View style={styles.avatar}>
                                <Label variant="body" style={styles.avatarText}>
                                    {contact.name.charAt(0).toUpperCase()}
                                </Label>
                            </View>
                            <View style={styles.info}>
                                <Label variant="body" style={styles.name}>
                                    {contact.name}
                                </Label>
                                <Label variant="caption" style={styles.username}>
                                    {contact.username}
                                </Label>
                            </View>
                        </View>

                        {/* Ação de Desbloqueio (Wireframe T19) */}
                        <Button
                            title={`Desbloquear ${contact.name.split(' ')[0]}`}
                            variant="primary"
                            onPress={() => handleUnblock(contact)}
                            style={styles.unblockButton}
                        />
                    </View>
                ))
            ) : (
                /* Estado Vazio */
                <View style={styles.emptyCard}>
                    <Label variant="body" style={styles.emptyTitle}>
                        Nenhum contato bloqueado
                    </Label>
                    <Label variant="caption" style={styles.emptySubtitle}>
                        Pessoas bloqueadas aparecerão aqui e não poderão enviar mensagens diretas.
                    </Label>
                </View>
            )}

            {/* Caixa de Esclarecimento Legal / Funcional */}
            <View style={styles.helperBox}>
                <Label variant="caption" style={styles.helperText}>
                    O desbloqueio permite receber novos contatos desta pessoa. Conversas anteriores não são apagadas automaticamente.
                </Label>
            </View>
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
    blockedCard: {
        backgroundColor: colors.surface,
        padding: spacing.lg,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: colors.border,
        marginBottom: spacing.md,
    },
    contactHeader: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: spacing.md,
    },
    avatar: {
        width: 48,
        height: 48,
        borderRadius: 24,
        backgroundColor: colors.protection,
        alignItems: 'center',
        justifyContent: 'center',
        marginRight: spacing.md,
    },
    avatarText: {
        color: colors.primary,
        fontWeight: '700',
        fontSize: 18,
    },
    info: {
        flex: 1,
    },
    name: {
        fontWeight: '700',
        color: colors.text,
    },
    username: {
        color: colors.textSecondary,
        marginTop: 2,
    },
    unblockButton: {
        width: '100%',
    },
    emptyCard: {
        backgroundColor: colors.surface,
        padding: spacing.xl,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: colors.border,
        alignItems: 'center',
        marginBottom: spacing.md,
    },
    emptyTitle: {
        fontWeight: '600',
        color: colors.text,
        marginBottom: spacing.xs,
    },
    emptySubtitle: {
        color: colors.textSecondary,
        textAlign: 'center',
    },
    helperBox: {
        backgroundColor: colors.surface,
        padding: spacing.md,
        borderRadius: radii.sm,
        borderWidth: 1,
        borderColor: colors.border,
    },
    helperText: {
        color: colors.textSecondary,
        textAlign: 'center',
        lineHeight: 18,
    },
});