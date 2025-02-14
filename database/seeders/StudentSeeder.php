<?php

namespace Database\Seeders;

use App\Models\Students\Student;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();
        $classes = ['X', 'XI', 'XII'];
        $majors = ['TKJ', 'RPL', 'AKL'];

        for ($i = 1; $i <= 300; $i++) {
            Student::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password'),
                'gender' => $faker->randomElement(['Laki-laki', 'Perempuan']),
                'class' => $faker->randomElement($classes),
                'major' => $faker->randomElement($majors),
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
                'photo' => null,
                // 'balance' => $faker->randomFloat(2, 10000, 100000),
                'balance' => 0,
                'allowed' => $faker->boolean
            ]);
        }
    }

}
