UI Components
=============
Discovery and rendering of component types.

Almost entirely based on [POC](https://www.drupal.org/files/issues/component_PoC-individual_commits-2702061-2-do-not-test.patch) by Wim Leers.

Features of the module include:

* Discovery of components:
  * in Drupal core modules and themes (although none are available yet)
  * in custom modules and themes
  * in a folder such as ui/components created from an external pattern library
* A file to define a component
  * variables
  * assets
  * examples
* Register templates in the Drupal theme registry
* Map component variables to theme variables
* Process a '#component' section in the render array
* Build a style guide from the component definition and template files
