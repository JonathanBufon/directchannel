// mobile/src/screens/admin/AdminReportsScreen.tsx
import React, { useState } from 'react';
import { View, StyleSheet, Pressable, FlatList } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { RootNavigationProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Label } from '../../components/atoms/Label';
import { colors, spacing, radii, dimensions } from '../../theme';

interface ReportItem {
    id: string;
    title: string;
    category: string;
    reportedUser: string;
    status: 'Pendente' | 'Analisado';
    date: string;
}

const INITIAL_REPORTS: ReportItem[] = [
    { id: 'rep_001', title: 'Relato 001', category: 'Contato indesejado', reportedUser: '@contaexemplo', status: 'Pendente', date: 'Hoje' },
    { id: 'rep_002', title: 'Relato 002', category: 'Assédio', reportedUser: '@spambot', status: 'Pendente', date: 'Ontem' },
    { id: 'rep_003', title: 'Relato 003', category: 'Spam', reportedUser: '@anonimo99', status: 'Pendente', date: '08/09' },
];

export const AdminReportsScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();
    const [filter, setFilter] = useState<'Pendentes' | 'Analisadas'>('Pendentes');
    const [reports] = useState<ReportItem[]>(INITIAL_REPORTS);

    const filteredReports = reports.filter((r) =>
        filter === 'Pendentes' ? r.status === 'Pendente' : r.status === 'Analisado'
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
                    Denúncias
                </Label>
            </View>

            {/* Seletor de Filtro de Estado (Wireframe A04) */}
            <View style={styles.filterBox}>
                <Label variant="caption" style={styles.filterLabel}>
                    Filtrar estado
                </Label>
                <View style={styles.toggleRow}>
                    <Pressable
                        onPress={() => setFilter('Pendentes')}
                        style={[styles.toggleButton, filter === 'Pendentes' ? styles.toggleActive : undefined]}
                    >
                        <Label
                            variant="body"
                            style={filter === 'Pendentes' ? styles.toggleTextActive : styles.toggleText}
                        >
                            Pendentes ({reports.filter((r) => r.status === 'Pendente').length})
                        </Label>
                    </Pressable>

                    <Pressable
                        onPress={() => setFilter('Analisadas')}
                        style={[styles.toggleButton, filter === 'Analisadas' ? styles.toggleActive : undefined]}
                    >
                        <Label
                            variant="body"
                            style={filter === 'Analisadas' ? styles.toggleTextActive : styles.toggleText}
                        >
                            Analisadas ({reports.filter((r) => r.status === 'Analisado').length})
                        </Label>
                    </Pressable>
                </View>
            </View>

            {/* Lista de Relatos Operacionais */}
            <FlatList
                data={filteredReports}
                keyExtractor={(item) => item.id}
                contentContainerStyle={styles.listContent}
                renderItem={({ item }) => (
                    <Pressable
                        style={styles.reportCard}
                        onPress={() =>
                            navigation.navigate('AdminReviewReport', {
                                reportId: item.title,
                                reportedUser: item.reportedUser,
                                category: item.category,
                                status: item.status,
                            })
                        }
                    >
                        <View style={styles.badge}>
                            <Label variant="body" style={styles.badgeText}>
                                R0
                            </Label>
                        </View>

                        <View style={styles.reportInfo}>
                            <Label variant="body" style={styles.reportTitle}>
                                {item.title}
                            </Label>
                            <Label variant="caption" style={styles.reportCategory}>
                                {item.category} · {item.reportedUser}
                            </Label>
                        </View>

                        <Label variant="title" style={styles.chevron}>
                            ›
                        </Label>
                    </Pressable>
                )}
                ListEmptyComponent={
                    <View style={styles.emptyContainer}>
                        <Label variant="body" style={styles.emptyTitle}>
                            Nenhuma denúncia pendente
                        </Label>
                        <Label variant="caption" style={styles.emptySubtitle}>
                            Todos os relatos operacionais foram processados pela equipe.
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
    headerTitle: {
        fontWeight: '700',
        color: colors.text,
    },
    filterBox: {
        marginBottom: spacing.md,
    },
    filterLabel: {
        color: colors.textSecondary,
        fontWeight: '600',
        marginBottom: spacing.xs,
    },
    toggleRow: {
        flexDirection: 'row',
        backgroundColor: colors.surface,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: colors.border,
        overflow: 'hidden',
    },
    toggleButton: {
        flex: 1,
        paddingVertical: spacing.sm,
        alignItems: 'center',
        justifyContent: 'center',
    },
    toggleActive: {
        backgroundColor: colors.primary,
    },
    toggleText: {
        color: colors.text,
        fontWeight: '500',
    },
    toggleTextActive: {
        color: '#FFFFFF',
        fontWeight: '700',
    },
    listContent: {
        paddingBottom: spacing.xl,
    },
    reportCard: {
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
    badge: {
        width: 44,
        height: 44,
        borderRadius: 22,
        backgroundColor: colors.protection,
        alignItems: 'center',
        justifyContent: 'center',
        marginRight: spacing.md,
    },
    badgeText: {
        color: colors.primary,
        fontWeight: '700',
    },
    reportInfo: {
        flex: 1,
    },
    reportTitle: {
        fontWeight: '700',
        color: colors.text,
    },
    reportCategory: {
        color: colors.textSecondary,
        marginTop: 2,
    },
    chevron: {
        color: colors.textSecondary,
        fontSize: 22,
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
    },
});