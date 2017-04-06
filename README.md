# WordPress

WordPress setup to work nicely with modern deployment methods


## Configuring

Before doing anything with WordPress you'll need to build the `.env` file. If
you don't have one present already then run `composer env` in the root and a new
one will be generate with randomised keys.

You'll most likely need to edit your MySQL connection details updating the
following lines:

    DB_HOST=localhost
    DB_NAME=wordpress
    DB_USER=root
    DB_PASSWORD=root

You also have WordPress options to consider:

    WP_ENV=local
    WP_DEBUG=false
    WP_THEME=app

`WP_ENV` is the environment you're WordPress currently sits. Most likely you
use `local` for when you're developing your site locally and then `production`
for when the site is put live.

`WP_DEBUG` is set to `false` by default but when developing your site you are
best to set this to `true` to see any error outputs.

`WP_THEME` is the name of the theme folder you want to use. `app` is the default
option but if you want a more specific name then update that value. If you do
you'll need to update  `gulpfile` where it says `var theme = 'app';` to reflect
the new theme folder name.

Worth noting that the file **`.env` SHOULD NEVER BE COMMITTED** as it contains
very sensitive information about your setup. Thus, when you go live with your
project or reproduced on another environment, you'll need to setup the file
again.


## Adding Plugins

You can manage all your plugins that are from the [WordPress Plugin Directory](http://wordpress.org/plugins/)
within composer via [WordPress Packagist](https://wpackagist.org/).

To simply add a plugin go to the root of this project in the terminal and run
the command `composer require wpackagist-plugin/PLUGIN_SLUG` replacing
`PLUGIN_SLUG` with the


## Adding a Custom Plugin

If you created a plugin that is only required for that project and isn't a
public repo or isn't in a repo at all, then you will need to edit `/.gitignore`
file. By default it ignores all folders in the plugin directory as you don't
want to add these to your own repo, but you can prefix your own plugin with
`app-` and git will know not to ignore it.

If you don't to prefix it for what ever reason then you'll just need to add it
to the `.gitingore` file just below the line `!public/plugins/app-*` in the
following way

    !/public/plugins/PLUGIN_SLUG

Where it says "`PLUGIN_SLUG`" you should replace that with your plugin's folder
name.


## Author(s)

- Neil Sweeney <neil@wolfiezero.com>


## License

[MIT License](LICENSE)
