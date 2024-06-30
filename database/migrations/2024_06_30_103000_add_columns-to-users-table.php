<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->addColumn('string', 'phone_number', ['length' => 16])->after('email')->nullable();
            $table->addColumn('text', 'bio', ['length' => 14])->after('phone_number')->nullable();
            $table->addColumn('string', 'location', ['length' => 64])->after('bio')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
