.. include:: ../../Includes.txt

.. _dealers:

=======================
Creating Dealer Records
=======================

.. rst-class:: bignums-xxl

1. In the TYPO3 Backend, select the :guilabel:`List` module.

2. In the page tree, select the folder where you would like to store your
   categories.

   .. note::

      Categories are fetched from the location specified in the
      :ref:`extensionconfig-categoriesrestriction`.

3. In the DocHeader, click on the :guilabel:`+` (*Create new record*) button.

   .. image:: Images/PageTreeAndCreateRecordButton.png

   The *New record* screen will appear, showing a tree with new record types.

4. In the record tree, click on *Dealer* (under *Pxa Dealers*).

   .. image:: Images/SelectCreateDealerRecord.png
      :width: 500px

   You will now see the *Create new Dealer* form.

5. Fill in the necessary fields in the *Create new Dealer* form.

   Here's a description of the record's fields apart from those you should know
   from other TYPO3 records.

   **General**

   Name (required)
      The unique name of the Dealer. E.g. "Big Machine Parts, Inc.", "Small
      Stuff (Smalltown)", or "Smalltown Mall". Use the business name if your
      Dealers are single businesses, but add location if your Dealers are parts
      of a chain. If all Dealers are part of the same chain, you can also use
      the location name.

   Logo
      Upload a logo for the Dealer. A well-know logo is easier to spot in a
      list of Dealers.

   **Coordinates**

   Show street view
      When enabled, a Google Street View image of the Dealer's address will be
      displayed in the map marker's information pop-up.

   Country
      Select the country where the Dealer is located.

   Address (required)
      The street address of the Dealer. E.g. "123 Main street"

   City (required)
      The postal city or town where the Dealer is located. E.g. "Smallville"

   Zipcode
      The zip or postal code of the city or town where the Dealer is located.
      E.g. "01234"

   Google maps position
      This Google Map shows the location of the Dealer as it will be displayed
      on the map in the frontend. Click the
      :guilabel:`Update marker position according to chosen country and address fields`
      button to update the marker location when you have added or changed the
      Dealer's address information.

   Latitude
      The latitude of the Dealer's exact coordinates. The value must be entered
      as Decimal Degrees, a decimal number between -90.00 and 90.00, with with
      decimal separator `.`. E.g. "55.600568"

   Longditude
      The longditude of the Dealer's exact coordinates. The value must be
      entered as Decimal Degrees, a decimal number between -90.00 and 90.00,
      with decimal separator `.`. E.g. "12.99902"

   **Additional fields**

   Phone
      The Dealer's phone number. If you display Dealers in multiple countries,
      it is a good idea to include the country code in a standard format, such
      as "+46 123 456 789".

   Link
      For example a link to use for a call-to-action button.

   Website
      A link to the Dealer's website.

   Email
      The Dealer's contact email address.

   Description
      A free-text field that can be used to describe the Dealer in further
      detail or add information that is not covered by the standard fields.

   **Categories**

   Categories
      Select one or more categories that will help the user filter Dealers in
      the frontend.

6. Click the :guilabel:`Save` button to save your Dealer.

Repeat these steps until you have added the Dealer records you need.
