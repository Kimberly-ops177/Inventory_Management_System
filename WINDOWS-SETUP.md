# Windows Setup Guide

## Enable Required PHP Extensions

### 1. Find your php.ini file

```bash
php --ini
```

This will show you the location of your `php.ini` file (e.g., `C:\php\php.ini`)

### 2. Enable mbstring extension

1. Open `php.ini` in a text editor (as Administrator)
2. Find the line: `;extension=mbstring`
3. Remove the semicolon to uncomment it: `extension=mbstring`
4. Save the file

### 3. Enable other required extensions

Make sure these lines are also uncommented (no semicolon at the start):

```ini
extension=pdo_mysql
extension=mbstring
extension=json
```

Optional but recommended:
```ini
extension=curl
extension=openssl
extension=zip
```

### 4. Restart your terminal

Close and reopen your terminal/command prompt for changes to take effect.

### 5. Verify

Run the requirements checker again:

```bash
php check-requirements.php
```

---

## Common Issues

### Issue: "php.ini" not found
- You may need to copy `php.ini-development` to `php.ini`
- Location is usually in your PHP installation directory

### Issue: Extension file not found
- Make sure `extension_dir` in php.ini points to the correct folder
- Usually `extension_dir = "ext"` or full path like `C:\php\ext`
- Verify the DLL files exist in that directory (e.g., `php_mbstring.dll`)

### Issue: Changes not taking effect
- Make sure you edited the correct php.ini (check with `php --ini`)
- Restart your terminal/command prompt
- If using a web server, restart it too

---

## Quick Development Server

Once all requirements are met:

```bash
# Run migrations
php database/migrate.php

# Seed sample data (optional)
php database/seed.php

# Start dev server
php -S localhost:8000 -t public
```

Then open: http://localhost:8000

Login: admin@example.com / password

---

## Deployment Script for Windows

Use `deploy-windows.bat` for Windows deployment:

```bash
deploy-windows.bat
```

**Note:** The `deploy.sh` script is for Linux production servers only, not for local Windows development.
