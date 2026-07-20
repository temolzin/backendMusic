<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebhookVerificationCodesTable extends Migration
{
    public function up()
    {
        Schema::create('webhook_verification_codes', function (Blueprint $table) {
            $table->id();
            $table->string('verification_code');
            $table->string('event_id')->nullable()->unique();
            $table->timestamp('event_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        DB::statement('DROP TABLE IF EXISTS webhook_verification_codes CASCADE');
    }
}
