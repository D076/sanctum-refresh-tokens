<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('personal_refresh_tokens', 'abilities')) {
            Schema::table('personal_refresh_tokens', static function (Blueprint $table) {
                $table->text('abilities')->nullable()->after('token');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('personal_refresh_tokens', 'abilities')) {
            Schema::table('personal_refresh_tokens', static function (Blueprint $table) {
                $table->dropColumn('abilities');
            });
        }
    }
};
