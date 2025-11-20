# Musumba Steel Web Platform

Full-stack bilingual (English & Kiswahili) website for Musumba Steel with MySQL-backed content management and administration console.

## Tech Stack
- PHP 8+
- MySQL 5.7+ (`musumbasteeltz` database)
- HTML5/CSS3/JavaScript (vanilla)

## Getting Started
1. Import schema and seed data:
   ```sql
   mysql -u root -p < schema/database.sql
   ```
2. Ensure the document root points to this directory (e.g. `http://localhost/musumba_steel`).
3. Copy your logo to `assets/img/logo.png`.

### Default Credentials
- Username: `marcellin@gmail.com`
- Password: `Burundi@2025`

## Translations
Static labels live in `includes/translations.php`. Dynamic content carries `*_en` and `*_sw` columns and is edited through the admin pages.

## Structure
- `index.php`: public site with tab navigation.
- `includes/`: helpers, translations, shared layout.
- `admin/`: authentication, dashboard, CRUD interfaces.
- `assets/`: styles, scripts, images.

## Notes
- Language preference stored in session; toggle via header links.
- Responsive navigation replicates the Burundi Backbone reference layout and uses brand colors from the provided logo.

