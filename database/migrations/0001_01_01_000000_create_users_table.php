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
        Schema::create('users', function (Blueprint $table) {
    $table->id(); // BIGSERIAL
    $table->string('name');             // ماشي nullable
    $table->string('email')->unique();      // ماشي nullable
    $table->string('password');             // ماشي nullable
    $table->rememberToken();                // خاص بـ remember me
    $table->timestamp('email_verified_at')->nullable(); // verification
    $table->string('role')->default('user'); // default role
    $table->string('status')->default('active'); 
    $table->string('avatar')->nullable();
    $table->timestamp('last_login_at')->nullable();// default status
    $table->timestamps();
});
      

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    
    Schema::dropIfExists('sessions');
    Schema::dropIfExists('password_reset_tokens');
    Schema::dropIfExists('users');
}
};
