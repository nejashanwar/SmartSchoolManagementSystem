<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AccessControlSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'ViewAny:User', 'View:User', 'Create:User', 'Update:User',
            'ViewAny:Role', 'View:Role', 'Create:Role', 'Update:Role', 'Delete:Role', 'DeleteAny:Role',
            'Restore:Role', 'ForceDelete:Role', 'ForceDeleteAny:Role', 'RestoreAny:Role', 'Replicate:Role', 'Reorder:Role',
            'View:AdminOverview',
            'View:SchoolProfile',
            'ViewAny:AuditEvent', 'View:AuditEvent',
            'ViewAny:Grade', 'View:Grade', 'Create:Grade', 'Update:Grade', 'Delete:Grade', 'DeleteAny:Grade',
            'ViewAny:AcademicYear', 'View:AcademicYear', 'Create:AcademicYear', 'Update:AcademicYear', 'Delete:AcademicYear', 'DeleteAny:AcademicYear',
            'ViewAny:AdmissionApplication', 'View:AdmissionApplication', 'Review:AdmissionApplication', 'Update:AdmissionApplication',
            'ViewAny:Student', 'View:Student',
            'ViewAny:TeacherProfile', 'View:TeacherProfile', 'Update:TeacherProfile',
            'ViewAny:Term', 'View:Term', 'Create:Term', 'Update:Term', 'Delete:Term', 'DeleteAny:Term',
            'ViewAny:Section', 'View:Section', 'Create:Section', 'Update:Section', 'Delete:Section', 'DeleteAny:Section',
            'ViewAny:Subject', 'View:Subject', 'Create:Subject', 'Update:Subject', 'Delete:Subject', 'DeleteAny:Subject',
            'ViewAny:TeacherAssignment', 'View:TeacherAssignment', 'Create:TeacherAssignment', 'Update:TeacherAssignment', 'Delete:TeacherAssignment', 'DeleteAny:TeacherAssignment',
            'Update:Student',
            'ViewAny:AttendanceRecord', 'View:AttendanceRecord', 'Create:AttendanceRecord', 'Update:AttendanceRecord', 'Delete:AttendanceRecord', 'DeleteAny:AttendanceRecord',
            'ViewAny:Exam', 'View:Exam', 'Create:Exam', 'Update:Exam', 'Delete:Exam', 'DeleteAny:Exam',
            'ViewAny:ExamSubject', 'View:ExamSubject', 'Create:ExamSubject', 'Update:ExamSubject', 'Delete:ExamSubject', 'DeleteAny:ExamSubject',
            'ViewAny:ExamMark', 'View:ExamMark', 'Create:ExamMark', 'Update:ExamMark', 'Delete:ExamMark', 'DeleteAny:ExamMark',
            'ViewAny:AttendanceSession', 'View:AttendanceSession', 'Create:AttendanceSession', 'Update:AttendanceSession', 'Delete:AttendanceSession', 'DeleteAny:AttendanceSession',
            'ViewAny:ParentProfile', 'View:ParentProfile', 'Create:ParentProfile', 'Update:ParentProfile', 'Delete:ParentProfile', 'DeleteAny:ParentProfile',
            'ViewAny:ExamType', 'View:ExamType', 'Create:ExamType', 'Update:ExamType', 'Delete:ExamType', 'DeleteAny:ExamType',
            'ViewAny:GradeScale', 'View:GradeScale', 'Create:GradeScale', 'Update:GradeScale', 'Delete:GradeScale', 'DeleteAny:GradeScale',
            'ViewAny:Result', 'View:Result', 'Publish:Result',
            'Generate:Result',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $allPermissions = Permission::query()->where('guard_name', 'web')->get();

        foreach (['super_admin', 'ADMIN'] as $name) {
            Role::findOrCreate($name, 'web')->syncPermissions($allPermissions);
        }

        foreach (['TEACHER', 'STUDENT', 'PARENT', 'CASHIER'] as $name) {
            Role::findOrCreate($name, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
