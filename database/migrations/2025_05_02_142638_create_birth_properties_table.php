<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('birth_properties', function (Blueprint $table) {
            $table->id('bID');
            $table->unsignedBigInteger('childID')->unique(); // Enforce 1:1 relationship
            $table->integer('motherAge');
            $table->integer('fatherAge');
            $table->integer('numberOfChildren');
            $table->string('birthType');
            $table->float('birthWeight');
            $table->string('childCondition');

            $table->foreign('childID')->references('childID')->on('children')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('birth_properties');
    }
};
