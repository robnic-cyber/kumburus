<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->string('payment_provider')->nullable();
            $table->decimal('payment_amount', 10, 2)->default(0);
        });

        DB::table('bookings')->update(['payment_amount' => DB::raw('total_amount')]);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP VIEW IF EXISTS booking_details');
            $this->createPostgresView(true);

            return;
        }

        $this->dropSqliteTriggers();

        Schema::table('booking_details', function (Blueprint $table): void {
            $table->string('payment_provider')->nullable();
            $table->decimal('payment_amount', 10, 2)->default(0);
        });

        DB::table('booking_details')->update(['payment_amount' => DB::raw('total_amount')]);
        $this->createSqliteTriggers(true);
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP VIEW IF EXISTS booking_details');
            Schema::table('bookings', function (Blueprint $table): void {
                $table->dropColumn(['payment_provider', 'payment_amount']);
            });
            $this->createPostgresView(false);

            return;
        }

        $this->dropSqliteTriggers();

        Schema::table('booking_details', function (Blueprint $table): void {
            $table->dropColumn(['payment_provider', 'payment_amount']);
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn(['payment_provider', 'payment_amount']);
        });

        $this->createSqliteTriggers(false);
    }

    private function createPostgresView(bool $withPaymentDetails): void
    {
        $details = $withPaymentDetails ? "                    b.payment_provider,\n                    b.payment_amount,\n" : '';

        DB::statement(<<<SQL
            CREATE VIEW booking_details AS
            SELECT
                b.id AS booking_id,
                b.confirmation_code,
                u.name AS guest_name,
                u.email AS guest_email,
                h.name AS hotel_name,
                r.name AS room_name,
                b.check_in,
                b.check_out,
                b.guests,
                b.total_amount,
                b.payment_method,
                {$details}                b.status,
                b.special_requests,
                b.created_at,
                b.updated_at
            FROM bookings b
            JOIN users u ON u.id = b.user_id
            JOIN rooms r ON r.id = b.room_id
            JOIN hotels h ON h.id = r.hotel_id
        SQL);
    }

    private function dropSqliteTriggers(): void
    {
        foreach ([
            'booking_details_after_hotel_update',
            'booking_details_after_room_update',
            'booking_details_after_user_update',
            'booking_details_after_delete',
            'booking_details_after_update',
            'booking_details_after_insert',
        ] as $trigger) {
            DB::statement("DROP TRIGGER IF EXISTS {$trigger}");
        }
    }

    private function createSqliteTriggers(bool $withPaymentDetails): void
    {
        $columns = 'booking_id, confirmation_code, guest_name, guest_email, hotel_name, room_name, check_in, check_out, guests, total_amount, payment_method';
        $values = 'b.id, b.confirmation_code, u.name, u.email, h.name, r.name, b.check_in, b.check_out, b.guests, b.total_amount, b.payment_method';

        if ($withPaymentDetails) {
            $columns .= ', payment_provider, payment_amount';
            $values .= ', b.payment_provider, b.payment_amount';
        }

        $insert = "INSERT INTO booking_details ({$columns}, status, special_requests, created_at, updated_at)
                SELECT {$values}, b.status, b.special_requests, b.created_at, b.updated_at";

        DB::statement(<<<SQL
            CREATE TRIGGER booking_details_after_insert
            AFTER INSERT ON bookings
            BEGIN
                {$insert}
                FROM bookings b
                JOIN users u ON u.id = b.user_id
                JOIN rooms r ON r.id = b.room_id
                JOIN hotels h ON h.id = r.hotel_id
                WHERE b.id = NEW.id;
            END
        SQL);

        DB::statement(<<<SQL
            CREATE TRIGGER booking_details_after_update
            AFTER UPDATE ON bookings
            BEGIN
                DELETE FROM booking_details WHERE booking_id = OLD.id;
                {$insert}
                FROM bookings b
                JOIN users u ON u.id = b.user_id
                JOIN rooms r ON r.id = b.room_id
                JOIN hotels h ON h.id = r.hotel_id
                WHERE b.id = NEW.id;
            END
        SQL);

        DB::statement(<<<SQL
            CREATE TRIGGER booking_details_after_delete
            AFTER DELETE ON bookings
            BEGIN
                DELETE FROM booking_details WHERE booking_id = OLD.id;
            END
        SQL);

        DB::statement(<<<SQL
            CREATE TRIGGER booking_details_after_user_update
            AFTER UPDATE OF name, email ON users
            BEGIN
                DELETE FROM booking_details
                WHERE booking_id IN (SELECT id FROM bookings WHERE user_id = NEW.id);
                {$insert}
                FROM bookings b
                JOIN users u ON u.id = b.user_id
                JOIN rooms r ON r.id = b.room_id
                JOIN hotels h ON h.id = r.hotel_id
                WHERE b.user_id = NEW.id;
            END
        SQL);

        DB::statement(<<<SQL
            CREATE TRIGGER booking_details_after_room_update
            AFTER UPDATE OF name, hotel_id ON rooms
            BEGIN
                DELETE FROM booking_details
                WHERE booking_id IN (SELECT id FROM bookings WHERE room_id = NEW.id);
                {$insert}
                FROM bookings b
                JOIN users u ON u.id = b.user_id
                JOIN rooms r ON r.id = b.room_id
                JOIN hotels h ON h.id = r.hotel_id
                WHERE b.room_id = NEW.id;
            END
        SQL);

        DB::statement(<<<SQL
            CREATE TRIGGER booking_details_after_hotel_update
            AFTER UPDATE OF name ON hotels
            BEGIN
                DELETE FROM booking_details
                WHERE booking_id IN (
                    SELECT b.id
                    FROM bookings b
                    JOIN rooms r ON r.id = b.room_id
                    WHERE r.hotel_id = NEW.id
                );
                {$insert}
                FROM bookings b
                JOIN users u ON u.id = b.user_id
                JOIN rooms r ON r.id = b.room_id
                JOIN hotels h ON h.id = r.hotel_id
                WHERE h.id = NEW.id;
            END
        SQL);
    }
};
