<?php

namespace Database\Seeders;

use App\Enums\DualAuthSettingEnum;
use App\Models\Backend\DualAuth\DualAuthSettings;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DualAuthSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //        $this->disableForeignKeys();


        //truncate before seed
        DualAuthSettings::truncate();

        foreach (DualAuthSettingEnum::cases() as $feature) {
            DualAuthSettings::create([
                "id" => $feature->value,
                "feature_name" => $feature->labels(),
                "status" => true,
                "updated_at" => Carbon::now(),
            ]);
        }

        //        $this->enableForeignKeys();
    }
}
