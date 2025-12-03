# 🚀 Features TODO - For Google Jules

## 🎯 Aufgabe: 2 Features implementieren

---

## Feature 1: Drag & Drop Sortierung

### Ziel:
PDFs im Admin-Panel per Drag & Drop sortieren können.

### Technische Details:

**Bibliothek:**
- SortableJS von CDN: `https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js`

**Datei:** `admin-server.php`

**Änderungen:**

1. **SortableJS einbinden** (im `<head>`):
```html
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
```

2. **CSS hinzufügen** (im `<style>` Bereich):
```css
.drag-handle {
    cursor: move;
    font-size: 1.5rem;
    color: #999;
    padding: 0 10px;
    user-select: none;
}
.drag-handle:hover {
    color: #003d7a;
}
.sortable-ghost {
    opacity: 0.4;
    background: #f0f7ff;
}
.sortable-drag {
    opacity: 1;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}
```

3. **Drag-Handle zu jedem PDF-Item hinzufügen:**
In `loadPDFs()` Funktion, im `item.innerHTML`:
```javascript
<div class="drag-handle" title="Ziehen zum Sortieren">⋮⋮</div>
```

4. **SortableJS initialisieren** (nach `loadPDFs()`):
```javascript
let sortableInstance = null;

function initSortable() {
    const list = document.getElementById('pdf-list');
    if (!list || sortableInstance) return;
    
    sortableInstance = Sortable.create(list, {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'sortable-ghost',
        dragClass: 'sortable-drag',
        onEnd: function(evt) {
            updatePDFOrder();
        }
    });
}

async function updatePDFOrder() {
    const items = document.querySelectorAll('.pdf-item');
    const order = [];
    
    items.forEach((item, index) => {
        order.push({
            index: parseInt(item.dataset.index),
            order: index + 1
        });
    });
    
    try {
        const response = await fetch('api.php?action=reorder', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                order: JSON.stringify(order)
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('Reihenfolge gespeichert', 'success');
        } else {
            showMessage('Fehler: ' + result.message, 'error');
        }
    } catch (error) {
        showMessage('Fehler beim Speichern der Reihenfolge', 'error');
    }
}

// Am Ende von loadPDFs() aufrufen:
initSortable();
```

5. **data-index Attribut hinzufügen:**
In der `loadPDFs()` Funktion:
```javascript
item.dataset.index = index;
```

**API-Support:**
- `api-new.php` hat bereits die `handleReorder()` Funktion
- Endpoint: `POST api.php?action=reorder`
- Body: `{ order: [{index: 0, order: 1}, ...] }`

---

## Feature 2: Mehrfachkategorisierung

### Ziel:
PDFs können mehreren Kategorien zugeordnet werden.

### Technische Details:

**Datenstruktur-Änderung:**
```javascript
// ALT (Single Category):
{ "category": "products" }

// NEU (Multi Category):
{ "categories": ["products", "sustainability", "marine"] }
```

---

### Teil 2A: Admin-Panel (`admin-server.php`)

**Änderungen:**

1. **CSS für Checkboxen hinzufügen:**
```css
.checkbox-group {
    border: 2px solid #e2e8f0;
    padding: 15px;
    border-radius: 8px;
    max-height: 250px;
    overflow-y: auto;
    background: #fafafa;
}
.checkbox-item {
    display: flex;
    align-items: center;
    padding: 8px;
    margin: 5px 0;
    border-radius: 4px;
}
.checkbox-item:hover {
    background: #f0f7ff;
}
.checkbox-item input[type="checkbox"] {
    margin-right: 10px;
    width: 18px;
    height: 18px;
    cursor: pointer;
}
```

2. **Dropdown durch Checkbox-Gruppe ersetzen:**
Im HTML, finde das Kategorie-Dropdown und ersetze es mit:
```html
<div class="form-group">
    <label>Kategorien * (mehrere wählbar)</label>
    <div id="category-checkboxes" class="checkbox-group">
        <!-- Dynamisch gefüllt -->
    </div>
    <small style="color: #718096; margin-top: 5px; display: block;">
        Mindestens eine Kategorie auswählen
    </small>
</div>
```

3. **JavaScript-Funktionen hinzufügen:**
```javascript
// Checkboxen generieren
function loadCategoryCheckboxes() {
    const container = document.getElementById('category-checkboxes');
    if (!container) return;
    
    container.innerHTML = '';
    
    const selectableCategories = categories.filter(cat => cat !== 'All');
    
    selectableCategories.forEach(cat => {
        const div = document.createElement('div');
        div.className = 'checkbox-item';
        
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.id = `cat-${cat}`;
        checkbox.value = cat;
        checkbox.name = 'categories[]';
        checkbox.className = 'category-checkbox';
        
        const label = document.createElement('label');
        label.htmlFor = `cat-${cat}`;
        label.textContent = cat;
        label.style.cursor = 'pointer';
        
        div.appendChild(checkbox);
        div.appendChild(label);
        container.appendChild(div);
    });
}

// Ausgewählte Kategorien sammeln
function getSelectedCategories() {
    const checkboxes = document.querySelectorAll('.category-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

// In loadCategories() am Ende aufrufen:
loadCategoryCheckboxes();
```

4. **Upload-Funktion anpassen:**
In der Form-Submit-Handler, finde:
```javascript
formData.append('category', ...);
```

Ersetze durch:
```javascript
const selectedCategories = getSelectedCategories();
if (selectedCategories.length === 0) {
    showMessage('Bitte mindestens eine Kategorie auswählen', 'error');
    return;
}
selectedCategories.forEach(cat => {
    formData.append('categories[]', cat);
});
```

5. **PDF-Liste anzeigen - Kategorie-Badges:**
In `loadPDFs()`, im `item.innerHTML` hinzufügen:
```javascript
const pdfCategories = pdf.categories || [pdf.category] || ['products'];
const categoryBadges = pdfCategories.map(cat => 
    `<span style="background: #e6f2ff; color: #003d7a; padding: 2px 6px; border-radius: 3px; font-size: 0.7rem; margin-right: 5px;">${cat}</span>`
).join('');

// Dann im HTML:
<div style="margin-top: 5px;">${categoryBadges}</div>
```

---

### Teil 2B: Frontend (`index.html`)

**Änderungen:**

1. **PDFs nach Order sortieren:**
In `loadPDFs()`, nach `const pdfs = data.pdfs || [];`:
```javascript
// Nach Order sortieren
pdfs.sort((a, b) => {
    const orderA = a.order || 999;
    const orderB = b.order || 999;
    return orderA - orderB;
});
```

2. **Multi-Kategorie Support in Card-Rendering:**
```javascript
// Kategorien ermitteln (Multi-Support + Legacy-Support)
const pdfCategories = pdf.categories || (pdf.category ? [pdf.category] : ['products']);

// data-categories als JSON speichern
card.dataset.categories = JSON.stringify(pdfCategories);

// Erste Kategorie für einfache Filter (Legacy)
card.dataset.category = pdfCategories[0].toLowerCase().replace(/\s+/g, '-');
```

3. **Filter-Logik anpassen:**
In `loadFilterButtons()`, im Button Click-Handler:
```javascript
btn.addEventListener('click', function() {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    
    const category = this.dataset.category;
    const cards = document.querySelectorAll('.pdf-card');
    
    cards.forEach(card => {
        const pdfCategories = JSON.parse(card.dataset.categories || '[]');
        
        // Zeigen wenn: "All" ODER irgendeine Kategorie passt
        if (category === 'all' || pdfCategories.some(cat => 
            cat.toLowerCase().replace(/\s+/g, '-') === category
        )) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
});
```

---

## 🔄 API-Wechsel

**Nach erfolgreicher Implementierung:**

1. Backup erstellen: `cp api.php api-old.php`
2. Neue API aktivieren: `cp api-new.php api.php`
3. Testen!

---

## ✅ Testing Checklist

Nach Implementierung testen:

**Drag & Drop:**
- [ ] SortableJS lädt ohne Fehler
- [ ] Drag-Handle erscheint
- [ ] PDFs lassen sich verschieben
- [ ] Reihenfolge wird gespeichert
- [ ] Nach Reload: Neue Reihenfolge bleibt

**Multi-Kategorie:**
- [ ] Checkboxen statt Dropdown
- [ ] Mehrere wählbar
- [ ] Upload funktioniert
- [ ] PDFs werden in richtigen Kategorien angezeigt
- [ ] Filter zeigt PDFs in mehreren Kategorien

---

## 🐛 Bekannte Herausforderungen

1. **Legacy-Daten:** Bestehende PDFs haben `category` statt `categories`
   - Lösung: Code unterstützt beides (Fallback)

2. **Order-Feld fehlt:** Alte PDFs haben kein `order` Feld
   - Lösung: API setzt Default 999

3. **Checkbox-Validierung:** Mindestens 1 Kategorie nötig
   - Lösung: Frontend-Check vor Submit

---

## 📋 Code-Style Guidelines

- **BÜFA-Blau:** #003d7a
- **Eckiges Design:** border-radius: 0
- **Konsistente Benennung:** camelCase für JS, kebab-case für CSS
- **Kommentare:** Auf Deutsch
- **Fehlerbehandlung:** try-catch + User-Feedback

---

## 🎯 Erfolg = 

✅ Drag & Drop funktioniert  
✅ Multi-Kategorie funktioniert  
✅ Keine Console-Errors  
✅ Bestehende Features funktionieren weiter  
✅ Code ist clean und kommentiert

---

**Viel Erfolg, Google Jules! 🚀**
