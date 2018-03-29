#!/usr/bin/env php
<?php
/**
 * Copyright 2015 Akamai Technologies, Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 *
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * Very basic script demonstrating diagnostic tools functionality
 */
require_once __DIR__ . '/cli/init.php';

$client = Akamai\Open\EdgeGrid\Client::createFromEdgeRcFile($configSection, $configFile);

# Request locations that support the diagnostic-tools
echo "Getting subscriptions.\n";

try {
    $response = $client->get('/firewall-rules-manager/v1/subscriptions');
    if ($response) {
        $result = json_decode($response->getBody(), true);
    }
    var_export($result);

} catch (GuzzleHttp\Exception\ClientException $e) {
    echo "An error occurred: " .$e->getMessage(). "\n";
    echo "Please try again with --debug or --verbose flags.\n";
}
