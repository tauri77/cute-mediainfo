=== Cute MediaInfo ===
Contributors: Mauricio Galetto
Donate link: https://www.paypal.com/donate/?hosted_button_id=XNASRT5UB7KBN
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl.txt
Tags: Video Info, MediaInfo, Video Links
Tested up to: 5.9
Stable tag: 1.0.3
Requires PHP: 5.6

Customizable plugin to display MediaInfo for humans.

== Description ==

### MediaInfo for humans

* MediaInfo library for modern browsers
* Autocomplete from mediainfo text
* Different ways to display using profiles
* Customizable profiles: Styles
* Customizable profiles: Layout
* Customizable profiles: Data to display
* Module for links (easy add)
* Two icon packs (extensible)
* Use as block or shortcode

Supported Links: 1fichier, Amazon, Amazon UE, box, Clicknupload, DailyUploads, ddownload, Dropapk, Dropbox, Fast Down, FastClick, FileFactory, GDrive, HexUpload, Jottacloud, KatFile, Mediafire, MEGA, MEGA Folder, NitroFlare, OneDrive, pCloud, pCloud(Euro), Rapidgator, Uploaded, UploadGIG, Uptobox, Usersdrive.

### Extra features with PRO Addon

Did you know that [PRO Addon](https://galetto.info/product/cute-mediainfo-pro/) contains a lot of extra features:

* **Screenshots with** thumbnails and full-size viewer
* **Link checker** for offline status
* **Link protection** with reCaptcha
* **Link override** ( usage example: protected or shortened link )
* **Extra icon pack**: Flat Color
* **Export/Import** profiles
* **Source Quality** field (DVDRip, BRRip, etc)


### Documentation

Documentation online on [cutemi-docs.galetto.info](https://cutemi-docs.galetto.info/)

== Screenshots ==

1. Cute MediaInfo Block
2. Cute MediaInfo Block Preview
3. Editing a MediaInfo 1
4. Editing a MediaInfo 2
5. MediaInfo List
6. General Settings
7. Advanced Settings
8. Profiles
9. Wizard

== Frequently Asked Questions ==

= What are the profiles =

Profiles determine the way information is displayed.
The same mediainfo can be shown with different profiles in different places.
For example, with profile "summary" in a widget, and with profile "full" in a post.
Each profile has different styles, layouts and data to show.

* Styles: Determine the colors, dimensions and fonts.
* Layouts: Determines if a block/group of information is displayed, how it is displayed and where. Examples of blocks are "Videos", "Audios", "Links".
* Data to show: Determine what information and where to show it. For example, whether or not to show the bitrate, and if it is shown, to show in text, icon or both. And then show it before or after bitrate mode, etc.

= Can i create my own icon pack? =

Yes, you can use this repository as a base: [CuteMI Gray Scale Icon Pack](https://github.com/tauri77/CuteMI-Gray-Scale-Icon-Pack)

= Can I create my own profile with a PHP template? =

Yes, you can, although customization options will not be available.
You can use this repository as a base: [CuteMI Template PHP Example](https://github.com/tauri77/CuteMI-Template-PHP-Example)

== Changelog ==

= 1.0.3 =
* fix some UI issues
* add edit link on block toolbar

= 1.0.2 =
* call unregister_post_type on deactivate before flush_rewrite_rules
* fix some css
* fix arguments on hook cutemi_table_customize_style
* fix meta term with type select (always show default)
* fix VP8 svg

= 1.0.1 =
* Code Cleanup
* Removed the option to export/import profiles for security reasons
* file_put_content was replaced by WP_Filesystem
* Added some validations and variable escaping
* curl was replaced by WP_Http

= 1.0.0 =
* First version of the plugin
