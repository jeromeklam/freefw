<?php

namespace FreeFW\Storage\Migrations\V20240106121500;

/**
 *
 * @author jeromeklam
 *
 */
class Database extends \FreeFW\Storage\Migrations\AbstractMigration
{

    /**
     *
     * @return bool
     */
    public function up(): bool
    {
        $this->sqlUp();
        return true;
    }

    /**
     *
     * @return bool
     */
    public function down(): bool
    {
        return true;
    }
}
