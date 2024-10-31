=== PayIBAN – Plugin machtigen via TAN-code voor Woocommerce ===
Tags: SEPA direct debit, SEPA incasso, ideal, incasso, payment gateway, payment provider, woocommerce, subscriptions, incassomachtigen
Requires at least: 3.1.0
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=C9UCRMLLHEDQA
Tested up to: 4.7.0
Stable tag: trunk
License: GPLv2
Contributors: Van Stein en Groentjes

Betaaloptie voor verwerken van digitale machtigingen. Onderteken eenvoudig met TAN-code. Geschikt voor alle 34 SEPA-landen. Ook te gebruiken in combinatie met andere betaalopties.

== Description ==

This plugin offers you a payment gateway for processing SEPA Mandates and creating SEPA Direct Debits. This plugin enables you to offers your customers both single and recurring payments.
This plugin offers everyone an easy way to sell and pay for subscriptions.  Both you and your customers don’t need a credit card or paypal account.

This plugin requires a free account of PayIBAN. PayIBAN is a payment provider. 
PayIBAN core business is processing and management of online SEPA mandates and generating plug-and-play SEPA Direct Debit collection files. Should you not have a collection contract we can collect on behalf of you.



== Installation ==

=Requirements=

-	Wordpress
-	Woocommerce

=Installation=

How to install this plugin:

1.	After download locate the “payiban-sepa-direct-debit-for-woocommerce.zip” file on your computer
2.	Login into your Wordpress-site and go to Plugins > Add New to upload the new plugin.
3.	Click on Uploads in the top menu.
4.	Click Choose File and select the .zip file you had located in step 1. Press okay.
5.	Once you have your file selected, click on Install Now.
6.	Activate the plugin right after upload.

After activation you will receive an email containing your PayIBAN-credentials

7.	Login into your PayIBAN account. 
8.	Select your username and password from the API and Plugin section.
9.	Login into Wordpress-site and go to Woocommerce settings.
10.	Select Check out and go to PayIBAN.
11.	Use username and password to connect plugin with your PayIBAN account.




== Frequently Asked Questions ==

= What do I need to do after installation of this plugin? =

Register your free PayIBAN account and connect your PayIBAN account to the plugin. Connecting the plugin with your PayIBAN account ensures that your payments are converted into SEPA direct debits and SEPA Direct debit files.

= How do I activate this plugin? =

Two steps validation: Step 1. After installation of the plugin in your Word-press site you need to activate it. After registration of your PayIBAN account you need to activate the plugin by filling in the username and password from your PayIBAN account. 

= How can I test the plugin? =
In the plugin settings you can select Test-mode and see if your payments are processed. Check the Mandates section within your PayIBAN account is payments have been processed succesfully.

= What do I pay for using this plugin of conversion of payments into SEPA Direct debits?  =

The plugin is free of charge. You only pay for payments which have been converted into SEPA Direct Debits. Check our pricing [here.](https://www.payiban.com/#pricing)..
When do I pay for conversion of payments into SEPA direct debits?
Payments for conversion will automatically be charged on the 1st of each new month. The number of SEPA direct debits are being calculated and these numbers are then charged. 

= How do I collect payments? =

If you have a collection contract you will automatically receive SEPA direct debit files. You can sent these files directly to your bank for collection.

= Do I a need a collection contract? =
No, should you not have a collection contract we setup an additional account for you. This account is created on the payment platform of one of our partners. We ask additional information for onboarding. For more details please sent us an email: sales@payiban.com.

= I do not receive a TAN code when testing payIBAN =
When the testmode is active TAN codes will not be send to your phone. Fill in a fake TAN code to procede with testing the plugin.


== Screenshots ==

1. In the checkout section of Woocommerce settings you can find payIBAN listed in the payment gateway list. Press the settings button to go to the PayIBAN plugin settings.
2. In the settings screen of this plugin you can fill in your API username and password and specify some texts that are shown on checkout.
3. This is the checkout interface (depending on your theme). In three simple steps your customers can pay with their IBAN account.

== Upgrade Notice ==



== Changelog ==

= 4.2.3 =
* Added option to order per day or week.

= 4.2.2 =
* Added option to specify fixed first term price instead of discount.

= 4.2.1 =
* Small bug fixes.

= 4.2.0 =
* Added the option to specify first term discounts.

= 4.1.1 =
* Compatibility with Wordpress 5.7.x

= 3.2.3 =
* You can now specify the language of the TAN code messages. (en,nl,de,es)

= 3.2.1 =
Important bug fix.

= 3.2.0 =
Fixed translation of new features.

= 3.1.0 =
You can now specify the number of billing cycles for specific products.

= 3.0.1 =
Minor bug fixes in email template.

= 3.0.0 =
Added account creation on activation.

= 2.0.0 =
You can now use the plugin for recurrent payments with Woocommerce without the need of the subscriptions plugin!


= 1.2.1 =
* Added support for languages
* Included Dutch
* Fixes several bugs

= 1.0 =
* Start

== Translations included ==
 * English
 * Dutch (Nederlands)
