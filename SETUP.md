# Kolo Immo — Application Mobile React Native

## Prérequis

- Node.js 18+
- Java Development Kit 17 (JDK 17)
- Android Studio avec Android SDK
- Variables d'environnement :
  - `ANDROID_HOME` → chemin vers Android SDK
  - `JAVA_HOME` → chemin vers JDK 17

## Installation rapide

```bash
cd C:\wamp64\www\kolo_immo_mobile\KoloImmo

# Installer les dépendances
npm install

# Lancer le Metro bundler
npm start

# Dans un autre terminal, lancer sur Android
npm run android
```

## Configuration de l'API

Le fichier `src/api/client.ts` configure l'URL de base :

| Cas                | URL                                        |
|--------------------|--------------------------------------------|
| Émulateur Android  | `http://10.0.2.2/kolo_immo/public/api/v1`  |
| Simulateur iOS     | `http://localhost/kolo_immo/public/api/v1` |
| Appareil physique  | `http://192.168.X.X/kolo_immo/public/api/v1` |

Pour un appareil physique, remplacez l'IP dans `src/api/client.ts`.

## Structure de l'app

```
src/
├── api/          → Clients HTTP (auth, properties, bookings, payments)
├── components/   → Composants réutilisables (Button, Input, PropertyCard...)
├── navigation/   → Navigation (AuthNavigator, MainNavigator, AppNavigator)
├── screens/
│   ├── auth/     → Login, Register, OTP
│   ├── home/     → Accueil avec logements vedettes
│   ├── properties/ → Recherche et détail de propriété
│   ├── bookings/ → Liste, détail, création, paiement
│   ├── messages/ → Conversations et messagerie
│   └── profile/  → Profil utilisateur
├── store/        → AuthContext (gestion d'état auth)
├── types/        → Types TypeScript
└── utils/        → Thème, helpers (formatCFA, formatDate...)
```

## Compte de test

- Locataire : `aicha.traore@demo.com` / `Tenant@2026`
- Propriétaire : connectez-vous via l'interface web d'abord

## Lancer le backend (WAMP)

Assurez-vous que WAMP est lancé et que le backend Laravel est accessible à :
`http://localhost/kolo_immo/public`
