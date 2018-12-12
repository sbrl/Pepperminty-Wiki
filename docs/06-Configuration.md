# Configuring
Pepperminty Wiki stores its settings in a JSON file called `peppermint.json`, which is stored in the same directory that you put `index.php` into. If it doesn't exist, it's created automatically for you when you load the page for the first time.

You can edit it directly, but most properties are configurable in a graphical interface. It can be accessed through the _Edit Master Settings_ option in the more menu, or the `configure` action (e.g. `https://wiki.example.com/?action=configure`) if it doesn't appear for you.

However, some settings do still require edits to `peppermint.json` directly. Examples here include:

 - Editing the various navigation bars
 - Configuring moderators
 - Setting allowed upload file types (and mime type overrides)
 - Manual HTTP/2.0 Server Push configuration

The [configuration guide](https://starbeamrainbowlabs.com/labs/peppermint/peppermint-config-info.php) contains a list of all the configuration directives that Pepperminty Wiki (and all the modules included in the repository) understand. It is generated automatically from `peppermint.guiconfig.json` in the repository.

 - [Configuration Reference](https://starbeamrainbowlabs.com/labs/peppermint/peppermint-config-info.php)
 - [`peppermint.guiconfig.json`](https://github.com/sbrl/Pepperminty-Wiki/blob/master/peppermint.guiconfig.json)

## User Accounts
User account details are currently stored as an object in `peppermint.json`, under the `users` special setting. Each user has their own object, in which lies their user data.

While users can change their own passwords and email addresses, you'll inevitably want to add your own users. You can do this through the brand-new user management page if you're logged in as a moderator or better. Click on the `Edit user table` option on the credits page, or navigate to the `user-table` action (example url: `https://example.com/path/to/index.php?action=user-table`).

If you're having trouble with the user table, you can also add users manually. Here's how to do it that:

1. Open `peppermint.json` in your favourite text editor.
2. Create a new property on the `users` object, whose value is an object and key is the new user's username. Use the existing users for reference.
3. Hash the new user's password. This can be done in the terminal or with the `hash` action - but make sure you don't leave any traces of your passwords lying around for others to find!
    a. To use the `hash` action, navigate to `https://example.com/path/to/index.php?action=hash&string=my_temporary_password`. Don't forget to change your password afterwards, or clear both your browser history & server logs! You could even use [the demo instance](https://starbeamrainbowlabs.com/labs/peppermint/build/?action=hash&string=password) I have running on my server, but I don't have a filter on my server logs :-)
    b. To do it through the terminal, the following one-liner should do the trick: `echo -n "some_password" | php -r 'echo(password_hash(base64_encode(hash("sha384", trim(fgets(STDIN)))), PASSWORD_DEFAULT) . "\n");'`
4. Save `peppermint.json` back to disk.


### Default Credentials
The default user account details are as follows:

 - `admin` with password `password`
 - `user` with password `cheese`

**Please remember to change your account password! Only you are responsible for the security of your account.**
