.. include:: ../Includes.rst.txt

.. _install-and-setup:

=================
Install and Setup
=================

.. _deeplinking-typoscript-template:

Include the TypoScript Template for Deeplinking Support
-------------------------------------------------------

.. note::
   Deeplinking is only supported in TYPO3 v8 and higher.

.. image:: _assets/includeDeeplinkingTypoScriptTemplate.png
   :class: with-shadow

In order to enable the *Edit in CMS* deeplinking button, you must include the
static template "Siteimprove Deeplinking Tags" in your site's root template.
This will include a couple of new meta tags in your page header allowing
Siteimprove to generate the button link. (Remember to clear the cache
afterwards.)

When you run TYPO3 in *Development* context, you will have access to a second
static template called "Siteimprove Deeplinking Development Tag".Including this
static template will insert a meta tag named "editingPage" on your pages for
testing and debugging purposes. It contains the full deeplinking URL similar to
the one that will be used when you click the *Edit in CMS* deeplinking button
within the *Siteimprove Intelligence Platform*.

Examples of links
~~~~~~~~~~~~~~~~~

A deep link to a page is of the following form:

.. code-block:: none

	https://example.com/typo3/index.php?tx_siteimprove_goto=page:{page_uid}:{language_uid}

Whereas the language_uid is optional and defaults to 0. Example links could look like this:

.. code-block:: none

	https://example.com/typo3/index.php?tx_siteimprove_goto=page:42
	https://example.com/typo3/index.php?tx_siteimprove_goto=page:42:1
