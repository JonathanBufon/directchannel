// mobile/src/screens/admin/AdminDashboardScreen.tsx
import React from 'react';
import { View, StyleSheet, Pressable, Alert } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { RootNavigationProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { Label } from '../../components/atoms/Label';
import { colors, spacing, radii, dimensions } from '../../theme';

export const AdminDashboardScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();

    const handleAdminLogout = () => {
        Alert.alert('Encerrar Sessão', 'Deseja sair do painel administrativo?', [
            { text: 'Cancelar', style: 'cancel' },
            {
                text: 'Sair',
                style: 'destructive',
                onPress: () => navigation.replace('Login'),
            },
        ]);
    };

    return (
        <ScreenTemplate scrollable>
            {/* Cabeçalho */}
            <View style={styles.header}>
                <Label variant="title" style={styles.headerTitle}>
                    Administração
                </Label>
                <Label variant="caption" style={styles.headerSubtitle}>
                    Painel de controle e governança da plataforma
                </Label>
            </View>

            {/* Cartões de Indicadores Operacionais (RF21) */}
            <View style={styles.metricsContainer}>
                {/* KPI 1: Usuários Ativos */}
                <View style={styles.metricCard}>
                    <Label variant="caption" style={styles.metricLabel}>
                        Usuários ativos
                    </Label>
                    <Label variant="title" style={styles.metricNumber}>
                        231
                    </Label>
                </View>

                {/* KPI 2: Denúncias Pendentes */}
                <View style={styles.metricCard}>
                    <Label variant="caption" style={styles.metricLabel}>
                        Denúncias pendentes
                    </Label>
                    <Label variant="title" style={styles.metricPendingNumber}>
                        3
                    </Label>
                </View>
            </View>

            {/* Menu de Ações Administrativas */}
            <View style={styles.menuCard}>
                {/* 1. Gerenciar Usuários (A02) */}
                <Pressable
                    style={styles.menuRow}
                    onPress={() => navigation.navigate('AdminUsers')}
                >
                    <Label variant="body" style={styles.menuText}>
                        Gerenciar usuários
                    </Label>
                    <Label variant="title" style={styles.chevron}>
                        ›
                    </Label>
                </Pressable>

                {/* 2. Analisar Denúncias (A04) */}
                <Pressable
                    style={styles.menuRow}
                    onPress={() => navigation.navigate('AdminReports')}
                >
                    <Label variant="body" style={styles.menuText}>
                        Analisar denúncias
                    </Label>
                    <Label variant="title" style={styles.chevron}>
                        ›
                    </Label>
                </Pressable>

                {/* 3. Configurações (A06) */}
                <Pressable
                    style={styles.menuRow}
                    onPress={() => navigation.navigate('AdminSettings')}
                >
                    <Label variant="body" style={styles.menuText}>
                        Configurações
                    </Label>
                    <Label variant="title" style={styles.chevron}>
                        ›
                    </Label>
                </Pressable>

                {/* 4. Sair */}
                <Pressable style={[styles.menuRow, styles.lastRow]} onPress={handleAdminLogout}>
                    <Label variant="body" style={styles.logoutText}>
                        Sair
                    </Label>
                    <Label variant="title" style={styles.logoutChevron}>
                        ›
                    </Label>
                </Pressable>
            </View>
        </ScreenTemplate>
    );
};

const styles = StyleSheet.create({
    header: {
        marginBottom: spacing.lg,
    },
    headerTitle: {
        fontWeight: '700',
        color: colors.text,
    },
    headerSubtitle: {
        color: colors.textSecondary,
        marginTop: spacing.xs,
    },
    metricsContainer: {
        marginBottom: spacing.xl,
        gap: spacing.md,
    },
    metricCard: {
        backgroundColor: colors.surface,
        padding: spacing.lg,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: colors.border,
    },
    metricLabel: {
        color: colors.textSecondary,
        fontWeight: '500',
        marginBottom: spacing.xs,
    },
    metricNumber: {
        fontSize: 32,
        fontWeight: '700',
        color: colors.primary,
    },
    metricPendingNumber: {
        fontSize: 32,
        fontWeight: '700',
        color: colors.error,
    },
    menuCard: {
        backgroundColor: colors.surface,
        borderRadius: radii.md,
        borderWidth: 1,
        borderColor: colors.border,
        overflow: 'hidden',
    },
    menuRow: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        padding: spacing.md,
        borderBottomWidth: 1,
        borderBottomColor: colors.border,
        minHeight: dimensions.minTouchArea,
    },
    lastRow: {
        borderBottomWidth: 0,
    },
    menuText: {
        fontWeight: '600',
        color: colors.text,
    },
    chevron: {
        color: colors.textSecondary,
        fontSize: 22,
    },
    logoutText: {
        fontWeight: '600',
        color: colors.error,
    },
    logoutChevron: {
        color: colors.error,
        fontSize: 22,
    },
});