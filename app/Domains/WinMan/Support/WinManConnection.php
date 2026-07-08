<?php

namespace App\Domains\WinMan\Support;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * Resolves the active read-only WinMan database connection.
 *
 * The active WinMan database (Condimentum / PreRelease) is resolved through
 * configuration so query logic never hardcodes a database name (scope §11.1).
 * DBMTS treats this connection as strictly read-only.
 */
class WinManConnection
{
    public function connectionName(): string
    {
        return (string) config('winman.connection', 'winman');
    }

    public function environment(): string
    {
        return (string) config('winman.environment', 'production');
    }

    public function database(): string
    {
        $environment = $this->environment();

        return (string) (config("winman.databases.{$environment}")
            ?? config('winman.databases.production'));
    }

    /**
     * Return the WinMan connection, ensuring it targets the resolved database.
     */
    public function connection(): ConnectionInterface
    {
        $name = $this->connectionName();
        $database = $this->database();

        if (Config::get("database.connections.{$name}.database") !== $database) {
            Config::set("database.connections.{$name}.database", $database);
            DB::purge($name);
        }

        return DB::connection($name);
    }
}
