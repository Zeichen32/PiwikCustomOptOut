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


## Requirements

+ Piwik >= 2.0.0

## Changelog

#### CustomOptOut 0.1.8:
* (MR #15) Make the opt-out form work even if JavaScript is disabled (craue)

#### CustomOptOut 0.1.7:
* (MR #14) Update Readme (kghbln)

#### CustomOptOut 0.1.6:
* Add [CodeMirror Editor](http://codemirror.net) to highlight the CSS Code

#### CustomOptOut 0.1.5:
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
