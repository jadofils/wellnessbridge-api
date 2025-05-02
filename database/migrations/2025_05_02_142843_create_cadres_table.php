<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cadres', function (Blueprint $table) {
            $table->id('cadID'); // Primary Key
            $table->string('name');
            $table->text('description');
            $table->string('qualification');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cadres');
    }
};
