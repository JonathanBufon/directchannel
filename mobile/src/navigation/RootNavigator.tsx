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
import { ChatScreen } from '../screens/conversations/ChatScreen';
import { FileViewerScreen } from '../screens/files/FileViewerScreen';
import { ContactInfoScreen } from '../screens/conversations/ContactInfoScreen';
import { VerifyContactScreen } from '../screens/conversations/VerifyContactScreen';
import { CreateGroupScreen } from '../screens/groups/CreateGroupScreen';
import { GroupChatScreen } from '../screens/groups/GroupChatScreen';
import { GroupInfoScreen } from '../screens/groups/GroupInfoScreen';
import { ManageGroupScreen } from '../screens/groups/ManageGroupScreen';
import { LeaveGroupScreen } from '../screens/groups/LeaveGroupScreen';
import { EditProfileScreen } from '../screens/profile/EditProfileScreen';
import { PrivacyScreen } from '../screens/profile/PrivacyScreen';
import { BlockedContactsScreen } from '../screens/profile/BlockedContactsScreen';
import { CodeChangedScreen } from '../screens/conversations/CodeChangedScreen';
import { BlockContactScreen } from '../screens/conversations/BlockContactScreen';
import { ResetPasswordScreen } from '../screens/auth/ResetPasswordScreen';
import { ReportSentScreen } from '../screens/reports/ReportSentScreen';
import { AdminDashboardScreen } from '../screens/admin/AdminDashboardScreen';
import { AdminUsersScreen } from '../screens/admin/AdminUsersScreen';
import { AdminManageAccountScreen } from '../screens/admin/AdminManageAccountScreen';
import { AdminReportsScreen } from '../screens/admin/AdminReportsScreen';
import { AdminReviewReportScreen } from '../screens/admin/AdminReviewReportScreen';
import { AdminSettingsScreen } from '../screens/admin/AdminSettingsScreen';
import { colors } from '../theme';
import { ReportUserScreen } from '../screens/reports/ReportUserScreen';

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
            <Stack.Screen
                name="Chat"
                component={ChatScreen}
                options={{ headerShown: false }}
            />
            <Stack.Screen
                name="FileViewer"
                component={FileViewerScreen}
                options={{ headerShown: false }}
            />
            <Stack.Screen
                name="ContactInfo"
                component={ContactInfoScreen}
                options={{ headerShown: false }}
            />
            <Stack.Screen
                name="VerifyContact"
                component={VerifyContactScreen}
                options={{ headerShown: false }}
            />
            <Stack.Screen
                name="CreateGroup"
                component={CreateGroupScreen}
                options={{ headerShown: false }}
            />
            <Stack.Screen
                name="GroupChat"
                component={GroupChatScreen}
                options={{ headerShown: false }}
            />
            <Stack.Screen
                name="GroupInfo"
                component={GroupInfoScreen}
                options={{ headerShown: false }}
            />
            <Stack.Screen
                name="ManageGroup"
                component={ManageGroupScreen}
                options={{ headerShown: false }}
            />
            <Stack.Screen
                name="LeaveGroup"
                component={LeaveGroupScreen}
                options={{ headerShown: false }}
            />
            <Stack.Screen
                name="EditProfile"
                component={EditProfileScreen}
                options={{ headerShown: false }}
            />
            <Stack.Screen
                name="Privacy"
                component={PrivacyScreen}
                options={{ headerShown: false }}
            />
            <Stack.Screen
                name="BlockedContacts"
                component={BlockedContactsScreen}
                options={{ headerShown: false }}
            />
            <Stack.Screen
                name="CodeChanged"
                component={CodeChangedScreen}
                options={{ headerShown: false }}
            />
            <Stack.Screen
                name="BlockContact"
                component={BlockContactScreen}
                options={{ headerShown: false }}
            />
            <Stack.Screen
                name="ResetPassword"
                component={ResetPasswordScreen}
                options={{ headerShown: false }}
            />
            <Stack.Screen
                name="ReportSent"
                component={ReportSentScreen}
                options={{ headerShown: false }}
            />
            <Stack.Screen
                name="ReportUser"
                component={ReportUserScreen}
                options={{ headerShown: false }}
            />
            <Stack.Screen
                name="AdminDashboard"
                component={AdminDashboardScreen}
                options={{ headerShown: false }}
            />
            <Stack.Screen
                name="AdminUsers"
                component={AdminUsersScreen}
                options={{ headerShown: false }}
            />
            <Stack.Screen
                name="AdminManageAccount"
                component={AdminManageAccountScreen}
                options={{ headerShown: false }}
            />
            <Stack.Screen
                name="AdminReports"
                component={AdminReportsScreen}
                options={{ headerShown: false }}
            />
            <Stack.Screen
                name="AdminReviewReport"
                component={AdminReviewReportScreen}
                options={{ headerShown: false }}
            />
            <Stack.Screen
                name="AdminSettings"
                component={AdminSettingsScreen}
                options={{ headerShown: false }}
            />
        </Stack.Navigator>
    );
};