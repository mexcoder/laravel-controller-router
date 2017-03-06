<?php
namespace Mexcoder\Routing;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;


class ControllerInspector
{
    /**
     * An array of HTTP verbs.
     *
     * @var array
     */
    protected $verbs = [
        'any', 'get', 'post', 'put', 'patch',
        'delete', 'head', 'options',
    ];
    /**
     * Get the routable methods for a controller.
     *
     * @param  string  $controller
     * @param  string  $prefix
     * @return array
     */
    public function getRoutable($controller, $prefix, $options = [])
    {
        $routable = [];
        $reflection = new ReflectionClass($controller);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        // To get the routable methods, we will simply spin through all methods on the
        // controller instance checking to see if it belongs to the given class and
        // is a publicly routable method. If so, we will add it to this listings.
        foreach ($methods as $method) {
            if ($this->isRoutable($method)) {
                $data = $this->getMethodData($method, $prefix, $options);
                $routable[$method->name][] = $data;
                // If the routable method is an index method, we will create a special index
                // route which is simply the prefix and the verb and does not contain any
                // the wildcard place-holders that each "typical" routes would contain.
                if ($data['plain'] == $prefix.'/index') {
                    $routable[$method->name][] = $this->getIndexData($data, $prefix);
                }
            }
        }
        return $routable;
    }
    /**
     * Determine if the given controller method is routable.
     *
     * @param  \ReflectionMethod  $method
     * @return bool
     */
    public function isRoutable(ReflectionMethod $method)
    {
        if ($method->class == 'Illuminate\Routing\Controller') {
            return false;
        }
        return Str::startsWith($method->name, $this->verbs);
    } 

    /**
     * Get the method data for a given method.
     *
     * @param  \ReflectionMethod  $method
     * @param  string  $prefix
     * @return array
     */
    public function getMethodData(ReflectionMethod $method, $prefix, $options = [])
    {
        $wildcards = Arr::get($options,"wildCards",false);
        $verb = $this->getVerb($name = $method->name);
        $plain = $this->getPlainUri($name, $prefix);
        $parameters = $this->getParameterString($method, $wildcards);
        $uri = $plain.$parameters;
        $name = lcfirst(str_replace($this->verbs, "", $method->name));
        
        return compact('verb', 'plain', 'parameters', 'uri','name');
    }
    /**
     * Get the routable data for an index method.
     *
     * @param  array   $data
     * @param  string  $prefix
     * @return array
     */
    protected function getIndexData($data, $prefix)
    {
        return ['verb' => $data['verb'],
                'plain' => $prefix, 
                'uri' =>  $prefix.$data["parameters"], 
                'name' => 'index'];
    }
    /**
     * Extract the verb from a controller action.
     *
     * @param  string  $name
     * @return string
     */
    public function getVerb($name)
    {
        return head(explode('_', Str::snake($name)));
    }
    /**
     * Determine the URI from the given method name.
     *
     * @param  string  $name
     * @param  string  $prefix
     * @return string
     */
    public function getPlainUri($name, $prefix)
    {
        return $prefix.'/'.implode('-', array_slice(explode('_', Str::snake($name)), 1));
    }
    /**
     * Add wildcards to the given URI.
     *
     * @param  string  $uri
     * @return string
     */
    public function addUriWildcards($uri)
    {
        return $uri.'/{one?}/{two?}/{three?}/{four?}/{five?}';
    }
    /**
    *   extracted from lesichkovm/laravel-advanced-route
    **/
    public function getParameterString(ReflectionMethod $method,$wildcards = true){
        $params = "";
        foreach ($method->getParameters() as $parameter) {
            if (self::hasType($parameter)) {
                continue;
            }
            $params .= sprintf('/{%s%s}', strtolower($parameter->getName()), $parameter->isDefaultValueAvailable() ? '?' : '');
        }

        if($params == null && $wildcards)
           return $this->addUriWildcards("");
        return $params;
    }

    public function addParameterString($uri, ReflectionMethod $method,$wildcards = true){
       return $uri.$this->getParameterString($method,$wildcards);
    }

    protected static function hasType(ReflectionParameter $param) {
        //TODO: if php7 use the native method
        preg_match('/\[\s\<\w+?>\s([\w]+)/s', $param->__toString(), $matches);
        return isset($matches[1]) ? true : false;
    }
}