.. include:: ../../Includes.txt

.. _typoscript:

========================
TypoScript Configuration
========================

The TypoScript configuration properties for the extension can be found in
:typoscript:`plugin.tx_pxadealers.settings`.

.. _typoscript-rootlevel:

Root level
==========

TypoScript properties defined in :typoscript:`plugin.tx_pxadealers.settings`,
e.g. :typoscript:`plugin.tx_pxadealers.settings.categoryPid`.

.. _typoscript-categorypid:

categoryPid
-----------

:aspect:`Property`
   categoryPid

:aspect:`Data type`
   :ref:`t3tsref:data-type-integer`

:aspect:`Description`
   The page ID where category records are stored.

.. _typoscript-cssfile:

cssFile
-------

:aspect:`Property`
   cssFile

:aspect:`Data type`
   :ref:`t3tsref:data-type-file`

:aspect:`Default`
   :file:`EXT:pxa_dealers/Resources/Public/Css/pxa_dealers.css`

:aspect:`Description`
   File to the CSS file. Included in the :file:`Map.html` template.

.. tip::

   Setting this property to an empty string or clearing it with the TypoScript
   :typoscript:`>` operand will not lead to an error. Instead, no file will be
   included.

.. _typoscript-markerclustererfile:

markerClustererFile
-------------------

:aspect:`Property`
   markerClustererFile

:aspect:`Data type`
   :ref:`t3tsref:data-type-file`

:aspect:`Default`
   :file:`EXT:pxa_dealers/Resources/Public/JavaScript/markerclusterer.js`

:aspect:`Description`
   File to the JavaScript file handling marker clusters. Included in the
   :file:`Map.html` template.

.. tip::

   Setting this property to an empty string or clearing it with the TypoScript
   :typoscript:`>` operand will not lead to an error. Instead, no file will be
   included.

.. _typoscript-pluginfile:

pluginFile
----------

:aspect:`Property`
   pluginFile

:aspect:`Data type`
   :ref:`t3tsref:data-type-file`

:aspect:`Default`
   :file:`EXT:pxa_dealers/Resources/Public/JavaScript/pxa_dealers_plugin.js`

:aspect:`Description`
   File to the JavaScript file handling the main plugin functionality. Included
   in the :file:`Map.html` template.

.. tip::

   Setting this property to an empty string or clearing it with the TypoScript
   :typoscript:`>` operand will not lead to an error. Instead, no file will be
   included.

.. _typoscript-dealersfile:

dealersFile
-----------

:aspect:`Property`
   dealersFile

:aspect:`Data type`
   :ref:`t3tsref:data-type-file`

:aspect:`Default`
   :file:`EXT:pxa_dealers/Resources/Public/JavaScript/pxa_dealers_plugin.js`

:aspect:`Description`
   File to the JavaScript file handling the dealer functionality. Included
   in the :file:`Map.html` template.

.. tip::

   Setting this property to an empty string or clearing it with the TypoScript
   :typoscript:`>` operand will not lead to an error. Instead, no file will be
   included.

.. _typoscript-searchfile:

searchFile
----------

:aspect:`Property`
   searchFile

:aspect:`Data type`
   :ref:`t3tsref:data-type-file`

:aspect:`Default`
   :file:`EXT:pxa_dealers/Resources/Public/JavaScript/pxa_dealers_search.js`

:aspect:`Description`
   File to the JavaScript file handling search functionality. Included in the
   :file:`Form.html` template.

.. tip::

   Setting this property to an empty string or clearing it with the TypoScript
   :typoscript:`>` operand will not lead to an error. Instead, no file will be
   included.

.. _typoscript-awesompletecssfile:

awesompleteCssFile
------------------

:aspect:`Property`
   awesompleteCssFile

:aspect:`Data type`
   :ref:`t3tsref:data-type-file`

:aspect:`Default`
   :file:`EXT:pxa_dealers/Resources/Public/Css/awesomplete.css`

:aspect:`Description`
   CSS file for the `Awesomeplete <https://projects.verou.me/awesomplete/>`_
   autocomplete plugin. Included in the :file:`Form.html` template.

.. tip::

   Setting this property to an empty string or clearing it with the TypoScript
   :typoscript:`>` operand will not lead to an error. Instead, no file will be
   included.

.. _typoscript-awesompletejsfile:

awesompleteJavaScriptFile
-------------------------

:aspect:`Property`
   awesompleteJavaScriptFile

:aspect:`Data type`
   :ref:`t3tsref:data-type-file`

:aspect:`Default`
   :file:`EXT:pxa_dealers/Resources/Public/JavaScript/awesomplete.min.js`

:aspect:`Description`
   JavaScript file for the
   `Awesomeplete <https://projects.verou.me/awesomplete/>`_ autocomplete plugin.
   Included in the :file:`Form.html` template.

.. tip::

   Setting this property to an empty string or clearing it with the TypoScript
   :typoscript:`>` operand will not lead to an error. Instead, no file will be
   included.

.. _typoscript-map:

Map
===

TypoScript properties used to configure the map display can be found in
:typoscript:`plugin.tx_pxadealers.settings.map.*`.

.. _typoscript-map-stylesjson:

stylesJSON
----------

:aspect:`Property`
   stylesJSON

:aspect:`Data type`
   JSON string

:aspect:`Description`
   Custom colors and styles for the Google Map display. If set, this property
   must be a JSON string, as defined in
   `Google Map's API <https://developers.google.com/maps/documentation/javascript/examples/maptype-styled-simple#maps_maptype_styled_simple-javascript>`__.

   .. code-block:: typoscript

    stylesJSON = [ { elementType: "geometry", stylers: [{ color: "#ebe3cd" }] } ]

.. _typoscript-map-name:

name
----

:aspect:`Property`
   name

:aspect:`Data type`
   :ref:`tsref:data-type-string`

:aspect:`Description`
   The display name for the map.

.. _typoscript-map-zoomonshow:

zoomOnShow
----------

:aspect:`Property`
   zoomOnShow

:aspect:`Data type`
   :ref:`tsref:data-type-integer`

:aspect:`Default`
   14

:aspect:`Description`
   The default zoom level for the map.
   `More about zoom levels <https://developers.google.com/maps/documentation/javascript/overview#zoom-levels>`__

.. _typoscript-map-markerclustererimagepath:

markerClusterer.imagePath
-------------------------

:aspect:`Property`
   markerClusterer.imagePath

:aspect:`Data type`
   :ref:`tsref:data-type-integer`

:aspect:`Default`
   :file:`/typo3conf/ext/pxa_dealers/Resources/Public/Images/markerClusterer/m`

:aspect:`Description`
   A path prefix for marker cluster icons.
   `More about marker clustering <https://developers.google.com/maps/documentation/javascript/marker-clustering>`__

.. _typoscript-map-markertypes:

markerTypes
-----------

:aspect:`Property`
   markerTypes

:aspect:`Data type`
   array

:aspect:`Default`
   :typoscript:`default = /typo3conf/ext/pxa_dealers/Resources/Public/Icons/map_marker_icon_blue.png`

:aspect:`Description`
   An array of marker icons for custom markers. :typoscript:`default` should
   always be defined.
   `More about custom markers <https://developers.google.com/maps/documentation/javascript/custom-markers>`__

.. _typoscript-map-javascriptapikey:

googleJavascriptApiKey
----------------------

:aspect:`Property`
   googleJavascriptApiKey

:aspect:`Data type`
   :ref:`tsref:data-type-string`

:aspect:`Description`
   A Google API key for **frontend** use only.

   .. warning::

      :typoscript:`googleJavascriptApiKey` and :typoscript:`googleServerApiKey`
      *must* be unique. Do *not* use the same code for both. The two keys should
      have different permissions. Calls made using the server side should not be
      possible with the JavaScript (frontend) key.

.. _typoscript-map-serverkey:

googleServerApiKey
------------------

:aspect:`Property`
   googleServerApiKey

:aspect:`Data type`
   :ref:`tsref:data-type-string`

:aspect:`Description`
   A Google API key for **backend** use only.

   .. warning::

      :typoscript:`googleJavascriptApiKey` and :typoscript:`googleServerApiKey`
      *must* be unique. Do *not* use the same code for both. The two keys should
      have different permissions. Calls made using the server side should not be
      possible with the JavaScript (frontend) key.

.. _typoscript-map-scrollfix:

scrollFix
---------

:aspect:`Property`
   scrollFix

:aspect:`Data type`
   :ref:`tsref:data-type-integer`

:aspect:`Default`
   0

:aspect:`Description`
   The number of extra pixels to scroll down when clicking *Show on map*. This
   is useful if you are using a sticky header menu that covers apart of the top
   of the page.

.. _typoscript-map-scrollfixmobile:

scrollFixMobile
---------------

:aspect:`Property`
   scrollFixMobile

:aspect:`Data type`
   :ref:`tsref:data-type-integer`

:aspect:`Default`
   0

:aspect:`Description`
   The number of extra pixels to scroll down when clicking *Show on map* on a
   mobile device (small screen sizes with a width 990px and less). This is
   useful if you are using a sticky header menu that covers apart of the top of
   the page.

.. _typoscript-map-hideifempty:

scrollFixMobile
---------------

:aspect:`Property`
   hideIfEmpty

:aspect:`Data type`
   :ref:`tsref:data-type-boolean`

:aspect:`Default`
   false

:aspect:`Description`
   If true, the map is hidden if there are no visible markers.

.. _typoscript-demand:

Demand
======

TypoScript properties used to configure search demand settings can be found in.
:typoscript:`plugin.tx_pxadealers.settings.demand.*`. These settings are also
available in the plugin flexForm, and configurations made there take presedence.

.. _typoscript-demand-orderby:

orderBy
-------

:aspect:`Property`
   orderBy

:aspect:`Data type`
   :ref:`tsref:data-type-string`

:aspect:`Default`
   crdate

:aspect:`Description`
   Order dealers records by this field. Recommended fields to use for this
   field:

   * :sql:`crdate`: Creation date
   * :sql:`name`: The dealer's name
   * :sql:`tstamp`: Last updated date

.. _typoscript-demand-orderdirection:

orderDirection
--------------

:aspect:`Property`
   orderDirection

:aspect:`Data type`
   :ref:`tsref:data-type-string`

:aspect:`Default`
   asc

:aspect:`Description`
   Order direction for dealer records. Available options:

   * :sql:`asc`: Ascending
   * :sql:`desc`: Descending

.. _typoscript-demand-countries:

countries
---------

:aspect:`Property`
   countries

:aspect:`Data type`
   :ref:`tsref:data-type-list`

:aspect:`Description`
   Limit to these countries. Comma-separated list of country UIDs from the
   :sql:`static_countries` table.

.. _typoscript-demand-categories:

categories
---------

:aspect:`Property`
   categories

:aspect:`Data type`
   :ref:`tsref:data-type-list`

:aspect:`Description`
   Limit to these categories. Comma-separated list of category UIDs.

.. _typoscript-search:

Search
======

TypoScript properties used to configure the dealers search can be found in
:typoscript:`plugin.tx_pxadealers.settings.search.*`.

.. _typoscript-search-searchfields:

searchFields
------------

:aspect:`Property`
   searchFields

:aspect:`Data type`
   :ref:`tsref:data-type-list`

:aspect:`Default`
   name, zipcode, city

:aspect:`Description`
   Database fields to include in free text search.

   **Typical acceptable search fields**

   * Dealer record
      * `address`
      * `city`
      * `description`
      * `email`
      * `link`
      * `name`
      * `phone`
      * `website`
      * `zipcode`
   * Dealer country record
      * `country.cn_iso_2`: 2-character ISO code
      * `country.cn_iso_3`: 3-character ISO code
      * `country.cn_iso_nr`: ISO country number
      * `country.cn_official_name_en`: The official country name in English,
        e.g. "Kingdom of Sweden".
      * `country.cn_official_name_local`: The official country name in the
        country's local language(s), e.g. "Konungariket Sverige".
      * `country.cn_short_en`: The country's short (common) name in English,
        e.g. "Sweden".
      * `country.cn_short_local`: The country's short (common) name in the
        country's local language(s), e.g. "Sverige".

.. _typoscript-search-secondarysearchfields:

secondarySearchFields
---------------------

:aspect:`Property`
   secondarySearchFields

:aspect:`Data type`
   :ref:`tsref:data-type-list`

:aspect:`Default`
   country.shortNameLocal, country.shortNameEn

:aspect:`Description`
   Search fields used if there are no results from an initial search
   using :ref:`typoscript-search-searchfields`. Performing a secodn search using
   new fields årevents lots of results, e.g. if the country name is part of the
   search phrase. These fields are typically fields with potentially broad
   search matches.

   See :ref:`typoscript-search-searchfields` for a list of typical fields
   available.

.. _typoscript-search-searchinradius:

searchInRadius
--------------

:aspect:`Property`
   searchInRadius

:aspect:`Data type`
   :ref:`tsref:data-type-boolean`

:aspect:`Default`
   false

:aspect:`Description`
   If enabled, search is limited to results within a radius around the
   geographic coordinates of the location provided by the user. See also
   :ref:`typoscript-search-radius`.

.. _typoscript-search-radius:

radius
------

:aspect:`Property`
   radius

:aspect:`Data type`
   :ref:`tsref:data-type-integer`

:aspect:`Default`
   50

:aspect:`Description`
   The search radius in kilometers. Used when geographic coordinates are
   provided or the coordinates of the place name given in the search string.

.. _typoscript-search-searchclosest:

searchClosest
-------------

:aspect:`Property`
   searchClosest

:aspect:`Data type`
   :ref:`tsref:data-type-boolean`

:aspect:`Default`
   false

:aspect:`Description`
   When true, the plugin will try to acquire the user's coordinates through the
   browser.

.. _typoscript-search-zipcodeinexactness:

zipcodeInexactness
------------------

:aspect:`Property`
   zipcodeInexactness

:aspect:`Data type`
   :ref:`tsref:data-type-integer`

:aspect:`Default`
   2

:aspect:`Description`
   Search for zip codes in database with X number of digits flexibility.
   E.g. searching for zipcode 12345 with :typoscript:`zipcodeInexactness = 3`
   will search within 12XXX, i.e. any zipcode between 12000 and 12999.

   This feature presumes that zip codes are similar within the same geographic
   region. Swedish zip codes 85000-85999 belong to the area around Sundsvall.
   We can therefore assume that a users that search for "85229" will be close
   to a dealer with zip code "85350".

   .. info::

      Google's APIs do not usually return valuable coordinates for for searches
      that only contain a zip code.

.. _typoscript-search-splitsearchstring:

splitSearchString
-----------------

:aspect:`Property`
   splitSearchString

:aspect:`Data type`
   :ref:`tsref:data-type-boolean`

:aspect:`Default`
   true

:aspect:`Description`
   If true, the search string is split and each word is matched agains the
   contents of each search field. The string is split using
   :ref:`typoscript-search-splitsearchstringregex`.

   **Example:** Let's say :ref:`typoscript-search-searchfields` is set to
   :typoscript:`name, address`. Searching for "elephant zebra" will return a
   dealer the name "Big Elephants, Inc." and a dealer with the address "9 Zebra
   Lane" because they both have "elephant" or "zebra" in the defined search
   fields.

   Setting this property to false will only return dealers with exact matches,
   e.g. a dealer with a name like "Lion Elephant Zebra Foods, Inc" because
   the name contains the exact string "elephant zebra".

.. _typoscript-search-splitsearchstringregex:

splitSearchStringRegex
----------------------

:aspect:`Property`
   splitSearchStringRegex

:aspect:`Data type`
   Regular expression

:aspect:`Default`
   /[\s,-]+/

:aspect:`Description`
   A regular expression search pattern matching all characters that should be
   used to split a search string (if :ref:`typoscript-search-splitsearchstring`
   is enabled).

.. _typoscript-search-joinsearchstringregex:

joinSearchStringRegex
---------------------

:aspect:`Property`
   joinSearchStringRegex

:aspect:`Data type`
   Regular expression

:aspect:`Default`
   /^\d+$/

:aspect:`Description`
   Adjacent search strings matching this pattern are re-joined if
   :ref:`typoscript-search-splitsearchstring` is enabled.

   The default pattern matches numbers, which helps with zip code search with
   numbers only. Some countries prefer to split zip codes, e.g. using spaces.
   When we split the search string, a zip code written as "123 45" will lead to
   a search for text containing "123" or "45", which will lead to less exact
   matches for zip codes than if the code was written "12345".

   This pattern allows us to merge strings we presume are formatted zip codes,
   such as "123" and "45", into "12345".

.. _typoscript-search-enableautocomplete:

enableAutocomplete
------------------

:aspect:`Property`
   enableAutocomplete

:aspect:`Data type`
   :ref:`tsref:data-type-boolean`

:aspect:`Default`
   true

:aspect:`Description`
   Enables the autocomplete dropdown menu in the search results.

   .. image:: Images/EnableAutocomplete.png

.. _typoscript-search-limittocountries:

limitToCountries
----------------

:aspect:`Property`
   limitToCountries

:aspect:`Data type`
   :ref:`tsref:data-type-list`
   (`2-char ISO country codes <https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2>`)

:aspect:`Max items`
   5

:aspect:`Description`
   Limit the autocomplete to results within these countries.
   `Google's Place Autocomplete API <https://developers.google.com/maps/documentation/places/web-service/autocomplete>`__
   supports maximum 5 countries.

   **Example:** Ensure that the autocomplete only returns autocomplete
   suggestions for places within Denmark, Norway, and Sweden. This means the
   user searching for "Oslo" will *not* see suggestions for "Oslo, Minnesota,
   USA".

   .. code-block:: typoscript

      limitToCountries = dk, no, se

.. _typoscript-search-searchresultpage:

searchResultPage
----------------

:aspect:`Property`
   searchResultPage

:aspect:`Data type`
   :ref:`tsref:data-type-page-id`

:aspect:`Description`
   The ID of the page to display the search results on.

.. _typoscript-list:

Dealers' list
=============

TypoScript properties used to configure the dealer listing can be found in
:typoscript:`plugin.tx_pxadealers.settings.dealersList.*`.

.. _typoscript-list-noimagepath:

noImagePath
-----------

:aspect:`Property`
   noImagePath

:aspect:`Data type`
   :ref:`tsref:data-type-path`

:aspect:`Default`
   :file:`EXT:pxa_dealers/Resources/Public/Images/noimage.png`

:aspect:`Description`
   Path to the image that is displayed if a dealer record doesn't have any image
   defined and :ref:`typoscript-list-showdefaultimageifnologo` is false.

.. _typoscript-list-showlogo:

showLogo
--------

:aspect:`Property`
   showLogo

:aspect:`Data type`
   :ref:`tsref:data-type-boolean`

:aspect:`Default`
   true

:aspect:`Description`
   If true, display a dealer's logo in the list.

.. _typoscript-list-showdefaultimageifnologo:

showDefaultImageIfNoLogo
------------------------

:aspect:`Property`
   showDefaultImageIfNoLogo

:aspect:`Data type`
   :ref:`tsref:data-type-boolean`

:aspect:`Default`
   true

:aspect:`Description`
   If true, display the image defined in :ref:`typoscript-list-noimagepath` if
   the dealer record has no logo image defined.

.. _typoscript-list-imagewidth:

imageWidth
----------

:aspect:`Property`
   imageWidth

:aspect:`Data type`
   :ref:`tsref:data-type-pixels`

:aspect:`Default`
   150m

:aspect:`Description`
   The width of images in the list.

.. _typoscript-list-imageheight:

imageHeight
----------

:aspect:`Property`
   imageHeight

:aspect:`Data type`
   :ref:`tsref:data-type-pixels`

:aspect:`Default`
   150m

:aspect:`Description`
   The height of images in the list.
