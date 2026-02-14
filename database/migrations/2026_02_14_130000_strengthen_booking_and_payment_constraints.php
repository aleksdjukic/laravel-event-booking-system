<?php

use App\Domain\Booking\Enums\BookingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->string('active_booking_key')->nullable()->after('status');
            $table->unique('active_booking_key', 'bookings_active_booking_key_unique');
        });

        DB::table('bookings')
            ->whereIn('status', [BookingStatus::PENDING->value, BookingStatus::CONFIRMED->value])
            ->orderBy('id')
            ->get(['id', 'user_id', 'ticket_id'])
            ->each(function (object $booking): void {
                DB::table('bookings')
                    ->where('id', $booking->id)
                    ->update(['active_booking_key' => $booking->user_id.':'.$booking->ticket_id]);
            });

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE bookings ADD CONSTRAINT chk_bookings_status CHECK (status IN ('pending','confirmed','cancelled'))");
            DB::statement("ALTER TABLE payments ADD CONSTRAINT chk_payments_status CHECK (status IN ('success','failed','refunded'))");
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE bookings ADD CONSTRAINT chk_bookings_status CHECK (status IN ('pending','confirmed','cancelled'))");
            DB::statement("ALTER TABLE payments ADD CONSTRAINT chk_payments_status CHECK (status IN ('success','failed','refunded'))");
        }

        if ($driver === 'sqlite') {
            DB::statement("
                CREATE TRIGGER bookings_status_check_insert
                BEFORE INSERT ON bookings
                FOR EACH ROW
                WHEN NEW.status NOT IN ('pending','confirmed','cancelled')
                BEGIN
                    SELECT RAISE(ABORT, 'invalid bookings.status');
                END;
            ");

            DB::statement("
                CREATE TRIGGER bookings_status_check_update
                BEFORE UPDATE OF status ON bookings
                FOR EACH ROW
                WHEN NEW.status NOT IN ('pending','confirmed','cancelled')
                BEGIN
                    SELECT RAISE(ABORT, 'invalid bookings.status');
                END;
            ");

            DB::statement("
                CREATE TRIGGER payments_status_check_insert
                BEFORE INSERT ON payments
                FOR EACH ROW
                WHEN NEW.status NOT IN ('success','failed','refunded')
                BEGIN
                    SELECT RAISE(ABORT, 'invalid payments.status');
                END;
            ");

            DB::statement("
                CREATE TRIGGER payments_status_check_update
                BEFORE UPDATE OF status ON payments
                FOR EACH ROW
                WHEN NEW.status NOT IN ('success','failed','refunded')
                BEGIN
                    SELECT RAISE(ABORT, 'invalid payments.status');
                END;
            ");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql' || $driver === 'pgsql') {
            DB::statement('ALTER TABLE bookings DROP CONSTRAINT chk_bookings_status');
            DB::statement('ALTER TABLE payments DROP CONSTRAINT chk_payments_status');
        }

        if ($driver === 'sqlite') {
            DB::statement('DROP TRIGGER IF EXISTS bookings_status_check_insert');
            DB::statement('DROP TRIGGER IF EXISTS bookings_status_check_update');
            DB::statement('DROP TRIGGER IF EXISTS payments_status_check_insert');
            DB::statement('DROP TRIGGER IF EXISTS payments_status_check_update');
        }

        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropUnique('bookings_active_booking_key_unique');
            $table->dropColumn('active_booking_key');
        });
    }
};
