## Welcome to Blox

Blox is a premium Wordpress plugin built for users of the [Genesis Framework](http://www.studiopress.com). Blox is a product of Outermost Design, LLC. We have no direct affiliation with the makers of Genesis.

Blox allows you to easily add content to your Genesis theme through Genesis hooks. You are free to download and use this plugin, but understand that automatic updates and support require a paid Blox license. Blox licenses can be purchased on [www.bloxwp.com](https://www.bloxwp.com/pricing/?utm_source=blox&utm_medium=plugin&utm_content=github-readme-links&utm_campaign=Blox_Plugin_Links)

### Support

There are a number of ways you can get help with Blox:

* Visit the plugin [Documentation](https://www.bloxwp.com/documentation/?utm_source=blox&utm_medium=plugin&utm_content=github-readme-links&utm_campaign=Blox_Plugin_Links).
* Direct support requires a paid Blox license. Submit a support ticket through [Your Account](https://www.bloxwp.com/your-account/?utm_source=blox&utm_medium=plugin&utm_content=github-readme-links&utm_campaign=Blox_Plugin_Links) on www.bloxwp.com.

### Find a bug or have a feature request?

We would love to hear from you! If you find a bug or have a feature request, please send in a support ticket through [Your Account](https://www.bloxwp.com/your-account/?utm_source=blox&utm_medium=plugin&utm_content=github-readme-links&utm_campaign=Blox_Addon_Links). Or better yet, add it to the [issue tracker](https://github.com/ndiego/blox/issues) here on Github. Either way, please be clear and detailed in you communication. If submitting a bug, be sure to provide instructions on how to replicate the issue you are having. Also provide the theme name and PHP version if possible.

### Changelog

##### Version 1.4.8 – 2019-06-26
* Fixed conflict with Genesis 3.x

##### Version 1.4.7 – 2018-02-17
* Fixed version bump error causing the plugin update message to keep displaying even after updating to the latest version

##### Version 1.4.6 – 2018-02-16
* Added blox_frontend_content and blox_frontend_style filters so users can filter the frontend output of Blox

##### Version 1.4.5 – 2017-09-06
* Fixed Woocommerce conflict caused by quick edit save function

##### Version 1.4.4 – 2017-08-06
* Fixed Jetpack conflict caused by jetpack_store_migration_data

##### Version 1.4.3 – 2017-05-05
* Fixed bug that broke urls placed in the Custom Block CSS settings field

##### Version 1.4.2 – 2017-04-10
* Fixed bug inadvertently introduced by the quick edit bug fix in v1.4.1

##### Version 1.4.1 – 2017-03-30
* Fixed post type archive location setting bug where the global block would not display if multiple post type archives were selected
* Fixed quick edit bug where the Local Blocks admin column would disappear on quick edit save
* Fixed slideshow copy bug that caused issues with the caption include parentheses

##### Version 1.4.0 – 2016-01-04
* Changed Apply Settings button in the slideshow modal for a better user experience
* Updated position settings and tests to handle additional position formats
* Updated EDD Updater to v1.6.8
* Updated .pot file for language translation
* Fixed slideshow caption bug which eliminated content wrapping

##### Version 1.3.0 – 2016-12-01
* Added fullscreen mode for raw content
* Added syntax highlighting in fullscreen mode for raw content
* Added new builder UI for the builtin slideshow
* Added new settings to the builtin slideshow (image size, background images)
* Added the ability to set default settings for the builtin slideshow
* Added quick edit functionality to global blocks for selected position and visibility settings
* Added bulk edit functionality to global blocks for enable/disable block
* Changed from Flexslider to Slick Slider for the js powering the builtin slideshow
* Updated styling on caption and code textareas to allow for horizontal scrolling
* Updated custom image and slideshow default images with standardized dashicons picture icon
* Fixed bug where global and local custom classes were not being applied

##### Version 1.2.1 – 2016-10-23
* Fixed EDD updater conflict with other plugins using EDD by adding a prefix to the updater

##### Version 1.2.0 – 2016-10-20
* Added System Info page to Blox Tools
* Changed suppress_filters to false in global blocks for WPML compatibility
* Tweaked spelling on Add-ons
* Updated EDD Updater to v1.6.5
* Fixed anonymous function that were causing issues in PHP 5.2
* Fixed Visibility by Role bug
* Fixed links in hook error messages
* Fixed error message in position admin columns when the current hook is disabled

##### Version 1.1.1
* Fixed location archive taxonomy bug

##### Version 1.1.0
* Added Hook Control
* Added ability to duplicate Global blocks
* Added Categories List shortcode
* Added Tags List shortcode
* Changed text and links throughout
* Fixed editor wpautop bug

##### Version 1.0.0
* Initial Release
