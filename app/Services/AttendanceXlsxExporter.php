<?php
namespace App\Services;
use RuntimeException;
use ZipArchive;
class AttendanceXlsxExporter {
 public function download(iterable $reports,string $from,string $until) {
  if(!class_exists(ZipArchive::class))throw new RuntimeException('The PHP zip extension is required to create Excel workbooks.');
  $path=tempnam(storage_path('app'),'attendance-report-');if($path===false)throw new RuntimeException('Could not allocate a temporary Excel report file.');
  $xlsxPath=$path.'.xlsx';rename($path,$xlsxPath);$zip=new ZipArchive();
  if($zip->open($xlsxPath,ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true){@unlink($xlsxPath);throw new RuntimeException('Could not create the Excel report.');}
  $headers=['Date','Session','Session status','Attendance teacher','Grade','Section','Student','Admission number','Attendance status','Recorded by','Marked','Students','Present','Absent','Late','Excused','Not recorded','Completion %'];
  $rows=[$headers];
  foreach($reports as $report){$session=$report['session'];foreach($report['rows'] as $row){$rows[]=[
   $session->session_date->toDateString(),$session->name,ucfirst($session->status),$session->teacherProfile?->user?->name??'',$session->section->grade->name,$session->section->name,
   $row['student']->full_name,$row['student']->admission_number,ucwords(str_replace('_',' ',$row['status'])),$row['recorded_by']??'',
   $report['marked'],$report['total'],$report['counts']['present'],$report['counts']['absent'],$report['counts']['late'],$report['counts']['excused'],$report['counts']['not_recorded'],$report['completion'],
  ];}}
  $sheetRows='';foreach($rows as $rowIndex=>$row){$number=$rowIndex+1;$sheetRows.='<row r="'.$number.'">';foreach($row as $columnIndex=>$value){$reference=$this->columnName($columnIndex+1).$number;$escaped=htmlspecialchars((string)$value,ENT_XML1|ENT_COMPAT,'UTF-8');if($rowIndex>0&&is_numeric($value))$sheetRows.='<c r="'.$reference.'"><v>'.$escaped.'</v></c>';else $sheetRows.='<c r="'.$reference.'" t="inlineStr"><is><t xml:space="preserve">'.$escaped.'</t></is></c>';} $sheetRows.='</row>';}
  $zip->addFromString('[Content_Types].xml','<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
  $zip->addFromString('_rels/.rels','<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
  $zip->addFromString('xl/workbook.xml','<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Attendance" sheetId="1" r:id="rId1"/></sheets></workbook>');
  $zip->addFromString('xl/_rels/workbook.xml.rels','<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
  $zip->addFromString('xl/worksheets/sheet1.xml','<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'.$sheetRows.'</sheetData></worksheet>');$zip->close();
  $filename='attendance-report-'.$from.'-to-'.$until.'.xlsx';
  return response()->download($xlsxPath,$filename,['Content-Type'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])->deleteFileAfterSend(true);
 }
 private function columnName(int $number):string{$name='';while($number>0){$number--;$name=chr(65+($number%26)).$name;$number=intdiv($number,26);}return $name;}
}
