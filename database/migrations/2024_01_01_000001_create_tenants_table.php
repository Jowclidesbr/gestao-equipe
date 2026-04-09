<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ─────────────────────────────────────────────────────────────────────────────
// 0001 – TENANTS (companies / business units)
// ─────────────────────────────────────────────────────────────────────────────
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique()->comment('used as subdomain: slug.app.com');
            $table->string('cnpj', 18)->unique()->nullable();
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 7)->default('#EC0000');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable()->comment('JSON: fiscal year, policies, etc.');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
