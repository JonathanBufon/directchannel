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
export type MainTabNavigationProp = BottomTabNavigationProp<MainTabParamList>;