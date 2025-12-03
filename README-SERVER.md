# PDF Landing Page - Server-Version (PHP)

## 🚀 Vorteile der Server-Version

✅ **Keine manuelle JSON-Verwaltung** - Alles wird automatisch gespeichert  
✅ **Direkter Datei-Upload** - PDFs und Bilder direkt über die Admin-Seite hochladen  
✅ **Automatische Speicherung** - pdfs.json wird automatisch aktualisiert  
✅ **Sicherer** - Dateinamen werden automatisch bereinigt  
✅ **Einfacher** - Kein manuelles Herunterladen/Hochladen von JSON-Dateien  

## 📋 Voraussetzungen

- Webserver mit PHP 7.4 oder höher
- Schreibrechte für Ordner (wird automatisch erstellt)

## 📁 Dateien

```
/
├── index.html              (Öffentliche Landing Page)
├── admin-server.php        (Admin-Bereich mit Upload)
├── api.php                 (Backend-API)
├── pdfs.json              (Automatisch generiert)
└── pdfs/                  (Automatisch erstellt)
    ├── beispiel-1234.pdf
    └── beispiel-thumb-1234.jpg
```

## 🔧 Installation

### 1. Dateien hochladen
Laden Sie alle Dateien auf Ihren Webserver hoch:
- `index.html`
- `admin-server.php`
- `api.php`

### 2. Ordner-Berechtigungen
Der `pdfs/` Ordner wird automatisch erstellt. Falls nicht, erstellen Sie ihn manuell:
```bash
mkdir pdfs
chmod 755 pdfs
```

### 3. Fertig!
Öffnen Sie `admin-server.php` in Ihrem Browser und beginnen Sie mit dem Upload.

## 📤 PDFs hochladen

1. Öffnen Sie `https://ihre-domain.de/admin-server.php`
2. Füllen Sie das Formular aus:
   - **Titel**: Name des Dokuments
   - **Beschreibung**: Optional
   - **Vorschaubild**: JPG, PNG, GIF oder WEBP (max. 10 MB)
   - **PDF-Datei**: PDF-Dokument (max. 10 MB)
3. Klicken Sie auf "PDF hochladen"
4. Das PDF erscheint sofort auf `index.html`

## 🔒 Sicherheit

### Admin-Bereich schützen

**Empfohlen:** Schützen Sie die Admin-Seite mit einem Passwort.

#### Option 1: .htaccess (Apache)

Erstellen Sie eine `.htaccess` Datei im gleichen Ordner:

```apache
<Files "admin-server.php">
    AuthType Basic
    AuthName "Admin-Bereich"
    AuthUserFile /pfad/zu/.htpasswd
    Require valid-user
</Files>
```

Erstellen Sie die `.htpasswd` Datei:
```bash
htpasswd -c .htpasswd admin
```

#### Option 2: PHP Session-Login

Fügen Sie am Anfang von `admin-server.php` hinzu:

```php
<?php
session_start();

// Einfacher Login-Check
if (!isset($_SESSION['logged_in'])) {
    if (isset($_POST['password']) && $_POST['password'] === 'IhrSicheresPasswort123') {
        $_SESSION['logged_in'] = true;
    } else {
        // Login-Formular anzeigen
        echo '<form method="post"><input type="password" name="password"><button>Login</button></form>';
        exit;
    }
}
?>
```

## ⚙️ Konfiguration

### Dateigrößen-Limit ändern

In `api.php`, Zeile 7:
```php
$maxFileSize = 10 * 1024 * 1024; // 10 MB -> ändern Sie die Zahl
```

### Erlaubte Bildformate ändern

In `api.php`, Zeile 6:
```php
$allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
```

### Upload-Ordner ändern

In `api.php`, Zeile 5:
```php
$uploadsDir = 'pdfs/'; // Ändern Sie den Pfad
```

## 🎨 Design anpassen

### Farben ändern
Suchen Sie in `index.html` und `admin-server.php` nach:
```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

### Logo hinzufügen
Fügen Sie im `<header>` ein `<img>` Tag hinzu:
```html
<header>
    <img src="logo.png" alt="Logo" style="max-width: 200px; margin-bottom: 20px;">
    <h1>📚 Dokumenten-Bibliothek</h1>
    ...
</header>
```

## 🐛 Fehlerbehebung

### "Permission denied" Fehler
```bash
chmod 755 pdfs/
chown www-data:www-data pdfs/  # Linux
```

### "File too large" Fehler
Erhöhen Sie die PHP-Limits in `php.ini`:
```ini
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
```

### PDFs werden nicht angezeigt
1. Prüfen Sie, ob `pdfs.json` existiert und lesbar ist
2. Öffnen Sie die Browser-Konsole (F12) auf Fehler
3. Prüfen Sie die Dateipfade in `pdfs.json`

### API funktioniert nicht
1. Prüfen Sie ob PHP aktiviert ist (erstellen Sie `info.php` mit `<?php phpinfo(); ?>`)
2. Prüfen Sie Server-Logs auf Fehler
3. Stellen Sie sicher, dass `api.php` ausführbar ist

## 📊 API-Endpunkte

Die `api.php` bietet folgende Endpunkte:

### Upload
```
POST api.php?action=upload
FormData: title, description, pdf (file), thumbnail (file)
```

### Löschen
```
POST api.php?action=delete
JSON: { "file": "pdfs/beispiel.pdf" }
```

### Auflisten
```
GET api.php?action=list
Returns: { "success": true, "pdfs": [...] }
```

## 🔄 Migration von lokaler Version

Falls Sie bereits die lokale Version (mit manuellem JSON) verwenden:

1. Laden Sie Ihre PDFs aus dem `pdfs/` Ordner auf den Server hoch
2. Die neue Admin-Seite wird automatisch eine neue `pdfs.json` erstellen
3. Laden Sie jedes PDF über die Admin-Oberfläche neu hoch, oder
4. Kopieren Sie Ihre alte `pdfs.json` und passen Sie die Pfade an

## 💡 Tipps

- **Backups**: Sichern Sie regelmäßig den `pdfs/` Ordner und `pdfs.json`
- **Performance**: Bei vielen PDFs (>50) sollten Sie Pagination hinzufügen
- **SEO**: Verwenden Sie aussagekräftige Dateinamen und Beschreibungen
- **Mobile**: Die Seite ist bereits responsive-optimiert

## 🆘 Support

Bei Problemen:
1. Prüfen Sie die Browser-Konsole (F12)
2. Prüfen Sie die PHP-Error-Logs auf dem Server
3. Stellen Sie sicher, dass alle Dateien korrekt hochgeladen wurden
4. Prüfen Sie die Ordner-Berechtigungen

## 📝 Lizenz

Diese Software ist frei verwendbar für private und kommerzielle Projekte.
