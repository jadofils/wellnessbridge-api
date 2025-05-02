<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('children', function (Blueprint $table) {
            $table->id('childID');
            $table->string('name');
            $table->string('gender');
            $table->date('dob');
            $table->string('image')->nullable();
            $table->text('address');
            $table->string('parentName');
            $table->string('parentContact');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('children');
    }
};
