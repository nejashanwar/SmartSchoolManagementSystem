<?php
namespace Database\Seeders;
use App\Models\ParentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
class ParentProfileSeeder extends Seeder { public function run():void { User::query()->role('PARENT')->each(fn(User $user)=>ParentProfile::query()->firstOrCreate(['user_id'=>$user->id])); } }
