<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_refresh_tokens', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('access_token_id')->index()->nullable();
            $table->morphs('tokenable');
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_refresh_tokens');
    }
};
