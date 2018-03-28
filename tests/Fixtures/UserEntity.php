<?php

namespace CoiSA\Spot\Tool\Tests\Fixtures;

use Spot\Entity;

/**
 * Class UserEntity
 *
 * @package CoiSA\Spot\Tool\Tests\Fixtures
 */
class UserEntity extends Entity
{
    /** @var string */
    protected static $table = 'users';

    /**
     * @inheritdoc
     */
    public static function fields(): array
    {
        return [
            'id'          => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'username'    => ['type' => 'string', 'unique' => true, 'required' => true],
            'password'    => ['type' => 'string', 'required' => true],
            'created_at'  => ['type' => 'datetime', 'columnDefinition' => 'DATETIME DEFAULT CURRENT_TIMESTAMP'],
            'modified_at' => ['type' => 'datetime', 'columnDefinition' => 'DATETIME NULL ON UPDATE CURRENT_TIMESTAMP']
        ];
    }
}