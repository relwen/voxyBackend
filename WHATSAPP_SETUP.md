# Configuration WhatsApp pour les Notifications d'Approbation

Ce guide explique comment configurer l'envoi de notifications WhatsApp lors de l'approbation des comptes utilisateurs.

## 📋 Fonctionnalité

Lorsqu'un administrateur ou un maestro approuve un compte utilisateur, un SMS WhatsApp est automatiquement envoyé à l'utilisateur pour l'informer de l'approbation.

## ⚙️ Configuration

### 1. Variables d'environnement à ajouter dans `.env`

Ajoutez les variables suivantes dans votre fichier `.env` :

```env
# Activation de l'envoi WhatsApp
WHATSAPP_ENABLED=true

# Choisir le provider : 'twilio', 'meta', ou 'custom'
WHATSAPP_PROVIDER=custom

# Configuration pour API personnalisée
WHATSAPP_API_URL=https://votre-api-whatsapp.com/api/send
WHATSAPP_API_KEY=votre-cle-api
WHATSAPP_SENDER_ID=votre-sender-id

# Configuration pour Twilio (si provider=twilio)
TWILIO_ACCOUNT_SID=votre-account-sid
TWILIO_AUTH_TOKEN=votre-auth-token
TWILIO_WHATSAPP_FROM=+14155238886

# Configuration pour Meta WhatsApp Business API (si provider=meta)
WHATSAPP_META_ACCESS_TOKEN=votre-access-token
WHATSAPP_META_PHONE_NUMBER_ID=votre-phone-number-id
```

### 2. Options de Configuration

#### Option A : API Personnalisée (Recommandé pour la flexibilité)

```env
WHATSAPP_ENABLED=true
WHATSAPP_PROVIDER=custom
WHATSAPP_API_URL=https://votre-api.com/api/whatsapp/send
WHATSAPP_API_KEY=votre-cle-api
WHATSAPP_SENDER_ID=votre-numero
```

L'API doit accepter une requête POST avec ce format :
```json
{
  "to": "+229XXXXXXXX",
  "message": "Votre message",
  "sender_id": "votre-sender-id"
}
```

#### Option B : Twilio WhatsApp API

1. Créez un compte sur [Twilio](https://www.twilio.com/)
2. Obtenez votre Account SID et Auth Token
3. Configurez un numéro WhatsApp via Twilio
4. Ajoutez dans `.env` :

```env
WHATSAPP_ENABLED=true
WHATSAPP_PROVIDER=twilio
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=votre-auth-token
TWILIO_WHATSAPP_FROM=+14155238886
```

#### Option C : Meta WhatsApp Business API

1. Créez une application sur [Meta for Developers](https://developers.facebook.com/)
2. Configurez WhatsApp Business API
3. Obtenez votre Access Token et Phone Number ID
4. Ajoutez dans `.env` :

```env
WHATSAPP_ENABLED=true
WHATSAPP_PROVIDER=meta
WHATSAPP_META_ACCESS_TOKEN=votre-access-token
WHATSAPP_META_PHONE_NUMBER_ID=votre-phone-number-id
```

### 3. Désactiver temporairement

Pour désactiver l'envoi WhatsApp sans modifier le code :

```env
WHATSAPP_ENABLED=false
```

Les messages seront simulés dans les logs mais l'approbation continuera de fonctionner normalement.

## 📱 Format des numéros de téléphone

Le service formate automatiquement les numéros au format international.

- Format accepté : `+229XXXXXXXX` (international avec +)
- Format accepté : `0XXXXXXXX` (format local, sera converti en +229XXXXXXXX)
- Format accepté : `229XXXXXXXX` (sera converti en +229XXXXXXXX)

Par défaut, le code pays `+229` (Bénin) est utilisé. Modifiez la méthode `formatPhoneNumber()` dans `WhatsAppService.php` pour changer le code pays par défaut.

## 🔍 Logs et Debugging

Tous les envois WhatsApp sont loggés dans `storage/logs/laravel.log` :

```php
// Logs d'information
Log::info('WhatsApp envoyé via [provider] à: +229XXXXXXXX');

// Logs d'erreur
Log::error('Erreur lors de l\'envoi WhatsApp: [message d\'erreur]');
```

## 📝 Message envoyé

Le message par défaut envoyé lors de l'approbation :

```
Bonjour [Nom],

Votre compte VoXY Box a été approuvé avec succès ! 🎉

Vous pouvez maintenant vous connecter à l'application et profiter de toutes les fonctionnalités.

Merci de votre confiance.

L'équipe VoXY Box
```

Pour modifier le message, éditez la méthode `sendApprovalNotification()` dans `app/Services/WhatsAppService.php`.

## ✅ Test

1. Assurez-vous que `WHATSAPP_ENABLED=true` dans `.env`
2. Approuvez un utilisateur depuis l'interface d'administration
3. Vérifiez les logs dans `storage/logs/laravel.log`
4. L'utilisateur devrait recevoir le message WhatsApp sur son numéro enregistré

## 🛠️ Personnalisation

### Changer le message d'approbation

Modifiez la méthode `sendApprovalNotification()` dans `app/Services/WhatsAppService.php` :

```php
public function sendApprovalNotification($user)
{
    if (!$user->phone) {
        return false;
    }

    $message = "Votre message personnalisé ici...";
    
    return $this->sendMessage($user->phone, $message);
}
```

### Changer le code pays par défaut

Dans `WhatsAppService.php`, modifiez la méthode `formatPhoneNumber()` :

```php
// Remplacer +229 par votre code pays (ex: +33 pour la France)
$phone = '+33' . substr($phone, 1);
```

## 📞 Support

En cas de problème, vérifiez :
1. Les variables d'environnement sont bien définies
2. Les credentials de l'API sont valides
3. Le numéro de téléphone de l'utilisateur est au bon format
4. Les logs dans `storage/logs/laravel.log`

