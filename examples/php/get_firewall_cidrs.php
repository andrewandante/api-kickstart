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

$singleIPs = [];
$allIPs = [];

# Request locations that support the diagnostic-tools
echo "Getting firewall cidrs.\n";

try {
    $response = $client->get('/firewall-rules-manager/v1/cidr-blocks');
    if ($response) {
        $addResult = json_decode($response->getBody(), true);
        foreach ($addResult as $cidrblock) {
            if ($cidrblock['lastAction'] != 'delete'
                && $cidrblock['serviceName'] == "CCUAPI"
            ) {
                $allIPs[] = $cidrblock['cidr'] . $cidrblock['cidrMask'];
            }
        }
    }
} catch (GuzzleHttp\Exception\ClientException $e) {
    echo "An error occurred: " .$e->getMessage(). "\n";
    echo "Please try again with --debug or --verbose flags.\n";
}
echo "Getting maps.\n";

var_export(['CCUPAI'=> count($allIPs)]);


try {
    $response = $client->get('/siteshield/v1/maps');
    if ($response) {
        $addResult = json_decode($response->getBody(), true);
//        var_export(['result' => $addResult]);
        foreach ($addResult['siteShieldMaps'] as $map) {
            foreach ($map['currentCidrs'] as $currentCidr) {
                $allIPs[] = $currentCidr;
            };
            foreach ($map['proposedCidrs'] as $proposedCidrs) {
                $allIPs[] = $proposedCidrs;
            };
        }
    }
} catch (GuzzleHttp\Exception\ClientException $e) {
    echo "An error occurred: " .$e->getMessage(). "\n";
    echo "Please try again with --debug or --verbose flags.\n";
}

//# Request locations that support the diagnostic-tools
//echo "Getting firewall cidrs (last action: update).\n";
//
//try {
//    $response = $client->get('/firewall-rules-manager/v1/cidr-blocks?lastAction=update');
//    if ($response) {
//        $updateResult = json_decode($response->getBody(), true);
//        foreach ($updateResult as $cidrblock) {
//            if ($cidrblock['cidrMask'] == '/32') {
//                $singleIPs[] = $cidrblock['cidr'];
//            }
//            $subnet = $cidrblock['cidr'].$cidrblock['cidrMask'];
//            $allIPs[] = $subnet;
////            echo $subnet . PHP_EOL;
//
//        }
//        echo count($updateResult) . ' blocks with UPDATE'. PHP_EOL;
//    }
//
//} catch (GuzzleHttp\Exception\ClientException $e) {
//    echo "An error occurred: " .$e->getMessage(). "\n";
//    echo "Please try again with --debug or --verbose flags.\n";
//}
//
//# Request locations that support the diagnostic-tools
//echo "Getting firewall cidrs (effective date > today).\n";
//
//try {
//    $response = $client->get('/firewall-rules-manager/v1/cidr-blocks?effectiveDateGt=2017-02-21');
//    if ($response) {
//        $result = json_decode($response->getBody(), true);
//        foreach ($result as $cidrblock) {
//            $subnet = $cidrblock['cidr'].$cidrblock['cidrMask'];
//            echo $subnet . PHP_EOL;
//        }
//    }
//
//} catch (GuzzleHttp\Exception\ClientException $e) {
//    echo "An error occurred: " .$e->getMessage(). "\n";
//    echo "Please try again with --debug or --verbose flags.\n";
////}
//echo "Consolidation..." . PHP_EOL;
//
//$consolidator = new SubnetConsolidator();
//$singleIPs = array_unique($singleIPs);
//$sortedIPs = $consolidator->sort_addresses($singleIPs);
//echo count($sortedIPs) . ' IPS detected with /32' . PHP_EOL;
//$betterSubnets = [];
//$subnetStart = null;
//foreach ($sortedIPs as $index => $ipv4) {
//    if (!isset($sortedIPs[(int) $index + 1])) {
//        // last IP
//        if ($subnetStart) {
//            $result = $consolidator->ip_range_to_subnet_array($subnetStart, $ipv4);
//            $betterSubnets = array_merge($betterSubnets, $result);
//            $subnetStart = null;
//        }
//        continue;
//    }
//
//    if ($sortedIPs[(int) $index + 1] == $consolidator->ip_after($ipv4)) {
//        if (!$subnetStart) {
//            $subnetStart = $ipv4;
//        }
//        continue;
//    } elseif (isset($sortedIPs[(int) $index - 1]) &&
//        $ipv4 == $consolidator->ip_after($sortedIPs[(int) $index - 1])) {
//        $result = $consolidator->ip_range_to_subnet_array($subnetStart, $ipv4);
//        $betterSubnets = array_merge($betterSubnets, $result);
//        $subnetStart = null;
//    } else {
//        $betterSubnets [] = $ipv4;
//        $subnetStart = null;
//    }
//}
//$not32s = count($addResult) + count($updateResult) - count($singleIPs);
//echo (count($betterSubnets) + $not32s) . ' subnets total' . PHP_EOL;
//
//foreach ($singleIPs as $cidr) {
//    if (!$consolidator::checkIP($cidr, $betterSubnets)) {
//        echo $cidr . ' ISNT COVERED OH NOOOOOOO'.PHP_EOL;
//    }
//}
//var_export($betterSubnets);
var_export(['total raw' => count($allIPs)]);
//$uniq = array_unique($allIPs);
//var_export(['total_uniq' => count($uniq)]);

$cons = \AndrewAndante\SubMuncher\SubMuncher::consolidate_subnets($allIPs);

var_export(['total_consolidated' => count($cons)]);

//var_export(\AndrewAndante\SubMuncher\Util::sort_cidrs($uniq));
//var_export($cons);

var_export(
        [
                "removed" => array_diff($allIPs, $cons),
                "added" => array_diff($cons, $allIPs)
        ]
);

$superCons = \AndrewAndante\SubMuncher\SubMuncher::consolidate_subnets($cons, 83);


var_export(
    [
        "removed" => array_diff($cons, $superCons),
        "added" => array_diff($superCons, $cons)
    ]
);