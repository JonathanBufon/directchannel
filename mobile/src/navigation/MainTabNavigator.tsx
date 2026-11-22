// mobile/src/navigation/MainTabNavigator.tsx
import React from 'react';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { MainTabParamList } from '../types/navigation';
import { ConversationsScreen } from '../screens/messages/ConversationsScreen';
import { GroupsScreen } from '../screens/groups/GroupsScreen';
import { ProfileScreen } from '../screens/profile/ProfileScreen';
import { colors, typography } from '../theme';

const Tab = createBottomTabNavigator<MainTabParamList>();

export const MainTabNavigator: React.FC = () => {
    return (
        <Tab.Navigator
            initialRouteName="Conversations"
            screenOptions={{
                headerShown: false, // Os títulos são gerenciados pelo ScreenTemplate
                tabBarActiveTintColor: colors.primary,
                tabBarInactiveTintColor: colors.textSecondary,
                tabBarStyle: {
                    backgroundColor: colors.surface,
                    borderTopColor: colors.border,
                    height: 60,
                    paddingBottom: 8,
                    paddingTop: 8,
                },
                tabBarLabelStyle: {
                    fontSize: typography.caption,
                    fontWeight: '600',
                },
                tabBarIconStyle: {
                    display: 'none', // Usando rótulos textuais diretos conforme acordado
                },
            }}
        >
            <Tab.Screen
                name="Conversations"
                component={ConversationsScreen}
                options={{ tabBarLabel: 'Conversas' }}
            />
            <Tab.Screen
                name="Groups"
                component={GroupsScreen}
                options={{ tabBarLabel: 'Grupos' }}
            />
            <Tab.Screen
                name="Profile"
                component={ProfileScreen}
                options={{ tabBarLabel: 'Perfil' }}
            />
        </Tab.Navigator>
    );
};