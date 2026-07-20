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
        Schema::create('pemohon', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number', 50);
            $table->string('name');
            $table->string('email');
            $table->dateTime('email_verified_at')->nullable();
            $table->string('identity_number', 50)->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('classification', 100)->nullable();
            $table->string('organization', 100)->nullable();
            $table->string('job_title', 100)->nullable();
            $table->string('username', 100)->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->text('address')->nullable();
            $table->integer('status')->default(0);
            $table->string('token')->nullable();
            $table->string('password')->nullable();
            $table->dateTime('last_login_at')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemohon');
    }
};
