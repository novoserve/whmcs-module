# NovoServe WHMCS Module

### Introduction

This provisioning module allows you as a reseller to offer your customers full server management.
With just one click they will be automatically logged in. Currently, this is the core functionality of this module.
For the admin convenience we also added the login ability from the WHMCS admin side, this allows you to quickly access the IPMI of your customer.

Current features:
- Autologin IPMI for the clientarea;
- Autologin IPMI for the admin side;
- Whitelabel console URL generation;

### Requirements
- WHMCS 7.x or 8.x;
- PHP 7 and the cURL extension;
- NovoServe API credentials (you can generate them in the NovoServe portal, under API Management).

### Installation

1. Upload the contents of the ZIP into your WHMCS root directory.
2. Setup a new or use an existing Product:
3. Under Module Settings, select the "NovoServe Console Module".
4. Now enter your API credentials accordingly and decide if you want whitelabel consoles (without any logo), or the default NovoServe branded.
5. Go to a service that uses this (newly) product and ensure that the Username contains a server tag (000-000). This is a requirement.

Note: Ensure that you added the IP address of your WHMCS instance to the API ACL in our portal.

### License
MIT License