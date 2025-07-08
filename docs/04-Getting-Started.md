# Getting Started

## System Requirements
- PHP-enabled web-server (must be at least PHP 7+; only versions of PHP that are [officially supported](https://www.php.net/supported-versions.php) are supported by Pepperminty Wiki)
- PHP session support (for logging in, see [here](https://php.net/manual/en/session.installation.php) for more information)
	- Alpine Linux users: install e.g. `php84-session`
- The following PHP extensions:
	- `mbstring` (for utf8 string handling - currently **required**)
	- `imagick` (for preview generation)
	- `fileinfo` (for proper mime type checking of uploaded files)
	- `zip` (for compressing exports)
	- `intl` (for Unicode text normalization when searching and in the id index, and when sending emails when utf-8 mode is disabled)
	- `pdo_sqlite3` (for search index storage; uses [PDO](https://www.php.net/manual/en/ref.pdo-sqlite.php)) \*\*
- Write access to Pepperminty Wiki's own folder (only for editing and first run)
- Recommended: Block access to `peppermint.json`, where it stores it's settings (including passwords!)

**\*\* Note for Alpine Linux users:** That means `pdo_sqlite`! i.e. from [`php84-pdo_sqlite`](http://dl-cdn.alpinelinux.org/alpine/edge/testing/x86_64/php84-pdo_sqlite-8.4.0_beta3-r0.apk) from Alpine's testing repository, ref `php -m` inside an alpine Docker container and [this post](https://devcoops.com/install-php-mbstring-on-alpine-linux/)


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
The Caddy web server makes it easy to block files. Add this to your `server` block if you have one, or if not just to the end of your Caddyfile:

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

## Docker
The recommended way of running Pepperminty Wiki is with a plain PHP-enabled web server. However, a Dockerfile is available in Pepperminty Wiki's git repository.

**Warning:** Installing Pepperminty Wiki with Docker is somewhat complicated, involving multiple steps. If this seems scary to you, it is recomended you use a plain PHP-enabled web server as described above.

**If you do not want to use Docker, you can skip the rest of this page and move onto the next one.**

This said, there are 4 steps to getting Pepperminty Wiki running in a Docker container:

1. Building the Docker container
2. Creating the file structure
3. Starting the Docker container
4. Completing the first run wizard

This guide will assume a plain Docker container running on a generic Linux box that has Docker installed. Of course, you can adapt this to container orchestration setups (e.g. Kubernetes, Docker Swarm, etc ~~[Nomad](https://www.infoq.com/news/2023/08/hashicorp-adopts-bsl/)~~ nope, the BSL license is bad).

If you do adapt it, please do open a pull request to update this guide with your instructions!


### Building the Docker container
Start by `cd`ing to a nice directory and cloning the Pepperminty Wiki git repository:

```bash
git clone https://github.com/sbrl/Pepperminty-Wiki.git
cd Pepperminty-Wiki;
```

Now, check out the git tag you want to build, if any. Stay as-is to build the latest development version.

Next, we simply use the `build.sh` script like so:

```bash
./build.sh docker
```

....this will build Pepperminty Wiki in the background, so it requires a working PHP install (no FPM required, only uses the CLI) with the `zip` PHP extension available.

Once done, it will create a new Docker image with the tag `pepperminty-wiki`.

You are now free to delete the cloned git repository, but it is advised to update regularly. If you are planning to script the update of the Docker container, the format of the giit tags can be relied upon.

- The latest git tag will be either the latest release or the latest beta/pre release
- Release tags look like this (replacing the version/hotfix numbers of course):
	- `v0.20`
	- `v0.20.2-hotfix2`
	- `v0.20.3`
- Beta release tags look like this:
	- `v0.19-beta2`

### Creating the file structure
Traditionally, Pepperminty Wiki keeps everything in a single directory, but in a Docker container is most likely desirable to keep the wiki data in a separate directory. Pepperminty Wiki supports this mode of operation through the [`data_storage_dir` configuration directive](https://starbeamrainbowlabs.com/labs/peppermint/peppermint-config-info.php#config_data_storage_dir) in `peppermint.json` - which it is also recommended to change if [`require_login_view`](https://starbeamrainbowlabs.com/labs/peppermint/peppermint-config-info.php#config_require_login_view) is set to true to avoid leaks.

To this end, there are 2 things we need to do here.

First, create an empty directory for Pepperminty Wiki to store your wiki data in, and `chown` it to `10801:10801` (UID:GID), since Pepperminty Wiki will bbe running under this user/group id combination inside the Docker container to improve security.

```bash
mkdir path/to/directory
sudo chown 10801:10801 path/to/directory
```

Then, create `peppermint.json` and prefill it with the following contents:

```bash
{ "data_storage_dir": "/srv/data", "firstrun_complete": false }
```

....the `data_storage_dir` here is INSIDE the container, not outside! The `"firstrun_complete": false` is required to show the firstrun installer.

Then, set the permissions on `peppermint.json`:

```bash
chmod 0600 path/to/peppermint.json
sudo chown 10801:10801 path/to/peppermint.json
```

**Note:** This method only works for Pepperminty Wiki v0.25 or above. Specifically, you MUST have commit [`c4f6ef2`](https://github.com/sbrl/Pepperminty-Wiki/commit/c4f6ef2c58afb291f3fb6e355eb0eeff00843cc5) (and [`9800c25`](https://github.com/sbrl/Pepperminty-Wiki/commit/9800c257de64774cc8e09e2a75f4de6a1dcb6ac2) immediately before it) for the firstrun wizard to correctly appear without editing `peppermint.json` a second time.

### Starting the Docker container
Now that you have the Docker image built and the file structure setup correctly, you can start the Docker container itself. This is the Docker command required as if you were starting it in the terminal:

```bash
docker run -it --rm -v /absolute/path/to/peppermint.json:/srv/app/peppermint.json --hostname peppermint -v /absolute/path/to/data:/srv/data pepperminty-wiki
```

...replacing:

- `/absolute/path/to/peppermint.json` with the path to `peppermint.json`, and
- `/absolute/path/to/data` with the path to the wiki data directory you created earlier.

...make sure that both of these paths are **absolute**, because otherwise you will experience issues because the Docker daemon that actually starts the Docker container does not know about your current working directory.

It is also strongly advisable to avoid spaces in your directory paths.

**Tip:** A simple solution to ensure the Docker container automatically restarts on reboot is to use [the `--restart always` argument](https://docs.docker.com/engine/containers/start-containers-automatically/#use-a-process-manager) to `docker run`.

#### docker-compose
If you use Docker Compose, the following Docker Compose file may prove useful:

```yaml
services:
  pepperminty-wiki:
    image: pepperminty-wiki
    container_name: peppermint
    volumes:
      - /absolute/path/to/peppermint.json:/srv/app/peppermint.json
      - /absolute/path/to/data:/srv/data
```

...replacing the source paths in `volumes` as previously described.

Note that this Docker Compose file is untested. If this doesn't work for you, please open a pull request.

### Completing the first run wizrd
Now that your Docker container is started, you should be able to navigate to it in your web browser to complete the first run wizard.

Setups vary, but if you don't know the IP address of your shiny new Docker container, try this method:

> First get the container ID:
> 
> ```bash
> docker ps
> ```
> 
> (First column is for container ID)
> 
> Use the container ID to run:
> 
> ```bash
> docker inspect <container ID>
> ```
> 
> At the bottom, under NetworkSettings, you can find \[the] IPAddress \[field]
> 
> Or just do for UNIX based [operating systems]:
> 
> ```bash
> docker inspect <container id> | grep "IPAddress"
> ```

_(Taken from [This StackOverflow Answer](https://stackoverflow.com/a/46310428/1460422))_

The Pepperminty Wiki Docker container always listens on port 80 for unencrypted HTTP requests, so enter something like the following IP address into your web browser:

```
http://172.17.0.3/
```

**Note:** The IP address will vary depending on your network layout. It is important you put it behind another reverse proxy that handles HTTPS encryption if you want it to be world-readable.

In your web browser, you should be presented with the first run wizard, which should tell you that all the PHP extensions needed are already installed.

Fill this out as normal:

- Pepperminty Wiki will add a `secret` field to Pepperminty Wiki - follow the on-screen instructions
- Put `/srv/data` into the "Data Storage Directory" field.
- Fill in all other fields as your needs require.

Finally, press the "Create Wiki!" button to finish the first run wizard, and now use your wiki as normal.


### FAQ

#### I get a "peppermint.json.compromised" exists on disk error
The error message may look like this:

> Error: peppermint.json.compromised exists on disk, so it's likely you need to block access to 'peppermint.json' from the internet. If you've done this already, please delete peppermint.json.compromised and reload this page.
> 
> If you've done this check manually, please set the disable_peppermint_access_check setting to false.
> 
> This check was done as part of the first run wizard.

....try restarting the container. The container is designed to block access to `peppermint.json` automatically.

If you do encounter this bug, please do get in touch so we can track it down  and fix it.


#### I get an "Error: Failed to decode the settings file! Does it contain a syntax error?" message
Check the permissions on your `peppermint.json`. It should be owned by UID 10801 and GID 10801. It should also be a file containing some JSON, and not a directory.