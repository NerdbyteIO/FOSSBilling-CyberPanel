<?php

use Symfony\Component\HttpClient\Response\CurlResponse;

/**
 * Copyright 2022-2024 FOSSBilling
 * Copyright 2011-2021 BoxBilling, Inc.
 * SPDX-License-Identifier: Apache-2.0.
 *
 * @copyright FOSSBilling (https://www.fossbilling.org)
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 */
class Server_Manager_CyberPanel extends Server_Manager
{
    /**
     * Returns server manager parameters.
     *
     * @return array returns an array with the label of the server manager
     */
    public static function getForm(): array
    {
        return [
            'label' => 'CyberPanel',
            'form' => [
                'credentials' => [
                    'fields' => [
                        [
                            'name' => 'username',
                            'type' => 'text',
                            'label' => 'Username',
                            'placeholder' => 'Admin',
                            'required' => true,
                        ],
                        [
                            'name' => 'accesshash',
                            'type' => 'text',
                            'label' => 'API key',
                            'placeholder' => 'API key that was generated.',
                            'required' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return void
     */
    public function init(): void
    {
        $this->_config['port'] = empty($this->_config['port']) ? '8090' : $this->_config['port'];
    }

    /**
     * Returns the URL for reseller account management.
     *
     * @param  Server_Account|null  $account  the account for which the URL is generated
     *
     * @return string returns the URL as a string
     */
    public function getResellerLoginUrl(Server_Account $account = null): string
    {
        return $this->getLoginUrl();
    }

    /**
     * Returns the URL for account management.
     *
     * @param  Server_Account|null  $account  the account for which the URL is generated
     *
     * @return string returns the URL as a string
     */
    public function getLoginUrl(Server_Account $account = null): string
    {
        return 'https://'.$this->_config['host'].':'.$this->_config['port'];
    }

    /**
     * Tests the connection to the server.
     *
     * @return bool returns true if the connection is successful
     */
    public function testConnection(): bool
    {
        $request = $this->request('fetchUsers', []);

        $response = json_decode($request->getContent());

        if (!$response->status) {
            throw new Server_Exception($response->error_message);
        }

        return true;
    }

    /**
     * @param  string  $controller
     * @param  array  $params
     * @return CurlResponse
     */
    private function request(string $controller, array $params = []): Symfony\Component\HttpClient\Response\CurlResponse
    {
        $client = $this->getHttpClient()
            ->withOptions([
                'headers' => [
                    'Authorization' => 'Basic '.$this->_config['accesshash'],
                ],
                'verify_peer' => false,
                'verify_host' => false,
                'timeout' => 60
            ]);

        $response = $client->request(
            'POST',
            'https://'.$this->_config['host'].':'.$this->_config['port'].'/cloudAPI/',
            [
                'json' => array_merge([
                    'controller' => $controller,
                    'serverUserName' => $this->_config['username']
                ],
                    $params),
            ]
        );

        return $response;
    }


    /**
     * Synchronizes the account with the server.
     *
     * @param  Server_Account  $account  the account to be synchronized
     *
     * @return Server_Account returns the synchronized account
     */
    public function synchronizeAccount(Server_Account $account): Server_Account
    {
        throw new Server_Exception(':type: does not support :action:',
            [':type:' => 'Cyberpanel', ':action:' => __trans('account synchronization')]);
    }

    /**
     * Creates a new account on the server.
     *
     * @param  Server_Account  $account  the account to be created
     *
     * @return bool returns true if the account is successfully created
     */
    public function createAccount(Server_Account $account): bool
    {
        $client = $account->getClient();
        $hostingPackage = $account->getPackage();
        /**
         *
         * Does the current package that is being sent to CyberPanel exist?
         * if not lets create it before making the website.
         * See GitHub issues #10, and #11
         */
        $packageName = $this->doesPackageExist($hostingPackage);

        /**
         *
         * Create the user.
         */
        $this->createUserAccount($account);

        /**
         *
         * Creating the website and assign it to the newly created user.
         */
        $request = $this->request('submitWebsiteCreation', [
            'domainName' => $account->getDomain(),
            'adminEmail' => $client->getEmail(),
            'package' => $packageName,
            'websiteOwner' => $account->getUsername(),
            'ownerPassword' => $account->getPassword(),
            'phpSelection' => 'PHP 8.3',
            'ssl' => 0,
            'dkimCheck' => 0,
            'openBasedir' => 0
        ]);

        $response = json_decode($request->getContent());

        /**
         *
         * Check if the request was successful.
         */
        if (!$response->status) {
            throw new Server_Exception($response->error_message);
        }

        return true;
    }


    /**
     * @param  Server_Package  $hostingPackage
     * @return string
     */
    private function doesPackageExist(Server_Package $hostingPackage): string
    {
        /**
         *
         * We need to remove the space in the package name if there
         * is one. This is because CyberPanel will do it; so we
         * need to match it.
         */
        $packageName = str_replace(' ', '', $hostingPackage->getName());

        $request = $this->request('fetchPackages', []);
        $response = json_decode($request->getContent());
        $packagesArray = json_decode($response->data, true);

        /**
         *
         * Check if the package already exists. If the package
         * exists we return the name of the package. If it doesn't
         * exist then we create a new package.
         */
        for ($i = 0; $i < count($packagesArray); $i++) {
            if ($packagesArray[$i]['packageName'] == $this->_config['username']."_".$packageName) {
                return $packagesArray[$i]['packageName'];
            }
        }

        $request = $this->request('submitPackage', [
            'packageName' => $packageName,
            'diskSpace' => $hostingPackage->getQuota(),
            'bandwidth' => $hostingPackage->getBandwidth(),
            'dataBases' => $hostingPackage->getMaxSql(),
            'ftpAccounts' => $hostingPackage->getMaxFtp(),
            'emails' => $hostingPackage->getMaxPop(),
            'allowedDomains' => $hostingPackage->getMaxDomains(),
        ]);

        $response = json_decode($request->getContent());

        if (!$response->status) {
            throw new Server_Exception($response->error_message);
        }

        return $this->_config['username']."_".$packageName;
    }

    /**
     * @param  Server_Account  $account
     * @return array
     */
    private function getName(Server_Account $account): array
    {
        $fullName = $account->getClient()->getFullName();

        $parts = explode(' ', $fullName, 2);

        return $parts;
    }

    /**
     * @param  Server_Account  $account
     * @return bool
     */
    private function createUserAccount(Server_Account $account): bool
    {
        /**
         *
         * Check if the user is reseller or not
         * or if they have a custom ACL.
         */
        if ($account->getReseller()) {
            $acl = $account->getPackage()->getCustomValue('ACL') ?? 'reseller';
        } else {
            $acl = $account->getPackage()->getCustomValue('ACL') ?? 'user';
        }

        list($firstName, $lastName) = $this->getName($account);

        $request = $this->request('submitUserCreation', [
            'firstName' => $firstName,
            'lastName' => $lastName ?: 'Unknown',
            'email' => $account->getClient()->getEmail(),
            'userName' => $account->getUsername(),
            'password' => $account->getPassword(),
            'websitesLimit' => $account->getPackage()->getMaxDomains(),
            'selectedACL' => $acl,
            'securityLevel' => 'HIGH'
        ]);

        $response = json_decode($request->getContent());

        if (!$response->status) {
            throw new Server_Exception($response->error_message);
        }

        return true;
    }

    /**
     * Suspends an account on the server.
     *
     * @param  Server_Account  $account  the account to be suspended
     *
     * @return bool returns true if the account is successfully suspended
     */
    public function suspendAccount(Server_Account $account): bool
    {
        $request = $this->request('submitWebsiteStatus', [
            'websiteName' => $account->getDomain(),
            'state' => 'Suspend'
        ]);

        $response = json_decode($request->getContent());

        if (!$response->websiteStatus) {
            throw new Server_Exception($response->error_message);
        }

        return true;
    }

    /**
     * Unsuspends an account on the server.
     *
     * @param  Server_Account  $account  the account to be unsuspended
     *
     * @return bool returns true if the account is successfully unsuspended
     */
    public function unsuspendAccount(Server_Account $account): bool
    {
        $request = $this->request('submitWebsiteStatus', [
            'websiteName' => $account->getDomain(),
            'state' => 'Activate'
        ]);

        $response = json_decode($request->getContent());

        if (!$response->websiteStatus) {
            throw new Server_Exception($response->error_message);
        }

        return true;
    }

    /**
     * Cancels an account on the server.
     *
     * @param  Server_Account  $account  the account to be cancelled
     *
     * @return bool returns true if the account is successfully cancelled
     */
    public function cancelAccount(Server_Account $account): bool
    {
        $request = $this->request('submitWebsiteDeletion', [
            'websiteName' => $account->getDomain()
        ]);

        $response = json_decode($request->getContent());

        if (!$response->status) {
            throw new Server_Exception($response->error_message);
        }

        /**
         *
         * We sleep for 2 seconds to allow the first command
         * to finish what it needs to do.
         */
        sleep(2);

        /**
         *
         * Lets continue and delete the client now.
         */
        $request = $this->request('submitUserDeletion', [
            'accountUsername' => $account->getUsername()
        ]);

        $response = json_decode($request->getContent());

        if (!$response->status) {
            throw new Server_Exception($response->error_message);
        }

        return true;
    }

    /**
     * Changes the package of an account on the server.
     *
     * @param  Server_Account  $account  the account for which the package is to be changed
     * @param  Server_Package  $package  the new package
     *
     * @return bool returns true if the package is successfully changed
     */
    public function changeAccountPackage(Server_Account $account, Server_Package $package): bool
    {
        throw new Server_Exception(':type: does not support :action:',
            [':type:' => 'Cyberpanel', ':action:' => __trans('package changes')]);

                /*
                 * We have to disable this function for now, this endpoint
                 * doesn't seem to exist in the cloudAPI. A ticket would need
                 * to be made to reach out to them, about it.
                $packageName = $this->doesPackageExist($package);

                $request = $this->request('changePackageAPI', [
                    'websiteName' => $account->getDomain(),
                    'packageName' => $packageName,
                ]);

                $response = json_decode($request->getContent());

                if (!$response->status) {
                    throw new Server_Exception($response->error_message);
                }

                return true;
                */
    }

    /**
     * Changes the username of an account on the server.
     *
     * @param  Server_Account  $account  the account for which the username is to be changed
     * @param  string  $newUsername  the new username
     *
     * @return bool returns true if the username is successfully changed
     */
    public function changeAccountUsername(Server_Account $account, string $newUsername): bool
    {
        throw new Server_Exception(':type: does not support :action:',
            [':type:' => 'Cyberpanel', ':action:' => __trans('username changes')]);
    }

    /**
     * Changes the domain of an account on the server.
     *
     * @param  Server_Account  $account  the account for which the domain is to be changed
     * @param  string  $newDomain  the new domain
     *
     * @return bool returns true if the domain is successfully changed
     */
    public function changeAccountDomain(Server_Account $account, string $newDomain): bool
    {
        throw new Server_Exception(':type: does not support :action:',
            [':type:' => 'Cyberpanel', ':action:' => __trans('changing the account domain')]);
    }

    /**
     * Changes the password of an account on the server.
     *
     * @param  Server_Account  $account  the account for which the password is to be changed
     * @param  string  $newPassword  the new password
     *
     * @return bool returns true if the password is successfully changed
     */
    public function changeAccountPassword(Server_Account $account, string $newPassword): bool
    {
        $client = $account->getClient();

        list($firstName, $lastName) = $this->GetName($account);

        $request = $this->request('saveModificationsUser', [
            'accountUsername' => $account->getUsername(),
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $client->getEmail(),
            'passwordByPass' => $newPassword,
        ]);

        $response = json_decode($request->getContent());

        if (!$response->status) {
            throw new Server_Exception($response->error_message);
        }

        return true;
    }

    /**
     * Changes the IP of an account on the server.
     *
     * @param  Server_Account  $account  the account for which the IP is to be changed
     * @param  string  $newIp  the new IP
     *
     * @return bool returns true if the IP is successfully changed
     */
    public function changeAccountIp(Server_Account $account, string $newIp): bool
    {
        throw new Server_Exception(':type: does not support :action:',
            [':type:' => 'Cyberpanel', ':action:' => __trans('changing the account IP')]);
    }
}
