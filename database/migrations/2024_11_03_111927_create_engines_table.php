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
        Schema::create('engines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('alias');
            $table->string('logo');
            $table->text('handler');
            $table->longText('credentials');
            $table->longText('instructions')->nullable();
            $table->longText('filters')->nullable();
            $table->boolean('support_negative_prompt')->default(false);
            $table->longText('sizes');
            $table->longText('art_styles')->nullable();
            $table->longText('lightning_styles')->nullable();
            $table->longText('moods')->nullable();
            $table->integer('max')->default(1);
            $table->boolean('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('engines');
    }
};
