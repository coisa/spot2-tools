<?php

declare(strict_types=1);

namespace CoiSA\Spot\Tool\Tests\Fixtures;

use Spot\Entity;

/**
 * Class OtherEntity
 *
 * @package CoiSA\Spot\Tool\Tests\Fixtures
 */
class OtherEntity extends Entity
{
    /** @var string */
    protected static $table = 'other_entity_table';

    /**
     * @inheritdoc
     */
    public static function fields(): array
    {
        return [
            'id'          => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'title'       => ['type' => 'string', 'required' => true],
            'description' => ['type' => 'string'],
            'created_at'  => ['type' => 'datetime', 'columnDefinition' => 'DATETIME DEFAULT CURRENT_TIMESTAMP'],
            'modified_at' => ['type' => 'datetime', 'columnDefinition' => 'DATETIME NULL ON UPDATE CURRENT_TIMESTAMP']
        ];
    }
}