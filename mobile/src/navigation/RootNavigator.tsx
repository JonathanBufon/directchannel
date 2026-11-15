// mobile/src/navigation/RootNavigator.tsx
import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { RootStackParamList } from '../types/navigation';
import { LoginScreen } from '../screens/auth/LoginScreen';
import { ProfileScreen } from '../screens/profile/ProfileScreen';
import { RegisterScreen } from '../screens/auth/RegisterScreen';
import { RecoverAccessScreen } from '../screens/auth/RecoverAccessScreen';
import { MainTabNavigator } from './MainTabNavigator';
import { SearchUsersScreen } from '../screens/messages/SearchUsersScreen';
import { colors } from '../theme';

const Stack = createNativeStackNavigator<RootStackParamList>();

export const RootNavigator: React.FC = () => {
  return (
    <Stack.Navigator
      id="rootStack"
      initialRouteName="Login"
      screenOptions={{
        headerStyle: { backgroundColor: colors.background },
        headerTintColor: colors.primary,
        contentStyle: { backgroundColor: colors.background },
      }}
    >
      <Stack.Screen
        name="Login"
        component={LoginScreen}
        options={{ headerShown: false }}
      />
      <Stack.Screen
        name="Register"
        component={RegisterScreen}
        options={{ headerShown: false }}
      />
      <Stack.Screen
        name="RecoverAccess"
        component={RecoverAccessScreen}
        options={{ headerShown: false }}
      />
      <Stack.Screen
        name="Main"
        component={MainTabNavigator}
        options={{ headerShown: false }} // Oculta o header da stack raiz
      />
      <Stack.Screen
        name="SearchUsers"
        component={SearchUsersScreen}
        options={{ headerShown: false }}
      />
    </Stack.Navigator>
  );
};