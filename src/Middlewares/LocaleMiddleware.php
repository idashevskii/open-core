<?php

declare(strict_types = 1);

namespace OpenCore\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Closure;

class LocaleMiddleware implements MiddlewareInterface {

    private static $localeAttribute = 'locale';
    private static $langAttribute = 'lang';
    private $settingsCallback;
    private $afterDetectedCallback;

    /**
     * Set the Dispatcher instance.
     */
    public function __construct(Closure $settingsCallback, Closure $afterDetectedCallback=null) {
        $this->settingsCallback = $settingsCallback;
        $this->afterDetectedCallback = $afterDetectedCallback;
    }

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {

        $header = $request->getHeader('Accept-Language');

        $locale = null;
        $lang = null;
        $q = null;
        $settings = ($this->settingsCallback)();
        if ($header) {
            $matches = [];
            // break up string into pieces (languages and q factors)
            preg_match_all('/(([a-z]{1,8})(?:-[a-z]{1,8})?)\s*(?:;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $header[0], $matches);
            $supportedLangs = array_flip($settings['langs']);
            list(, $locales, $langs, $qs) = $matches;
            foreach ($locales as $i => $testLocale) {
                $testLang = $langs[$i];
                $testQ = $qs[$i] ? (float) $qs[$i] : 1;
                if (isset($supportedLangs[$testLang]) && $q === null || $q < $testQ) {
                    $locale = $testLocale;
                    $lang = $testLang;
                    $q = $testQ;
                }
            }
        }

        if ($q === null) {
            $locale = $settings['defaultLocale'];
            $lang = $settings['defaultLang'];
        }
        //die($locale.', '.$lang);
        
        if($this->afterDetectedCallback){
            ($this->afterDetectedCallback)($locale, $lang);
        }
        
        return $handler->handle($request
                                ->withAttribute(self::$localeAttribute, $locale)
                                ->withAttribute(self::$langAttribute, $lang));
    }

}
