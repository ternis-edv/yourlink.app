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
        Schema::create('link_clicks', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('link_id')->constrained()->onDelete('cascade');
            $table->string('ip_address', 45)->nullable(); // Masked for DSGVO
            $table->text('user_agent')->nullable();
            $table->text('referer')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('city')->nullable();
            $table->boolean('is_robot')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('link_clicks');
    }
};
