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
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();
            $table->string('label');
            $table->boolean('admin_email')->default(false);
            $table->boolean('user_email')->default(false);
            $table->timestamps();
        });

        $defaults = [
            ['type' => 'new_request',       'label' => 'New Borrow Request',       'admin_email' => true,  'user_email' => false],
            ['type' => 'borrow_request',    'label' => 'Borrow Request Created',   'admin_email' => false, 'user_email' => true],
            ['type' => 'borrow_approved',   'label' => 'Borrow Request Approved',  'admin_email' => false, 'user_email' => true],
            ['type' => 'borrow_rejected',   'label' => 'Borrow Request Rejected',  'admin_email' => false, 'user_email' => true],
            ['type' => 'borrow_cancelled',  'label' => 'Borrow Request Cancelled', 'admin_email' => false, 'user_email' => true],
            ['type' => 'borrow_handover',   'label' => 'Asset Handed Over',        'admin_email' => false, 'user_email' => true],
            ['type' => 'borrow_returned',   'label' => 'Asset Returned',           'admin_email' => false, 'user_email' => true],
            ['type' => 'borrow_overdue',    'label' => 'Overdue Borrowing',        'admin_email' => true,  'user_email' => true],
        ];

        foreach ($defaults as $row) {
            \DB::table('notification_settings')->insert(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
