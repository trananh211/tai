<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Role;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role_user = Role::where('name', 'user')->first();
        $role_manager  = Role::where('name', 'admin')->first();
        $role_saler = Role::where('name', 'saler')->first();

        $employee = new User();
        $employee->name = 'User Name';
        $employee->email = 'user@example.com';
        $employee->password = bcrypt('12345678');
        $employee->save();
        $employee->roles()->attach($role_user);

        $employee = new User();
        $employee->name = 'Employee Name';
        $employee->email = 'employee@example.com';
        $employee->password = bcrypt('12345678');
        $employee->save();
        $employee->roles()->attach($role_user);

        $saler = new User();
        $saler->name = 'Saler Name';
        $saler->email = 'saler@example.com';
        $saler->password = bcrypt('12345678');
        $saler->save();
        $saler->roles()->attach($role_saler);

        $manager = new User();
        $manager->name = 'Admin Name';
        $manager->email = 'admin@example.com';
        $manager->password = bcrypt('12345678');
        $manager->save();
        $manager->roles()->attach($role_manager);
    }

}
