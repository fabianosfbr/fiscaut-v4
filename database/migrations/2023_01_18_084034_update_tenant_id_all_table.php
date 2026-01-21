<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories_tag', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->after('id');
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->after('id');
        });

        Schema::table('cfes', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->after('id');
        });

        Schema::table('ctes', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->after('id');
        });

        Schema::table('issuers', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->after('id');
        });

        Schema::table('log_sefaz_content', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->after('id');
        });

        Schema::table('log_sefaz_event', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->after('id');
        });

        Schema::table('log_sefaz_manifesto_event', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->after('id');
        });

        Schema::table('nfe_products', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->after('id');
        });

        Schema::table('nfes', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->after('id');
        });

        Schema::table('nfses', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->after('id');
        });

        Schema::table('tagging_tag_groups', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->after('id');
        });

        Schema::table('tagging_tagged', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->after('id');
        });

        Schema::table('tagging_tags', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->after('id');
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->after('id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->after('id');
        });

        Schema::table('users_verify', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
