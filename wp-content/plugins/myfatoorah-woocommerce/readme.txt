=== MyFatoorah - WooCommerce ===
Contributors: myfatoorah,  nermeenshoman, rashasaeed
Tags: myfatoorah, my fatoorah, payment, woo, woo commerce
Requires at least: 5.9
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 2.2.11
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

MyFatoorah Payment Gateway for WooCommerce. Integrated with MyFatoorah DHL/Aramex Shipping Methods.


== Description ==
= Introduction =
MyFatoorah is a flexible and open-source payment gateway built on the WooCommerce e-commerce solution. Sell anything, anywhere, and make your way.

MyFatoorah offers a seamless business experience by offering a technology put together by our tech team. This enables smooth business operations involving sales activity, product invoicing, shipping, and payment processing. MyFatoorah invoicing and payment gateway solution trigger your business to greater success at all levels in the new age world of commerce. Leverage your sales and payments at all e-commerce platforms (ERPs, CRMs, CMSs) with transparent and slick applications that are well-integrated into social media and telecom services. For every closing sale click, you make a business function gets done for you, along with generating factual reports and statistics to fine-tune your business plan with no-barrier low cost.

Our technology experts have designed the best GCC E-commerce solutions for the native financial instruments (Debit Cards, Credit Cards, etc.) supporting online sales and payments, for events, shopping, mall, and associated services.

= Payment Gateways =
MyFatoorah plugin supports off-site and on-site payments. If you need more payment options, you can contact the MyFatoorah support team to activate payment gateways in your account.

MyFatoorah supports below payment gateways:
* KNET
* VISA/MASTER
* MADA
* Benefit
* Qatar Debit Cards
* UAE Debit Cards
* AMEX
* Apple Pay
* KFast
* STC Pay
* Mezza
* Orange Cash
* Oman Net

= Shipping Methods =
You need to contact the MyFatoorah account manager or sales representative person to activate the shipping feature in your account.
MyFatoorah supports the below Shipping methods,
* DHL
* Aramex


== Installation ==
Before you start, you need to create a new account on the [MyFatoorah portal website](https://portal.myfatoorah.com). Then, contact [support@myfatoorah.com](mailto:support@myfatoorah.com) to activate it by an account manager.

After that, follow the installation steps in [this link](https://myfatoorah.readme.io/docs/woocommerce).

= Demo configuration =
1. You can use the demo token mentioned [here](https://myfatoorah.readme.io/docs/demo-information).
2. Make sure the test mode checkbox is enabled.
3. You can use one of [the test cards](https://myfatoorah.readme.io/docs/test-cards).

= Live Configuration =
1. You can use the live token mentioned in [this link](https://myfatoorah.readme.io/docs/live-token).
2. Make sure test mode is disabled.

= Embedded Configuration =
You need to contact the MyFatoorah account manager or sales representative person to check the availability of this feature in your country.

= Shipping Configuration =
1. Contact the MyFatoorah account manager or sales representative person to activate the shipping feature in your account.
2. Add a valid address in your MyFatoorah account from update profile → Shipping configuration details. [For more details](https://myfatoorah.readme.io/docs/shipping-information).
3. All products should have dimensions and weight.
4. Follow the shipping configuration [guide](https://myfatoorah.readme.io/docs/woocommerce-shipping).


== Frequently Asked Questions ==
= What's the difference between the **MyFatoorah - Cards** and **MyFatoorah - Embedded** payment methods? =
* **MyFatoorah - Cards**: This uses the default gateways that were enabled in your portal account. You should enable only this payment method. 
* **MyFatoorah - Embedded**: This is used only if you have any gateway that has the direct payment feature in it at your portal account.

= What is the difference between the List Payment Options? = 
* **MyFatoorah Invoice Page (Redirect):** It will redirect the buyer to the MyFatoorah invoice page that contains a list of all payment gateways with their service charges.
* **List All Enabled Gateways in Checkout Page:** It will list the payment gateways inside your website and then will redirect the buyer straightway to the payment gateway page.

= What is the Single Gateway View? = 
Select this option if you enabled only one gateway in your MyFatoorah portal account, and you need to display a custom title/icon instead of the default title/icon.

= What is the Webhook Secret Key? = 
This option enables triggering events each time the order status changes at the MyFatoorah portal side. This feature recovers the lost orders due to connection loss or delay called back. Kindly refer to the https://myfatoorah.readme.io/docs/woocommerce-webhook.


== Screenshots ==
1- New Design Feature allows your customer to see all available payment methods and go direct to the payment page, screenshot-1.png.
2. This option redirects your customer to see the invoice of their purchase before the payment process, screenshot-2.png.
3- List the available payment methods in a classic way, screenshot-3.png.
4. Embed your payment form into your checkout page, screenshot-4.png.
5. List MyFatoorah Shipping rates, screenshot-4.png.


== Changelog ==
= 2.2.11 (2025-02-17) =
- Align payment flow with WooCommerce Blocks lifecycle

See [changelog.txt](https://plugins.svn.wordpress.org/myfatoorah-woocommerce/trunk/changelog.txt) for older logs.


== Upgrade Notice ==
= 2.2.11 =
The old design and the MyFatoorah Embedded payment is deprecated and will be removed soon. Please, use MyFatoorah's new design ONLY.
