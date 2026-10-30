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
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('id_siinas')->index();
            $table->timestamp('token_expired_at')->nullable();
            $table->string('nama_perusahaan');
            $table->string('tipe_akun')->nullable();
            $table->string('badan_hukum')->nullable();
            $table->string('badan_usaha')->nullable();
            $table->string('npwp')->nullable();
            $table->string('nib')->nullable();
            $table->string('email_perusahaan')->nullable();
            $table->string('skala_usaha')->nullable();
            $table->string('website')->nullable();
            $table->timestamp('tgl_daftar_siinas')->nullable();
            $table->json('alamat_kantor')->nullable();
            $table->json('alamat_pabrik')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
    }
};
