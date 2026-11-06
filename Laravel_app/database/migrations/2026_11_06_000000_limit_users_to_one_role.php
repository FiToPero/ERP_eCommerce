<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $connection = DB::connection();
        if (! in_array($connection->getDriverName(), ['pgsql', 'sqlite'], true)) {
            throw new RuntimeException('The single-user-role index requires PostgreSQL or SQLite.');
        }

        $grammar = $connection->getQueryGrammar();
        $table = $grammar->wrapTable(config('permission.table_names.model_has_roles'));
        $modelId = $grammar->wrap(config('permission.column_names.model_morph_key'));
        $modelType = $grammar->wrap('model_type');
        $userType = $connection->getPdo()->quote((new User)->getMorphClass());

        $connection->statement("CREATE UNIQUE INDEX model_has_roles_one_user_role ON {$table} ({$modelId}) WHERE {$modelType} = {$userType}");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX model_has_roles_one_user_role');
    }
};
