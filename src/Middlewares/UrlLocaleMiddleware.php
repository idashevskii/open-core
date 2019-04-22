<?php
declare(strict_types = 1);

namespace OpenCore\Middlewares;

use Middlewares\Utils\Traits\HasResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UrlLocaleMiddleware implements MiddlewareInterface {
        
    use HasResponseFactory;
    
    private static $localeAttribute = 'locale';
       
    private $callback=null;
    
    /**
     * Set the Dispatcher instance.
     */
    public function __construct(callable $callback) {
        $this->callback=$callback;
    }
    
    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface{
        $uri=$request->getUri();
        $matches=[];
        preg_match('/\/?((?:[a-z]{1,8})(?:-[a-z]{1,8})?)\/?(.*)/i', $uri->getPath(), $matches);
        $trimmedPath=null;
        $locale=null;
        if($matches){
            list(,$locale, $trimmedPath)=$matches;
        }else{
            return $this->createResponse(400, 'No Locale Provided');
        }
        if($this->callback){
            ($this->callback)($locale);
        }
        return $handler->handle($request->withUri($uri->withPath($trimmedPath))
                ->withAttribute(self::$localeAttribute, $locale));
        
    }
    
}
