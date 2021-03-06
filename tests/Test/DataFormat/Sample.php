<?php

namespace Caster\Tests\Test\DataFormat;

class Sample extends \Caster\DataFormat\DataFormat
{
    protected $database = 'default';
    protected $table = 'sample';

    protected $queries = [
        'create_table' => "CREATE TABLE IF NOT EXISTS __TABLE_NAME__ (
          `id` BIGINT(20) UNSIGNED AUTO_INCREMENT,
          `key` VARCHAR(255) NOT NULL,
          `value` VARCHAR(255) NOT NULL,
          `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `created_at` TIMESTAMP DEFAULT '2000-01-01 00\\:00\\:00',
          PRIMARY KEY (id),
          UNIQUE KEY `key` (`key`)
        )",
        'drop_table' => "DROP TABLE IF EXISTS __TABLE_NAME__",
        'insert' => "INSERT INTO __TABLE_NAME__ (
          `key`,
          `value`,
          created_at
        ) VALUES (
          :key,
          :value,
          CURRENT_TIMESTAMP
        )",
        'insert_multi' => "
            INSERT INTO __TABLE_NAME__ (
              `key`,
              `value`,
              created_at
            ) VALUES :list<
              :key,
              :value,
              CURRENT_TIMESTAMP
            >
        ",
        'find_by_id' => "
            SELECT * FROM __TABLE_NAME__ WHERE id = :id
        ",
        'find_by_ids' => "
            SELECT * FROM __TABLE_NAME__ WHERE id IN (:ids)
        ",
        'find_by_key' => "
            SELECT * FROM __TABLE_NAME__ WHERE `key` = :key
        ",
        'find_all' => "
            SELECT * FROM __TABLE_NAME__
        ",
    ];
}