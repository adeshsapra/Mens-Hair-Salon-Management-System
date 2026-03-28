# Database Backup Guide

This project supports SQL dump backups for the `classycut` database.

## 1) phpMyAdmin (quick method)
1. Open phpMyAdmin.
2. Select database `classycut`.
3. Click `Export`.
4. Choose `Quick` and format `SQL`.
5. Download the `.sql` file.

## 2) Command line (professional method)
Backup:

```bash
mysqldump -u root -p classycut > backup.sql
```

Restore:

```bash
mysql -u root -p classycut < backup.sql
```

## 3) One-click backup in admin panel
1. Login as admin.
2. Open `Admin > Database Backup`.
3. Click `Create Backup (.sql)`.

The script creates the dump and downloads it, and also stores it in:

`database/backups/`
