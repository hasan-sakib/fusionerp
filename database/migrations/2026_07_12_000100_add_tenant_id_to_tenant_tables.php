<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── users ────────────────────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();
        });

        // ── categories ───────────────────────────────────────────────────────
        Schema::table('categories', function (Blueprint $table): void {
            $table->foreignId('tenant_id')
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();

            // Drop global unique, replace with per-tenant unique
            $table->dropUnique(['slug']);
            $table->unique(['tenant_id', 'slug']);
        });

        // ── products ─────────────────────────────────────────────────────────
        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('tenant_id')
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();

            $table->dropUnique(['slug']);
            $table->unique(['tenant_id', 'slug']);

            // SKU may be null; drop the global unique and add per-tenant
            $table->dropUnique(['sku']);
            $table->unique(['tenant_id', 'sku']);
        });

        // ── inventory_movements ──────────────────────────────────────────────
        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->foreignId('tenant_id')
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
        });

        // ── orders ───────────────────────────────────────────────────────────
        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('tenant_id')
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique(['tenant_id', 'sku']);
            $table->dropUnique(['tenant_id', 'slug']);
            $table->unique('slug');
            $table->unique('sku');
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropUnique(['tenant_id', 'slug']);
            $table->unique('slug');
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
