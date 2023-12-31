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
 * @package    Cache
 * @subpackage Drivers
 *
 * @copyright  Copyright (c) 2005 - 2023 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Cacheo de Archivos para Sistemas Operativos *Nix
 *
 * @category   Kumbia
 * @package    Cache
 * @subpackage Drivers
 */
class NixfileCache extends Cache
{
    /**
     * Maxima marca de tiempo aproximada para procesadores de 32bits
     *
     * 18 de Enero de 2038
     */
    const MAX_TIMESTAMP = 2147401800;

    /**
     * Obtiene el nombre de archivo a partir de un id y grupo
     *
     * @param string $id
     * @param string $group
     * @return string
     * */
    protected function _getFilename($id, $group)
    {
        return 'cache_' . md5($id) . '.' . md5($group);
    }

    /**
     * Carga un elemento cacheado
     *
     * @param string $id
     * @param string $group
     * @return string|null
     */
    public function get($id, $group = 'default')
    {
        $this->_id = $id;
        $this->_group = $group;

        $filename = APP_PATH . 'temp/cache/' . $this->_getFilename($id, $group);

        if (is_file($filename) && filemtime($filename) >= time()) {
            return file_get_contents($filename);
        }
    }

    /**
     * Guarda un elemento en la cache con nombre $id y valor $value
     *
     * @param string $id
     * @param string $group
     * @param string $value
     * @param int $lifetime tiempo de vida en forma timestamp de unix
     * @return boolean
     */
    public function save($value, $lifetime = '', $id = '', $group = 'default')
    {
        if (!$id) {
            $id = $this->_id;
            $group = $this->_group;
        }

        if ($lifetime) {
            $lifetime = strtotime($lifetime);
        } else {
            $lifetime = self::MAX_TIMESTAMP;
        }

        $filename = APP_PATH . 'temp/cache/' . $this->_getFilename($id, $group);

        // Almacena en la fecha de modificacion la fecha de expiracion
        return file_put_contents($filename, $value) && touch($filename, $lifetime);
    }

    /**
     * Limpia la cache
     *
     * @param string $group
     * @return boolean
     */
    public function clean($group = '')
    {
        $pattern = $group ? APP_PATH . 'temp/cache/' . '*.' . md5($group) : APP_PATH . 'temp/cache/*';
        foreach (glob($pattern) as $filename) {
            if (!unlink($filename)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Elimina un elemento de la cache
     *
     * @param string $id
     * @param string $group
     * @return bool
     */
    public function remove($id, $group = 'default')
    {
        return unlink(APP_PATH . 'temp/cache/' . $this->_getFilename($id, $group));
    }

}
