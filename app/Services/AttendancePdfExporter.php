<?php
namespace App\Services;
use Symfony\Component\HttpFoundation\StreamedResponse;
class AttendancePdfExporter {
 public function download(iterable $reports,string $from,string $until):StreamedResponse {
  $lines=[['Madrasaa Ifaa Islaamaa - Attendance Report',16],['Period: '.$from.' to '.$until,10],['Generated: '.now()->format('Y-m-d H:i'),9],['',9]];
  foreach($reports as $report){$session=$report['session'];$lines[]=[strtoupper($session->session_date->toDateString()).' - '.$session->section->grade->name.' '.$session->section->name.' - '.$session->name.' ('.strtoupper($session->status).')',11];$lines[]=['Teacher: '.($session->teacherProfile?->user?->name??'Unassigned'),9];$lines[]=['Students '.$report['marked'].'/'.$report['total'].' marked - Present '.$report['counts']['present'].' - Absent '.$report['counts']['absent'].' - Late '.$report['counts']['late'].' - Excused '.$report['counts']['excused'].' - Not recorded '.$report['counts']['not_recorded'].' - '.$report['completion'].'%',9];foreach($report['rows'] as $row){$lines[]=['  '.$row['student']->full_name.' | '.$row['student']->admission_number.' | '.strtoupper(str_replace('_',' ',$row['status'])).($row['recorded_by']?' | '.$row['recorded_by']:''),9];}$lines[]=['',8];}
  $chunks=array_chunk($lines,48);if($chunks===[])$chunks=[[['No attendance sessions found for this period.',10]]];$pdf=$this->makePdf($chunks);
  return response()->streamDownload(static function()use($pdf){echo $pdf;},'attendance-report-'.$from.'-to-'.$until.'.pdf',['Content-Type'=>'application/pdf']);
 }
 private function makePdf(array $pages):string {
  $objects=[1=>'<< /Type /Catalog /Pages 2 0 R >>',2=>'',3=>'<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>'];$kids=[];
  foreach($pages as $index=>$lines){$pageId=4+($index*2);$contentId=$pageId+1;$kids[]=$pageId.' 0 R';$stream="BT\n";$y=800;foreach($lines as [$text,$size]){$safe=$this->pdfText((string)$text);$stream.='/F1 '.$size.' Tf 1 0 0 1 40 '.$y.' Tm ('.$safe.") Tj\n";$y-=max(12,$size+4);}$stream.="ET\n";$objects[$pageId]='<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R >> >> /Contents '.$contentId.' 0 R >>';$objects[$contentId]='<< /Length '.strlen($stream).">>\nstream\n".$stream."endstream";}
  $objects[2]='<< /Type /Pages /Kids ['.implode(' ',$kids).'] /Count '.count($kids).' >>';ksort($objects);$pdf="%PDF-1.4\n%AttendanceReport\n";$offsets=[0];$max=max(array_keys($objects));for($id=1;$id<=$max;$id++){$offsets[$id]=strlen($pdf);$pdf.=$id." 0 obj\n".$objects[$id]."\nendobj\n";}$xref=strlen($pdf);$pdf.="xref\n0 ".($max+1)."\n0000000000 65535 f \n";for($id=1;$id<=$max;$id++)$pdf.=sprintf('%010d 00000 n ',$offsets[$id])."\n";$pdf.="trailer\n<< /Size ".($max+1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";return $pdf;
 }
 private function pdfText(string $value):string{$encoded=@iconv('UTF-8','Windows-1252//TRANSLIT//IGNORE',$value);if($encoded!==false)$value=$encoded;$value=substr($value,0,115);return str_replace(['\\','(',')'],['\\\\','\\(','\\)'],$value);}
}
