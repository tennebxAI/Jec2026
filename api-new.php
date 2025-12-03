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
    
    // Multi-Kategorie Support
    $categories = $_POST['categories'] ?? [];
    if (!is_array($categories)) {
        $categories = [$categories];
    }
    $categories = array_filter($categories);
    if (empty($categories)) {
        sendResponse(false, 'Mindestens eine Kategorie muss gewählt werden');
        return;
    }
    
    // Order-Feld
    $order = isset($_POST['order']) ? (int)$_POST['order'] : 999;
    
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
    
    $newPdf = [
        'title' => $title,
        'description' => $description,
        'categories' => $categories,
        'order' => $order,
        'size' => $pdfSize,
        'file' => $pdfPath,
        'file_de' => $pdfPathDe ?? $pdfPath,
        'thumbnail' => $thumbnailPath,
        'thumbnail_de' => $thumbnailPathDe ?? $thumbnailPath,
        'date' => date('d.m.Y')
    ];
    
    // Optional: Link speichern
    if (isset($_POST['link']) && !empty(trim($_POST['link']))) {
        $newPdf['link'] = trim($_POST['link']);
    }
    
    // Daten laden und hinzufügen
    $data = loadPDFData();
    $data['pdfs'][] = $newPdf;
    $data['lastUpdated'] = date('c');
    
    if (savePDFData($data)) {
        sendResponse(true, 'PDF erfolgreich hinzugefügt', $newPdf);
    } else {
        sendResponse(false, 'Fehler beim Speichern der Daten');
    }
}

/**
 * PDF bearbeiten
 */
function handleEdit() {
    global $uploadsDir, $jsonFile, $allowedImageTypes, $maxFileSize;
    
    $editIndex = isset($_POST['edit_index']) ? (int)$_POST['edit_index'] : -1;
    
    if ($editIndex < 0) {
        sendResponse(false, 'Ungültiger Index');
        return;
    }
    
    $data = loadPDFData();
    $pdfs = $data['pdfs'];
    
    if (!isset($pdfs[$editIndex])) {
        sendResponse(false, 'PDF nicht gefunden');
        return;
    }
    
    $existingPdf = $pdfs[$editIndex];
    
    // Titel und Beschreibung aktualisieren
    if (isset($_POST['title'])) {
        $pdfs[$editIndex]['title'] = trim($_POST['title']);
    }
    
    if (isset($_POST['description'])) {
        $pdfs[$editIndex]['description'] = trim($_POST['description']);
    }
    
    // Kategorien aktualisieren (Multi-Support)
    if (isset($_POST['categories'])) {
        $categories = $_POST['categories'];
        if (!is_array($categories)) {
            $categories = [$categories];
        }
        $categories = array_filter($categories);
        if (!empty($categories)) {
            $pdfs[$editIndex]['categories'] = $categories;
        }
    }
    
    // Order aktualisieren
    if (isset($_POST['order'])) {
        $pdfs[$editIndex]['order'] = (int)$_POST['order'];
    }
    
    $title = $pdfs[$editIndex]['title'];
    
    // Neues Vorschaubild (optional)
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $thumbnailFile = $_FILES['thumbnail'];
        
        // Validieren
        if ($thumbnailFile['size'] > $maxFileSize) {
            sendResponse(false, 'Vorschaubild ist zu groß (max. 10 MB)');
            return;
        }
        
        $thumbnailMimeType = mime_content_type($thumbnailFile['tmp_name']);
        if (!in_array($thumbnailMimeType, $allowedImageTypes)) {
            sendResponse(false, 'Ungültiges Bildformat');
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
        
        if ($thumbnailFileDe['size'] > $maxFileSize) {
            sendResponse(false, 'Deutsches Vorschaubild ist zu groß (max. 10 MB)');
            return;
        }
        
        $thumbnailMimeTypeDe = mime_content_type($thumbnailFileDe['tmp_name']);
        if (!in_array($thumbnailMimeTypeDe, $allowedImageTypes)) {
            sendResponse(false, 'Ungültiges Bildformat für deutsches Vorschaubild');
            return;
        }
        
        if (isset($existingPdf['thumbnail_de']) && !filter_var($existingPdf['thumbnail_de'], FILTER_VALIDATE_URL)) {
            if (file_exists($existingPdf['thumbnail_de']) && $existingPdf['thumbnail_de'] !== $existingPdf['thumbnail']) {
                unlink($existingPdf['thumbnail_de']);
            }
        }
        
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
    
    $pdfSource = $_POST['pdf_source'] ?? 'upload';
    
    // PDF-Link aktualisieren (falls im Link-Modus)
    if ($pdfSource === 'link' && isset($_POST['pdf_url']) && !empty(trim($_POST['pdf_url']))) {
        $pdfUrl = trim($_POST['pdf_url']);
        
        if (!filter_var($pdfUrl, FILTER_VALIDATE_URL)) {
            sendResponse(false, 'Ungültige PDF-URL');
            return;
        }
        
        if (isset($existingPdf['file']) && !filter_var($existingPdf['file'], FILTER_VALIDATE_URL)) {
            if (file_exists($existingPdf['file'])) {
                unlink($existingPdf['file']);
            }
        }
        
        $pdfs[$editIndex]['file'] = $pdfUrl;
        $pdfs[$editIndex]['size'] = 'Extern';
        
        if (isset($_POST['pdf_url_de']) && !empty(trim($_POST['pdf_url_de']))) {
            $pdfUrlDe = trim($_POST['pdf_url_de']);
            if (filter_var($pdfUrlDe, FILTER_VALIDATE_URL)) {
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
                if (isset($existingPdf['file_de']) && !filter_var($existingPdf['file_de'], FILTER_VALIDATE_URL)) {
                    if (file_exists($existingPdf['file_de']) && $existingPdf['file_de'] !== $existingPdf['file']) {
                        unlink($existingPdf['file_de']);
                    }
                }
                $pdfs[$editIndex]['file_de'] = $pdfPathDe;
            }
        }
    }
    
    $data['pdfs'] = $pdfs;
    $data['lastUpdated'] = date('c');
    
    if (savePDFData($data)) {
        sendResponse(true, 'PDF erfolgreich aktualisiert', $pdfs[$editIndex]);
    } else {
        sendResponse(false, 'Fehler beim Speichern');
    }
}

/**
 * PDF löschen
 */
function handleDelete() {
    global $jsonFile;
    
    $file = $_POST['file'] ?? '';
    
    if (empty($file)) {
        sendResponse(false, 'Keine Datei angegeben');
        return;
    }
    
    $data = loadPDFData();
    $pdfs = $data['pdfs'];
    $found = false;
    
    foreach ($pdfs as $index => $pdf) {
        if ($pdf['file'] === $file) {
            // Dateien löschen (nur lokale)
            if (!filter_var($pdf['file'], FILTER_VALIDATE_URL) && file_exists($pdf['file'])) {
                unlink($pdf['file']);
            }
            
            if (isset($pdf['file_de']) && !filter_var($pdf['file_de'], FILTER_VALIDATE_URL) && file_exists($pdf['file_de']) && $pdf['file_de'] !== $pdf['file']) {
                unlink($pdf['file_de']);
            }
            
            if (!filter_var($pdf['thumbnail'], FILTER_VALIDATE_URL) && file_exists($pdf['thumbnail'])) {
                unlink($pdf['thumbnail']);
            }
            
            if (isset($pdf['thumbnail_de']) && !filter_var($pdf['thumbnail_de'], FILTER_VALIDATE_URL) && file_exists($pdf['thumbnail_de']) && $pdf['thumbnail_de'] !== $pdf['thumbnail']) {
                unlink($pdf['thumbnail_de']);
            }
            
            array_splice($pdfs, $index, 1);
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        sendResponse(false, 'PDF nicht gefunden');
        return;
    }
    
    $data['pdfs'] = $pdfs;
    $data['lastUpdated'] = date('c');
    
    if (savePDFData($data)) {
        sendResponse(true, 'PDF erfolgreich gelöscht');
    } else {
        sendResponse(false, 'Fehler beim Speichern');
    }
}

/**
 * PDFs auflisten
 */
function handleList() {
    $data = loadPDFData();
    
    // Nach Order sortieren
    usort($data['pdfs'], function($a, $b) {
        $orderA = $a['order'] ?? 999;
        $orderB = $b['order'] ?? 999;
        return $orderA - $orderB;
    });
    
    sendResponse(true, 'PDFs geladen', $data['pdfs']);
}

/**
 * Reihenfolge ändern (Drag & Drop)
 */
function handleReorder() {
    global $jsonFile;
    
    $orderData = json_decode($_POST['order'] ?? '[]', true);
    
    if (empty($orderData)) {
        sendResponse(false, 'Keine Order-Daten erhalten');
        return;
    }
    
    $data = loadPDFData();
    $pdfs = $data['pdfs'];
    
    // Order für jedes PDF aktualisieren
    foreach ($orderData as $item) {
        $index = $item['index'];
        $newOrder = $item['order'];
        
        if (isset($pdfs[$index])) {
            $pdfs[$index]['order'] = $newOrder;
        }
    }
    
    // Nach Order sortieren
    usort($pdfs, function($a, $b) {
        $orderA = $a['order'] ?? 999;
        $orderB = $b['order'] ?? 999;
        return $orderA - $orderB;
    });
    
    $data['pdfs'] = $pdfs;
    $data['lastUpdated'] = date('c');
    
    if (savePDFData($data)) {
        sendResponse(true, 'Reihenfolge gespeichert');
    } else {
        sendResponse(false, 'Fehler beim Speichern');
    }
}

/**
 * Hilfsfunktionen
 */
function loadPDFData() {
    global $jsonFile;
    
    if (!file_exists($jsonFile)) {
        return ['pdfs' => [], 'lastUpdated' => date('c')];
    }
    
    $json = file_get_contents($jsonFile);
    return json_decode($json, true) ?: ['pdfs' => [], 'lastUpdated' => date('c')];
}

function savePDFData($data) {
    global $jsonFile;
    
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($jsonFile, $json) !== false;
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

function sendResponse($success, $message, $data = null) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
}
?>
