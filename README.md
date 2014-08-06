paygate-rpp-opencart
====================

Opencart extension that integrates with Paygate payment gateway

Installation
====================
Note:  Compatible with OpenCart 1.4.x and 1.5.x

In order to get a PayGate Merchant ID please browse to "https://www.paygate.co.za"
Find the Application form.
Complete and save it and a PayGate Sales person will contact you with further information.

Installation:

1. Unzip the file to the root of your OpenCart installation. Make sure you adhere to the directory structure.
2. Go to http://www.yourdomain.com/{admin} - or whatever admin is named
3. Log in with Admin credentials
4. Go to Extensions >> Payments.  
5. Select "Install" next to "PayGate" extension
6. Click Edit.  
7. Enter your PayGate Merchant ID.
8. Enter your PayGate Seceret Key, it must the same with the secret key you entered on PayGate admin.
9. Set the Status to enabled.
10. Set the Completion Status to the status that you require the order to be set to if the payment succeeds.
11. Set the Denied Status to the status that you require the order to be set to if the payment is declined.
12. Set the Failed Status to the status that you require the order to be set to if the payment fails.
13. Once you have completed your testing simply enter the PayGate Merchant ID instead of the Test ID.

Done.
