# Simple WP Backup

## Description
Simple WP Backup is a WordPress plugin that enables a full backup of the website (files + database). Additionally, it allows restoring a created backup.

## Features
- Creates a ZIP backup of all WordPress files and the database
- Stores backups in the plugin folder (`wp-content/plugins/simple-wp-backup/backups/`)
- Sends an email to the administrator upon successful backup
- Provides an easy restoration option in the admin panel

## Installation
1. Upload the `simple-wp-backup` folder to the `wp-content/plugins/` directory.
2. Activate the plugin via the WordPress admin panel under `Plugins`.
3. Ensure that the backup directory `wp-content/plugins/simple-wp-backup/backups/` is writable.

## Usage
1. A new menu item **WP Backup** will appear in the WordPress admin panel.
2. Click **Create Backup** to generate a backup.
3. Under **Restore Backup**, select a previous backup and restore it.

## Important Notes
- If the backup is not created, check and adjust the paths to `mysqldump.exe` and `mysql.exe` in the plugin file (`simple-wp-backup.php`).
- The administrator receives an email notification upon successful backup (uses the WordPress-registered admin email).
- Backups are stored in the plugin folder. Regularly download and save them externally for additional security.

## License
This plugin is released under the MIT License. Free to use and modify.

