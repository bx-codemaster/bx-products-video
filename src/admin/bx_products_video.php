<?php
/* -------------------------------------------------------------------------------------------
   $Id: admin/bx_products_video.php 13892 2023-04-04 10:48:28Z benax $
    _                           
   | |__   ___ _ __   __ ___  __
   | '_ \ / _ \ '_ \ / _ \ \/ /
   | |_) |  __/ | | | (_| |>  < 
   |_.__/ \___|_| |_|\__,_/_/\_\

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -------------------------------------------------------------------------------------------
   Released under the GNU General Public License
 * Copyright (c) 2009 - 2026 [www.modified-shop.org]
 * -----------------------------------------------------------------------------------------
 * 
 * BESCHREIBUNG:
 * Backend-Handler für sichere Video-Uploads zu Produkten.
 * Unterstützt chunk-basierte Uploads mit umfassenden Sicherheitsprüfungen.
 * 
 * SICHERHEITSFEATURES:
 * - Whitelist für Dateierweiterungen (mp4, webm, ogg, ogv)
 * - MIME-Type-Validierung via finfo_file()
 * - Magic Bytes Überprüfung (ftyp, EBML, OggS)
 * - Maximale Dateigröße: 100 MB
 * - Directory Traversal Schutz
 * - SQL-Injection Schutz
 * - CORS auf eigene Domain beschränkt
 * - Automatisches Löschen ungültiger Dateien
 * 
 * UNTERSTÜTZTE AKTIONEN:
 * - upload: Chunk-weises Hochladen von Videodateien
 * - update: Finale Validierung und Datenbank-Update
 * - delete: Sicheres Löschen von Videos
 * - getVideoName: Abruf des aktuellen Video-Dateinamens
 * - isfile: Existenzprüfung von Dateien
 * 
 * -----------------------------------------------------------------------------------------
 * Released under the GNU General Public License
 * -----------------------------------------------------------------------------------------
   -----------------------------------------------------------------------------------------*/
   
   require('includes/application_top.php');
   
   // CORS auf eigene Domain beschränken (nach Laden der Konstanten)
   $allowed_origin = (defined('HTTPS_SERVER') && HTTPS_SERVER) ? HTTPS_SERVER : ((defined('HTTP_SERVER') && HTTP_SERVER) ? HTTP_SERVER : '');
   if ($allowed_origin) {
       header('Access-Control-Allow-Origin: ' . rtrim($allowed_origin, '/'));
   }

   // Sicherheitskonfiguration
   define('MAX_VIDEO_SIZE', 100 * 1024 * 1024); // 100 MB
   define('ALLOWED_VIDEO_EXTENSIONS', ['mp4', 'webm', 'ogg', 'ogv']);
   define('ALLOWED_VIDEO_MIMES', ['video/mp4', 'video/webm', 'video/ogg']);
   define('VIDEO_UPLOAD_DIR', '../media/products/');

   $action    = isset($_GET["action"]) ? trim($_GET["action"]) : '';
   $filename  = isset($_GET["bxName"]) ? trim($_GET["bxName"]) : (isset($_POST["bxName"]) ? trim($_POST["bxName"]) : '');
   $data      = isset($_POST['data'])  ? substr($_POST['data'], strpos($_POST['data'], ",") + 1) : NULL;
   $pId       = isset($_POST["pId"])   ? (int)$_POST["pId"] : (isset($_GET["pId"]) ? (int)$_GET["pId"] : '');
   
   // Sichere Extension-Extraktion mit pathinfo()
   $pathinfo  = pathinfo($filename);
   $extension = isset($pathinfo['extension']) ? strtolower($pathinfo['extension']) : '';
   
   // Filename-Sanitisierung gegen Directory Traversal
   $filename = basename($filename);
   
   /**
    * Validiert, ob die hochgeladene Datei ein gültiges Video ist
    * @param string $filepath Pfad zur temporären Datei
    * @param string $extension Dateiendung
    * @return bool True wenn gültig, sonst false
    */
   function validateVideoFile($filepath, $extension) {
       // Prüfe Extension gegen Whitelist
       if (!in_array($extension, ALLOWED_VIDEO_EXTENSIONS)) {
           return false;
       }
       
       // Prüfe MIME-Type mit finfo
       if (function_exists('finfo_open')) {
           $finfo    = finfo_open(FILEINFO_MIME_TYPE);
           $mimeType = finfo_file($finfo, $filepath);
           finfo_close($finfo); // deprecated in PHP 8.1, aber für Kompatibilität behalten
           
           if (!in_array($mimeType, ALLOWED_VIDEO_MIMES)) {
               return false;
           }
       }
       
       // Magic Bytes Überprüfung für gängige Videoformate
       $handle = fopen($filepath, 'rb');
       if ($handle === false) {
           return false;
       }
       
       $magicBytes = fread($handle, 12);
       fclose($handle);
       
       // MP4: ftyp (bytes 4-8)
       if ($extension === 'mp4' && substr($magicBytes, 4, 4) === 'ftyp') {
           return true;
       }
       
       // WebM: 0x1A 0x45 0xDF 0xA3 (EBML header)
       if (($extension === 'webm' || $extension === 'ogg' || $extension === 'ogv') && 
           ord($magicBytes[0]) === 0x1A && ord($magicBytes[1]) === 0x45) {
           return true;
       }
       
       // Ogg: OggS
       if (($extension === 'ogg' || $extension === 'ogv') && substr($magicBytes, 0, 4) === 'OggS') {
           return true;
       }
       
       return false;
   }

   switch ($action) {
      case 'upload':
         // Validierung der Extension
         if (!in_array($extension, ALLOWED_VIDEO_EXTENSIONS)) {
             http_response_code(400);
             die(json_encode(['error' => 'Ungültige Dateiendung. Nur MP4, WebM und Ogg sind erlaubt.']));
         }
         
         // Produkt-ID validieren
         if (empty($pId) || $pId < 1) {
             http_response_code(400);
             die(json_encode(['error' => 'Ungültige Produkt-ID']));
         }
         
         // Prüfe ob Upload-Verzeichnis existiert und beschreibbar ist
         if (!is_dir(VIDEO_UPLOAD_DIR)) {
             if (!mkdir(VIDEO_UPLOAD_DIR, 0755, true)) {
                 http_response_code(500);
                 die(json_encode(['error' => 'Upload-Verzeichnis konnte nicht erstellt werden']));
             }
         }
         
         if (!is_writable(VIDEO_UPLOAD_DIR)) {
             http_response_code(500);
             die(json_encode(['error' => 'Keine Schreibrechte im Upload-Verzeichnis']));
         }
         
         $decoded = base64_decode($data);
         if ($decoded === false) {
             http_response_code(400);
             die(json_encode(['error' => 'Ungültige Dateidaten']));
         }
         
         $file = 'video-'.str_pad($pId, 4, "0", STR_PAD_LEFT).'.'.$extension;
         $filepath = VIDEO_UPLOAD_DIR . $file;
         $isFirstChunk = isset($_GET['first']) && $_GET['first'] == '1';
         
         // Beim ersten Chunk: Lösche existierende Datei
         if ($isFirstChunk && file_exists($filepath)) {
             unlink($filepath);
         }
         
         // Schreibe Datei chunk-weise mit exklusivem Lock (verhindert Race Condition bei parallelen Requests)
         $handle = fopen($filepath, 'ab');
         if ($handle === false) {
             http_response_code(500);
             die(json_encode(['error' => 'Datei konnte nicht geöffnet werden']));
         }
         
         if (!flock($handle, LOCK_EX)) {
             fclose($handle);
             http_response_code(500);
             die(json_encode(['error' => 'Datei konnte nicht gesperrt werden']));
         }
         
         // Größenprüfung innerhalb des Locks (atomar mit dem Schreibvorgang)
         $stat = fstat($handle);
         $currentSize = $stat['size'];
         if ($currentSize + strlen($decoded) > MAX_VIDEO_SIZE) {
             flock($handle, LOCK_UN);
             fclose($handle);
             http_response_code(413);
             die(json_encode(['error' => 'Datei überschreitet maximale Größe von ' . MAX_VIDEO_SIZE . ' Bytes']));
         }
         
         $result = fwrite($handle, $decoded);
         $totalSize = fstat($handle)['size'];
         flock($handle, LOCK_UN);
         fclose($handle);
         
         if ($result === false) {
             http_response_code(500);
             die(json_encode(['error' => 'Fehler beim Schreiben der Datei']));
         }
         
         echo json_encode([
             'success' => true,
             'written' => $result,
             'totalSize' => $totalSize,
         ]);
         exit;
         break;
      case 'delete':
         // Validiere Produkt-ID
         if (empty($pId) || $pId < 1) {
             http_response_code(400);
             die(json_encode(['error' => 'Ungültige Produkt-ID']));
         }
         
         // Sanitize filename
         $filename = basename($filename);
         $filepath = VIDEO_UPLOAD_DIR . $filename;
         
         // Lösche nur, wenn Datei existiert und im erlaubten Verzeichnis liegt
         if (file_exists($filepath) && strpos(realpath($filepath), realpath(VIDEO_UPLOAD_DIR)) === 0) {
            unlink($filepath);
         }
         
         xtc_db_query("UPDATE ".TABLE_PRODUCTS." SET products_video = '' WHERE products_id = '".(int)$pId."'");
         echo json_encode(['success' => true]);
         return true;
         break;
         
      case 'update':
         // Validierung der Extension
         if (!in_array($extension, ALLOWED_VIDEO_EXTENSIONS)) {
             http_response_code(400);
             die(json_encode(['error' => 'Ungültige Dateiendung']));
         }
         
         // Validiere Produkt-ID
         if (empty($pId) || $pId < 1) {
             http_response_code(400);
             die(json_encode(['error' => 'Ungültige Produkt-ID']));
         }
         
         $file = 'video-'.str_pad($pId, 4, "0", STR_PAD_LEFT).'.'.$extension;
         $filepath = VIDEO_UPLOAD_DIR . $file;
         
         // Finale Validierung der hochgeladenen Datei
         if (!file_exists($filepath)) {
             http_response_code(404);
             die(json_encode(['error' => 'Datei nicht gefunden']));
         }
         
         if (filesize($filepath) > MAX_VIDEO_SIZE) {
             unlink($filepath); // Entferne zu große Datei
             http_response_code(413);
             die(json_encode(['error' => 'Datei überschreitet maximale Größe']));
         }
         
         // Validiere Videodatei (Magic Bytes + MIME)
         if (!validateVideoFile($filepath, $extension)) {
             unlink($filepath); // Entferne ungültige Datei
             http_response_code(400);
             die(json_encode(['error' => 'Keine gültige Videodatei. Nur echte MP4, WebM oder Ogg Videos sind erlaubt.']));
         }
         
         // Alles OK - Update Datenbank
         xtc_db_query("UPDATE ".TABLE_PRODUCTS." SET products_video = '".xtc_db_input($file)."' WHERE products_id = '".(int)$pId."'");
         echo json_encode(['success' => true, 'filename' => $file]);
         exit;
         break;
      case 'getVideoName':
         if (empty($pId) || $pId < 1) {
             http_response_code(400);
             die(json_encode(['error' => 'Ungültige Produkt-ID']));
         }
         $result_query = xtc_db_query("SELECT products_video FROM ".TABLE_PRODUCTS." WHERE products_id = '".(int)$pId."'");
         $result = xtc_db_fetch_array($result_query);
         echo json_encode(['filename' => $result["products_video"]]);
         return $result["products_video"];
         break;
         
      case 'isfile':
         // Sanitize und validiere Pfad
         $filename = basename($filename);
         $filepath = realpath($filename);
         
         // Prüfe ob Datei existiert und nicht außerhalb des erlaubten Verzeichnisses liegt
         if ($filepath === false) {
             echo "false";
             return false;
         }
         
         $result = is_file($filepath) ? "true" : "false";
         echo $result;
         return $result;
         break;
         
      default:
         http_response_code(400);
         die(json_encode(['error' => 'Ungültige Aktion']));
   }
?>