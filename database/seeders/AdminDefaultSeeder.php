<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminDefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    { 

        DB::table('admins')->insert([
            'name' => "ahmed khaled ",
            'email' => "ahmedkhaledr2@gmail.com",
            'password' => Hash::make("12345678"),
        ]);    }
}
