# Getting Started

## System Requirements
- PHP-enabled web-server (must be PHP 7+)
- PHP session support (for logging in, see [here](https://php.net/manual/en/session.installation.php) for more information)
- The following PHP extensions:
    - `mbstring` (for utf8 string handling - currently **required**)
    - `imagick` (for preview generation)
    - `fileinfo` (for proper mime type checking of uploaded files)
    - `zip` (for compressing exports)
    - `intl` (for Unicode text normalization when searching and in the id index)
    - `sqlite` (for search index storage; uses [PDO](https://www.php.net/manual/en/ref.pdo-sqlite.php))
- Write access to Pepperminty Wiki's own folder (only for editing)
- Recommended: Block access to `peppermint.json`, where it stores it's settings

## Setup Instructions
1. Once you've ensured your web server meets the requirements, obtain a copy of Pepperminty Wiki (see _[Getting a copy](05-Getting-A-Copy.html)_).
2. Put the `index.php` file on your web server.
3. Navigate to Pepperminty Wiki in your web browser. If you uploaded the `index.php` to `wiki/` on your web server `bobsrockets.com`, then you should navigate to `bobsrockets.com/wiki/`.
4. See the [Configuring](06-Configuration.html) section for information on how to customize your installation, including the default login credentials.
5. Ensure you configure your web server to block access to `peppermint.json`, as this contains all your account details (including your hashed password!)
