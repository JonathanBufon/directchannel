// mobile/src/screens/groups/ManageGroupScreen.tsx
import React, { useState } from 'react';
import { View, StyleSheet, Pressable, Alert } from 'react-native';
import { useNavigation, useRoute } from '@react-navigation/native';
import { RootNavigationProp, ManageGroupRouteProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Label } from '../../components/atoms/Label';
import { Input } from '../../components/atoms/Input';
import { Button } from '../../components/atoms/Button';
import { colors, spacing, radii } from '../../theme';

// Participantes configuráveis
const ALL_MEMBERS = [
    { id: 'usr_marina', name: 'Marina Costa', username: '@marina' },
    { id: 'usr_pedro', name: 'Pedro Lima', username: '@pedro' },
    { id: 'usr_ana', name: 'Ana Souza', username: '@ana' },
    { id: 'usr_lucas', name: 'Lucas Almeida', username: '@lucas' },
];

export const ManageGroupScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();
    const route = useRoute<ManageGroupRouteProp>();
    const { groupName: initialName } = route.params;

    const [groupName, setGroupName] = useState(initialName);
    const [searchQuery, setSearchQuery] = useState('');
    const [selectedIds, setSelectedIds] = useState<string[]>([
        'usr_marina',
        'usr_pedro',
        'usr_ana',
    ]);
    const [isLoading, setIsLoading] = useState(false);

    const toggleMember = (id: string) => {
        setSelectedIds((prev) =>
            prev.includes(id) ? prev.filter((item) => item !== id) : [...prev, id]
        );
    };

    const handleSaveChanges = async () => {
        if (!groupName.trim()) {
            Alert.alert('Campo Obrigatório', 'O nome do grupo não pode ficar vazio.');
            return;
        }

        setIsLoading(true);
        try {
            // Simulação da rotação de chaves criptográficas da equipe
            await new Promise((resolve) => setTimeout(resolve, 1000));

            Alert.alert(
                'Alterações Salvas',
                'As chaves do grupo foram atualizadas para proteger as próximas mensagens.',
                [{ text: 'OK', onPress: () => navigation.goBack() }]
            );
        } catch {
            Alert.alert('Erro', 'Não foi possível salvar as alterações.');
        } finally {
            setIsLoading(false);
        }
    };

    const filteredMembers = ALL_MEMBERS.filter(
        (m) =>
            m.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
            m.username.toLowerCase().includes(searchQuery.toLowerCase())
    );

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
                    Gerenciar grupo
                </Label>
            </View>

            {/* Edição do Nome */}
            <View style={styles.fieldBox}>
                <Label variant="body" style={styles.fieldLabel}>
                    Nome do grupo
                </Label>
                <Input
                    value={groupName}
                    onChangeText={setGroupName}
                    style={styles.input}
                />
            </View>

            {/* Adicionar / Buscar Participante */}
            <View style={styles.fieldBox}>
                <Label variant="body" style={styles.fieldLabel}>
                    Adicionar participante
                </Label>
                <Input
                    placeholder="@identificador"
                    value={searchQuery}
                    onChangeText={setSearchQuery}
                    autoCapitalize="none"
                    style={styles.input}
                />
            </View>

            {/* Lista de Membros com Checkbox (Desmarcar = Remover) */}
            <View style={styles.membersCard}>
                {filteredMembers.map((member) => {
                    const isSelected = selectedIds.includes(member.id);
                    return (
                        <Pressable
                            key={member.id}
                            onPress={() => toggleMember(member.id)}
                            style={styles.memberRow}
                        >
                            <View
                                style={[
                                    styles.checkbox,
                                    isSelected ? styles.checkboxSelected : styles.checkboxUnselected,
                                ]}
                            >
                                {isSelected ? (
                                    <Label variant="body" style={styles.checkmark}>
                                        ✓
                                    </Label>
                                ) : null}
                            </View>

                            <View style={styles.memberInfo}>
                                <Label variant="body" style={styles.memberName}>
                                    {member.name}
                                </Label>
                                <Label variant="caption">{member.username}</Label>
                            </View>
                        </Pressable>
                    );
                })}
            </View>

            {/* Aviso de Rotação de Chaves (RF16) */}
            <View style={styles.rotationNotice}>
                <Label variant="caption" style={styles.rotationText}>
                    🔒 Remoções atualizam a proteção das próximas mensagens. Ex-membros não conseguirão decifrá-las.
                </Label>
            </View>

            {/* Botão de Salvar */}
            <Button
                title="Salvar alterações"
                variant="primary"
                isLoading={isLoading}
                onPress={handleSaveChanges}
                style={styles.saveButton}
            />
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
    fieldBox: {
        marginBottom: spacing.md,
    },
    fieldLabel: {
        fontWeight: '500',
        marginBottom: spacing.xs,
    },
    input: {
        backgroundColor: colors.surface,
    },
    membersCard: {
        backgroundColor: colors.surface,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: colors.border,
        marginBottom: spacing.md,
        overflow: 'hidden',
    },
    memberRow: {
        flexDirection: 'row',
        alignItems: 'center',
        padding: spacing.md,
        borderBottomWidth: 1,
        borderBottomColor: colors.border,
    },
    checkbox: {
        width: 24,
        height: 24,
        borderRadius: radii.sm,
        borderWidth: 1,
        alignItems: 'center',
        justifyContent: 'center',
        marginRight: spacing.md,
    },
    checkboxSelected: {
        backgroundColor: colors.primary,
        borderColor: colors.primary,
    },
    checkboxUnselected: {
        borderColor: colors.border,
        backgroundColor: colors.surface,
    },
    checkmark: {
        color: '#FFFFFF',
        fontWeight: '700',
        fontSize: 14,
    },
    memberInfo: {
        flex: 1,
    },
    memberName: {
        fontWeight: '600',
        color: colors.text,
    },
    rotationNotice: {
        backgroundColor: colors.protection,
        padding: spacing.md,
        borderRadius: radii.sm,
        marginBottom: spacing.lg,
    },
    rotationText: {
        color: colors.primary,
        textAlign: 'center',
        lineHeight: 18,
    },
    saveButton: {
        width: '100%',
        marginBottom: spacing.xl,
    },
});