=== MimeTypes Link Icons ===
Contributors: eagerterrier, jrf
Donate link: http://blog.eagerterrier.co.uk/2010/10/holy-cow-ive-gone-and-made-a-mime-type-wordpress-plugin/
Tags: mime-type, icons, ai, asf, bib, csv, deb, doc, docx, djvu, dmg, dwg, dwf, epub, flac, gif, gz, indd, iso, jpg, log, m4v, midi, mkv, mov, mp3, mp4, mpeg, mpg, odp, ods, odt, oga, ogg, ogv, pdf, png, ppt, pptx, psd, ra, ram, rm, rpm, rv, skp, spx, tar, tex, tgz, txt, vob, wmv, xls, xlsx, xml, xpi, zip.
Requires at least: 1.5.1.3
Tested up to: 3.5.1
Stable tag: trunk

Adds icons automatically to any uploads inserted into your blog posts.

== Description ==

MimeTypes Link Icons is a plugin that looks for uploads in your blogs posts and adds a nice icon next to it. Option to add file size next to item.

Supported Extensions:

* .ai
* .asf
* .bib
* .csv
* .deb
* .doc
* .docx
* .djvu
* .dmg
* .dwg
* .dwf
* .epub
* .flac
* .gif
* .gz
* .indd
* .iso
* .jpg
* .log
* .m4v
* .midi
* .mkv
* .mov
* .mp3
* .mp4
* .mpeg
* .mpg
* .odp
* .ods
* .odt
* .oga
* .ogg
* .ogv
* .pdf
* .png
* .ppt
* .pptx
* .psd
* .ra
* .ram
* .rm
* .rpm
* .rv
* .skp
* .spx
* .tar
* .tex
* .tgz
* .txt
* .vob
* .wmv
* .xls
* .xlsx
* .xml
* .xpi
* .zip


Each icon is configurable. You can choose to display a PNG with transparent background or GIF with white matte. Each icon is available in the following sizes:

* 16x16px
* 24x24px
* 48x48px
* 64x64px
* 128x128px

== Installation ==


1. Upload the whole `mime_type_link_images` folder to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. By default the PDF icon will be the only one being searched for. It will display the 48x48 gif next to your pdf links. Any other 

== Frequently Asked Questions ==

= Does `MimeTypes Link Icons` just convert uploaded document links? =

No. It searches your post for any links containing the mimetype extensions you have activated. This will be triggered by any link.


== Screenshots ==

1. Screenshot of the administration screen
2. Screenshot of plugin in action.
3. MimeTypes Link Icons adds icons automatically to your inline attachments.
4. Now you can get mime type link images to add the file size of your attachment, too.

== Changelog ==

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
* Some bug fixes and plugin conflict fixes. The exlude class name can now be used on parents and the link itself

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
* Fixing an issue that effect asyncronous users only. http://wordpress.org/support/topic/plugin-mimetypes-link-icons-plugin-conflict-or-bug?replies=12#post-2349689

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
* Some bug fixes and plugin conflict fixes. The exlude class name can now be used on parents and the link itself

= 2.1.3 =
* Removing a couple of short php open tags

= 2.1.2 =
* Adding 3 new mime types (openoffice)

= 2.1.1 =
* Adding 14 new mime types (mostly video)

= 2.1.0 =
* File size was being overwritten by JS. Fixed. Please be aware that file size cannot work with asychronous loading type.

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
* Fixing an issue that effect asyncronous users only. http://wordpress.org/support/topic/plugin-mimetypes-link-icons-plugin-conflict-or-bug?replies=12#post-2349689

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