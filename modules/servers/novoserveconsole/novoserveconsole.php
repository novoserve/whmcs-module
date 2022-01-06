<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

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
function novoserveconsole_MetaData()
{
    return array(
        'DisplayName' => 'NovoServe Console Module',
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
function novoserveconsole_ConfigOptions()
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
            'Type' => 'yesno',
            'Default' => 'no',
            'Description' => 'Use the whitelabel console.',
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
 * @see novoserveconsole_AdminServicesTabFieldsSave()
 *
 * @return array
 */
function novoserveconsole_AdminServicesTabFields(array $params)
{
    try {

        $apiKey = $params['configoption1'];
        $apiSecret = $params['configoption2'];
        $whiteLabel = $params['configoption3'];
        $serverTag = $params['username'];
        $whiteLabel = ($whiteLabel == 'on' ? 'yes' : 'no');

        if (!novoserveconsole_checkTag($serverTag)) {
            return ['Console' => 'No server tag found, please ensure that you have entered the server tag in the Username field.'];
        }

        $generateLink = novoserveconsole_generateConsoleLink($serverTag, $whiteLabel, $apiKey, $apiSecret);
        if (isset($generateLink['status']) && $generateLink['status'] == 'success') {
            return ['Console' => '<a href="' . $generateLink['results'] . '" target="_blank" class="btn btn-primary">Autologin IPMI</a>'];
        } else {
            return ['Console' => 'Could not generate console link, error: '.$generateLink['results']];
        }

    } catch (Exception $e) {
        logModuleCall(
            'novoserveconsole',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
    }
    return array();
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
function novoserveconsole_ClientArea(array $params)
{
    try {

        $apiKey = $params['configoption1'];
        $apiSecret = $params['configoption2'];
        $whiteLabel = $params['configoption3'];
        $serverTag = $params['username'];
        $serviceStatus = $params['status'];
        $whiteLabel = ($whiteLabel == 'on' ? 'yes' : 'no');

        if ($serviceStatus == 'Active') {
            $generateLink = novoserveconsole_generateConsoleLink($serverTag, $whiteLabel, $apiKey, $apiSecret);
            if (isset($generateLink['status']) && $generateLink['status'] == 'success') {
                return '<a href="' . $generateLink['results'] . '" target="_blank" class="btn btn-primary btn-lg">Autologin IPMI</a>';
            } else {
                return '<input type="button" class="btn btn-primary btn-lg" value="Autologin IPMI" disabled>';
            }
        } else {
            return;
        }

    } catch (Exception $e) {
        logModuleCall(
            'novoserveconsole',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
    }
}

function novoserveconsole_generateConsoleLink($serverTag, $whiteLabel, $apiKey, $apiSecret)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://capi.novoserve.com/v0/servers/'.urlencode(trim($serverTag)).'/ipmi-link',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_USERPWD => trim($apiKey).':'.trim($apiSecret),
        CURLOPT_POSTFIELDS => json_encode([
            'remoteIp' => $_SERVER['REMOTE_ADDR'],
            'whitelabel' => $whiteLabel
        ]),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response, true);
}

function novoserveconsole_checkTag($tag)
{
    if (preg_match('/^\d{3}-\d{3}$/', $tag, $out)) {
        return true;
    }
    return false;
}
