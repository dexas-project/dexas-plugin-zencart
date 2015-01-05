bitshares/zencart-plugin
=====================

# Installation

1. Copy these files into your osCommerce root directory
2. Copy Bitshares Checkout (https://github.com/sidhujag/bitsharescheckout) files into your osCommerce root directory, overwrite any existing files.

# Configuration

1. In your osCommerce admin panel under Modules > Payment, install the Bitshares module
2. Fill out all of the configuration information:
	- Verify that the module is enabled.
	- Choose a status for unpaid and paid orders (or leave the default values as
      defined).
	- Choose a sort order for displaying this payment option to visitors.
      Lowest is displayed first.
1. Fill out config.php with appropriate information and configure Bitshares Checkout
    - See the readme at https://github.com/sidhujag/bitsharescheckout

# Usage

When a user chooses the Bitshares method, they will be
presented with an order summary as the next step (prices are shown in whatever
currency they've selected for shopping). Upon confirming their order, the system
takes the user to the Bithshares Checkout.  Once payment is received, the Bithshares Checkout
will redirect the user back to the merchant website.

In your Admin control panel, you can see the orders made via Bitshares just as
you could see for any other payment mode.  The status you selected in the
configuration steps above will indicate whether the order has been paid for.  


# Support

## Bitshares Support

* [GitHub Issues](https://github.com/sidhujag/bitshares-zencart/issues)
  * Open an issue if you are having issues with this plugin.


## ZenCart Support

* [Homepage](http://www.zen-cart.com)
* [Documentation](http://www.zen-cart.com/wiki/index.php/Developers_API)
* [Support Forums](http://www.zen-cart.com/forum.php)

# Contribute

To contribute to this project, please fork and submit a pull request.

# License

The MIT License (MIT)

Copyright (c) 2011-2014 Bitshares

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
