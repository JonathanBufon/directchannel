# DirectChannel - Frontend Mobile

Aplicativo móvel desenvolvido com **React Native** e **Expo**, seguindo a metodologia **Atomic Design**, tipagem estrita com **TypeScript** e padrões de segurança com validação no cliente.

---

## 📋 Pré-requisitos

Antes de iniciar, certifique-se de ter instalado em sua máquina:
- **Node.js** (versão 18 LTS ou 20 LTS recomendada)
- **npm** ou **yarn**
- **Dispositivo Físico:** App **Expo Go** instalado no celular ([Android](https://play.google.com/store/apps/details?id=host.exp.exponent) / [iOS](https://apps.apple.com/app/expo-go/id982107779)) **OU**
- **Emulador:** Android Studio (com dispositivo virtual configurado) ou Xcode (macOS).

---

## 🚀 Como executar o projeto

1. **Acesse a pasta do frontend:**
   ```bash
   cd mobile
   ```

2. **Instale as dependências:**
   ```bash
   npm install
   ```

3. **Inicie o servidor de desenvolvimento do Expo:**
   ```bash
   npx expo start
   ```

4. **Abra o aplicativo:**
   - **No celular físico:** Abra o aplicativo **Expo Go** e escaneie o código QR gerado no terminal.
     *(Certifique-se de que o computador e o celular estejam conectados na mesma rede Wi-Fi).*
   - **No emulador Android:** Com o emulador já aberto, pressione a tecla `a` no terminal.
   - **No simulador iOS (macOS):** Pressione a tecla `i` no terminal.
   - **Se houver erro de cache:** Execute `npx expo start -c`.

---

## 🏛️ Estrutura e Padrões de Código

O código segue a arquitetura **Atomic Design**:

- `src/theme/`: Tokens centralizados de cores, tipografia, espaçamentos e raios (sem valores soltos no código).
- `src/utils/`: Funções puras de utilidades e validações sem JSX (`validation.ts`).
- `src/components/atoms/`: Componentes visuais mínimos sem lógica de negócio (`Label`, `Input`, `Button`).
- `src/components/molecules/`: Junção de átomos com propósito (`FormField`, `ErrorMessage`).
- `src/components/templates/`: Esqueletos de layout e gestão de teclado (`CenteredTemplate`).
- `src/components/organisms/`: Seções completas de tela; gerenciam estado de input mas **não chamam API nem navegação diretamente** (`LoginForm`).
- `src/screens/`: Telas "inteligentes" que conectam serviços, navegação e templates (`LoginScreen`).

### Convenções de desenvolvimento:
- Identificadores (arquivos, variáveis, funções, componentes): **Inglês**.
- Comentários explicativos: **Português**.
```