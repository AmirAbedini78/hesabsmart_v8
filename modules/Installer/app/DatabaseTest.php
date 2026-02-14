<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.7.0
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2025 KONKORD DIGITAL
 */

namespace Modules\Installer;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDOException;

class DatabaseTest
{
    /**
     * The last error occured during database tests.
     */
    protected ?string $lastError = null;

    /**
     * Test table name.
     */
    protected string $testTable = 'test_table';

    /**
     * Initialize new DatabaseTest instance.
     */
    public function __construct(protected Connection $connection) {}

    /**
     * Test DROP privilege.
     */
    public function testDropTable(): void
    {
        try {
            // Even if there is no table, will fail if the DROP privilege is not granted
            $this->dropTable();
        } catch (QueryException $e) {
            $this->lastError = $e->getMessage();
        }
    }

    /**
     * Test CREATE privilege.
     */
    public function testCreateTable(): void
    {
        $this->performTest(function () {
            $this->createTable();
        });
    }

    /**
     * Test INSERT privilege.
     */
    public function testInsert(): void
    {
        $this->performTest(function () {
            $this->createTable();
            $this->insertRow();
        });
    }

    /**
     * Test SELECT privilege.
     */
    public function testSelect(): void
    {
        $this->performTest(function () {
            $this->createTable();
            DB::usingConnection($this->connection->getName(), function () {
                $tableName = $this->withTablePrefix();

                DB::select("SELECT * FROM {$tableName}");
            });
        });
    }

    /**
     * Test UPDATE privilege.
     */
    public function testUpdate(): void
    {
        $this->performTest(function () {
            $this->createTable();
            $this->insertRow();
            DB::usingConnection($this->connection->getName(), function () {
                DB::table($this->testTable)->update(['test_column' => 'Concord']);
            });
        });
    }

    /**
     * Test DELETE privilege.
     */
    public function testDelete(): void
    {
        $this->performTest(function () {
            $this->createTable();
            $this->insertRow();
            DB::usingConnection($this->connection->getName(), function () {
                DB::table($this->testTable)->delete();
            });
        });
    }

    /**
     * Test ALTER privilege.
     */
    public function testAlter(): void
    {
        $this->performTest(function () {
            $this->createTable(function ($table) {
                $table->primary('id');
            });
        });
    }

    /**
     * Test INDEX privilege.
     */
    public function testIndex(): void
    {
        $this->performTest(function () {
            $this->createTable();
            $this->insertRow();

            $tableName = $this->withTablePrefix();

            $this->connection->getPdo()->exec(
                "CREATE INDEX test_column_index ON {$tableName} (test_column(10))"
            );
        });
    }

    /**
     * Test REFERENCES privilege.
     */
    public function testReferences(): void
    {
        try {
            $this->createTable(function ($table) {
                $table->primary('id');
            }, 'test_users');

            $this->createTable(function ($table) {
                $table->primary('id');
                $table->unsignedBigInteger('test_user_id');
                $table->foreign('test_user_id')
                    ->references('id')
                    ->on('test_users');
            });
        } catch (QueryException $e) {
            $this->lastError = $e->getMessage();
        } finally {
            $this->dropTable($this->testTable);
            $this->dropTable('test_users');
        }
    }

    /**
     * Get the last test error.
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Drop table.
     */
    protected function dropTable(?string $tableName = null): void
    {
        Schema::setConnection($this->connection)->dropIfExists($tableName ?: $this->testTable);
    }

    /**
     * Ensure the test table has prefix.
     */
    protected function withTablePrefix(?string $tableName = null): string
    {
        return $this->connection->getTablePrefix().($tableName ?? $this->testTable);
    }

    /**
     * Perform test.
     */
    protected function performTest(Closure $callback): void
    {
        try {
            $callback();
        } catch (QueryException|PDOException $e) {
            $this->lastError = $e->getMessage();
        } finally {
            $this->dropTable();
        }
    }

    /**
     * Create test table.
     */
    protected function createTable(?Closure $callback = null, ?string $tableName = null): void
    {
        Schema::setConnection($this->connection)->create($tableName ?: $this->testTable, function (Blueprint $table) use ($callback) {
            $table->unsignedBigInteger('id');
            $table->string('test_column');

            if ($callback) {
                $callback($table);
            }
        });
    }

    /**
     * Insert test row in the test table
     */
    protected function insertRow(): void
    {
        DB::usingConnection($this->connection->getName(), function () {
            DB::insert(
                'insert into '.$this->withTablePrefix().' (id, test_column) values (?, ?)',
                [1, 'Concord']
            );
        });
    }
}
