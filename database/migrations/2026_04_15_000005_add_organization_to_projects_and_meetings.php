<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Nullable para no romper proyectos existentes
            $table->foreignId('organization_id')
                ->nullable()
                ->after('id')
                ->constrained('organizations')
                ->nullOnDelete();

            $table->foreignId('department_id')
                ->nullable()
                ->after('organization_id')
                ->constrained('departments')
                ->nullOnDelete();

            $table->index('organization_id');
            $table->index('department_id');
        });

        Schema::table('meetings', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('id')
                ->constrained('organizations')
                ->nullOnDelete();

            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['department_id']);
            $table->dropIndex(['organization_id']);
            $table->dropIndex(['department_id']);
            $table->dropColumn(['organization_id', 'department_id']);
        });

        Schema::table('meetings', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id']);
            $table->dropColumn('organization_id');
        });
    }
};
