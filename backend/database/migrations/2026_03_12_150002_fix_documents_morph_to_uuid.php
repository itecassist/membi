<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropMorphs('documentable');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->uuidMorphs('documentable');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropMorphs('documentable');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->morphs('documentable');
        });
    }
};
