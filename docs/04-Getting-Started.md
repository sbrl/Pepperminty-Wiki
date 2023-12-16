# Getting Started

## System Requirements
- PHP-enabled web-server (must be at least PHP 7+; only versions of PHP that are [officially supported](https://www.php.net/supported-versions.php) are supported by Pepperminty Wiki)
- PHP session support (for logging in, see [here](https://php.net/manual/en/session.installation.php) for more information)
- The following PHP extensions:
	- `mbstring` (for utf8 string handling - currently **required**)
	- `imagick` (for preview generation)
	- `fileinfo` (for proper mime type checking of uploaded files)
	- `zip` (for compressing exports)
	- `intl` (for Unicode text normalization when searching and in the id index, and when sending emails when utf-8 mode is disabled)
	- `sqlite3` (for search index storage; uses [PDO](https://www.php.net/manual/en/ref.pdo-sqlite.php))
- Write access to Pepperminty Wiki's own folder (only for editing and first run)
- Recommended: Block access to `peppermint.json`, where it stores it's settings (including passwords!)


## Setup Instructions
1. Once you've ensured your web server meets the requirements, obtain a copy of Pepperminty Wiki (see _[Getting a copy](05-Getting-A-Copy.html)_).
2. Put the `index.php` file on your web server.
3. Navigate to Pepperminty Wiki in your web browser. If you uploaded the `index.php` to `wiki/` on your web server `bobsrockets.com`, then you should navigate to `bobsrockets.com/wiki/`.
4. See the [Configuring](06-Configuration.html) section for information on how to customise your installation, including the default login credentials.
5. Ensure you configure your web server to block access to `peppermint.json`, as this contains all your account details (including your hashed password!)

### Blocking access to pepppermint.json

#### Nginx
For those running Nginx, this configuration snippet should block access to `peppermint.json`:

```nginx
location /peppermint.json {
	deny all;
}
```

#### Apache
If you are running Apache, then the following configuration snippet should block access to `peppermint.json` (credit: [@viradpt](https://github.com/sbrl/Pepperminty-Wiki/issues/224#issuecomment-912683114)):

```htaccess
<Files "peppermint.json">
    Order Allow,Deny
    Deny from all
</Files>
```

#### Lighttpd
If you're running lighttpd, then you need to load the `mod_access` module:

```lighttpd
server.modules += ( "mod_access" )
```

If you already have a `server.modules` directive, simply add `mod_access` to the list if you haven't already. Then, just block access like so:

```lighttpd
$HTTP["url"] =~ "^/peppermint.json" {
     url.access-deny = ("")
}
```

#### Caddy
The Caddy web server makes it easy to block files. Add this to your `server` block if you have one, or if not just to the end of your file:

```caddy
@blocked {
    path *peppermint.json
}
respond @blocked 403
```

#### Microsoft IIS
For those running IIS, the following will grant the appropriate read and write permissions to the IIS_IUSRS group, and prevent the peppermint.json file from being retrieved.

Open an elevated (administrator) Command Prompt and run the following.
Change the "installdir" variable to the directory where you've placed the Pepperminty index.php file.
This assumes your IIS website is named "Default Web Site" and that you want to create a "pepperminty" application under it. If yours is different, change the variables appropriately.

```
SETLOCAL
SET installdir=c:\inetpub\wwwroot\pepperminty\
SET iissitename="Default Web Site"
SET iisappfull="Default Web Site/pepperminty"
SET iisapppath="/pepperminty"

cd /d %WINDIR%\system32\inetsrv\
appcmd add app /site.name:%iissitename% /path:%iisapppath% /physicalPath:%installdir%
appcmd set config %iisappfull% -section:system.webServer/security/requestFiltering /+"hiddenSegments.[segment='peppermint.json']"
cd /d %installdir%
icacls . /grant IIS_IUSRS:(OI)(CI)RXWM
ENDLOCAL
```

#### Other web servers
If you aren't running any of these web servers and have a configuration snippet to share for your web server, please [open an issue](https://github.com/sbrl/Pepperminty-Wiki/issues/new) to get in touch - and then we can add your configuration snippet to improve this documentation for everyone.


## Verifying Your Download
Advanced and privacy-conscious users may want to verify the authenticity of their downloaded release. Since [v0.21.1-hotfix1](https://github.com/sbrl/Pepperminty-Wiki/releases/tag/v0.21.1-hotfix1), [Pepperminty Wiki releases on GitHub](https://github.com/sbrl/Pepperminty-Wiki/releases) are now signed. This is done in the following fashion:

 - The release `index.php` is hashed with SHA256 and saved to `HASHES.SHA256`
 - `HASHES.SHA256` is then signed via GPG, generating `HASHES.SHA256.asc` as the signature file

Thus, verifying the authenticity of a downloaded release is a 2-step process. It is assumed in this section that the user is familiar with a Linux terminal, and has one opened in which they have `cd`ed to the directory containing the files downloaded from a release.

3 files should be present:

Filename			| Purpose
--------------------|----------------------
`index.php`			| Pepperminty Wiki itself
`HASHES.SHA256`		| The SHA256 hash(es)
`HASHES.SHA256.asc`	| The GPG signatue

First, the SHA256 hashes must be verified:

```bash
sha256sum -c HASHES.SHA256
```

This should output something like `OK` if verification successful, or an error message if not.

Next, the GPG signature can be verified. To do this, we need to download the public key with which the release was signed. At the current time, this is my personal GPG key with the id `C2F7843F9ADF9FEE264ACB9CC1C6C0BB001E1725`, but check the release notes too. Download it like so:

```bash
gpg --keyserver hkps://keyserver.ubuntu.com --recv-keys C2F7843F9ADF9FEE264ACB9CC1C6C0BB001E1725
```

Then, verify the GPG signature:

```bash
gpg --verify HASHES.SHA256.asc
```

It might complain that the key is untrusted, but it should also tell you which key signed the release, and whether the signature itself is valid or not - which is what you're looking for. If you'd like to mark the key you downloaded as trusted, you can do so like this:

```bash
echo -e "4\nsave\n" | gpg --batch --expert --command-fd 0 --edit-key "C2F7843F9ADF9FEE264ACB9CC1C6C0BB001E1725" trust >/dev/null 2>&1;
```

Then, simply re-run the GPG verification command above to see the difference.
