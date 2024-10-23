# FOSSBilling-CyberPanel Server Manager V2 - BETA

> [!NOTE]  
> Tested with [FOSSBilling](https://github.com/FOSSBilling/FOSSBilling) v0.6.20,v0.6.22
>


> [!IMPORTANT]  
> The primary difference between V1.x and V2 is that V2 utilizes the /cloudAPI endpoint, which requires the server API username and token, rather than the /api endpoint with the API username and password.
> 

## Looking for V1?
V2 is currently in beta and needs further testing, if you would like to use v1.x branch you can do so at the following link: [v1.x branch](https://github.com/NerdbyteIO/FOSSBilling-CyberPanel/tree/v1.x)

## Installation

1. Download or clone the `CyberPanel.php` file into your [FOSSBilling](https://github.com/FOSSBilling/FOSSBilling) installation at `/library/Server/Manager`.
2. This version requires a CloudAPI token. Obtain it by executing the following command as the root user:

   ```bash
   mysql -e "SELECT token FROM $CYBERPANEL-DBNAME.loginSystem_administrator WHERE username='$USERNAME' \G"
   ```

   You will see output like:

   ```
   +------------------------------------------------------------------------+
    | token                                                                  |
    +------------------------------------------------------------------------+
    | Basic 1334b8120fee23669888fe4409d23a0a0fe63845a1e70811be2f91af1be000a5 |
    +------------------------------------------------------------------------+
   ```

   **Ensure to replace `$CYBERPANEL-DBNAME` and `$USERNAME` with your actual database name and CyberPanel username.**

   Alternatively, you can retrieve the token via phpMyAdmin from the `loginSystem_administrator` table. **Do not include "BASIC".** in the API Token field of FOSSBilling

## Custom Package Values

- **ACL:** Default is set to user/reseller based on the selected package; it can be overridden by specifying a custom ACL value in the package.

## Features

### Server
- Verify Connection

### Website Functions
- Create Website (includes user creation in CyberPanel)
- Change Website Package
- Suspend/Unsuspend Website

### User Functions
- Change User Password

### Limitations
- Cannot change account username or domain.
- Synchronization of accounts is not supported due to API constraints.

## Important Notes

- This community-maintained package is not affiliated with [FOSSBilling](https://github.com/FOSSBilling/FOSSBilling). Please report issues here, not on the FOSSBilling repo.
- API reseller support is limited. While creating reseller accounts is supported, the API does not allow retrieval of all domains/users for suspension actions.
- For any questions or issues, please open an issue on GitHub.
- If updates to [FOSSBilling](https://github.com/FOSSBilling/FOSSBilling) disrupt this server manager's functionality, report the issue here for updates.
- Developed using the [CyberPanel API Docs](https://cyberpanel.docs.apiary.io).

## Donate

If you find this server manager useful and wish to support further development, consider buying me a coffee! Your support is appreciated, but entirely optional.

<a href="https://www.buymeacoffee.com/jsonkenyon" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/default-orange.png" alt="Buy Me A Coffee" height="41" width="174"></a>
