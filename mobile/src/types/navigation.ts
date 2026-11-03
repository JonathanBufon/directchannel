// mobile/src/types/navigation.ts
import { NativeStackNavigationProp } from '@react-navigation/native-stack';

// Definição das rotas e seus parâmetros
export type RootStackParamList = {
  Login: undefined;
  Main: undefined; // Área autenticada (Perfil e Recursos Nativos)
};

export type RootNavigationProp = NativeStackNavigationProp<RootStackParamList>;