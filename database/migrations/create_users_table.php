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
            $table->string('phone')->nullable()->after('email');
            $table->string('profile_image_url')->nullable()->after('phone');
            $table->integer('completed_trips')->default(0)->after('profile_image_url');
            $table->integer('saved_trips')->default(0)->after('completed_trips');
            $table->integer('achievements_count')->default(0)->after('saved_trips');
            $table->enum('preferred_language', ['ar', 'en'])->default('ar')->after('achievements_count');
            $table->boolean('is_admin')->default(false)->after('preferred_language');
            $table->boolean('is_active')->default(true)->after('is_admin');

            $table->index('is_active');
            $table->index('is_admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'profile_image_url',
                'completed_trips',
                'saved_trips',
                'achievements_count',
                'preferred_language',
                'is_admin',
                'is_active'
            ]);
        });
    }
};
