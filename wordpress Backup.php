<?php
/**
 * Plugin Name: Simple WP Backup
 * Description: Erstellt ein vollständiges Backup der WordPress-Website und ermöglicht das Wiederherstellen eines Backups.
 * Version: 1.0
 * Author: Manuel Koppo
 */

if (!defined('ABSPATH')) {
    exit; // Sicherheitscheck
}

// Backup-Verzeichnis definieren
define('SIMPLE_WP_BACKUP_DIR', plugin_dir_path(__FILE__) . 'backups/');
if (!file_exists(SIMPLE_WP_BACKUP_DIR)) {
    mkdir(SIMPLE_WP_BACKUP_DIR, 0755, true);
}

// Funktion zur Erstellung des Backups
function simple_wp_backup_create() {
    global $wpdb;
    
    $timestamp = date('Y-m-d_H-i-s');
    $backup_dir = SIMPLE_WP_BACKUP_DIR;
    $backup_file = $backup_dir . "backup_$timestamp.zip";
    
    // WordPress-Pfad
    $wp_path = ABSPATH;
    
    // Datenbank-Dump erstellen
    $db_file = $backup_dir . "db_$timestamp.sql";
    $db_name = DB_NAME;
    $db_user = DB_USER;
    $db_pass = DB_PASSWORD;
    $db_host = DB_HOST;
    
    $mysqldump_path = 'C:\xampp\mysql\bin\mysqldump.exe'; // Pfad anpassen!
    $dump_command = "\"$mysqldump_path\" --host=$db_host --user=$db_user --password=$db_pass $db_name > \"$db_file\"";
    system($dump_command);
    
    // ZIP-Datei mit allen Dateien + DB-Dump erstellen
    $zip = new ZipArchive();
    if ($zip->open($backup_file, ZipArchive::CREATE) === TRUE) {
        // Dateien in ZIP packen
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($wp_path), RecursiveIteratorIterator::LEAVES_ONLY);
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($wp_path));
                $zip->addFile($filePath, $relativePath);
            }
        }
        // DB-Dump hinzufügen
        $zip->addFile($db_file, "db_$timestamp.sql");
        $zip->close();
    }
    unlink($db_file);
    
    // Admin benachrichtigen
    $admin_email = get_option('admin_email');
    $subject = "WordPress Backup Completed";
    $message = "A new backup has been successfully created: $backup_file";
    wp_mail($admin_email, $subject, $message);
    
    return $backup_file;
}

// Funktion zur Wiederherstellung des Backups
function simple_wp_backup_restore($backup_file) {
    global $wpdb;
    
    $backup_dir = SIMPLE_WP_BACKUP_DIR;
    $zip = new ZipArchive();
    
    if ($zip->open($backup_dir . $backup_file) === TRUE) {
        $zip->extractTo(ABSPATH);
        $zip->close();
        
        // Datenbank wiederherstellen
        $db_file = $backup_dir . "db_" . substr($backup_file, 7, 19) . ".sql";
        if (file_exists($db_file)) {
            $db_name = DB_NAME;
            $db_user = DB_USER;
            $db_pass = DB_PASSWORD;
            $db_host = DB_HOST;
            
            $mysql_path = 'C:\xampp\mysql\bin\mysql.exe'; // Pfad anpassen!
            $restore_command = "\"$mysql_path\" --host=$db_host --user=$db_user --password=$db_pass $db_name < \"$db_file\"";
            system($restore_command);
        }
        echo '<div class="updated"><p>Backup successfully restored!</p></div>';
    } else {
        echo '<div class="error"><p>Failed to restore backup.</p></div>';
    }
}

// Admin-Menüeintrag hinzufügen
function simple_wp_backup_admin_menu() {
    add_menu_page(
        'Simple WP Backup',
        'WP Backup',
        'manage_options',
        'simple-wp-backup',
        'simple_wp_backup_admin_page',
        'dashicons-backup',
        80
    );
}
add_action('admin_menu', 'simple_wp_backup_admin_menu');

// Admin-Seite für Backup und Wiederherstellung
function simple_wp_backup_admin_page() {
    if (isset($_POST['simple_wp_backup'])) {
        simple_wp_backup_create();
        echo '<div class="updated"><p>Backup successfully created!</p></div>';
    }
    if (isset($_POST['simple_wp_restore']) && !empty($_POST['backup_file'])) {
        simple_wp_backup_restore($_POST['backup_file']);
    }
    ?>
    <div class="wrap">
        <h1>Simple WP Backup</h1>
        <form method="post">
            <input type="hidden" name="simple_wp_backup" value="1">
            <button type="submit" class="button button-primary">Backup machen</button>
        </form>
        <h2>Backup wiederherstellen</h2>
        <form method="post">
            <select name="backup_file">
                <?php
                $files = scandir(SIMPLE_WP_BACKUP_DIR);
                foreach ($files as $file) {
                    if (strpos($file, 'backup_') === 0 && strpos($file, '.zip') !== false) {
                        echo "<option value='$file'>$file</option>";
                    }
                }
                ?>
            </select>
            <input type="hidden" name="simple_wp_restore" value="1">
            <button type="submit" class="button button-secondary">Backup einspielen</button>
        </form>
    </div>
    <?php
}
