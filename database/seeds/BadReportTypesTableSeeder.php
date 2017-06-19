<?php

use App\BadReportType;
use App\Language;
use Illuminate\Database\Seeder;

class BadReportTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BadReportType::create([
            'name' => '垃圾营销'
        ]);

        BadReportType::create([
            'name' => '不实信息'
        ]);

        BadReportType::create([
            'name' => '有害信息'
        ]);

        BadReportType::create([
            'name' => '违法信息'
        ]);

        BadReportType::create([
            'name' => '淫秽色情'
        ]);

        BadReportType::create([
            'name' => '人身攻击我'
        ]);

        BadReportType::create([
            'name' => '抄袭我的内容'
        ]);

        // BadReportType::create([
        //     'name' => '骚扰我'
        // ]);
    }
}
