<?php

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__.'/vendor/autoload.php';

use NovoServe\API\Client;
use NovoServe\Cloudrack\Types\ServerTag;

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related abilities and
 * settings.
 *
 * @see https://developers.whmcs.com/provisioning-modules/meta-data-params/
 *
 * @return array
 */
function novoserve_MetaData(): array
{
    return array(
        'DisplayName' => 'NovoServe Module',
        'APIVersion' => '1.1',
        'RequiresServer' => false
    );
}

/**
 * Define product configuration options.
 *
 * The values you return here define the configuration options that are
 * presented to a user when configuring a product for use with the module. These
 * values are then made available in all module function calls with the key name
 * configoptionX - with X being the index number of the field from 1 to 24.
 *
 * You can specify up to 24 parameters, with field types:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * Examples of each and their possible configuration parameters are provided in
 * this sample function.
 *
 * @see https://developers.whmcs.com/provisioning-modules/config-options/
 *
 * @return array
 */
function novoserve_ConfigOptions(): array
{
    return array(
        'Key' => array(
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your API key.',
        ),
        'Secret' => array(
            'Type' => 'password',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your API secret.',
        ),
        'Whitelabel' => array(
            'Type' => 'text',
            'Size' => '25',
            'Default' => 'yes',
            'Description' => 'Whether to use the whitelabel console, enter "yes" to enable it, or use your personal token for branded consoles.',
        )
    );
}

/**
 * Admin services tab additional fields.
 *
 * Define additional rows and fields to be displayed in the admin area service
 * information and management page within the clients profile.
 *
 * Supports an unlimited number of additional field labels and content of any
 * type to output.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 * @see novoserve_AdminServicesTabFieldsSave()
 *
 * @return array
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

        // Generate an IPMI link;
        $getIpmiLink = $api->post('servers/'.$serverTag.'/ipmi-link/', [
            'remoteIp' => $_SERVER['REMOTE_ADDR'],
            'whitelabel' => $whiteLabel
        ]);

        return ['NovoServe Module' => '<a href="' . $getIpmiLink['results'] . '" target="_blank" class="btn btn-primary">IPMI</a>'];

    } catch (Exception $e) {
        logModuleCall(
            'novoserve',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
        return ['NovoServe Module' => 'Could not generate IPMI link, error: '.$e->getMessage()];
    }
}


/**
 * Client area output logic handling.
 *
 * This function is used to define module specific client area output. It should
 * return an array consisting of a template file and optional additional
 * template variables to make available to that template.
 *
 * The template file you return can be one of two types:
 *
 * * tabOverviewModuleOutputTemplate - The output of the template provided here
 *   will be displayed as part of the default product/service client area
 *   product overview page.
 *
 * * tabOverviewReplacementTemplate - Alternatively using this option allows you
 *   to entirely take control of the product/service overview page within the
 *   client area.
 *
 * Whichever option you choose, extra template variables are defined in the same
 * way. This demonstrates the use of the full replacement.
 *
 * Please Note: Using tabOverviewReplacementTemplate means you should display
 * the standard information such as pricing and billing details in your custom
 * template or they will not be visible to the end user.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return array
 */
function novoserve_ClientArea(array $params): array
{
    try {

        // Get all service details;
        $apiKey = $params['configoption1'];
        $apiSecret = $params['configoption2'];
        $whiteLabel = is_string($params['configoption3']) ? $params['configoption3'] : 'yes';
        $serverTag = new ServerTag($params['username']);
        $serviceStatus = $params['status'];

        // Some over-engineered code to get the actual current traffic period;
        if ($params['model']->billingcycle != 'Free Account') {
            $nextDueDateTime = new DateTime($params['model']->nextduedate);
            $nextDueDateDay = $nextDueDateTime->format('d');
            if (date('d') <= $nextDueDateDay) {
                $getPeriodEnd = $nextDueDateDay.'-'.date('m-Y');
                $getPeriodEndDateTime = new DateTime($getPeriodEnd);
                $getPeriodEndDateTime->modify('-1 month');
                $getPeriodStart = $getPeriodEndDateTime->format('d-m-Y');
            } else {
                $getPeriodEnd = $nextDueDateDay.'-'.(sprintf("%02d", date('m')+1)).'-'.date('Y');
                $getPeriodEndDateTime = new DateTime($getPeriodEnd);
                $getPeriodEndDateTime->modify('-1 month');
                $getPeriodStart = $getPeriodEndDateTime->format('d-m-Y');
            }
        }

        // Stop if service is not active;
        if ($serviceStatus != 'Active') {
            throw new Exception('Service is not active.');
        }

        // Create API object;
        $api = new Client($apiKey, $apiSecret);

        // Process POST requests;
        if (!empty($_POST)) {

            // Power On;
            if (isset($_POST['poweron'])) {
                $powerOperation = $api->post('servers/'.$serverTag.'/poweron', []);
                $success = 'Power on command executed.';
            }

            // Reset;
            if (isset($_POST['reset'])) {
                $powerOperation = $api->post('servers/'.$serverTag.'/reset', []);
                $success = 'Reset command executed.';
            }

            // Power Off;
            if (isset($_POST['poweroff'])) {
                $powerOperation = $api->post('servers/'.$serverTag.'/poweroff', []);
                $success = 'Power off command executed.';
            }

            // Cold boot;
            if (isset($_POST['coldboot'])) {
                $powerOperation = $api->post('servers/'.$serverTag.'/coldboot', []);
                $success = 'Cold boot command executed.';
            }

        }

        // Execute API requests;
        $getIpmiLink = $api->post('servers/'.$serverTag.'/ipmi-link/', [
            'remoteIp' => $_SERVER['REMOTE_ADDR'],
            'whitelabel' => $whiteLabel
        ]);
        $getBandwidthGraph = $api->get('servers/'.$serverTag.'/bandwidth/graph', [
            'from' => strtotime($getPeriodStart),
            'height' => 200
        ]);
        $getTrafficUsage = $api->get('servers/'.$serverTag.'/bandwidth', [
            'from' => strtotime($getPeriodStart)
        ]);

        // Prepare values before loading it into template vars;
        $getTrafficUsage['results']['dateTimeFrom'] = date('d-m-Y', strtotime($getTrafficUsage['results']['dateTimeFrom']));
        $getTrafficUsage['results']['dateTimeUntil'] = date('d-m-Y', strtotime($getTrafficUsage['results']['dateTimeUntil']));
        $getTrafficUsage['results']['usage'] = round($getTrafficUsage['results']['usage'], 2);
        $getTrafficUsage['results']['download'] = round($getTrafficUsage['results']['download'], 2);

        // Load and return template with variables;
        return array(
            'tabOverviewModuleOutputTemplate' => 'templates/main.tpl',
            'templateVariables' => array(
                'success' => $success ?? false,
                'serverTag' => $serverTag,
                'serverHostname' => $params['domain'],
                'ipmiLink' => $getIpmiLink['results'],
                'bandwidthGraph' => $getBandwidthGraph['results']['image'],
                'trafficUsage' => $getTrafficUsage['results']
            ),
        );

    } catch (Exception $e) {
        logModuleCall(
            'novoserve',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return array(
            'tabOverviewModuleOutputTemplate' => 'templates/error.tpl',
            'templateVariables' => array('error' => $e->getMessage()),
        );
    }
}
