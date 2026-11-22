// mobile/src/components/atoms/Label.tsx
import React from 'react';
import { Text, StyleSheet, TextProps } from 'react-native';
import { colors, typography } from '../../theme';

export type LabelVariant = 'title' | 'subtitle' | 'body' | 'caption';

interface LabelProps extends TextProps {
    variant?: LabelVariant;
    color?: string;
    children: React.ReactNode;
}

export const Label: React.FC<LabelProps> = ({
    variant = 'body',
    color,
    style,
    children,
    ...rest
}) => {
    return (
        <Text
            style={[
                styles.base,
                styles[variant],
                color ? { color } : undefined,
                style,
            ]}
            {...rest}
        >
            {children}
        </Text>
    );
};

const styles = StyleSheet.create({
    base: {
        color: colors.text,
    },
    // Variações tipográficas definidas no tema
    title: {
        fontSize: typography.title,
        fontWeight: '700',
    },
    subtitle: {
        fontSize: typography.subtitle,
        fontWeight: '600',
    },
    body: {
        fontSize: typography.body,
        fontWeight: '400',
    },
    caption: {
        fontSize: typography.caption,
        fontWeight: '400',
        color: colors.textSecondary,
    },
});