=== Author Customization ===
Contributors: cconover
Donate link: https://christiaanconover.com/code/wp-author-customization#donate
Tags: author, user, profile, tinymce, wysiwyg, rel-nofollow
Requires at least: 3.6
Tested up to: 4.0
Stable tag: 0.3.3
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Author Customization expands core author management with features such as per-post author information and rich user bio editing.

== Description ==

Author Customization gives you much more flexibility in managing post and page author data. Your author data will no longer be tied solely to the WordPress user system, and can now be managed on a per-post basis. Author name and description are saved with each post, allowing you to preserve this information as it was when the post was published.

**Features**

- Per-post author display name and biographical info
- TinyMCE (WYSIWYG) editor for user profile and per-post biographical info
- rel="nofollow" link inside biographical info entries

[Author Customization on Github](https://github.com/cconover/wp-author-customization)

== Installation ==

1. Upload the `author-customization` directory to the `wp-content/plugins` directory
2. Activate the plugin through the `Plugins` menu in WordPress
3. Adjust the settings on the `Author Custom` Settings page

== Frequently Asked Questions ==

= What data about an author gets saved to post metadata? =
The post author's display name and biographical info are copied from their user profile.

= Can I turn per-post author info on or off at any time? =
Yes. The plugin saves author info to each post you edit whether or not you've enabled displaying author data from the post metadata. If you decide later that you want per-post author information to be displayed, any post you've created or edited since installing the plugin will already be set up to do this.

= Can I assign multiple authors to a post? =
Not yet, but [that's in the works](https://github.com/cconover/wp-author-customization/issues/9). I plan to add support for this in a future update.

= I'm not seeing any author information on my posts! What's gone wrong? =
In order for author information to appear on your posts and pages, the theme you're using must have support for this. If it does, you should see your author info changes without issue. If you know it shows author information but you're not seeing the changes you made using this plugin, pop over to the Support area. If your theme does not have support for author info, contact the theme developer.

== Screenshots ==

1. Author meta box, displayed when editing a post or page. The author dropdown menu is only shown to editors and higher.

2. Plugin options page.

3. TinyMCE editor on user profile page. This is optional, but enabled by default.

== Changelog ==

= 0.3.2 =
Fixed bug causing author info not to be saved to post meta if the user doesn't have permissions to edit other users' posts and pages.

= 0.3.1 =
* Full support for WordPress 3.9 and TinyMCE 4.0
* Fixed the plugin upgrade process to ensure deprecated settings are upgraded, and upgrades run more smoothly for all future versions
* Option to turn WYSIWYG on or off has been removed. WYSIWYG is on unless the system or user cannot support it.
* General code cleanup and minor bug fixes

= 0.3.0 =
The plugin has been completely rewritten to be object-oriented, in preparation for new features being added in the future, and to make it run more efficiently.

The plugin options have been restructured as well. Whe you upgrade, your current settings should be ported to the new structure. If you notice any strange behavior, check the plugin settings page.

Note: this plugin has some incompatibilities with WordPress 3.9. These will be fixed before the official release of 3.9, and will be noted in the changelog.

= 0.2.2 =
Fixed bug that threw a "Missing Argument" error when creating a new post.

= 0.2.1 =
* Bug fix: WYSIWYG on Edit Profile page was not showing full formatting.
* Bug fix: Calling profile display name and description when no post-specific info was available could result in fatal error.
* Bug fix: WYSIWYG editor settings was called improperly for profile page editor.

= 0.2.0 =
* Added option to update the author's user profile with the data entered in the post author info fields.
* Bug fixes

= 0.1.1 =
* Bug fix: changes made to author info after a new post author was selected were not being saved because the server was not being told whether JavaScript was active.
* Bug fix: "loading" spinner image displayed when a new post author is selected was being shown out of proportion.

= 0.1.0 =
Initial release.

== Upgrade Notice ==

= 0.3.2 =
Fixed bug causing author info not to be saved to post meta if the user doesn't have permissions to edit other users' posts and pages.

= 0.3.1 =
Fixed incompatibilities with WordPress 3.9 and TinyMCE 4.0.

= 0.3.0 =
Completely rewritten to be object-oriented.

= 0.2.1 =
Fixed bugs that can cause fatal errors when displaying the author name & description.

= 0.1.1 =
Major bug in saving customized author info when selecting a new post author has been corrected.
