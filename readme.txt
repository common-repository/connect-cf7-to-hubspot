=== Connect CF7 to HubSpot ===
Contributors: procoders
Tags: hubspot, contact form 7
Requires at least: 5.3
Tested up to: 6.5.3
Stable tag: 1.1.6
Requires PHP: 8.0
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://procoders.tech/

Seamlessly Connect CF7 to HubSpot to automate your lead management process.

== Description ==

Connect CF7 to HubSpot by ProCoders is a powerful plugin that connects WordPress Contact Form 7 directly with your HubSpot account, automating your workflow and enhancing lead management. With this plugin, every submission from Contact Form 7 is instantly and securely synced with HubSpot, allowing you to manage contacts, track submissions, and engage with your audience without manual data entry.

**Third-Party Service Integration**:
This plugin integrates with HubSpot, a third-party service, to manage and sync data from Contact Form 7 submissions. By using this plugin, data collected through Contact Form 7 will be sent to HubSpot for processing and storage. Please be aware that by using this plugin, you agree to the terms of service and privacy policy of HubSpot.
This plugin interacts with the HubSpot service and sends data to it. Specifically, our plugin sends requests to the following URLs:
- https://api.hubapi.com
- https://api.hsforms.com

Please read HubSpot's Privacy Policy carefully to understand what data is collected and how it is used.
HubSpot's Privacy Policy: https://legal.hubspot.com/privacy-policy
HubSpot Terms of Service: View Terms - https://legal.hubspot.com/terms-of-service

**Features include:**
- Easy integration with HubSpot’s Lead Capture Forms, Contacts, and Deals.
- Automated syncing of Contact Form 7 submissions to HubSpot.
- Support for custom fields in HubSpot, allowing for detailed data capture.
- Intuitive mapping of form fields to HubSpot properties.
- Utilization of the latest HubSpot API for enhanced security and reliability.

== Description ==

Connect CF7 to HubSpot by ProCoders is a powerful plugin that connects WordPress Contact Form 7 directly with your HubSpot account, automating your workflow and enhancing lead management. With this plugin, every submission from Contact Form 7 is instantly and securely synced with HubSpot, allowing you to manage contacts, track submissions, and engage with your audience without manual data entry.

**Third-Party Service Integration**:
This plugin integrates with HubSpot, a third-party service, to manage and sync data from Contact Form 7 submissions. By using this plugin, data collected through Contact Form 7 will be sent to HubSpot for processing and storage. Please be aware that by using this plugin, you agree to the terms of service and privacy policy of HubSpot.
Specifically, our plugin sends requests to the following URLs:
- https://api.hubapi.com
- https://api.hsforms.com
- HubSpot's Privacy Policy: https://legal.hubspot.com/privacy-policy
- HubSpot Terms of Service: View Terms - https://legal.hubspot.com/terms-of-service

**Features include:**
- Easy integration with HubSpot’s Lead Capture Forms, Contacts, and Deals.
- Automated syncing of Contact Form 7 submissions to HubSpot.
- Support for custom fields in HubSpot, allowing for detailed data capture.
- Intuitive mapping of form fields to HubSpot properties.
- Utilization of the latest HubSpot API for enhanced security and reliability.

== Screenshots ==

1. On this page, you can activate the integration, filter fields by module, and specify which fields should correspond to CF7 fields.
2. On this page, we see a list of available CF7 forms, with forms actively integrated with HubSpot highlighted in green and inactive ones in red. To modify the settings of a form, click on the pencil icon.
3. On the settings page, you can add your HubSpot token following the instructions, as well as configure email notifications for API errors.
4. On this page, you can view error or notice outputs that occur when plugin is accessing HubSpot API.

== Installation ==

1. Download the plugin from WordPress.org and unzip it.
2. Upload the 'connect-cf7-hubspot' folder to your '/wp-content/plugins/' directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Navigate to the plugin settings page and follow the on-screen instructions to connect your HubSpot account.

**For WordPress Multisite:**
1. Upload and install the plugin zip file via 'Network Admin' -> 'Plugins' -> 'Add New'.
2. Do not network-activate. Instead, activate the plugin on a per-site basis for precise control.

== Frequently Asked Questions ==
= Could you answer to some question I have about your plugin? =
For more detailed assistance or any additional queries, please feel free to contact us through
our website: https://procoders.tech/contacts/
= Is Contact Form 7 HubSpot integration easy with your plugin? =
Yes, our plugin makes it incredibly easy to integrate Contact Form 7 with HubSpot. We've designed it to be
user-friendly and streamlined, allowing you to seamlessly sync your
form submissions with your HubSpot account without any hassle.
= How do I obtain a HubSpot Access Token? =
To obtain a HubSpot Access Token, follow these steps:
- Log in to your HubSpot account as an administrator.
- Go to "Settings" -> "Integrations" -> "Private apps."
- Click on the "Create a private app" button.
- In the "Basic Info" tab, add an app name and description.
- In the "Scopes" tab, select the following "CRM" and "Standard" scopes: crm.objects.contacts,
crm.objects.deals, crm.schemas.contacts, crm.schemas.deals.
- Under "files," check the "Request" checkbox.
- Under "forms," check the "Request" checkbox.
- Click on the "Create app" button.
- Copy the generated access token for use in our plugin.
=Can I use this plugin to sync contacts to a specific HubSpot pipeline? =
Yes, during the setup process of our plugin, you'll have the option to choose which pipeline your contacts should be synced with in HubSpot. This allows you to integrate your form submissions seamlessly into your existing sales or marketing workflows within the HubSpot platform.
= How can I contribute to the plugin? =
We appreciate your interest in contributing to our plugin! If you'd like to get involved, whether it's by reporting bugs, suggesting new features, or proposing enhancements, please reach out to us at hello@procoders.tech. We value community input and are always looking for ways to improve our products.
= What is Contact Form 7? =
Contact Form 7 is a popular and highly customizable WordPress plugin that allows you to create and manage various types of contact forms on your website. It offers a wide range of features and integrations, making it a versatile solution for collecting user data and facilitating communication.
= Is Contact Form 7 GDPR compliant? =
Yes, Contact Form 7 is designed to be GDPR (General Data Protection Regulation) compliant. It includes features and settings that help website owners comply with GDPR requirements, such as the ability to obtain explicit consent from users before collecting their data and providing options for data retention and erasure.
= Is HubSpot a CRM or marketing tool? =
HubSpot is a comprehensive platform that offers both CRM (Customer Relationship Management) and marketing tools. While it started as a marketing automation solution, HubSpot has evolved into an all-in-one suite that combines CRM capabilities with marketing, sales, and customer service functionalities.
= Can I use this plugin to sync contacts to a specific HubSpot pipeline? =
Yes, during the setup process, you can choose which pipeline your contacts should be synced with in HubSpot.
= HS plugin is "splitting" a form submission into two in HubSpot =
Try to disable "Collect data from website forms" in "Non-HubSpot forms" options
== Changelog ==

= 1.1.6 =
- Update FAQ

= 1.1.4 =
- Add German and Hebrew languages

= 1.1.4 =
- Add screenshots section and update README format

= 1.1.3 =
- Fix versioning

= 1.1.2 =
- Fix form submission

= 1.1.1 =
- Fix tickets

= 1.0.0 =
- Initial release. Offers seamless integration between Contact Form 7 and HubSpot for efficient lead management.

== Upgrade Notice ==

= 1.0.0 =
HubSpot is a comprehensive platform that offers both CRM (Customer Relationship Management) and
marketing tools. While it started as a marketing automation solution, HubSpot has evolved into an all-in-one
suite that combines CRM capabilities with marketing, sales, and customer service functionalities.


Blockquote:
> Enhance your WordPress site with HubSpot & Contact Form 7 Integration.
