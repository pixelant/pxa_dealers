.. include:: ../../Includes.txt

.. _registerkeys:

===========================
Registering Google API Keys
===========================

.. _registerkeys-two:

JavaScript and server (PHP) keys
================================

For the extension to work properly and avoid misuse of your API keys, you will
have to register *two* keys and assign them to the correct TypoScript
configuration properties:

* :ref:`typoscript-map-javascriptapikey`: A publicly accessible key used in
  JavaScript.
* :ref:`typoscript-map-serverkey`: A secret key used by PHP on the server.

The process to register the keys is the same for both, but each key needs
different access privileges.

.. _registerkeys-register:

How to register the API keys
============================

.. rst-class:: bignums-xxl

1. Go to the Credentials Console

   Open a web browser and navigate to
   `console.cloud.google.com/apis/credentials <https://console.cloud.google.com/apis/credentials>`__.

   If you are not logged in, you will be asked to log into a Google Account. If
   you have multiple accounts, make sure that you log into the one with the
   billing information you would like to use.

2. Select project

   The link above should bring you to the *Credentials* (1) section of the
   *Google Cloud Platform*'s *APIs & Services* console.

   Ensure that you have selected the correct project from the drop-down menu (2)
   at the top of the page or created a new project.

   Here's how the console looks with some API keys already added (3):

   .. image:: ../Images/GoogleApiCredentials.png

3. Create new credential

   Click the :guilabel:`Create credentials` button (1) and select
   :guilabel:`API key` (2) from the drop-down menu.

   .. image:: ../Images/CreateCredentialsMenu.png
      :width: 500px

4. Credentials created

   The new API credentials will be generated immediately and presented to you
   (1). You can copy the credentials right away by clicking the copy button (2).

   .. image:: ../Images/GoogleApiKeyCreated.png
      :width: 500px

5. Proceed by configuring the key

   Click on the :guilabel:`Restrict key` button (3, above) to enter the
   *Restrict and rename API key* interface.

6. Give the key a name

   Enter a name for the API key (1). We recommend giving a descriptive name,
   e.g. "pxa_dealers googleJavascriptApiKey 2021" and
   "pxa_dealers googleServerApiKey 2021" for the two keys.

   .. image:: ../Images/RestrictAndRenameApiKey.png
      :width: 500px

   .. warning::

      Please note the warning (2) about the key being unrestricted. Never use an
      unrestricted key for public websites!

7. Configure access restrictions

   Now, we need to restrict access to the key. We do this further down in the
   *Restrict and rename API key* interface. The two keys we are generating need
   different restrictions configured in the :guilabel:`Access restrictions`
   section.

   :ref:`typoscript-map-javascriptapikey`
      This is key is used by JavaScript in the web browser. It must be
      restricted to requests coming from your website. This is called
      *referrer restriction* because it uses the :mailheader:`Referrer` HTTP
      header.

      .. rst-class:: bignums

      1. Select :guilabel:`HTTP referrers (web sites)`.

      2. Click the :guilabel:`Add an item` button.

      3. Enter a domain pattern matching your website's domain. If your web
         site can be found at "https://www.dealersexample.com/", the pattern
         `*.dealersexample.com/*`.

      .. image:: ../Images/GoogleApiKeyReferrerRestriction.png
         :width: 500px

   :ref:`typoscript-map-serverkey`:
      This key is used by the server (PHP script), so we can restrict it to the
      IP addresses used by the server.

      .. note::

         Many cloud services use different *inbound* and *outbound* IP
         addresses. Make sure you use the outbound IPs.

         * `Platform.sh public IPs list <https://docs.platform.sh/development/public-ips.html>`__

      .. rst-class:: bignums

      1. Select :guilabel:`IP addresses (web servers, cron jobs, etc.)`.

      2. Click the :guilabel:`Add an item` button.

      3. Enter the IP address or IP address range in the :guilabel:`address`
         field.

      Repeat step 2 and 3 until you have added all the necessary IP addresses.

      .. image:: ../Images/GoogleApiKeyIpRestriction.png
         :width: 500px

   .. info::

      If the restriction setting is wrong, you will see an error message in one
      of two places:

      * **Server-side** restriction errors in the TYPO3 log.
      * **JavaScript** restriction errors in the browser console.

8. Enable APIs

   If this is the first time you are using this project, you'll have to enable
   the necessary APIs from the
   `API library <https://console.cloud.google.com/apis/library>`__. If they are
   already enabled, you can skip to step 9.

   These APIs are:

   * `Geocoding <https://console.cloud.google.com/apis/library/geocoding-backend.googleapis.com>`__
   * `Geolocation <https://console.cloud.google.com/apis/library/geolocation.googleapis.com>`__
   * `Maps JavaScript <https://console.cloud.google.com/apis/library/maps-backend.googleapis.com>`__
   * `Places <https://console.cloud.google.com/apis/library/places-backend.googleapis.com>`__

   .. rst-class:: bignums

   1. From within the Google Cloud Platform, select :guilabel:`Library` (1) from
      the left-hand navigation bar click this link to the
      `API library <https://console.cloud.google.com/apis/library>`__

      .. image:: ../Images/SelectLibraryFromGoogleCloudPlatform.png
         :width: 500px

   2. Use the search field (1) to search for the APIs.

      .. image:: ../Images/GoogleApiLibraryHome.png

   3. Click on the correct API in the search result. You will be taken to the
      API's detail page.

   4. Click the :guilabel:`Enable` button to enable the API.

      .. image:: ../Images/EnableGoogleApi.png

   Repeat steps 2 through 4 until you have enabled all the necessary APIs.

   When you are finished, navigate back to the *Restrict and rename API key*
   interface for the API key you're working on.

9. Select APIs to use

   Within the *Restrict and rename API key* interface, find your way to the
   :guilabel:`API restrictions` section.

   .. rst-class:: bignums

   1. Select :guilabel:`Restrict key`.

   2. Click on the :guilabel:`Select APIs` drop-down menu.

   3. Tick the following checkboxes:

      * Geocoding API
      * Geolocation API
      * Maps JavaScript API
      * Places API

   4. Click the :guilabel:`OK` button.

   5. Click the :guilabel:`Save` button.

   .. image:: ../Images/SelectGoogleApis.png


