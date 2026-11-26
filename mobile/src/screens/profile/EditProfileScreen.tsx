// mobile/src/screens/profile/EditProfileScreen.tsx
import React, { useState, useEffect } from 'react';
import { View, StyleSheet, Pressable, Image, Alert } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { RootNavigationProp } from '../../types/navigation';
import { ScreenTemplate } from '../../components/templates/ScreenTemplate';
import { FormField } from '../../components/molecules/FormField';
import { Button } from '../../components/atoms/Button';
import { Label } from '../../components/atoms/Label';
import { colors, spacing, radii } from '../../theme';
import { getUserProfile, saveUserProfile, UserProfile } from '../../services/storage';
import { capturePhoto, pickImageFromGallery } from '../../services/natives';
import { validateName, validateUsername, validateEmail, getFirstError } from '../../utils/validation';

export const EditProfileScreen: React.FC = () => {
    const navigation = useNavigation<RootNavigationProp>();

    const [name, setName] = useState('');
    const [username, setUsername] = useState('');
    const [email, setEmail] = useState('');
    const [avatarUri, setAvatarUri] = useState<string | null>(null);

    const [touched, setTouched] = useState<{ [key: string]: boolean }>({});
    const [isLoading, setIsLoading] = useState(false);
    const [isLoadingPhoto, setIsLoadingPhoto] = useState(false);

    useEffect(() => {
        async function loadCurrent() {
            const profile = await getUserProfile();
            if (profile) {
                setName(profile.name);
                setUsername(profile.username);
                setEmail(profile.email);
                setAvatarUri(profile.avatarUri ?? null);
            }
        }
        loadCurrent();
    }, []);

    const errors = {
        name: validateName(name),
        username: validateUsername(username),
        email: validateEmail(email),
    };

    const markTouched = (field: string) => {
        setTouched((prev) => ({ ...prev, [field]: true }));
    };

    const handleTakePhoto = async () => {
        try {
            setIsLoadingPhoto(true);
            const uri = await capturePhoto();
            if (uri) setAvatarUri(uri);
        } catch (error) {
            Alert.alert('Câmera', (error as Error).message);
        } finally {
            setIsLoadingPhoto(false);
        }
    };

    const handlePickGallery = async () => {
        try {
            setIsLoadingPhoto(true);
            const uri = await pickImageFromGallery();
            if (uri) setAvatarUri(uri);
        } catch (error) {
            Alert.alert('Galeria', (error as Error).message);
        } finally {
            setIsLoadingPhoto(false);
        }
    };

    const handleSave = async () => {
        setTouched({ name: true, username: true, email: true });

        const hasError = getFirstError(errors);
        if (hasError || isLoading) return;

        setIsLoading(true);
        try {
            const updatedProfile: UserProfile = {
                id: 'usr_me',
                name: name.trim(),
                username: username.trim(),
                email: email.trim(),
                avatarUri,
            };

            await saveUserProfile(updatedProfile);

            Alert.alert('Sucesso', 'Perfil atualizado com sucesso.', [
                { text: 'OK', onPress: () => navigation.goBack() },
            ]);
        } catch {
            Alert.alert('Erro', 'Não foi possível salvar as alterações.');
        } finally {
            setIsLoading(false);
        }
    };

    const initial = name ? name.slice(0, 2).toUpperCase() : 'SP';

    return (
        <ScreenTemplate scrollable>
            {/* Cabeçalho */}
            <View style={styles.header}>
                <Pressable onPress={() => navigation.goBack()} style={styles.backButton}>
                    <Label variant="title" style={styles.backIcon}>
                        ‹
                    </Label>
                </Pressable>
                <Label variant="title" style={styles.headerTitle}>
                    Editar perfil
                </Label>
            </View>

            {/* Editor do Avatar */}
            <View style={styles.avatarSection}>
                <View style={styles.avatarWrapper}>
                    {avatarUri ? (
                        <Image source={{ uri: avatarUri }} style={styles.avatarImage} />
                    ) : (
                        <View style={styles.avatarPlaceholder}>
                            <Label variant="title" style={styles.avatarText}>
                                {initial}
                            </Label>
                        </View>
                    )}
                </View>

                <View style={styles.photoActions}>
                    <Button
                        title="Tirar foto"
                        variant="secondary"
                        isLoading={isLoadingPhoto}
                        onPress={handleTakePhoto}
                        style={styles.photoButton}
                    />
                    <Button
                        title="Galeria"
                        variant="secondary"
                        isLoading={isLoadingPhoto}
                        onPress={handlePickGallery}
                        style={styles.photoButton}
                    />
                </View>
            </View>

            {/* Formulário de Campos */}
            <View style={styles.form}>
                <FormField
                    label="Nome público"
                    placeholder="Seu nome"
                    value={name}
                    onChangeText={setName}
                    onBlur={() => markTouched('name')}
                    errorMessage={touched.name ? errors.name : null}
                />

                <FormField
                    label="Identificador"
                    placeholder="@seuusuario"
                    value={username}
                    onChangeText={setUsername}
                    onBlur={() => markTouched('username')}
                    errorMessage={touched.username ? errors.username : null}
                    autoCapitalize="none"
                    autoCorrect={false}
                />

                <FormField
                    label="E-mail privado"
                    placeholder="voce@exemplo.com"
                    value={email}
                    onChangeText={setEmail}
                    onBlur={() => markTouched('email')}
                    errorMessage={touched.email ? errors.email : null}
                    keyboardType="email-address"
                    autoCapitalize="none"
                    autoCorrect={false}
                />

                {/* Botão Salvar */}
                <Button
                    title="Salvar"
                    variant="primary"
                    isLoading={isLoading}
                    onPress={handleSave}
                    style={styles.saveButton}
                />

                {/* Botão Cancelar */}
                <Pressable onPress={() => navigation.goBack()} style={styles.cancelLink}>
                    <Label variant="body" style={styles.cancelText}>
                        Cancelar ›
                    </Label>
                </Pressable>
            </View>
        </ScreenTemplate>
    );
};

const styles = StyleSheet.create({
    header: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: spacing.lg,
    },
    backButton: {
        paddingRight: spacing.md,
        paddingVertical: spacing.xs,
    },
    backIcon: {
        color: colors.text,
        fontSize: 28,
        lineHeight: 32,
    },
    headerTitle: {
        fontWeight: '700',
        color: colors.text,
    },
    avatarSection: {
        alignItems: 'center',
        marginBottom: spacing.xl,
    },
    avatarWrapper: {
        width: 88,
        height: 88,
        borderRadius: 44,
        backgroundColor: colors.protection,
        overflow: 'hidden',
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: spacing.md,
    },
    avatarImage: {
        width: '100%',
        height: '100%',
    },
    avatarPlaceholder: {
        width: '100%',
        height: '100%',
        alignItems: 'center',
        justifyContent: 'center',
    },
    avatarText: {
        fontSize: 32,
        color: colors.primary,
        fontWeight: '700',
    },
    photoActions: {
        flexDirection: 'row',
        gap: spacing.sm,
        width: '100%',
        justifyContent: 'center',
    },
    photoButton: {
        flex: 1,
        maxWidth: 140,
    },
    form: {
        width: '100%',
    },
    saveButton: {
        marginTop: spacing.sm,
        marginBottom: spacing.md,
    },
    cancelLink: {
        alignItems: 'center',
        paddingVertical: spacing.sm,
    },
    cancelText: {
        color: colors.text,
        fontWeight: '500',
    },
});