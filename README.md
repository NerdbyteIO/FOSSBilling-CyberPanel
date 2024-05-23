# FOSSBilling-Cyberpanel Server Manager

> [!NOTE]  
> Tested with [FOSSBilling](https://github.com/FOSSBilling/FOSSBilling) v0.6.20
> 

## Installation

- Download or git clone the CyberPanel.php file to your [FOSSBilling](https://github.com/FOSSBilling/FOSSBilling) installation at the following location: /library/Server/Manager

## Custom Package Values

ACL - By default this is set to user, but you may override it in your package by setting a ACL custom value in the package.


## Features
#### Server
- Verify Connection

#### Website Functions
- Create Website (This will also create the user in CyberPanel)
- Change Website Package
- Suspend/Un-suspend Website

#### User Functions
- Change User Password

#### Things The Don't Work Due To Lack Of API
- Changing Account Username
- Changing Account Domain
- Synchronizing Accounts

## Important Notes

- This community-maintained package isn't affiliated with [FOSSBilling](https://github.com/FOSSBilling/FOSSBilling). Please report issues here rather than on the [FOSSBilling](https://github.com/FOSSBilling/FOSSBilling) repo.
- Reseller support in the API is limited.  It does support creating Reseller accounts, though the API doesn't seem to provide a way to get all the domains/users hosted by the Reseller to suspend/un-suspend them. I'm not really sure if this happens if the reseller's website get suspended. 
- For questions, concerns, or issues with this server manager, please open an issue on GitHub.
- If [FOSSBilling](https://github.com/FOSSBilling/FOSSBilling) updates and breaks this server manager, report the issue here, and I'll update it to work with the latest version.
- Created using these API docs: [CyberPanel API Docs](https://cyberpanel.docs.apiary.io)


## Donate
If you're finding value in this server manager and feel like buying me a coffee, that's awesome! Your support is appreciated and helps fuel further development. However, there's absolutely no pressure. I'm genuinely rewarded knowing people find my work useful, though I do have a soft spot for coffee! ☕️

 <a href="https://www.buymeacoffee.com/jsonkenyon" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/default-orange.png" alt="Buy Me A Coffee" height="41" width="174"></a>


