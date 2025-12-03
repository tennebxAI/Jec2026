<?php
require_once 'config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Admin - BÜFA PDF Verwaltung</title>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
        }

        .top-header {
            background: #003d7a;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-header .logo {
            font-size: 1.3rem;
            font-weight: 600;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        header {
            text-align: center;
            margin-bottom: 40px;
            background: white;
            padding: 40px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #003d7a;
            font-weight: 700;
        }

        header p {
            font-size: 1.1rem;
            color: #666;
        }

        .back-link {
            display: inline-block;
            color: #003d7a;
            text-decoration: none;
            margin-bottom: 20px;
            padding: 10px 20px;
            background: white;
            border-radius: 8px;
            transition: background 0.3s ease;
            font-weight: 500;
        }

        .back-link:hover {
            background: #e6f2ff;
        }

        .view-page-btn {
            display: inline-block;
            color: white;
            background: #003d7a;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: background 0.3s ease;
            font-weight: 600;
        }

        .view-page-btn:hover {
            background: #002d5c;
        }

        .logout-btn {
            display: inline-block;
            color: white;
            background: #fc8181;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: background 0.3s ease;
            font-weight: 600;
        }

        .logout-btn:hover {
            background: #f56565;
        }

        .category-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #f0f7ff;
            border: 2px solid #003d7a;
            border-radius: 6px;
            font-size: 0.9rem;
            color: #003d7a;
            font-weight: 600;
        }

        .category-badge button {
            background: none;
            border: none;
            color: #fc8181;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 0;
            line-height: 1;
        }

        .category-badge button:hover {
            color: #f56565;
        }

        .upload-section {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 40px;
        }

        .upload-section h2 {
            color: #2d3748;
            margin-bottom: 25px;
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #4a5568;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .form-group input[type="text"],
        .form-group input[type="url"],
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-family: inherit;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="url"]:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #003d7a;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .file-input-wrapper {
            position: relative;
            display: block;
            width: 100%;
        }

        .file-input-label {
            display: block;
            padding: 20px;
            background: #f7fafc;
            border: 2px dashed #cbd5e0;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            color: #4a5568;
        }

        .file-input-label:hover {
            background: #edf2f7;
            border-color: #003d7a;
        }

        .file-input-label.has-file {
            background: #e6f2ff;
            border-color: #003d7a;
            color: #003d7a;
        }

        .file-input {
            display: none;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .add-btn {
            flex: 1;
            padding: 14px 30px;
            background: #003d7a;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .add-btn:hover:not(:disabled) {
            background: #002d5c;
        }

        .add-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

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

        .reset-btn {
            padding: 14px 30px;
            background: #e2e8f0;
            color: #4a5568;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .reset-btn:hover {
            background: #cbd5e0;
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
            display: none;
        }

        .message.success {
            background: #48bb78;
            color: white;
        }

        .message.error {
            background: #fc8181;
            color: white;
        }

        .message.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .pdf-list {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .pdf-list h2 {
            color: #2d3748;
            margin-bottom: 25px;
            font-size: 1.5rem;
        }

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

        .pdf-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f7fafc;
            border-radius: 8px;
            margin-bottom: 15px;
            transition: background 0.3s ease;
        }

        .pdf-item:hover {
            background: #edf2f7;
        }

        .pdf-item-thumbnail {
            width: 60px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
            background: #003d7a;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .pdf-item-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 4px;
        }

        .pdf-item-info {
            flex: 1;
            min-width: 0;
        }

        .pdf-item-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
        }

        .pdf-item-meta {
            font-size: 0.85rem;
            color: #718096;
        }

        .delete-btn {
            padding: 8px 16px;
            background: #fc8181;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
            flex-shrink: 0;
        }

        .delete-btn:hover {
            background: #f56565;
        }

        .edit-btn {
            padding: 8px 16px;
            background: #4299e1;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
            flex-shrink: 0;
            margin-right: 10px;
        }

        .edit-btn:hover {
            background: #3182ce;
        }

        .no-pdfs-message {
            text-align: center;
            padding: 40px;
            color: #718096;
        }

        footer {
            text-align: center;
            color: #666;
            margin-top: 40px;
            padding: 20px;
            background: white;
            border-radius: 8px;
        }

        @media (max-width: 768px) {
            .upload-section,
            .pdf-list {
                padding: 25px;
            }

            .button-group {
                flex-direction: column;
            }

            header h1 {
                font-size: 1.8rem;
            }

            .pdf-item {
                flex-wrap: wrap;
            }

            .delete-btn {
                width: 100%;
                margin-top: 10px;
            }

            .edit-btn {
                width: 100%;
                margin-top: 10px;
                margin-right: 0;
            }

            .top-header {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }

            .view-page-btn {
                width: 100%;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px 15px;
            }

            .upload-section,
            .pdf-list {
                padding: 20px;
            }

            header {
                padding: 30px 15px;
            }

            header h1 {
                font-size: 1.5rem;
            }

            .form-group label {
                font-size: 0.9rem;
            }

            .add-btn, .reset-btn {
                padding: 12px 20px;
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
    <div class="top-header">
        <div class="logo">BÜFA Composite Systems</div>
    </div>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="index.html" class="back-link">← Zurück zur Übersicht</a>
                <a href="https://jec2026.buefa-composites.com/index.html" target="_blank" class="view-page-btn">🔗 Öffentliche Seite öffnen</a>
            </div>
            <a href="logout.php" class="logout-btn" onclick="return confirm('Wirklich ausloggen?')">🔓 Logout</a>
        </div>

        <header>
            <h1>🔐 PDF Verwaltung</h1>
            <p>Verwalten Sie Ihre PDF-Dokumente</p>
        </header>

        <div class="message" id="message"></div>

        <!-- Kategorie-Verwaltung -->
        <div class="upload-section">
            <h2>📂 Kategorien verwalten</h2>
            
            <div style="margin-bottom: 20px;">
                <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                    <input type="text" id="new-category-input" placeholder="Neue Kategorie (z.B. Marine, Wind Energy...)" style="flex: 1; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 1rem;">
                    <button onclick="addCategory()" style="padding: 12px 24px; background: #003d7a; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">+ Hinzufügen</button>
                </div>
                
                <div id="categories-list" style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <!-- Kategorien werden hier angezeigt -->
                </div>
            </div>
        </div>

        <!-- Upload-Formular -->
        <div class="upload-section">
            <h2>Neues PDF hinzufügen</h2>
            
            <form id="upload-form" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="pdf-title-input">Titel *</label>
                    <input type="text" id="pdf-title-input" name="title" placeholder="z.B. Produktkatalog 2024" required>
                </div>

                <div class="form-group">
                    <label for="pdf-description-input">Beschreibung</label>
                    <textarea id="pdf-description-input" name="description" placeholder="Kurze Beschreibung des Dokuments (optional)"></textarea>
                </div>

                <div class="form-group">
                    <label>Kategorien * (mehrere wählbar)</label>
                    <div id="category-checkboxes" class="checkbox-group">
                        <!-- Dynamisch gefüllt -->
                    </div>
                    <small style="color: #718096; margin-top: 5px; display: block;">
                        Mindestens eine Kategorie auswählen
                    </small>
                </div>

                <div class="form-group">
                    <label>PDF Quelle *</label>
                    <div style="display: flex; gap: 15px; margin-bottom: 15px; flex-wrap: wrap;">
                        <label style="display: flex; align-items: center; cursor: pointer; padding: 10px 15px; background: #f7fafc; border-radius: 6px; border: 2px solid #cbd5e0; transition: all 0.3s;">
                            <input type="radio" name="pdf-source" value="upload" checked onchange="togglePdfSource()" style="margin-right: 8px;"> 
                            <span>📤 PDF hochladen</span>
                        </label>
                        <label style="display: flex; align-items: center; cursor: pointer; padding: 10px 15px; background: #f7fafc; border-radius: 6px; border: 2px solid #cbd5e0; transition: all 0.3s;">
                            <input type="radio" name="pdf-source" value="link" onchange="togglePdfSource()" style="margin-right: 8px;">
                            <span>🔗 PDF-Link verwenden</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Vorschaubild (JPG, PNG) *</label>
                    <div class="file-input-wrapper">
                        <label for="thumbnail-input" class="file-input-label" id="thumbnail-label">
                            📷 Vorschaubild (EN) auswählen
                        </label>
                        <input type="file" id="thumbnail-input" name="thumbnail" class="file-input" accept="image/*" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Vorschaubild (Deutsch) - Optional</label>
                    <div class="file-input-wrapper">
                        <label for="thumbnail-input-de" class="file-input-label" id="thumbnail-label-de">
                            📷 Vorschaubild (DE) auswählen
                        </label>
                        <input type="file" id="thumbnail-input-de" name="thumbnail_de" class="file-input" accept="image/*">
                    </div>
                    <small style="color: #718096; margin-top: 5px; display: block;">Falls kein deutsches Vorschaubild vorhanden, wird das englische für beide Sprachen verwendet</small>
                </div>

                <!-- PDF Upload Bereich -->
                <div id="pdf-upload-section">
                    <div class="form-group">
                        <label>PDF-Datei (Englisch) *</label>
                        <div class="file-input-wrapper">
                            <label for="pdf-file-input" class="file-input-label" id="pdf-file-label">
                                📄 PDF-Datei (EN) auswählen
                            </label>
                            <input type="file" id="pdf-file-input" name="pdf" class="file-input" accept=".pdf">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>PDF-Datei (Deutsch) - Optional</label>
                        <div class="file-input-wrapper">
                            <label for="pdf-file-input-de" class="file-input-label" id="pdf-file-label-de">
                                📄 PDF-Datei (DE) auswählen
                            </label>
                            <input type="file" id="pdf-file-input-de" name="pdf_de" class="file-input" accept=".pdf">
                        </div>
                        <small style="color: #718096; margin-top: 5px; display: block;">Falls keine deutsche Version vorhanden, wird die englische Version für beide Sprachen verwendet</small>
                    </div>
                </div>

                <!-- PDF Link Bereich -->
                <div id="pdf-link-section" style="display: none;">
                    <div class="form-group">
                        <label for="pdf-url-input">PDF-URL (Englisch) *</label>
                        <input type="url" id="pdf-url-input" name="pdf_url" placeholder="https://beispiel.de/dokument-en.pdf">
                        <small style="color: #718096; margin-top: 5px; display: block;">Vollständige URL zur englischen PDF-Datei</small>
                    </div>

                    <div class="form-group">
                        <label for="pdf-url-input-de">PDF-URL (Deutsch) - Optional</label>
                        <input type="url" id="pdf-url-input-de" name="pdf_url_de" placeholder="https://beispiel.de/dokument-de.pdf">
                        <small style="color: #718096; margin-top: 5px; display: block;">Vollständige URL zur deutschen PDF-Datei (optional)</small>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="add-btn" id="submit-btn">✓ PDF hinzufügen</button>
                    <button type="button" class="reset-btn" onclick="resetForm()">✕ Zurücksetzen</button>
                </div>
            </form>
        </div>

        <!-- Liste der vorhandenen PDFs -->
        <div class="pdf-list">
            <h2>Vorhandene PDFs</h2>
            <div id="pdf-list-content">
                <!-- PDFs werden hier geladen -->
            </div>
        </div>

        <footer>
            <p>&copy; 2026 BÜFA Composite Systems GmbH & Co. KG. Alle Rechte vorbehalten.</p>
        </footer>
    </div>

    <script>
        // Kategorien verwalten
        let categories = JSON.parse(localStorage.getItem('categories') || '["All", "Sustainability", "Products", "Systems"]');

        function loadCategories() {
            renderCategoriesList();
            loadCategoryCheckboxes();
        }

        function renderCategoriesList() {
            const list = document.getElementById('categories-list');
            list.innerHTML = '';
            
            categories.forEach((cat, index) => {
                if (cat === 'All') return; // "All" nicht anzeigen, da es immer vorhanden ist
                
                const badge = document.createElement('div');
                badge.className = 'category-badge';
                badge.innerHTML = `
                    <span>${cat}</span>
                    <button onclick="deleteCategory(${index})" title="Löschen">×</button>
                `;
                list.appendChild(badge);
            });
        }

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

        function addCategory() {
            const input = document.getElementById('new-category-input');
            const newCategory = input.value.trim();
            
            if (!newCategory) {
                alert('Bitte geben Sie einen Kategorienamen ein.');
                return;
            }
            
            if (categories.includes(newCategory)) {
                alert('Diese Kategorie existiert bereits.');
                return;
            }
            
            categories.push(newCategory);
            localStorage.setItem('categories', JSON.stringify(categories));
            input.value = '';
            loadCategories();
            showMessage('✓ Kategorie hinzugefügt', 'success');
        }

        function deleteCategory(index) {
            const category = categories[index];
            
            if (!confirm(`Kategorie "${category}" wirklich löschen? PDFs mit dieser Kategorie bleiben erhalten, haben aber eine ungültige Kategorie.`)) {
                return;
            }
            
            categories.splice(index, 1);
            localStorage.setItem('categories', JSON.stringify(categories));
            loadCategories();
            showMessage('✓ Kategorie gelöscht', 'success');
        }

        // Zwischen PDF Upload und PDF Link wechseln
        function togglePdfSource() {
            const source = document.querySelector('input[name="pdf-source"]:checked').value;
            const pdfUploadSection = document.getElementById('pdf-upload-section');
            const pdfLinkSection = document.getElementById('pdf-link-section');
            
            if (source === 'upload') {
                pdfUploadSection.style.display = 'block';
                pdfLinkSection.style.display = 'none';
                document.getElementById('pdf-file-input').required = true;
                document.getElementById('pdf-url-input').required = false;
            } else {
                pdfUploadSection.style.display = 'none';
                pdfLinkSection.style.display = 'block';
                document.getElementById('pdf-file-input').required = false;
                document.getElementById('pdf-url-input').required = true;
            }
        }

        // PDFs laden
        async function loadPDFs() {
            try {
                const response = await fetch('api.php?action=list');
                const data = await response.json();
                
                if (data.success) {
                    renderPDFList(data.pdfs);
                } else {
                    console.error('Fehler beim Laden:', data.message);
                }
            } catch (error) {
                console.error('Fehler:', error);
            }
        }

        // PDF-Liste rendern
        function renderPDFList(pdfs) {
            allPdfs = pdfs; // Speichern für Bearbeitung
            const listContent = document.getElementById('pdf-list-content');
            
            if (!pdfs || pdfs.length === 0) {
                listContent.innerHTML = '<div class="no-pdfs-message">Noch keine PDFs vorhanden</div>';
                return;
            }

            listContent.innerHTML = '';
            pdfs.forEach((pdf, index) => {
                const item = document.createElement('div');
                item.className = 'pdf-item';
                item.dataset.index = index;
                
                const thumbnailHtml = pdf.thumbnail 
                    ? `<img src="${pdf.thumbnail}" alt="${pdf.title}" onerror="this.parentElement.innerHTML='📄'">`
                    : '📄';

                const linkInfo = pdf.link && pdf.link.trim() !== '' 
                    ? `<div class="pdf-item-path" style="margin-top: 5px;">🔗 Link: <a href="${pdf.link}" target="_blank" style="color: #003d7a;">${pdf.link}</a></div>`
                    : '';
                
                // Sprach-Info anzeigen
                const hasGermanVersion = pdf.file_de && pdf.file_de !== pdf.file;
                const langInfo = hasGermanVersion 
                    ? '<span style="background: #48bb78; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; margin-left: 5px;">EN + DE</span>'
                    : '<span style="background: #90cdf4; color: #1a365d; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; margin-left: 5px;">nur EN</span>';

                const pdfCategories = pdf.categories || [pdf.category] || ['products'];
                const categoryBadges = pdfCategories.map(cat =>
                    `<span style="background: #e6f2ff; color: #003d7a; padding: 2px 6px; border-radius: 3px; font-size: 0.7rem; margin-right: 5px;">${cat}</span>`
                ).join('');

                item.innerHTML = `
                    <div class="drag-handle" title="Ziehen zum Sortieren">⋮⋮</div>
                    <div class="pdf-item-thumbnail">
                        ${thumbnailHtml}
                    </div>
                    <div class="pdf-item-info">
                        <div class="pdf-item-title">${pdf.title} ${langInfo}</div>
                        <div class="pdf-item-meta">
                            📅 ${pdf.date} • 📦 ${pdf.size}
                        </div>
                        <div style="margin-top: 5px;">${categoryBadges}</div>
                        ${linkInfo}
                    </div>
                    <button class="edit-btn" onclick="editPDF(${index})">
                        ✏️ Bearbeiten
                    </button>
                    <button class="delete-btn" onclick="deletePDF('${pdf.file}')">
                        🗑️ Löschen
                    </button>
                `;
                
                listContent.appendChild(item);
            });
            initSortable();
        }

        // PDF hochladen
        document.getElementById('upload-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submit-btn');
            const messageDiv = document.getElementById('message');
            const pdfSource = document.querySelector('input[name="pdf-source"]:checked').value;
            
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Wird gespeichert...';
            
            const formData = new FormData(e.target);
            formData.append('pdf_source', pdfSource);

            const selectedCategories = getSelectedCategories();
            if (selectedCategories.length === 0) {
                showMessage('Bitte mindestens eine Kategorie auswählen', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = '✓ PDF hinzufügen';
                return;
            }
            selectedCategories.forEach(cat => {
                formData.append('categories[]', cat);
            });
            
            // Wenn im Bearbeitungsmodus, Index mitschicken
            if (editingIndex !== null) {
                formData.append('edit_index', editingIndex);
            }
            
            try {
                const action = editingIndex !== null ? 'edit' : 'upload';
                const response = await fetch(`api.php?action=${action}`, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const message = editingIndex !== null ? '✓ PDF erfolgreich aktualisiert!' : '✓ PDF erfolgreich hinzugefügt!';
                    showMessage(message, 'success');
                    editingIndex = null; // Erst jetzt Bearbeitungsmodus beenden
                    resetForm();
                    loadPDFs();
                } else {
                    showMessage('✗ Fehler: ' + data.message, 'error');
                }
            } catch (error) {
                showMessage('✗ Fehler beim Speichern: ' + error.message, 'error');
            } finally {
                submitBtn.disabled = false;
                // Nur Button-Text zurücksetzen wenn nicht im Edit-Modus
                if (editingIndex === null) {
                    submitBtn.textContent = '✓ PDF hinzufügen';
                }
            }
        });

        // PDF bearbeiten
        let editingIndex = null;
        let allPdfs = [];

        function editPDF(index) {
            editingIndex = index;
            const pdf = allPdfs[index];
            
            // Formular mit vorhandenen Daten füllen
            document.getElementById('pdf-title-input').value = pdf.title;
            document.getElementById('pdf-description-input').value = pdf.description;

            // Kategorien in Checkboxen setzen
            const pdfCategories = pdf.categories || [pdf.category] || [];
            document.querySelectorAll('.category-checkbox').forEach(cb => {
                cb.checked = pdfCategories.includes(cb.value);
            });
            
            // Vorschaubild nicht mehr erforderlich im Edit-Modus
            document.getElementById('thumbnail-input').required = false;
            
            // Prüfen ob PDF ein Link oder Upload ist
            const isExternalLink = pdf.file && (pdf.file.startsWith('http://') || pdf.file.startsWith('https://'));
            
            if (isExternalLink) {
                // Link-Methode aktivieren
                document.querySelector('input[name="pdf-source"][value="link"]').checked = true;
                togglePdfSource();
                document.getElementById('pdf-url-input').value = pdf.file;
                document.getElementById('pdf-url-input').required = false; // URL nicht mehr required, da schon vorhanden
                
                // Deutsche URL setzen (falls vorhanden)
                if (pdf.file_de && pdf.file_de !== pdf.file) {
                    document.getElementById('pdf-url-input-de').value = pdf.file_de;
                }
            } else {
                // Upload-Methode aktivieren (PDF kann nicht neu hochgeladen werden beim Bearbeiten)
                document.querySelector('input[name="pdf-source"][value="upload"]').checked = true;
                togglePdfSource();
                document.getElementById('pdf-file-input').required = false; // PDF nicht mehr required im Edit
            }
            
            // Button-Text ändern
            document.getElementById('submit-btn').textContent = '✓ Änderungen speichern';
            
            // Hinweis anzeigen
            showMessage('ℹ️ Bearbeitungsmodus: Ändern Sie Titel, Beschreibung oder Vorschaubild. PDF bleibt erhalten (außer bei Links).', 'success');
            
            // Zum Formular scrollen
            document.querySelector('.upload-section').scrollIntoView({ behavior: 'smooth' });
        }

        // PDF löschen
        async function deletePDF(filename) {
            if (!confirm('Möchten Sie dieses PDF wirklich löschen?')) {
                return;
            }
            
            try {
                const response = await fetch('api.php?action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ file: filename })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('✓ PDF erfolgreich gelöscht', 'success');
                    loadPDFs();
                } else {
                    showMessage('✗ Fehler beim Löschen: ' + data.message, 'error');
                }
            } catch (error) {
                showMessage('✗ Fehler: ' + error.message, 'error');
            }
        }

        // Nachricht anzeigen
        function showMessage(text, type) {
            const messageDiv = document.getElementById('message');
            messageDiv.textContent = text;
            messageDiv.className = 'message ' + type + ' show';
            
            setTimeout(() => {
                messageDiv.classList.remove('show');
            }, 5000);
        }

        // Formular zurücksetzen
        function resetForm() {
            document.getElementById('upload-form').reset();
            document.getElementById('thumbnail-label').textContent = '📷 Vorschaubild (EN) auswählen';
            document.getElementById('thumbnail-label').classList.remove('has-file');
            document.getElementById('thumbnail-label-de').textContent = '📷 Vorschaubild (DE) auswählen';
            document.getElementById('thumbnail-label-de').classList.remove('has-file');
            document.getElementById('pdf-file-label').textContent = '📄 PDF-Datei (EN) auswählen';
            document.getElementById('pdf-file-label').classList.remove('has-file');
            document.getElementById('pdf-file-label-de').textContent = '📄 PDF-Datei (DE) auswählen';
            document.getElementById('pdf-file-label-de').classList.remove('has-file');
            // Zurück auf Upload-Methode
            document.querySelector('input[name="pdf-source"][value="upload"]').checked = true;
            togglePdfSource();
            // Required-Felder wiederherstellen
            document.getElementById('thumbnail-input').required = true;
            document.getElementById('pdf-file-input').required = true;
            // Button-Text zurücksetzen
            document.getElementById('submit-btn').textContent = '✓ PDF hinzufügen';
        }

        // Dateinamen-Vorschau und Auto-Fill für Titel
        document.getElementById('thumbnail-input').addEventListener('change', function(e) {
            const label = document.getElementById('thumbnail-label');
            if (this.files[0]) {
                label.textContent = '📷 ' + this.files[0].name;
                label.classList.add('has-file');
            } else {
                label.textContent = '📷 Vorschaubild (EN) auswählen';
                label.classList.remove('has-file');
            }
        });

        document.getElementById('thumbnail-input-de').addEventListener('change', function(e) {
            const label = document.getElementById('thumbnail-label-de');
            if (this.files[0]) {
                label.textContent = '📷 ' + this.files[0].name;
                label.classList.add('has-file');
            } else {
                label.textContent = '📷 Vorschaubild (DE) auswählen';
                label.classList.remove('has-file');
            }
        });

        document.getElementById('pdf-file-input').addEventListener('change', function(e) {
            const label = document.getElementById('pdf-file-label');
            const titleInput = document.getElementById('pdf-title-input');
            
            if (this.files[0]) {
                label.textContent = '📄 ' + this.files[0].name;
                label.classList.add('has-file');
                
                // Titel vorschlagen (nur wenn Titel leer ist)
                if (!titleInput.value || titleInput.value.trim() === '') {
                    // Dateiname ohne Endung nehmen
                    let filename = this.files[0].name;
                    let suggestedTitle = filename.replace(/\.[^/.]+$/, ''); // Endung entfernen
                    
                    // Unterstriche und Bindestriche durch Leerzeichen ersetzen
                    suggestedTitle = suggestedTitle.replace(/[-_]/g, ' ');
                    
                    // Ersten Buchstaben groß schreiben
                    suggestedTitle = suggestedTitle.charAt(0).toUpperCase() + suggestedTitle.slice(1);
                    
                    titleInput.value = suggestedTitle;
                    titleInput.focus();
                    titleInput.select(); // Titel markieren, damit man ihn leicht ändern kann
                }
            } else {
                label.textContent = '📄 PDF-Datei (EN) auswählen';
                label.classList.remove('has-file');
            }
        });

        document.getElementById('pdf-file-input-de').addEventListener('change', function(e) {
            const label = document.getElementById('pdf-file-label-de');
            
            if (this.files[0]) {
                label.textContent = '📄 ' + this.files[0].name;
                label.classList.add('has-file');
            } else {
                label.textContent = '📄 PDF-Datei (DE) auswählen';
                label.classList.remove('has-file');
            }
        });

        // PDFs beim Laden der Seite anzeigen
        loadPDFs();
        loadCategories();

        let sortableInstance = null;

        function initSortable() {
            const list = document.getElementById('pdf-list-content');
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
    </script>
</body>
</html>
