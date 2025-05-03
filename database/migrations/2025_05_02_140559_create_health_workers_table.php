<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('health_workers', function (Blueprint $table) {
            $table->id('hwID'); // Primary Key
            $table->string('name');
            $table->string('gender');
            $table->date('dob');
            $table->string('role');
            $table->string('telephone')->unique();
            $table->string('email')->unique();
            $table->string('image')->nullable();
            $table->text('address');
            $table->unsignedBigInteger('cadID');

            // Corrected Foreign Key Reference
            $table->foreign('cadID')->references('cadID')->on('cadres')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_workers');
    }
};
