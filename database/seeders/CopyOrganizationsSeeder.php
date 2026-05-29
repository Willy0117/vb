<?php
// database/seeders/CopyOrganizationsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CopyOrganizationsSeeder extends Seeder
{
    public function run(): void
    {
        $orgs = DB::table('organizations')
            ->where('type', 3)
            ->get();

        foreach ($orgs as $org) {
            $exists = DB::table('application_organizations')
                ->where('member_id', $org->member_id)
                ->where('type', 3)
                ->exists();

            if (!$exists) {
                $data = (array) $org;
                unset($data['id']);
                unset($data['note']);
                $data['created_at'] = now();
                $data['updated_at'] = now();

                DB::table('application_organizations')->insert($data);
            }
        }

        $this->command->info('コピー完了: ' . count($orgs) . '件処理しました');
    }
}
