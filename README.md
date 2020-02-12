Opencart Facebook Login 3.x
---------------------------------
Description:
Let your customers login and checkout using Facebook account. This module uses your own created Facebook API for your own website. In case, you don't have website API for Facebook yet, then below you can find instructions how to register your own API.
---------------------------------
Function:
- Login or Register user from Facebook.
- The login it's present on register, login and checkout page.
- Add eventi if users login
---------------------------------
Installation
1. Login to your website OpenCart admin panel
2. Go to Extensions > Extension installer and upload ocmod package
3. Go to Extensions > Extensions > Module and active Facebook Login and insert request info.
4. Go to Extensions > Modifications and click Refresh

To create and connect Facebook login with your API follow these simple steps:
1. Create your Facebook App (more info: https://developers.facebook.com/docs/apps/register).
2. After that you got your own App ID and App Secret
3. Go to your OpenCart shop admin area > Extension > Modules > Facebook Login.
4. Install and configure your Facebook module insert Facebook App ID Location.
5. Connect your facebook login button with the layout.

----------------------------------------------
In case you need help or you got any idea how to improve this module pls add pull request.
In develope now:
- New system comunication core async
- Add module for insert on layout
- Add Button logout facebook
----------------------------------------------
Change log
- V41 -- Dev -> Add new sys core for connect to facebook
- V40 -- Added email block input if facebook profile it's not connected with email or connected with telephone number or privacy settings blocks email api. Add note for enable pop-up.
- V39 -- Fix email no permission
- V38 -- Relase module