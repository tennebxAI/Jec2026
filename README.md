# 🎯 BÜFA Composite Systems - JEC World 2026 Landing Page

Mehrsprachige PDF-Landing-Page für die JEC World 2026 Messe in Paris.

## 📋 Features

### ✅ Aktuell implementiert:

- **Zweisprachig:** DE/EN Sprachwechsel
- **Responsive Design:** Desktop, Tablet, Mobile optimiert
- **Admin-Panel:** Passwort-geschütztes Backend
- **PDF-Upload:** 
  - Hochladen oder Link eingeben
  - EN + DE Versionen
  - EN + DE Vorschaubilder
- **Kategorien:** Dynamische Filter-Buttons
- **Sticky Header:** Mit Logo, Sprachwechsel, Contact-Button
- **BÜFA Corporate Identity:** Eckiges Design, BÜFA-Blau (#003d7a)

### 🚧 TODO (für Google Jules):

- **Drag & Drop Sortierung:** PDFs im Admin per Drag & Drop sortieren
- **Mehrfachkategorisierung:** PDFs mehreren Kategorien zuordnen

Siehe **FEATURES-TODO.md** für Details!

## 🏗️ Technologie

- **Frontend:** HTML5, CSS3, JavaScript (ES6+)
- **Backend:** PHP 7.4+
- **Datenbank:** JSON-File-Storage (pdfs.json)
- **Authentication:** PHP Sessions

## 📁 Dateistruktur

```
├── config.php              # Login-Konfiguration
├── login.php               # Login-Seite
├── logout.php              # Logout-Handler
├── admin-server.php        # Admin-Panel
├── api.php                 # Backend-API (aktuell)
├── api-new.php            # Backend-API (mit neuen Features)
├── index.html              # Öffentliche Landing Page
├── pdfs/                   # Upload-Ordner (nicht in Git)
└── pdfs.json              # PDF-Datenbank (nicht in Git)
```

## 🚀 Quick Start

### 1. Passwort ändern
In `config.php` Zeile 11

### 2. Admin-Login
URL: `/admin-server.php`  
Username: `admin`  
Passwort: (dein Passwort)

### 3. Erste PDFs hochladen

## 📞 Support

Bei Fragen: Projekt-Owner kontaktieren

---

**Version:** 1.0.0  
**JEC World 2026** - Paris, März 2026
