<?php

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/vendor/autoload.php';

use NovoServe\API\Client;
use NovoServe\Cloudrack\Types\ServerTag;
use NovoServe\Whmcs\ResellerModule\ClientIpHelper;

/**
 * Define module related meta data.
 * Values returned here are used to determine module related abilities and
 * settings.
 * @see https://developers.whmcs.com/provisioning-modules/meta-data-params/
 * @return array
 */
function novoserve_MetaData(): array
{
    return [
        'DisplayName' => 'NovoServe Module',
        'APIVersion' => '1.1',
        'RequiresServer' => false
    ];
}

/**
 * Define product configuration options.
 * The values you return here define the configuration options that are
 * presented to a user when configuring a product for use with the module. These
 * values are then made available in all module function calls with the key name
 * configoptionX - with X being the index number of the field from 1 to 24.
 * You can specify up to 24 parameters, with field types:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 * Examples of each and their possible configuration parameters are provided in
 * this sample function.
 * @see https://developers.whmcs.com/provisioning-modules/config-options/
 * @return array
 */
function novoserve_ConfigOptions(): array
{
    return [
        'Key' => [
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your API key.',
        ],
        'Secret' => [
            'Type' => 'password',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your API secret.',
        ],
        'Whitelabel' => [
            'Type' => 'text',
            'Size' => '25',
            'Default' => 'yes',
            'Description' => 'Whether to use the whitelabel console, enter "yes" to enable it, or use your personal token for branded consoles.',
        ]
    ];
}

/**
 * Admin services tab additional fields.
 * Define additional rows and fields to be displayed in the admin area service
 * information and management page within the clients profile.
 * Supports an unlimited number of additional field labels and content of any
 * type to output.
 *
 * @param array $params common module parameters
 *
 * @return array
 * @see novoserve_AdminServicesTabFieldsSave()
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 */
function novoserve_AdminServicesTabFields(array $params): array
{
    try {
        // Get all service details;
        $apiKey = $params['configoption1'];
        $apiSecret = $params['configoption2'];
        $whiteLabel = is_string($params['configoption3']) ? $params['configoption3'] : 'yes';
        $serverTag = new ServerTag($params['username']);

        // Create API object;
        $api = new Client($apiKey, $apiSecret);
        $ipmiLink = getIpmiLink($api, $serverTag, $whiteLabel);

        return [
            'NovoServe Module' => '<a class="btn btn-primary" href="' . $ipmiLink . '" target="_blank">IPMI</a>'
        ];
    } catch (Exception $e) {
        logModuleCall(
            'novoserve',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return ['NovoServe Module' => 'Could not initialize NovoServe module, error: ' . $e->getMessage()];
    }
}

/*
 * Add buttons to the admin side to manage power functions as well
 */
function novoserve_AdminCustomButtonArray(): array
{
    return [
        'Power On' => 'poweron',
        'Reset' => 'reset',
        'Power Off' => 'poweroff',
        'Cold Boot' => 'coldboot',
    ];
}

function novoserve_poweron(array $params): string
{
    return doPowerAction(getApiClient($params), getServerTag($params), 'poweron');
}
function novoserve_reset(array $params): string
{
    return doPowerAction(getApiClient($params), getServerTag($params), 'reset');
}
function novoserve_poweroff(array $params): string
{
    return doPowerAction(getApiClient($params), getServerTag($params), 'poweroff');
}
function novoserve_coldboot(array $params): string
{
    return doPowerAction(getApiClient($params), getServerTag($params), 'coldboot');
}

function getApiClient(array $params): Client
{
    $apiKey = $params['configoption1'];
    $apiSecret = $params['configoption2'];
    return new Client($apiKey, $apiSecret);
}

function getServerTag(array $params): ServerTag
{
    return new ServerTag($params['username']);
}

function doPowerAction(Client $api, ServerTag $serverTag, string $action): string
{
    $actions = [
        'poweron' => 'Power on',
        'poweroff' => 'Power off',
        'coldboot' => 'Cold boot',
        'reset' => 'Reset',
    ];
    if (!isset($actions[$action])) {
        return 'Unknown power action';
    }

    try {
        $api->post('server/' . $serverTag . '/' . $action);
        return $actions[$action] . ' command executed.';
    } catch (Exception $e) {
        logModuleCall(
            'novoserve',
            __FUNCTION__,
            [],
            $e->getMessage(),
            $e->getTraceAsString()
        );
        return 'Could not perform power action on server.';
    }
}

function getIpmiLink(Client $api, ServerTag $serverTag, string $whiteLabel): string
{
    try {
        // Generate an IPMI link;
        $ipmiLink = $api->post('servers/' . $serverTag . '/ipmi-link', [
            'remoteIp' => ClientIpHelper::getClientIpAddress(),
            'whitelabel' => $whiteLabel,
        ])['results'] ?? '';

        return $ipmiLink;

    } catch (Exception $e) {
        logModuleCall(
            'novoserve',
            __FUNCTION__,
            [],
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return 'Could not generate IPMI link, error: ' . $e->getMessage();
    }
}

/**
 * Client area output logic handling.
 * This function is used to define module specific client area output. It should
 * return an array consisting of a template file and optional additional
 * template variables to make available to that template.
 * The template file you return can be one of two types:
 * * tabOverviewModuleOutputTemplate - The output of the template provided here
 *   will be displayed as part of the default product/service client area
 *   product overview page.
 * * tabOverviewReplacementTemplate - Alternatively using this option allows you
 *   to entirely take control of the product/service overview page within the
 *   client area.
 * Whichever option you choose, extra template variables are defined in the same
 * way. This demonstrates the use of the full replacement.
 * Please Note: Using tabOverviewReplacementTemplate means you should display
 * the standard information such as pricing and billing details in your custom
 * template or they will not be visible to the end user.
 *
 * @param array $params common module parameters
 *
 * @return array
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 */
function novoserve_ClientArea(array $params): array
{
    try {
        // Stop if service is not active;
        $serviceStatus = $params['status'];
        if ($serviceStatus !== 'Active') {
            throw new Exception('Service is not active.');
        }

        $getPeriodStart = '';
        $getPeriodEnd = '';
        // Some over-engineered code to get the actual current traffic period;
        if ($params['model']->billingcycle !== 'Free Account') {
            $nextDueDateTime = new DateTime($params['model']->nextinvoicedate);
            $nextDueDateDay = $nextDueDateTime->format('d');
            $nextDueDateTime = new DateTime(date('Y-m-') . $nextDueDateDay); // Create DateTime object, it will automatically bump the date if the day is not in this month;
            $getPeriodEndDateTime = $nextDueDateTime;
            $getPeriodStart = $getPeriodEndDateTime->modify('-1 month')->format('d-m-Y');

            if (date('d') < $nextDueDateDay) {
                $getPeriodEnd = $nextDueDateTime->format('d-m-Y');
            } else {
                $getPeriodEnd = $nextDueDateTime->modify('+1 month')->format('d-m-Y');
            }
        }

        // Get all service details;
        $whiteLabel = is_string($params['configoption3']) ? $params['configoption3'] : 'yes';
        $serverTag = getServerTag($params);

        // Create API object;
        $api = getApiClient($params);

        // Process POST requests;
        if (!empty($_POST)) {
            if (isset($_POST['poweron'])) {
                $success = doPowerAction($api, $serverTag, 'poweron');
            }
            if (isset($_POST['poweroff'])) {
                $success = doPowerAction($api, $serverTag, 'poweroff');
            }
            if (isset($_POST['reset'])) {
                $success = doPowerAction($api, $serverTag, 'reset');
            }
            if (isset($_POST['coldboot'])) {
                $success = doPowerAction($api, $serverTag, 'coldboot');
            }
        }

        $ipmiLink = getIpmiLink($api, $serverTag, $whiteLabel);

        $getBandwidthGraph = $api->get('servers/' . $serverTag . '/bandwidth/graph', [
            'from' => strtotime($getPeriodStart),
            'height' => 200
        ]);
        $getTrafficUsage = $api->get('servers/' . $serverTag . '/bandwidth', [
            'from' => strtotime($getPeriodStart)
        ]);

        // Prepare values before loading it into template vars;
        $getTrafficUsage['results']['dateTimeFrom'] = date('d-m-Y', strtotime($getPeriodStart));
        $getTrafficUsage['results']['dateTimeUntil'] = date('d-m-Y', strtotime($getPeriodEnd));
        $getTrafficUsage['results']['usage'] = round($getTrafficUsage['results']['usage'], 2);
        $getTrafficUsage['results']['download'] = round($getTrafficUsage['results']['download'], 2);

        // Load and return template with variables;
        return [
            'tabOverviewModuleOutputTemplate' => 'templates/main.tpl',
            'templateVariables' => [
                'success' => $success ?? false,
                'serverTag' => $serverTag,
                'serverHostname' => $params['domain'],
                'ipmiLink' => $ipmiLink,
                'bandwidthGraph' => $getBandwidthGraph['results']['image'],
                'trafficUsage' => $getTrafficUsage['results']
            ],
        ];

    } catch (Exception $e) {
        logModuleCall(
            'novoserve',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return [
            'tabOverviewModuleOutputTemplate' => 'templates/error.tpl',
            'templateVariables' => ['error' => $e->getMessage()],
        ];
    }
}
