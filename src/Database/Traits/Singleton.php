<?php namespace Waynelogic\FilamentCms\Database\Traits;

trait Singleton
{
    /**
     * @var ?static instance
     */
    protected static $instance;

    /**
     * instance create a new instance of this singleton
     */
    final public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * forgetInstance if it exists
     */
    final public static function forgetInstance(): void
    {
        static::$instance = null;
    }

    /**
     * __construct
     */
    final protected function __construct()
    {
        $this->init();
    }

    /**
     * init the singleton free from constructor parameters
     */
    protected function init() : void {}

    /**
     * __clone
     * @ignore
     */
    private function __clone()
    {
        trigger_error('Cloning '.__CLASS__.' is not allowed.', E_USER_ERROR);
    }

    /**
     * __wakeup
     * @ignore
     */
    public function __wakeup()
    {
        trigger_error('Unserializing '.__CLASS__.' is not allowed.', E_USER_ERROR);
    }
}
