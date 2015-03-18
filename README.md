# Custom Opt-Out Styles Piwik Plugin

[![Build Status](https://travis-ci.org/Zeichen32/PiwikCustomOptOut.png?branch=master)](https://travis-ci.org/Zeichen32/PiwikCustomOptOut)

## Description

Adds a new admin tab allowing to change the opt-out CSS Styles for each website.

## Usage

1) Click on the "Settings" link located in the top menu on the right

2) Click on the "Custom Opt-Out" tab located in the "Settings" section of the sidebar on the left

3) Enter your customized CSS code into the textarea input field called "Custom Css" e.g.
```css     
  body {
    font-family: Arial, Verdana, sans-serif;
    font-size: 12px;
    color: #ddd;
    line-height: 160%;
    margin: 10px;
    padding: 0;
  }
```
or insert a URL to the file containing your custom CSS into the input field called "External CSS File" e.g.

  ``http://www.example.org/styles/piwikcustom.css``

4) Click the "Save" button.

5) Use the iframe code provided below the input fields to add the Piwiki Opt-Out to your website.

## How to change the opt-out text

I needed to remove this feature due to the breaking changes in [Piwik 2.11.0](https://github.com/piwik/piwik/blob/master/CHANGELOG.md#piwik-2110)

## Requirements

+ Piwik >= 2.7.0

## Changelog

#### CustomOptOut 0.2.5: (HotFix)
* Fix [CodeMirror Editor](http://codemirror.net) Textarea with large css code
* Fix invalid html in optout template

#### CustomOptOut 0.2.4:
* (Issue #23) Check DNT in OptOut Page
* Update [CodeMirror Editor](http://codemirror.net)

#### CustomOptOut 0.2.3:
* (Issue #22) Remove escaping from externel css url
* Fix PluginSettings

#### CustomOptOut 0.2.2: (HotFix)
* Update [CodeMirror Editor](http://codemirror.net)

#### CustomOptOut 0.2.1:
* (Issue #21) Remove possibility to change opt-out text for each website (Breaking Changes in [Piwik 2.11.0](https://github.com/piwik/piwik/blob/master/CHANGELOG.md#piwik-2110))

#### CustomOptOut 0.2.0:
* Add possibility to change opt-out text for each website

#### CustomOptOut 0.1.9:
* Add XFrameOption [See Piwik Commit](https://github.com/piwik/piwik/commit/25545fdc55a1decd13548c1f3f6479789956e56c)

#### CustomOptOut 0.1.8:
* (MR #15) Make the opt-out form work even if JavaScript is disabled (craue)

#### CustomOptOut 0.1.7:
* (MR #14) Update Readme (kghbln)

#### CustomOptOut 0.1.6:
* Add [CodeMirror Editor](http://codemirror.net) to highlight the CSS Code

#### CustomOptOut 0.1.5: (HotFix)
* (Issue #6) Disable AngularJs form binding

#### CustomOptOut 0.1.4:
* (Issue #3) Code updated to support Piwik 2.1 and newer
* (Issue #2) Allow relative urls in css file field

#### CustomOptOut 0.1.3:
* (MR #1) Added a p-tag around the opt-out text for better markup and easier styling. (christianseel)

#### CustomOptOut 0.1.2:
* Fix wrong css escaping

#### CustomOptOut 0.1.1:
* Initial Version


## Authors

**Jens Averkamp**

+ [https://github.com/Zeichen32](https://github.com/Zeichen32)

**Sven Motz**

+ [https://github.com/xMysteriox](https://github.com/xMysteriox)

## Support
**Please direct any feedback to [https://github.com/Zeichen32/PiwikCustomOptOut](https://github.com/Zeichen32/PiwikCustomOptOut)**

## Copyright and license

Released under the GPL v3 (or later) license, see [misc/gpl-3.0.txt](misc/gpl-3.0.txt)
