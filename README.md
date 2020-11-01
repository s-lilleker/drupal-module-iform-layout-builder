# IForm layout builder

# Overview

This module allows custom data entry forms for Indicia wildlife recording to be built using the
Layout Builder functionality introduced in Drupal 8. This provides a simpler way for editors to
build custom survey forms than the existing customisable dynamic forms supported by the IForm
module.

## Installation

Set up the Drupal private file system (file_private_path in settings.php).

Create an RSA private/public key pair:
```bash
$ openssl genrsa -des3 -out rsa_private.pem 2048
$ openssl rsa -in rsa_private.pem -pubout > rsa_public.pub
```

Save rsa_private.pem in the private file system folder.

The contents of rsa_public.key needs to be saved into the website registration on the warehouse.
