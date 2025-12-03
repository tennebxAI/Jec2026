# 🔐 BÜFA PDF Landing Page - Login System

## 📋 Setup-Anleitung

### 1. Dateien hochladen

Lade folgende Dateien auf deinen Server hoch:

```
✅ config.php          - Konfiguration & Passwort
✅ login.php           - Login-Seite
✅ logout.php          - Logout-Script
✅ admin-server.php    - Admin-Panel (jetzt geschützt)
✅ api.php             - Backend
✅ index.html          - Öffentliche Seite
```

### 2. Passwort ändern (WICHTIG!)

**Öffne `config.php` und ändere das Passwort:**

```php
// Zeile 10-11:
$ADMIN_USERNAME = 'admin';
$ADMIN_PASSWORD = 'buefa2026'; // ← HIER DEIN PASSWORT EINGEBEN!
```

**Empfehlung für sicheres Passwort:**
- Mindestens 12 Zeichen
- Groß- und Kleinbuchstaben
- Zahlen und Sonderzeichen
- Beispiel: `BÜFA_JEC2026!Secure`

### 3. Login

**URL:** `https://jec2026.buefa-composites.com/admin-server.php`

**Standard-Login:**
- Benutzername: `admin`
- Passwort: `buefa2026` (oder dein geändertes Passwort)

**"Angemeldet bleiben"** - Checkbox:
- Aktiviert: Bleibt 7 Tage eingeloggt
- Deaktiviert: Automatischer Logout nach 2 Stunden Inaktivität

### 4. Logout

**Oben rechts im Admin-Panel:**
- Klick auf **"🔓 Logout"**
- Bestätigung → Ausgeloggt

---

## 🔒 Sicherheits-Features

✅ **Passwort-Hashing** - Passwort wird verschlüsselt gespeichert  
✅ **Session-Management** - Sichere PHP Sessions  
✅ **Timeout** - Auto-Logout nach 2 Stunden Inaktivität  
✅ **Remember Me** - Optional 7 Tage Cookie  
✅ **Redirect-Schutz** - Nicht eingeloggt → Automatisch zu Login  

---

## ❓ Häufige Fragen

### Passwort vergessen?

1. Öffne `config.php` auf dem Server
2. Ändere `$ADMIN_PASSWORD` Zeile 11
3. Speichern → Neues Passwort aktiv

### Benutzername ändern?

In `config.php` Zeile 10:
```php
$ADMIN_USERNAME = 'deinname'; // Statt 'admin'
```

### Session-Timeout ändern?

In `config.php` Zeile 17:
```php
define('SESSION_TIMEOUT', 7200); // Sekunden (7200 = 2 Stunden)
```

### Remember Me Dauer ändern?

In `config.php` Zeile 18:
```php
define('REMEMBER_ME_DURATION', 604800); // Sekunden (604800 = 7 Tage)
```

---

## 🚨 Wichtige Hinweise

### Vor dem Live-Gang:

1. ✅ **Passwort geändert?** - NIEMALS Standardpasswort online lassen!
2. ✅ **HTTPS aktiviert?** - Login nur über verschlüsselte Verbindung
3. ✅ **Backup erstellt?** - Von allen Dateien

### Zugriff auf Admin:

- **Ohne Login:** Redirect zu `login.php`
- **Mit Login:** Voller Zugriff auf Admin-Panel
- **Nach Logout:** Erneuter Login erforderlich

---

## 📞 Support

Bei Problemen:
1. Prüfe ob `config.php` richtig hochgeladen wurde
2. Prüfe ob PHP Sessions aktiviert sind (Standard auf allen Servern)
3. Prüfe ob Cookies im Browser aktiviert sind

---

## ✨ Features

- 🎨 BÜFA Corporate Design
- 📱 Mobile-optimiert
- 🔐 Sicher & verschlüsselt
- ⏱️ Auto-Timeout
- 💾 Remember Me Cookie
- 🚪 Einfacher Logout

---

**© 2026 BÜFA Composite Systems GmbH & Co. KG**
