# IForm layout builder

# Overview

This module allows custom data entry forms for Indicia wildlife recording to be built using the
Layout Builder functionality introduced in Drupal 8. This provides a simpler way for editors to
build custom survey forms than the existing customisable dynamic forms supported by the IForm
module.

As well as being entirely driven by the Drupal Layout Builder user interface, a key difference with
other methods of configuring Indicia recording forms is the server-side configuration of the survey
dataset and all custom attributes is all automated so there is no need to log into the warehouse
when creating forms.

## Installation

Install the IForm module in Drupal as usual then configure it to connect to your website
registration on the warehouse. Ensure that you've selected a master checklist on the configuration
pointing to a warehouse species list containing all available taxa. Then install the
iform_layout_builder module.

Set up the Drupal private file system (file_private_path in settings.php).

Create an RSA private/public key pair:
```bash
$ openssl genrsa -des3 -out rsa_private.pem 2048
$ openssl rsa -in rsa_private.pem -pubout > rsa_public.pub
```
Or on Windows:
```bash
winpty openssl genpkey -algorithm RSA -out rsa_private.pem -pkeyopt rsa_keygen_bits:2048
winpty openssl rsa -pubout -in rsa_private.pem -out rsa_public.pem
```


Save rsa_private.pem in the private file system folder.

The contents of rsa_public.key needs to be saved into the website registration on the warehouse.

Also on the website registration on the warehouse, ensure that the website URL is set correctly,
e.g. https://www.example.com/

## Getting Started

Once installed:

Before proceeding, please familiarise yourself with the Drupal Layout Builder module, added in
Drupal 8.5: https://www.drupal.org/docs/8/core/modules/layout-builder/layout-builder-overview.

* Click Content > Add Content > Indicia layout builder form.
* Enter a title for your form and ensure Survey Dataset is set to "-Create a new survey dataset-".
* Set form type to "Enter a single record".
* Click "Save". You should now have a basic recording form but with no method of inputting a
  species. Click the Layout tab to change the form.
* Click "Add block" in the section you would like to add the species input control to and in the
  "Choose a block" sidebar that appears, select "Single species". Save the block.
* Save the layout and you now have a very basic recording form.

Custom list of species can be configured using the scratchpad editing prebuilt forms.