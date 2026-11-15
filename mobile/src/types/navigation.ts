// mobile/src/types/navigation.ts
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { BottomTabNavigationProp } from '@react-navigation/bottom-tabs';

// Definição das rotas e seus parâmetros
export type RootStackParamList = {
  Login: undefined;
  Register: undefined;
  RecoverAccess: undefined;
  Main: undefined;
  SearchUsers: undefined;
};

export type MainTabParamList = {
  Conversations: undefined;
  Groups: undefined;
  Profile: undefined;
};

export type RootNavigationProp = NativeStackNavigationProp<RootStackParamList>;
export type MainTabNavigationProp = BottomTabNavigationProp<MainTabParamList>;