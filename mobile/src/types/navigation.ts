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
export type MainTabNavigationProp = BottomTabNavigationProp<MainTabParamList>;