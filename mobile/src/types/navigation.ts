// mobile/src/types/navigation.ts
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { BottomTabNavigationProp } from '@react-navigation/bottom-tabs';
import { RouteProp } from '@react-navigation/native';

// Definição das rotas e seus parâmetros
export type RootStackParamList = {
    Login: undefined;
    Register: undefined;
    RecoverAccess: undefined;
    Main: undefined;
    SearchUsers: undefined;
    Chat: {
        userId: string;
        name: string;
        username: string;
    };
    FileViewer: {
        fileName: string;
        fileSize: string;
        isIncoming?: boolean;
    };
    ContactInfo: {
        userId: string;
        name: string;
        username: string;
    };
    VerifyContact: {
        userId: string;
        name: string;
        username: string;
    };
    CreateGroup: undefined;
    GroupChat: {
        groupId: string;
        groupName: string;
        memberCount: number;
    };
    GroupInfo: {
        groupId: string;
        groupName: string;
        memberCount: number;
        isAdmin?: boolean;
    };
    ManageGroup: {
        groupId: string;
        groupName: string;
    };
    LeaveGroup: {
        groupId: string;
        groupName: string;
    };
    EditProfile: undefined;
    Privacy: undefined;
    BlockedContacts: undefined;
    CodeChanged: {
        userId: string;
        name: string;
        username: string;
    };
    BlockContact: {
        userId: string;
        name: string;
    };
    ResetPassword: {
        email?: string;
    };
    ReportUser: {
        userId: string;
        username: string;
    };
    ReportSent: {
        userId: string;
        contactName: string;
    };
    AdminDashboard: undefined;
    AdminUsers: undefined;
    AdminManageAccount: {
        userId: string;
        name: string;
        username: string;
        initialStatus: 'Ativa' | 'Bloqueada';
    };
    AdminReports: undefined;
    AdminReviewReport: {
        reportId: string;
        reportedUser: string;
        category: string;
        status: 'Pendente' | 'Analisado';
    };
    AdminSettings: undefined;
};

export type MainTabParamList = {
    Conversations: undefined;
    Groups: undefined;
    Profile: undefined;
};

export type RootNavigationProp = NativeStackNavigationProp<RootStackParamList>;
export type ChatRouteProp = RouteProp<RootStackParamList, 'Chat'>;
export type FileViewerRouteProp = RouteProp<RootStackParamList, 'FileViewer'>;
export type ContactInfoRouteProp = RouteProp<RootStackParamList, 'ContactInfo'>;
export type VerifyContactRouteProp = RouteProp<RootStackParamList, 'VerifyContact'>;
export type GroupChatRouteProp = RouteProp<RootStackParamList, 'GroupChat'>;
export type GroupInfoRouteProp = RouteProp<RootStackParamList, 'GroupInfo'>;
export type ManageGroupRouteProp = RouteProp<RootStackParamList, 'ManageGroup'>;
export type LeaveGroupRouteProp = RouteProp<RootStackParamList, 'LeaveGroup'>;
export type CodeChangedRouteProp = RouteProp<RootStackParamList, 'CodeChanged'>;
export type BlockContactRouteProp = RouteProp<RootStackParamList, 'BlockContact'>;
export type ResetPasswordRouteProp = RouteProp<RootStackParamList, 'ResetPassword'>;
export type ReportUserRouteProp = RouteProp<RootStackParamList, 'ReportUser'>;
export type ReportSentRouteProp = RouteProp<RootStackParamList, 'ReportSent'>;
export type AdminManageAccountRouteProp = RouteProp<RootStackParamList, 'AdminManageAccount'>;
export type AdminReviewReportRouteProp = RouteProp<RootStackParamList, 'AdminReviewReport'>;
export type MainTabNavigationProp = BottomTabNavigationProp<MainTabParamList>;