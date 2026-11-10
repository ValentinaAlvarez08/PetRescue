<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Sprint 4 — HU-11: un mismo correo puede vigilar varias zonas (casa,
     * trabajo, finca). "subscribers" pasa a ser la persona (un registro por
     * correo, con un enlace privado para administrar sus zonas) y cada zona
     * vive en "alert_zones" con su propio radio y su propio enlace de baja.
     *
     * Las suscripciones del Sprint 1 se convierten en zonas y se conserva su
     * token de baja, así los enlaces de correos ya enviados siguen sirviendo.
     */
    public function up(): void
    {
        Schema::create('alert_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_id')->constrained()->cascadeOnDelete();

            $table->string('label', 40);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('radius_km', 5, 2)->default(5);

            $table->boolean('active')->default(true);

            // CID 4: cada correo trae el enlace de baja de la zona que lo generó.
            $table->uuid('unsubscribe_token')->unique();

            $table->timestamps();

            $table->index(['latitude', 'longitude']);
        });

        Schema::table('subscribers', function (Blueprint $table) {
            $table->uuid('manage_token')->nullable()->after('email');
        });

        // Un registro por correo: los duplicados se fusionan en el primero.
        $keepers = [];
        $zoneCount = [];
        foreach (DB::table('subscribers')->orderBy('id')->get() as $row) {
            $email = Str::lower(trim($row->email));
            $keeperId = $keepers[$email] ??= $row->id;
            $n = $zoneCount[$keeperId] = ($zoneCount[$keeperId] ?? 0) + 1;

            DB::table('alert_zones')->insert([
                'subscriber_id' => $keeperId,
                'label' => $n === 1 ? 'Mi zona' : "Mi zona {$n}",
                'latitude' => $row->latitude,
                'longitude' => $row->longitude,
                'radius_km' => $row->radius_km,
                'active' => $row->notifications_enabled,
                'unsubscribe_token' => $row->unsubscribe_token,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);

            if ($keeperId === $row->id) {
                DB::table('subscribers')->where('id', $row->id)->update([
                    'email' => $email,
                    'manage_token' => (string) Str::uuid(),
                ]);
            } else {
                DB::table('subscribers')->where('id', $row->id)->delete();
            }
        }

        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropIndex(['latitude', 'longitude']);
            $table->dropUnique(['unsubscribe_token']);
        });

        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'radius_km', 'notifications_enabled', 'unsubscribe_token']);
        });

        Schema::table('subscribers', function (Blueprint $table) {
            $table->unique('email');
            $table->unique('manage_token');
        });
    }

    /**
     * Vuelve al esquema de una zona por suscriptor (se conserva la primera).
     */
    public function down(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropUnique(['manage_token']);
        });

        Schema::table('subscribers', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->default(0);
            $table->decimal('longitude', 10, 7)->default(0);
            $table->decimal('radius_km', 5, 2)->default(5);
            $table->boolean('notifications_enabled')->default(true);
            $table->uuid('unsubscribe_token')->nullable();
        });

        foreach (DB::table('subscribers')->get() as $row) {
            $zone = DB::table('alert_zones')->where('subscriber_id', $row->id)->orderBy('id')->first();

            DB::table('subscribers')->where('id', $row->id)->update($zone ? [
                'latitude' => $zone->latitude,
                'longitude' => $zone->longitude,
                'radius_km' => $zone->radius_km,
                'notifications_enabled' => $zone->active,
                'unsubscribe_token' => $zone->unsubscribe_token,
            ] : [
                'notifications_enabled' => false,
                'unsubscribe_token' => (string) Str::uuid(),
            ]);
        }

        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropColumn('manage_token');
            $table->unique('unsubscribe_token');
            $table->index(['latitude', 'longitude']);
        });

        Schema::dropIfExists('alert_zones');
    }
};
