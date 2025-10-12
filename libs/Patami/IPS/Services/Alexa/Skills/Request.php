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


namespace Patami\IPS\Services\Alexa\Skills;


use Patami\IPS\IO\IOModule;
use Patami\IPS\Services\Alexa\Skills\Custom\Response;
use Patami\IPS\System\Locales;
use Patami\IPS\System\Logging\DebugInterface;
use Patami\IPS\Services\Alexa\Skills\Exceptions\UnexpectedInformationReceivedException;


/**
 * Abstract base class for Amazon Alexa Smart Home and Custom Skill requests.
 * @package IPSPATAMI
 */
abstract class Request implements LocaleInterface, DebugInterface
{

    /**
     * @var IOModule I/O module object processing the request.
     */
    protected $io;

    /**
     * @var array Decoded request body received from the Amazon servers.
     * @link https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/alexa-skills-kit-interface-reference#request-format
     */
    protected $data;

    /**
     * @var string Locale code of the request.
     * The request is initialized with the default locale and switches over to the locale supplied in the request body.
     * @see Locales
     */
    protected $locale;

    /**
     * Request constructor.
     * @param IOModule $io I/O module object processing the request.
     * @param array $data Decoded request body received from the Amazon servers.
     * @param string $defaultLocale Locale code to be used before it can be extracted from request.
     */
    public function __construct(IOModule $io, array $data, $defaultLocale)
    {
        // Remember the variables
        $this->io = $io;
        $this->data = $data;
        $this->locale = $defaultLocale;
    }

    /**
     * Static factory method that creates a new request object.
     * This method is called by IOModuleTrait::ProcessRequest() to create the request object.
     * @param IOModule $io I/O module object processing the request.
     * @param string $text JSON-encoded request body received from the Amazon servers.
     * @param string $defaultLocale Locale code to be used before it can be extracted from request.
     * @return $this
     * @throws UnexpectedInformationReceivedException if the request body could not be JSON-decoded.
     * @see IOModuleTrait::ProcessRequest()
     */
    public static function CreateFromText(IOModule $io, $text, $defaultLocale)
    {
        // Decode JSON data from the text
        $data = json_decode($text, true);

        // Throw an exception if the data could not be decoded
        if (is_null($data)) {
            throw new UnexpectedInformationReceivedException();
        }

        // Return a new instance of this class
        $className = get_called_class();
        return new $className($io, $data, $defaultLocale);
    }

    /**
     * Returns the I/O module object.
     * @return IOModule I/O module object processing the request.
     */
    public function GetIO()
    {
        // Return the IO instance
        return $this->io;
    }

    public function GetLocale()
    {
        // Return the locale
        return $this->locale;
    }

    /**
     * Processes the request and returns a Response object.
     * This method is called by IOModuleTrait::ProcessRequest() to process the request.
     * This needs to be overridden by the concrete class to actually process the request.
     * @return Response The respnse object used by the I/O module to generate the response sent back to the Amazon
     * servers.
     * @see IOModuleTrait::ProcessRequest()
     */
    abstract public function Process();

    public function Debug($tag, $message, array $data = null)
    {
        // Forward debugging to the IO
        $this->io->Debug($tag, $message, $data);

        // Enable fluent interface
        return $this;
    }

}