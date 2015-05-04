=== energ1zer ===
Contributors: frankquebec
Donate link: http://yipp.ca
Tags: shortcode rich text edit layout responsive design thumbnails rounded
Requires at least: 3.5.1
Tested up to: 3.6
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Energ1zer allows you to map post and pages to dynamic elements in other pages, such as the featured images.

== Description ==

Features 

*   permanent HTML tags in post
*   permanent line breaks in post
*   automatic rounded pictures thumbnails (bubbles)
*   rounded thumbnails (bubbles) can be made automatically grayscale

Wordpress sometime has the bad habit of screwing with your HTML markup when you switch from Html vs Text editor. When you do a nice design for a client, you want this design to stay in place. This plugin allows to insert shortcodes such as [br] to have permanent line breaks in your post. 

== Usage ==

= For line break that will not dispear when you edit the page =
[br]

= For spacing that will not break when you edit the page =

[energ1zer spacer="5"]
[energ1zer spacer="5" break="true"]  // will break:both floating elements

= To render safely an email link, example someguy@gmail.com =

<a class="jsEnerg1zerEmail" href="#email" data-username="someguy" data-domain="gmail.com"><img class=" size-full wp-image-94 alignleft" src="/wp-content/uploads/2015/04/contact-e4.png" alt="contact" width="133" height="17" /></a>

== Installation ==

This section describes how to install the plugin and get it working.

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'energ1zer'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `energ1zer.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `energ1zer.zip`
2. Extract the `energ1zer` directory to your computer
3. Upload the energ1zer` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard


== Frequently Asked Questions ==

= What about the rounded thumbnails, how does it works? Do I have to do some processing to my images before uploading? =

There is no special processing needed. If you want to re-center the part that is in the circle - that is re-position it - you can use Wordpress built-in function to re-position the thumbnail.

== Screenshots ==

N/A

== Changelog ==

= 1.0 =
First version


