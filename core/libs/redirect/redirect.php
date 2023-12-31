<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   Kumbia
 * @package    Redirect
 *
 * @copyright  Copyright (c) 2005 - 2023 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */
/**
 * Clase para redireccionar peticiones
 *
 * @category   Kumbia
 * @package    Redirect
 */
class Redirect
{   

    /**
     * Redirecciona la ejecución a otro controlador en un
     * tiempo de ejecución determinado
     *
     * @param string $route ruta a la que será redirigida la petición.
     * @param integer $seconds segundos que se esperarán antes de redirigir
     * @param integer $statusCode código http de la respuesta, por defecto 302
     * 
     * @return void
     */
    public static function to($route = '', $seconds = 0, $statusCode = 302)
    {
        $route || $route = Router::get('controller_path') . '/';

        $route = PUBLIC_PATH . ltrim($route, '/');

        if ($seconds) {
            header("Refresh: $seconds; url=$route");
            return;
        }
        header('Location: '.$route, TRUE, $statusCode);
        $_SESSION['KUMBIA.CONTENT'] = ob_get_clean();
        View::select(null, null);
    }

    /**
     * Redirecciona la ejecución a una accion del controlador actual en un
     * tiempo de ejecución determinado
     *
     * @param string $action acción del controlador actual a la que se redirige
     * @param integer $seconds segundos que se esperarán antes de redirigir
     * @param integer $statusCode código http de la respuesta, por defecto 302
     * 
     * @return void
     */
    public static function toAction($action, $seconds = 0, $statusCode = 302)
    {
        self::to(Router::get('controller_path') . "/$action", $seconds, $statusCode);
    }

    /**
     * Enrutamiento interno
     * @example
     * Redirect::intern("module: modulo", "controller: nombre", "action: accion", "parameters: 1/2")
     * 
     * @return void
     */
    public static function internal()
    {
        static $cyclic = 0;
        $url = Util::getParams(func_get_args());
        $default = array('controller' => 'index', 'action' => 'index');

        $url['parameters'] = isset($url['parameters']) ? explode('/', $url['parameters']) : array();
        
        if (isset($url['module'])) {
            $vars = $url + $default;
            $vars['controller_path'] = $vars['module'] . '/' . $vars['controller'];
        } elseif (isset($url['controller'])) {
            $vars = $url + $default;
            $vars['controller_path'] = $vars['controller'];
        } else {
            $vars = $url;
        }
        
        if (++$cyclic > 1000)
            throw new KumbiaException('Se ha detectado un enrutamiento cíclico. Esto puede causar problemas de estabilidad');

        Router::to($vars, TRUE);
    }
    /**
     * Enrutamiento interno
     * @deprecated Se mantiene por legacy temporalmente
     * @example
     * Redirect::route_to("module: modulo", "controller: nombre", "action: accion", "parameters: 1/2")
     * 
     * @return void
     */
    public static function route_to() {
        self::internal(func_get_args());
    }
}
