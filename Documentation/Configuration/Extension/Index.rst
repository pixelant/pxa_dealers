.. include:: ../../Includes.txt

.. _extensionconfig:

=======================
Extension Configuration
=======================

The extension configuratoin can be found in :guilabel:`Admin Tools` ->
:guilabel:`Settings` -> :guilabel:`Extension Configuration`.

.. _extensionconfig-categoriesrestriction:

Categories PID restriction
==========================

:aspect:`Property`
   categoriesRestriction

:aspect:`Data type`
   :ref:`t3tsref:data-type-string`

:aspect:`Description`
   What restriction to use when fetching categories to display when editing
   Dealer records in the backend.

   * **None:** No restriction. (`none`)
   * **Pages TSconfig ID list:** A list of page IDs defined in Page TS Config.
      (`page_tsconfig_idlist`)
     :typoscript:`TCEFORM.tx_pxadealers_domain_model_dealer.categories.PAGE_TSCONFIG_IDLIST`
   * **Current pid:** On the current page. (`current_pid`)
   * **Siteroot:** In the root of the website.(`siteroot`)
