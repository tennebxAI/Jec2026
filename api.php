<?php
header('Content-Type: application/json');

// Konfiguration
$uploadsDir = 'pdfs/';
$jsonFile = 'pdfs.json';
$allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxFileSize = 10 * 1024 * 1024; // 10 MB

// Stelle sicher, dass der Upload-Ordner existiert
if (!file_exists($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

// Aktion bestimmen
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'upload':
        handleUpload();
        break;
    case 'edit':
        handleEdit();
        break;
    case 'delete':
        handleDelete();
        break;
    case 'list':
        handleList();
        break;
    case 'reorder':
        handleReorder();
        break;
    default:
        sendResponse(false, 'Ungültige Aktion');
}

/**
 * PDF hochladen (mit Link oder Upload)
 */
function handleUpload() {
    global $uploadsDir, $jsonFile, $allowedImageTypes, $maxFileSize;
    
    // Validierung
    if (!isset($_POST['title']) || empty(trim($_POST['title']))) {
        sendResponse(false, 'Titel ist erforderlich');
        return;
    }
    
    if (!isset($_FILES['thumbnail']) || $_FILES['thumbnail']['error'] !== UPLOAD_ERR_OK) {
        sendResponse(false, 'Vorschaubild ist erforderlich');
        return;
    }
    
    $title = trim($_POST['title']);
    $description = trim($_POST['description'] ?? 'Keine Beschreibung verfügbar');
    $category = trim($_POST['category'] ?? 'products');
    $pdfSource = $_POST['pdf_source'] ?? 'upload';
    $thumbnailFile = $_FILES['thumbnail'];
    
    // Vorschaubild validieren
    if ($thumbnailFile['size'] > $maxFileSize) {
        sendResponse(false, 'Vorschaubild ist zu groß (max. 10 MB)');
        return;
    }
    
    $thumbnailMimeType = mime_content_type($thumbnailFile['tmp_name']);
    if (!in_array($thumbnailMimeType, $allowedImageTypes)) {
        sendResponse(false, 'Ungültiges Bildformat (nur JPG, PNG, GIF, WEBP erlaubt)');
        return;
    }
    
    // Sichere Dateinamen generieren
    $timestamp = time();
    $safeTitle = preg_replace('/[^a-z0-9-_]/i', '-', $title);
    $safeTitle = preg_replace('/-+/', '-', $safeTitle);
    $safeTitle = trim($safeTitle, '-');
    
    $thumbnailExtension = pathinfo($thumbnailFile['name'], PATHINFO_EXTENSION);
    $thumbnailFilename = $safeTitle . '-thumb-' . $timestamp . '.' . $thumbnailExtension;
    $thumbnailPath = $uploadsDir . $thumbnailFilename;
    
    // Vorschaubild hochladen
    if (!move_uploaded_file($thumbnailFile['tmp_name'], $thumbnailPath)) {
        sendResponse(false, 'Fehler beim Speichern des Vorschaubilds');
        return;
    }
    
    // Deutsches Vorschaubild (optional)
    $thumbnailPathDe = null;
    if (isset($_FILES['thumbnail_de']) && $_FILES['thumbnail_de']['error'] === UPLOAD_ERR_OK) {
        $thumbnailFileDe = $_FILES['thumbnail_de'];
        
        if ($thumbnailFileDe['size'] <= $maxFileSize) {
            $thumbnailMimeTypeDe = mime_content_type($thumbnailFileDe['tmp_name']);
            if (in_array($thumbnailMimeTypeDe, $allowedImageTypes)) {
                $thumbnailExtensionDe = pathinfo($thumbnailFileDe['name'], PATHINFO_EXTENSION);
                $thumbnailFilenameDe = $safeTitle . '-thumb-de-' . $timestamp . '.' . $thumbnailExtensionDe;
                $thumbnailPathDe = $uploadsDir . $thumbnailFilenameDe;
                
                if (move_uploaded_file($thumbnailFileDe['tmp_name'], $thumbnailPathDe)) {
                    // Deutsches Vorschaubild erfolgreich hochgeladen
                } else {
                    $thumbnailPathDe = null;
                }
            }
        }
    }
    
    // PDF entweder hochladen oder Link verwenden
    if ($pdfSource === 'link') {
        // PDF-Link verwenden
        if (!isset($_POST['pdf_url']) || empty(trim($_POST['pdf_url']))) {
            unlink($thumbnailPath);
            sendResponse(false, 'PDF-URL (Englisch) ist erforderlich');
            return;
        }
        
        $pdfUrl = trim($_POST['pdf_url']);
        
        if (!filter_var($pdfUrl, FILTER_VALIDATE_URL)) {
            unlink($thumbnailPath);
            sendResponse(false, 'Ungültige PDF-URL');
            return;
        }
        
        $pdfPath = $pdfUrl;
        $pdfSize = 'Extern';
        
        // Deutsche PDF-URL (optional)
        $pdfPathDe = null;
        if (isset($_POST['pdf_url_de']) && !empty(trim($_POST['pdf_url_de']))) {
            $pdfUrlDe = trim($_POST['pdf_url_de']);
            if (filter_var($pdfUrlDe, FILTER_VALIDATE_URL)) {
                $pdfPathDe = $pdfUrlDe;
            }
        }
        
    } else {
        // PDF hochladen
        if (!isset($_FILES['pdf']) || $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
            unlink($thumbnailPath);
            sendResponse(false, 'PDF-Datei (Englisch) ist erforderlich');
            return;
        }
        
        $pdfFile = $_FILES['pdf'];
        
        // PDF validieren
        if ($pdfFile['size'] > $maxFileSize) {
            unlink($thumbnailPath);
            sendResponse(false, 'PDF-Datei ist zu groß (max. 10 MB)');
            return;
        }
        
        if (mime_content_type($pdfFile['tmp_name']) !== 'application/pdf') {
            unlink($thumbnailPath);
            sendResponse(false, 'Die hochgeladene Datei ist keine PDF');
            return;
        }
        
        // Sicheren Dateinamen generieren
        $pdfExtension = pathinfo($pdfFile['name'], PATHINFO_EXTENSION);
        $pdfFilename = $safeTitle . '-en-' . $timestamp . '.' . $pdfExtension;
        $pdfPath = $uploadsDir . $pdfFilename;
        
        // PDF hochladen
        if (!move_uploaded_file($pdfFile['tmp_name'], $pdfPath)) {
            unlink($thumbnailPath);
            sendResponse(false, 'Fehler beim Speichern der PDF');
            return;
        }
        
        // Dateigröße berechnen
        $pdfSize = formatFileSize(filesize($pdfPath));
        
        // Deutsche PDF (optional)
        $pdfPathDe = null;
        if (isset($_FILES['pdf_de']) && $_FILES['pdf_de']['error'] === UPLOAD_ERR_OK) {
            $pdfFileDe = $_FILES['pdf_de'];
            
            if ($pdfFileDe['size'] <= $maxFileSize && mime_content_type($pdfFileDe['tmp_name']) === 'application/pdf') {
                $pdfFilenameDe = $safeTitle . '-de-' . $timestamp . '.' . $pdfExtension;
                $pdfPathDe = $uploadsDir . $pdfFilenameDe;
                
                if (move_uploaded_file($pdfFileDe['tmp_name'], $pdfPathDe)) {
                    // Deutsche PDF erfolgreich hochgeladen
                } else {
                    $pdfPathDe = null;
                }
            }
        }
    }
    
    // PDF zur JSON-Datenbank hinzufügen
    $pdfs = loadPDFs();
    
    $newPdf = [
        'title' => $title,
        'description' => $description,
        'category' => $category,
        'size' => $pdfSize,
        'file' => $pdfPath,
        'file_de' => $pdfPathDe ?? $pdfPath, // Fallback zu EN wenn keine DE Version
        'thumbnail' => $thumbnailPath,
        'thumbnail_de' => $thumbnailPathDe ?? $thumbnailPath, // Fallback zu EN wenn keine DE Version
        'date' => date('d.m.Y')
    ];
    
    $pdfs[] = $newPdf;
    
    if (!savePDFs($pdfs)) {
        // Hochgeladene Dateien löschen, wenn JSON-Speicherung fehlschlägt
        if ($pdfSource === 'upload' && file_exists($pdfPath)) {
            unlink($pdfPath);
        }
        unlink($thumbnailPath);
        sendResponse(false, 'Fehler beim Speichern in der Datenbank');
        return;
    }
    
    sendResponse(true, 'PDF erfolgreich hinzugefügt', ['pdf' => $newPdf]);
}

/**
 * PDF bearbeiten
 */
function handleEdit() {
    global $uploadsDir, $jsonFile, $allowedImageTypes, $maxFileSize;
    
    // Validierung
    if (!isset($_POST['title']) || empty(trim($_POST['title']))) {
        sendResponse(false, 'Titel ist erforderlich');
        return;
    }
    
    if (!isset($_POST['edit_index'])) {
        sendResponse(false, 'Kein PDF zum Bearbeiten ausgewählt');
        return;
    }
    
    $title = trim($_POST['title']);
    $description = trim($_POST['description'] ?? 'Keine Beschreibung verfügbar');
    $category = trim($_POST['category'] ?? 'products');
    $editIndex = (int)$_POST['edit_index'];
    $pdfSource = $_POST['pdf_source'] ?? 'upload';
    
    // PDFs laden
    $pdfs = loadPDFs();
    
    if (!isset($pdfs[$editIndex])) {
        sendResponse(false, 'PDF nicht gefunden');
        return;
    }
    
    $existingPdf = $pdfs[$editIndex];
    
    // Titel und Beschreibung aktualisieren
    $pdfs[$editIndex]['title'] = $title;
    $pdfs[$editIndex]['description'] = $description;
    $pdfs[$editIndex]['category'] = $category;
    
    // Vorschaubild aktualisieren (falls hochgeladen)
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $thumbnailFile = $_FILES['thumbnail'];
        
        // Validieren
        if ($thumbnailFile['size'] > $maxFileSize) {
            sendResponse(false, 'Vorschaubild ist zu groß (max. 10 MB)');
            return;
        }
        
        $thumbnailMimeType = mime_content_type($thumbnailFile['tmp_name']);
        if (!in_array($thumbnailMimeType, $allowedImageTypes)) {
            sendResponse(false, 'Ungültiges Bildformat (nur JPG, PNG, GIF, WEBP erlaubt)');
            return;
        }
        
        // Altes Vorschaubild löschen (nur wenn lokal)
        if (isset($existingPdf['thumbnail']) && !filter_var($existingPdf['thumbnail'], FILTER_VALIDATE_URL)) {
            if (file_exists($existingPdf['thumbnail'])) {
                unlink($existingPdf['thumbnail']);
            }
        }
        
        // Neues Vorschaubild hochladen
        $timestamp = time();
        $safeTitle = preg_replace('/[^a-z0-9-_]/i', '-', $title);
        $safeTitle = preg_replace('/-+/', '-', $safeTitle);
        $safeTitle = trim($safeTitle, '-');
        
        $thumbnailExtension = pathinfo($thumbnailFile['name'], PATHINFO_EXTENSION);
        $thumbnailFilename = $safeTitle . '-thumb-' . $timestamp . '.' . $thumbnailExtension;
        $thumbnailPath = $uploadsDir . $thumbnailFilename;
        
        if (!move_uploaded_file($thumbnailFile['tmp_name'], $thumbnailPath)) {
            sendResponse(false, 'Fehler beim Speichern des Vorschaubilds');
            return;
        }
        
        $pdfs[$editIndex]['thumbnail'] = $thumbnailPath;
    }
    
    // Deutsches Vorschaubild aktualisieren (falls hochgeladen)
    if (isset($_FILES['thumbnail_de']) && $_FILES['thumbnail_de']['error'] === UPLOAD_ERR_OK) {
        $thumbnailFileDe = $_FILES['thumbnail_de'];
        
        // Validieren
        if ($thumbnailFileDe['size'] > $maxFileSize) {
            sendResponse(false, 'Deutsches Vorschaubild ist zu groß (max. 10 MB)');
            return;
        }
        
        $thumbnailMimeTypeDe = mime_content_type($thumbnailFileDe['tmp_name']);
        if (!in_array($thumbnailMimeTypeDe, $allowedImageTypes)) {
            sendResponse(false, 'Ungültiges Bildformat für deutsches Vorschaubild');
            return;
        }
        
        // Altes deutsches Vorschaubild löschen (nur wenn lokal und unterschiedlich vom englischen)
        if (isset($existingPdf['thumbnail_de']) && !filter_var($existingPdf['thumbnail_de'], FILTER_VALIDATE_URL)) {
            if (file_exists($existingPdf['thumbnail_de']) && $existingPdf['thumbnail_de'] !== $existingPdf['thumbnail']) {
                unlink($existingPdf['thumbnail_de']);
            }
        }
        
        // Neues deutsches Vorschaubild hochladen
        $timestamp = time();
        $safeTitle = preg_replace('/[^a-z0-9-_]/i', '-', $title);
        $safeTitle = preg_replace('/-+/', '-', $safeTitle);
        $safeTitle = trim($safeTitle, '-');
        
        $thumbnailExtensionDe = pathinfo($thumbnailFileDe['name'], PATHINFO_EXTENSION);
        $thumbnailFilenameDe = $safeTitle . '-thumb-de-' . $timestamp . '.' . $thumbnailExtensionDe;
        $thumbnailPathDe = $uploadsDir . $thumbnailFilenameDe;
        
        if (!move_uploaded_file($thumbnailFileDe['tmp_name'], $thumbnailPathDe)) {
            sendResponse(false, 'Fehler beim Speichern des deutschen Vorschaubilds');
            return;
        }
        
        $pdfs[$editIndex]['thumbnail_de'] = $thumbnailPathDe;
    }
    
    // PDF-Link aktualisieren (falls im Link-Modus)
    if ($pdfSource === 'link' && isset($_POST['pdf_url']) && !empty(trim($_POST['pdf_url']))) {
        $pdfUrl = trim($_POST['pdf_url']);
        
        if (!filter_var($pdfUrl, FILTER_VALIDATE_URL)) {
            sendResponse(false, 'Ungültige PDF-URL');
            return;
        }
        
        // Alte PDF-Datei löschen (nur wenn lokal gespeichert)
        if (isset($existingPdf['file']) && !filter_var($existingPdf['file'], FILTER_VALIDATE_URL)) {
            if (file_exists($existingPdf['file'])) {
                unlink($existingPdf['file']);
            }
        }
        
        $pdfs[$editIndex]['file'] = $pdfUrl;
        $pdfs[$editIndex]['size'] = 'Extern';
        
        // Deutsche PDF-URL aktualisieren (optional)
        if (isset($_POST['pdf_url_de']) && !empty(trim($_POST['pdf_url_de']))) {
            $pdfUrlDe = trim($_POST['pdf_url_de']);
            if (filter_var($pdfUrlDe, FILTER_VALIDATE_URL)) {
                // Alte deutsche PDF löschen
                if (isset($existingPdf['file_de']) && !filter_var($existingPdf['file_de'], FILTER_VALIDATE_URL)) {
                    if (file_exists($existingPdf['file_de'])) {
                        unlink($existingPdf['file_de']);
                    }
                }
                $pdfs[$editIndex]['file_de'] = $pdfUrlDe;
            }
        }
    }
    
    // Neue deutsche PDF hochladen (falls vorhanden)
    if (isset($_FILES['pdf_de']) && $_FILES['pdf_de']['error'] === UPLOAD_ERR_OK) {
        $pdfFileDe = $_FILES['pdf_de'];
        
        if ($pdfFileDe['size'] <= $maxFileSize && mime_content_type($pdfFileDe['tmp_name']) === 'application/pdf') {
            $timestamp = time();
            $safeTitle = preg_replace('/[^a-z0-9-_]/i', '-', $title);
            $safeTitle = preg_replace('/-+/', '-', $safeTitle);
            $safeTitle = trim($safeTitle, '-');
            
            $pdfExtension = pathinfo($pdfFileDe['name'], PATHINFO_EXTENSION);
            $pdfFilenameDe = $safeTitle . '-de-' . $timestamp . '.' . $pdfExtension;
            $pdfPathDe = $uploadsDir . $pdfFilenameDe;
            
            if (move_uploaded_file($pdfFileDe['tmp_name'], $pdfPathDe)) {
                // Alte deutsche PDF löschen
                if (isset($existingPdf['file_de']) && !filter_var($existingPdf['file_de'], FILTER_VALIDATE_URL)) {
                    if (file_exists($existingPdf['file_de']) && $existingPdf['file_de'] !== $existingPdf['file']) {
                        unlink($existingPdf['file_de']);
                    }
                }
                $pdfs[$editIndex]['file_de'] = $pdfPathDe;
            }
        }
    }
    
    // Speichern
    if (!savePDFs($pdfs)) {
        sendResponse(false, 'Fehler beim Speichern der Änderungen');
        return;
    }
    
    sendResponse(true, 'PDF erfolgreich aktualisiert', ['pdf' => $pdfs[$editIndex]]);
}

/**
 * PDF löschen
 */
function handleDelete() {
    global $uploadsDir, $jsonFile;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['file'])) {
        sendResponse(false, 'Dateiname fehlt');
        return;
    }
    
    $filename = $input['file'];
    
    // PDFs laden
    $pdfs = loadPDFs();
    
    // PDF finden
    $index = -1;
    $pdfToDelete = null;
    
    foreach ($pdfs as $i => $pdf) {
        if ($pdf['file'] === $filename) {
            $index = $i;
            $pdfToDelete = $pdf;
            break;
        }
    }
    
    if ($index === -1) {
        sendResponse(false, 'PDF nicht gefunden');
        return;
    }
    
    // Dateien löschen (nur wenn lokal gespeichert)
    if (isset($pdfToDelete['file']) && !filter_var($pdfToDelete['file'], FILTER_VALIDATE_URL)) {
        if (file_exists($pdfToDelete['file'])) {
            unlink($pdfToDelete['file']);
        }
    }
    
    if (isset($pdfToDelete['thumbnail']) && !filter_var($pdfToDelete['thumbnail'], FILTER_VALIDATE_URL)) {
        if (file_exists($pdfToDelete['thumbnail'])) {
            unlink($pdfToDelete['thumbnail']);
        }
    }
    
    // Aus JSON-Datenbank entfernen
    array_splice($pdfs, $index, 1);
    
    if (!savePDFs($pdfs)) {
        sendResponse(false, 'Fehler beim Aktualisieren der Datenbank');
        return;
    }
    
    sendResponse(true, 'PDF erfolgreich gelöscht');
}

/**
 * PDFs auflisten
 */
function handleList() {
    $pdfs = loadPDFs();
    sendResponse(true, 'PDFs geladen', ['pdfs' => $pdfs]);
}

/**
 * PDFs aus JSON laden
 */
function loadPDFs() {
    global $jsonFile;
    
    if (!file_exists($jsonFile)) {
        return [];
    }
    
    $content = file_get_contents($jsonFile);
    $data = json_decode($content, true);
    
    return $data['pdfs'] ?? [];
}

/**
 * PDFs in JSON speichern
 */
function savePDFs($pdfs) {
    global $jsonFile;
    
    $data = [
        'pdfs' => $pdfs,
        'lastUpdated' => date('c')
    ];
    
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
    return file_put_contents($jsonFile, $json) !== false;
}

/**
 * Dateigröße formatieren
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' Bytes';
    }
}

/**
 * JSON-Antwort senden
 */
function sendResponse($success, $message, $data = []) {
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit;
}
?>
