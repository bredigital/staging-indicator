# WordPress Staging Indicator
![Staging Indicator menu][1]

Displays information in the header of WordPress admin when logged in, to help identify which system you are using. The 
menu item will also drop down a site switcher to enable fast switching between stages.

To change the displayed values, edit the `config.env` file. This plugin adds support for the plugin editor to edit 
`.env`, so you can edit it directly from WordPress.

## Requirements
* WordPress 3.8 or higher.
* PHP 5.6 or higher.
* [Composer][2] (if compliling from source).

## Setup
The latest downloads can be [found in our releases][3]. Simply download the latest .zip package, and upload it to your 
WordPress site using the 'add new' function in WordPress admin.

If you are building from source, you will need to grab the dependencies using `composer install`, and rename 
`config.env.example` to `config.env` to begin.

[1]: https://i.imgur.com/hEOZ56I.png
[2]: https://getcomposer.org/
[3]: https://github.com/bredigital/staging-indicator/releases/latest