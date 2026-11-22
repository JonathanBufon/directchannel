// mobile/src/screens/groups/CreateGroupScreen.tsx
import React, { useState } from 'react';
import { View, StyleSheet, Pressable, ScrollView, Alert } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { RootNavigationProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Label } from '../../components/atoms/Label';
import { Input } from '../../components/atoms/Input';
import { Button } from '../../components/atoms/Button';
import { colors, spacing, radii } from '../../theme';

// Contatos elegíveis para adição no grupo (Pág. 11 e 25)
const AVAILABLE_CONTACTS = [
    { id: 'usr_marina', name: 'Marina Costa', username: '@marina' },
    { id: 'usr_pedro', name: 'Pedro Lima', username: '@pedro' },
    { id: 'usr_ana', name: 'Ana Souza', username: '@ana' },
];

export const CreateGroupScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();
    const [groupName, setGroupName] = useState('');
    const [searchContact, setSearchContact] = useState('');
    const [selectedIds, setSelectedIds] = useState<string[]>(['usr_marina', 'usr_pedro', 'usr_ana']);
    const [isLoading, setIsLoading] = useState(false);

    // Toggle imutável para seleção múltipla
    const toggleContact = (id: string) => {
        setSelectedIds((prev) =>
            prev.includes(id) ? prev.filter((item) => item !== id) : [...prev, id]
        );
    };

    const handleCreateGroup = async () => {
        const trimmed = groupName.trim();
        if (!trimmed) {
            Alert.alert('Campo Obrigatório', 'Informe o nome do grupo.');
            return;
        }
        if (selectedIds.length === 0) {
            Alert.alert('Participantes', 'Selecione ao menos um participante para o grupo.');
            return;
        }

        setIsLoading(true);
        try {
            // Simulação da geração de chaves coletivas e registro do grupo
            await new Promise((resolve) => setTimeout(resolve, 1000));

            navigation.replace('GroupChat', {
                groupId: `grp_${Date.now()}`,
                groupName: trimmed,
                memberCount: selectedIds.length + 1, // Participantes + criador (Você)
            });
        } catch {
            Alert.alert('Erro', 'Não foi possível criar o grupo.');
        } finally {
            setIsLoading(false);
        }
    };

    const filteredContacts = AVAILABLE_CONTACTS.filter((c) =>
        c.name.toLowerCase().includes(searchContact.toLowerCase()) ||
        c.username.toLowerCase().includes(searchContact.toLowerCase())
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
                    Criar grupo
                </Label>
            </View>

            {/* Campo: Nome do Grupo */}
            <View style={styles.fieldBox}>
                <Label variant="body" style={styles.fieldLabel}>
                    Nome do grupo
                </Label>
                <Input
                    placeholder="Equipe do projeto"
                    value={groupName}
                    onChangeText={setGroupName}
                    style={styles.input}
                />
            </View>

            {/* Campo: Buscar Participante */}
            <View style={styles.fieldBox}>
                <Label variant="body" style={styles.fieldLabel}>
                    Buscar participante
                </Label>
                <Input
                    placeholder="@identificador"
                    value={searchContact}
                    onChangeText={setSearchContact}
                    autoCapitalize="none"
                    style={styles.input}
                />
            </View>

            {/* Lista de Seleção com Checkbox */}
            <View style={styles.contactsCard}>
                {filteredContacts.map((contact) => {
                    const isSelected = selectedIds.includes(contact.id);
                    return (
                        <Pressable
                            key={contact.id}
                            onPress={() => toggleContact(contact.id)}
                            style={styles.contactRow}
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

                            <View style={styles.contactInfo}>
                                <Label variant="body" style={styles.contactName}>
                                    {contact.name}
                                </Label>
                                <Label variant="caption">{contact.username}</Label>
                            </View>
                        </Pressable>
                    );
                })}
            </View>

            {/* Botão de Criação */}
            <Button
                title="Criar grupo"
                variant="primary"
                isLoading={isLoading}
                onPress={handleCreateGroup}
                style={styles.submitButton}
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
    contactsCard: {
        backgroundColor: colors.surface,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: colors.border,
        marginBottom: spacing.xl,
        overflow: 'hidden',
    },
    contactRow: {
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
    contactInfo: {
        flex: 1,
    },
    contactName: {
        fontWeight: '600',
        color: colors.text,
    },
    submitButton: {
        width: '100%',
        marginBottom: spacing.xl,
    },
});