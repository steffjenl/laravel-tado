<?php
namespace Tado;

/**
 * Class Facade
 *
 * @category  DevOps
 * @package   laravel-tado
 * @author    Stephan Eizinga <stephan.eizinga@gmail.com>
 */
class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'tado';
    }
}
