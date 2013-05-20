=== MimeTypes Link Icons ===
Contributors: eagerterrier, jrf
Donate link: http://blog.eagerterrier.co.uk/2010/10/holy-cow-ive-gone-and-made-a-mime-type-wordpress-plugin/
Tags: mime-type, icons, file icons, 3g2, 3gp, ai, air, asf, avi, bib, csv, deb, djvu, dmg, doc, docx, dwf, dwg, eps, epub, exe, flac, flv, gif, gz, ico, indd, iso, jpg, jpeg, log, m4a, m4v, midi, mkv, mov, mp3, mp4, mpeg, mpg, msi, odp, ods, odt, oga, ogg, ogv, pdf, png, pps, ppsx, ppt, pptx, psd, pub, qt, ra, ram, rm, rpm, rtf, rv, skp, spx, sql, tar, tex, tgz, tiff, ttf, txt, vob, wav, wmv, xls, xlsx, xml, xpi, zip.
Requires at least: 1.5.1.3
Tested up to: 3.6-beta3
Stable tag: 3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds icons automatically to any uploads and/or file links inserted into your blog posts.

== Description ==

MimeTypes Link Icons is a plugin that looks for links to files and uploads in your blogs posts and adds a nice icon next to it. Optionally add the file's file size next to the link.

**Important note on v3.0**: This version partially breaks backwards compatibility: the plugin now requires PHP5.1+ and WP 3.1+. Please have a look at the [changelog](http://wordpress.org/extend/plugins/mimetypes-link-icons/changelog/) for more information about the changes.

The icons are configurable. You can choose to display a PNG with transparent background or GIF with white matte, display the icon to the left or the right of the link and choose the icon size.

Each icon is available in the following sizes:

* 16x16px
* 24x24px
* 48x48px
* 64x64px
* 128x128px

= Supported File Extensions =
* .3g2
* .3gp
* .ai
* .air
* .asf
* .avi
* .bib
* .csv
* .deb
* .djvu
* .dmg
* .doc
* .docx
* .dwf
* .dwg
* .eps
* .epub
* .exe
* .flac
* .flv
* .gif
* .gz
* .ico
* .indd
* .iso
* .jpg
* .jpeg
* .log
* .m4a
* .m4v
* .midi
* .mkv
* .mov
* .mp3
* .mp4
* .mpeg
* .mpg
* .msi
* .odp
* .ods
* .odt
* .oga
* .ogg
* .ogv
* .pdf
* .png
* .pps
* .ppsx
* .ppt
* .pptx
* .psd
* .pub
* .qt
* .ra
* .ram
* .rm
* .rpm
* .rtf
* .rv
* .skp
* .spx
* .sql
* .tar
* .tex
* .tgz
* .tiff
* .ttf
* .txt
* .vob
* .wav
* .wmv
* .xls
* .xlsx
* .xml
* .xpi
* .zip


= Localization =
* Dutch - [jrf](http://wordpress.org/support/profile/jrf)

Please help make this plugin available in more languages by translating it. The translation files are included in the download. See the [FAQ](http://wordpress.org/extend/plugins/mimetypes-link-icons/faq/) for more info.


= Requirements =

Since version 3.0, the plugin now requires PHP5.1+ and WP 3.1+


= Need more ? =
If you need support for this plugin or even want a completely different plugin programmed: hire [Toby](http://blog.eagerterrier.co.uk/) or [Juliette](http://adviesenzo.nl/)!



== Installation ==

1. Extract the .zip file and upload its contents to the whole `mimetypes_link_icons` folder to your `/wp-content/plugins/` directory. Alternately, you can install directly from the Plugin directory within your WordPress Install.
1. Activate the plugin through the "Plugins" menu in WordPress.
1. By default the PDF icon will be the only one being searched for. It will display the 16x16 png next to your pdf links. You can activate the plugin for other file types via the settings page.


== Frequently Asked Questions ==

= Does `MimeTypes Link Icons` only convert links to uploaded documents ? =

No. It searches your post for any links containing the file extensions you have activated. This will be triggered by any link within the normal content area.


= I don't want MimeTypes Link Icons to convert a particular link.... =

No worries ;-) Just enable the classnames setting on the settings page and add one or more classnames.

The way this works is as follows:
The plugin will look for the classname in your document and will remove the Mimetypes link icons (and file sizes) from all links wrapped within that class.

Examples:

*	If you want to disable the plugin for a particular link, you may add the class "no_mtli" to the link itself and add "no_mtli" to the list of excluded classes.
*	If you want to disable the plugin for a particular post - for instance post 123 -, you could add the "post-123" class to the list of excluded classes.
*	If you want to disable the plugin for all attachment pages, you could add the "type-attachment" class to the list of excluded classes. Just for the image attachment pages ? add the "image-attachment" class.

Please note: Classnames may differ depending on your theme, so look at the html source of the relevant pages to determine which classname(s) to exclude.


= I want to format the file size differently... =
You can ;-)

First of all, you can change the rounding precision for the file size on the settings screen.

Secondly, there's an output filter available for your use which will receive the formatted file size string which will look something along the lines of `(123.4 kB)`.

To use the filter add a snippet like the following to your (child-)theme's functions.php file:
`
function my_function( $formatted_file_size ) {
	// do your thing
	return $formatted_file_size;
}
add_filter( 'mtli_filesize', 'my_function' );
`

Please note: be aware that the file size string will be added to the page via CSS, so the output of your function should be usable in a CSS string!


= I want to have the mimetype icons for a content area which is outside of the loop (a sidebar for instance). Can I? =
Yes you can.

If you generate the output yourself in a template file, change:
`echo $my_content;`
to
`echo mimetypes_to_icons( $my_content );`

or even better, if the content you want to change supplies you with an output filter - add the following to your (child-)theme's functions.php file:
`add_filter( 'name_of_output_filter', 'mimetypes_to_icons' );`
for instance:
`add_filter( 'widget_text', 'mimetypes_to_icons' );`

Please note: the icons generated for that specific content area, will be generated in non-async mode. All other settings will be respected.


= Is there a way to clear the file size cache ? =
Yup! Just uncheck the 'cache file sizes' checkbox, save your settings and then check the checkbox again. The file size cache has now been cleared.


= I want to be able to upload more file types to my WordPress blog! =

This is outside of the scope of this plugin, but you should probably read [this explanation](http://itswordpress.com/featured/add-additional-file-types-to-wordpress-media-library/) (includes code sample) on how to add more file types to the WordPress allowed list in an upgrade-friendly manner.


= I'm a plugin/theme developer and the MimeTypes Link Icons plugin is conflicting with my plugin... =
You can temporarily suspend this plugin by using the pause_mtli() and unpause_mtli() functions.

Add the following code to your plugin where you want to suspend the plugin:
`
if( function_exists( 'pause_mtli' ) ) {
	pause_mtli();
}

// Your code

if( function_exists( 'unpause_mtli' ) ) {
	unpause_mtli();
}
`

Please **do** advise your users about your use of these functions as we're not looking to get complaints from users about this plugin not working ;-)


= How can I translate the plugin? =

The plugin is fully translation ready and translations are much appreciated!
Use the `/languages/mimetypes-link-icons.pot` file which is included in the download to create a new .po file for your language.
To get your translation included in the next release of this plugin:

* Send us pull request or open an issue on [GitHub](https://github.com/eagerterrier/MimeTypes-Link-Icons)
* Open a thread in the [WP forum](http://wordpress.org/support/plugin/mimetypes-link-icons)
* or send the translation to us via email

If you need more information, read this article on [how to translate using a .po file](http://codex.wordpress.org/Translating_WordPress).


== Screenshots ==

1. Screenshot of the administration screen
2. Screenshot of plugin in action.
3. MimeTypes Link Icons adds icons automatically to your inline attachments.
4. Now you can get MimeTypes Link Icons to add the file size of your attachment, too.


== Changelog ==

= 3.0 by jrf =

* [New file extensions] Added additional file extensions which are within the WP allowed file types list: .jpeg
.pps, .ppsx, .m4a, .wav, .avi, .3gp, .3g2
* [New file extensions] Added several additional file extensions based on user requests: [.pub](http://wordpress.org/support/topic/ms-publisher), [.eps](http://wordpress.org/support/topic/eps-support), [.rtf and .exe](http://wordpress.org/support/topic/plugin-mimetypes-link-icons-new-feature-request-self-add-new-mime-types)
* [New file extensions] And some more: .tiff, .ico, .ttf, .qt, .air, .msi, .sql, .flv

* [New feature] [Ability to disable this plugin for more than one classname](http://wordpress.org/support/topic/request-disable-for-multiple-classnames)
* [New feature] Caching of the results of (slow) file size retrievals. This will make page loading a lot faster for pages with lots of file links. Will automatically be turned on, you can turn it off and/or fine tune the cache duration via the settings page. Default cache duration: 1 week.
* [New feature] Set the [rounding precision]((http://wordpress.org/support/topic/thanks-and-a-simple-suggestion)) (number of digits after the decimal point) for file sizes, small files (b) will always round to 0 decimals.
* [New feature] [Output filter for file size string](http://wordpress.org/support/topic/thanks-and-a-simple-suggestion) See the [FAQ](http://wordpress.org/extend/plugins/mimetypes-link-icons/faq/) for more info.
* [New feature] [Ability to have mimetype icons for content outside of the loop](http://wordpress.org/support/topic/using-mimetypes-link-icons-outside-loops) See the [FAQ](http://wordpress.org/extend/plugins/mimetypes-link-icons/faq/) for info on how to use this.

* [Usability] Added 'check all'/'uncheck all' togglers for the file types to the settings page
* [Usability] Compacter options screen - file types now display in two columns
* [Usability] Added help tab to the settings page
* [Usability] Added proper settings link on plugins page, credits now link to the [GitHub repo](https://github.com/eagerterrier/MimeTypes-Link-Icons)
* [Usability] Added clean uninstall routine

* [Compatibility] Added pause_mtli() and unpause_mtli() functions for use by other plugins in case of (page specific) conflicts. See the [FAQ](http://wordpress.org/extend/plugins/mimetypes-link-icons/faq/) for info on how to use this.

* [Bug fix] Images didn't display if wp-content and/or the plugins directory was in a non-standard location.
* [Bug fix] Added epub css styling
* [Bug fix] File size now complies with the localized number format style
* [Bug fix] Curl settings error when in safe_mode - thanks [wolkenkrieger for reporting](http://wordpress.org/support/topic/php-error-in-223)
* [Bug fix] If file size is unknown or file size retrieval failed completely, no file size indication will be shown (it used to show 'unknownb' or 'b')
* [Bug fix] Links are now matched in a case-insensitive manner, so that both document.DOC as well as document.doc will be matched (was only lowercase)
* [Bug fix] If a link already had a class attribute, a second one used to be added. Most browsers don't handle this well. Fixed so that additional class will be added to the existing class attribute.
* [Bug fix] If file size showing would be on and the link had the disabled classname set, the icon would not show, but the file size still did - thanks [Leanne for reporting](http://wordpress.org/support/topic/file-size-showing).
* [Bug fix] File size showing in async mode would never work
* [Bug fix] Classes to avoid where not being applied when not in async mode / would force async mode - thanks [John Percival for reporting](http://wordpress.org/support/topic/async-replacement-causing-jquery-problems)
* [Bug fix] File size not always showing on attachments.php page - thanks [aluizioll for reporting](http://wordpress.org/support/topic/file-size-not-being-displayed-in-attachmentphp)
* [Bug fix] Padding was too easily overruled by CSS from other plugins - thanks [MGmirkin for reporting](http://wordpress.org/support/topic/possible-incompatibility-with-links-shortcode-plugin-not-sure-which-is-causing)
* [Bug fix] File size was retrieved twice for each file... oops.
* [Bug fix] Hopefully fixed bug in retrieval of file size - thanks [brigerard and digitalnordic for reporting](http://wordpress.org/support/topic/problem-with-version-223)
* [Bug fix] File size retrieval should now also work for (most) relative paths

* [Localization] Added .pot file to enable translations of this plugin

* [Code improvements] Complete rewrite in OO style including implementation of the Settings API
* [Code improvements] Implemented lean loading as much as possible
* [Code improvements] Greatly improved input validation for the options page

* [Misc] Added GPL license information


= 2.2.3 =
Adding epub due to user request

= 2.2.2.1 =
Further fixes suggested by @jrf

= 2.2.2 =
Fixes suggested by @jrf

= 2.2.1 =
Admin CSS fix for some users

= 2.2.0 =
Fix for some users who had issues with $_GET vars on style.php

= 2.1.9 =
Changes were made by mistake in v 2.1.8 that weren't completed.

= 2.1.8 =
Fixed a typo found by @pdecaux

= 2.1.7 =
Fixed an IE8 bug found by @quartney

= 2.1.6 =
* Added eleven new mime types: deb, flac, midi, mkv, mp3, oga, ogg, ogv, spx, xml, xpi.
* New option to have icon display on left or right (defaults to left)
* Alphabetized file types
* Changed default image size to "16" (better default because it is closer to text size and does not overwhelm the page with large icons)
* Changed default image type to "png" (better image)
* Reworded text and streamlined display format for Enable/Disable classname override, Show File Size, and Asynchronous Replacement.
* Removed duplicate "png" option from options/default options array

= 2.1.5 =
* Adding DJVU icon
* Typo in the code that in some circumstances leads to plugin 2.1.4 ceasing to function

= 2.1.4 =
* Some bug fixes and plugin conflict fixes. The exclude class name can now be used on parents and the link itself

= 2.1.3 =
* Removing a couple of short php open tags

= 2.1.2 =
* Adding 3 new mime types (openoffice)

= 2.1.1 =
* Adding 14 new mime types (mostly video)

= 2.1.0 =
* File size was being overwritten by JS. Fixed.

= 2.0.9b =
* Bug fix for IE7 users

= 2.0.9 =
* Bug fix for asynchronous users. 

= 2.0.8 =
* Bug fix for asynchronous users. Bug fix for new icon types

= 2.0.7 =
* Adding 14 more icon types - jpg, tar, txt, gif, png, tgz, psd, ai, indd, iso, gz, dmg, bib, & tex

= 2.0.6 =
* 2.0.5 is not showing in the repository. 2.0.6 is a *bump* for 2.0.5

= 2.0.5 =
* Fixing an issue that effect asynchronous users only. http://wordpress.org/support/topic/plugin-mimetypes-link-icons-plugin-conflict-or-bug?replies=12#post-2349689

= 2.0.4 =
* Shifting the CSS to the head to stop CSS code being truncated and displaying on search results etc in the_excerpt

= 2.0.3 =
* Fixing bug that picked up .xlsx files when only .xls files were selected
* Fixing bug that caused problems if the user modified the plugin to run off the extract
* Adding optional field that will skip adding the icon in a parent div of the site owner's choosing

= 2.0.2 =
* Adding smaller 16x16 images at request of user

= 2.0.1 =
* Fixing bug with asynchronous mode

= 2.0.0 =
* Adding option for displaying filesize. Uses :after pseudo element with CSS. Therefore, will not work on IE6.

= 1.1.0 =
* Enhancements

= 1.0.9 =
* Minor Bug fix. Preparing for 2.0

= 1.0.8 =
* Adding pptx format

= 1.0.7 =
* Adding ability for users to use anchor tags in the PDF URL - ie http://example.com/wp-content/uploads/myfile.pdf#page9

= 1.0.6 =
* Turns out some themes don't use get_header OR get_footer. Had to put the hook into the_content instead.

= 1.0.5 =
* Adding optional asynchronous method for users with conflicting plugins (for example the infocus theme's fancy_box)

= 1.0.4 =
* Bug fix on the preg_replace replace syntax

= 1.0.3 =
* Added new file type icons at request of benlikespizza - ppt, skp, dwg, dwf, jpg

= 1.0.2 =
* Fixed Bug that caused icons not to appear when some conflicting plugins were installed

= 1.0.1 =
* Typo in CSS caused some images not to show


== Upgrade Notice ==

= 3.0 by jrf =
Several new features, new file extensions, complete plugin rewrite to comply with the current WP standards. Upgrade highly recommended. Please refer to the [changelog](http://wordpress.org/extend/plugins/mimetypes-link-icons/changelog/) for detailed information on all the changes.

= 2.2.3 =
Adding epub extension due to user request

= 2.2.2.1 =
Further fixes suggested by @jrf

= 2.2.2 =
Fixes suggested by @jrf

= 2.2.1 =
Admin CSS fix for some users, having direct link to settings from plugins page. Cosmetic fix. Non-essential upgrade.

= 2.2.0 =
Fix for some users who had issues with $_GET vars on style.php

= 2.1.9 =
Changes were made by mistake in v 2.1.8 that weren't completed.  Recommended to all users. Apologies for anyone who upgraded today and got 2.1.8...

= 2.1.8 =
Fixed a typo found by @pdecaux. Recommended to users of the filesize option.

= 2.1.7 =
* Fixed an IE8 bug found by @quartney. Recommended to all users.

= 2.1.6 =
* Adding 11 new mime types
* New option to have icon display on left or right (defaults to left)
* Other front end changes

= 2.1.5 =
* Adding DJVU icon
* Typo in the code that in some circumstances leads to plugin 2.1.4 ceasing to function

= 2.1.4 =
* Some bug fixes and plugin conflict fixes. The exclude class name can now be used on parents and the link itself

= 2.1.3 =
* Removing a couple of short php open tags

= 2.1.2 =
* Adding 3 new mime types (openoffice)

= 2.1.1 =
* Adding 14 new mime types (mostly video)

= 2.1.0 =
* File size was being overwritten by JS. Fixed. Please be aware that file size cannot work with asynchronous loading type.

= 2.0.9b =
* Bug fix for IE7 users

= 2.0.9 =
* Important bug fix for asynchronous users that fixes DOM scripting clash between plugins. Recommended for all users.

= 2.0.8 =
* Bug fix for asynchronous users. Bug fix for new icon types. Recommended for all users.

= 2.0.7 =
* Adding 14 more icon types - jpg, tar, txt, gif, png, tgz, psd, ai, indd, iso, gz, dmg, bib, & tex

= 2.0.6 =
* 2.0.5 is not showing in the repository. 2.0.6 is a *bump* for 2.0.5

= 2.0.5 =
* Fixing an issue that effect asynchronous users only. http://wordpress.org/support/topic/plugin-mimetypes-link-icons-plugin-conflict-or-bug?replies=12#post-2349689

= 2.0.4 =
* Shifting the CSS to the head to stop CSS code being truncated and displaying on search results etc in the_excerpt

= 2.0.3 =
* Fixing bug that picked up .xlsx files when only .xls files were selected
* Fixing bug that caused problems if the user modified the plugin to run off the extract
* Adding optional field that will skip adding the icon in a parent div of the site owner's choosing

= 2.0.2 =
* Adding smaller 16x16 images at request of user

= 2.0.1 =
* Fixing bug with asynchronous mode

= 2.0.0 =
* Adding option for displaying filesize. Uses :after pseudo element with CSS. Therefore, will not work on IE6.

= 1.1.0 =
* Enhancements

= 1.0.9 =
* Minor Bug fix. Preparing for 2.0

= 1.0.8 =
* Adding pptx format

= 1.0.7 =
* Adding ability for users to use anchor tags in the PDF URL - ie http://example.com/wp-content/uploads/myfile.pdf#page9

= 1.0.6 =
* Bug fix

= 1.0.5 =
* Adding optional asynchronous method for users with conflicting plugins (for example the infocus theme's fancy_box)

= 1.0.4 =
* Bug fix

= 1.0.3 =
* Added new file type icons - ppt, skp, dwg, dwf, jpg

= 1.0.2 =
* Bug fix

= 1.0.1 =
Typo in CSS caused some images not to show. Recommended for all users