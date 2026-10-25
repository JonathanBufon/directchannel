
import React from 'react';
import {
  KeyboardAvoidingView,
  ScrollView,
  Platform,
  StyleSheet,
  View,
  ViewStyle,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { colors, spacing } from '../../theme';

export interface CenteredTemplateProps {
  header?: React.ReactNode;
  children: React.ReactNode;
  footer?: React.ReactNode;
  containerStyle?: ViewStyle;
}

export const CenteredTemplate: React.FC<CenteredTemplateProps> = ({
  header,
  children,
  footer,
  containerStyle,
}) => {
  return (
    <SafeAreaView style={styles.safeArea}>
      <KeyboardAvoidingView
        style={styles.keyboardAvoid}
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      >
        <ScrollView
          contentContainerStyle={[styles.scrollContent, containerStyle]}
          keyboardShouldPersistTaps="handled"
          showsVerticalScrollIndicator={false}
        >
          {header ? <View style={styles.headerContainer}>{header}</View> : null}
          <View style={styles.bodyContainer}>{children}</View>
          {footer ? <View style={styles.footerContainer}>{footer}</View> : null}
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: colors.background,
  },
  keyboardAvoid: {
    flex: 1,
  },
  scrollContent: {
    flexGrow: 1,
    justifyContent: 'center',
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.xl,
  },
  headerContainer: {
    marginBottom: spacing.xl,
    alignItems: 'center',
  },
  bodyContainer: {
    width: '100%',
  },
  footerContainer: {
    marginTop: spacing.xl,
    alignItems: 'center',
  },
});