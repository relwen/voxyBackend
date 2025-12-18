# Documentation API OTP - VoXY

## Intégration Wasender pour l'envoi de codes OTP

### Configuration

Ajoutez ces variables dans votre fichier `.env` :

```env
ITSENDA_BEARER_TOKEN=4a09d11a8fd9559a591c16df9d04fa372cf99d258258016c7c6d467e667730fd
ITSENDA_BASE_URL=https://wasenderapi.com/api
```

### Endpoints API

#### 1. Demander un code OTP

**Endpoint:** `POST /api/request-otp`

**Body:**
```json
{
    "phone": "+33612345678"
}
```

**Réponse succès (200):**
```json
{
    "success": true,
    "message": "Code OTP envoyé avec succès",
    "phone": "+33612345678",
    "otp": "123456"  // Uniquement en mode développement (APP_DEBUG=true)
}
```

**Réponses d'erreur:**
- `404`: Numéro de téléphone non trouvé
- `403`: Compte non approuvé ou désactivé
- `500`: Erreur lors de l'envoi du SMS

#### 2. Vérifier le code OTP et se connecter

**Endpoint:** `POST /api/verify-otp`

**Body:**
```json
{
    "phone": "+33612345678",
    "otp": "123456"
}
```

**Réponse succès (200):**
```json
{
    "success": true,
    "message": "Connexion réussie",
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "+33612345678",
        "chorale_id": 1,
        "voice_part": "TENOR",
        "role": "user",
        "status": "approved",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

**Réponses d'erreur:**
- `400`: Code OTP incorrect ou expiré
- `429`: Trop de tentatives échouées (max 5)
- `422`: Données invalides

### Sécurité

- Le code OTP est valide pendant **5 minutes**
- Maximum **5 tentatives** par code OTP
- Le code OTP est stocké dans le cache Laravel
- En mode développement (`APP_DEBUG=true`), le code OTP est retourné dans la réponse pour faciliter les tests

### Exemple d'utilisation (cURL)

```bash
# 1. Demander un code OTP
curl -X POST http://127.0.0.1:8000/api/request-otp \
  -H "Content-Type: application/json" \
  -d '{"phone": "+33612345678"}'

# 2. Vérifier le code OTP
curl -X POST http://127.0.0.1:8000/api/verify-otp \
  -H "Content-Type: application/json" \
  -d '{"phone": "+33612345678", "otp": "123456"}'
```

### Exemple d'utilisation (JavaScript/Fetch)

```javascript
// 1. Demander un code OTP
const requestOTP = async (phone) => {
  const response = await fetch('http://127.0.0.1:8000/api/request-otp', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ phone }),
  });
  
  return await response.json();
};

// 2. Vérifier le code OTP
const verifyOTP = async (phone, otp) => {
  const response = await fetch('http://127.0.0.1:8000/api/verify-otp', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ phone, otp }),
  });
  
  return await response.json();
};

// Utilisation
const phone = '+33612345678';
const otpResponse = await requestOTP(phone);
console.log('OTP envoyé:', otpResponse);

// L'utilisateur entre le code reçu par SMS
const otp = '123456';
const loginResponse = await verifyOTP(phone, otp);
console.log('Token:', loginResponse.token);
```

### Notes importantes

1. Le numéro de téléphone doit être au format international (ex: +33612345678)
2. Le code OTP est un nombre à 6 chiffres
3. Après 5 tentatives échouées, un nouveau code doit être demandé
4. Le token retourné doit être utilisé dans l'en-tête `Authorization: Bearer {token}` pour les requêtes authentifiées

