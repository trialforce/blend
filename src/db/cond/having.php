<?php
namespace Db\Cond;
/**
 * Uma condição do tipo having
 */
class Having extends \Db\Cond
{

    /**
     * Cria uma condição do tipo having
     *
     * @param string $filter
     * @param mixed $value
     * @param string $condition
     */
    public function __construct( $filter, $value = NULL, $condition = self::COND_AND )
    {
        parent::__construct( $filter, $value, $condition, self::TYPE_HAVING );
    }

}