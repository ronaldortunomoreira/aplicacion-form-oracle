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
        // 1. Accommodation Types (Hoteles, Hostales, Cabañas, Apartamentos, Glamping)
        Schema::create('accommodation_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('icon', 50)->default('building');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Accommodations (Propiedades)
        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accommodation_type_id')->constrained('accommodation_types')->onDelete('cascade');
            $table->string('name', 200);
            $table->string('slug', 220)->unique();
            $table->string('headline', 255)->nullable();
            $table->text('description');
            $table->string('address', 255);
            $table->string('city', 100)->index();
            $table->string('state', 100)->nullable();
            $table->string('country', 100);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedTinyInteger('star_rating')->nullable(); // 1-5 estrellas (null para hostales o cabañas)
            $table->decimal('guest_rating', 3, 1)->default(9.0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->string('check_in_time', 10)->default('15:00');
            $table->string('check_out_time', 10)->default('11:00');
            $table->json('amenities')->nullable(); // ['wifi', 'pool', 'kitchen', 'parking', 'pets', 'ac', 'breakfast']
            $table->json('policies')->nullable();  // reglas de la casa y cancelación
            $table->json('images')->nullable();    // URLs de fotos de alta resolución
            $table->enum('status', ['active', 'maintenance', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['city', 'accommodation_type_id', 'status'], 'idx_accommodations_search');
            $table->index('guest_rating', 'idx_accommodations_rating');
        });

        // 3. Room Units (Habitaciones / Unidades / Camas / Cabaña entera)
        Schema::create('room_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accommodation_id')->constrained('accommodations')->onDelete('cascade');
            $table->string('name', 200); // Ej: "Suite Deluxe", "Cama en Dormitorio Mixto (6)", "Cabaña Completa"
            $table->enum('unit_type', ['entire_place', 'private_room', 'shared_room'])->default('private_room');
            $table->unsignedSmallInteger('capacity_adults')->default(2);
            $table->unsignedSmallInteger('capacity_children')->default(0);
            $table->unsignedSmallInteger('total_units')->default(1); // Inventario de unidades idénticas
            $table->decimal('base_price_per_night', 10, 2);
            $table->decimal('cleaning_fee', 10, 2)->default(0.00); // Común en cabañas y apartamentos
            $table->enum('bathroom_type', ['private', 'shared'])->default('private'); // Vital para hostales
            $table->json('bed_distribution')->nullable(); // Ej: ["1 Cama King"] o ["6 Literas Individuales"]
            $table->json('amenities')->nullable(); // Amenities específicos de la habitación
            $table->json('images')->nullable();
            $table->timestamps();

            $table->index(['accommodation_id', 'base_price_per_night']);
        });

        // 4. Reservations (Reservas)
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_code', 20)->unique();
            $table->foreignId('room_unit_id')->constrained('room_units')->onDelete('restrict');
            $table->string('guest_name', 150);
            $table->string('guest_email', 150)->index();
            $table->string('guest_phone', 50);
            $table->string('guest_document_id', 50)->nullable();
            $table->date('check_in_date')->index();
            $table->date('check_out_date')->index();
            $table->unsignedSmallInteger('adults_count')->default(1);
            $table->unsignedSmallInteger('children_count')->default(0);
            $table->unsignedSmallInteger('nights')->default(1);
            $table->decimal('price_per_night', 10, 2);
            $table->decimal('cleaning_fee', 10, 2)->default(0.00);
            $table->decimal('taxes', 10, 2)->default(0.00);
            $table->decimal('total_price', 10, 2);
            $table->enum('payment_method', ['pay_at_property', 'credit_card', 'transfer'])->default('pay_at_property');
            $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('pending');
            $table->enum('reservation_status', ['confirmed', 'cancelled', 'completed'])->default('confirmed');
            $table->text('special_requests')->nullable();
            $table->string('cancellation_reason', 255)->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();

            // Índice compuesto crítico para validación de disponibilidad y prevención de solapamientos (overbooking)
            $table->index(
                ['room_unit_id', 'reservation_status', 'check_in_date', 'check_out_date'],
                'idx_res_availability'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('room_units');
        Schema::dropIfExists('accommodations');
        Schema::dropIfExists('accommodation_types');
    }
};
