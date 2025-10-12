<?php
/**
 * Patami IPS Framework
 *
 * @package IPSPATAMI
 * @version 3.4
 * @link https://bitbucket.org/patami/ipspatami
 *
 * @author Florian Wiethoff <florian.wiethoff@patami.com>
 * @copyright 2017 Florian Wiethoff
 *
 * @license GPL
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * By intentionally submitting any modifications, corrections or derivatives to this work, or any other work intended
 * for use with this Software, to the author, you confirm that you are the copyright holder for those contributions and
 * you grant the author a nonexclusive, worldwide, irrevocable, royalty-free, perpetual, license to use, copy, create
 * derivative works based on those contributions, and sublicense and distribute those contributions and any derivatives
 * thereof.
 */


namespace Patami\IPS\IO;


use Patami\IPS\Modules\Module;


/**
 * Abstract base class for IPS WebHook and WebOAuth IO modules.
 *
 * This class extends the functionality provided by the Module class to handle requests sent via IPS' WebHook or
 * WebOAuth interfaces.
 *
 * @see Module
 *
 * @package IPSPATAMI
 */
abstract class IOModule extends Module
{

    /**
     * Processes a request.
     * This method is automatically called by the child class when a WebHook or WebOAuth is received.
     * It reads the request, calls IOModule::ProcessRequest() to handle the request and sends the response returned
     * by the handler to the remote client. The IOModule::ProcessRequest() implementation must not output anything.
     * All output generated will be captured and logged in the module's debug log.
     * @see IOModule::ProcessRequest()
     */
    protected function ProcessData()
    {
        // Activate workaround for IP-Symcon bug
        if (! isset($_IPS)) {
            global $_IPS;
            $this->Debug('IP-Symcon', 'Workaround for IP-Symcon bug activated');
        }

        // Don't run if executed manually from the script editor
        if ($_IPS['SENDER'] == "Execute") {
            echo "This script cannot be used this way.";
            return;
        }

        // Capture error messages
        ob_start();

        // Read the request body
        $text = $this->ReadRequest();

        // Process the request
        $response = $this->ProcessRequest($text);

        // Log errors, if any
        $errors = ob_get_contents();
        ob_end_clean();
        if ($errors != '') {
            $this->Debug('Unexpected Output', $errors);
        }

        // Send the response
        $this->SendResponse($response);
    }

    /**
     * Reads the incoming request from the raw HTTP request.
     * @return string Text of the incoming request.
     */
    protected function ReadRequest()
    {
        $text = @file_get_contents('php://input');
        $this->Debug('Incoming Request Data', $text);

        return $text;
    }

    /**
     * Processes the request received from the remote client and generates the response.
     * This method must be implemented by concrete child classes to implement the specific communication protocol.
     * @param string $text Text received from the remote client.
     * @return ResponseInterface Response to be sent to the remote client.
     */
    abstract protected function ProcessRequest($text);

    /**
     * Sends the response to the remote client.
     * @param ResponseInterface $response Response to be sent to the remote client.
     */
    protected function SendResponse(ResponseInterface $response)
    {
        // Get the response as text
        $text = $response->GetText();
        $this->Debug('Outgoing Response Data', $text);

        // Get the response content type
        $contentType = $response->GetContentType();

        // Send the response data
        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . strlen($text));
        echo $text;
    }

}