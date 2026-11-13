<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  if(!Schema::hasColumn('attendance_records','attendance_session_id'))Schema::table('attendance_records', function (Blueprint $table): void { $table->foreignId('attendance_session_id')->nullable()->after('id')->constrained('attendance_sessions')->restrictOnDelete(); });
  $groups=DB::table('attendance_records')->whereNull('attendance_session_id')->select('section_id','attendance_date')->distinct()->get();
  foreach($groups as $group){
   $date=substr((string)$group->attendance_date,0,10);
   $creator=DB::table('attendance_records')->where('section_id',$group->section_id)->where('attendance_date',$group->attendance_date)->value('recorded_by');
   $sessionId=DB::table('attendance_sessions')->where('section_id',$group->section_id)->where('session_date',$date)->where('name','Daily')->value('id');
   if(!$sessionId)$sessionId=DB::table('attendance_sessions')->insertGetId(['section_id'=>$group->section_id,'created_by'=>$creator,'session_date'=>$date,'name'=>'Daily','status'=>'completed','created_at'=>now(),'updated_at'=>now()]);
   DB::table('attendance_records')->where('section_id',$group->section_id)->where('attendance_date',$group->attendance_date)->whereNull('attendance_session_id')->update(['attendance_session_id'=>$sessionId]);
  }
  if(!Schema::hasIndex('attendance_records','attendance_records_student_id_index'))Schema::table('attendance_records',function(Blueprint $table):void{$table->index('student_id','attendance_records_student_id_index');});
  if(Schema::hasIndex('attendance_records','attendance_records_student_id_attendance_date_unique'))Schema::table('attendance_records', function (Blueprint $table): void { $table->dropUnique('attendance_records_student_id_attendance_date_unique'); });
  if(!Schema::hasIndex('attendance_records','attendance_session_student_unique'))Schema::table('attendance_records',function(Blueprint $table):void{$table->unique(['attendance_session_id','student_id'],'attendance_session_student_unique');});
 }
 public function down(): void { Schema::table('attendance_records', function (Blueprint $table): void { $table->dropUnique('attendance_session_student_unique'); $table->dropConstrainedForeignId('attendance_session_id'); $table->unique(['student_id','attendance_date']); }); }
};
