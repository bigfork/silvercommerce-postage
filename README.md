# SilverCommerce Postage Setup

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/silvercommerce/postage/badges/quality-score.png?b=1.0)](https://scrutinizer-ci.com/g/silvercommerce/postage/?branch=1.0)
[![Build Status](https://travis-ci.org/silvercommerce/postage.svg?branch=1.0)](https://travis-ci.org/silvercommerce/postage)

Adds a basic postage setup to a SilverCommerce install, and allows users to
easily add new postage types.

## Instalation

Install this via composer:

    composer require silvercommerce/postage

Not using composer? [Install composer](https://getcomposer.org/)

Now run `dev/build` (either via the browser, or using sake).

## Basic usage

This module adds a "Postage Settings" field to the `Shop` tab on `SiteConfig`
(/admin/settings). By default three postage types are included:

* Flat Rate (rate applied to any parcel in the chosen locations).
* Weight Based (define rates based on the a minimum and maximum weight).
* Quantity Based (defined rates based on the number of items in a parcel).
* Price Based (define rates based on the value of a parcel).

**NOTE** The default postage types can be assigned to `Locations`. In order to
use these locations, you will have to create "Zones", as defined by the
[GeoZones module](https://github.com/silvercommerce/geozones).

These zones can then be linked to the postage type you choose and are used to
determine location when the `PostageType` is attempting to provide a list of
possible `PostageOptions`.

## Creating new `PostageType`'s

The postage module provides a base class (`PostageType`) that is the bases of
postage options. `PostageType` provides one method: `getPossiblePostage` which 
is responsible for returning an SSList of `PostageOption` objects.

This system is designed to be as generic as possible. To create your own postage
options, simply extend `PostageOption` and then return your own list of options
within `getPossiblePostage`.