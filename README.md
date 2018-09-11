# AvantZoom (plugin for Omeka Classic)

The AvantZoom plugin provides image zooming using OpenSeadragon.

## Dependencies
AvantZoom depends on the following open source library which is included in the `views/shared/javascripts` folder.
Click the link below to see it copyright and license.

* [OpenSeadragon](https://openseadragon.github.io/) - An open-source, web-based viewer for high-resolution zoomable
 images, implemented in pure JavaScript, for desktop and mobile. 

## Installation

1. Unzip the AvantZoom-master file into your Omeka installation's plugin directory.
1. Rename the folder to AvantZoom.
1. Activate the plugin from the Admin → Settings → Plugins page.
1. Add a `zoom` folder to your Omeka installation's `files` folder.
1. Copy the `images` folder from the AvantZoom folder to the `files/zoom` folder. The folder contains icon used
by the OpenSeadragon viewer for the zoom in, zoom out, and other controls. AvantZoom does not use this folder.
1. Edit your theme's `show.php` file as explained below.


#### Edit show.php

The AvantZoom plugin provides everything you need to start using OpenSeadragon, but you must edit your theme's
`show.php` file to insert code at both the top and the bottom of the file. This code adds the OpenSeadragon viewer to the Show
page and also makes calls to the AvantZoom logic that configures OpenSeadragon to display an item's image.

The instructions that follow are using the `show.php` from the [Seasons](https://omeka.org/classic/themes/seasons/) theme.
You might need to make different edits for other themes.

First edit the code at the top of `show.php` by replacing the first four lines in the file (shown below) ...

```
<?php echo head(array('title' => metadata('item', array('Dublin Core', 'Title')),'bodyclass' => 'items show')); ?>
<h1><?php echo metadata('item', array('Dublin Core', 'Title')); ?></h1>
<div id="primary">
    <?php if ((get_theme_option('Item FileGallery') == 0) && metadata('item', 'has files')): ?>
```
... with the lines that follow. Note the insertion of `!$zoom &&` in the last line. This condition determines
whether or not to display the OpenSeadragon viewer. When `$zoom` is false, the item's image is not zoomable in which
case, the orgiginal HTML is rendered to display the image in the usual (non-zoomable) manner.
```
<?php
$zoomScript = ImageZoom::generateOpenSeadragonViewer($item);
$zoom = !empty($zoomScript);
if ($zoom)
    queue_js_file('openseadragon.min');
echo head(array('title' => metadata('item', array('Dublin Core', 'Title')),'bodyclass' => 'items show'));
echo '<h1>' . metadata('item', array('Dublin Core', 'Title')) . '</h1>';
if ($zoom)
    echo '<div id="openseadragon"></div>';
?>
<div id="primary">
    <?php if (!$zoom && (get_theme_option('Item FileGallery') == 0) && metadata('item', 'has files')): ?>
```
At the end of `show.php`, insert code just above the call to foot() so that the end of the file looks like this:
 
 ```
<?php
if ($zoom)
    echo $this->partial('avantzoom-script.php', array('viewerScript' => $zoomScript));
?>
<?php echo foot(); ?>
 ```

## Usage

Once you have AvantZoom installed properly per the Installation instructions above, you can begin to selectively make
item images zoomable. Not every image must or should be zoomable. The best candidates are large, high resolutions images that
have a lot of detail. Generally better results come from TIFF files, but high resolution JPEG files can work very well too.

You make an image zoomable by creating tiles for it as explained in the steps below. You then upload the tiles to your
Omeka server. The AvantZoom logic automatically detects the presence of tiles and displays the OpenSeadragon viewer.
If an item's image has no tiles, the image is displayed in the usual way.

By default, AvantZoom configures OpenSeadragon to work with tiles created by the [Zoomify](http://www.zoomify.com/) 
application. If you are using other kinds of tiles, you'll need to edit the `ImageZoom::emitZoomScript function` in `ImageZoom.php`
accordingly.

Follow these steps to make an image zoomable:

1. Ensure that the item already has a non-zoomable image attached to it. An item with no image will not display its zoomable image.
1. Use Zoomify to create a folder of tiles from an item's high resolution image.
1. Rename the folder to be the same as the item's Identifier value.
1. Use FTP or other mechanism to transfer the folder to your Omeka installation's `files/zoom` folder.

If the item has more than one image, e.g. a document with eight pages, follow these steps:
1.	Follow Steps 1 and 2 above for each image, but give each folder a unique name e.g. by adding a suffix to the item’s identifier.
1.	Create a single folder for the item named using the item’s Identifier value.
1.	Move the folders for each image into this single folder.
1.	Transfer the single folder with all of its subfolders to the `zoom` folder as described above in Step 3.
1.  The AvantZoom plugin will automatically detect that the item has multiple zoomable images and display them appropriately.

## Warning

Use it at your own risk.

##  License

This plugin is published under [GNU/GPL].

This program is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation; either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
details.

You should have received a copy of the GNU General Public License along with
this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

Copyright
---------

* Created by [gsoules](https://github.com/gsoules)
* Copyright George Soules, 2017-2018.
* See [LICENSE](https://github.com/gsoules/AvantRelationships/blob/master/LICENSE) for more information.

