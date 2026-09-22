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
        Schema::table('sms_templates', function (Blueprint $table) {
            $table->boolean('status')->default(true)->after('url');
        });

        Schema::table('email_templates', function (Blueprint $table) {
            $table->boolean('status')->default(true)->after('button_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_templates', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
