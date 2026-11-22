// mobile/src/components/templates/ScreenTemplate.tsx
import React from 'react';
import { View, StyleSheet, ScrollView, ViewStyle } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Label } from '../atoms/Label';
import { colors, spacing } from '../../theme';

export interface ScreenTemplateProps {
    title?: string;
    subtitle?: string;
    children: React.ReactNode;
    contentContainerStyle?: ViewStyle;
    scrollable?: boolean;
}

export const ScreenTemplate: React.FC<ScreenTemplateProps> = ({
    title,
    subtitle,
    children,
    contentContainerStyle,
    scrollable = true,
}) => {
    const content = (
        <>
            {title ? (
                <View style={styles.header}>
                    <Label variant="title" style={styles.title}>
                        {title}
                    </Label>
                    {subtitle ? (
                        <Label variant="caption" style={styles.subtitle}>
                            {subtitle}
                        </Label>
                    ) : null}
                </View>
            ) : null}
            {children}
        </>
    );

    return (
        <SafeAreaView style={styles.safeArea} edges={['top']}>
            {scrollable ? (
                <ScrollView
                    contentContainerStyle={[styles.scrollContent, contentContainerStyle]}
                    showsVerticalScrollIndicator={false}
                    keyboardShouldPersistTaps="handled"
                >
                    {content}
                </ScrollView>
            ) : (
                <View style={[styles.staticContent, contentContainerStyle]}>{content}</View>
            )}
        </SafeAreaView>
    );
};

const styles = StyleSheet.create({
    safeArea: {
        flex: 1,
        backgroundColor: colors.background,
    },
    scrollContent: {
        padding: spacing.lg,
        flexGrow: 1,
    },
    staticContent: {
        flex: 1,
        padding: spacing.lg,
    },
    header: {
        marginBottom: spacing.lg,
    },
    title: {
        color: colors.text,
        fontWeight: '700',
    },
    subtitle: {
        color: colors.textSecondary,
        marginTop: spacing.xs,
    },
});