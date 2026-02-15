<?php

use App\Domain\User\Enums\Role;
use App\Support\Database\SqlList;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->index('role', 'users_role_index');
        });

        $driver = DB::getDriverName();
        $roles = $this->roleSqlList();

        if ($driver === 'mysql' || $driver === 'pgsql') {
            DB::statement("ALTER TABLE users ADD CONSTRAINT chk_users_role CHECK (role IN ($roles))");
        }

        if ($driver === 'sqlite') {
            DB::statement("
                CREATE TRIGGER users_role_check_insert
                BEFORE INSERT ON users
                FOR EACH ROW
                WHEN NEW.role NOT IN ($roles)
                BEGIN
                    SELECT RAISE(ABORT, 'invalid users.role');
                END;
            ");

            DB::statement("
                CREATE TRIGGER users_role_check_update
                BEFORE UPDATE OF role ON users
                FOR EACH ROW
                WHEN NEW.role NOT IN ($roles)
                BEGIN
                    SELECT RAISE(ABORT, 'invalid users.role');
                END;
            ");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql' || $driver === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT chk_users_role');
        }

        if ($driver === 'sqlite') {
            DB::statement('DROP TRIGGER IF EXISTS users_role_check_insert');
            DB::statement('DROP TRIGGER IF EXISTS users_role_check_update');
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_role_index');
        });
    }

    private function roleSqlList(): string
    {
        return SqlList::inQuoted(Role::values());
    }
};
