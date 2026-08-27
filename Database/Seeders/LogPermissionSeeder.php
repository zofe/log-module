<?php

namespace App\Modules\Log\Database\Seeders;



use App\Models\Company;
use App\Modules\Auth\Models\CompanyRoles;
use Illuminate\Database\Seeder;


class LogPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //permissions
        \App\Modules\Auth\Models\Permission::firstOrNew(['name' => 'can view logs'])->save();

        $msps = Company::whereHas('roles', function($q){
            $q->where("name", '=', 'msp');
        })->get();

        foreach ($msps as $msp) {
            $msp->owner->givePermissionTo(['can view logs']);

            $cr = CompanyRoles::where('name','=','admin')->where('company_id','=',$msp->id)->first();

            if($cr) {
                $permissions = $cr->permissions;
                $permissions[] = 'can view logs';
                $cr->permissions = $permissions;

                $cr->save();
            }
        }
    }
}
